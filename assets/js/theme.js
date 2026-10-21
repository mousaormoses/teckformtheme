/**
 * Teckform theme: header scroll behavior, back-to-top button and dark mode toggle.
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var header = document.querySelector( '.teckform-header--sticky, .teckform-header--transparent' );
	var toTop = document.querySelector( '.teckform-back-to-top' );
	var footer = document.querySelector( '.teckform-footer, .site-footer' );
	var lastY = window.scrollY;

	// Hide-on-scroll only applies while the header is actually stuck to the top (sticky can be desktop-only).
	function isStuck() {
		var position = window.getComputedStyle( header ).position;

		return 'sticky' === position || 'fixed' === position;
	}

	function onScroll() {
		var y = window.scrollY;

		if ( header ) {
			header.classList.toggle( 'is-scrolled', y > 10 );

			if ( header.classList.contains( 'teckform-header--hide-on-scroll' ) ) {
				var goingDown = y > lastY && y > header.offsetHeight;
				var menuOpen = header.querySelector( '.is-open' );

				header.classList.toggle( 'is-hidden', goingDown && ! menuOpen && isStuck() );
			}
		}

		if ( toTop ) {
			toTop.classList.toggle( 'is-visible', y > 400 );

			// Ride above the footer once it scrolls into view, so the button never covers footer links or toggles.
			if ( footer ) {
				var overlap = window.innerHeight - footer.getBoundingClientRect().top;

				toTop.style.setProperty( '--teckform-to-top-lift', Math.max( 0, overlap ) + 'px' );
			}
		}

		lastY = y;
	}

	if ( header || toTop ) {
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll );
		onScroll();
	}

	if ( toTop ) {
		toTop.addEventListener( 'click', function () {
			var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

			window.scrollTo( { top: 0, behavior: reduceMotion ? 'auto' : 'smooth' } );
		} );
	}

	// Dark mode. The initial mode is set by an inline script in <head>.
	function isDark() {
		return 'dark' === root.getAttribute( 'data-teckform-theme' );
	}

	function setMode( mode ) {
		root.setAttribute( 'data-teckform-theme', mode );
		document.querySelectorAll( '.teckform-dark-toggle' ).forEach( function ( toggle ) {
			toggle.setAttribute( 'aria-pressed', 'dark' === mode ? 'true' : 'false' );
		} );
	}

	if ( root.hasAttribute( 'data-teckform-theme' ) ) {
		setMode( isDark() ? 'dark' : 'light' );

		document.addEventListener( 'click', function ( event ) {
			if ( ! event.target.closest( '.teckform-dark-toggle' ) ) {
				return;
			}

			var mode = isDark() ? 'light' : 'dark';

			setMode( mode );

			try {
				window.localStorage.setItem( 'teckform-theme', mode );
			} catch ( e ) {}
		} );

		// Follow the device setting live, until the visitor picks a mode themselves.
		if ( 'auto' === window.teckformDarkDefault ) {
			window.matchMedia( '(prefers-color-scheme: dark)' ).addEventListener( 'change', function ( event ) {
				var saved = null;

				try {
					saved = window.localStorage.getItem( 'teckform-theme' );
				} catch ( e ) {}

				if ( ! saved ) {
					setMode( event.matches ? 'dark' : 'light' );
				}
			} );
		}
	}
}() );
