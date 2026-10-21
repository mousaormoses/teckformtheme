<?php
/**
 * Contact Form widget: field setup shared with the widget, and the handler that
 * validates a submission, saves it as a Message and emails it.
 *
 * The message is saved before the email is sent, so nothing is lost when the
 * server can't send email.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field types the form supports.
 *
 * @return array<string, string>
 */
function teckform_contact_form_field_types() {
	return array(
		'text'       => __( 'Text', 'teckform' ),
		'email'      => __( 'Email', 'teckform' ),
		'tel'        => __( 'Phone', 'teckform' ),
		'number'     => __( 'Number', 'teckform' ),
		'textarea'   => __( 'Text area', 'teckform' ),
		'select'     => __( 'Dropdown', 'teckform' ),
		'checkbox'   => __( 'Checkboxes', 'teckform' ),
		'acceptance' => __( 'Consent', 'teckform' ),
	);
}

/**
 * The widget's fields in a clean, predictable shape. Used both to print the form
 * and to check a submission, so both always agree on field keys and rules.
 *
 * @param array $settings Widget settings.
 * @return array[] Each: key, type, label, placeholder, required, options, text, rows, min, max, item_id.
 */
function teckform_contact_form_fields( $settings ) {
	$fields = array();
	$types  = teckform_contact_form_field_types();
	$items  = isset( $settings['fields'] ) && is_array( $settings['fields'] ) ? $settings['fields'] : array();

	foreach ( array_values( $items ) as $index => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$type    = isset( $item['field_type'] ) && isset( $types[ $item['field_type'] ] ) ? $item['field_type'] : 'text';
		$item_id = isset( $item['_id'] ) ? sanitize_key( $item['_id'] ) : '';
		$label   = isset( $item['field_label'] ) ? trim( (string) $item['field_label'] ) : '';

		$fields[] = array(
			'key'         => $item_id ? $item_id : 'field_' . $index,
			'item_id'     => $item_id,
			'type'        => $type,
			/* translators: %d: field number. */
			'label'       => '' !== $label ? $label : sprintf( __( 'Field %d', 'teckform' ), $index + 1 ),
			'show_label'  => '' !== $label,
			'placeholder' => isset( $item['placeholder'] ) ? (string) $item['placeholder'] : '',
			'required'    => isset( $item['required'] ) && 'yes' === $item['required'],
			'options'     => in_array( $type, array( 'select', 'checkbox' ), true ) ? teckform_form_lines( isset( $item['field_options'] ) ? $item['field_options'] : '' ) : array(),
			'text'        => isset( $item['acceptance_text'] ) ? (string) $item['acceptance_text'] : '',
			'rows'        => isset( $item['rows'] ) && (int) $item['rows'] > 0 ? (int) $item['rows'] : 5,
			'min'         => isset( $item['number_min'] ) && is_numeric( $item['number_min'] ) ? $item['number_min'] : '',
			'max'         => isset( $item['number_max'] ) && is_numeric( $item['number_max'] ) ? $item['number_max'] : '',
		);
	}

	return $fields;
}

/**
 * Check and clean one posted field.
 *
 * @param array $field Field from teckform_contact_form_fields().
 * @param mixed $raw   Posted value (unslashed).
 * @return array{0: string|string[], 1: string} Clean value and an error message ('' when valid).
 */
