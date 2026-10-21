/**
 * Teckform Pricing Table widget: the monthly / yearly switch swaps every plan's
 * price and period label. Also runs for tables re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function initTable( table ) {
		if ( table.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		table.setAttribute( 'data-teckform-ready', '' );

		var toggle = table.querySelector( '.teckform-pricing__toggle' );

		if ( ! toggle ) {
			return;
		}

		var labels = table.querySelectorAll( '.teckform-pricing__switch-label' );

		function setPeriod( period ) {
			if ( table.getAttribute( 'data-period' ) === period ) {
				return;
			}

			table.setAttribute( 'data-period', period );
			toggle.setAttribute( 'aria-checked', 'yearly' === period ? 'true' : 'false' );

			labels.forEach( function ( label ) {
				label.classList.toggle( 'is-active', label.getAttribute( 'data-period' ) === period );
			} );

			table.querySelectorAll( '.teckform-pricing__price' ).forEach( function ( price ) {
				price.querySelectorAll( '[data-' + period + ']' ).forEach( function ( el ) {
					el.textContent = el.getAttribute( 'data-' + period );
				} );

				if ( reduceMotion ) {
					return;
				}

				// Restart the swap animation.
				price.classList.remove( 'is-updating' );
				price.offsetWidth; // eslint-disable-line no-unused-expressions
				price.classList.add( 'is-updating' );
			} );
		}

		toggle.addEventListener( 'click', function () {
			setPeriod( 'true' === toggle.getAttribute( 'aria-checked' ) ? 'monthly' : 'yearly' );
		} );

		labels.forEach( function ( label ) {
			label.addEventListener( 'click', function () {
				setPeriod( label.getAttribute( 'data-period' ) );
			} );
		} );

		table.addEventListener( 'animationend', function ( event ) {
			var price = event.target.closest( '.teckform-pricing__price' );

			if ( price ) {
				price.classList.remove( 'is-updating' );
			}
		} );
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-pricing' ).forEach( initTable );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-pricing-table.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
