/**
 * Cookie consent banner — localStorage persistence.
 * Key: somvio_cookie_consent = 'accepted' | 'declined'
 */
( () => {
	const STORAGE_KEY = 'somvio_cookie_consent';
	const VALID = { accepted: true, declined: true };

	const root = document.querySelector( '[data-cookie-consent]' );
	if ( ! root ) {
		return;
	}

	const getStored = () => {
		try {
			return localStorage.getItem( STORAGE_KEY ) || '';
		} catch ( e ) {
			return '';
		}
	};

	const setStored = ( value ) => {
		try {
			localStorage.setItem( STORAGE_KEY, value );
		} catch ( e ) {
			/* private mode / blocked storage — still dismiss for this session */
		}
	};

	const hide = () => {
		root.classList.remove( 'is-visible' );
		root.setAttribute( 'aria-hidden', 'true' );

		const onEnd = () => {
			root.hidden = true;
			root.removeEventListener( 'transitionend', onEnd );
		};

		root.addEventListener( 'transitionend', onEnd );
		window.setTimeout( () => {
			if ( ! root.hidden ) {
				root.hidden = true;
			}
		}, 400 );
	};

	const show = () => {
		root.hidden = false;
		root.setAttribute( 'aria-hidden', 'false' );
		// Next frame so CSS transition runs from hidden → visible.
		window.requestAnimationFrame( () => {
			window.requestAnimationFrame( () => {
				root.classList.add( 'is-visible' );
			} );
		} );
	};

	const stored = getStored();
	if ( VALID[ stored ] ) {
		return;
	}

	show();

	const acceptBtn = root.querySelector( '[data-cookie-consent-accept]' );
	const declineBtn = root.querySelector( '[data-cookie-consent-decline]' );

	if ( acceptBtn ) {
		acceptBtn.addEventListener( 'click', () => {
			setStored( 'accepted' );
			hide();
		} );
	}

	if ( declineBtn ) {
		declineBtn.addEventListener( 'click', () => {
			setStored( 'declined' );
			hide();
		} );
	}
} )();