function teckform_contact_form_clean_field( $field, $raw ) {
	$type = $field['type'];

	if ( 'checkbox' === $type && $field['options'] ) {
		$value = array_values( array_intersect( $field['options'], array_map( 'strval', is_array( $raw ) ? $raw : array() ) ) );

		return array( $value, $field['required'] && ! $value ? __( 'Please choose at least one option.', 'teckform' ) : '' );
	}

	if ( in_array( $type, array( 'checkbox', 'acceptance' ), true ) ) {
		$value = is_string( $raw ) && '1' === $raw ? '1' : '';

		if ( $field['required'] && '' === $value ) {
			return array( '', 'acceptance' === $type ? __( 'Please accept to continue.', 'teckform' ) : teckform_form_error_text( 'required' ) );
		}

		return array( $value, '' );
	}

	$raw = is_scalar( $raw ) ? (string) $raw : '';

	if ( 'textarea' === $type ) {
		$value = mb_substr( sanitize_textarea_field( $raw ), 0, 10000 );
	} else {
		$value = mb_substr( sanitize_text_field( $raw ), 0, 'email' === $type ? 254 : 1000 );
	}

	if ( '' === trim( $value ) ) {
		return array( '', $field['required'] ? teckform_form_error_text( 'required' ) : '' );
	}

	switch ( $type ) {
		case 'email':
			$value = sanitize_email( $value );

			if ( ! is_email( $value ) ) {
				return array( $value, teckform_form_error_text( 'email' ) );
			}
			break;

		case 'tel':
			if ( ! preg_match( '/^[0-9+()\-.\s\/]{3,30}$/', $value ) ) {
				return array( $value, __( 'Please enter a valid phone number.', 'teckform' ) );
			}
			break;

		case 'number':
			if ( ! is_numeric( $value ) ) {
				return array( $value, __( 'Please enter a number.', 'teckform' ) );
			}

			if ( ( '' !== $field['min'] && (float) $value < (float) $field['min'] ) || ( '' !== $field['max'] && (float) $value > (float) $field['max'] ) ) {
				/* translators: 1: smallest number, 2: largest number. */
				return array( $value, sprintf( __( 'Please enter a number between %1$s and %2$s.', 'teckform' ), '' !== $field['min'] ? $field['min'] : '-∞', '' !== $field['max'] ? $field['max'] : '∞' ) );
			}
			break;

		case 'select':
			if ( ! in_array( $value, $field['options'], true ) ) {
				return array( '', __( 'Please choose one of the options.', 'teckform' ) );
			}
			break;
	}

	return array( $value, '' );
}

/**
 * Value as shown in the email and the dashboard.
 *
 * @param array           $field Field.
 * @param string|string[] $value Clean value.
 * @return string
 */
function teckform_contact_form_display_value( $field, $value ) {
	if ( is_array( $value ) ) {
		return implode( ', ', $value );
	}

	if ( in_array( $field['type'], array( 'checkbox', 'acceptance' ), true ) ) {
		if ( '1' !== $value ) {
			return __( 'No', 'teckform' );
		}

		return 'acceptance' === $field['type'] ? __( 'Accepted', 'teckform' ) : __( 'Yes', 'teckform' );
	}

	return (string) $value;
}

/**
 * Fill {field label}, {site name} and {page} placeholders in the subject line.
 *
 * @param string $subject Subject with placeholders.
 * @param array  $entries Label/value pairs.
 * @param string $page    Page title.
 * @return string
 */
function teckform_contact_form_subject( $subject, $entries, $page ) {
	$map = array(
		'site name' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		'site_name' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		'page'      => $page,
	);

	foreach ( $entries as $entry ) {
		$label = function_exists( 'mb_strtolower' ) ? mb_strtolower( $entry['label'] ) : strtolower( $entry['label'] );

		if ( ! isset( $map[ $label ] ) ) {
			$map[ $label ] = $entry['value'];
		}
	}

	$subject = preg_replace_callback( '/\{([^{}]{1,80})\}/u', function ( $match ) use ( $map ) {
		$key = trim( function_exists( 'mb_strtolower' ) ? mb_strtolower( $match[1] ) : strtolower( $match[1] ) );

		return isset( $map[ $key ] ) ? $map[ $key ] : '';
	}, (string) $subject );

	// No line breaks in a mail header, and tidy spaces left by empty placeholders.
	$subject = trim( preg_replace( '/\s+/', ' ', sanitize_text_field( $subject ) ) );

	return '' !== $subject ? $subject : __( 'New message from your website', 'teckform' );
}

