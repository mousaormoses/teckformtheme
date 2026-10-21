/**
 * Teckform Login / Register widget: Login and Register tabs, the show/hide
 * password button, and sending both forms with fetch() so errors show inline
 * without a reload. Without JavaScript both forms are shown one under the other
 * and post normally. Also runs for widgets re-rendered in the Elementor editor.
 */
( function () {
	'use strict';

	var BLOCK = 'teckform-login';

	/* ---------------------------------------------------------------------
	 * Sending
	 * ------------------------------------------------------------------ */

	function setMessage( form, text, type ) {
		var box = form.querySelector( '.' + BLOCK + '__message' );

		if ( ! box ) {
			return;
		}

		box.textContent = text || '';
		box.classList.toggle( 'is-success', !! text && 'success' === type );
		box.classList.toggle( 'is-error', !! text && 'error' === type );
	}

	function clearErrors( form ) {
		form.querySelectorAll( '.' + BLOCK + '__error' ).forEach( function ( note ) {
			note.remove();
		} );

		form.querySelectorAll( '[aria-invalid="true"]' ).forEach( function ( input ) {
			input.removeAttribute( 'aria-invalid' );
			input.removeAttribute( 'aria-describedby' );
		} );
	}

	function showErrors( form, errors ) {
		var first = null;

		Object.keys( errors || {} ).forEach( function ( key ) {
			var field = form.querySelector( '[data-field="' + ( window.CSS && CSS.escape ? CSS.escape( key ) : key ) + '"]' );
			var input = field && field.querySelector( 'input' );

			if ( ! input ) {
				return;
			}

			var note = document.createElement( 'p' );

			note.className = BLOCK + '__error';
			note.id = input.id + '-error';
			note.textContent = errors[ key ];
			field.appendChild( note );
			input.setAttribute( 'aria-invalid', 'true' );
			input.setAttribute( 'aria-describedby', note.id );
			first = first || input;
		} );

		if ( first ) {
			first.focus();
		}
	}

	function setSending( form, sending ) {
		var button = form.querySelector( '[type="submit"]' );

		form.setAttribute( 'aria-busy', sending ? 'true' : 'false' );
		form.classList.toggle( 'is-sending', sending );

		if ( button ) {
			button.disabled = sending;
			button.classList.toggle( 'is-sending', sending );
		}
	}

	function submit( event ) {
		var form = event.currentTarget;
		var url = form.getAttribute( 'data-ajax-url' );

		if ( ! url || ! window.fetch || ! window.FormData ) {
			return;
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

					// Logged in: open the next page (or reload this one) with the new login cookies.
					if ( data.redirect ) {
						window.location.assign( data.redirect );
						return;
					}

					form.reset();
				} else {
					setMessage( form, data.message || fallback, 'error' );
					showErrors( form, data.errors );

					// Clear a wrong password so it can be typed again straight away.
					var password = form.querySelector( 'input[name="pwd"]' );

					if ( password && ! ( data.errors && data.errors.log ) ) {
						password.value = '';
					}
				}

				setSending( form, false );
			} )
			.catch( function () {
				setMessage( form, fallback, 'error' );
				setSending( form, false );
			} );
	}

	/* ---------------------------------------------------------------------
	 * Show / hide password
	 * ------------------------------------------------------------------ */

	function initToggle( button ) {
		var input = document.getElementById( button.getAttribute( 'aria-controls' ) );

		if ( ! input ) {
			return;
		}

		button.hidden = false;

		button.addEventListener( 'click', function () {
			var show = 'password' === input.type;

			input.type = show ? 'text' : 'password';
			button.setAttribute( 'aria-pressed', show ? 'true' : 'false' );
			button.setAttribute( 'aria-label', button.getAttribute( show ? 'data-hide-label' : 'data-show-label' ) );
			input.focus();
		} );

		// Never send the password as a visible text field.
		input.form.addEventListener( 'submit', function () {
			if ( 'text' === input.type ) {
				input.type = 'password';
				button.setAttribute( 'aria-pressed', 'false' );
				button.setAttribute( 'aria-label', button.getAttribute( 'data-show-label' ) );
			}
		} );
	}

	/* ---------------------------------------------------------------------
	 * Tabs
	 * ------------------------------------------------------------------ */

	function initTabs( widget ) {
		var list = widget.querySelector( '.' + BLOCK + '__tabs' );

		if ( ! list ) {
			return;
		}

		var tabs = Array.prototype.slice.call( list.querySelectorAll( '[role="tab"]' ) );
		var panels = widget.querySelectorAll( '.' + BLOCK + '__panel' );

		function select( tab, focus ) {
			var name = tab.getAttribute( 'data-panel' );

			tabs.forEach( function ( other ) {
				var active = other === tab;

				other.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				other.tabIndex = active ? 0 : -1;
			} );

			panels.forEach( function ( panel ) {
				panel.hidden = panel.getAttribute( 'data-panel' ) !== name;
			} );

			if ( focus ) {
				tab.focus();
			}
		}

		list.hidden = false;
		widget.classList.add( 'is-enhanced' );

		var current = tabs.filter( function ( tab ) {
			return 'true' === tab.getAttribute( 'aria-selected' );
		} )[ 0 ] || tabs[ 0 ];

		select( current, false );

		tabs.forEach( function ( tab, index ) {
			tab.addEventListener( 'click', function () {
				select( tab, false );
			} );

			// Arrow keys move between tabs; Home and End jump to the first and last.
			tab.addEventListener( 'keydown', function ( event ) {
				var rtl = 'rtl' === window.getComputedStyle( list ).direction;
				var next = null;

				if ( 'ArrowRight' === event.key || 'ArrowLeft' === event.key ) {
					var step = ( 'ArrowRight' === event.key ) !== rtl ? 1 : -1;

					next = tabs[ ( index + step + tabs.length ) % tabs.length ];
				} else if ( 'Home' === event.key ) {
					next = tabs[ 0 ];
				} else if ( 'End' === event.key ) {
					next = tabs[ tabs.length - 1 ];
				}

				if ( next ) {
					event.preventDefault();
					select( next, true );
				}
			} );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Start
	 * ------------------------------------------------------------------ */

	function initWidget( widget ) {
		if ( widget.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		widget.setAttribute( 'data-teckform-ready', '' );
		initTabs( widget );
		widget.querySelectorAll( '.' + BLOCK + '__toggle' ).forEach( initToggle );
		widget.querySelectorAll( '.' + BLOCK + '__form' ).forEach( function ( form ) {
			form.addEventListener( 'submit', submit );
		} );
	}

	function initAll( root ) {
		( root || document ).querySelectorAll( '.' + BLOCK ).forEach( initWidget );
	}

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-login-form.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
