/**
 * Teckform Before / After widget: drag the handle with a mouse, finger or pen,
 * or move the hidden range input with the keyboard. Also runs for comparisons
 * re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	function initCompare( root ) {
		if ( root.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		root.setAttribute( 'data-teckform-ready', '' );

		var range = root.querySelector( '.teckform-before-after__range' );

		if ( ! range ) {
			return;
		}

		var vertical = root.classList.contains( 'teckform-before-after--vertical' );
		var valueText = range.getAttribute( 'data-value-text' ) || '%s%';
		var beforeLabel = root.querySelector( '.teckform-before-after__label--before' );
		var afterLabel = root.querySelector( '.teckform-before-after__label--after' );
		var position = parseFloat( root.style.getPropertyValue( '--teckform-ba-position' ) );
		var pointer = null;

		if ( isNaN( position ) ) {
			position = parseFloat( range.value ) || 50;
		}

		function isRtl() {
			return 'rtl' === window.getComputedStyle( root ).direction;
		}

		// Fade out a label once the handle passes over it.
		function updateLabels() {
			var box = root.getBoundingClientRect();
			var size = vertical ? box.height : box.width;
			var handle = position / 100 * size;
			var rtl = isRtl();
			var rect;
			var edge;

			if ( beforeLabel ) {
				rect = beforeLabel.getBoundingClientRect();

				if ( vertical ) {
					edge = rect.bottom - box.top;
				} else {
					edge = rtl ? box.right - rect.left : rect.right - box.left;
				}

				beforeLabel.classList.toggle( 'is-covered', edge > handle );
			}

			if ( afterLabel ) {
				rect = afterLabel.getBoundingClientRect();

				if ( vertical ) {
					edge = rect.top - box.top;
				} else {
					edge = rtl ? box.right - rect.right : rect.left - box.left;
				}

				afterLabel.classList.toggle( 'is-covered', edge < handle );
			}
		}

		function set( value ) {
			position = Math.max( 0, Math.min( 100, value ) );
			root.style.setProperty( '--teckform-ba-position', position + '%' );
			range.value = String( Math.round( position ) );
			range.setAttribute( 'aria-valuetext', valueText.replace( '%s', Math.round( position ) ) );
			updateLabels();
		}

		// Position from the start edge (left, or right on right-to-left sites) or the top.
		function fromPointer( event ) {
			var box = root.getBoundingClientRect();
			var ratio;

			if ( vertical ) {
				ratio = ( event.clientY - box.top ) / box.height;
			} else if ( isRtl() ) {
				ratio = ( box.right - event.clientX ) / box.width;
			} else {
				ratio = ( event.clientX - box.left ) / box.width;
			}

			set( ratio * 100 );
		}

		root.addEventListener( 'pointerdown', function ( event ) {
			if ( 'mouse' === event.pointerType && 0 !== event.button ) {
				return;
			}

			pointer = event.pointerId;
			root.setPointerCapture( event.pointerId );
			root.classList.add( 'is-dragging' );

			// A mouse or pen click jumps straight there. A finger waits until it
			// moves, so starting to scroll the page doesn't move the handle.
			if ( 'touch' !== event.pointerType ) {
				event.preventDefault();
				fromPointer( event );
				range.focus( { preventScroll: true } );
			}
		} );

		root.addEventListener( 'pointermove', function ( event ) {
			if ( pointer === event.pointerId ) {
				fromPointer( event );
			}
		} );

		function release( event ) {
			if ( pointer === event.pointerId ) {
				pointer = null;
				root.classList.remove( 'is-dragging' );
			}
		}

		root.addEventListener( 'pointerup', release );
		root.addEventListener( 'pointercancel', release );

		// Arrow keys follow the handle on screen: in a top-and-bottom comparison
		// Down shows more of the Before image; on right-to-left sites Left does.
		range.addEventListener( 'keydown', function ( event ) {
			var step = event.shiftKey ? 10 : 1;
			var rtl = isRtl();
			var delta;

			switch ( event.key ) {
				case 'ArrowRight':
					delta = vertical || ! rtl ? step : -step;
					break;
				case 'ArrowLeft':
					delta = vertical || ! rtl ? -step : step;
					break;
				case 'ArrowDown':
					delta = vertical ? step : -step;
					break;
				case 'ArrowUp':
					delta = vertical ? -step : step;
					break;
				case 'PageUp':
					delta = 10;
					break;
				case 'PageDown':
					delta = -10;
					break;
				case 'Home':
					delta = -100;
					break;
				case 'End':
					delta = 100;
					break;
				default:
					return;
			}

			event.preventDefault();
			set( position + delta );
		} );

		// Screen readers and other assistive tools may set the value directly.
		range.addEventListener( 'input', function () {
			set( parseFloat( range.value ) );
		} );

		if ( 'ResizeObserver' in window ) {
			var observer = new ResizeObserver( function () {
				if ( ! root.isConnected ) {
					observer.disconnect();
					return;
				}

				updateLabels();
			} );

			observer.observe( root );
		}

		set( position );
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-before-after' ).forEach( initCompare );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-before-after.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
