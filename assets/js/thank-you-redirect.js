/**
 * Redirect to /thank-you/ after successful booking (LatePoint / Somvio events).
 * Also smooth-scrolls #booking-calculator hash targets under the sticky header.
 */
(function () {
	'use strict';

	var cfg = window.somvioThankYou || {};
	var thankYouUrl = cfg.url || '/thank-you/';
	var storageKey = cfg.storageKey || 'somvio_booking_summary';
	var delay = typeof cfg.redirectDelay === 'number' ? cfg.redirectDelay : 400;
	var redirected = false;
	var CALCULATOR_ID = 'booking-calculator';

	function storeSummary(detail) {
		if (!detail || typeof detail !== 'object') {
			return;
		}
		try {
			window.sessionStorage.setItem(storageKey, JSON.stringify(detail));
		} catch (e) {
			/* private mode / quota — redirect without summary */
		}
	}

	function goThankYou(detail) {
		if (redirected) {
			return;
		}
		if (detail && detail.requires_payment) {
			return;
		}
		if (detail && detail.deferRedirect) {
			return;
		}
		redirected = true;
		storeSummary(detail || {});
		window.setTimeout(function () {
			window.location.assign(thankYouUrl);
		}, delay);
	}

	function fromCustomEvent(event) {
		goThankYou(event && event.detail ? event.detail : {});
	}

	function scrollToCalculator(behavior) {
		var el = document.getElementById(CALCULATOR_ID);
		if (!el || typeof el.scrollIntoView !== 'function') {
			return false;
		}
		el.scrollIntoView({
			behavior: behavior || 'smooth',
			block: 'start',
		});
		return true;
	}

	function onHashTarget() {
		if (window.location.hash !== '#' + CALCULATOR_ID) {
			return;
		}
		window.requestAnimationFrame(function () {
			scrollToCalculator('smooth');
		});
	}

	document.addEventListener('click', function (event) {
		var link = event.target && event.target.closest
			? event.target.closest('a[href*="#booking-calculator"]')
			: null;
		if (!link) {
			return;
		}
		var href = link.getAttribute('href') || '';
		var url;
		try {
			url = new URL(href, window.location.href);
		} catch (e) {
			return;
		}
		if (url.hash !== '#' + CALCULATOR_ID) {
			return;
		}
		if (url.pathname.replace(/\/$/, '') !== window.location.pathname.replace(/\/$/, '')) {
			return;
		}
		event.preventDefault();
		if (window.history && window.history.pushState) {
			window.history.pushState(null, '', '#' + CALCULATOR_ID);
		} else {
			window.location.hash = CALCULATOR_ID;
		}
		scrollToCalculator('smooth');
	});

	document.addEventListener('somvio:booking-success', fromCustomEvent);
	document.addEventListener('somvio:booking-paid', fromCustomEvent);
	document.addEventListener('somvio:quote-success', fromCustomEvent);
	document.addEventListener('latepoint:booking_created', fromCustomEvent);

	if (window.jQuery) {
		window.jQuery(document).on(
			'latepoint:booking_created latepoint_booking_created booking_created',
			function (_e, data) {
				goThankYou(data && typeof data === 'object' ? data : {});
			}
		);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', onHashTarget);
	} else {
		onHashTarget();
	}
	window.addEventListener('hashchange', onHashTarget);
})();
