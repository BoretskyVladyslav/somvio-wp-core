/**
 * Somvio before/after comparison slider.
 * Pointer + touch drag with range sync for keyboard / a11y.
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
		var handle = root.querySelector('.before-after__handle');
		var dragging = false;
		var activePointerId = null;
		var activeTouchId = null;

		if (!frame) {
			return;
		}

		setPosition(root, range ? parseFloat(range.value) || 50 : 50);

		/**
		 * @param {number} clientX
		 * @returns {void}
		 */
		function moveToClientX(clientX) {
			var rect = frame.getBoundingClientRect();
			if (rect.width <= 0) {
				return;
			}
			setPosition(root, ((clientX - rect.left) / rect.width) * 100);
		}

		/**
		 * @returns {void}
		 */
		function startDragVisual() {
			dragging = true;
			root.classList.add('before-after__slider--dragging');
		}

		/**
		 * @returns {void}
		 */
		function endDragVisual() {
			dragging = false;
			activePointerId = null;
			activeTouchId = null;
			root.classList.remove('before-after__slider--dragging');
		}

		if (range) {
			range.addEventListener('input', function () {
				if (dragging) {
					return;
				}
				setPosition(root, parseFloat(range.value) || 0);
			});

			range.addEventListener('keydown', function () {
				/* Arrow keys update value; sync on next input tick. */
				window.requestAnimationFrame(function () {
					setPosition(root, parseFloat(range.value) || 0);
				});
			});
		}

		/**
		 * @param {PointerEvent} event
		 * @returns {void}
		 */
		function onPointerDown(event) {
			if (event.pointerType === 'mouse' && event.button !== 0) {
				return;
			}

			/* Keyboard focus stays on the range; ignore its native pointer path. */
			if (range && event.target === range) {
				return;
			}

			startDragVisual();
			activePointerId = event.pointerId;

			try {
				root.setPointerCapture(event.pointerId);
			} catch (err) {
				// Ignore.
			}

			moveToClientX(event.clientX);
			event.preventDefault();
		}

		/**
		 * @param {PointerEvent} event
		 * @returns {void}
		 */
		function onPointerMove(event) {
			if (!dragging || activePointerId === null || event.pointerId !== activePointerId) {
				return;
			}

			moveToClientX(event.clientX);
			event.preventDefault();
		}

		/**
		 * @param {PointerEvent} event
		 * @returns {void}
		 */
		function onPointerUp(event) {
			if (activePointerId !== null && event.pointerId !== activePointerId) {
				return;
			}

			endDragVisual();
		}

		root.addEventListener('pointerdown', onPointerDown);
		root.addEventListener('pointermove', onPointerMove);
		root.addEventListener('pointerup', onPointerUp);
		root.addEventListener('pointercancel', onPointerUp);

		/*
		 * Touch fallback for browsers where pointer events are incomplete
		 * on overlayed controls, or range still intercepts gestures.
		 */
		root.addEventListener(
			'touchstart',
			function (event) {
				if (!event.changedTouches.length || dragging) {
					return;
				}

				var touch = event.changedTouches[0];
				activeTouchId = touch.identifier;
				startDragVisual();
				moveToClientX(touch.clientX);

				if (event.cancelable) {
					event.preventDefault();
				}
			},
			{ passive: false }
		);

		root.addEventListener(
			'touchmove',
			function (event) {
				if (activeTouchId === null) {
					return;
				}

				var touch = null;
				var i;

				for (i = 0; i < event.changedTouches.length; i++) {
					if (event.changedTouches[i].identifier === activeTouchId) {
						touch = event.changedTouches[i];
						break;
					}
				}

				if (!touch) {
					return;
				}

				moveToClientX(touch.clientX);

				if (event.cancelable) {
					event.preventDefault();
				}
			},
			{ passive: false }
		);

		function endTouch(event) {
			if (activeTouchId === null) {
				return;
			}

			var i;
			for (i = 0; i < event.changedTouches.length; i++) {
				if (event.changedTouches[i].identifier === activeTouchId) {
					endDragVisual();
					return;
				}
			}
		}

		root.addEventListener('touchend', endTouch, { passive: true });
		root.addEventListener('touchcancel', endTouch, { passive: true });

		if (handle) {
			handle.setAttribute('aria-hidden', 'true');
		}
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
