/**
 * Teckform Portfolio Grid widget: filters projects by category without
 * reloading the page. Projects that leave fade and shrink out, the rest glide
 * to their new places, and new ones fade in. Also runs for grids re-rendered
 * in the Elementor editor.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var DURATION = 300;

	function initPortfolio( root ) {
		if ( root.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		root.setAttribute( 'data-teckform-ready', '' );

		var buttons = Array.prototype.slice.call( root.querySelectorAll( '.teckform-portfolio__filter' ) );
		var items = Array.prototype.slice.call( root.querySelectorAll( '.teckform-portfolio__item' ) );
		var status = root.querySelector( '.teckform-portfolio__status' );
		var template = root.getAttribute( 'data-status' ) || '';
		var timer = null;

		if ( ! buttons.length ) {
			return;
		}

		function matches( item, filter ) {
			var terms = ' ' + ( item.getAttribute( 'data-terms' ) || '' ) + ' ';

			return '*' === filter || -1 !== terms.indexOf( ' ' + filter + ' ' );
		}

		// Slide items that stay from where they were to where they are now.
		function glide( staying, before ) {
			if ( ! staying[ 0 ] || 'function' !== typeof staying[ 0 ].animate ) {
				return;
			}

			staying.forEach( function ( item, i ) {
				var after = item.getBoundingClientRect();
				var x = before[ i ].left - after.left;
				var y = before[ i ].top - after.top;

				if ( x || y ) {
					item.animate(
						[ { transform: 'translate(' + x + 'px, ' + y + 'px)' }, { transform: 'none' } ],
						{ duration: DURATION, easing: 'cubic-bezier(0.2, 0.7, 0.2, 1)' }
					);
				}
			} );
		}

		function apply( filter ) {
			var leaving = [];
			var entering = [];
			var staying = [];
			var count = 0;

			buttons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.getAttribute( 'data-filter' ) === filter ? 'true' : 'false' );
			} );

			items.forEach( function ( item ) {
				if ( matches( item, filter ) ) {
					count++;

					if ( item.hidden ) {
						entering.push( item );
					} else {
						item.classList.remove( 'is-out' );
						staying.push( item );
					}
				} else if ( ! item.hidden ) {
					item.classList.add( 'is-out' );
					leaving.push( item );
				}
			} );

			if ( status && template ) {
				status.textContent = template.replace( '%d', count );
			}

			window.clearTimeout( timer );

			// Second step, once the leaving items have faded: take them out of the
			// layout and bring the new ones in.
			function swap() {
				// The Elementor editor replaces the widget on every change; skip the old copy.
				if ( ! root.isConnected ) {
					return;
				}

				var before = staying.map( function ( item ) {
					return item.getBoundingClientRect();
				} );

				leaving.forEach( function ( item ) {
					item.hidden = true;
				} );

				entering.forEach( function ( item ) {
					item.hidden = false;

					if ( ! reduceMotion ) {
						item.classList.add( 'is-out' );
					}
				} );

				if ( reduceMotion ) {
					leaving.concat( entering ).forEach( function ( item ) {
						item.classList.remove( 'is-out' );
					} );
					return;
				}

				glide( staying, before );

				root.offsetWidth; // Start the fade-in from the hidden state.

				entering.forEach( function ( item ) {
					item.classList.remove( 'is-out' );
				} );
			}

			if ( reduceMotion || ! leaving.length ) {
				swap();
			} else {
				timer = window.setTimeout( swap, DURATION );
			}
		}

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				apply( button.getAttribute( 'data-filter' ) );
			} );
		} );

		// Arrow keys move between the filter buttons, as in a toolbar.
		root.querySelector( '.teckform-portfolio__filters' ).addEventListener( 'keydown', function ( event ) {
			var index = buttons.indexOf( document.activeElement );
			var rtl = 'rtl' === window.getComputedStyle( root ).direction;
			var step = 0;

			if ( -1 === index ) {
				return;
			}

			if ( 'ArrowRight' === event.key ) {
				step = rtl ? -1 : 1;
			} else if ( 'ArrowLeft' === event.key ) {
				step = rtl ? 1 : -1;
			} else if ( 'Home' === event.key ) {
				step = -index;
			} else if ( 'End' === event.key ) {
				step = buttons.length - 1 - index;
			}

			if ( step ) {
				event.preventDefault();
				buttons[ ( index + step + buttons.length ) % buttons.length ].focus();
			}
		} );
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-portfolio' ).forEach( initPortfolio );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-portfolio.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
