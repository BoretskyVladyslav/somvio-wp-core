/**
 * Why Choose Somvio — mobile carousel.
 *
 * Native CSS scroll-snap on the viewport. JS only moves by index and reflects
 * the nearest slide in the arrow disabled states.
 */
(function () {
	'use strict';

	var MOBILE_MQ = '(max-width: 767px)';

	/**
	 * @param {HTMLElement} root
	 * @returns {void}
	 */
	function initCarousel(root) {
		var viewport = root.querySelector('[data-why-choose-viewport]');
		var track = root.querySelector('[data-why-choose-track]');
		var slides = Array.prototype.slice.call(root.querySelectorAll('[data-why-choose-slide]'));
		var controls = root.querySelector('[data-why-choose-controls]');
		var prevBtn = root.querySelector('[data-why-choose-prev]');
		var nextBtn = root.querySelector('[data-why-choose-next]');
		var mq = window.matchMedia(MOBILE_MQ);
		var index = 0;
		var scrollRaf = 0;

		if (!viewport || !track || !slides.length) {
			return;
		}

		/**
		 * @param {number} next
		 * @returns {number}
		 */
		function clampIndex(next) {
			return Math.max(0, Math.min(slides.length - 1, next));
		}

		/**
		 * Slide offsets are measured relative to the first slide, whose native
		 * start-snap position is exactly scrollLeft 0.
		 *
		 * @param {number} next
		 * @param {ScrollBehavior} [behavior]
		 * @returns {void}
		 */
		function goTo(next, behavior) {
			index = clampIndex(next);
			var left = Math.max(0, slides[index].offsetLeft - slides[0].offsetLeft);

			viewport.scrollTo({
				left: left,
				behavior: behavior || 'smooth',
			});

			updateControls();
		}

		/**
		 * Nearest card start edge to the scrollport left.
		 *
		 * @returns {void}
		 */
		function syncIndexFromScroll() {
			var scrollLeft = viewport.scrollLeft;
			var best = 0;
			var bestDist = Infinity;

			for (var i = 0; i < slides.length; i++) {
				var target = Math.max(0, slides[i].offsetLeft - slides[0].offsetLeft);
				var dist = Math.abs(target - scrollLeft);
				if (dist < bestDist) {
					bestDist = dist;
					best = i;
				}
			}

			index = best;
			updateControls();
		}

		/**
		 * @returns {void}
		 */
		function updateControls() {
			if (!prevBtn || !nextBtn) {
				return;
			}

			var atStart = index <= 0;
			var atEnd = index >= slides.length - 1;

			prevBtn.disabled = atStart;
			nextBtn.disabled = atEnd;
			prevBtn.setAttribute('aria-disabled', atStart ? 'true' : 'false');
			nextBtn.setAttribute('aria-disabled', atEnd ? 'true' : 'false');
			prevBtn.classList.toggle('is-disabled', atStart);
			nextBtn.classList.toggle('is-disabled', atEnd);
		}

		/**
		 * @returns {void}
		 */
		function setMode() {
			var isMobile = mq.matches;

			root.classList.toggle('why-choose--carousel', isMobile);

			if (controls) {
				controls.hidden = !isMobile;
			}

			if (!isMobile) {
				index = 0;
				updateControls();
				return;
			}

			window.requestAnimationFrame(function () {
				syncIndexFromScroll();
			});
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function (event) {
				event.preventDefault();
				if (!mq.matches || prevBtn.disabled) {
					return;
				}
				goTo(index - 1);
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function (event) {
				event.preventDefault();
				if (!mq.matches || nextBtn.disabled) {
					return;
				}
				goTo(index + 1);
			});
		}

		viewport.addEventListener(
			'scroll',
			function () {
				if (!mq.matches) {
					return;
				}
				if (scrollRaf) {
					window.cancelAnimationFrame(scrollRaf);
				}
				scrollRaf = window.requestAnimationFrame(function () {
					scrollRaf = 0;
					syncIndexFromScroll();
				});
			},
			{ passive: true }
		);

		if (typeof mq.addEventListener === 'function') {
			mq.addEventListener('change', setMode);
		} else if (typeof mq.addListener === 'function') {
			mq.addListener(setMode);
		}

		var resizeTimer = 0;
		window.addEventListener('resize', function () {
			window.clearTimeout(resizeTimer);
			resizeTimer = window.setTimeout(function () {
				if (mq.matches) {
					syncIndexFromScroll();
				}
			}, 100);
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
