/**
 * Blog category filter tabs (Figma 300:2187).
 */
(function () {
	'use strict';

	function initBlogFilters(root) {
		var filters = root.querySelector('[data-blog-filters]');
		var posts = root.querySelector('[data-blog-posts]');

		if (!filters || !posts) {
			return;
		}

		var buttons = Array.prototype.slice.call(
			filters.querySelectorAll('[data-blog-filter]')
		);
		var cards = Array.prototype.slice.call(
			posts.querySelectorAll('[data-blog-category]')
		);

		if (!buttons.length || !cards.length) {
			return;
		}

		function setActive(id) {
			buttons.forEach(function (btn) {
				var active = btn.getAttribute('data-blog-filter') === id;
				btn.classList.toggle('is-active', active);
				btn.setAttribute('aria-selected', active ? 'true' : 'false');
			});

			cards.forEach(function (card) {
				var cat = card.getAttribute('data-blog-category') || '';
				var show = id === 'all' || cat === id;
				card.hidden = !show;
				card.classList.toggle('is-filtered-out', !show);
			});
		}

		filters.addEventListener('click', function (event) {
			var btn = event.target.closest('[data-blog-filter]');
			if (!btn || !filters.contains(btn)) {
				return;
			}
			setActive(btn.getAttribute('data-blog-filter') || 'all');
		});
	}

	function boot() {
		document.querySelectorAll('.blog-grid').forEach(initBlogFilters);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
