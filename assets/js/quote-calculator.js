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

	/**
	 * @param {number} n
	 * @returns {string}
	 */
	function formatMoney(n) {
		var symbol = rates.symbol || '£';
		return symbol + Number(n).toFixed(2);
	}

	/**
	 * Preview total from raw inputs (UI feedback only).
	 *
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

		return Math.round((base + bathExtra) * svcMult * propMult * 100) / 100;
	}

	/**
	 * @param {Date} d
	 * @returns {string} YYYY-MM-DD
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

		function readFields() {
			var map = ['service', 'property', 'bedrooms', 'bathrooms', 'date', 'time', 'name', 'email', 'phone', 'comment'];
			map.forEach(function (key) {
				var el = field(key);
				if (el) {
					state[key] = el.value;
				}
			});
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
			var slots = root.querySelectorAll('[data-quote-slot]');
			slots.forEach(function (btn) {
				var val = btn.getAttribute('data-quote-slot');
				var selected = state.time === val;
				btn.classList.toggle('is-selected', selected);
				btn.setAttribute('aria-checked', selected ? 'true' : 'false');
			});
		}

		function setStep(step) {
			state.step = step;
			var isSuccess = step === 5;

			panels.forEach(function (panel) {
				var n = parseInt(panel.getAttribute('data-quote-step'), 10);
				panel.hidden = n !== step;
			});

			if (titleEl) {
				titleEl.hidden = isSuccess;
				if (!isSuccess) {
					titleEl.textContent =
						step === 2
							? i18n.titleDate || 'Get Your Date'
							: i18n.titleDefault || 'Get Your Instant Quote';
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
			}
			if (step === 3) {
				renderSlots();
			}
			if (step === 4) {
				readFields();
				renderPrice();
			}

			showError('');
			root.dispatchEvent(
				new CustomEvent('somvio:quote-step', {
					bubbles: true,
					detail: { step: step, root: root },
				})
			);
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
				if (!state.date) {
					showError(i18n.required || 'Please complete the required fields.');
					return false;
				}
				return true;
			}

			if (state.step === 3) {
				if (!state.time) {
					showError(i18n.required || 'Please complete the required fields.');
					return false;
				}
				return true;
			}

			if (state.step === 4) {
				if (!state.name || !state.email || !state.phone) {
					showError(i18n.required || 'Please complete the required fields.');
					return false;
				}
				if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.email)) {
					showError(i18n.invalidEmail || 'Please enter a valid email address.');
					return false;
				}
				return true;
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
			state.submitting = true;
			if (nextBtn) {
				nextBtn.disabled = true;
			}
			readFields();
			renderPrice();

			var payload = {
				service: state.service,
				property: state.property,
				bedrooms: parseInt(state.bedrooms, 10),
				bathrooms: parseInt(state.bathrooms, 10),
				date: state.date,
				time: state.time,
				name: state.name,
				email: state.email,
				phone: state.phone,
				comment: state.comment,
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
					if (nextBtn) {
						nextBtn.disabled = false;
					}
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

			renderSlots();
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

		// Bind selects / inputs that affect price.
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
				showError('');
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

	// Re-init for dynamically injected modals.
	document.addEventListener('somvio:quote-mount', function (e) {
		var target = e.target;
		if (target && target.matches && target.matches('[data-quote-calculator]')) {
			initCalculator(target);
		} else if (e.detail && e.detail.root) {
			initCalculator(e.detail.root);
		}
	});

	/**
	 * Floating quote modal open/close.
	 */
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
