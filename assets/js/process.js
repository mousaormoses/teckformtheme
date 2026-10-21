/**
 * Teckform Process / Timeline widget: steps fade in and their connector line
 * draws as they scroll into view. Also runs for widgets re-rendered in the
 * Elementor editor. Does nothing for visitors who prefer reduced motion.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function initProcess( list ) {
		if ( list.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		list.setAttribute( 'data-teckform-ready', '' );

		if ( reduceMotion || ! list.classList.contains( 'teckform-process--animate' ) || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var steps = list.querySelectorAll( '.teckform-process__step' );

		// Hide the steps only now that JS is running, so they never stay hidden without it.
		list.classList.add( 'is-observing' );

		var observer = new IntersectionObserver( function ( entries ) {
			var shown = 0;

			// The editor replaced the widget: stop watching the old copy.
			if ( ! list.isConnected ) {
				observer.disconnect();
				return;
			}

			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) {
					return;
				}

				// Steps that appear together come in one after another.
				entry.target.style.setProperty( '--teckform-process-delay', ( shown * 0.12 ) + 's' );
				entry.target.classList.add( 'is-visible' );
				observer.unobserve( entry.target );
				shown++;
			} );
		}, { threshold: 0.2, rootMargin: '0px 0px -40px 0px' } );

		steps.forEach( function ( step ) {
			observer.observe( step );
		} );
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-process' ).forEach( initProcess );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-process.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
