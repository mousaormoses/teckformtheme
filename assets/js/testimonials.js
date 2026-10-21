/**
 * Teckform Testimonials Carousel widget: arrows, dots, autoplay, mouse dragging
 * and keyboard control on top of a native scroll-snap row. Touch swiping is the
 * browser's own scrolling. Also runs for carousels re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function initCarousel( root ) {
		if ( root.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		root.setAttribute( 'data-teckform-ready', '' );

		var track = root.querySelector( '.teckform-testimonials__track' );

		if ( ! track ) {
			return;
		}

		var slides = Array.prototype.slice.call( track.children );
		var prev = root.querySelector( '.teckform-testimonials__arrow--prev' );
		var next = root.querySelector( '.teckform-testimonials__arrow--next' );
		var dotsBox = root.querySelector( '.teckform-testimonials__dots' );
		var dotLabel = root.getAttribute( 'data-dot-label' ) || '%d';
		var loop = '1' === root.getAttribute( 'data-loop' );
		var autoplay = '1' === root.getAttribute( 'data-autoplay' ) && ! reduceMotion;
		var pauseOnHover = '1' === root.getAttribute( 'data-pause-hover' );
		var interval = parseInt( root.getAttribute( 'data-interval' ), 10 ) || 6000;
		var behavior = reduceMotion ? 'auto' : 'smooth';
		var index = 0;
		var perView = 1;
		var dots = [];
		var timer = null;
		var hovered = false;
		var focused = false;
		var scrollTimer = null;
		var drag = null;
		var suppressClick = false;

		function isRtl() {
			return 'rtl' === window.getComputedStyle( track ).direction;
		}

		// Slides per view comes from the responsive control, through a CSS variable.
		function readPerView() {
			var value = parseInt( window.getComputedStyle( root ).getPropertyValue( '--teckform-testimonials-per-view' ), 10 );

			return Math.max( 1, Math.min( slides.length, value || 1 ) );
		}

		function lastIndex() {
			return Math.max( 0, slides.length - perView );
		}

		// How far the track must scroll to line this slide up with its start edge.
		// Physical pixels, so it works the same with scrollBy() in both directions.
		function offsetOf( slide ) {
			var trackRect = track.getBoundingClientRect();
			var slideRect = slide.getBoundingClientRect();

			return isRtl() ? slideRect.right - trackRect.right : slideRect.left - trackRect.left;
		}

		function atEnd() {
			return track.scrollWidth - track.clientWidth - Math.abs( track.scrollLeft ) < 2;
		}

		// The slide currently lined up with the start edge.
		function currentIndex() {
			var best = 0;
			var bestDistance = Infinity;

			slides.forEach( function ( slide, i ) {
				var distance = Math.abs( offsetOf( slide ) );

				if ( distance < bestDistance ) {
					best = i;
					bestDistance = distance;
				}
			} );

			if ( atEnd() ) {
				best = lastIndex();
			}

			return Math.min( best, lastIndex() );
		}

		function update() {
			dots.forEach( function ( dot, i ) {
				dot.setAttribute( 'aria-current', i === index ? 'true' : 'false' );
			} );

			if ( ! loop ) {
				if ( prev ) {
					prev.disabled = index <= 0;
				}

				if ( next ) {
					next.disabled = index >= lastIndex();
				}
			}
		}

		function goTo( target, instant ) {
			var last = lastIndex();

			if ( target > last ) {
				target = loop ? 0 : last;
			} else if ( target < 0 ) {
				target = loop ? last : 0;
			}

			index = target;
			track.scrollBy( { left: offsetOf( slides[ target ] ), behavior: instant ? 'auto' : behavior } );
			update();
		}

		// One dot per position the row can stop at.
		function buildDots() {
			if ( ! dotsBox ) {
				return;
			}

			var count = lastIndex() + 1;

			if ( count === dots.length ) {
				return;
			}

			dotsBox.innerHTML = '';
			dots = [];

			for ( var i = 0; i < count; i++ ) {
				var dot = document.createElement( 'button' );

				dot.type = 'button';
				dot.className = 'teckform-testimonials__dot';
				dot.setAttribute( 'aria-label', dotLabel.replace( '%d', i + 1 ) );
				dot.addEventListener( 'click', goTo.bind( null, i, false ) );
				dotsBox.appendChild( dot );
				dots.push( dot );
			}
		}

		function layout() {
			perView = readPerView();
			root.classList.toggle( 'is-static', lastIndex() === 0 );
			buildDots();
			index = Math.min( index, lastIndex() );
			update();
		}

		/* Autoplay ------------------------------------------------------ */

		function stop() {
			window.clearInterval( timer );
			timer = null;
		}

		function start() {
			stop();

			if ( ! autoplay || lastIndex() === 0 ) {
				return;
			}

			timer = window.setInterval( function () {
				// The Elementor editor replaces the widget on every change; stop the old copy.
				if ( ! root.isConnected ) {
					cleanUp();
					return;
				}

				if ( document.hidden || focused || ( pauseOnHover && hovered ) || drag ) {
					return;
				}

				if ( ! loop && index >= lastIndex() ) {
					stop();
					return;
				}

				goTo( index + 1 );
			}, interval );
		}

		/* Events -------------------------------------------------------- */

		function step( direction ) {
			goTo( index + direction );
			start(); // Restart the countdown after a manual move.
		}

		if ( prev ) {
			prev.addEventListener( 'click', step.bind( null, -1 ) );
		}

		if ( next ) {
			next.addEventListener( 'click', step.bind( null, 1 ) );
		}

		// Keep the dots in sync with swipes and trackpad scrolling.
		track.addEventListener( 'scroll', function () {
			window.clearTimeout( scrollTimer );
			scrollTimer = window.setTimeout( function () {
				if ( ! drag ) {
					index = currentIndex();
					update();
				}
			}, 120 );
		}, { passive: true } );

		track.addEventListener( 'keydown', function ( event ) {
			if ( event.target !== track ) {
				return;
			}

			var forward = isRtl() ? 'ArrowLeft' : 'ArrowRight';
			var back = isRtl() ? 'ArrowRight' : 'ArrowLeft';

			if ( event.key === forward ) {
				step( 1 );
			} else if ( event.key === back ) {
				step( -1 );
			} else if ( 'Home' === event.key ) {
				goTo( 0 );
			} else if ( 'End' === event.key ) {
				goTo( lastIndex() );
			} else {
				return;
			}

			event.preventDefault();
		} );

		root.addEventListener( 'mouseenter', function () {
			hovered = true;
		} );

		root.addEventListener( 'mouseleave', function () {
			hovered = false;
		} );

		root.addEventListener( 'focusin', function () {
			focused = true;
		} );

		root.addEventListener( 'focusout', function ( event ) {
			if ( ! event.relatedTarget || ! root.contains( event.relatedTarget ) ) {
				focused = false;
			}
		} );

		// Mouse dragging. Touch and pen use the browser's own scrolling.
		track.addEventListener( 'pointerdown', function ( event ) {
			if ( 'mouse' !== event.pointerType || 0 !== event.button ) {
				return;
			}

			drag = {
				id: event.pointerId,
				x: event.clientX,
				scroll: track.scrollLeft,
				from: index,
				moved: false,
			};
		} );

		track.addEventListener( 'pointermove', function ( event ) {
			if ( ! drag || event.pointerId !== drag.id ) {
				return;
			}

			var dx = event.clientX - drag.x;

			if ( ! drag.moved && Math.abs( dx ) > 5 ) {
				drag.moved = true;
				track.setPointerCapture( drag.id );
				root.classList.add( 'is-dragging' );
			}

			if ( drag.moved ) {
				track.scrollLeft = drag.scroll - dx;
			}
		} );

		function endDrag( event ) {
			if ( ! drag || event.pointerId !== drag.id ) {
				return;
			}

			var state = drag;

			drag = null;

			if ( ! state.moved ) {
				return;
			}

			var dx = event.clientX - state.x;
			var forward = isRtl() ? dx > 0 : dx < 0;
			var target = currentIndex();

			// A short flick still moves one slide.
			if ( target === state.from && Math.abs( dx ) > 40 ) {
				target += forward ? 1 : -1;
			}

			root.classList.remove( 'is-dragging' );
			suppressClick = true;
			window.setTimeout( function () {
				suppressClick = false;
			}, 0 );
			goTo( Math.max( 0, Math.min( lastIndex(), target ) ) );
			start();
		}

		track.addEventListener( 'pointerup', endDrag );
		track.addEventListener( 'pointercancel', endDrag );

		// A drag shouldn't also follow a link inside a slide.
		track.addEventListener( 'click', function ( event ) {
			if ( suppressClick ) {
				event.preventDefault();
				event.stopPropagation();
			}
		}, true );

		track.addEventListener( 'dragstart', function ( event ) {
			event.preventDefault();
		} );

		var observer = 'ResizeObserver' in window ? new ResizeObserver( function () {
			if ( ! root.isConnected ) {
				cleanUp();
				return;
			}

			var before = perView;

			layout();

			if ( before !== perView ) {
				goTo( index, true );
			}
		} ) : null;

		function cleanUp() {
			stop();

			if ( observer ) {
				observer.disconnect();
			}
		}

		if ( observer ) {
			observer.observe( track );
		} else {
			window.addEventListener( 'resize', layout );
		}

		layout();
		start();
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-testimonials' ).forEach( initCarousel );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-testimonials.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
