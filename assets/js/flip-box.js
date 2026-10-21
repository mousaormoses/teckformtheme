/**
 * Teckform Flip Box widget: on touch screens, where there is no hover, a tap
 * turns the box over and a second tap turns it back. A tap on the back's button
 * follows the link. Mouse hover and keyboard focus (the box itself, or the back's
 * button) are handled by CSS.
 * Also runs for boxes re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var noHover = window.matchMedia( '(hover: none)' );

	function initBox( box ) {
		if ( box.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		box.setAttribute( 'data-teckform-ready', '' );

		box.addEventListener( 'click', function ( event ) {
			if ( ! noHover.matches ) {
				return;
			}

			var flipped = box.classList.contains( 'is-flipped' );
			var link = event.target.closest( 'a, button' );

			// The back is showing: let the button do its job.
			if ( link && flipped ) {
				return;
			}

			// A tap that reached the hidden back's button shows the back first.
			if ( link ) {
				event.preventDefault();
			}

			box.classList.toggle( 'is-flipped', ! flipped );
		} );
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-flip-box' ).forEach( initBox );
	}

	initAll();

	// A tap anywhere else turns open boxes back to the front.
	document.addEventListener( 'click', function ( event ) {
		document.querySelectorAll( '.teckform-flip-box.is-flipped' ).forEach( function ( box ) {
			if ( ! box.contains( event.target ) ) {
				box.classList.remove( 'is-flipped' );
			}
		} );
	} );

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-flip-box.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
