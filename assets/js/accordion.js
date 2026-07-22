/**
 * Somvio accordion — smooth single-open panels with aria-expanded.
 */
(function () {
	'use strict';

	var REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var DURATION_MS = REDUCED_MOTION ? 0 : 280;
	var OPEN_CLASS = 'is-open';

	/**
	 * @param {HTMLElement} panel
	 * @param {boolean} open
	 * @returns {void}
	 */
	function animatePanel(panel, open) {
		var inner = panel.querySelector('[data-accordion-panel-inner]');

		if (!inner) {
			panel.hidden = !open;
			return;
		}

		if (open) {
			panel.hidden = false;
			panel.style.height = '0px';
			panel.style.overflow = 'hidden';
			// Force reflow before expanding.
			void panel.offsetHeight;
			panel.style.transition = 'height ' + DURATION_MS + 'ms ease';
			panel.style.height = inner.scrollHeight + 'px';

			window.setTimeout(function () {
				panel.style.height = '';
				panel.style.overflow = '';
				panel.style.transition = '';
			}, DURATION_MS);
			return;
		}

		panel.style.height = panel.scrollHeight + 'px';
		panel.style.overflow = 'hidden';
		void panel.offsetHeight;
		panel.style.transition = 'height ' + DURATION_MS + 'ms ease';
		panel.style.height = '0px';

		window.setTimeout(function () {
			panel.hidden = true;
			panel.style.height = '';
			panel.style.overflow = '';
			panel.style.transition = '';
		}, DURATION_MS);
	}

	/**
	 * @param {HTMLElement} item
	 * @param {boolean} open
	 * @returns {void}
	 */
	function setItemOpen(item, open) {
		var trigger = item.querySelector('[data-accordion-trigger]');
		var panel = item.querySelector('[data-accordion-panel]');

		if (!trigger || !panel) {
			return;
		}

		var wasOpen = item.classList.contains(OPEN_CLASS);

		if (open === wasOpen) {
			return;
		}

		item.classList.toggle(OPEN_CLASS, open);
		trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
		animatePanel(panel, open);
	}

	/**
	 * @param {HTMLElement} root
	 * @returns {void}
	 */
	function initAccordion(root) {
		var items = Array.prototype.slice.call(root.querySelectorAll('[data-accordion-item]'));

		items.forEach(function (item) {
			var trigger = item.querySelector('[data-accordion-trigger]');

			if (!trigger) {
				return;
			}

			trigger.addEventListener('click', function () {
				var isOpen = item.classList.contains(OPEN_CLASS);

				items.forEach(function (other) {
					if (other !== item) {
						setItemOpen(other, false);
					}
				});

				setItemOpen(item, !isOpen);
			});
		});
	}

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	ready(function () {
		document.querySelectorAll('[data-accordion]').forEach(initAccordion);
	});
})();
