<?php
/**
 * Newsletter widget handler ("Save in WordPress" mode): stores each sign-up as a
 * private Subscriber. Addresses already on the list are not added twice.
 *
 * In "Send to a mailing service" mode the form posts straight to the service and
 * never reaches this handler.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ID of the subscriber with this email address, or 0.
 *
 * @param string $email Email address.
 * @return int
 */
function teckform_find_subscriber( $email ) {
	$ids = get_posts( array(
		'post_type'      => 'teckform_subscriber',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_key'       => '_teckform_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => strtolower( $email ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	) );

	return $ids ? (int) $ids[0] : 0;
}

/**
 * Receive a Newsletter sign-up.
 */
function teckform_handle_newsletter() {
	$context  = teckform_form_request_context();
	$settings = teckform_form_get_widget_settings( $context['post_id'], $context['element_id'], 'teckform-newsletter' );
	$respond  = array( 'element_id' => $context['element_id'] );

	if ( null === $settings || 'external' === teckform_form_setting( $settings, 'mode', 'wordpress' ) ) {
		teckform_form_respond( false, teckform_form_error_text( 'not_found' ), $respond );
	}

	$success_text = teckform_form_setting( $settings, 'success_message', __( 'Thanks for subscribing!', 'teckform' ) );
	$check        = teckform_form_verify( 'newsletter' );

	if ( is_wp_error( $check ) ) {
		if ( 'spam' === $check->get_error_code() ) {
			teckform_form_respond( true, $success_text, $respond );
		}

		teckform_form_respond( false, $check->get_error_message(), $respond );
	}

	$show_name    = 'yes' === teckform_form_setting( $settings, 'show_name', '' );
	$show_consent = 'yes' === teckform_form_setting( $settings, 'show_consent', '' );

	$email   = teckform_form_posted( 'teckform_email' );
	$email   = is_string( $email ) ? sanitize_email( $email ) : '';
	$name    = teckform_form_posted( 'teckform_name' );
	$name    = $show_name && is_string( $name ) ? mb_substr( sanitize_text_field( $name ), 0, 200 ) : '';
	$consent = $show_consent && '1' === teckform_form_posted( 'teckform_consent' );
	$errors  = array();

	if ( ! $email || ! is_email( $email ) ) {
		$errors['email'] = teckform_form_error_text( 'email' );
	}

	if ( $show_name && 'yes' === teckform_form_setting( $settings, 'name_required', '' ) && '' === $name ) {
		$errors['name'] = teckform_form_error_text( 'required' );
	}

	if ( $show_consent && ! $consent ) {
		$errors['consent'] = __( 'Please accept to continue.', 'teckform' );
	}

	if ( $errors ) {
		teckform_form_respond( false, teckform_form_setting( $settings, 'error_message', __( 'Please check the form and try again.', 'teckform' ) ), $respond + array(
			'errors' => $errors,
			'values' => array(
				'email' => $email,
				'name'  => $name,
			),
		) );
	}

	if ( teckform_find_subscriber( $email ) ) {
		teckform_form_respond( true, teckform_form_setting( $settings, 'duplicate_message', __( 'You are already subscribed. Thank you!', 'teckform' ) ), $respond );
	}

	$page_id = $context['page_id'] && get_post( $context['page_id'] ) ? $context['page_id'] : $context['post_id'];

	$subscriber_id = wp_insert_post( array(
		'post_type'   => 'teckform_subscriber',
		'post_status' => 'private',
		'post_title'  => strtolower( $email ),
		'meta_input'  => array(
			'_teckform_email'    => strtolower( $email ),
			'_teckform_name'     => $name,
			'_teckform_source'   => sanitize_text_field( teckform_form_setting( $settings, 'list_name', '' ) ),
			'_teckform_page_id'  => $page_id,
			'_teckform_page_url' => esc_url_raw( teckform_form_return_url() ),
			'_teckform_consent'  => $consent ? current_time( 'mysql' ) : '',
		),
	), true );

	if ( is_wp_error( $subscriber_id ) || ! $subscriber_id ) {
		teckform_form_respond( false, teckform_form_setting( $settings, 'error_message', __( 'Sorry, something went wrong. Please try again.', 'teckform' ) ), $respond );
	}

	/**
	 * After a newsletter sign-up is saved.
	 *
	 * @param int    $subscriber_id Subscriber ID.
	 * @param string $email         Email address.
	 * @param string $name          Name ('' when not asked).
	 * @param array  $settings      Widget settings.
	 */
	do_action( 'teckform_newsletter_subscribed', $subscriber_id, $email, $name, $settings );

	teckform_form_respond( true, $success_text, $respond );
}
teckform_form_add_handler( 'newsletter', 'teckform_handle_newsletter' );
