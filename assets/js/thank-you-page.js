/**
 * Hydrate Thank You page from sessionStorage booking summary.
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'somvio_booking_summary';

	function readSummary() {
		try {
			var raw = window.sessionStorage.getItem(STORAGE_KEY);
			if (!raw) {
				return null;
			}
			var data = JSON.parse(raw);
			window.sessionStorage.removeItem(STORAGE_KEY);
			return data && typeof data === 'object' ? data : null;
		} catch (e) {
			return null;
		}
	}

	function init() {
		var root = document.querySelector('[data-thank-you]');
		if (!root) {
			return;
		}

		var summary = readSummary();
		if (!summary) {
			return;
		}

		var recap = root.querySelector('[data-thank-you-recap]');
		var keys = ['service', 'date', 'time', 'name', 'phone', 'email', 'address', 'total', 'booking_id'];
		var hasAny = false;

		keys.forEach(function (key) {
			var el = root.querySelector('[data-thank-you-field="' + key + '"]');
			if (!el) {
				return;
			}
			var val = summary[key];
			if (val == null || val === '') {
				var row = el.closest('.thank-you__row');
				if (row) {
					row.hidden = true;
				}
				return;
			}
			el.textContent = String(val);
			hasAny = true;
			if (key === 'booking_id') {
				var bookingRow = root.querySelector('[data-thank-you-booking-row]');
				if (bookingRow) {
					bookingRow.hidden = false;
				}
			}
		});

		if (recap && hasAny) {
			recap.hidden = false;
		}

		if (summary.message) {
			var subtitle = root.querySelector('[data-thank-you-subtitle]');
			if (subtitle) {
				subtitle.textContent = String(summary.message);
			}
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
