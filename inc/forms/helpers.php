<?php
/**
 * Shared helpers for the form widgets: Contact Form, Newsletter and Login / Register.
 *
 * The widgets only print the forms. The handlers in this folder receive them through
 * admin-ajax.php (JavaScript) or admin-post.php (no JavaScript) and never trust the
 * field setup sent by the browser: they load the widget's saved settings from the
 * Elementor document instead. Every form gets the same protection: a nonce, a hidden
 * honeypot field, a signed timestamp (forms sent too fast are refused) and a per-IP
 * rate limit.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability needed to read messages and subscribers in the dashboard.
 *
 * @return string
 */
function teckform_form_capability() {
	return (string) apply_filters( 'teckform_form_capability', 'manage_options' );
}

/**
 * Find an element by ID in Elementor's element tree.
 *
 * @param array  $elements   Elements data (sections, columns, containers, widgets).
 * @param string $element_id Element ID.
 * @return array|null
 */
function teckform_form_find_element( $elements, $element_id ) {
	foreach ( (array) $elements as $element ) {
		if ( ! is_array( $element ) ) {
			continue;
		}

		if ( isset( $element['id'] ) && (string) $element['id'] === (string) $element_id ) {
			return $element;
		}

		if ( ! empty( $element['elements'] ) ) {
			$found = teckform_form_find_element( $element['elements'], $element_id );

			if ( $found ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * The saved settings of a form widget, read from its Elementor document.
 *
 * The widget is rebuilt from the saved data so its control defaults are filled in
 * (Elementor does not save values left at their default).
 *
 * @param int    $post_id     Document (post or template) that contains the widget.
 * @param string $element_id  Elementor element ID.
 * @param string $widget_type Expected widget name, e.g. 'teckform-contact-form'.
 * @return array|null Settings, or null when the widget can't be found.
 */
function teckform_form_get_widget_settings( $post_id, $element_id, $widget_type ) {
	$post_id = absint( $post_id );

	if ( ! $post_id || '' === $element_id || ! did_action( 'elementor/loaded' ) ) {
		return null;
	}

	$post = get_post( $post_id );

	// Forms on drafts and private pages only work for people who can see them.
	if ( ! $post || ( ! is_post_publicly_viewable( $post ) && ! current_user_can( 'edit_post', $post_id ) ) ) {
		return null;
	}

	$document = \Elementor\Plugin::$instance->documents->get( $post_id );

	if ( ! $document ) {
		return null;
	}

	$element = teckform_form_find_element( $document->get_elements_data(), $element_id );

	if ( ! $element || ! isset( $element['widgetType'] ) || $widget_type !== $element['widgetType'] ) {
		return null;
	}

	$settings = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array();

	try {
		$widget = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $element );

		if ( $widget ) {
			$settings = $widget->get_settings_for_display();
		}
	} catch ( \Throwable $e ) {
		// Fall back to the raw saved settings; handlers supply their own defaults.
		unset( $e );
	}

	return is_array( $settings ) ? $settings : array();
}

/**
 * A setting value, or a default when it is missing.
 *
 * @param array  $settings Widget settings.
 * @param string $key      Setting key.
 * @param mixed  $default  Default value.
 * @return mixed
 */
function teckform_form_setting( $settings, $key, $default = '' ) {
	return isset( $settings[ $key ] ) && '' !== $settings[ $key ] ? $settings[ $key ] : $default;
}

/**
 * ID of the Elementor document being rendered, so the handler can find the widget again.
 * This is the template's ID when the form sits in a header, footer or popup template.
 *
 * @return int
 */
function teckform_form_current_document_id() {
	if ( did_action( 'elementor/loaded' ) ) {
		$document = \Elementor\Plugin::$instance->documents->get_current();

		if ( $document ) {
			return (int) $document->get_main_id();
		}
	}

	return (int) get_the_ID();
}

/**
 * URL of the page being viewed, without the form result argument.
 *
 * @return string
 */
function teckform_form_current_url() {
	if ( wp_doing_ajax() || empty( $_SERVER['HTTP_HOST'] ) || empty( $_SERVER['REQUEST_URI'] ) ) {
		$url = is_singular() ? get_permalink() : home_url( '/' );
	} else {
		$host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
		$path = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$url  = set_url_scheme( 'http://' . $host . '/' . ltrim( $path, '/' ) );
	}

	return remove_query_arg( 'teckform_flash', (string) $url );
}

/**
 * Print the hidden fields every form needs: handler action, where the widget lives,
 * the page to return to, nonce, signed timestamp and the honeypot.
 *
 * @param string $action     Handler name, e.g. 'contact_form'. Posted as 'teckform_{action}'.
 * @param string $element_id Elementor element ID.
 */
function teckform_form_hidden_fields( $action, $element_id ) {
	$fields = array(
		'action'              => 'teckform_' . $action,
		'teckform_post_id'    => teckform_form_current_document_id(),
		'teckform_element_id' => $element_id,
		'teckform_page_id'    => (int) get_queried_object_id(),
		'teckform_referer'    => teckform_form_current_url(),
		'teckform_nonce'      => wp_create_nonce( 'teckform_form_' . $action ),
		'teckform_ts'         => teckform_form_time_token(),
	);

	foreach ( $fields as $name => $value ) {
		printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $name ), esc_attr( $value ) );
	}

	// Honeypot: invisible to people and screen readers, but bots fill it in.
	printf(
		'<div class="teckform-form-hp" aria-hidden="true" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);clip-path:inset(50%%);white-space:nowrap;"><label>%s<input type="text" name="teckform_website" value="" tabindex="-1" autocomplete="off"></label></div>',
		esc_html__( 'Leave this field empty', 'teckform' )
	);
}

/**
 * Current time signed with the site's salt, so the "sent too fast" check can't be faked.
 *
 * @return string
 */
function teckform_form_time_token() {
	$time = (string) time();

	return $time . '-' . wp_hash( 'teckform-form-time|' . $time );
}

/**
 * Seconds since the form was shown, or -1 when the token is missing or forged.
 *
 * @param string $token Value from teckform_form_time_token().
 * @return int
 */
function teckform_form_token_age( $token ) {
	$parts = explode( '-', (string) $token, 2 );

	if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) || ! hash_equals( wp_hash( 'teckform-form-time|' . $parts[0] ), $parts[1] ) ) {
		return -1;
	}

	return time() - (int) $parts[0];
}

