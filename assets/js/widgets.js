/**
 * Teckform widgets: mobile menu and full-screen search.
 *
 * Uses event delegation so it also works for widgets re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	function setOpen( root, open, toggleSelector ) {
		var toggle = root.querySelector( toggleSelector );

		root.classList.toggle( 'is-open', open );
		document.body.classList.toggle( 'teckform-no-scroll', open );

		if ( toggle ) {
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}
	}

	function openNav( nav ) {
		var close = nav.querySelector( '.teckform-nav__close' );

		setOpen( nav, true, '.teckform-nav__toggle' );

		if ( close ) {
			close.focus();
		}
	}

	function closeNav( nav, returnFocus ) {
		var toggle = nav.querySelector( '.teckform-nav__toggle' );

		setOpen( nav, false, '.teckform-nav__toggle' );

		if ( returnFocus && toggle ) {
			toggle.focus();
		}
	}

	function openSearch( search ) {
		var input = search.querySelector( '.teckform-search__input' );

		setOpen( search, true, '.teckform-search__toggle' );

		if ( input ) {
			input.focus();
		}
	}

	function closeSearch( search ) {
		var toggle = search.querySelector( '.teckform-search__toggle' );

		setOpen( search, false, '.teckform-search__toggle' );

		if ( toggle ) {
			toggle.focus();
		}
	}

	// Line a mega menu up with the header content, the screen, or center it under its item.
	function positionMega( item ) {
		var mega = item.querySelector( '.teckform-nav__mega' );

		if ( ! mega || 'absolute' !== window.getComputedStyle( mega ).position ) {
			return;
		}

		var nav = item.closest( '.teckform-nav' );
		var mode = nav.getAttribute( 'data-mega-width' ) || 'container';
		var itemRect = item.getBoundingClientRect();
		var viewport = document.documentElement.clientWidth;
		var left;
		var width;

		if ( 'full' === mode ) {
			left = 0;
			width = viewport;
		} else if ( 'container' === mode ) {
			var box = nav.closest( '.e-con-inner, .elementor-container, .e-con, .teckform-header' ) || nav;
			var boxRect = box.getBoundingClientRect();

			left = boxRect.left;
			width = boxRect.width;
		} else {
			width = Math.min( mega.offsetWidth, viewport - 32 );
			left = Math.max( 16, Math.min( itemRect.left + ( itemRect.width - width ) / 2, viewport - width - 16 ) );
		}

		mega.style.left = Math.round( left - itemRect.left ) + 'px';

		if ( 'custom' !== mode ) {
			mega.style.width = Math.round( width ) + 'px';
		}
	}

	function onMegaHover( event ) {
		var item = event.target.closest && event.target.closest( '.teckform-has-mega' );

		if ( item ) {
			positionMega( item );
		}
	}

	document.addEventListener( 'mouseover', onMegaHover );
	document.addEventListener( 'focusin', onMegaHover );

	document.addEventListener( 'click', function ( event ) {
		var target = event.target;
		var el;

		// Clicking outside a menu closes any submenu opened with its arrow button.
		if ( ! target.closest( '.teckform-nav' ) ) {
			document.querySelectorAll( '.teckform-nav__menu li.is-open' ).forEach( function ( item ) {
				item.classList.remove( 'is-open' );
				item.querySelector( '.teckform-nav__sub-toggle' ).setAttribute( 'aria-expanded', 'false' );
			} );
		}

		if ( ( el = target.closest( '.teckform-nav__toggle' ) ) ) {
			openNav( el.closest( '.teckform-nav' ) );
		} else if ( ( el = target.closest( '.teckform-nav__close, .teckform-nav__overlay' ) ) ) {
			closeNav( el.closest( '.teckform-nav' ), true );
		} else if ( ( el = target.closest( '.teckform-nav__sub-toggle' ) ) ) {
			el.setAttribute( 'aria-expanded', el.parentElement.classList.toggle( 'is-open' ) ? 'true' : 'false' );
			positionMega( el.parentElement );
		} else if ( ( el = target.closest( '.teckform-nav.is-open a' ) ) ) {
			// Links to a section on the same page should not leave the menu open.
			closeNav( el.closest( '.teckform-nav' ), false );
		} else if ( ( el = target.closest( '.teckform-search__toggle' ) ) ) {
			openSearch( el.closest( '.teckform-search' ) );
		} else if ( target.closest( '.teckform-search__close' ) || target.classList.contains( 'teckform-search__overlay' ) ) {
			closeSearch( target.closest( '.teckform-search' ) );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}

		document.querySelectorAll( '.teckform-nav.is-open' ).forEach( function ( nav ) {
			closeNav( nav, true );
		} );
		document.querySelectorAll( '.teckform-search.is-open' ).forEach( closeSearch );
	} );
}() );
