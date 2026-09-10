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

	const setScrollLock = ( locked ) => {
		if ( locked ) {
			const gap = Math.max( 0, window.innerWidth - document.documentElement.clientWidth );
			document.documentElement.style.setProperty( '--somvio-scrollbar-compensation', gap + 'px' );
			document.documentElement.classList.add( 'somvio-no-scroll' );
			body.classList.add( 'somvio-no-scroll' );
			return;
		}

		document.documentElement.style.removeProperty( '--somvio-scrollbar-compensation' );
		document.documentElement.classList.remove( 'somvio-no-scroll' );
		body.classList.remove( 'somvio-no-scroll' );
	};

	const getDrawerFocusable = () => {
		const drawer = header.querySelector( '.somvio-header__drawer' );
		const nodes = [];

		if ( ! mqDesktop.matches && toggle && ! toggle.hidden && toggle.getAttribute( 'aria-hidden' ) !== 'true' ) {
			nodes.push( toggle );
		}

		if ( ! drawer ) {
			return nodes;
		}

		Array.prototype.push.apply(
			nodes,
			Array.prototype.slice.call(
				drawer.querySelectorAll(
					'a[href], button:not([disabled]):not([hidden]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
				)
			).filter( ( el ) => {
				if ( el === toggle || el.hidden || el.getAttribute( 'aria-hidden' ) === 'true' ) {
					return false;
				}
				if ( el.classList.contains( 'somvio-header__drawer-close' ) ) {
					return false;
				}
				return el.offsetParent !== null || el.getClientRects().length > 0;
			} )
		);

		return nodes;
	};

	const releaseNavFocus = () => {
		const active = document.activeElement;
		if ( ! active || active === document.body ) {
			return;
		}

		const trapped = nav.contains( active )
			|| ( backdrop && ( active === backdrop || backdrop.contains( active ) ) );
		if ( ! trapped ) {
			return;
		}

		if ( toggle && ! toggle.hidden && typeof toggle.focus === 'function' ) {
			toggle.focus();
			return;
		}

		if ( typeof active.blur === 'function' ) {
			active.blur();
		}
	};

	const setNavOpen = ( isOpen ) => {
		const open = Boolean( isOpen ) && ! mqDesktop.matches;
		const wasOpen = header.classList.contains( 'somvio-header--nav-open' );

		if ( ! open ) {
			releaseNavFocus();
		}

		header.classList.toggle( 'somvio-header--nav-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', open ? labelClose : labelOpen );
		nav.setAttribute( 'aria-hidden', mqDesktop.matches ? 'false' : open ? 'false' : 'true' );

		setScrollLock( open );

		if ( 'inert' in nav ) {
			nav.inert = ! mqDesktop.matches && ! open;
		}

		if ( backdrop ) {
			backdrop.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
			backdrop.tabIndex = -1;
		}

		if ( ! open ) {
			closeSubmenus();
			if ( wasOpen && toggle && ! toggle.hidden && typeof toggle.focus === 'function' ) {
				toggle.focus();
			}
		} else if ( toggle && typeof toggle.focus === 'function' ) {
			toggle.focus();
		}
	};

	const syncViewport = () => {
		const isDesktop = mqDesktop.matches;

		if ( isDesktop && ( toggle === document.activeElement || nav.contains( document.activeElement ) ) ) {
			const firstLink = nav.querySelector( '.somvio-header__link' );
			if ( firstLink && typeof firstLink.focus === 'function' ) {
				firstLink.focus();
			} else if ( document.activeElement && typeof document.activeElement.blur === 'function' ) {
				document.activeElement.blur();
			}
		}

		toggle.hidden = isDesktop;
		toggle.setAttribute( 'aria-hidden', isDesktop ? 'true' : 'false' );
		toggle.tabIndex = isDesktop ? -1 : 0;

		if ( isDesktop ) {
			header.classList.remove( 'somvio-header--nav-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.setAttribute( 'aria-label', labelOpen );
			nav.setAttribute( 'aria-hidden', 'false' );
			if ( 'inert' in nav ) {
				nav.inert = false;
			}
			setScrollLock( false );
			closeSubmenus();
			if ( backdrop ) {
				backdrop.setAttribute( 'aria-hidden', 'true' );
				backdrop.tabIndex = -1;
			}
			return;
		}

		setNavOpen( false );
	};

	toggle.addEventListener( 'click', () => {
		setNavOpen( ! header.classList.contains( 'somvio-header--nav-open' ) );
	} );

	header.querySelectorAll( '[data-header-drawer-close]' ).forEach( ( closeBtn ) => {
		closeBtn.addEventListener( 'click', () => {
			setNavOpen( false );
		} );
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

	/* Close drawer after choosing a submenu, top-level, or drawer CTA link. */
	nav.addEventListener( 'click', ( event ) => {
		const sublink = event.target.closest( '.somvio-header__sublink' );
		if ( sublink && nav.contains( sublink ) ) {
			if ( ! mqDesktop.matches ) {
				setNavOpen( false );
				return;
			}

			closeSubmenus();
			parentLinks().forEach( ( link ) => setExpanded( link, false ) );
			return;
		}

		if ( mqDesktop.matches ) {
			return;
		}

		const parentToggle = event.target.closest( '.menu-item-has-children > a' );
		if ( parentToggle && nav.contains( parentToggle ) ) {
			return;
		}

		const closer = event.target.closest(
			'.somvio-header__link, .somvio-header__cta--drawer, .somvio-header__whatsapp'
		);
		if ( closer && nav.contains( closer ) ) {
			setNavOpen( false );
		}
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Tab' && header.classList.contains( 'somvio-header--nav-open' ) ) {
			const drawer = header.querySelector( '.somvio-header__drawer' );
			const focusable = getDrawerFocusable();
			if ( ! focusable.length ) {
				event.preventDefault();
				return;
			}

			const first = focusable[ 0 ];
			const last = focusable[ focusable.length - 1 ];
			const active = document.activeElement;
			const inside = ( toggle && ( active === toggle || toggle.contains( active ) ) )
				|| ( drawer && drawer.contains( active ) );
			if ( ! inside ) {
				event.preventDefault();
				( event.shiftKey ? last : first ).focus();
				return;
			}

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
			return;
		}

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
