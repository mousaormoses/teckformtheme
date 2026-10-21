/**
 * Teckform FAQ widget: accordion with smooth height animation. Closed answers
 * use hidden="until-found", so the browser's find-in-page can still open them.
 * Also runs for FAQs re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function parts( item ) {
		return {
			button: item.querySelector( '.teckform-faq__question' ),
			answer: item.querySelector( '.teckform-faq__answer' ),
		};
	}

	// Run once the height transition ends, or right away when nothing animates.
	function afterTransition( answer, done ) {
		var finished = false;

		function finish( event ) {
			if ( finished || ( event && ( event.target !== answer || 'height' !== event.propertyName ) ) ) {
				return;
			}

			finished = true;
			answer.removeEventListener( 'transitionend', finish );
			done();
		}

		answer.addEventListener( 'transitionend', finish );
		// Fallback in case transitionend never fires (display changes, zero duration).
		window.setTimeout( finish, 450 );
	}

	function open( item, animate ) {
		var p = parts( item );

		item.classList.add( 'is-open' );
		p.button.setAttribute( 'aria-expanded', 'true' );
		p.answer.removeAttribute( 'hidden' );

		if ( ! animate || reduceMotion ) {
			p.answer.style.height = '';
			return;
		}

		p.answer.style.height = '0px';
		p.answer.offsetHeight; // eslint-disable-line no-unused-expressions
		p.answer.style.height = p.answer.scrollHeight + 'px';

		afterTransition( p.answer, function () {
			if ( item.classList.contains( 'is-open' ) ) {
				p.answer.style.height = '';
			}
		} );
	}

	function close( item, animate ) {
		var p = parts( item );

		item.classList.remove( 'is-open' );
		p.button.setAttribute( 'aria-expanded', 'false' );

		if ( ! animate || reduceMotion ) {
			p.answer.style.height = '';
			p.answer.setAttribute( 'hidden', 'until-found' );
			return;
		}

		p.answer.style.height = p.answer.scrollHeight + 'px';
		p.answer.offsetHeight; // eslint-disable-line no-unused-expressions
		p.answer.style.height = '0px';

		afterTransition( p.answer, function () {
			if ( ! item.classList.contains( 'is-open' ) ) {
				p.answer.setAttribute( 'hidden', 'until-found' );
				p.answer.style.height = '';
			}
		} );
	}

	function initFaq( faq ) {
		if ( faq.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		faq.setAttribute( 'data-teckform-ready', '' );

		var multiple = 'yes' === faq.getAttribute( 'data-multiple' );
		var items = Array.prototype.slice.call( faq.querySelectorAll( '.teckform-faq__item' ) );

		function show( item, animate ) {
			if ( ! multiple ) {
				items.forEach( function ( other ) {
					if ( other !== item && other.classList.contains( 'is-open' ) ) {
						close( other, animate );
					}
				} );
			}

			open( item, animate );
		}

		items.forEach( function ( item ) {
			var p = parts( item );

			p.button.addEventListener( 'click', function () {
				if ( item.classList.contains( 'is-open' ) ) {
					close( item, true );
				} else {
					show( item, true );
				}
			} );

			// Find-in-page matched text in a closed answer: open it without animating.
			p.answer.addEventListener( 'beforematch', function () {
				show( item, false );
			} );
		} );

		// Home / End / arrow keys move between questions, as in the WAI accordion pattern.
		faq.addEventListener( 'keydown', function ( event ) {
			var button = event.target.closest( '.teckform-faq__question' );

			if ( ! button ) {
				return;
			}

			var buttons = items.map( function ( item ) {
				return parts( item ).button;
			} );
			var index = buttons.indexOf( button );
			var next = null;

			if ( 'ArrowDown' === event.key ) {
				next = buttons[ ( index + 1 ) % buttons.length ];
			} else if ( 'ArrowUp' === event.key ) {
				next = buttons[ ( index - 1 + buttons.length ) % buttons.length ];
			} else if ( 'Home' === event.key ) {
				next = buttons[ 0 ];
			} else if ( 'End' === event.key ) {
				next = buttons[ buttons.length - 1 ];
			}

			if ( next ) {
				event.preventDefault();
				next.focus();
			}
		} );

		// Open the item a #link points to, e.g. /faq/#teckform-faq-abc123-2-question.
		if ( window.location.hash ) {
			var target = null;

			try {
				target = faq.querySelector( window.location.hash );
			} catch ( e ) {
				target = null;
			}

			var targetItem = target && target.closest( '.teckform-faq__item' );

			if ( targetItem && ! targetItem.classList.contains( 'is-open' ) ) {
				show( targetItem, false );
			}
		}
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.teckform-faq' ).forEach( initFaq );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-faq.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
