/**
 * Somvio header — sticky scroll state, responsive nav drawer & Services dropdown.
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
	const mqFinePointer = window.matchMedia( '(hover: hover) and (pointer: fine)' );
	const body = document.body;

	if ( ! toggle || ! nav ) {
		return;
	}

	const labelOpen = toggle.getAttribute( 'aria-label' ) || 'Open menu';
	const labelClose = 'Close menu';

	const parentLinks = () => nav.querySelectorAll( '.menu-item-has-children > a' );

	const setExpanded = ( link, expanded ) => {
		if ( link ) {
			link.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
		}
	};

	const closeSubmenus = ( exceptItem ) => {
		nav.querySelectorAll( '.menu-item-has-children.is-submenu-open' ).forEach( ( item ) => {
			if ( exceptItem && item === exceptItem ) {
				return;
			}
			item.classList.remove( 'is-submenu-open' );
			setExpanded( item.querySelector( ':scope > a' ), false );
		} );
	};

	const toggleSubmenu = ( item, forceOpen ) => {
		if ( ! item ) {
			return;
		}

		const link = item.querySelector( ':scope > a' );
		const submenu = item.querySelector( ':scope > .sub-menu' );
		if ( ! link || ! submenu ) {
			return;
		}

		const willOpen = typeof forceOpen === 'boolean'
			? forceOpen
			: ! item.classList.contains( 'is-submenu-open' );

		closeSubmenus( item );
		item.classList.toggle( 'is-submenu-open', willOpen );
		setExpanded( link, willOpen );
	};

	const getTopLevelParent = ( node ) => {
		if ( ! node || ! node.closest ) {
			return null;
		}
		const item = node.closest( '.somvio-header__menu > .menu-item-has-children' );
		return item && nav.contains( item ) ? item : null;
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
			closeSubmenus();
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
			closeSubmenus();
			if ( backdrop ) {
				backdrop.setAttribute( 'aria-hidden', 'true' );
			}
			return;
		}

		setNavOpen( false );
	};

	toggle.addEventListener( 'click', () => {
		setNavOpen( ! header.classList.contains( 'somvio-header--nav-open' ) );
	} );

	if ( backdrop ) {
		backdrop.addEventListener( 'click', () => {
			setNavOpen( false );
		} );
	}

	/* Parent link: expand/collapse submenu (mobile drawer + coarse desktop pointer). */
	nav.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( '.menu-item-has-children > a' );
		if ( ! link || ! nav.contains( link ) ) {
			return;
		}

		const useClickToggle = ! mqDesktop.matches || ! mqFinePointer.matches;
		if ( ! useClickToggle ) {
			return;
		}

		event.preventDefault();
		toggleSubmenu( link.parentElement );
	} );

	/* Fine-pointer desktop: mirror CSS hover into aria-expanded. */
	nav.addEventListener( 'mouseover', ( event ) => {
		if ( ! mqDesktop.matches || ! mqFinePointer.matches ) {
			return;
		}
		const item = getTopLevelParent( event.target );
		if ( item ) {
			setExpanded( item.querySelector( ':scope > a' ), true );
		}
	} );

	nav.addEventListener( 'mouseout', ( event ) => {
		if ( ! mqDesktop.matches || ! mqFinePointer.matches ) {
			return;
		}
		const item = getTopLevelParent( event.target );
		if ( ! item ) {
			return;
		}
		const related = event.relatedTarget;
		if ( related && item.contains( related ) ) {
			return;
		}
		if ( ! item.classList.contains( 'is-submenu-open' ) ) {
			setExpanded( item.querySelector( ':scope > a' ), false );
		}
	} );

	nav.addEventListener( 'focusin', ( event ) => {
		const item = getTopLevelParent( event.target );
		if ( item ) {
			setExpanded( item.querySelector( ':scope > a' ), true );
		}
	} );

	nav.addEventListener( 'focusout', ( event ) => {
		const item = getTopLevelParent( event.target );
		if ( ! item ) {
			return;
		}
		requestAnimationFrame( () => {
			if ( ! item.contains( document.activeElement ) && ! item.classList.contains( 'is-submenu-open' ) ) {
				setExpanded( item.querySelector( ':scope > a' ), false );
			}
		} );
	} );

	/* Close drawer after choosing a submenu link. */
	nav.addEventListener( 'click', ( event ) => {
		const sublink = event.target.closest( '.somvio-header__sublink' );
		if ( ! sublink || ! nav.contains( sublink ) ) {
			return;
		}

		if ( ! mqDesktop.matches ) {
			setNavOpen( false );
			return;
		}

		closeSubmenus();
		parentLinks().forEach( ( link ) => setExpanded( link, false ) );
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Escape' ) {
			return;
		}
		setNavOpen( false );
		closeSubmenus();
		parentLinks().forEach( ( link ) => setExpanded( link, false ) );
	} );

	document.addEventListener( 'click', ( event ) => {
		if ( ! mqDesktop.matches || mqFinePointer.matches ) {
			return;
		}
		if ( nav.contains( event.target ) ) {
			return;
		}
		closeSubmenus();
	} );

	if ( typeof mqDesktop.addEventListener === 'function' ) {
		mqDesktop.addEventListener( 'change', syncViewport );
	} else {
		window.addEventListener( 'resize', syncViewport );
	}

	parentLinks().forEach( ( link ) => {
		link.setAttribute( 'aria-expanded', link.getAttribute( 'aria-expanded' ) || 'false' );
		link.setAttribute( 'aria-haspopup', 'true' );
	} );

	syncViewport();
} )();