/**
 * Email body listing every field.
 *
 * @param array  $entries  Label/value pairs.
 * @param string $page_url Page the form was sent from.
 * @param bool   $html     HTML or plain text.
 * @return string
 */
function teckform_contact_form_email_body( $entries, $page_url, $html ) {
	$date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

	if ( ! $html ) {
		$body = '';

		foreach ( $entries as $entry ) {
			$body .= $entry['label'] . ":\n" . ( '' !== $entry['value'] ? $entry['value'] : '-' ) . "\n\n";
		}

		/* translators: 1: page URL, 2: date and time. */
		return $body . "---\n" . sprintf( __( 'Sent from %1$s on %2$s', 'teckform' ), $page_url, $date ) . "\n";
	}

	$rows = '';

	foreach ( $entries as $entry ) {
		$rows .= '<tr><td style="padding:14px 0;border-bottom:1px solid #e5e7eb;">'
			. '<div style="margin:0 0 4px;color:#6b7280;font-size:13px;font-weight:600;">' . esc_html( $entry['label'] ) . '</div>'
			. '<div style="color:#1f2937;font-size:15px;line-height:1.6;">' . ( '' !== $entry['value'] ? nl2br( esc_html( $entry['value'] ) ) : '&ndash;' ) . '</div>'
			. '</td></tr>';
	}

	$footer = sprintf(
		/* translators: 1: page link, 2: date and time. */
		esc_html__( 'Sent from %1$s on %2$s', 'teckform' ),
		'<a href="' . esc_url( $page_url ) . '" style="color:#2563eb;">' . esc_html( $page_url ) . '</a>',
		esc_html( $date )
	);

	return '<!doctype html><html><body style="margin:0;padding:24px;background:#f9fafb;">'
		. '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;margin:0 auto;padding:8px 24px 24px;background:#ffffff;border-radius:8px;border-collapse:separate;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Arial,sans-serif;">'
		. $rows
		. '<tr><td style="padding-top:16px;color:#9ca3af;font-size:12px;">' . $footer . '</td></tr>'
		. '</table></body></html>';
}

/**
 * Receive a Contact Form submission.
 */
