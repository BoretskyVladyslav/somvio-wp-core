/**
 * Somvio before/after comparison slider.
 * Syncs CSS --ba-pos from a full-area range input (mouse, touch, keyboard).
 */
(function () {
	'use strict';

	/**
	 * @param {number} value
	 * @param {number} min
	 * @param {number} max
	 * @returns {number}
	 */
	function clamp(value, min, max) {
		return Math.min(max, Math.max(min, value));
	}

	/**
	 * @param {HTMLElement} root
	 * @param {number} percent
	 * @returns {void}
	 */
	function setPosition(root, percent) {
		var pos = clamp(percent, 0, 100);
		var rounded = Math.round(pos * 10) / 10;

		root.style.setProperty('--ba-pos', rounded + '%');

		var range = root.querySelector('[data-before-after-range]');

		if (!range) {
			return;
		}

		if (String(range.value) !== String(rounded)) {
			range.value = String(rounded);
		}

		range.setAttribute('aria-valuenow', String(rounded));
		range.setAttribute('aria-valuetext', rounded + '% before');
	}

	/**
	 * @param {HTMLElement} root
	 * @returns {void}
	 */
	function initSlider(root) {
		var range = root.querySelector('[data-before-after-range]');
		var frame = root.querySelector('.before-after__frame');
		var dragging = false;
		var activePointerId = null;

		setPosition(root, range ? parseFloat(range.value) || 50 : 50);

		if (range) {
			range.addEventListener('input', function () {
				setPosition(root, parseFloat(range.value) || 0);
			});

			range.addEventListener('pointerdown', function () {
				root.classList.add('before-after__slider--dragging');
			});

			range.addEventListener('pointerup', function () {
				root.classList.remove('before-after__slider--dragging');
			});

			range.addEventListener('pointercancel', function () {
				root.classList.remove('before-after__slider--dragging');
			});
		}

		if (!frame) {
			return;
		}

		/**
		 * @param {PointerEvent} event
		 * @returns {void}
		 */
		function onPointerDown(event) {
			if (event.target === range) {
				return;
			}

			if (event.button !== undefined && event.button !== 0) {
				return;
			}

			dragging = true;
			activePointerId = event.pointerId;
			root.classList.add('before-after__slider--dragging');

			try {
				frame.setPointerCapture(event.pointerId);
			} catch (err) {
				// Ignore.
			}

			var rect = frame.getBoundingClientRect();
			if (rect.width > 0) {
				setPosition(root, ((event.clientX - rect.left) / rect.width) * 100);
			}

			event.preventDefault();
		}

		/**
		 * @param {PointerEvent} event
		 * @returns {void}
		 */
		function onPointerMove(event) {
			if (!dragging || (activePointerId !== null && event.pointerId !== activePointerId)) {
				return;
			}

			var rect = frame.getBoundingClientRect();
			if (rect.width > 0) {
				setPosition(root, ((event.clientX - rect.left) / rect.width) * 100);
			}
		}

		/**
		 * @param {PointerEvent} event
		 * @returns {void}
		 */
		function onPointerUp(event) {
			if (activePointerId !== null && event.pointerId !== activePointerId) {
				return;
			}

			dragging = false;
			activePointerId = null;
			root.classList.remove('before-after__slider--dragging');
		}

		// Range sits on top for a11y; pointer fallback if range is unavailable.
		frame.addEventListener('pointerdown', onPointerDown);
		frame.addEventListener('pointermove', onPointerMove);
		frame.addEventListener('pointerup', onPointerUp);
		frame.addEventListener('pointercancel', onPointerUp);
	}

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	ready(function () {
		document.querySelectorAll('[data-before-after]').forEach(initSlider);
	});
})();
