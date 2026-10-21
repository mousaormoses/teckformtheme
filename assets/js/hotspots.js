/**
 * Teckform Image Hotspots widget: opens tooltips on hover or click (a tap on
 * touch screens), closes them with Escape or a click elsewhere, and places each
 * tooltip on a side with room, slid along the edge so it stays inside the image.
 * Also runs for widgets re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var EDGE = 8; // Minimum gap between a tooltip and the image edge, in pixels.
	var OPPOSITE = {
		top: 'bottom',
		bottom: 'top',
		start: 'end',
		end: 'start',
	};

	function marker( spot ) {
		return spot.querySelector( '.teckform-hotspots__marker' );
	}

	function tooltip( spot ) {
		return spot.querySelector( '.teckform-hotspots__tooltip' );
	}

	function setPosition( spot, position ) {
		Object.keys( OPPOSITE ).forEach( function ( key ) {
			spot.classList.toggle( 'is-pos-' + key, key === position );
		} );
	}

	// How much room is left on a side; negative means the tooltip sticks out.
	function roomFor( position, tip, box ) {
		if ( 'top' === position ) {
			return Math.min( tip.top - box.top - EDGE, box.width - tip.width - 2 * EDGE );
		}

		if ( 'bottom' === position ) {
			return Math.min( box.bottom - tip.bottom - EDGE, box.width - tip.width - 2 * EDGE );
		}

		return Math.min( tip.left - box.left - EDGE, box.right - tip.right - EDGE, box.height - tip.height - 2 * EDGE );
	}

	function place( spot ) {
		var tip = tooltip( spot );
		var root = spot.closest( '.teckform-hotspots' );
		var inner = spot.closest( '.teckform-hotspots__inner' );

		if ( ! tip || ! inner ) {
			return;
		}

		var box = inner.getBoundingClientRect();
		var preferred = spot.getAttribute( 'data-position' );
		var order = OPPOSITE[ preferred ] ? [ preferred, OPPOSITE[ preferred ], 'top', 'bottom' ] : [ 'top', 'bottom', 'end', 'start' ];
		var chosen = null;
		var best = order[ 0 ];
		var bestRoom = -Infinity;

		// Tooltips are never wider than the image.
		root.style.setProperty( '--teckform-hotspots-room', box.width + 'px' );
		tip.style.setProperty( '--teckform-hotspot-shift', '0px' );

		for ( var i = 0; i < order.length; i++ ) {
			setPosition( spot, order[ i ] );

			var room = roomFor( order[ i ], tip.getBoundingClientRect(), box );

			if ( room >= 0 ) {
				chosen = order[ i ];
				break;
			}

			if ( room > bestRoom ) {
				bestRoom = room;
				best = order[ i ];
			}
		}

		chosen = chosen || best;
		setPosition( spot, chosen );

		// Slide along the edge if it still sticks out at one end.
		var rect = tip.getBoundingClientRect();
		var shift = 0;

		if ( 'top' === chosen || 'bottom' === chosen ) {
			if ( rect.left < box.left + EDGE ) {
				shift = box.left + EDGE - rect.left;
			} else if ( rect.right > box.right - EDGE ) {
				shift = box.right - EDGE - rect.right;
			}
		} else if ( rect.top < box.top + EDGE ) {
			shift = box.top + EDGE - rect.top;
		} else if ( rect.bottom > box.bottom - EDGE ) {
			shift = box.bottom - EDGE - rect.bottom;
		}

		tip.style.setProperty( '--teckform-hotspot-shift', Math.round( shift ) + 'px' );
	}

	function close( spot ) {
		var button = marker( spot );

		spot.classList.remove( 'is-open' );

		if ( button && button.hasAttribute( 'aria-expanded' ) ) {
			button.setAttribute( 'aria-expanded', 'false' );
		}
	}

	function open( spot ) {
		if ( ! tooltip( spot ) ) {
			return;
		}

		spot.closest( '.teckform-hotspots' ).querySelectorAll( '.teckform-hotspots__spot.is-open' ).forEach( function ( other ) {
			if ( other !== spot ) {
				close( other );
			}
		} );

		place( spot );
		spot.classList.add( 'is-open' );
		marker( spot ).setAttribute( 'aria-expanded', 'true' );
	}

	function initWidget( root ) {
		if ( root.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		root.setAttribute( 'data-teckform-ready', '' );

		var hover = 'hover' === root.getAttribute( 'data-trigger' );
		var inner = root.querySelector( '.teckform-hotspots__inner' );

		root.querySelectorAll( '.teckform-hotspots__spot' ).forEach( function ( spot ) {
			var button = marker( spot );
			var closeTimer = null;
			var pointerType = '';

			if ( ! button || ! tooltip( spot ) ) {
				return;
			}

			button.addEventListener( 'pointerdown', function ( event ) {
				pointerType = event.pointerType;
			} );

			button.addEventListener( 'click', function () {
				var type = pointerType;

				pointerType = '';

				// Hover mode with a mouse: hovering already opened it, so a click keeps it open.
				if ( hover && 'mouse' === type ) {
					open( spot );
					return;
				}

				if ( spot.classList.contains( 'is-open' ) ) {
					close( spot );
				} else {
					open( spot );
				}
			} );

			if ( hover ) {
				spot.addEventListener( 'pointerenter', function ( event ) {
					if ( 'mouse' === event.pointerType ) {
						window.clearTimeout( closeTimer );
						open( spot );
					}
				} );

				spot.addEventListener( 'pointerleave', function ( event ) {
					// Stay open while a keyboard user is inside it (a mouse click's focus doesn't count).
					if ( 'mouse' === event.pointerType && ! spot.querySelector( ':focus-visible' ) ) {
						closeTimer = window.setTimeout( close.bind( null, spot ), 150 );
					}
				} );

				// Keyboard focus opens it like hover does. Not for taps, which use click.
				button.addEventListener( 'focus', function () {
					// Skipped once when Escape hands focus back to the marker.
					if ( spot.hasAttribute( 'data-skip-focus' ) ) {
						spot.removeAttribute( 'data-skip-focus' );
						return;
					}

					if ( button.matches( ':focus-visible' ) ) {
						open( spot );
					}
				} );
			}

			// Tabbing out of a spot closes its tooltip.
			spot.addEventListener( 'focusout', function ( event ) {
				if ( event.relatedTarget && ! spot.contains( event.relatedTarget ) ) {
					close( spot );
				}
			} );
		} );

		// Keep open tooltips inside the image when it changes size.
		if ( 'ResizeObserver' in window && inner ) {
			var observer = new ResizeObserver( function () {
				if ( ! root.isConnected ) {
					observer.disconnect();
					return;
				}

				root.querySelectorAll( '.teckform-hotspots__spot.is-open' ).forEach( place );
			} );

			observer.observe( inner );
		}
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-hotspots' ).forEach( initWidget );
	}

	initAll();

	// Escape closes the open tooltip and puts focus back on its marker.
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}

		document.querySelectorAll( '.teckform-hotspots__spot.is-open' ).forEach( function ( spot ) {
			var hadFocus = spot.contains( document.activeElement );

			close( spot );

			if ( hadFocus ) {
				spot.setAttribute( 'data-skip-focus', '' );
				marker( spot ).focus();
				spot.removeAttribute( 'data-skip-focus' );
			}
		} );
	} );

	// A click or tap outside a spot closes its tooltip.
	document.addEventListener( 'click', function ( event ) {
		document.querySelectorAll( '.teckform-hotspots__spot.is-open' ).forEach( function ( spot ) {
			if ( ! spot.contains( event.target ) ) {
				close( spot );
			}
		} );
	} );

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-hotspots.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
