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

		let json = {};
		try {
			json = await res.json();
		} catch (err) {
			throw new Error('http_' + res.status);
		}

		const data = json && json.data && typeof json.data === 'object' ? json.data : {};

		return {
			valid: Boolean(json.success && data.valid),
			postcode: data.postcode || '',
			prefix: data.prefix || '',
			message: data.message || json.message || '',
		};
	}

	function showError(root, message, invalidField) {
		const el = root.querySelector('[data-quote-start-error]');
		const input = root.querySelector('[data-quote-start-postcode]');
		const serviceEl = root.querySelector('[data-quote-start-service]');
		if (!el) {
			return;
		}
		if (!el.id) {
			el.id = (input && input.id ? input.id : 'quote-start') + '-error';
		}
		if (!message) {
			el.hidden = true;
			el.textContent = '';
			if (input) {
				input.removeAttribute('aria-invalid');
				input.removeAttribute('aria-describedby');
			}
			if (serviceEl) {
				serviceEl.removeAttribute('aria-invalid');
				serviceEl.removeAttribute('aria-describedby');
			}
			return;
		}
		el.hidden = false;
		el.textContent = message;
		const target = invalidField === 'service' && serviceEl ? serviceEl : input;
		if (input) {
			if (target === input) {
				input.setAttribute('aria-invalid', 'true');
				input.setAttribute('aria-describedby', el.id);
			} else {
				input.removeAttribute('aria-invalid');
				input.removeAttribute('aria-describedby');
			}
		}
		if (serviceEl) {
			if (target === serviceEl) {
				serviceEl.setAttribute('aria-invalid', 'true');
				serviceEl.setAttribute('aria-describedby', el.id);
			} else {
				serviceEl.removeAttribute('aria-invalid');
				serviceEl.removeAttribute('aria-describedby');
			}
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

		function isAllowedService(service) {
			if (!serviceEl || !service) {
				return false;
			}
			return Array.prototype.some.call(serviceEl.options, (opt) => opt.value === service);
		}

		async function validatePostcode() {
			const service = serviceEl ? serviceEl.value.trim() : '';
			const postcode = postcodeEl.value.trim();

			if (!service || !isAllowedService(service)) {
				return { ok: false, error: serviceMsg, field: 'service' };
			}

			if (!postcode) {
				return { ok: false, error: invalidMsg, field: 'postcode' };
			}

			const result = await somvioValidatePostcode(postcode);
			if (result.valid !== true) {
				return { ok: false, error: result.message || uncovered, result, service, postcode, field: 'postcode' };
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
				showError(root, checked.ok === true ? '' : checked.error, checked.field);
			} catch (err) {
				if (gen !== blurGen || submitting) {
					return;
				}
				showError(root, genericMsg, 'postcode');
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
				submitBtn.setAttribute('aria-busy', 'true');
			}

			try {
				const checked = await validatePostcode();
				if (checked.ok !== true || !checked.result || checked.result.valid !== true) {
					showError(root, checked.error || uncovered, checked.field || 'postcode');
					return;
				}

				goToBooking(checked.service, checked.result.postcode || checked.postcode);
			} catch (err) {
				showError(root, genericMsg, 'postcode');
			} finally {
				submitting = false;
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.setAttribute('aria-busy', 'false');
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
