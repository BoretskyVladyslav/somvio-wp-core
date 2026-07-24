/**
 * Somvio multi-step instant quote calculator.
 * Client price is preview-only; confirm always recalculates on the server.
 */
(function () {
	'use strict';

	var TOTAL_STEPS = 4;
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

		var today = new Date();
		today.setHours(0, 0, 0, 0);

		var state = {
			step: 1,
			service: '',
			property: '',
			bedrooms: '1',
			bathrooms: '2',
			date: '',
			time: '',
			addons: [],
			name: '',
			email: '',
			phone: '',
			comment: '',
			previewTotal: 0,
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

		function readAddons() {
			var selected = [];
			root.querySelectorAll('[data-quote-addon]').forEach(function (input) {
				if (input.checked) {
					selected.push(input.getAttribute('data-quote-addon') || input.value);
				}
			});
			state.addons = selected;
		}

		function readFields() {
			['service', 'property', 'bedrooms', 'bathrooms', 'date', 'time', 'name', 'email', 'phone', 'comment'].forEach(
				function (key) {
					var el = field(key);
					if (el) {
						state[key] = el.value;
					}
				}
			);
			readAddons();
			state.previewTotal = getPreviewTotal(state);
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
			if (nextLabel && loading && state.step === TOTAL_STEPS) {
				nextLabel.textContent = i18n.submitting || 'Submitting…';
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

		function renderAddons() {
			root.querySelectorAll('[data-quote-addon]').forEach(function (input) {
				var card = input.closest('.quote-calculator__addon');
				if (card) {
					card.classList.toggle('is-selected', input.checked);
				}
			});
		}

		function setStep(step) {
			state.step = step;
			root.setAttribute('data-step', String(step));
			var isSuccess = step === 5;

			root.classList.toggle('quote-calculator--success', isSuccess);

			panels.forEach(function (panel) {
				var n = parseInt(panel.getAttribute('data-quote-step'), 10);
				panel.hidden = n !== step;
			});

			if (titleEl) {
				titleEl.hidden = isSuccess;
				if (!isSuccess) {
					if (step === 2) {
						titleEl.textContent = i18n.titleDate || 'Get Your Date';
					} else if (step === 3) {
						titleEl.textContent = i18n.titleAddons || 'Add-ons & Extras';
					} else {
						titleEl.textContent = i18n.titleDefault || 'Get Your Instant Quote';
					}
				}
			}

			if (stepLabel) {
				stepLabel.hidden = isSuccess;
				if (!isSuccess) {
					var tpl = i18n.stepOf || 'Step %1$d of %2$d';
					stepLabel.textContent = tpl
						.replace('%1$d', String(Math.min(step, TOTAL_STEPS)))
						.replace('%2$d', String(TOTAL_STEPS));
				}
			}

			if (backBtn) {
				backBtn.hidden = isSuccess || step <= 1;
				backBtn.setAttribute('aria-hidden', step <= 1 ? 'true' : 'false');
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

			if (step === 2) {
				renderCalendar();
				renderSlots();
			}
			if (step === 3) {
				renderAddons();
			}
			if (step === 4) {
				readFields();
				renderPrice();
			}

			clearFieldErrors();
			showError('');
			setLoading(false);

			root.dispatchEvent(
				new CustomEvent('somvio:quote-step', {
					bubbles: true,
					detail: { step: step, root: root },
				})
			);
		}

		/**
		 * @returns {{ valid: boolean, firstInvalid: HTMLElement|null }}
		 */
		function validateStep4() {
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
				if (!state.service || !state.property || !state.bedrooms || !state.bathrooms) {
					showError(i18n.required || 'Please complete the required fields.');
					return false;
				}
				return true;
			}

			if (state.step === 2) {
				clearFieldErrors();
				if (!state.date) {
					showError(i18n.required || 'Please complete the required fields.');
					return false;
				}
				if (!state.time) {
					setFieldError('time', i18n.selectTime || 'Please select a time slot.');
					showError(i18n.selectTime || 'Please select a time slot.');
					return false;
				}
				return true;
			}

			if (state.step === 3) {
				return true;
			}

			if (state.step === 4) {
				var result = validateStep4();
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

			var validation = validateStep4();
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

			var payload = {
				service: state.service,
				property: state.property,
				bedrooms: parseInt(state.bedrooms, 10),
				bathrooms: parseInt(state.bathrooms, 10),
				date: state.date,
				time: state.time,
				addons: state.addons.slice(),
				name: trim(state.name),
				email: trim(state.email),
				phone: normalizePhone(state.phone),
				comment: trim(state.comment),
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
					if (result.status === 409 && result.data && result.data.data && result.data.data.total != null) {
						state.previewTotal = Number(result.data.data.total);
						renderPrice();
						showError(result.data.message || i18n.submitError);
						return;
					}
					if (!result.ok) {
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
					setStep(5);
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
			state.addons = [];
			state.name = '';
			state.email = '';
			state.phone = '';
			state.comment = '';
			state.calYear = today.getFullYear();
			state.calMonth = today.getMonth();

			['date', 'time', 'name', 'email', 'phone', 'comment'].forEach(function (key) {
				var el = field(key);
				if (el) {
					el.value = '';
				}
			});
			if (dateDisplay) {
				dateDisplay.value = '';
			}

			root.querySelectorAll('[data-quote-addon]').forEach(function (input) {
				input.checked = false;
			});

			renderSlots();
			renderAddons();
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

		['service', 'property', 'bedrooms', 'bathrooms'].forEach(function (key) {
			var el = field(key);
			if (!el) {
				return;
			}
			el.addEventListener('change', function () {
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

		root.querySelectorAll('[data-quote-addon]').forEach(function (input) {
			input.addEventListener('change', function () {
				readAddons();
				renderAddons();
				renderPrice();
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
				if (state.step === 5) {
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
				setStep(state.step + 1);
			});
		}

		if (backBtn) {
			backBtn.addEventListener('click', function () {
				if (state.step > 1 && state.step <= TOTAL_STEPS) {
					setStep(state.step - 1);
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

		function openModal(e) {
			if (e) {
				e.preventDefault();
			}
			lastFocus = document.activeElement;
			modal.hidden = false;
			modal.setAttribute('aria-hidden', 'false');
			document.documentElement.classList.add('has-quote-modal');
			var focusable = modal.querySelector('[data-quote-next], button, [href], input, select, textarea');
			if (focusable) {
				focusable.focus();
			}
		}

		function closeModal(options) {
			var opts = options || {};
			modal.hidden = true;
			modal.setAttribute('aria-hidden', 'true');
			document.documentElement.classList.remove('has-quote-modal');
			if (opts.skipReset !== true) {
				var calc = modal.querySelector('[data-quote-calculator]');
				if (calc && typeof calc.somvioQuoteReset === 'function') {
					calc.somvioQuoteReset({ emitClose: false });
				}
			}
			if (lastFocus && typeof lastFocus.focus === 'function') {
				lastFocus.focus();
			}
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
