/**
 * Homepage quote start — validate UK postcode, then send to /booking/.
 */
(() => {
	const cfg = window.somvioQuoteStart || {};
	const uncovered = (cfg.i18n && cfg.i18n.uncovered) || 'Sorry, we do not cover this area yet';
	const invalidMsg = (cfg.i18n && cfg.i18n.invalid) || 'Enter a valid UK postcode.';
	const serviceMsg = (cfg.i18n && cfg.i18n.service) || 'Please select a service.';
	const genericMsg = (cfg.i18n && cfg.i18n.generic) || 'Something went wrong. Please try again.';

	async function somvioValidatePostcode(postcode) {
		if (!cfg.ajaxUrl) {
			throw new Error('no_ajax_url');
		}

		const body = new FormData();
		body.append('action', 'somvio_validate_postcode');
		body.append('nonce', cfg.nonce || '');
		body.append('postcode', postcode);

		const res = await fetch(cfg.ajaxUrl, {
			method: 'POST',
			body,
			credentials: 'same-origin',
		});

		if (!res.ok) {
			throw new Error('http_' + res.status);
		}

		const json = await res.json();
		const data = json && json.data && typeof json.data === 'object' ? json.data : {};

		return {
			valid: json.valid === true || data.valid === true,
			postcode: data.postcode || json.postcode || '',
			prefix: data.prefix || json.prefix || '',
			message: json.message || data.message || '',
		};
	}

	function showError(root, message) {
		const el = root.querySelector('[data-quote-start-error]');
		const input = root.querySelector('[data-quote-start-postcode]');
		if (!el) {
			return;
		}
		if (!message) {
			el.hidden = true;
			el.textContent = '';
			if (input) {
				input.removeAttribute('aria-invalid');
			}
			return;
		}
		el.hidden = false;
		el.textContent = message;
		if (input) {
			input.setAttribute('aria-invalid', 'true');
		}
	}

	function bookingUrl(service, postcode) {
		const url = new URL(cfg.bookingUrl || '/booking/', window.location.origin);
		url.searchParams.set('service', service);
		url.searchParams.set('postcode', postcode);
		url.hash = 'booking-calculator';
		return url;
	}

	function goToBooking(service, postcode) {
		const url = bookingUrl(service, postcode);
		if (url.origin !== window.location.origin) {
			throw new Error('bad_origin');
		}
		window.location.assign(url.toString());
	}

	function initQuoteStart(root) {
		const form = root.querySelector('[data-quote-start-form]');
		const serviceEl = root.querySelector('[data-quote-start-service]');
		const postcodeEl = root.querySelector('[data-quote-start-postcode]');
		const submitBtn = root.querySelector('[data-quote-start-submit]');
		if (!form || !postcodeEl) {
			return;
		}

		let blurGen = 0;
		let submitting = false;

		async function validatePostcode() {
			const service = serviceEl ? serviceEl.value.trim() : '';
			const postcode = postcodeEl.value.trim();

			if (!service) {
				return { ok: false, error: serviceMsg };
			}

			if (!postcode) {
				return { ok: false, error: invalidMsg };
			}

			const result = await somvioValidatePostcode(postcode);
			if (result.valid !== true) {
				return { ok: false, error: result.message || uncovered, result, service, postcode };
			}

			return { ok: true, result, service, postcode };
		}

		async function onBlur() {
			if (!postcodeEl.value.trim() || submitting) {
				if (!postcodeEl.value.trim()) {
					showError(root, '');
				}
				return;
			}

			const gen = ++blurGen;
			try {
				const checked = await validatePostcode();
				if (gen !== blurGen || submitting) {
					return;
				}
				showError(root, checked.ok === true ? '' : checked.error);
			} catch (err) {
				if (gen !== blurGen || submitting) {
					return;
				}
				showError(root, genericMsg);
			}
		}

		async function onSubmit() {
			blurGen += 1;
			if (submitting) {
				return;
			}

			submitting = true;
			if (submitBtn) {
				submitBtn.disabled = true;
			}

			try {
				const checked = await validatePostcode();
				if (checked.ok !== true || !checked.result || checked.result.valid !== true) {
					showError(root, checked.error || uncovered);
					return;
				}

				goToBooking(checked.service, checked.result.postcode || checked.postcode);
			} catch (err) {
				showError(root, genericMsg);
			} finally {
				submitting = false;
				if (submitBtn) {
					submitBtn.disabled = false;
				}
			}
		}

		form.addEventListener('submit', (event) => {
			event.preventDefault();
			onSubmit();
		});

		postcodeEl.addEventListener('blur', onBlur);

		postcodeEl.addEventListener('input', () => {
			showError(root, '');
		});
	}

	function boot() {
		document.querySelectorAll('[data-quote-start]').forEach(initQuoteStart);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