function teckform_handle_contact_form() {
	$context  = teckform_form_request_context();
	$settings = teckform_form_get_widget_settings( $context['post_id'], $context['element_id'], 'teckform-contact-form' );
	$respond  = array( 'element_id' => $context['element_id'] );

	if ( null === $settings ) {
		teckform_form_respond( false, teckform_form_error_text( 'not_found' ), $respond );
	}

	$success_text = teckform_form_setting( $settings, 'success_message', __( 'Thank you! Your message has been sent.', 'teckform' ) );
	$error_text   = teckform_form_setting( $settings, 'error_message', __( 'Sorry, your message could not be sent. Please try again.', 'teckform' ) );
	$check        = teckform_form_verify( 'contact_form' );

	if ( is_wp_error( $check ) ) {
		// Bots that filled the honeypot are told it worked, so they don't retry.
		if ( 'spam' === $check->get_error_code() ) {
			teckform_form_respond( true, $success_text, $respond );
		}

		teckform_form_respond( false, $check->get_error_message(), $respond );
	}

	$fields = teckform_contact_form_fields( $settings );
	$posted = teckform_form_posted( 'teckform_fields' );
	$posted = is_array( $posted ) ? $posted : array();

	$values  = array();
	$errors  = array();
	$entries = array();

	foreach ( $fields as $field ) {
		list( $value, $error ) = teckform_contact_form_clean_field( $field, isset( $posted[ $field['key'] ] ) ? $posted[ $field['key'] ] : null );

		$values[ $field['key'] ] = $value;

		if ( $error ) {
			$errors[ $field['key'] ] = $error;
			continue;
		}

		$entries[] = array(
			'label' => $field['label'],
			'value' => teckform_contact_form_display_value( $field, $value ),
			'type'  => $field['type'],
		);
	}

	if ( ! $fields ) {
		teckform_form_respond( false, teckform_form_error_text( 'not_found' ), $respond );
	}

	if ( $errors ) {
		teckform_form_respond( false, __( 'Please check the highlighted fields.', 'teckform' ), $respond + array(
			'errors' => $errors,
			'values' => $values,
		) );
	}

	// Who it's from: the first email field, and the first text field whose label mentions a name.
	$from_email = '';
	$from_name  = '';

	foreach ( $fields as $field ) {
		$value = $values[ $field['key'] ];

		if ( ! $from_email && 'email' === $field['type'] && is_string( $value ) && is_email( $value ) ) {
			$from_email = $value;
		}

		if ( ! $from_name && 'text' === $field['type'] && is_string( $value ) && '' !== $value && preg_match( '/name|nom|naam|اسم/iu', $field['label'] ) ) {
			$from_name = $value;
		}
	}

	$page_id    = $context['page_id'] && get_post( $context['page_id'] ) ? $context['page_id'] : $context['post_id'];
	$page_url   = teckform_form_return_url();
	$page_title = $page_id ? wp_specialchars_decode( get_the_title( $page_id ), ENT_QUOTES ) : '';
	$subject    = teckform_contact_form_subject(
		teckform_form_setting( $settings, 'email_subject', __( 'New message from {Name} on {site name}', 'teckform' ) ),
		$entries,
		$page_title
	);
	$plain_body = teckform_contact_form_email_body( $entries, $page_url, false );
	$form_name  = sanitize_text_field( teckform_form_setting( $settings, 'form_name', __( 'Contact form', 'teckform' ) ) );

	// Save first, so the message is kept even when email fails.
	$message_id = wp_insert_post( array(
		'post_type'    => 'teckform_message',
		'post_status'  => 'private',
		'post_title'   => $subject,
		'post_content' => $plain_body,
		'meta_input'   => array(
			'_teckform_fields'     => $entries,
			'_teckform_from_name'  => $from_name,
			'_teckform_from_email' => $from_email,
			'_teckform_page_id'    => $page_id,
			'_teckform_page_url'   => esc_url_raw( $page_url ),
			'_teckform_form_name'  => $form_name,
			'_teckform_read'       => '0',
		),
	), true );

	$recipients = teckform_form_parse_emails( teckform_form_setting( $settings, 'email_to', '' ) );

	if ( ! $recipients ) {
		$recipients = array( get_option( 'admin_email' ) );
	}

	$html    = 'plain' !== teckform_form_setting( $settings, 'email_format', 'html' );
	$headers = array();

	if ( $html ) {
		$headers[] = 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' );
	}

	if ( $from_email ) {
		$headers[] = 'Reply-To: ' . $from_email;
	}

	$sent = wp_mail( $recipients, $subject, $html ? teckform_contact_form_email_body( $entries, $page_url, true ) : $plain_body, $headers );

	if ( ! is_wp_error( $message_id ) && $message_id ) {
		update_post_meta( $message_id, '_teckform_mail_sent', $sent ? '1' : '0' );
	} elseif ( ! $sent ) {
		teckform_form_respond( false, $error_text, $respond + array( 'values' => $values ) );
	}

	/**
	 * After a contact form message is saved and emailed.
	 *
	 * @param int|WP_Error $message_id Saved message ID.
	 * @param array        $entries    Label/value pairs.
	 * @param array        $settings   Widget settings.
	 */
	do_action( 'teckform_contact_form_sent', $message_id, $entries, $settings );

	$redirect = teckform_form_setting( $settings, 'redirect_url', array() );

	teckform_form_respond( true, $success_text, $respond + array(
		'redirect' => is_array( $redirect ) && ! empty( $redirect['url'] ) ? $redirect['url'] : '',
	) );
}
teckform_form_add_handler( 'contact_form', 'teckform_handle_contact_form' );
