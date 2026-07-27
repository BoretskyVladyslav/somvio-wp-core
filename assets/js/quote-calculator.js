/**
 * Somvio multi-step instant quote calculator.
 * Client price is preview-only; confirm always recalculates on the server.
 */
(function () {
	'use strict';

	var TOTAL_STEPS = 5;
	var SUCCESS_STEP = 6;
	var cfg = window.somvioQuoteCalc || {};
	var rates = cfg.rates || {};
	var i18n = cfg.i18n || {};

	var EMAIL_RE = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
	var PHONE_RE = /^(\+?[1-9]\d{9,14}|0[1-9]\d{9,10})$/;

	/**
	 * @param {number} n
	 * @returns {string}
	 */
	function formatMoney(n) {
		var symbol = rates.symbol || '£';
		return symbol + Number(n).toFixed(2);
	}

	/**
	 * @param {string} value
	 * @returns {string}
	 */
	function trim(value) {
		return String(value || '').trim();
	}

	/**
	 * @param {string} phone
	 * @returns {string}
	 */
	function normalizePhone(phone) {
		var raw = String(phone || '');
		var hasPlus = raw.charAt(0) === '+';
		var digits = raw.replace(/[^\d]/g, '');
		return hasPlus ? '+' + digits : digits;
	}

	/**
	 * @param {string} phone
	 * @returns {string}
	 */
	function formatPhoneDisplay(phone) {
		var normalized = normalizePhone(phone);
		if (!normalized) {
			return '';
		}
		if (normalized.charAt(0) === '+') {
			return normalized;
		}
		if (normalized.length <= 5) {
			return normalized;
		}
		if (normalized.length <= 10) {
			return normalized.replace(/(\d{4,5})(\d{3,6})/, '$1 $2').trim();
		}
		return normalized.replace(/(\d{5})(\d{6})/, '$1 $2');
	}

	/**
	 * @param {string} email
	 * @returns {boolean}
	 */
	function isValidEmail(email) {
		return EMAIL_RE.test(trim(email));
	}

	/**
	 * @param {string} phone
	 * @returns {boolean}
	 */
	function isValidPhone(phone) {
		return PHONE_RE.test(normalizePhone(phone));
	}

	/**
	 * @param {string} name
	 * @returns {boolean}
	 */
	function isValidName(name) {
		return trim(name).length >= 2;
	}

	function serviceHasExtras(service) {
		var list = rates.extras_services;
		if (Array.isArray(list) && list.length) {
			return list.indexOf(service) !== -1;
		}
		return (
			service === 'deep-cleaning' ||
			service === 'end-of-tenancy' ||
			service === 'after-builders'
		);
	}

	function getRoomFieldsForService(service) {
		if (service === 'regular-cleaning') {
			return ['bedrooms'];
		}
		if (service === 'airbnb-cleaning') {
			return ['bedrooms', 'bathrooms', 'linen_changes'];
		}
		if (
			service === 'deep-cleaning' ||
			service === 'end-of-tenancy' ||
			service === 'after-builders'
		) {
			return ['main_rooms', 'bedrooms', 'bathrooms'];
		}
		return ['bedrooms', 'bathrooms'];
	}

	function bedroomHomeLabel(n) {
		var count = parseInt(n, 10) || 1;
		var tpl = i18n.bedroomHome || '%d Bedroom Home';
		return tpl.replace('%d', String(count));
	}

	/**
	 * @param {object} state
	 * @returns {number}
	 */
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

	/**
	 * @param {Date} d
	 * @returns {string}
	 */
	function toISODate(d) {
		var y = d.getFullYear();
		var m = String(d.getMonth() + 1).padStart(2, '0');
		var day = String(d.getDate()).padStart(2, '0');
		return y + '-' + m + '-' + day;
	}

	/**
	 * @param {string} iso
	 * @returns {string}
	 */
	function formatDisplayDate(iso) {
		if (!iso) {
			return i18n.selectDate || 'Select date';
		}
		var parts = iso.split('-');
		if (parts.length !== 3) {
			return iso;
		}
		var months = i18n.months || [];
		var monthIdx = parseInt(parts[1], 10) - 1;
		var monthName = months[monthIdx] || parts[1];
		return parseInt(parts[2], 10) + ' ' + monthName + ' ' + parts[0];
	}

	/**
	 * @param {HTMLElement} root
	 */
	function initCalculator(root) {
		if (root.getAttribute('data-quote-ready') === '1') {
			return;
		}
		root.setAttribute('data-quote-ready', '1');

		var form = root.querySelector('[data-quote-form]');
		var titleEl = root.querySelector('[data-quote-title]');
		var panels = Array.prototype.slice.call(root.querySelectorAll('[data-quote-panel]'));
		var stepLabel = root.querySelector('[data-quote-step-label]');
		var nextBtn = root.querySelector('[data-quote-next]');
		var nextLabel = root.querySelector('[data-quote-next-label]');
		var nextIcon = root.querySelector('[data-quote-next-icon]');
		var spinner = root.querySelector('[data-quote-spinner]');
		var backBtn = root.querySelector('[data-quote-back]');
		var errorEl = root.querySelector('[data-quote-error]');
		var footer = root.querySelector('[data-quote-footer]');
		var dateDisplay = root.querySelector('[data-quote-date-display]');
		var calLabel = root.querySelector('[data-quote-cal-label]');
		var calGrid = root.querySelector('[data-quote-cal-grid]');
		var calWeekdays = root.querySelector('[data-quote-cal-weekdays]');
		var calPrev = root.querySelector('[data-quote-cal-prev]');
		var calNext = root.querySelector('[data-quote-cal-next]');
		var priceTotal = root.querySelector('[data-price-total]');
		var priceLive = root.querySelector('[data-price-live]');
		var hasStepped = false;

		var today = new Date();
		today.setHours(0, 0, 0, 0);

		var state = {
			step: 1,
			service: '',
			property: '',
			main_rooms: '1',
			bedrooms: '1',
			bathrooms: '1',
			linen_changes: '0',
			welcome_pack: 'no',
			addons: [],
			date: '',
			time: '',
			name: '',
			email: '',
			phone: '',
			comment: '',
			previewTotal: 0,
			quotedTotal: null,
			calYear: today.getFullYear(),
			calMonth: today.getMonth(),
			submitting: false,
		};

		function field(name) {
			return root.querySelector('[data-quote-field="' + name + '"]');
		}

		function fieldError(name) {
			return root.querySelector('[data-quote-field-error="' + name + '"]');
		}

		function readFields() {
			['service', 'property', 'main_rooms', 'bedrooms', 'bathrooms', 'linen_changes', 'date', 'time', 'name', 'email', 'phone', 'comment'].forEach(
				function (key) {
					var el = field(key);
					if (el) {
						state[key] = el.value;
					}
				}
			);
			var welcomeEl = root.querySelector('[data-quote-field="welcome_pack"]:checked');
			state.welcome_pack = welcomeEl ? welcomeEl.value : 'no';
			if (state.service === 'regular-cleaning') {
				state.bathrooms = state.bathrooms || '1';
			}
			state.previewTotal = getPreviewTotal(state);
		}

		function getVisibleSteps() {
			return serviceHasExtras(state.service) ? [1, 2, 3, 4, 5] : [1, 3, 4, 5];
		}

		function getNextStep(step) {
			var visible = getVisibleSteps();
			var idx = visible.indexOf(step);
			if (idx === -1) {
				if (step === 2 && !serviceHasExtras(state.service)) {
					return 3;
				}
				return Math.min(step + 1, TOTAL_STEPS);
			}
			if (idx >= visible.length - 1) {
				return visible[visible.length - 1];
			}
			return visible[idx + 1];
		}

		function getPrevStep(step) {
			var visible = getVisibleSteps();
			var idx = visible.indexOf(step);
			if (idx <= 0) {
				return 1;
			}
			return visible[idx - 1];
		}

		function renderAddons() {
			root.querySelectorAll('[data-quote-addon]').forEach(function (btn) {
				var key = btn.getAttribute('data-quote-addon');
				var on = state.addons.indexOf(key) !== -1;
				btn.classList.toggle('is-selected', on);
				btn.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
			var addonsField = field('addons');
			if (addonsField) {
				addonsField.value = state.addons.join(',');
			}
		}

		function syncRoomFields() {
			var fields = getRoomFieldsForService(state.service);
			var countersWrap = root.querySelector('[data-quote-counters]');
			var welcomeWrap = root.querySelector('[data-quote-welcome]');
			var hasExtras = serviceHasExtras(state.service);

			root.setAttribute('data-quote-has-extras', hasExtras ? '1' : '0');

			if (countersWrap) {
				fields.forEach(function (key) {
					var el = countersWrap.querySelector('[data-quote-counter="' + key + '"]');
					if (el) {
						countersWrap.appendChild(el);
					}
				});
			}

			root.querySelectorAll('[data-quote-counter]').forEach(function (wrap) {
				var key = wrap.getAttribute('data-quote-counter');
				var show = fields.indexOf(key) !== -1;
				wrap.hidden = !show;
				wrap.setAttribute('aria-hidden', show ? 'false' : 'true');

				var labelEl = wrap.querySelector('[data-quote-counter-label]');
				var input = wrap.querySelector('[data-quote-field="' + key + '"]');
				if (!labelEl || !input) {
					return;
				}

				if (state.service === 'regular-cleaning' && key === 'bedrooms') {
					labelEl.textContent = bedroomHomeLabel(input.value || state.bedrooms);
				} else if (state.service === 'airbnb-cleaning' && key === 'bedrooms') {
					labelEl.textContent = i18n.noOfBedrooms || 'No. of Bedrooms';
				} else if (state.service === 'airbnb-cleaning' && key === 'bathrooms') {
					labelEl.textContent = i18n.noOfBathrooms || 'No. of Bathrooms';
				} else if (key === 'main_rooms') {
					labelEl.textContent = i18n.mainRooms || 'Main rooms';
				} else if (key === 'bedrooms') {
					labelEl.textContent = i18n.bedrooms || 'Bedrooms';
				} else if (key === 'bathrooms') {
					labelEl.textContent = i18n.bathrooms || 'Bathrooms';
				} else if (key === 'linen_changes') {
					labelEl.textContent = i18n.linenChanges || 'No. of Linen Changes';
				}
			});

			if (welcomeWrap) {
				var showWelcome = state.service === 'airbnb-cleaning';
				welcomeWrap.hidden = !showWelcome;
				welcomeWrap.setAttribute('aria-hidden', showWelcome ? 'false' : 'true');
			}

			if (!hasExtras) {
				state.addons = [];
				renderAddons();
			}
		}

		function showError(msg) {
			if (!errorEl) {
				return;
			}
			if (!msg) {
				errorEl.hidden = true;
				errorEl.textContent = '';
				return;
			}
			errorEl.hidden = false;
			errorEl.textContent = msg;
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
			}
		}

		function clearFieldErrors() {
			['name', 'email', 'phone', 'time'].forEach(function (name) {
				setFieldError(name, '');
			});
		}

		function setLoading(loading) {
			if (!nextBtn) {
				return;
			}
			nextBtn.disabled = loading;
			nextBtn.classList.toggle('is-loading', loading);
			nextBtn.setAttribute('aria-busy', loading ? 'true' : 'false');
			if (spinner) {
				spinner.hidden = !loading;
			}
			if (nextIcon) {
				nextIcon.hidden = loading;
			}
			if (nextLabel && state.step === TOTAL_STEPS) {
				nextLabel.textContent = loading
					? i18n.submitting || 'Submitting…'
					: i18n.submitQuote || 'Submit Quote';
			}
		}

		function renderPrice() {
			var text = formatMoney(state.previewTotal);
			if (priceTotal) {
				priceTotal.textContent = text;
			}
			if (priceLive) {
				priceLive.textContent = (i18n.estimatedTotal || 'Estimated total') + ' ' + text;
			}
		}

		function renderWeekdays() {
			if (!calWeekdays) {
				return;
			}
			var days = i18n.weekdays || ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
			calWeekdays.innerHTML = '';
			days.forEach(function (d) {
				var el = document.createElement('span');
				el.className = 'quote-calculator__cal-weekday';
				el.textContent = d;
				calWeekdays.appendChild(el);
			});
		}

		function renderCalendar() {
			if (!calGrid || !calLabel) {
				return;
			}

			var months = i18n.months || [];
			calLabel.textContent = (months[state.calMonth] || '') + ' ' + state.calYear;

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

			cells.forEach(function (cell) {
				var iso = toISODate(cell.date);
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'quote-calculator__cal-day';
				btn.textContent = String(cell.day);
				btn.setAttribute('role', 'option');
				btn.setAttribute('data-date', iso);

				var past = cell.date < today;
				if (cell.outside) {
					btn.classList.add('is-outside');
				}
				if (past) {
					btn.classList.add('is-disabled');
					btn.disabled = true;
				}
				if (state.date === iso) {
					btn.classList.add('is-selected');
					btn.setAttribute('aria-selected', 'true');
				} else {
					btn.setAttribute('aria-selected', 'false');
				}

				if (!past) {
					btn.addEventListener('click', function () {
						state.date = iso;
						var dateField = field('date');
						if (dateField) {
							dateField.value = iso;
						}
						if (dateDisplay) {
							dateDisplay.value = formatDisplayDate(iso);
						}
						renderCalendar();
						showError('');
					});
				}

				calGrid.appendChild(btn);
			});
		}

		function renderSlots() {
			root.querySelectorAll('[data-quote-slot]').forEach(function (btn) {
				var val = btn.getAttribute('data-quote-slot');
				var selected = state.time === val;
				btn.classList.toggle('is-selected', selected);
				btn.setAttribute('aria-checked', selected ? 'true' : 'false');
			});
		}

		function setStep(step) {
			if (step === 2 && !serviceHasExtras(state.service)) {
				step = 3;
			}

			state.step = step;
			root.setAttribute('data-step', String(step));
			var isSuccess = step === SUCCESS_STEP;
			var visible = getVisibleSteps();
			var displayStep = Math.max(1, visible.indexOf(step) + 1);
			var totalVisible = visible.length;

			root.classList.toggle('quote-calculator--success', isSuccess);

			panels.forEach(function (panel) {
				var n = parseInt(panel.getAttribute('data-quote-step'), 10);
				panel.hidden = n !== step;
			});

			if (titleEl) {
				titleEl.hidden = isSuccess;
				if (!isSuccess) {
					titleEl.textContent =
						step === 3
							? i18n.titleDate || 'Get Your Date'
							: step === 2
								? i18n.titleExtras || 'Extra Services'
								: i18n.titleDefault || 'Get Your Instant Quote';
				}
			}

			if (stepLabel) {
				stepLabel.hidden = isSuccess;
				if (!isSuccess) {
					var tpl = i18n.stepOf || 'Step %1$d of %2$d';
					stepLabel.textContent = tpl
						.replace('%1$d', String(Math.min(displayStep, totalVisible)))
						.replace('%2$d', String(totalVisible));
				}
			}

			if (backBtn) {
				backBtn.hidden = isSuccess || step <= 1;
				backBtn.setAttribute('aria-hidden', isSuccess || step <= 1 ? 'true' : 'false');
			}

			if (nextBtn && nextLabel) {
				if (isSuccess) {
					nextLabel.textContent = i18n.close || 'Close';
					nextBtn.classList.add('quote-calculator__close');
				} else if (step === TOTAL_STEPS) {
					nextLabel.textContent = i18n.submitQuote || 'Submit Quote';
					nextBtn.classList.remove('quote-calculator__close');
				} else {
					nextLabel.textContent = i18n.nextStep || 'Next Step';
					nextBtn.classList.remove('quote-calculator__close');
				}
			}

			if (footer) {
				footer.classList.toggle('quote-calculator__footer--center', isSuccess);
			}

			if (step === 3) {
				renderCalendar();
			}
			if (step === 4) {
				renderSlots();
			}
			if (step === 5) {
				readFields();
				renderPrice();
			}

			clearFieldErrors();
			showError('');
			setLoading(false);

			scrollQuoteToTop();

			root.dispatchEvent(
				new CustomEvent('somvio:quote-step', {
					bubbles: true,
					detail: { step: step, root: root, hasExtras: serviceHasExtras(state.service) },
				})
			);
		}

		function scrollQuoteToTop() {
			if (state.step === SUCCESS_STEP) {
				return;
			}
			if (!hasStepped) {
				hasStepped = true;
				return;
			}
			var header = document.querySelector('.somvio-header, .site-header, header');
			var offset = 16;
			if (header) {
				offset += header.getBoundingClientRect().height || 0;
			}
			var rect = root.getBoundingClientRect();
			var top = window.pageYOffset + rect.top - offset;
			if (typeof window.scrollTo === 'function') {
				window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
			}
		}

		/**
		 * @returns {{ valid: boolean, firstInvalid: HTMLElement|null }}
		 */
		function validateContact() {
			readFields();
			clearFieldErrors();
			showError('');

			var nameVal = trim(state.name);
			var emailVal = trim(state.email);
			var phoneVal = normalizePhone(state.phone);
			var firstInvalid = null;
			var hasError = false;

			if (!isValidName(nameVal)) {
				setFieldError('name', i18n.invalidName || 'Please enter your full name.');
				firstInvalid = field('name');
				hasError = true;
			}

			if (!isValidEmail(emailVal)) {
				setFieldError('email', i18n.invalidEmail || 'Please enter a valid email address.');
				if (!firstInvalid) {
					firstInvalid = field('email');
				}
				hasError = true;
			}

			if (!isValidPhone(phoneVal)) {
				setFieldError('phone', i18n.invalidPhone || 'Please enter a valid phone number.');
				if (!firstInvalid) {
					firstInvalid = field('phone');
				}
				hasError = true;
			}

			if (hasError) {
				showError(i18n.required || 'Please complete the required fields.');
			}

			return { valid: !hasError, firstInvalid: firstInvalid };
		}

		/**
		 * @returns {boolean}
		 */
		function validateStep() {
			readFields();

			if (state.step === 1) {
				var rooms = getRoomFieldsForService(state.service);
				if (!state.service || !state.property) {
					showError(i18n.required || 'Please complete the required fields.');
					return false;
				}
				if (rooms.indexOf('bedrooms') !== -1 && !state.bedrooms) {
					showError(i18n.required || 'Please complete the required fields.');
					return false;
				}
				if (rooms.indexOf('bathrooms') !== -1 && !state.bathrooms) {
					showError(i18n.required || 'Please complete the required fields.');
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
					showError(i18n.required || 'Please complete the required fields.');
					return false;
				}
				return true;
			}

			if (state.step === 4) {
				clearFieldErrors();
				if (!state.time) {
					setFieldError('time', i18n.selectTime || 'Please select a time slot.');
					showError(i18n.selectTime || 'Please select a time slot.');
					return false;
				}
				return true;
			}

			if (state.step === 5) {
				var result = validateContact();
				if (!result.valid && result.firstInvalid) {
					result.firstInvalid.focus();
				}
				return result.valid;
			}

			return true;
		}

		/**
		 * @returns {Promise<void>}
		 */
		function submitQuote() {
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
			renderPrice();

			if (!cfg.restUrl || !cfg.nonce) {
				state.submitting = false;
				setLoading(false);
				showError(i18n.submitError || 'Something went wrong. Please try again.');
				return Promise.resolve();
			}

			var payload = {
				service: state.service,
				property: state.property,
				bedrooms: parseInt(state.bedrooms, 10) || 1,
				bathrooms: parseInt(state.bathrooms, 10) || 1,
				main_rooms: parseInt(state.main_rooms, 10) || 0,
				linen_changes: parseInt(state.linen_changes, 10) || 0,
				welcome_pack: state.welcome_pack === 'yes' ? 'yes' : 'no',
				addons: serviceHasExtras(state.service) ? state.addons.slice() : [],
				date: state.date,
				time: state.time,
				name: trim(state.name),
				email: trim(state.email),
				phone: normalizePhone(state.phone),
				comment: trim(state.comment),
				client_total: state.quotedTotal != null ? state.quotedTotal : state.previewTotal,
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
					return res.text().then(function (text) {
						var data = {};
						if (text) {
							try {
								data = JSON.parse(text);
							} catch (error) {
								data = {};
							}
						}
						return { ok: res.ok, status: res.status, data: data };
					});
				})
				.then(function (result) {
					if (result.status === 409 && result.data && result.data.data && result.data.data.total != null) {
						state.previewTotal = Number(result.data.data.total);
						state.quotedTotal = state.previewTotal;
						renderPrice();
						showError(result.data.message || i18n.submitError);
						return;
					}
					if (!result.ok || !result.data || !result.data.success) {
						var msg =
							(result.data && result.data.message) ||
							i18n.submitError ||
							'Something went wrong. Please try again.';
						showError(msg);
						return;
					}
					if (result.data && result.data.total != null) {
						state.previewTotal = Number(result.data.total);
						renderPrice();
					}
					setStep(SUCCESS_STEP);
					root.dispatchEvent(
						new CustomEvent('somvio:quote-success', {
							bubbles: true,
							detail: { total: state.previewTotal, root: root },
						})
					);
				})
				.catch(function () {
					showError(i18n.submitError || 'Something went wrong. Please try again.');
				})
				.finally(function () {
					state.submitting = false;
					setLoading(false);
				});
		}

		function resetCalculator(options) {
			var opts = options || {};
			state.step = 1;
			state.date = '';
			state.time = '';
			state.name = '';
			state.email = '';
			state.phone = '';
			state.comment = '';
			state.addons = [];
			state.welcome_pack = 'no';
			state.quotedTotal = null;
			state.calYear = today.getFullYear();
			state.calMonth = today.getMonth();

			['date', 'time', 'name', 'email', 'phone', 'comment', 'addons'].forEach(function (key) {
				var el = field(key);
				if (el) {
					el.value = '';
				}
			});
			root.querySelectorAll('[data-quote-field="welcome_pack"]').forEach(function (radio) {
				radio.checked = radio.value === 'no';
				var option = radio.closest('.quote-calculator__welcome-option');
				if (option) {
					option.classList.toggle('is-selected', radio.checked);
				}
			});
			if (dateDisplay) {
				dateDisplay.value = '';
			}

			renderSlots();
			renderAddons();
			syncRoomFields();
			clearFieldErrors();
			setStep(1);
			readFields();
			renderPrice();

			if (opts.emitClose !== false) {
				root.dispatchEvent(
					new CustomEvent('somvio:quote-close', {
						bubbles: true,
						detail: { root: root },
					})
				);
			}
		}

		['service', 'property'].forEach(function (key) {
			var el = field(key);
			if (!el) {
				return;
			}
			el.addEventListener('change', function () {
				state.quotedTotal = null;
				if (key === 'service') {
					state.service = el.value;
					if (!serviceHasExtras(state.service)) {
						state.addons = [];
					}
					syncRoomFields();
				}
				readFields();
				renderPrice();
			});
		});

		root.querySelectorAll('[data-quote-counter]').forEach(function (wrap) {
			var key = wrap.getAttribute('data-quote-counter');
			var input = wrap.querySelector('[data-quote-field="' + key + '"]');
			var dec = wrap.querySelector('[data-quote-counter-dec]');
			var inc = wrap.querySelector('[data-quote-counter-inc]');
			if (!input) {
				return;
			}
			var min = parseInt(input.getAttribute('min'), 10);
			var max = parseInt(input.getAttribute('max'), 10);
			if (isNaN(min)) {
				min = 0;
			}
			if (isNaN(max)) {
				max = 10;
			}

			function syncButtons(n) {
				if (dec) {
					dec.disabled = n <= min;
				}
				if (inc) {
					inc.disabled = n >= max;
				}
			}

			function setVal(n) {
				n = Math.max(min, Math.min(max, n));
				input.value = String(n);
				state[key] = String(n);
				state.quotedTotal = null;
				syncButtons(n);
				if (state.service === 'regular-cleaning' && key === 'bedrooms') {
					var labelEl = wrap.querySelector('[data-quote-counter-label]');
					if (labelEl) {
						labelEl.textContent = bedroomHomeLabel(n);
					}
				}
				readFields();
				renderPrice();
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
		});

		root.querySelectorAll('[data-quote-field="welcome_pack"]').forEach(function (radio) {
			radio.addEventListener('change', function () {
				if (!radio.checked) {
					return;
				}
				state.welcome_pack = radio.value;
				root.querySelectorAll('[data-quote-field="welcome_pack"]').forEach(function (el) {
					var option = el.closest('.quote-calculator__welcome-option');
					if (option) {
						option.classList.toggle('is-selected', !!el.checked);
					}
				});
				state.quotedTotal = null;
				readFields();
				renderPrice();
			});
		});

		root.querySelectorAll('[data-quote-addon]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var key = btn.getAttribute('data-quote-addon');
				var idx = state.addons.indexOf(key);
				if (idx === -1) {
					state.addons.push(key);
				} else {
					state.addons.splice(idx, 1);
				}
				state.quotedTotal = null;
				renderAddons();
				readFields();
				renderPrice();
			});
		});

		root.querySelectorAll('[data-quote-slot]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				state.time = btn.getAttribute('data-quote-slot') || '';
				var timeField = field('time');
				if (timeField) {
					timeField.value = state.time;
				}
				renderSlots();
				setFieldError('time', '');
				showError('');
			});
		});

		var phoneEl = field('phone');
		if (phoneEl) {
			phoneEl.addEventListener('input', function () {
				var cleaned = phoneEl.value.replace(/[^\d+\s]/g, '');
				if (cleaned.charAt(0) === '+') {
					cleaned = '+' + cleaned.slice(1).replace(/[^\d\s]/g, '');
				} else {
					cleaned = cleaned.replace(/[^\d\s]/g, '');
				}
				phoneEl.value = cleaned;
				setFieldError('phone', '');
			});
			phoneEl.addEventListener('blur', function () {
				phoneEl.value = formatPhoneDisplay(phoneEl.value);
			});
		}

		['name', 'email'].forEach(function (key) {
			var el = field(key);
			if (!el) {
				return;
			}
			el.addEventListener('input', function () {
				setFieldError(key, '');
			});
		});

		if (calPrev) {
			calPrev.addEventListener('click', function () {
				state.calMonth -= 1;
				if (state.calMonth < 0) {
					state.calMonth = 11;
					state.calYear -= 1;
				}
				renderCalendar();
			});
		}

		if (calNext) {
			calNext.addEventListener('click', function () {
				state.calMonth += 1;
				if (state.calMonth > 11) {
					state.calMonth = 0;
					state.calYear += 1;
				}
				renderCalendar();
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				if (state.submitting) {
					return;
				}
				if (state.step === SUCCESS_STEP) {
					resetCalculator();
					return;
				}
				if (!validateStep()) {
					return;
				}
				if (state.step === TOTAL_STEPS) {
					submitQuote();
					return;
				}
				setStep(getNextStep(state.step));
			});
		}

		if (backBtn) {
			backBtn.addEventListener('click', function () {
				if (state.step > 1 && state.step <= TOTAL_STEPS) {
					setStep(getPrevStep(state.step));
				}
			});
		}

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
			});
		}

		renderWeekdays();
		readFields();
		syncRoomFields();
		renderAddons();
		renderPrice();
		setStep(1);

		root.somvioQuoteReset = resetCalculator;
		root.somvioQuoteGoTo = setStep;
	}

	function initAll() {
		document.querySelectorAll('[data-quote-calculator]').forEach(initCalculator);
	}

	document.addEventListener('somvio:quote-mount', function (e) {
		var target = e.target;
		if (target && target.matches && target.matches('[data-quote-calculator]')) {
			initCalculator(target);
		} else if (e.detail && e.detail.root) {
			initCalculator(e.detail.root);
		}
	});

	function initQuoteModal() {
		var modal = document.querySelector('[data-quote-modal]');
		if (!modal) {
			return;
		}

		var lastFocus = null;
		var dialog = modal.querySelector('.quote-modal__dialog');
		var isolationRecords = [];

		function setModalIsolation(active) {
			if (active) {
				isolationRecords = [];
				var current = modal;
				while (current && current !== document.body) {
					var parent = current.parentElement;
					if (!parent) {
						break;
					}
					Array.prototype.forEach.call(parent.children, function (sibling) {
						if (sibling === current) {
							return;
						}
						isolationRecords.push({
							element: sibling,
							hadInert: sibling.hasAttribute('inert'),
							hadAriaHidden: sibling.hasAttribute('aria-hidden'),
							ariaHidden: sibling.getAttribute('aria-hidden'),
						});
						sibling.setAttribute('inert', '');
						sibling.setAttribute('aria-hidden', 'true');
					});
					current = parent;
				}
				return;
			}

			isolationRecords.forEach(function (record) {
				if (!record.hadInert) {
					record.element.removeAttribute('inert');
				}
				if (record.hadAriaHidden) {
					record.element.setAttribute('aria-hidden', record.ariaHidden);
				} else {
					record.element.removeAttribute('aria-hidden');
				}
			});
			isolationRecords = [];
		}

		function getFocusable() {
			return Array.prototype.slice
				.call(
					modal.querySelectorAll(
						'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
					)
				)
				.filter(function (element) {
					return !element.hidden && element.offsetParent !== null;
				});
		}

		function openModal(e) {
			if (e) {
				e.preventDefault();
			}
			lastFocus = document.activeElement;
			modal.hidden = false;
			modal.setAttribute('aria-hidden', 'false');
			document.documentElement.classList.add('has-quote-modal');
			var focusable = getFocusable();
			var focusTarget = focusable[0] || dialog;
			if (focusTarget && typeof focusTarget.focus === 'function') {
				focusTarget.focus();
			}
			setModalIsolation(true);
		}

		function closeModal(options) {
			var opts = options || {};
			setModalIsolation(false);
			modal.hidden = true;
			modal.setAttribute('aria-hidden', 'true');
			document.documentElement.classList.remove('has-quote-modal');
			if (opts.skipReset !== true) {
				var calc = modal.querySelector('[data-quote-calculator]');
				if (calc && typeof calc.somvioQuoteReset === 'function') {
					calc.somvioQuoteReset({ emitClose: false });
				}
			}
			if (lastFocus && document.contains(lastFocus) && typeof lastFocus.focus === 'function') {
				lastFocus.focus();
			}
			lastFocus = null;
		}

		document.querySelectorAll('[data-quote-modal-open]').forEach(function (trigger) {
			trigger.addEventListener('click', openModal);
		});

		modal.querySelectorAll('[data-quote-modal-close]').forEach(function (el) {
			el.addEventListener('click', function () {
				closeModal();
			});
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !modal.hidden) {
				closeModal();
				return;
			}
			if (e.key !== 'Tab' || modal.hidden) {
				return;
			}
			var focusable = getFocusable();
			if (!focusable.length) {
				e.preventDefault();
				if (dialog) {
					dialog.focus();
				}
				return;
			}
			var first = focusable[0];
			var last = focusable[focusable.length - 1];
			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
		});

		modal.addEventListener('somvio:quote-close', function () {
			if (!modal.hidden) {
				closeModal({ skipReset: true });
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initAll();
			initQuoteModal();
		});
	} else {
		initAll();
		initQuoteModal();
	}
})();
