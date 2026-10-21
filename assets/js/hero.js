/**
 * Teckform Hero widget: rotating and typed headline words, counting numbers
 * and the scroll-down arrow. Also runs for heroes re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Slide between words. The box animates to the visible word's width so the rest of the headline moves smoothly.
	function initWords( box ) {
		var words = box.querySelectorAll( '.teckform-hero__word' );
		var title = box.closest( '.teckform-hero__title' );
		var interval = parseInt( box.getAttribute( 'data-interval' ), 10 ) || 2500;
		var index = 0;

		function fit() {
			box.style.width = words[ index ].offsetWidth + 'px';
		}

		// A longer word can wrap the headline onto another line. Reserve the tallest
		// version's height so the page below doesn't jump as the words change.
		function reserveHeight() {
			var tallest = 0;

			title.style.minHeight = '';
			box.style.transition = 'none';

			words.forEach( function ( word ) {
				box.style.width = word.offsetWidth + 'px';
				tallest = Math.max( tallest, title.offsetHeight );
			} );

			title.style.minHeight = tallest + 'px';
			fit();
			box.offsetWidth; // Apply the width before turning the transition back on.
			box.style.transition = '';
		}

		function resize() {
			if ( title ) {
				reserveHeight();
			} else {
				fit();
			}
		}

		resize();
		window.addEventListener( 'resize', resize );

		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( resize );
		}

		if ( reduceMotion || words.length < 2 ) {
			return;
		}

		var timer = window.setInterval( function () {
			// The Elementor editor replaces the widget on every change; stop the old copy.
			if ( ! box.isConnected ) {
				window.clearInterval( timer );
				window.removeEventListener( 'resize', resize );
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
			}, 450 );
		}, interval );
	}

	// Type each word letter by letter, pause, delete it, then type the next.
	function initTyping( el ) {
		var words;

		try {
			words = JSON.parse( el.getAttribute( 'data-words' ) ) || [];
		} catch ( e ) {
			words = [];
		}

		if ( reduceMotion || ! words.length ) {
			return;
		}

		var title = el.closest( '.teckform-hero__title' );

		// Same as the slide animation: keep the headline at its tallest so the page doesn't jump.
		function reserveHeight() {
			var current = el.textContent;
			var tallest = 0;

			title.style.minHeight = '';

			words.forEach( function ( text ) {
				el.textContent = text;
				tallest = Math.max( tallest, title.offsetHeight );
			} );

			el.textContent = current;
			title.style.minHeight = tallest + 'px';
		}

		if ( title ) {
			reserveHeight();
			window.addEventListener( 'resize', reserveHeight );

			if ( document.fonts && document.fonts.ready ) {
				document.fonts.ready.then( reserveHeight );
			}
		}

		var pause = parseInt( el.getAttribute( 'data-interval' ), 10 ) || 2500;
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
			window.setTimeout( tick, deleting ? 45 : 90 );
		}

		window.setTimeout( tick, pause );
	}

	// Count numbers up from zero the first time they scroll into view.
	function initCounters( hero ) {
		var numbers = hero.querySelectorAll( '.teckform-hero__stat-number[data-count]' );

		if ( reduceMotion || ! numbers.length || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		function run( el ) {
			var target = parseFloat( el.getAttribute( 'data-count' ) );
			var decimals = ( el.getAttribute( 'data-count' ).split( '.' )[ 1 ] || '' ).length;
			var start = null;

			function step( time ) {
				if ( null === start ) {
					start = time;
				}

				var progress = Math.min( ( time - start ) / 1600, 1 );
				var eased = 1 - Math.pow( 1 - progress, 3 );

				el.textContent = ( target * eased ).toFixed( decimals );

				if ( progress < 1 ) {
					window.requestAnimationFrame( step );
				}
			}

			window.requestAnimationFrame( step );
		}

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					observer.unobserve( entry.target );
					run( entry.target );
				}
			} );
		}, { threshold: 0.4 } );

		numbers.forEach( function ( el ) {
			observer.observe( el );
		} );
	}

	function initHero( hero ) {
		if ( hero.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		hero.setAttribute( 'data-teckform-ready', '' );
		hero.querySelectorAll( '.teckform-hero__words' ).forEach( initWords );
		hero.querySelectorAll( '.teckform-hero__typed' ).forEach( initTyping );
		initCounters( hero );
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-hero' ).forEach( initHero );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-hero.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );

	// Scroll-down arrow: jump to whatever comes after the hero.
	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.teckform-hero__scroll' );

		if ( ! button ) {
			return;
		}

		var hero = button.closest( '.teckform-hero' );

		window.scrollTo( {
			top: hero.getBoundingClientRect().bottom + window.scrollY,
			behavior: reduceMotion ? 'auto' : 'smooth',
		} );
	} );
}() );
