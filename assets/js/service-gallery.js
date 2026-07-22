/**
 * Single Service gallery — infinite loop carousel with peek + swipe.
 */
(function () {
	'use strict';

	var TRANSITION = 'transform 0.45s cubic-bezier(0.25, 0.1, 0.25, 1)';
	var SWIPE_THRESHOLD = 48;

	/**
	 * @param {HTMLElement} root
	 * @returns {void}
	 */
	function initGallery(root) {
		var viewport = root.querySelector('[data-service-gallery-viewport]');
		var track = root.querySelector('[data-service-gallery-track]');
		var slides = root.querySelectorAll('[data-service-gallery-slide]');
		var prevBtn = root.querySelector('[data-service-gallery-prev]');
		var nextBtn = root.querySelector('[data-service-gallery-next]');
		var uniqueCount = parseInt(root.getAttribute('data-service-gallery-count') || '0', 10);
		var index = uniqueCount;
		var animating = false;
		var drag = null;

		if (!viewport || !track || !slides.length || uniqueCount < 1) {
			return;
		}

		/**
		 * @returns {{slideW: number, gap: number, step: number, viewportW: number}}
		 */
		function metrics() {
			var first = slides[0];
			var style = window.getComputedStyle(track);
			var gap = parseFloat(style.columnGap || style.gap) || 0;
			var slideW = first.getBoundingClientRect().width;
			return {
				slideW: slideW,
				gap: gap,
				step: slideW + gap,
				viewportW: viewport.getBoundingClientRect().width,
			};
		}

		/**
		 * @param {number} i
		 * @param {boolean} animate
		 * @param {number} [dragOffset]
		 * @returns {void}
		 */
		function setPosition(i, animate, dragOffset) {
			var m = metrics();
			var offset = typeof dragOffset === 'number' ? dragOffset : 0;
			var x = m.viewportW / 2 - m.slideW / 2 - i * m.step + offset;

			track.style.transition = animate ? TRANSITION : 'none';
			track.style.transform = 'translate3d(' + x + 'px, 0, 0)';
			index = i;
			updateActive();
		}

		/**
		 * @returns {void}
		 */
		function updateActive() {
			var logical = ((index % uniqueCount) + uniqueCount) % uniqueCount;
			slides.forEach(function (slide, i) {
				var isActive = i === index;
				slide.classList.toggle('is-active', isActive);
				slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
			});
			root.setAttribute('data-active-index', String(logical));
		}

		/**
		 * @returns {void}
		 */
		function normalizeLoop() {
			if (index >= uniqueCount * 2) {
				setPosition(index - uniqueCount, false);
			} else if (index < uniqueCount) {
				setPosition(index + uniqueCount, false);
			}
		}

		/**
		 * @param {number} delta
		 * @returns {void}
		 */
		function go(delta) {
			if (animating) {
				return;
			}
			animating = true;
			setPosition(index + delta, true);
		}

		track.addEventListener('transitionend', function (event) {
			if (event.target !== track || event.propertyName !== 'transform') {
				return;
			}
			normalizeLoop();
			animating = false;
		});

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				go(-1);
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				go(1);
			});
		}

		/**
		 * @param {number} clientX
		 * @param {number} clientY
		 * @param {number} pointerId
		 * @returns {void}
		 */
		function startDrag(clientX, clientY, pointerId) {
			if (animating) {
				return;
			}
			drag = {
				startX: clientX,
				startY: clientY,
				pointerId: pointerId,
				locked: null,
				moved: false,
			};
			track.style.transition = 'none';
		}

		/**
		 * @param {number} clientX
		 * @param {number} clientY
		 * @returns {void}
		 */
		function moveDrag(clientX, clientY) {
			if (!drag) {
				return;
			}

			var dx = clientX - drag.startX;
			var dy = clientY - drag.startY;

			if (drag.locked === null) {
				if (Math.abs(dx) < 6 && Math.abs(dy) < 6) {
					return;
				}
				drag.locked = Math.abs(dx) > Math.abs(dy);
			}

			if (!drag.locked) {
				return;
			}

			drag.moved = true;
			setPosition(index, false, dx);
		}

		/**
		 * @param {number} clientX
		 * @returns {void}
		 */
		function endDrag(clientX) {
			if (!drag) {
				return;
			}

			var dx = clientX - drag.startX;
			var shouldSlide = drag.locked && drag.moved && Math.abs(dx) >= SWIPE_THRESHOLD;

			if (shouldSlide) {
				animating = true;
				setPosition(index + (dx < 0 ? 1 : -1), true);
			} else {
				setPosition(index, true);
			}

			drag = null;
		}

		viewport.addEventListener('pointerdown', function (event) {
			if (event.pointerType === 'mouse' && event.button !== 0) {
				return;
			}
			startDrag(event.clientX, event.clientY, event.pointerId);
			if (viewport.setPointerCapture) {
				viewport.setPointerCapture(event.pointerId);
			}
		});

		viewport.addEventListener('pointermove', function (event) {
			if (!drag || drag.pointerId !== event.pointerId) {
				return;
			}
			moveDrag(event.clientX, event.clientY);
		});

		function finishPointer(event) {
			if (!drag || drag.pointerId !== event.pointerId) {
				return;
			}
			endDrag(event.clientX);
		}

		viewport.addEventListener('pointerup', finishPointer);
		viewport.addEventListener('pointercancel', finishPointer);

		/* Keep vertical page scroll; block it only once a horizontal swipe locks. */
		viewport.addEventListener(
			'touchmove',
			function (event) {
				if (drag && drag.locked && event.cancelable) {
					event.preventDefault();
				}
			},
			{ passive: false }
		);

		root.setAttribute('tabindex', '0');

		root.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				go(-1);
			} else if (event.key === 'ArrowRight') {
				event.preventDefault();
				go(1);
			}
		});

		window.addEventListener('resize', function () {
			setPosition(index, false);
		});

		setPosition(index, false);
	}

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	ready(function () {
		document.querySelectorAll('[data-service-gallery]').forEach(initGallery);
	});
})();
