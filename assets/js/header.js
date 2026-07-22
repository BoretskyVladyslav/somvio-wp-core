/**
 * Somvio header — sticky scroll state, responsive nav drawer & Services accordion.
 */
( () => {
	const header = document.querySelector( '#masthead.site-header, .somvio-header' );
	if ( ! header ) {
		return;
	}

	const syncScrolled = () => {
		header.classList.toggle( 'is-scrolled', window.scrollY > 50 );
	};

	window.addEventListener( 'scroll', syncScrolled, { passive: true } );
	syncScrolled();

	const toggle = header.querySelector( '.somvio-header__toggle' );
	const nav = header.querySelector( '.somvio-header__nav' );
	const backdrop = header.querySelector( '.somvio-header__backdrop' );
	const mqDesktop = window.matchMedia( '(min-width: 1024px)' );
	const body = document.body;

	if ( ! toggle || ! nav ) {
		return;
	}

	const labelOpen = toggle.getAttribute( 'aria-label' ) || 'Open menu';
	const labelClose = 'Close menu';

	const closeMobileSubmenus = () => {
		nav.querySelectorAll( '.menu-item-has-children.is-submenu-open' ).forEach( ( item ) => {
			item.classList.remove( 'is-submenu-open' );
			const trigger = item.querySelector( ':scope > a' );
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	};

	const setNavOpen = ( isOpen ) => {
		const open = Boolean( isOpen ) && ! mqDesktop.matches;

		header.classList.toggle( 'somvio-header--nav-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', open ? labelClose : labelOpen );
		nav.setAttribute( 'aria-hidden', mqDesktop.matches ? 'false' : open ? 'false' : 'true' );
		body.classList.toggle( 'somvio-no-scroll', open );

		if ( 'inert' in nav ) {
			nav.inert = ! mqDesktop.matches && ! open;
		}

		if ( backdrop ) {
			backdrop.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
		}

		if ( ! open ) {
			closeMobileSubmenus();
		}
	};

	const syncViewport = () => {
		if ( mqDesktop.matches ) {
			header.classList.remove( 'somvio-header--nav-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.setAttribute( 'aria-label', labelOpen );
			nav.setAttribute( 'aria-hidden', 'false' );
			if ( 'inert' in nav ) {
				nav.inert = false;
			}
			body.classList.remove( 'somvio-no-scroll' );
			closeMobileSubmenus();
			if ( backdrop ) {
				backdrop.setAttribute( 'aria-hidden', 'true' );
			}
			return;
		}

		setNavOpen( false );
	};

	toggle.addEventListener( 'click', () => {
		const isOpen = header.classList.contains( 'somvio-header--nav-open' );
		setNavOpen( ! isOpen );
	} );

	if ( backdrop ) {
		backdrop.addEventListener( 'click', () => {
			setNavOpen( false );
		} );
	}

	nav.addEventListener( 'click', ( event ) => {
		if ( mqDesktop.matches ) {
			return;
		}

		const link = event.target.closest( '.menu-item-has-children > a' );
		if ( ! link || ! nav.contains( link ) ) {
			return;
		}

		const item = link.parentElement;
		const submenu = item && item.querySelector( ':scope > .sub-menu' );
		if ( ! item || ! submenu ) {
			return;
		}

		event.preventDefault();

		const willOpen = ! item.classList.contains( 'is-submenu-open' );

		nav.querySelectorAll( '.menu-item-has-children.is-submenu-open' ).forEach( ( openItem ) => {
			if ( openItem !== item ) {
				openItem.classList.remove( 'is-submenu-open' );
				const other = openItem.querySelector( ':scope > a' );
				if ( other ) {
					other.setAttribute( 'aria-expanded', 'false' );
				}
			}
		} );

		item.classList.toggle( 'is-submenu-open', willOpen );
		link.setAttribute( 'aria-expanded', willOpen ? 'true' : 'false' );
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			setNavOpen( false );
		}
	} );

	if ( typeof mqDesktop.addEventListener === 'function' ) {
		mqDesktop.addEventListener( 'change', syncViewport );
	} else {
		window.addEventListener( 'resize', syncViewport );
	}

	nav.querySelectorAll( '.menu-item-has-children > a' ).forEach( ( link ) => {
		link.setAttribute( 'aria-expanded', 'false' );
		link.setAttribute( 'aria-haspopup', 'true' );
	} );

	syncViewport();
} )();
