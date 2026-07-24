/**
 * Booking page form (Figma 418:6214 / 418:6213).
 * Client price is preview-only; server recalculates on submit.
 */
(function () {
	'use strict';

	var TOTAL_STEPS = 4;
	var SUCCESS_STEP = 5;
	var cfg = window.somvioBookingForm || {};
	var rates = cfg.rates || {};
	var services = cfg.services || {};
	var i18n = cfg.i18n || {};

	var EMAIL_RE = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
	/* E.164-ish or UK national: +44… / 07… after digit normalize */
	var PHONE_RE = /^(\+[1-9]\d{9,14}|0[1-9]\d{8,10})$/;

	function formatMoney(n) {
		return (rates.symbol || '£') + Number(n).toFixed(2);
	}

	function trim(value) {
		return String(value || '').trim();
	}

	function normalizePhone(phone) {
		var raw = String(phone || '');
		var hasPlus = raw.indexOf('+') !== -1;
		var digits = raw.replace(/[^\d]/g, '');
		return hasPlus ? '+' + digits : digits;
	}

	function isValidEmail(email) {
		return EMAIL_RE.test(trim(email));
	}

	function isValidPhone(phone) {
		var normalized = normalizePhone(phone);
		if (!PHONE_RE.test(normalized)) {
			return false;
		}
		var digits = normalized.replace(/[^\d]/g, '');
		return digits.length >= 10 && digits.length <= 15;
	}

	function isValidName(name) {
		var value = trim(name);
		return value.length >= 2 && /[A-Za-z\u00C0-\u024F]/.test(value);
	}

	function isValidAddress(address) {
		return trim(address).length >= 3;
	}

	function getPreviewTotal(state) {
		var bedKey = String(Math.max(1, Math.min(5, parseInt(state.bedrooms, 10) || 1)));
		var base =
			rates.bedroom_base && rates.bedroom_base[bedKey] != null
				? Number(rates.bedroom_base[bedKey])
				: 55;
		var baths = Math.max(1, parseInt(state.bathrooms, 10) || 1);
		var bathExtra = Math.max(0, baths - 1) * Number(rates.bathroom_extra || 10);
		var svcMult =
			rates.service_mult && rates.service_mult[state.service] != null
				? Number(rates.service_mult[state.service])
				: 1;
		var propMult =
			rates.property_mult && rates.property_mult[state.property] != null
				? Number(rates.property_mult[state.property])
				: 1;

		var addonTotal = 0;
		var addonDefs = rates.addons || {};
		(state.addons || []).forEach(function (key) {
			if (addonDefs[key] && addonDefs[key].price != null) {
				addonTotal += Number(addonDefs[key].price);
			}
		});

		return Math.round(((base + bathExtra) * svcMult * propMult + addonTotal) * 100) / 100;
	}

	function toISODate(d) {
		var y = d.getFullYear();
		var m = String(d.getMonth() + 1).padStart(2, '0');
		var day = String(d.getDate()).padStart(2, '0');
		return y + '-' + m + '-' + day;
	}

	function formatDisplayDate(iso) {
		if (!iso) {
			return i18n.selectDate || 'Select date';
		}
		var parts = iso.split('-');
		if (parts.length !== 3) {
			return iso;
		}
		var monthsShort = i18n.monthsShort || i18n.months || [];
		var monthIdx = parseInt(parts[1], 10) - 1;
		var monthName = monthsShort[monthIdx] || parts[1];
		return parseInt(parts[2], 10) + ' ' + monthName + ' ' + parts[0];
	}

	function formatSlot(slot) {
		return String(slot || '')
			.split('-')
			.map(function (part) {
				return String(part).replace(/^0(\d:)/, '$1');
			})
			.join(' - ');
	}

	/**
	 * @param {HTMLElement} root
	 */
	function initBookingForm(root) {
		if (root.getAttribute('data-booking-ready') === '1') {
			return;
		}
		root.setAttribute('data-booking-ready', '1');

		var panels = Array.prototype.slice.call(root.querySelectorAll('[data-booking-panel]'));
		var dateDisplay = root.querySelector('[data-booking-date-display]');
		var dateToggle = root.querySelector('[data-booking-date-toggle]');
		var dateBlock = root.querySelector('[data-booking-date-block]');
		var calendarEl = root.querySelector('[data-booking-calendar]');
		var calLabel = root.querySelector('[data-booking-cal-label]');
		var calGrid = root.querySelector('[data-booking-cal-grid]');
		var calWeekdays = root.querySelector('[data-booking-cal-weekdays]');
		var calPrev = root.querySelector('[data-booking-cal-prev]');
		var calNext = root.querySelector('[data-booking-cal-next]');
		var globalError = root.querySelector('[data-booking-error]');

		var today = new Date();
		today.setHours(0, 0, 0, 0);

		var state = {
			step: 1,
			service: '',
			property: 'house',
			bedrooms: '1',
			toilets: '1',
			kitchens: '1',
			bathrooms: '1',
			addons: [],
			date: '',
			time: '',
			first_name: '',
			last_name: '',
			email: '',
			phone: '',
			address: '',
			comment: '',
			terms_accepted: false,
			previewTotal: 0,
			calYear: today.getFullYear(),
			calMonth: today.getMonth(),
			submitting: false,
		};

		function field(name) {
			return root.querySelector('[data-booking-field="' + name + '"]');
		}

		function fieldError(name) {
			return root.querySelector('[data-booking-field-error="' + name + '"]');
		}

		function showError(msg) {
			if (!globalError) {
				return;
			}
			if (!msg) {
				globalError.hidden = true;
				globalError.textContent = '';
				return;
			}
			globalError.hidden = false;
			globalError.textContent = msg;
		}

		function setFieldError(name, msg) {
			var errEl = fieldError(name);
			var inputEl = field(name);
			if (errEl) {
				if (msg) {
					errEl.hidden = false;
					errEl.textContent = msg;
				} else {
					errEl.hidden = true;
					errEl.textContent = '';
				}
			}
			if (inputEl) {
				if (msg) {
					inputEl.setAttribute('aria-invalid', 'true');
					inputEl.classList.add('is-invalid');
				} else {
					inputEl.removeAttribute('aria-invalid');
					inputEl.classList.remove('is-invalid');
				}
				if (name === 'terms_accepted') {
					var termsLabel = inputEl.closest('.booking-form__terms');
					if (termsLabel) {
						termsLabel.classList.toggle('is-invalid', !!msg);
					}
				}
			}
		}

		function clearFieldErrors() {
			[
				'first_name',
				'last_name',
				'email',
				'phone',
				'address',
				'time',
				'date',
				'terms_accepted',
			].forEach(function (name) {
				setFieldError(name, '');
			});
		}

		function readFields() {
			['service', 'bedrooms', 'toilets', 'kitchens', 'bathrooms', 'date', 'time', 'first_name', 'last_name', 'email', 'phone', 'address', 'comment'].forEach(
				function (key) {
					var el = field(key);
					if (el) {
						state[key] = el.value;
					}
				}
			);
			var termsEl = field('terms_accepted');
			state.terms_accepted = !!(termsEl && termsEl.checked);
			state.previewTotal = getPreviewTotal(state);
		}

		function syncState() {
			readFields();
		}

		function renderServiceCards() {
			root.querySelectorAll('[data-booking-service]').forEach(function (btn) {
				var val = btn.getAttribute('data-booking-service');
				var selected = !!state.service && state.service === val;
				btn.classList.toggle('is-selected', selected);
				btn.setAttribute('aria-checked', selected ? 'true' : 'false');
			});
			var serviceField = field('service');
			if (serviceField) {
				serviceField.value = state.service || '';
			}
			updateNextAvailability();
		}

		function updateNextAvailability() {
			var hasService = !!state.service;
			var hasDate = !!state.date;
			var hasTime = !!state.time;
			var contactReady = isContactReady();
			var onStep1 = state.step === 1;
			var onStep3 = state.step === 3;
			var onStep4 = state.step === 4;

			root.querySelectorAll('[data-booking-next]').forEach(function (nextBtn) {
				var panel = nextBtn.closest('[data-booking-panel]');
				var panelStep = panel ? parseInt(panel.getAttribute('data-booking-step'), 10) : 0;
				var needsServiceGate = panelStep === 1 || onStep1;
				var needsDateTimeGate = panelStep === 3 || onStep3;
				var needsContactGate = panelStep === 4 || onStep4;
				var disabled = state.submitting;
				var title = '';

				if (state.submitting) {
					title = i18n.submitting || 'Submitting…';
				} else if (needsServiceGate && !hasService) {
					disabled = true;
					title = i18n.selectService || 'Select a service to continue';
				} else if (needsDateTimeGate && (!hasDate || !hasTime)) {
					disabled = true;
					title = i18n.selectDateTime || 'Select a date and time to continue';
				} else if (needsContactGate && !contactReady) {
					disabled = true;
					title = i18n.completeContact || 'Complete required fields and accept the terms to continue';
				}

				nextBtn.disabled = disabled;
				nextBtn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
				if (title) {
					nextBtn.setAttribute('title', title);
				} else {
					nextBtn.removeAttribute('title');
				}
			});
		}

		function isContactReady() {
			return (
				isValidName(state.first_name) &&
				isValidName(state.last_name) &&
				isValidEmail(state.email) &&
				isValidPhone(state.phone) &&
				isValidAddress(state.address) &&
				!!state.terms_accepted
			);
		}

		function getContactFieldError(key) {
			var value = state[key];
			if (key === 'first_name' || key === 'last_name') {
				if (!trim(value)) {
					return i18n.requiredField || i18n.invalidName || 'Please enter your name.';
				}
				if (!isValidName(value)) {
					return i18n.invalidName || 'Please enter your name.';
				}
				return '';
			}
			if (key === 'email') {
				if (!trim(value)) {
					return i18n.requiredField || i18n.invalidEmail || 'Please enter a valid email address.';
				}
				if (!isValidEmail(value)) {
					return i18n.invalidEmail || 'Please enter a valid email address.';
				}
				return '';
			}
			if (key === 'phone') {
				if (!trim(value)) {
					return i18n.requiredField || i18n.invalidPhone || 'Please enter a valid phone number.';
				}
				if (!isValidPhone(value)) {
					return i18n.invalidPhone || 'Please enter a valid phone number.';
				}
				return '';
			}
			if (key === 'address') {
				if (!isValidAddress(value)) {
					return i18n.invalidAddress || 'Please enter your street address.';
				}
				return '';
			}
			if (key === 'terms_accepted') {
				return state.terms_accepted
					? ''
					: i18n.termsRequired || 'Please accept the Terms & Conditions and Privacy Policy.';
			}
			return '';
		}

		function validateContactField(key, opts) {
			var options = opts || {};
			var allowEmpty = !!options.allowEmpty;
			var value = key === 'terms_accepted' ? state.terms_accepted : state[key];
			if (allowEmpty && key !== 'terms_accepted' && !trim(value)) {
				setFieldError(key, '');
				return true;
			}
			var msg = getContactFieldError(key);
			setFieldError(key, msg);
			return !msg;
		}

		function renderSuccessRecap() {
			var serviceLabel = (services && services[state.service]) || state.service || '';
			var map = {
				service: serviceLabel,
				date: formatDisplayDate(state.date),
				time: formatSlot(state.time),
				name: trim(state.first_name) + ' ' + trim(state.last_name),
				phone: trim(state.phone),
				email: trim(state.email),
				address: trim(state.address),
				total: formatMoney(state.previewTotal),
			};
			Object.keys(map).forEach(function (key) {
				var el = root.querySelector('[data-booking-success="' + key + '"]');
				if (el) {
					el.textContent = map[key];
				}
			});
		}

		function renderAddons() {
			root.querySelectorAll('[data-booking-addon]').forEach(function (btn) {
				var key = btn.getAttribute('data-booking-addon');
				var on = state.addons.indexOf(key) !== -1;
				btn.classList.toggle('is-selected', on);
				btn.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
			var addonsField = field('addons');
			if (addonsField) {
				addonsField.value = state.addons.join(',');
			}
		}

		function renderSlots() {
			var hasDate = !!state.date;
			root.querySelectorAll('[data-booking-slot]').forEach(function (btn) {
				var val = btn.getAttribute('data-booking-slot');
				var selected = state.time === val;
				btn.classList.toggle('is-selected', selected);
				btn.setAttribute('aria-checked', selected ? 'true' : 'false');
				btn.disabled = !hasDate;
			});
			var slotsEl = root.querySelector('[data-booking-slots]');
			if (slotsEl) {
				slotsEl.classList.toggle('is-disabled', !hasDate);
				slotsEl.setAttribute('aria-disabled', hasDate ? 'false' : 'true');
			}
		}

		function updateSlotsAvailability() {
			renderSlots();
		}

		function renderWeekdays() {
			if (!calWeekdays) {
				return;
			}
			var days = i18n.weekdays || ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
			calWeekdays.innerHTML = '';
			days.forEach(function (d) {
				var el = document.createElement('span');
				el.className = 'booking-form__cal-weekday';
				el.textContent = d;
				calWeekdays.appendChild(el);
			});
		}

		function syncCalViewToDate(iso) {
			if (!iso) {
				return false;
			}
			var parts = String(iso).split('-');
			if (parts.length !== 3) {
				return false;
			}
			var y = parseInt(parts[0], 10);
			var m = parseInt(parts[1], 10) - 1;
			if (isNaN(y) || isNaN(m) || m < 0 || m > 11) {
				return false;
			}
			state.calYear = y;
			state.calMonth = m;
			return true;
		}

		function canNavigatePrevMonth() {
			return (
				state.calYear > today.getFullYear() ||
				(state.calYear === today.getFullYear() && state.calMonth > today.getMonth())
			);
		}

		function isPastDate(dateObj) {
			var cellDay = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
			return cellDay < today;
		}

		function updateCalNavButtons() {
			if (calPrev) {
				var allowPrev = canNavigatePrevMonth();
				calPrev.disabled = !allowPrev;
				calPrev.setAttribute('aria-disabled', allowPrev ? 'false' : 'true');
			}
			if (calNext) {
				calNext.disabled = false;
				calNext.setAttribute('aria-disabled', 'false');
			}
		}

		function renderCalendar() {
			if (!calGrid || !calLabel) {
				return;
			}

			var months = i18n.months || [];
			calLabel.textContent = (months[state.calMonth] || '') + ' ' + state.calYear;
			updateCalNavButtons();

			var first = new Date(state.calYear, state.calMonth, 1);
			var startDow = first.getDay();
			var daysInMonth = new Date(state.calYear, state.calMonth + 1, 0).getDate();
			var prevDays = new Date(state.calYear, state.calMonth, 0).getDate();

			calGrid.innerHTML = '';

			var cells = [];
			var i;
			for (i = startDow - 1; i >= 0; i--) {
				cells.push({
					day: prevDays - i,
					outside: true,
					date: new Date(state.calYear, state.calMonth - 1, prevDays - i),
				});
			}
			for (i = 1; i <= daysInMonth; i++) {
				cells.push({
					day: i,
					outside: false,
					date: new Date(state.calYear, state.calMonth, i),
				});
			}
			while (cells.length % 7 !== 0 || cells.length < 42) {
				var nextDay = cells.length - (startDow + daysInMonth) + 1;
				cells.push({
					day: nextDay,
					outside: true,
					date: new Date(state.calYear, state.calMonth + 1, nextDay),
				});
			}

			var frag = document.createDocumentFragment();

			cells.forEach(function (cell) {
				var iso = toISODate(cell.date);
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'booking-form__cal-day';
				btn.textContent = String(cell.day);
				btn.setAttribute('role', 'option');
				btn.setAttribute('data-date', iso);
				btn.style.visibility = 'visible';
				btn.style.opacity = '1';
				btn.style.display = 'flex';

				var past = isPastDate(cell.date);
				if (cell.outside) {
					btn.classList.add('is-outside');
				}
				if (past) {
					btn.classList.add('is-disabled');
					btn.disabled = true;
					btn.setAttribute('aria-disabled', 'true');
					btn.tabIndex = -1;
				}
				if (state.date === iso) {
					btn.classList.add('is-selected');
					btn.setAttribute('aria-selected', 'true');
				} else {
					btn.setAttribute('aria-selected', 'false');
				}

				if (!past) {
					btn.addEventListener('click', function (event) {
						event.preventDefault();
						event.stopPropagation();
						if (isPastDate(cell.date)) {
							return;
						}
						state.date = iso;
						syncCalViewToDate(iso);
						var dateField = field('date');
						if (dateField) {
							dateField.value = iso;
						}
						if (dateDisplay) {
							dateDisplay.value = formatDisplayDate(iso);
						}
						setCalendarOpen(false);
						updateSlotsAvailability();
						syncState();
						setFieldError('date', '');
						showError('');
						updateNextAvailability();
					});
				}

				frag.appendChild(btn);
			});

			calGrid.appendChild(frag);
		}

		/**
		 * Re-paint calendar after Step 3 becomes visible (fixes 0-dimension init).
		 */
		function refreshCalendarPaint() {
			if (!calendarEl || calendarEl.hidden) {
				return;
			}
			renderWeekdays();
			renderCalendar();
			renderSlots();
			/* Force layout + browser repaint. */
			void calendarEl.offsetWidth;
			void (calGrid && calGrid.offsetHeight);
			window.dispatchEvent(new Event('resize'));
		}

		function setCalendarOpen(open) {
			if (!calendarEl) {
				return;
			}
			if (open) {
				if (state.date) {
					syncCalViewToDate(state.date);
				} else {
					state.calYear = today.getFullYear();
					state.calMonth = today.getMonth();
				}
			}
			calendarEl.hidden = !open;
			if (dateBlock) {
				dateBlock.classList.toggle('is-calendar-open', !!open);
			}
			if (dateDisplay) {
				dateDisplay.setAttribute('aria-expanded', open ? 'true' : 'false');
			}
			if (dateToggle) {
				dateToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			}
			if (open) {
				requestAnimationFrame(function () {
					refreshCalendarPaint();
					requestAnimationFrame(refreshCalendarPaint);
				});
			}
		}

		function updateStepLabels(step) {
			var tpl = i18n.stepOf || 'Step %1$d of %2$d';
			var label = tpl
				.replace('%1$d', String(Math.min(step, TOTAL_STEPS)))
				.replace('%2$d', String(TOTAL_STEPS));
			root.querySelectorAll('[data-booking-step-label]').forEach(function (el) {
				el.hidden = step === SUCCESS_STEP;
				if (step !== SUCCESS_STEP) {
					el.textContent = label;
				}
			});
		}

		function setLoading(loading) {
			root.querySelectorAll('[data-booking-next]').forEach(function (nextBtn) {
				nextBtn.classList.toggle('is-loading', loading);
				nextBtn.setAttribute('aria-busy', loading ? 'true' : 'false');
				var spinner = nextBtn.querySelector('[data-booking-spinner]');
				var nextIcon = nextBtn.querySelector('[data-booking-next-icon]');
				var nextLabel = nextBtn.querySelector('[data-booking-next-label]');
				if (spinner) {
					spinner.hidden = !loading;
				}
				if (nextIcon) {
					nextIcon.hidden = loading;
				}
				if (nextLabel && loading && state.step === TOTAL_STEPS) {
					nextLabel.textContent = i18n.submitting || 'Submitting…';
				}
			});
			updateNextAvailability();
		}

		function setStep(step) {
			state.step = step;
			root.setAttribute('data-step', String(step));
			root.classList.toggle('is-success', step === SUCCESS_STEP);
			var activePanel = null;

			panels.forEach(function (panel) {
				var n = parseInt(panel.getAttribute('data-booking-step'), 10);
				var show = n === step;
				panel.hidden = !show;
				panel.setAttribute('aria-hidden', show ? 'false' : 'true');
				if (show) {
					activePanel = panel;
				}
			});

			updateStepLabels(step);
			updateNextAvailability();

			clearFieldErrors();
			showError('');
			setLoading(false);
			syncState();

			if (step === 3) {
				setCalendarOpen(false);
				updateSlotsAvailability();
			}

			if (step === SUCCESS_STEP) {
				renderSuccessRecap();
			}

			if (activePanel && typeof activePanel.scrollIntoView === 'function' && step !== 1) {
				activePanel.scrollIntoView({ behavior: 'smooth', block: step === SUCCESS_STEP ? 'center' : 'start' });
			}

			root.dispatchEvent(
				new CustomEvent('somvio:booking-step', {
					bubbles: true,
					detail: { step: step },
				})
			);
		}

		function validateContact() {
			readFields();
			clearFieldErrors();
			showError('');

			var firstInvalid = null;
			var hasError = false;
			['first_name', 'last_name', 'phone', 'email', 'address', 'terms_accepted'].forEach(function (key) {
				var msg = getContactFieldError(key);
				if (msg) {
					setFieldError(key, msg);
					if (!firstInvalid) {
						firstInvalid = field(key);
					}
					hasError = true;
				}
			});

			if (hasError) {
				showError(i18n.required || 'Please complete the required fields.');
			}

			return { valid: !hasError, firstInvalid: firstInvalid };
		}

		function validateStep() {
			readFields();

			if (state.step === 1) {
				if (!state.service) {
					showError(i18n.selectService || 'Please select a service.');
					return false;
				}
				return true;
			}

			if (state.step === 2) {
				return true;
			}

			if (state.step === 3) {
				clearFieldErrors();
				if (!state.date) {
					setFieldError('date', i18n.selectDate || 'Please select a date.');
					showError(i18n.selectDate || 'Please select a date.');
					return false;
				}
				var selectedParts = String(state.date).split('-');
				if (selectedParts.length === 3) {
					var selectedDay = new Date(
						parseInt(selectedParts[0], 10),
						parseInt(selectedParts[1], 10) - 1,
						parseInt(selectedParts[2], 10)
					);
					if (isPastDate(selectedDay)) {
						state.date = '';
						var dateField = field('date');
						if (dateField) {
							dateField.value = '';
						}
						if (dateDisplay) {
							dateDisplay.value = '';
						}
						setFieldError('date', i18n.selectDate || 'Please select a date.');
						showError(i18n.selectDate || 'Please select a future date.');
						updateSlotsAvailability();
						updateNextAvailability();
						return false;
					}
				}
				if (!state.time) {
					setFieldError('time', i18n.selectTime || 'Please select a time slot.');
					showError(i18n.selectTime || 'Please select a time slot.');
					return false;
				}
				return true;
			}

			if (state.step === 4) {
				var result = validateContact();
				if (!result.valid && result.firstInvalid) {
					result.firstInvalid.focus();
				}
				return result.valid;
			}

			return true;
		}

		function submitBooking() {
			if (state.submitting) {
				return Promise.resolve();
			}

			var validation = validateContact();
			if (!validation.valid) {
				if (validation.firstInvalid) {
					validation.firstInvalid.focus();
				}
				return Promise.resolve();
			}

			state.submitting = true;
			setLoading(true);
			readFields();
			syncState();

			var payload = {
				service: state.service,
				property: state.property || 'house',
				bedrooms: parseInt(state.bedrooms, 10),
				bathrooms: parseInt(state.bathrooms, 10),
				toilets: parseInt(state.toilets, 10),
				kitchens: parseInt(state.kitchens, 10),
				date: state.date,
				time: state.time,
				first_name: trim(state.first_name),
				last_name: trim(state.last_name),
				name: trim(state.first_name) + ' ' + trim(state.last_name),
				email: trim(state.email),
				phone: normalizePhone(state.phone),
				address: trim(state.address),
				comment: trim(state.comment),
				addons: state.addons.slice(),
				terms_accepted: true,
				source: 'booking',
				client_total: state.previewTotal,
			};

			return fetch(cfg.restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cfg.nonce || '',
				},
				body: JSON.stringify(payload),
			})
				.then(function (res) {
					return res.json().then(function (data) {
						return { ok: res.ok, status: res.status, data: data };
					});
				})
				.then(function (result) {
					state.submitting = false;
					setLoading(false);

					if (result.status === 409 && result.data && result.data.data && result.data.data.total != null) {
						state.previewTotal = Number(result.data.data.total);
						syncState();
						showError(result.data.message || i18n.submitError || 'Something went wrong. Please try again.');
						return;
					}

					if (!result.ok || !result.data || !result.data.success) {
						var msg =
							(result.data && (result.data.message || (result.data.data && result.data.data.message))) ||
							i18n.submitError ||
							'Something went wrong. Please try again.';
						showError(msg);
						return;
					}

					if (result.data.total != null) {
						state.previewTotal = Number(result.data.total);
						syncState();
					}

					setStep(SUCCESS_STEP);
					root.dispatchEvent(
						new CustomEvent('somvio:booking-success', {
							bubbles: true,
							detail: { total: state.previewTotal },
						})
					);
				})
				.catch(function () {
					state.submitting = false;
					setLoading(false);
					showError(i18n.submitError || 'Something went wrong. Please try again.');
				});
		}

		/* Events */
		root.querySelectorAll('[data-booking-service]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				state.service = btn.getAttribute('data-booking-service') || '';
				showError('');
				renderServiceCards();
				syncState();
			});
		});

		root.querySelectorAll('[data-booking-counter]').forEach(function (wrap) {
			var key = wrap.getAttribute('data-booking-counter');
			var input = wrap.querySelector('[data-booking-field="' + key + '"]');
			var dec = wrap.querySelector('[data-booking-counter-dec]');
			var inc = wrap.querySelector('[data-booking-counter-inc]');
			if (!input) {
				return;
			}
			var minAttr = input.getAttribute('min');
			var maxAttr = input.getAttribute('max');
			var min = minAttr !== null && minAttr !== '' ? parseInt(minAttr, 10) : 1;
			var max = maxAttr !== null && maxAttr !== '' ? parseInt(maxAttr, 10) : 5;
			if (isNaN(min)) {
				min = 1;
			}
			if (isNaN(max)) {
				max = 5;
			}

			function syncButtons(n) {
				if (dec) {
					dec.disabled = n <= min;
				}
				if (inc) {
					inc.disabled = n >= max;
				}
			}

			function setActive() {
				root.querySelectorAll('[data-booking-counter]').forEach(function (el) {
					el.classList.remove('is-active');
				});
				wrap.classList.add('is-active');
			}

			function setVal(n) {
				n = Math.max(min, Math.min(max, n));
				input.value = String(n);
				state[key] = String(n);
				syncButtons(n);
				setActive();
				syncState();
			}

			syncButtons(parseInt(input.value, 10) || min);

			if (dec) {
				dec.addEventListener('click', function () {
					setVal((parseInt(input.value, 10) || 0) - 1);
				});
			}
			if (inc) {
				inc.addEventListener('click', function () {
					setVal((parseInt(input.value, 10) || 0) + 1);
				});
			}

			wrap.addEventListener('focusin', setActive);
		});

		root.querySelectorAll('[data-booking-addon]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var key = btn.getAttribute('data-booking-addon');
				var idx = state.addons.indexOf(key);
				if (idx === -1) {
					state.addons.push(key);
				} else {
					state.addons.splice(idx, 1);
				}
				renderAddons();
				syncState();
			});
		});

		root.querySelectorAll('[data-booking-slot]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!state.date || btn.disabled) {
					return;
				}
				state.time = btn.getAttribute('data-booking-slot') || '';
				var timeField = field('time');
				if (timeField) {
					timeField.value = state.time;
				}
				renderSlots();
				syncState();
				setFieldError('time', '');
				showError('');
				updateNextAvailability();
			});
		});

		if (dateToggle) {
			dateToggle.addEventListener('click', function (event) {
				event.preventDefault();
				var willOpen = !calendarEl || calendarEl.hidden;
				setCalendarOpen(willOpen);
			});
			dateToggle.addEventListener('keydown', function (event) {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					var willOpen = !calendarEl || calendarEl.hidden;
					setCalendarOpen(willOpen);
				}
			});
		}

		if (calPrev) {
			calPrev.addEventListener('click', function () {
				if (!canNavigatePrevMonth()) {
					return;
				}
				state.calMonth -= 1;
				if (state.calMonth < 0) {
					state.calMonth = 11;
					state.calYear -= 1;
				}
				refreshCalendarPaint();
			});
		}
		if (calNext) {
			calNext.addEventListener('click', function () {
				state.calMonth += 1;
				if (state.calMonth > 11) {
					state.calMonth = 0;
					state.calYear += 1;
				}
				refreshCalendarPaint();
			});
		}

		root.querySelectorAll('[data-booking-next]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (state.step === SUCCESS_STEP) {
					return;
				}
				if (!validateStep()) {
					return;
				}
				if (state.step === TOTAL_STEPS) {
					submitBooking();
					return;
				}
				setStep(state.step + 1);
			});
		});

		['first_name', 'last_name', 'email', 'phone', 'address', 'comment'].forEach(function (key) {
			var el = field(key);
			if (!el) {
				return;
			}
			el.addEventListener('input', function () {
				state[key] = el.value;
				syncState();
				if (key !== 'comment' && el.classList.contains('is-invalid')) {
					validateContactField(key);
				}
				updateNextAvailability();
			});
			if (key !== 'comment') {
				el.addEventListener('blur', function () {
					state[key] = el.value;
					syncState();
					validateContactField(key);
					updateNextAvailability();
				});
			}
		});

		var termsEl = field('terms_accepted');
		if (termsEl) {
			termsEl.addEventListener('change', function () {
				state.terms_accepted = !!termsEl.checked;
				validateContactField('terms_accepted', { allowEmpty: false });
				updateNextAvailability();
			});
		}

		renderWeekdays();
		renderServiceCards();
		renderAddons();
		syncState();
		setStep(1);
	}

	function boot() {
		document.querySelectorAll('[data-booking-form]').forEach(initBookingForm);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
