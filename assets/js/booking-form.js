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
	var PHONE_RE = /^(\+?[1-9]\d{9,14}|0[1-9]\d{9,10})$/;

	function formatMoney(n) {
		return (rates.symbol || '£') + Number(n).toFixed(2);
	}

	function trim(value) {
		return String(value || '').trim();
	}

	function normalizePhone(phone) {
		var raw = trim(phone);
		var hasPlus = raw.charAt(0) === '+';
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

	function parseISODate(iso) {
		var match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(iso || ''));
		if (!match) {
			return null;
		}
		var date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
		if (
			date.getFullYear() !== Number(match[1]) ||
			date.getMonth() !== Number(match[2]) - 1 ||
			date.getDate() !== Number(match[3])
		) {
			return null;
		}
		date.setHours(0, 0, 0, 0);
		return date;
	}

	function formatDisplayDate(iso) {
		if (!iso) {
			return i18n.selectDatePlaceholder || 'Select date';
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
		var value = String(slot || '');
		if (value.indexOf('-') !== -1) {
			value = value.split('-')[0];
		}
		return value.trim();
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
		return [];
	}

	function bedroomHomeLabel(n, i18nLabels) {
		var count = parseInt(n, 10) || 1;
		var tpl = (i18nLabels && i18nLabels.bedroomHome) || '%d Bedroom Home';
		return tpl.replace('%d', String(count));
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
		var formEl = root.querySelector('[data-booking-form-el]');
		var successModal = root.querySelector('[data-booking-success-modal]');
		var successDialog = root.querySelector('.booking-form__success-dialog');
		var previousFocus = null;
		var isolationRecords = [];
		var hasStepped = false;

		var today = new Date();
		today.setHours(0, 0, 0, 0);

		var state = {
			step: 1,
			service: '',
			property: 'house',
			main_rooms: '1',
			bedrooms: '1',
			bathrooms: '1',
			linen_changes: '0',
			welcome_pack: 'no',
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
			payment_method: 'online',
			previewTotal: 0,
			quotedTotal: null,
			confirmedTotal: null,
			calYear: today.getFullYear(),
			calMonth: today.getMonth(),
			submitting: false,
			serviceBtn: null,
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
					var termsWrap = inputEl.closest('[data-booking-terms-wrap]') || inputEl.closest('.booking-form__terms-wrap');
					if (termsLabel) {
						termsLabel.classList.toggle('is-invalid', !!msg);
					}
					if (termsWrap) {
						termsWrap.classList.toggle('is-invalid', !!msg);
						if (!msg) {
							termsWrap.classList.remove('is-attention');
						}
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
				'payment_method',
			].forEach(function (name) {
				setFieldError(name, '');
			});
		}

		/**
		 * Scroll to an invalid field without jumping to the step top.
		 * Terms use a visually-hidden checkbox — scroll the visible label instead.
		 *
		 * @param {string} key
		 * @param {HTMLElement|null} el
		 * @returns {void}
		 */
		function scrollToFieldError(key, el) {
			var header = document.querySelector('.somvio-header, .site-header, header');
			var offset = 16;
			if (header) {
				offset += header.getBoundingClientRect().height || 0;
			}

			var target = el;
			if (key === 'terms_accepted' && el) {
				target =
					el.closest('[data-booking-terms-wrap]') ||
					el.closest('.booking-form__terms-wrap') ||
					el.closest('.booking-form__terms') ||
					el;
			} else if (key === 'payment_method') {
				target =
					root.querySelector('.booking-form__payment') ||
					(el && el.closest('.booking-form__payment-options')) ||
					el;
			}

			if (!target) {
				return;
			}

			target.style.scrollMarginTop = offset + 'px';
			if (typeof target.scrollIntoView === 'function') {
				target.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}

			if (key === 'terms_accepted' && target.classList) {
				target.classList.remove('is-attention');
				/* Retrigger highlight animation. */
				void target.offsetWidth;
				target.classList.add('is-attention');
			}

			if (el && typeof el.focus === 'function') {
				try {
					el.focus({ preventScroll: true });
				} catch (err) {
					el.focus();
				}
			}
		}

		function readFields() {
			['service', 'main_rooms', 'bedrooms', 'bathrooms', 'linen_changes', 'date', 'time', 'first_name', 'last_name', 'email', 'phone', 'address', 'comment'].forEach(
				function (key) {
					var el = field(key);
					if (el) {
						state[key] = el.value;
					}
				}
			);
			var welcomeEl = root.querySelector('[data-booking-field="welcome_pack"]:checked');
			state.welcome_pack = welcomeEl ? welcomeEl.value : 'no';
			var termsEl = field('terms_accepted');
			state.terms_accepted = !!(termsEl && termsEl.checked);
			var paymentEl = root.querySelector('[data-booking-field="payment_method"]:checked');
			state.payment_method = paymentEl && paymentEl.value === 'online' ? 'online' : 'cash';
			state.previewTotal = getPreviewTotal(state);
		}

		function syncState() {
			readFields();
			renderPrice();
		}

		function renderPrice() {
			var amount =
				state.confirmedTotal != null
					? state.confirmedTotal
					: state.quotedTotal != null
						? state.quotedTotal
						: state.previewTotal;
			var text = formatMoney(amount);
			root.querySelectorAll('[data-booking-total]').forEach(function (el) {
				el.textContent = text;
			});
			root.querySelectorAll('[data-booking-price-live]').forEach(function (el) {
				el.textContent = (i18n.totalPrice || 'Total Price') + ' ' + text;
			});
		}

		function invalidateServerQuote() {
			state.quotedTotal = null;
			state.confirmedTotal = null;
		}

		function renderServiceCards() {
			var buttons = root.querySelectorAll('[data-booking-service]');
			var matched = null;
			buttons.forEach(function (btn) {
				var val = btn.getAttribute('data-booking-service');
				if (!!state.service && state.service === val) {
					if (state.serviceBtn === btn || (!state.serviceBtn && !matched)) {
						matched = btn;
					}
				}
			});
			if (matched) {
				state.serviceBtn = matched;
			} else if (state.serviceBtn && state.serviceBtn.getAttribute('data-booking-service') !== state.service) {
				state.serviceBtn = null;
			}

			buttons.forEach(function (btn, index) {
				var selected = state.serviceBtn === btn;
				btn.classList.toggle('is-selected', selected);
				btn.setAttribute('aria-checked', selected ? 'true' : 'false');
				btn.tabIndex = selected || (!state.service && index === 0) ? 0 : -1;
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
				(state.payment_method === 'cash' || state.payment_method === 'online')
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
					: i18n.termsRequired ||
							'You must accept the Terms & Conditions and Privacy Policy to complete your booking.';
			}
			if (key === 'payment_method') {
				if (state.payment_method === 'online' && !cfg.stripeEnabled) {
					return (
						i18n.stripeKeysMissing ||
						i18n.onlinePaymentUnavailable ||
						'Stripe API keys are missing. Cannot process online payment.'
					);
				}
				return state.payment_method === 'cash' || state.payment_method === 'online'
					? ''
					: i18n.selectPayment || 'Please select a payment method.';
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

		function buildSuccessDetail(extra) {
			var serviceLabel = (services && services[state.service]) || state.service || '';
			var detail = {
				service: serviceLabel,
				date: formatDisplayDate(state.date),
				time: formatSlot(state.time),
				name: (trim(state.first_name) + ' ' + trim(state.last_name)).trim(),
				phone: trim(state.phone),
				email: trim(state.email),
				address: trim(state.address),
				total: formatMoney(state.confirmedTotal != null ? state.confirmedTotal : state.previewTotal),
				booking_id: 0,
				payment_method: state.payment_method || '',
				requires_payment: false,
				deferRedirect: false,
			};
			if (extra && typeof extra === 'object') {
				Object.keys(extra).forEach(function (key) {
					detail[key] = extra[key];
				});
			}
			return detail;
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
				total: formatMoney(state.confirmedTotal != null ? state.confirmedTotal : state.previewTotal),
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
			var firstEnabledAssigned = false;
			root.querySelectorAll('[data-booking-slot]').forEach(function (btn) {
				var val = btn.getAttribute('data-booking-slot');
				var selected = state.time === val;
				btn.classList.toggle('is-selected', selected);
				btn.setAttribute('aria-checked', selected ? 'true' : 'false');
				btn.disabled = !hasDate;
				btn.tabIndex = selected || (hasDate && !state.time && !firstEnabledAssigned) ? 0 : -1;
				if (hasDate && !state.time && !firstEnabledAssigned) {
					firstEnabledAssigned = true;
				}
			});
			var slotsEl = root.querySelector('[data-booking-slots]');
			if (slotsEl) {
				slotsEl.classList.toggle('is-disabled', !hasDate);
				slotsEl.setAttribute('aria-disabled', hasDate ? 'false' : 'true');
			}
		}

		function setupRadioKeyboard(groupSelector, itemSelector) {
			var group = root.querySelector(groupSelector);
			if (!group) {
				return;
			}
			group.addEventListener('keydown', function (event) {
				if (
					event.key !== 'ArrowLeft' &&
					event.key !== 'ArrowRight' &&
					event.key !== 'ArrowUp' &&
					event.key !== 'ArrowDown' &&
					event.key !== 'Home' &&
					event.key !== 'End'
				) {
					return;
				}
				var buttons = Array.prototype.slice
					.call(group.querySelectorAll(itemSelector))
					.filter(function (button) {
						return !button.disabled;
					});
				if (!buttons.length) {
					return;
				}
				var currentIndex = buttons.indexOf(document.activeElement);
				var nextIndex = currentIndex < 0 ? 0 : currentIndex;
				if (event.key === 'Home') {
					nextIndex = 0;
				} else if (event.key === 'End') {
					nextIndex = buttons.length - 1;
				} else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
					nextIndex = (nextIndex - 1 + buttons.length) % buttons.length;
				} else {
					nextIndex = (nextIndex + 1) % buttons.length;
				}
				event.preventDefault();
				buttons[nextIndex].focus();
				buttons[nextIndex].click();
			});
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
				btn.setAttribute('data-date', iso);
				btn.setAttribute('aria-label', formatDisplayDate(iso));

				var past = isPastDate(cell.date);
				if (iso === toISODate(today)) {
					btn.setAttribute('aria-current', 'date');
				}
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
					btn.setAttribute('aria-pressed', 'true');
				} else {
					btn.setAttribute('aria-pressed', 'false');
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
						setCalendarOpen(false, true);
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

		function setCalendarOpen(open, restoreFocus) {
			if (!calendarEl) {
				return;
			}
			var wasOpen = !calendarEl.hidden;
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
					requestAnimationFrame(function () {
						refreshCalendarPaint();
						var target =
							calGrid &&
							(calGrid.querySelector('.booking-form__cal-day.is-selected:not(:disabled)') ||
								calGrid.querySelector('.booking-form__cal-day:not(:disabled)'));
						if (target) {
							target.focus();
						}
					});
				});
			} else if (wasOpen && restoreFocus !== false && dateToggle && typeof dateToggle.focus === 'function') {
				dateToggle.focus();
			}
		}

		/**
		 * Format "Step X of Y" for the footer label.
		 * Supports {current}/{total} and legacy %1$d/%2$d / %d placeholders.
		 *
		 * @param {number} current Display step in the active sequence (1-based).
		 * @param {number} total   Length of the active sequence (3 or 4).
		 * @returns {string}
		 */
		function formatStepOf(current, total) {
			var tpl = (i18n && i18n.stepOf) || 'Step {current} of {total}';
			var cur = String(current);
			var tot = String(total);
			if (tpl.indexOf('{current}') !== -1 || tpl.indexOf('{total}') !== -1) {
				return tpl.replace(/\{current\}/g, cur).replace(/\{total\}/g, tot);
			}
			/* Legacy gettext-style placeholders from older localize strings. */
			if (/%1\$?d/.test(tpl) || /%2\$?d/.test(tpl)) {
				return tpl
					.replace(/%1\$d/g, cur)
					.replace(/%2\$d/g, tot)
					.replace(/%1\$s/g, cur)
					.replace(/%2\$s/g, tot)
					.replace(/%1d/g, cur)
					.replace(/%2d/g, tot);
			}
			var once = 0;
			return tpl.replace(/%d/g, function () {
				once += 1;
				return once === 1 ? cur : tot;
			});
		}

		function updateStepLabels(step) {
			var visible = getVisibleSteps();
			var idx = visible.indexOf(step);
			/* Remap to the visible sequence (e.g. 1,3,4 → 1,2,3 of 3). */
			var displayStep = idx === -1 ? 1 : idx + 1;
			var total = Math.max(1, visible.length);
			var label = formatStepOf(displayStep, total);

			root.querySelectorAll('[data-booking-step-label]').forEach(function (el) {
				el.hidden = step === SUCCESS_STEP;
				if (step !== SUCCESS_STEP) {
					el.textContent = label;
				}
			});
			updateStepNumbers(visible);
			updateStepper(step, visible);
		}

		function updateStepNumbers(visible) {
			var list = visible || getVisibleSteps();
			panels.forEach(function (panel) {
				var n = parseInt(panel.getAttribute('data-booking-step'), 10);
				var numEl = panel.querySelector('[data-booking-step-num]');
				if (!numEl) {
					return;
				}
				var idx = list.indexOf(n);
				numEl.textContent = idx === -1 ? '' : String(idx + 1) + '.';
			});
		}

		function updateStepper(step, visible) {
			var list = visible || getVisibleSteps();
			var hasExtras = serviceHasExtras(state.service);
			var extrasTab = root.querySelector('[data-booking-extras-tab]');
			var stepper = root.querySelector('[data-booking-stepper]');

			root.setAttribute('data-booking-has-extras', hasExtras ? '1' : '0');
			root.classList.toggle('booking-form--has-extras', hasExtras);
			root.classList.toggle('booking-form--no-extras', !!state.service && !hasExtras);

			if (stepper) {
				stepper.hidden = step === SUCCESS_STEP;
				stepper.setAttribute('aria-hidden', step === SUCCESS_STEP ? 'true' : 'false');
			}

			if (extrasTab) {
				extrasTab.hidden = !hasExtras;
				extrasTab.setAttribute('aria-hidden', hasExtras ? 'false' : 'true');
			}

			root.querySelectorAll('[data-booking-step-item]').forEach(function (item) {
				var n = parseInt(item.getAttribute('data-booking-step-item'), 10);
				var btn = item.querySelector('[data-booking-step-tab]');
				var indexEl = item.querySelector('[data-booking-step-index]');
				var idx = list.indexOf(n);
				var isVisible = idx !== -1;
				var currentIdx = list.indexOf(step);
				var isCurrent = n === step;
				var isDone = isVisible && currentIdx !== -1 && idx < currentIdx;

				if (n === 2 && !hasExtras) {
					item.hidden = true;
					item.setAttribute('aria-hidden', 'true');
					if (btn) {
						btn.disabled = true;
						btn.setAttribute('aria-disabled', 'true');
						btn.removeAttribute('aria-current');
						btn.tabIndex = -1;
					}
					return;
				}

				item.hidden = false;
				item.setAttribute('aria-hidden', 'false');
				item.classList.toggle('is-current', isCurrent);
				item.classList.toggle('is-done', isDone);

				if (indexEl && isVisible) {
					indexEl.textContent = String(idx + 1);
				}

				if (btn) {
					/* Allow jump only to current or earlier visible steps; hard-block extras when skipped. */
					var canJump = isVisible && currentIdx !== -1 && idx <= currentIdx;
					btn.disabled = !canJump;
					btn.setAttribute('aria-disabled', canJump ? 'false' : 'true');
					btn.tabIndex = canJump ? 0 : -1;
					if (isCurrent) {
						btn.setAttribute('aria-current', 'step');
					} else {
						btn.removeAttribute('aria-current');
					}
				}
			});
		}

		function getVisibleSteps() {
			return serviceHasExtras(state.service) ? [1, 2, 3, 4] : [1, 3, 4];
		}

		function getNextStep(step) {
			var visible = getVisibleSteps();
			var idx = visible.indexOf(step);
			if (idx === -1) {
				/* Recover if somehow on a hidden step (e.g. step 2 without extras). */
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

		function clearAddonsIfNeeded() {
			if (!serviceHasExtras(state.service)) {
				if (state.addons.length) {
					state.addons = [];
					invalidateServerQuote();
				}
				renderAddons();
			}
		}

		function syncRoomFields() {
			var fields = getRoomFieldsForService(state.service);
			var countersWrap = root.querySelector('[data-booking-counters]');
			var welcomeWrap = root.querySelector('[data-booking-welcome]');
			var showCounters = !!state.service && fields.length > 0;
			var hasExtras = serviceHasExtras(state.service);

			root.setAttribute('data-booking-has-extras', hasExtras ? '1' : '0');
			root.classList.toggle('booking-form--has-extras', hasExtras);
			root.classList.toggle('booking-form--no-extras', !!state.service && !hasExtras);

			if (countersWrap) {
				countersWrap.hidden = !showCounters;
				/* Keep DOM order aligned with the active service field list. */
				fields.forEach(function (key) {
					var el = countersWrap.querySelector('[data-booking-counter="' + key + '"]');
					if (el) {
						countersWrap.appendChild(el);
					}
				});
			}

			root.querySelectorAll('[data-booking-counter]').forEach(function (wrap) {
				var key = wrap.getAttribute('data-booking-counter');
				var show = fields.indexOf(key) !== -1;
				wrap.hidden = !show;
				wrap.setAttribute('aria-hidden', show ? 'false' : 'true');

				var labelEl = wrap.querySelector('[data-booking-counter-label]');
				var input = wrap.querySelector('[data-booking-field="' + key + '"]');
				if (!labelEl || !input) {
					return;
				}

				if (state.service === 'regular-cleaning' && key === 'bedrooms') {
					labelEl.textContent = bedroomHomeLabel(input.value || state.bedrooms, i18n);
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
				root.querySelectorAll('[data-booking-field="welcome_pack"]').forEach(function (radio) {
					var option = radio.closest('.booking-form__welcome-option');
					if (option) {
						option.classList.toggle('is-selected', !!radio.checked);
					}
				});
			}

			if (!hasExtras) {
				clearAddonsIfNeeded();
			}

			updateStepper(state.step, getVisibleSteps());
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
				if (nextLabel && state.step === TOTAL_STEPS) {
					nextLabel.textContent = loading
						? i18n.submitting || 'Submitting…'
						: i18n.complete || 'Complete Booking';
				}
			});
			updateNextAvailability();
		}

		function setModalIsolation(active) {
			if (active) {
				isolationRecords = [];
				var current = successModal;
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

		function getModalFocusable() {
			if (!successModal) {
				return [];
			}
			return Array.prototype.slice
				.call(
					successModal.querySelectorAll(
						'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
					)
				)
				.filter(function (element) {
					return !element.hidden && element.getAttribute('aria-hidden') !== 'true';
				});
		}

		function closeSuccessModal() {
			root.classList.remove('is-success');
			document.body.classList.remove('booking-form-modal-open');
			setModalIsolation(false);
			if (!successModal) {
				return;
			}
			successModal.classList.remove('is-open');
			successModal.hidden = true;
			successModal.setAttribute('aria-hidden', 'true');
			if (previousFocus && document.contains(previousFocus) && typeof previousFocus.focus === 'function') {
				previousFocus.focus();
			}
			previousFocus = null;
		}

		function openSuccessModal() {
			state.step = SUCCESS_STEP;
			state.submitting = false;
			root.setAttribute('data-step', String(SUCCESS_STEP));
			root.classList.add('is-success');
			renderSuccessRecap();
			setLoading(false);
			updateStepLabels(SUCCESS_STEP);
			updateNextAvailability();

			if (successModal) {
				previousFocus = document.activeElement;
				successModal.hidden = false;
				successModal.setAttribute('aria-hidden', 'false');
				/* Force reflow so open animation restarts */
				void successModal.offsetWidth;
				successModal.classList.add('is-open');
				document.body.classList.add('booking-form-modal-open');
				var focusable = getModalFocusable();
				var focusTarget = focusable[0] || successDialog;
				if (focusTarget && typeof focusTarget.focus === 'function') {
					focusTarget.focus();
				}
				setModalIsolation(true);
			}

			root.dispatchEvent(
				new CustomEvent('somvio:booking-step', {
					bubbles: true,
					detail: { step: SUCCESS_STEP },
				})
			);
		}

		function scrollFormToTop() {
			if (!hasStepped) {
				hasStepped = true;
				return;
			}
			var header = document.querySelector('.somvio-header, .site-header, header');
			var offset = 16;
			if (header) {
				offset += header.getBoundingClientRect().height || 0;
			}
			var target =
				root.querySelector('[data-booking-step="' + state.step + '"]') ||
				root.querySelector('.booking-form__stepper') ||
				root;
			if (target && typeof target.scrollIntoView === 'function') {
				target.style.scrollMarginTop = offset + 'px';
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		}

		function setStep(step) {
			if (step === SUCCESS_STEP) {
				openSuccessModal();
				return;
			}

			/* Never land on Extra Services when the selected service skips them. */
			if (step === 2 && !serviceHasExtras(state.service)) {
				step = 3;
			}

			closeSuccessModal();
			state.step = step;
			root.setAttribute('data-step', String(step));
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
				setCalendarOpen(false, false);
				updateSlotsAvailability();
			}

			scrollFormToTop();

			root.dispatchEvent(
				new CustomEvent('somvio:booking-step', {
					bubbles: true,
					detail: { step: step, hasExtras: serviceHasExtras(state.service) },
				})
			);
		}

		function validateContact() {
			readFields();
			clearFieldErrors();
			showError('');

			var firstInvalid = null;
			var firstInvalidKey = '';
			var hasError = false;
			['first_name', 'last_name', 'phone', 'email', 'address', 'payment_method', 'terms_accepted'].forEach(function (key) {
				var msg = getContactFieldError(key);
				if (msg) {
					setFieldError(key, msg);
					if (!firstInvalid) {
						firstInvalid = field(key) || root.querySelector('[data-booking-field="' + key + '"]');
						firstInvalidKey = key;
					}
					hasError = true;
				}
			});

			if (hasError) {
				if (firstInvalidKey === 'terms_accepted') {
					showError(
						i18n.termsRequired ||
							'You must accept the Terms & Conditions and Privacy Policy to complete your booking.'
					);
				} else if (firstInvalidKey === 'payment_method') {
					var payMsg = getContactFieldError('payment_method');
					showError(
						payMsg ||
							i18n.selectPayment ||
							'Please select a payment method.'
					);
				} else {
					showError(i18n.required || 'Please complete the required fields.');
				}
			}

			return { valid: !hasError, firstInvalid: firstInvalid, firstInvalidKey: firstInvalidKey };
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
				var selectedDay = parseISODate(state.date);
				if (!selectedDay || isPastDate(selectedDay)) {
					state.date = '';
					state.time = '';
					var invalidDateField = field('date');
					var invalidTimeField = field('time');
					if (invalidDateField) {
						invalidDateField.value = '';
					}
					if (invalidTimeField) {
						invalidTimeField.value = '';
					}
					if (dateDisplay) {
						dateDisplay.value = '';
					}
					setFieldError('date', i18n.selectDate || 'Please select a date.');
					showError(i18n.selectDate || 'Please select a date.');
					updateSlotsAvailability();
					updateNextAvailability();
					return false;
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
				if (!result.valid) {
					scrollToFieldError(result.firstInvalidKey, result.firstInvalid);
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
				scrollToFieldError(validation.firstInvalidKey, validation.firstInvalid);
				return Promise.resolve();
			}

			readFields();
			syncState();

			if (state.payment_method === 'online' && !cfg.stripeEnabled) {
				var stripeMsg =
					i18n.stripeKeysMissing ||
					i18n.onlinePaymentUnavailable ||
					'Stripe API keys are missing. Cannot process online payment.';
				setFieldError('payment_method', stripeMsg);
				showError(stripeMsg);
				scrollToFieldError(
					'payment_method',
					root.querySelector('[data-booking-field="payment_method"]:checked') ||
						root.querySelector('[data-booking-field="payment_method"]')
				);
				return Promise.resolve();
			}

			state.submitting = true;
			setLoading(true);

			if (!cfg.restUrl || !cfg.nonce) {
				state.submitting = false;
				setLoading(false);
				showError(i18n.submitError || 'Something went wrong. Please try again.');
				return Promise.resolve();
			}

			var payload = {
				service: state.service,
				property: state.property || 'house',
				bedrooms: parseInt(state.bedrooms, 10) || 1,
				bathrooms: parseInt(state.bathrooms, 10) || 1,
				main_rooms: parseInt(state.main_rooms, 10) || 0,
				linen_changes: parseInt(state.linen_changes, 10) || 0,
				welcome_pack: state.welcome_pack === 'yes' ? 'yes' : 'no',
				date: state.date,
				time: state.time,
				first_name: trim(state.first_name),
				last_name: trim(state.last_name),
				name: trim(state.first_name) + ' ' + trim(state.last_name),
				email: trim(state.email),
				phone: normalizePhone(state.phone),
				address: trim(state.address),
				comment: trim(state.comment),
				addons: serviceHasExtras(state.service) ? state.addons.slice() : [],
				terms_accepted: true,
				payment_method: state.payment_method === 'online' ? 'online' : 'cash',
				source: 'booking',
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
					state.submitting = false;
					setLoading(false);

					if (result.status === 409 && result.data && result.data.data && result.data.data.total != null) {
						state.previewTotal = Number(result.data.data.total);
						state.quotedTotal = state.previewTotal;
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
						state.confirmedTotal = state.previewTotal;
					}

					var needsPayment = !!(
						result.data.requires_payment &&
						result.data.payment &&
						result.data.payment.client_secret
					);

					var successDetail = buildSuccessDetail({
						total: formatMoney(state.previewTotal),
						booking_id: result.data.booking_id || 0,
						payment_method: result.data.payment_method || state.payment_method,
						requires_payment: needsPayment,
						deferRedirect: needsPayment,
						message: result.data.message || '',
					});

					if (needsPayment) {
						openSuccessModal();
					}

					root.dispatchEvent(
						new CustomEvent('somvio:booking-success', {
							bubbles: true,
							detail: successDetail,
						})
					);

					if (needsPayment) {
						initStripePayment(result.data);
					} else if (result.data.payment_error) {
						openSuccessModal();
						showStripeError(
							result.data.message ||
								i18n.paymentError ||
								'Payment could not be completed. Please try again.'
						);
					}
				})
				.catch(function () {
					state.submitting = false;
					setLoading(false);
					showError(i18n.submitError || 'Something went wrong. Please try again.');
				});
		}

		function showStripeError(msg) {
			var errEl = root.querySelector('[data-booking-stripe-error]');
			var wrap = root.querySelector('[data-booking-stripe]');
			if (wrap) {
				wrap.hidden = false;
			}
			if (!errEl) {
				return;
			}
			if (!msg) {
				errEl.hidden = true;
				errEl.textContent = '';
				return;
			}
			errEl.hidden = false;
			errEl.textContent = msg;
		}

		function loadStripeJs() {
			if (window.Stripe) {
				return Promise.resolve(window.Stripe);
			}
			return new Promise(function (resolve, reject) {
				var existing = document.querySelector('script[data-somvio-stripe]');
				if (existing) {
					// Script tag already present — may have finished loading before listeners attach.
					if (window.Stripe) {
						resolve(window.Stripe);
						return;
					}
					var settled = false;
					function onReady() {
						if (settled) {
							return;
						}
						settled = true;
						if (window.Stripe) {
							resolve(window.Stripe);
						} else {
							reject(new Error('stripe_unavailable'));
						}
					}
					existing.addEventListener('load', onReady);
					existing.addEventListener('error', function () {
						if (settled) {
							return;
						}
						settled = true;
						reject(new Error('stripe_load_failed'));
					});
					// If load already fired, poll briefly then fail closed.
					var attempts = 0;
					var poll = setInterval(function () {
						attempts += 1;
						if (window.Stripe) {
							clearInterval(poll);
							onReady();
						} else if (attempts >= 40) {
							clearInterval(poll);
							if (!settled) {
								settled = true;
								reject(new Error('stripe_load_timeout'));
							}
						}
					}, 50);
					return;
				}
				var script = document.createElement('script');
				script.src = 'https://js.stripe.com/v3/';
				script.async = true;
				script.setAttribute('data-somvio-stripe', '1');
				script.onload = function () {
					resolve(window.Stripe);
				};
				script.onerror = reject;
				document.head.appendChild(script);
			});
		}

		function initStripePayment(data) {
			var wrap = root.querySelector('[data-booking-stripe]');
			var mountEl = root.querySelector('[data-booking-stripe-element]');
			var payBtn = root.querySelector('[data-booking-stripe-pay]');
			if (!wrap || !mountEl || !data.payment) {
				return;
			}

			wrap.hidden = false;
			showStripeError('');

			var pubKey = data.payment.publishable_key || cfg.stripePublishableKey || '';
			var clientSecret = data.payment.client_secret;
			var intentId = data.payment.payment_intent_id || '';
			var bookingId = data.booking_id || 0;
			var mounted = false;

			function mountPaymentElement() {
				if (mounted) {
					return;
				}
				mounted = true;

				loadStripeJs()
					.then(function (StripeFactory) {
						if (!StripeFactory || !pubKey) {
							throw new Error('stripe_unavailable');
						}
						var stripe = StripeFactory(pubKey);
						var elements = stripe.elements({
							clientSecret: clientSecret,
							appearance: {
								theme: 'stripe',
								variables: {
									colorPrimary: '#40d7d0',
									borderRadius: '4px',
								},
							},
						});
						var paymentElement = elements.create('payment');
						mountEl.innerHTML = '';
						paymentElement.mount(mountEl);

						if (!payBtn) {
							return;
						}

						payBtn.onclick = function () {
							if (payBtn.disabled) {
								return;
							}
							payBtn.disabled = true;
							var label = payBtn.querySelector('.btn__label');
							if (label) {
								label.textContent = i18n.paying || 'Processing payment…';
							}
							showStripeError('');

							stripe
								.confirmPayment({
									elements: elements,
									redirect: 'if_required',
									confirmParams: {
										return_url: window.location.href,
									},
								})
								.then(function (result) {
									if (result.error) {
										payBtn.disabled = false;
										if (label) {
											label.textContent = i18n.complete || 'Pay now';
										}
										showStripeError(result.error.message || i18n.paymentError);
										return;
									}

									return fetch(cfg.confirmPaymentUrl || '', {
										method: 'POST',
										credentials: 'same-origin',
										headers: {
											'Content-Type': 'application/json',
											'X-WP-Nonce': cfg.nonce || '',
										},
										body: JSON.stringify({
											payment_intent_id:
												intentId || (result.paymentIntent && result.paymentIntent.id) || '',
											booking_id: bookingId,
											client_total:
												state.confirmedTotal != null ? state.confirmedTotal : state.previewTotal,
										}),
									}).then(function (res) {
										return res.json().then(function (body) {
											return { ok: res.ok, body: body };
										});
									});
								})
								.then(function (confirmResult) {
									if (!confirmResult) {
										return;
									}
									payBtn.disabled = false;
									if (label) {
										label.textContent = i18n.complete || 'Pay now';
									}
									if (!confirmResult.ok || !confirmResult.body || !confirmResult.body.success) {
										showStripeError(
											(confirmResult.body && confirmResult.body.message) ||
												i18n.paymentError ||
												'Payment could not be completed. Please try again.'
										);
										return;
									}
								wrap.hidden = true;
								var subtitle = root.querySelector('.booking-form__success-subtitle');
								var text = root.querySelector('.booking-form__success-text');
								if (subtitle) {
									subtitle.textContent = i18n.paymentSuccess || 'Payment successful — your booking is confirmed.';
								}
								if (text) {
									text.textContent = i18n.paymentSuccess || 'Payment successful — your booking is confirmed.';
								}
								root.dispatchEvent(
									new CustomEvent('somvio:booking-paid', {
										bubbles: true,
										detail: buildSuccessDetail({
											booking_id: (data && data.booking_id) || 0,
											message:
												i18n.paymentSuccess ||
												'Payment successful — your booking is confirmed.',
											requires_payment: false,
											deferRedirect: false,
										}),
									})
								);
							})
							.catch(function () {
								payBtn.disabled = false;
								if (label) {
									label.textContent = i18n.complete || 'Pay now';
								}
								showStripeError(i18n.paymentError || 'Payment could not be completed. Please try again.');
							});
					};
				})
				.catch(function () {
					showStripeError(i18n.paymentError || 'Payment could not be completed. Please try again.');
				});
			}

			/* Mount after success-card animation so Stripe Elements paints correctly. */
			var successCard = root.querySelector('.booking-form__card--success');
			var scheduled = false;
			function scheduleMount() {
				if (scheduled) {
					return;
				}
				scheduled = true;
				window.requestAnimationFrame(function () {
					window.requestAnimationFrame(mountPaymentElement);
				});
			}
			if (successCard) {
				successCard.addEventListener('animationend', scheduleMount, { once: true });
			}
			window.setTimeout(scheduleMount, 450);
		}

		/* Events */
		root.querySelectorAll('[data-booking-service]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				state.service = btn.getAttribute('data-booking-service') || '';
				state.serviceBtn = btn;
				invalidateServerQuote();
				showError('');
				clearAddonsIfNeeded();
				renderServiceCards();
				syncRoomFields();

				/* Leave Extra Services if the new service skips that step. */
				if (state.step === 2 && !serviceHasExtras(state.service)) {
					setStep(1);
				} else {
					updateStepLabels(state.step);
				}
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
				invalidateServerQuote();
				syncButtons(n);
				setActive();
				if (state.service === 'regular-cleaning' && key === 'bedrooms') {
					var labelEl = wrap.querySelector('[data-booking-counter-label]');
					if (labelEl) {
						labelEl.textContent = bedroomHomeLabel(n, i18n);
					}
				}
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

		root.querySelectorAll('[data-booking-field="welcome_pack"]').forEach(function (radio) {
			radio.addEventListener('change', function () {
				if (radio.checked) {
					state.welcome_pack = radio.value;
					root.querySelectorAll('[data-booking-field="welcome_pack"]').forEach(function (el) {
						var option = el.closest('.booking-form__welcome-option');
						if (option) {
							option.classList.toggle('is-selected', !!el.checked);
						}
					});
					invalidateServerQuote();
					syncState();
				}
			});
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
				invalidateServerQuote();
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

		if (calGrid) {
			calGrid.addEventListener('keydown', function (event) {
				var offset = 0;
				if (event.key === 'ArrowLeft') {
					offset = -1;
				} else if (event.key === 'ArrowRight') {
					offset = 1;
				} else if (event.key === 'ArrowUp') {
					offset = -7;
				} else if (event.key === 'ArrowDown') {
					offset = 7;
				} else {
					return;
				}
				var days = Array.prototype.slice.call(calGrid.querySelectorAll('.booking-form__cal-day:not(:disabled)'));
				var index = days.indexOf(document.activeElement);
				if (index < 0) {
					return;
				}
				var nextIndex = Math.max(0, Math.min(days.length - 1, index + offset));
				event.preventDefault();
				days[nextIndex].focus();
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
				setStep(getNextStep(state.step));
			});
		});

		root.querySelectorAll('[data-booking-back]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (state.step <= 1 || state.step === SUCCESS_STEP) {
					return;
				}
				setStep(getPrevStep(state.step));
			});
		});

		root.querySelectorAll('[data-booking-step-tab]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var target = parseInt(btn.getAttribute('data-booking-step-tab'), 10);
				if (isNaN(target) || target === state.step) {
					return;
				}
				/* Hard-block Extra Services when Regular / Airbnb (no extras). */
				if (target === 2 && !serviceHasExtras(state.service)) {
					return;
				}
				var visible = getVisibleSteps();
				if (visible.indexOf(target) === -1) {
					return;
				}
				/* Only allow navigating to current or earlier steps. */
				var currentIdx = visible.indexOf(state.step);
				var targetIdx = visible.indexOf(target);
				if (targetIdx > currentIdx) {
					return;
				}
				setStep(target);
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

		root.querySelectorAll('[data-booking-field="payment_method"]').forEach(function (radio) {
			radio.addEventListener('change', function () {
				state.payment_method = radio.value === 'online' ? 'online' : 'cash';
				root.querySelectorAll('.booking-form__payment-option').forEach(function (opt) {
					var input = opt.querySelector('[data-booking-field="payment_method"]');
					opt.classList.toggle('is-selected', !!(input && input.checked));
				});
				var onlinePanel = root.querySelector('[data-booking-online-panel]');
				if (onlinePanel) {
					onlinePanel.hidden = state.payment_method !== 'online';
				}
				setFieldError('payment_method', '');
				updateNextAvailability();
			});
			if (radio.checked) {
				radio.dispatchEvent(new Event('change'));
			}
		});

		if (formEl) {
			formEl.addEventListener('submit', function (event) {
				event.preventDefault();
				if (state.step === TOTAL_STEPS && validateStep()) {
					submitBooking();
				}
			});
		}

		document.addEventListener('keydown', function (event) {
			if (!successModal || successModal.hidden || event.key !== 'Tab') {
				return;
			}
			var focusable = getModalFocusable();
			if (!focusable.length) {
				event.preventDefault();
				if (successDialog) {
					successDialog.focus();
				}
				return;
			}
			var first = focusable[0];
			var last = focusable[focusable.length - 1];
			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});

		setupRadioKeyboard('.booking-form__services', '[data-booking-service]');
		setupRadioKeyboard('[data-booking-slots]', '[data-booking-slot]');

		renderWeekdays();
		renderServiceCards();
		renderAddons();
		syncRoomFields();
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
