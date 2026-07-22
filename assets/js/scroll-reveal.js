/**
 * Scroll reveal — adds .is-revealed when .reveal-on-scroll enters the viewport.
 *
 * @package Somvio_Child
 */
(function () {
	'use strict';

	var elements = document.querySelectorAll('.reveal-on-scroll');

	if (!elements.length) {
		return;
	}

	var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (prefersReducedMotion || !('IntersectionObserver' in window)) {
		elements.forEach(function (el) {
			el.classList.add('is-revealed');
		});
		return;
	}

	var observer = new IntersectionObserver(
		function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) {
					return;
				}

				entry.target.classList.add('is-revealed');
				observer.unobserve(entry.target);
			});
		},
		{
			threshold: 0.15,
			rootMargin: '0px 0px -8% 0px',
		}
	);

	elements.forEach(function (el) {
		observer.observe(el);
	});
})();
