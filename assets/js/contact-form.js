(function () {
	'use strict';

	var cfg = window.somvioContactForm || {};
	var form = document.getElementById('somvio-contact-form');

	if (!form || !cfg.restUrl) {
		return;
	}

	var statusEl = form.querySelector('.contact-form__status');
	var bodyEl = form.querySelector('[data-contact-body]');
	var successEl = form.querySelector('[data-contact-success]');
	var successTitleEl = successEl ? successEl.querySelector('.contact-form__success-title') : null;
	var successTextEl = successEl ? successEl.querySelector('.contact-form__success-text') : null;
	var submitBtn = form.querySelector('.contact-form__submit');
	var labelEl = submitBtn ? submitBtn.querySelector('.btn__label') : null;
	var i18n = cfg.i18n || {};

	var EMAIL_RE = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
	var PHONE_RE = /^\+?[0-9\s().-]{7,20}$/;

	function setStatus(message, isError) {
		if (!statusEl) {
			return;
		}
		statusEl.hidden = !message;
		statusEl.textContent = message || '';
		statusEl.classList.toggle('is-error', !!isError);
		statusEl.classList.toggle('is-success', !!message && !isError);
	}

	function setSubmitting(busy) {
		if (!submitBtn) {
			return;
		}
		submitBtn.disabled = !!busy;
		if (labelEl) {
			labelEl.textContent = busy
				? i18n.submitting || 'Sending…'
				: i18n.submit || 'Send';
		}
	}

	function getErrorEl(key) {
		return form.querySelector('[data-contact-error="' + key + '"]');
	}

	function setFieldError(key, field, message) {
		var errorEl = getErrorEl(key);
		var invalid = !!message;

		if (field) {
			field.classList.toggle('is-invalid', invalid);
			field.setAttribute('aria-invalid', invalid ? 'true' : 'false');
		}

		if (key === 'terms') {
			var termsLabel = form.querySelector('.contact-form__terms');
			if (termsLabel) {
				termsLabel.classList.toggle('is-invalid', invalid);
			}
		}

		if (!errorEl) {
			return;
		}

		errorEl.hidden = !invalid;
		errorEl.textContent = message || '';
	}

	function clearErrors() {
		['name', 'email', 'phone', 'message', 'terms'].forEach(function (key) {
			var field = form.elements.namedItem(key === 'terms' ? 'terms_accepted' : key);
			setFieldError(key, field, '');
		});
	}

	function isValidEmail(value) {
		return EMAIL_RE.test(value);
	}

	function isValidPhone(value) {
		if (!PHONE_RE.test(value)) {
			return false;
		}
		var digits = value.replace(/\D/g, '');
		return digits.length >= 7 && digits.length <= 15;
	}

	function validateField(key) {
		var name = form.elements.namedItem('name');
		var email = form.elements.namedItem('email');
		var phone = form.elements.namedItem('phone');
		var message = form.elements.namedItem('message');
		var terms = form.elements.namedItem('terms_accepted');

		if (key === 'name') {
			var nameVal = name ? String(name.value || '').trim() : '';
			if (!nameVal) {
				setFieldError('name', name, i18n.invalidName || 'Please enter your name.');
				return false;
			}
			setFieldError('name', name, '');
			return true;
		}

		if (key === 'email') {
			var emailVal = email ? String(email.value || '').trim() : '';
			if (!emailVal) {
				setFieldError('email', email, i18n.requiredField || 'This field is required.');
				return false;
			}
			if (!isValidEmail(emailVal)) {
				setFieldError('email', email, i18n.invalidEmail || 'Please enter a valid email address.');
				return false;
			}
			setFieldError('email', email, '');
			return true;
		}

		if (key === 'phone') {
			var phoneVal = phone ? String(phone.value || '').trim() : '';
			if (!phoneVal) {
				setFieldError('phone', phone, i18n.requiredField || 'This field is required.');
				return false;
			}
			if (!isValidPhone(phoneVal)) {
				setFieldError('phone', phone, i18n.invalidPhone || 'Please enter a valid phone number.');
				return false;
			}
			setFieldError('phone', phone, '');
			return true;
		}

		if (key === 'message') {
			var msgVal = message ? String(message.value || '').trim() : '';
			if (!msgVal) {
				setFieldError('message', message, i18n.invalidMsg || 'Please enter a message.');
				return false;
			}
			setFieldError('message', message, '');
			return true;
		}

		if (key === 'terms') {
			var termsOk = !terms || !!terms.checked;
			if (!termsOk) {
				setFieldError(
					'terms',
					terms,
					i18n.termsRequired || 'Please accept the Terms & Conditions and Privacy Policy.'
				);
				return false;
			}
			setFieldError('terms', terms, '');
			return true;
		}

		return true;
	}

	function validate() {
		var keys = ['name', 'email', 'phone', 'message', 'terms'];
		var ok = true;
		var firstMsg = '';

		keys.forEach(function (key) {
			var valid = validateField(key);
			if (!valid) {
				ok = false;
				if (!firstMsg) {
					var errorEl = getErrorEl(key);
					firstMsg = errorEl ? errorEl.textContent : '';
				}
			}
		});

		if (!ok) {
			setStatus(firstMsg || i18n.required || 'Please complete the required fields.', true);
		} else {
			setStatus('', false);
		}

		return ok;
	}

	['name', 'email', 'phone', 'message'].forEach(function (key) {
		var field = form.elements.namedItem(key);
		if (!field) {
			return;
		}
		field.addEventListener('blur', function () {
			validateField(key);
		});
		field.addEventListener('input', function () {
			if (field.classList.contains('is-invalid')) {
				validateField(key);
			}
		});
	});

	var termsInput = form.elements.namedItem('terms_accepted');
	if (termsInput) {
		termsInput.addEventListener('change', function () {
			validateField('terms');
		});
	}

	form.addEventListener('submit', function (event) {
		event.preventDefault();
		setStatus('', false);

		if (!validate()) {
			var firstInvalid = form.querySelector('.is-invalid');
			if (firstInvalid && typeof firstInvalid.focus === 'function') {
				firstInvalid.focus();
			}
			return;
		}

		var payload = {
			name: String(form.elements.namedItem('name').value || '').trim(),
			email: String(form.elements.namedItem('email').value || '').trim(),
			phone: String(form.elements.namedItem('phone').value || '').trim(),
			message: String(form.elements.namedItem('message').value || '').trim(),
			company_website: String(
				(form.elements.namedItem('company_website') || {}).value || ''
			).trim(),
		};

		setSubmitting(true);

		fetch(cfg.restUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.nonce || '',
			},
			body: JSON.stringify(payload),
		})
			.then(function (res) {
				return res.json().then(function (data) {
					return { ok: res.ok, data: data };
				});
			})
			.then(function (result) {
				if (!result.ok || !result.data || !result.data.success) {
					var err =
						(result.data && (result.data.message || result.data.code)) ||
						i18n.submitError ||
						'Something went wrong. Please try again.';
					throw new Error(typeof err === 'string' ? err : i18n.submitError);
				}

				clearErrors();
				setStatus('', false);

				if (successTitleEl) {
					successTitleEl.textContent = i18n.successTitle || 'Thank You!';
				}
				if (successTextEl) {
					successTextEl.textContent =
						i18n.successText ||
						result.data.message ||
						'Your message has been sent.';
				}

				form.classList.add('is-sent');
				if (bodyEl) {
					bodyEl.hidden = true;
				}
				if (successEl) {
					successEl.hidden = false;
				}
			})
			.catch(function (err) {
				setStatus(
					(err && err.message) || i18n.submitError || 'Something went wrong. Please try again.',
					true
				);
			})
			.finally(function () {
				if (!form.classList.contains('is-sent')) {
					setSubmitting(false);
				}
			});
	});
})();
