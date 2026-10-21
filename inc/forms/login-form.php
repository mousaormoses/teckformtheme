<?php
/**
 * Login / Register widget handlers.
 *
 * Login uses wp_signon(), registration uses register_new_user(), so WordPress's own
 * checks, hooks and emails apply (including the "set your password" email).
 * WordPress's error messages are shown with their HTML removed.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Where to go after logging in, from the widget's settings.
 *
 * @param array $settings Widget settings.
 * @return string
 */
function teckform_login_redirect_url( $settings ) {
	if ( 'custom' === teckform_form_setting( $settings, 'redirect_type', 'current' ) ) {
		$url = teckform_form_setting( $settings, 'redirect_url', array() );

		if ( is_array( $url ) && ! empty( $url['url'] ) ) {
			return $url['url'];
		}
	}

	return teckform_form_return_url();
}

/**
 * Whether the widget may show its Register tab.
 *
 * @param array $settings Widget settings.
 * @return bool
 */
function teckform_login_can_register( $settings ) {
	return get_option( 'users_can_register' ) && 'yes' === teckform_form_setting( $settings, 'show_register', '' );
}

/**
 * Receive a login.
 */
function teckform_handle_login() {
	$context  = teckform_form_request_context();
	$settings = teckform_form_get_widget_settings( $context['post_id'], $context['element_id'], 'teckform-login-form' );
	$respond  = array(
		'element_id' => $context['element_id'],
		'panel'      => 'login',
	);

	if ( null === $settings ) {
		teckform_form_respond( false, teckform_form_error_text( 'not_found' ), $respond );
	}

	$redirect = teckform_login_redirect_url( $settings );
	$success  = teckform_form_setting( $settings, 'login_success_message', __( 'You are logged in. Taking you there now…', 'teckform' ) );

	if ( is_user_logged_in() ) {
		teckform_form_respond( true, $success, $respond + array( 'redirect' => $redirect ) );
	}

	$check = teckform_form_verify( 'login' );

	if ( is_wp_error( $check ) ) {
		$message = 'spam' === $check->get_error_code() ? __( 'Your login could not be checked. Please try again.', 'teckform' ) : $check->get_error_message();
		teckform_form_respond( false, $message, $respond );
	}

	$login    = teckform_form_posted( 'log' );
	$login    = is_string( $login ) ? trim( $login ) : '';
	$password = teckform_form_posted( 'pwd' );
	$password = is_string( $password ) ? $password : ''; // Passwords are used as typed, like wp-login.php does.
	$values   = array( 'log' => sanitize_text_field( $login ) );
	$errors   = array();

	if ( '' === $login ) {
		$errors['log'] = teckform_form_error_text( 'required' );
	}

	if ( '' === $password ) {
		$errors['pwd'] = teckform_form_error_text( 'required' );
	}

	if ( $errors ) {
		teckform_form_respond( false, __( 'Please enter your username or email address and your password.', 'teckform' ), $respond + array(
			'errors' => $errors,
			'values' => $values,
		) );
	}

	$user = wp_signon( array(
		'user_login'    => $login,
		'user_password' => $password,
		'remember'      => 'yes' === teckform_form_setting( $settings, 'show_remember', '' ) && null !== teckform_form_posted( 'rememberme' ),
	), is_ssl() );

	if ( is_wp_error( $user ) ) {
		$message = teckform_form_plain_text( implode( ' ', $user->get_error_messages() ) );

		teckform_form_respond( false, '' !== $message ? $message : __( 'Login failed. Please try again.', 'teckform' ), $respond + array( 'values' => $values ) );
	}

	teckform_form_respond( true, $success, $respond + array( 'redirect' => $redirect ) );
}
teckform_form_add_handler( 'login', 'teckform_handle_login' );

/**
 * Receive a registration.
 */
function teckform_handle_register() {
	$context  = teckform_form_request_context();
	$settings = teckform_form_get_widget_settings( $context['post_id'], $context['element_id'], 'teckform-login-form' );
	$respond  = array(
		'element_id' => $context['element_id'],
		'panel'      => 'register',
	);

	if ( null === $settings ) {
		teckform_form_respond( false, teckform_form_error_text( 'not_found' ), $respond );
	}

	if ( ! teckform_login_can_register( $settings ) ) {
		teckform_form_respond( false, __( 'Registration is closed on this site.', 'teckform' ), $respond );
	}

	if ( is_user_logged_in() ) {
		teckform_form_respond( false, __( 'You are already logged in.', 'teckform' ), $respond );
	}

	$check = teckform_form_verify( 'register' );

	if ( is_wp_error( $check ) ) {
		$message = 'spam' === $check->get_error_code() ? __( 'Your registration could not be checked. Please try again.', 'teckform' ) : $check->get_error_message();
		teckform_form_respond( false, $message, $respond );
	}

	$user_login = teckform_form_posted( 'user_login' );
	$user_login = is_string( $user_login ) ? sanitize_user( $user_login ) : '';
	$user_email = teckform_form_posted( 'user_email' );
	$user_email = is_string( $user_email ) ? sanitize_email( $user_email ) : '';
	$values     = array(
		'user_login' => $user_login,
		'user_email' => $user_email,
	);

	$user_id = register_new_user( $user_login, $user_email );

	if ( is_wp_error( $user_id ) ) {
		// Show each error under the field it belongs to.
		$fields = array(
			'empty_username'   => 'user_login',
			'invalid_username' => 'user_login',
			'username_exists'  => 'user_login',
			'empty_email'      => 'user_email',
			'invalid_email'    => 'user_email',
			'email_exists'     => 'user_email',
		);
		$errors = array();

		foreach ( $user_id->get_error_codes() as $code ) {
			if ( isset( $fields[ $code ] ) && ! isset( $errors[ $fields[ $code ] ] ) ) {
				$errors[ $fields[ $code ] ] = teckform_form_plain_text( $user_id->get_error_message( $code ) );
			}
		}

		$message = teckform_form_plain_text( implode( ' ', $user_id->get_error_messages() ) );

		teckform_form_respond( false, '' !== $message ? $message : __( 'Registration failed. Please try again.', 'teckform' ), $respond + array(
			'errors' => $errors,
			'values' => $values,
		) );
	}

	teckform_form_respond( true, teckform_form_setting( $settings, 'register_success_message', __( 'Registration complete. Please check your email for a link to set your password.', 'teckform' ) ), $respond );
}
teckform_form_add_handler( 'register', 'teckform_handle_register' );
