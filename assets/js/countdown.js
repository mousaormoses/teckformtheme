/**
 * Teckform Countdown widget: updates the numbers every second and, when time is
 * up, hides the widget, shows the message or goes to another page. Evergreen
 * timers keep each visitor's deadline in localStorage. Also runs for countdowns
 * re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var LENGTHS = { days: 86400, hours: 3600, minutes: 60, seconds: 1 };
	var ORDER = [ 'days', 'hours', 'minutes', 'seconds' ];

	function isEditor() {
		return document.body.classList.contains( 'elementor-editor-active' ) ||
			!! ( window.elementorFrontend && window.elementorFrontend.isEditMode && window.elementorFrontend.isEditMode() );
	}

	// Same split as the PHP render: a hidden unit's time goes into the next smaller shown unit.
	function split( seconds, units ) {
		var values = {};

		ORDER.forEach( function ( unit ) {
			if ( -1 === units.indexOf( unit ) ) {
				return;
			}

			values[ unit ] = Math.floor( seconds / LENGTHS[ unit ] );
			seconds -= values[ unit ] * LENGTHS[ unit ];
		} );

		return values;
	}

	function pad( number ) {
		return number < 10 ? '0' + number : String( number );
	}

	// End time in milliseconds.
	function getEnd( el, editor ) {
		var duration = el.getAttribute( 'data-duration' );

		if ( null === duration ) {
			return ( parseInt( el.getAttribute( 'data-end' ), 10 ) || 0 ) * 1000;
		}

		duration = ( parseInt( duration, 10 ) || 0 ) * 1000;

		// Evergreen: the editor always previews the full time and stores nothing.
		if ( editor ) {
			return Date.now() + duration;
		}

		var key = el.getAttribute( 'data-key' );
		var end = 0;

		try {
			end = parseInt( window.localStorage.getItem( key ), 10 ) || 0;
		} catch ( e ) {
			end = 0;
		}

		if ( ! end ) {
			end = Date.now() + duration;

			try {
				window.localStorage.setItem( key, String( end ) );
			} catch ( e ) {
				// Storage blocked (private mode): the timer restarts on each visit.
			}
		}

		return end;
	}

	function initCountdown( el ) {
		if ( el.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		el.setAttribute( 'data-teckform-ready', '' );

		var editor = isEditor();
		var units = ( el.getAttribute( 'data-units' ) || '' ).split( ',' ).filter( Boolean );
		var numbers = {};
		var end = getEnd( el, editor );

		el.querySelectorAll( '.teckform-countdown__number' ).forEach( function ( number ) {
			numbers[ number.getAttribute( 'data-unit' ) ] = number;
		} );

		function finish() {
			var action = el.getAttribute( 'data-expire' );

			if ( 'message' === action ) {
				var list = el.querySelector( '.teckform-countdown__units' );
				var message = el.querySelector( '.teckform-countdown__message' );

				if ( list && message ) {
					list.hidden = true;
					message.hidden = false;
					el.classList.add( 'is-expired' );
				}

				return;
			}

			// Hiding and redirecting would get in the way while editing.
			if ( editor ) {
				return;
			}

			if ( 'hide' === action ) {
				( el.closest( '.elementor-widget' ) || el ).style.display = 'none';
			} else if ( 'redirect' === action && el.getAttribute( 'data-redirect' ) ) {
				var target = new URL( el.getAttribute( 'data-redirect' ), window.location.href );

				// Never redirect to the page we are on, which would loop forever.
				if ( target.href.split( '#' )[ 0 ] !== window.location.href.split( '#' )[ 0 ] ) {
					window.location.assign( target.href );
				}
			}
		}

		function tick() {
			// The Elementor editor replaced the widget: stop the old copy.
			if ( ! el.isConnected ) {
				return;
			}

			var left = Math.max( 0, Math.ceil( ( end - Date.now() ) / 1000 ) );
			var values = split( left, units );

			units.forEach( function ( unit ) {
				var text = pad( values[ unit ] );

				if ( numbers[ unit ] && numbers[ unit ].textContent !== text ) {
					numbers[ unit ].textContent = text;
				}
			} );

			if ( 0 === left ) {
				finish();
				return;
			}

			// Wake up just after the next whole second so the numbers change on time.
			var wait = ( end - Date.now() ) % 1000;

			window.setTimeout( tick, ( wait > 0 ? wait : 1000 ) + 20 );
		}

		tick();
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-countdown' ).forEach( initCountdown );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-countdown.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
