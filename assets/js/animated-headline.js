/**
 * Teckform Animated Headline widget: rotating words (slide up, fade, flip),
 * typed words, and hand-drawn highlight shapes. Also runs for headlines
 * re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function getNumber( el, name, fallback ) {
		var value = parseInt( el.getAttribute( name ), 10 );

		return value > 0 ? value : fallback;
	}

	// Run a callback once the element is on screen (straight away without IntersectionObserver).
	function whenVisible( el, callback ) {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			callback();
			return;
		}

		var observer = new IntersectionObserver( function ( entries ) {
			if ( entries[ 0 ].isIntersecting ) {
				observer.disconnect();
				callback();
			}
		}, { threshold: 0.5 } );

		observer.observe( el );
	}

	// Keep the headline at its tallest version so a longer word wrapping onto
	// another line doesn't make the page below jump. `show` displays version i.
	function reserveHeight( title, count, show, after ) {
		var tallest = 0;
		var i;

		title.style.minHeight = '';

		for ( i = 0; i < count; i++ ) {
			show( i );
			tallest = Math.max( tallest, title.offsetHeight );
		}

		title.style.minHeight = tallest + 'px';
		after();
	}

	// Re-measure on resize and once web fonts have loaded; stop when the widget is gone.
	function watchSize( el, measure ) {
		function onResize() {
			if ( ! el.isConnected ) {
				window.removeEventListener( 'resize', onResize );
				return;
			}

			measure();
		}

		measure();
		window.addEventListener( 'resize', onResize );

		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( onResize );
		}
	}

	// Slide up, fade and flip: words take turns in one grid cell. The box animates
	// to the visible word's width so the rest of the headline moves smoothly.
	function initWords( root ) {
		var box = root.querySelector( '.teckform-animated-headline__words' );

		if ( ! box ) {
			return;
		}

		var words = box.querySelectorAll( '.teckform-animated-headline__word' );
		var title = root.querySelector( '.teckform-animated-headline__title' );
		var interval = getNumber( root, 'data-interval', 2500 );
		var speed = parseFloat( window.getComputedStyle( root ).getPropertyValue( '--teckform-animated-headline-speed' ) ) || 500;
		var index = 0;

		function fit() {
			box.style.width = words[ index ].offsetWidth + 'px';
		}

		watchSize( box, function () {
			box.style.transition = 'none';
			reserveHeight( title, words.length, function ( i ) {
				box.style.width = words[ i ].offsetWidth + 'px';
			}, function () {
				fit();
				box.offsetWidth; // Apply the width before turning the transition back on.
				box.style.transition = '';
			} );
		} );

		if ( reduceMotion ) {
			return;
		}

		var timer = window.setInterval( function () {
			// The Elementor editor replaces the widget on every change; stop the old copy.
			if ( ! box.isConnected ) {
				window.clearInterval( timer );
				return;
			}

			var previous = words[ index ];

			index = ( index + 1 ) % words.length;
			previous.classList.remove( 'is-active' );
			previous.classList.add( 'is-leaving' );
			words[ index ].classList.add( 'is-active' );
			fit();

			window.setTimeout( function () {
				previous.classList.remove( 'is-leaving' );
			}, speed + 50 );
		}, interval );
	}

	// Typing: type each word letter by letter, pause, delete it, then type the next.
	function initTyping( root ) {
		var el = root.querySelector( '.teckform-animated-headline__typed' );
		var words;

		if ( ! el ) {
			return;
		}

		try {
			words = JSON.parse( el.getAttribute( 'data-words' ) ) || [];
		} catch ( e ) {
			words = [];
		}

		if ( reduceMotion || words.length < 2 ) {
			return;
		}

		var title = root.querySelector( '.teckform-animated-headline__title' );

		watchSize( el, function () {
			var current = el.textContent;

			reserveHeight( title, words.length, function ( i ) {
				el.textContent = words[ i ];
			}, function () {
				el.textContent = current;
			} );
		} );

		var pause = getNumber( root, 'data-interval', 2500 );
		var speed = getNumber( root, 'data-typing-speed', 90 );
		var word = 0;
		var length = words[ 0 ].length;
		var deleting = false;

		function tick() {
			if ( ! el.isConnected ) {
				return;
			}

			if ( ! deleting && length === words[ word ].length ) {
				deleting = true;
				window.setTimeout( tick, pause );
				return;
			}

			if ( deleting && 0 === length ) {
				deleting = false;
				word = ( word + 1 ) % words.length;
			}

			length += deleting ? -1 : 1;
			el.textContent = words[ word ].slice( 0, length );
			window.setTimeout( tick, deleting ? Math.max( 10, speed / 2 ) : speed );
		}

		window.setTimeout( tick, pause );
	}

	// Highlight: draw the shape when the headline comes into view, then optionally
	// fade it out and draw it again on a loop.
	function initHighlight( root ) {
		var shape = root.querySelector( '.teckform-animated-headline__shape' );

		if ( ! shape ) {
			return;
		}

		if ( reduceMotion ) {
			root.classList.add( 'is-drawn' );
			return;
		}

		var draw = getNumber( root, 'data-draw', 1200 );
		var pause = parseInt( root.getAttribute( 'data-loop' ), 10 ) || 0;

		// The second line of a double underline starts halfway through the first.
		var drawTime = shape.querySelectorAll( 'path' ).length > 1 ? draw * 1.5 : draw;

		function drawShape() {
			if ( ! root.isConnected ) {
				return;
			}

			root.classList.add( 'is-drawn' );

			if ( pause ) {
				window.setTimeout( erase, drawTime + pause );
			}
		}

		function erase() {
			if ( ! root.isConnected ) {
				return;
			}

			root.classList.add( 'is-erasing' );

			window.setTimeout( function () {
				// Rewind the stroke instantly while it's invisible, then draw again.
				root.classList.add( 'is-resetting' );
				root.classList.remove( 'is-drawn', 'is-erasing' );
				root.offsetWidth; // Apply the rewind before turning the transition back on.
				root.classList.remove( 'is-resetting' );
				window.setTimeout( drawShape, 200 );
			}, 450 );
		}

		whenVisible( root, function () {
			window.setTimeout( drawShape, 200 );
		} );
	}

	function initHeadline( root ) {
		if ( root.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		root.setAttribute( 'data-teckform-ready', '' );

		var effect = root.getAttribute( 'data-effect' );

		if ( 'highlight' === effect ) {
			initHighlight( root );
		} else if ( 'typing' === effect ) {
			initTyping( root );
		} else {
			initWords( root );
		}
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-animated-headline' ).forEach( initHeadline );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-animated-headline.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