/**
 * Visitor IP address. Proxy headers are ignored because they are easy to fake;
 * sites behind a proxy can use the filter.
 *
 * @return string
 */
function teckform_form_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$ip = (string) apply_filters( 'teckform_form_client_ip', $ip );

	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/**
 * Count an attempt and report whether this IP has made too many.
 *
 * @param string $bucket Form kind, e.g. 'contact_form'.
 * @return bool True when the limit is reached.
 */
function teckform_form_rate_limited( $bucket ) {
	$defaults = array(
		'contact_form' => array( 5, 10 * MINUTE_IN_SECONDS ),
		'newsletter'   => array( 5, 10 * MINUTE_IN_SECONDS ),
		'login'        => array( 10, 10 * MINUTE_IN_SECONDS ),
		'register'     => array( 3, HOUR_IN_SECONDS ),
	);

	$limit = isset( $defaults[ $bucket ] ) ? $defaults[ $bucket ] : array( 10, 10 * MINUTE_IN_SECONDS );

	/**
	 * Attempts allowed per IP address and the time window in seconds.
	 *
	 * @param array  $limit  array( max attempts, window in seconds ).
	 * @param string $bucket Form kind.
	 */
	$limit = apply_filters( 'teckform_form_rate_limit', $limit, $bucket );
	$max   = max( 1, (int) $limit[0] );
	$key   = 'teckform_rl_' . md5( $bucket . '|' . teckform_form_client_ip() );
	$data  = get_transient( $key );

	if ( ! is_array( $data ) || ! isset( $data['count'], $data['expires'] ) || $data['expires'] < time() ) {
		$data = array(
			'count'   => 0,
			'expires' => time() + max( 1, (int) $limit[1] ),
		);
	}

	if ( $data['count'] >= $max ) {
		return true;
	}

	++$data['count'];
	set_transient( $key, $data, max( 1, $data['expires'] - time() ) );

	return false;
}

/**
 * The widget a submission belongs to, as posted by the form.
 *
 * @return array{post_id: int, element_id: string, page_id: int}
 */
