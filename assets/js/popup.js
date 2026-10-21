/**
 * Teckform Popup widget.
 *
 * Opens the popup from its button, from links to #popup-id, and automatically
 * (after a delay, after scrolling, on exit intent). Center and full screen popups
 * are modal: focus is trapped inside and the page doesn't scroll. The slide-in is
 * not modal. In the Elementor editor the popup is shown inline and nothing runs.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var focusable = 'a[href], area[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), iframe, audio[controls], video[controls], [contenteditable="true"], [tabindex]:not([tabindex="-1"])';
	var popups = {}; // Popup ID => controller.
	var openStack = []; // Open popups, most recent last.

	function storageKey( id ) {
		return 'teckform-popup-' + id;
	}

	// Whether an automatic trigger may open this popup, given "Show again after N days".
	function allowedByStorage( id, days ) {
		if ( days <= 0 ) {
			return true;
		}

		try {
			var shown = parseInt( window.localStorage.getItem( storageKey( id ) ), 10 );

			return ! shown || Date.now() - shown > days * 86400000;
		} catch ( e ) {
			return true; // Storage blocked: show it, like a first visit.
		}
	}

	function rememberShown( id ) {
		try {
			window.localStorage.setItem( storageKey( id ), String( Date.now() ) );
		} catch ( e ) {}
	}

	function isVisible( el ) {
		return !! ( el.offsetWidth || el.offsetHeight || el.getClientRects().length );
	}

	function lockScroll( lock ) {
		var html = document.documentElement;

		if ( lock ) {
			// Keep the page from shifting sideways when its scrollbar disappears.
			html.style.setProperty( '--teckform-scrollbar-width', ( window.innerWidth - html.clientWidth ) + 'px' );
			document.body.classList.add( 'teckform-no-scroll' );
		} else {
			document.body.classList.remove( 'teckform-no-scroll' );
			html.style.removeProperty( '--teckform-scrollbar-width' );
		}
	}

	function createPopup( root ) {
		var dialog = root.querySelector( '.teckform-popup__dialog' );

		if ( ! dialog || ! dialog.id ) {
			return null;
		}

		var id = dialog.id;
		var panel = dialog.querySelector( '.teckform-popup__panel' );
		var modal = 'true' === dialog.getAttribute( 'aria-modal' );
		var days = parseInt( root.getAttribute( 'data-show-again' ), 10 ) || 0;
		var isOpen = false;
		var autoOpened = false;
		var returnFocus = null;
		var closeTimer = null;
		var pressedOnOverlay = false;

		function speed() {
			if ( reduceMotion ) {
				return 0;
			}

			return parseFloat( window.getComputedStyle( root ).getPropertyValue( '--teckform-popup-speed' ) ) || 300;
		}

		/**
		 * @param {Element|null} opener Element that opened the popup (gets focus back), or null.
		 * @param {boolean}      auto   Opened by an automatic trigger.
		 */
		function open( opener, auto ) {
			if ( ! root.isConnected ) {
				return;
			}

			if ( auto ) {
				// Automatic triggers: once per page view, never over another popup.
				if ( autoOpened || openStack.length || ! allowedByStorage( id, days ) ) {
					return;
				}

				autoOpened = true;
				rememberShown( id );
			}

			if ( isOpen && ! closeTimer ) {
				return;
			}

			window.clearTimeout( closeTimer );
			closeTimer = null;

			// A link inside another popup: close that one and hand its focus target over.
			var parent = opener && opener.closest( '.teckform-popup__dialog' );

			if ( parent && parent !== dialog && parent.teckformPopup ) {
				returnFocus = parent.teckformPopup.returnFocus();
				parent.teckformPopup.close( true );
			} else if ( ! isOpen ) {
				returnFocus = opener || ( document.activeElement !== document.body ? document.activeElement : null );
			}

			if ( ! isOpen ) {
				isOpen = true;
				openStack.push( api );

				if ( dialog.open ) {
					// Already open (should not happen); leave it.
				} else if ( modal && 'function' === typeof dialog.showModal ) {
					dialog.showModal();
				} else if ( 'function' === typeof dialog.show ) {
					dialog.show();
				} else {
					dialog.setAttribute( 'open', '' );
				}

				if ( modal ) {
					lockScroll( true );
				}
			}

			// Apply the hidden state first so the transition runs (works in background tabs too, unlike requestAnimationFrame).
			dialog.offsetWidth;
			dialog.classList.add( 'is-visible' );

			// A slide-in opened automatically doesn't take focus from what the visitor is doing.
			if ( modal || ! auto ) {
				panel.focus( { preventScroll: true } );
			}
		}

		/**
		 * @param {boolean} keepFocus Don't move focus back (another popup takes over).
		 */
		function close( keepFocus ) {
			if ( ! isOpen || closeTimer ) {
				return;
			}

			if ( keepFocus ) {
				returnFocus = null;
			}

			dialog.classList.remove( 'is-visible' );

			closeTimer = window.setTimeout( function () {
				closeTimer = null;

				if ( 'function' === typeof dialog.close ) {
					dialog.close();
				} else {
					dialog.removeAttribute( 'open' );
				}

				cleanUp();
			}, speed() );
		}

		// After the dialog has closed, by us or by the browser (Escape on a modal dialog).
		function cleanUp() {
			if ( ! isOpen ) {
				return;
			}

			isOpen = false;
			window.clearTimeout( closeTimer );
			closeTimer = null;
			dialog.classList.remove( 'is-visible' );
			openStack.splice( openStack.indexOf( api ), 1 );

			if ( modal && ! openStack.some( function ( popup ) {
				return popup.modal;
			} ) ) {
				lockScroll( false );
			}

			// Return focus only when it was inside the popup (or lost), not if the visitor moved on.
			var active = document.activeElement;

			if ( returnFocus && returnFocus.isConnected && ( ! active || active === document.body || dialog.contains( active ) ) ) {
				returnFocus.focus( { preventScroll: true } );
			}

			returnFocus = null;
		}

		// Keep Tab and Shift+Tab inside a modal popup.
		function trapFocus( event ) {
			var items = Array.prototype.filter.call( dialog.querySelectorAll( focusable ), isVisible );

			if ( ! items.length ) {
				event.preventDefault();
				panel.focus();
				return;
			}

			var first = items[ 0 ];
			var last = items[ items.length - 1 ];
			var active = document.activeElement;

			if ( event.shiftKey && ( active === first || active === panel || ! dialog.contains( active ) ) ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && ( active === last || ! dialog.contains( active ) ) ) {
				event.preventDefault();
				first.focus();
			}
		}

		// The browser can close a modal dialog itself (a second Escape press, for example).
		dialog.addEventListener( 'close', cleanUp );

		// Escape on a modal dialog: animate out instead of the browser's instant close.
		dialog.addEventListener( 'cancel', function ( event ) {
			event.preventDefault();
			close();
		} );

		dialog.addEventListener( 'keydown', function ( event ) {
			if ( 'Tab' === event.key && modal ) {
				trapFocus( event );
			}
		} );

		// A click that starts and ends on the overlay closes the popup (not a text selection dragged out).
		dialog.addEventListener( 'mousedown', function ( event ) {
			pressedOnOverlay = event.target === dialog;
		} );

		dialog.addEventListener( 'click', function ( event ) {
			var target = event.target;

			if ( target === dialog ) {
				if ( pressedOnOverlay && root.hasAttribute( 'data-close-overlay' ) ) {
					close();
				}

				return;
			}

			if ( target.closest( '.teckform-popup__close, [data-teckform-popup-close], a[href="#close-popup"]' ) ) {
				event.preventDefault();
				close();
			}
		} );

		var button = root.querySelector( '.teckform-popup__button' );

		if ( button ) {
			button.addEventListener( 'click', function () {
				open( button, false );
			} );
		}

		var api = {
			id: id,
			root: root,
			modal: modal,
			open: open,
			close: close,
			isOpen: function () {
				return isOpen;
			},
			returnFocus: function () {
				return returnFocus;
			},
		};

		dialog.teckformPopup = api;

		return api;
	}

	// Automatic triggers. Each opens the popup at most once per page view.
	function initTriggers( popup ) {
		var root = popup.root;
		var days = parseInt( root.getAttribute( 'data-show-again' ), 10 ) || 0;

		if ( ! allowedByStorage( popup.id, days ) ) {
			return;
		}

		function auto() {
			popup.open( null, true );
		}

		if ( root.hasAttribute( 'data-open-load' ) ) {
			window.setTimeout( auto, parseInt( root.getAttribute( 'data-open-load' ), 10 ) || 0 );
		}

		if ( root.hasAttribute( 'data-open-scroll' ) ) {
			var percent = parseInt( root.getAttribute( 'data-open-scroll' ), 10 ) || 50;

			var onScroll = function () {
				var max = document.documentElement.scrollHeight - window.innerHeight;

				if ( ! root.isConnected ) {
					window.removeEventListener( 'scroll', onScroll );
					return;
				}

				if ( max > 0 && window.scrollY / max * 100 >= percent ) {
					window.removeEventListener( 'scroll', onScroll );
					auto();
				}
			};

			window.addEventListener( 'scroll', onScroll, { passive: true } );
		}

		// Exit intent: the mouse leaves the window through the top. Only with a real mouse.
		if ( root.hasAttribute( 'data-open-exit' ) && window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
			var onLeave = function ( event ) {
				if ( event.relatedTarget || event.clientY > 0 ) {
					return;
				}

				document.removeEventListener( 'mouseout', onLeave );

				if ( root.isConnected ) {
					auto();
				}
			};

			document.addEventListener( 'mouseout', onLeave );
		}
	}

	// Mark links to the popup so assistive technology knows they open a dialog.
	function markLinks( popup ) {
		document.querySelectorAll( 'a[href*="#"]' ).forEach( function ( link ) {
			if ( isPopupLink( link ) === popup.id ) {
				link.setAttribute( 'aria-haspopup', 'dialog' );
				link.setAttribute( 'aria-controls', popup.id );
			}
		} );
	}

	// The popup ID a link points to (a #hash on this same page), or ''.
	function isPopupLink( link ) {
		if ( ! link.hash || link.href.split( '#' )[ 0 ] !== window.location.href.split( '#' )[ 0 ] ) {
			return '';
		}

		try {
			return decodeURIComponent( link.hash.slice( 1 ) );
		} catch ( e ) {
			return link.hash.slice( 1 );
		}
	}

	function initPopup( root ) {
		if ( root.hasAttribute( 'data-teckform-ready' ) ) {
			return;
		}

		root.setAttribute( 'data-teckform-ready', '' );

		// Editor preview: shown inline for editing, no behavior.
		if ( root.classList.contains( 'teckform-popup--preview' ) ) {
			return;
		}

		var popup = createPopup( root );

		if ( ! popup ) {
			return;
		}

		popups[ popup.id ] = popup;
		markLinks( popup );
		initTriggers( popup );
	}

	function initAll( scope ) {
		( scope || document ).querySelectorAll( '.teckform-popup' ).forEach( initPopup );
	}

	// Any link on the page to #popup-id opens that popup.
	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest( 'a[href*="#"]' );

		if ( ! link || event.defaultPrevented || event.button > 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
			return;
		}

		var popup = popups[ isPopupLink( link ) ];

		if ( popup && popup.root.isConnected ) {
			event.preventDefault();
			popup.open( link, false );
		}
	} );

	// Escape closes the most recent popup (the slide-in too, which gets no "cancel" event).
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key || ! openStack.length ) {
			return;
		}

		event.preventDefault();
		openStack[ openStack.length - 1 ].close();
	} );

	initAll();

	// Elementor editor: run again whenever the widget is re-rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/teckform-popup.default', function ( $scope ) {
			initAll( $scope[ 0 ] );
		} );
	} );
}() );
