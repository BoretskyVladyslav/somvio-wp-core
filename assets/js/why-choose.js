/**
 * Why Choose Somvio — mobile carousel (scroll-snap + arrows + touch swipe).
 */
(function () {
	'use strict';

	var MOBILE_MQ = '(max-width: 767px)';

	/**
	 * @param {HTMLElement} root
	 * @returns {void}
	 */
	function initCarousel(root) {
		var track = root.querySelector('[data-why-choose-track]');
		var slides = root.querySelectorAll('[data-why-choose-slide]');
		var controls = root.querySelector('[data-why-choose-controls]');
		var prevBtn = root.querySelector('[data-why-choose-prev]');
		var nextBtn = root.querySelector('[data-why-choose-next]');
		var mq = window.matchMedia(MOBILE_MQ);
		var index = 0;
		var drag = null;

		if (!track || !slides.length) {
			return;
		}

		/**
		 * @returns {number}
		 */
		function slideStep() {
			var first = slides[0];
			if (!first) {
				return track.clientWidth;
			}
			var style = window.getComputedStyle(track);
			var gap = parseFloat(style.columnGap || style.gap) || 0;
			return first.getBoundingClientRect().width + gap;
		}

		/**
		 * @param {number} next
		 * @param {ScrollBehavior} [behavior]
		 * @returns {void}
		 */
		function goTo(next, behavior) {
			index = Math.max(0, Math.min(slides.length - 1, next));
			track.scrollTo({
				left: index * slideStep(),
				behavior: behavior || 'smooth',
			});
			updateControls();
		}

		/**
		 * @returns {void}
		 */
		function syncIndexFromScroll() {
			var step = slideStep();
			if (step <= 0) {
				return;
			}
			index = Math.round(track.scrollLeft / step);
			index = Math.max(0, Math.min(slides.length - 1, index));
			updateControls();
		}

		/**
		 * @returns {void}
		 */
		function updateControls() {
			if (!prevBtn || !nextBtn) {
				return;
			}
			prevBtn.disabled = index <= 0;
			nextBtn.disabled = index >= slides.length - 1;
		}

		/**
		 * @returns {void}
		 */
		function setMode() {
			var isMobile = mq.matches;

			if (controls) {
				controls.hidden = !isMobile;
			}

			root.classList.toggle('why-choose--carousel', isMobile);
			root.classList.toggle('service-why--carousel', isMobile);

			if (!isMobile) {
				track.scrollLeft = 0;
				index = 0;
				updateControls();
			} else {
				goTo(index, 'auto');
			}
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				goTo(index - 1);
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				goTo(index + 1);
			});
		}

		track.addEventListener(
			'scroll',
			function () {
				if (!mq.matches) {
					return;
				}
				window.requestAnimationFrame(syncIndexFromScroll);
			},
			{ passive: true }
		);

		track.addEventListener(
			'touchstart',
			function (event) {
				if (!mq.matches || !event.touches.length) {
					return;
				}
				drag = {
					startX: event.touches[0].clientX,
					startY: event.touches[0].clientY,
					scrollLeft: track.scrollLeft,
					locked: null,
				};
			},
			{ passive: true }
		);

		track.addEventListener(
			'touchmove',
			function (event) {
				if (!drag || !mq.matches || !event.touches.length) {
					return;
				}

				var dx = event.touches[0].clientX - drag.startX;
				var dy = event.touches[0].clientY - drag.startY;

				if (drag.locked === null) {
					drag.locked = Math.abs(dx) > Math.abs(dy);
				}

				if (!drag.locked) {
					return;
				}

				track.scrollLeft = drag.scrollLeft - dx;
			},
			{ passive: true }
		);

		track.addEventListener(
			'touchend',
			function () {
				if (!drag || !mq.matches) {
					drag = null;
					return;
				}

				if (drag.locked) {
					syncIndexFromScroll();
					goTo(index);
				}

				drag = null;
			},
			{ passive: true }
		);

		track.addEventListener(
			'touchcancel',
			function () {
				drag = null;
			},
			{ passive: true }
		);

		if (typeof mq.addEventListener === 'function') {
			mq.addEventListener('change', setMode);
		} else if (typeof mq.addListener === 'function') {
			mq.addListener(setMode);
		}

		window.addEventListener('resize', function () {
			if (mq.matches) {
				goTo(index, 'auto');
			}
		});

		setMode();
	}

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	ready(function () {
		document.querySelectorAll('[data-why-choose]').forEach(initCarousel);
	});
})();