function teckform_form_request_context() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- The nonce is checked in teckform_form_verify().
	return array(
		'post_id'    => isset( $_POST['teckform_post_id'] ) ? absint( $_POST['teckform_post_id'] ) : 0,
		'element_id' => isset( $_POST['teckform_element_id'] ) ? sanitize_key( wp_unslash( $_POST['teckform_element_id'] ) ) : '',
		'page_id'    => isset( $_POST['teckform_page_id'] ) ? absint( $_POST['teckform_page_id'] ) : 0,
	);
	// phpcs:enable
}

/**
 * Run the security checks shared by every form.
 *
 * @param string $action Handler name, e.g. 'contact_form'.
 * @return true|WP_Error Error codes: invalid_nonce, spam, too_fast, rate_limited.
 */
function teckform_form_verify( $action ) {
	$nonce = isset( $_POST['teckform_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['teckform_nonce'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'teckform_form_' . $action ) ) {
		return new WP_Error( 'invalid_nonce', teckform_form_error_text( 'invalid_nonce' ) );
	}

	if ( ! empty( $_POST['teckform_website'] ) ) {
		return new WP_Error( 'spam', '' );
	}

	$age = teckform_form_token_age( isset( $_POST['teckform_ts'] ) ? sanitize_text_field( wp_unslash( $_POST['teckform_ts'] ) ) : '' );

	if ( $age < 0 ) {
		return new WP_Error( 'invalid_nonce', teckform_form_error_text( 'invalid_nonce' ) );
	}

	if ( $age < (int) apply_filters( 'teckform_form_min_seconds', 3, $action ) ) {
		return new WP_Error( 'too_fast', teckform_form_error_text( 'too_fast' ) );
	}

	if ( teckform_form_rate_limited( $action ) ) {
		return new WP_Error( 'rate_limited', teckform_form_error_text( 'rate_limited' ) );
	}

	return true;
}

/**
 * Messages for the shared checks.
 *
 * @param string $code Error code.
 * @return string
 */
function teckform_form_error_text( $code ) {
	$texts = array(
		'invalid_nonce' => __( 'Your session has expired. Please reload the page and try again.', 'teckform' ),
		'too_fast'      => __( 'That was very quick! Please wait a moment and send the form again.', 'teckform' ),
		'rate_limited'  => __( 'Too many attempts. Please wait a few minutes and try again.', 'teckform' ),
		'not_found'     => __( 'This form could not be found. Please reload the page and try again.', 'teckform' ),
		'required'      => __( 'This field is required.', 'teckform' ),
		'email'         => __( 'Please enter a valid email address.', 'teckform' ),
	);

	return isset( $texts[ $code ] ) ? $texts[ $code ] : __( 'Something went wrong. Please try again.', 'teckform' );
}

/**
 * A raw posted value, unslashed but not yet sanitized.
 *
 * @param string $key POST key.
 * @return mixed
 */
function teckform_form_posted( $key ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Callers verify the nonce and sanitize per field type.
	return isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : null;
}

/**
 * Plain text from a message that may contain HTML (WordPress login and
 * registration errors do), safe to send as JSON or print with esc_html().
 *
 * @param string $html Message.
 * @return string
 */
function teckform_form_plain_text( $html ) {
	$text = html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES, get_bloginfo( 'charset' ) );

	return trim( preg_replace( '/\s+/', ' ', $text ) );
}

/**
 * Non-empty trimmed lines of a textarea setting, e.g. select options.
 *
 * @param string $text Text with one item per line.
 * @return string[]
 */
function teckform_form_lines( $text ) {
	$lines = array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) );

	return array_values( array_unique( array_filter( $lines, 'strlen' ) ) );
}

/**
 * Valid addresses from a comma-separated list.
 *
 * @param string $text Email addresses.
 * @return string[]
 */
function teckform_form_parse_emails( $text ) {
	$emails = array();

	foreach ( preg_split( '/[,;\s]+/', (string) $text ) as $email ) {
		$email = sanitize_email( $email );

		if ( $email && is_email( $email ) ) {
			$emails[] = $email;
		}
	}

	return array_values( array_unique( $emails ) );
}

/**
 * HTML allowed in consent and note texts: simple formatting and links.
 *
 * @return array
 */
function teckform_form_inline_html() {
	return array(
		'a'      => array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
		),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
		'br'     => array(),
	);
}

/**
 * Where to send a visitor back to after a form posted without JavaScript.
 *
 * @return string
 */
