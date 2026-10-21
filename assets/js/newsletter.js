/**
 * Teckform Newsletter widget ("Save in WordPress" mode): sends the sign-up with
 * fetch() so the page doesn't reload, and shows the reply inline. Forms that post
 * to a mailing service have no AJAX address and are left to the browser, as are
 * all forms when JavaScript is off. Also runs for forms re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var BLOCK = 'teckform-newsletter';

	function setMessage( form, text, type ) {
		var box = form.querySelector( '.' + BLOCK + '__message' );

		if ( ! box ) {
			return;
		}

		box.textContent = text || '';
		box.classList.toggle( 'is-success', !! text && 'success' === type );
		box.classList.toggle( 'is-error', !! text && 'error' === type );
	}

	// Remove notes and invalid states left by the last attempt (also those printed by the server).
	function clearErrors( form ) {
		form.querySelectorAll( '.' + BLOCK + '__error' ).forEach( function ( note ) {
			note.remove();
		} );

		form.querySelectorAll( '[aria-invalid="true"]' ).forEach( function ( input ) {
			input.removeAttribute( 'aria-invalid' );
			input.removeAttribute( 'aria-describedby' );
		} );

		form.querySelectorAll( 'fieldset[aria-describedby]' ).forEach( function ( group ) {
			group.removeAttribute( 'aria-describedby' );
		} );
	}

	// Put each error under its field and move focus to the first one.
	function showErrors( form, errors ) {
		var first = null;

		Object.keys( errors || {} ).forEach( function ( key ) {
			var field = form.querySelector( '[data-field="' + ( window.CSS && CSS.escape ? CSS.escape( key ) : key ) + '"]' );

			if ( ! field ) {
				return;
			}

			var input = field.querySelector( 'input, select, textarea' );
			var note = document.createElement( 'p' );

			note.className = BLOCK + '__error';
			note.id = ( input ? input.id : 'teckform-' + key ) + '-error';
			note.textContent = errors[ key ];
			field.appendChild( note );

			if ( 'FIELDSET' === field.tagName ) {
				field.setAttribute( 'aria-describedby', note.id );
			} else if ( input ) {
				input.setAttribute( 'aria-invalid', 'true' );
				input.setAttribute( 'aria-describedby', note.id );
			}

			first = first || input;
		} );

		if ( first ) {
			first.focus();
		}
	}

	function setSending( form, sending ) {
		var button = form.querySelector( '[type="submit"]' );

		form.classList.toggle( 'is-sending', sending );
		form.setAttribute( 'aria-busy', sending ? 'true' : 'false' );

		if ( button ) {
			button.disabled = sending;
			button.classList.toggle( 'is-sending', sending );
		}
	}

	function submit( event ) {
		var form = event.currentTarget;
		var url = form.getAttribute( 'data-ajax-url' );

		if ( ! url || ! window.fetch || ! window.FormData ) {
			return; // Let the browser post the form normally.
		}

		event.preventDefault();

		if ( form.classList.contains( 'is-sending' ) ) {
			return;
		}

		var fallback = form.getAttribute( 'data-error-message' ) || 'Something went wrong. Please try again.';

		clearErrors( form );
		setMessage( form, '', '' );
		setSending( form, true );

		window.fetch( url, {
			method: 'POST',
			body: new FormData( form ),
			credentials: 'same-origin',
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				var data = ( json && json.data ) || {};

				if ( json && json.success ) {
					setMessage( form, data.message, 'success' );

					if ( data.redirect ) {
						window.location.assign( data.redirect );
						return; // Keep the button disabled while the next page loads.
					}

					form.reset();
				} else {
					setMessage( form, data.message || fallback, 'error' );
					showErrors( form, data.errors );
				}

				setSending( form, false );
			} )
			.catch( function () {
				setMessage( form, fallback, 'error' );
				setSending( form, false );
			} );
	}

	function initForm( form ) {
		if ( form.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		form.setAttribute( 'data-teckform-ready', '' );
		form.addEventListener( 'submit', submit );
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.' + BLOCK + '__form' ).forEach( initForm );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-newsletter.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