function teckform_form_return_url() {
	$url = teckform_form_posted( 'teckform_referer' );
	$url = is_string( $url ) ? wp_validate_redirect( esc_url_raw( $url ), '' ) : '';

	if ( ! $url ) {
		$url = wp_get_referer();
	}

	return remove_query_arg( 'teckform_flash', $url ? $url : home_url( '/' ) );
}

/**
 * Send the result of a submission and stop.
 *
 * With JavaScript: a JSON reply. Without: a redirect back to the form's page; the
 * message is kept on the server for a few minutes and only a random key goes in
 * the URL, so nobody can put their own text on the page through a link.
 *
 * @param bool   $success Whether it worked.
 * @param string $message Message for the visitor (plain text).
 * @param array  $args {
 *     Optional.
 *
 *     @type string $element_id Widget the result belongs to.
 *     @type array  $errors     Field key => error message.
 *     @type array  $values     Field values to refill after an error (no JavaScript only).
 *     @type string $redirect   URL to go to after success. Must be trusted (from settings).
 *     @type string $panel      Which part of the widget to show, e.g. 'register'.
 * }
 */
function teckform_form_respond( $success, $message, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'element_id' => '',
		'errors'     => array(),
		'values'     => array(),
		'redirect'   => '',
		'panel'      => '',
	) );

	$message  = teckform_form_plain_text( $message );
	$redirect = $args['redirect'] ? esc_url_raw( $args['redirect'] ) : '';

	if ( wp_doing_ajax() ) {
		$data = array(
			'message'  => $message,
			'errors'   => array_map( 'teckform_form_plain_text', (array) $args['errors'] ),
			'redirect' => $success ? $redirect : '',
		);

		if ( $success ) {
			wp_send_json_success( $data );
		}

		wp_send_json_error( $data );
	}

	if ( $success && $redirect ) {
		// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- The URL comes from the widget's saved settings or a validated return URL.
		wp_redirect( $redirect );
		exit;
	}

	$key = strtolower( wp_generate_password( 20, false ) );

	set_transient( 'teckform_flash_' . $key, array(
		'element_id' => (string) $args['element_id'],
		'success'    => (bool) $success,
		'message'    => $message,
		'errors'     => array_map( 'teckform_form_plain_text', (array) $args['errors'] ),
		'values'     => $success ? array() : (array) $args['values'],
		'panel'      => sanitize_key( $args['panel'] ),
	), 10 * MINUTE_IN_SECONDS );

	$url = add_query_arg( 'teckform_flash', $key, teckform_form_return_url() );

	if ( $args['element_id'] ) {
		$url .= '#teckform-' . sanitize_key( $args['element_id'] );
	}

	wp_safe_redirect( $url );
	exit;
}

/**
 * Result of a form posted without JavaScript, for the widget it belongs to.
 *
 * @param string $element_id Elementor element ID.
 * @return array|null Keys: success, message, errors, values, panel.
 */
function teckform_form_get_flash( $element_id ) {
	static $cache = array();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only lookup by a random key.
	$key = isset( $_GET['teckform_flash'] ) ? sanitize_key( wp_unslash( $_GET['teckform_flash'] ) ) : '';

	if ( '' === $key || 20 !== strlen( $key ) ) {
		return null;
	}

	if ( ! array_key_exists( $key, $cache ) ) {
		$cache[ $key ] = get_transient( 'teckform_flash_' . $key );
	}

	$flash = $cache[ $key ];

	if ( ! is_array( $flash ) || ! isset( $flash['element_id'] ) || (string) $element_id !== $flash['element_id'] ) {
		return null;
	}

	return $flash;
}

/**
 * Whether the widget is being shown in the Elementor editor or its preview.
 *
 * @return bool
 */
function teckform_form_is_editor() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}

	$plugin = \Elementor\Plugin::$instance;

	return ( $plugin->editor && $plugin->editor->is_edit_mode() ) || ( $plugin->preview && $plugin->preview->is_preview_mode() );
}

/**
 * Register a handler for both the JavaScript (admin-ajax.php) and the plain
 * (admin-post.php) submission, for visitors and logged-in users.
 *
 * @param string   $action   Handler name; the form posts action=teckform_{name}.
 * @param callable $callback Handler.
 */
function teckform_form_add_handler( $action, $callback ) {
	foreach ( array( 'wp_ajax_', 'wp_ajax_nopriv_', 'admin_post_', 'admin_post_nopriv_' ) as $prefix ) {
		add_action( $prefix . 'teckform_' . $action, $callback );
	}
}
