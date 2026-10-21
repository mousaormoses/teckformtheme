<?php
/**
 * Where form submissions are kept: Messages (Contact Form widget) and Subscribers
 * (Newsletter widget, "Save in WordPress" mode).
 *
 * Both are private post types that only administrators can see. Entries are
 * read-only in the dashboard; subscribers can be exported as a CSV file.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capabilities for the form post types: everything needs teckform_form_capability(),
 * and nobody can add entries by hand.
 *
 * @return array<string, string>
 */
function teckform_form_post_type_caps() {
	$cap = teckform_form_capability();

	return array(
		'edit_post'              => $cap,
		'read_post'              => $cap,
		'delete_post'            => $cap,
		'edit_posts'             => $cap,
		'edit_others_posts'      => $cap,
		'edit_private_posts'     => $cap,
		'edit_published_posts'   => $cap,
		'delete_posts'           => $cap,
		'delete_others_posts'    => $cap,
		'delete_private_posts'   => $cap,
		'delete_published_posts' => $cap,
		'read_private_posts'     => $cap,
		'publish_posts'          => 'do_not_allow',
		'create_posts'           => 'do_not_allow',
	);
}

/**
 * Register the Messages and Subscribers post types.
 */
function teckform_register_form_post_types() {
	register_post_type( 'teckform_message', array(
		'labels'          => array(
			'name'               => __( 'Messages', 'teckform' ),
			'singular_name'      => __( 'Message', 'teckform' ),
			'menu_name'          => __( 'Messages', 'teckform' ),
			'all_items'          => __( 'Messages', 'teckform' ),
			'edit_item'          => __( 'Message', 'teckform' ),
			'view_item'          => __( 'View Message', 'teckform' ),
			'search_items'       => __( 'Search Messages', 'teckform' ),
			'not_found'          => __( 'No messages yet. Messages sent with the Contact Form widget appear here.', 'teckform' ),
			'not_found_in_trash' => __( 'No messages found in Trash.', 'teckform' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'rewrite'         => false,
		'query_var'       => false,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 26,
		'supports'        => false,
		'capabilities'    => teckform_form_post_type_caps(),
		'map_meta_cap'    => false,
	) );

	register_post_type( 'teckform_subscriber', array(
		'labels'          => array(
			'name'               => __( 'Subscribers', 'teckform' ),
			'singular_name'      => __( 'Subscriber', 'teckform' ),
			'menu_name'          => __( 'Subscribers', 'teckform' ),
			'all_items'          => __( 'Subscribers', 'teckform' ),
			'edit_item'          => __( 'Subscriber', 'teckform' ),
			'view_item'          => __( 'View Subscriber', 'teckform' ),
			'search_items'       => __( 'Search Subscribers', 'teckform' ),
			'not_found'          => __( 'No subscribers yet. Sign-ups from the Newsletter widget appear here.', 'teckform' ),
			'not_found_in_trash' => __( 'No subscribers found in Trash.', 'teckform' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=teckform_message',
		'show_in_rest'    => false,
		'rewrite'         => false,
		'query_var'       => false,
		'supports'        => false,
		'capabilities'    => teckform_form_post_type_caps(),
		'map_meta_cap'    => false,
	) );
}
add_action( 'init', 'teckform_register_form_post_types' );

/**
 * Number of unread messages.
 *
 * @return int
 */
function teckform_unread_message_count() {
	$query = new WP_Query( array(
		'post_type'      => 'teckform_message',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_teckform_read', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => '0', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	) );

	return (int) $query->found_posts;
}

/**
 * Show the unread count next to "Messages" in the admin menu.
 */
function teckform_message_menu_count() {
	global $menu;

	if ( ! current_user_can( teckform_form_capability() ) || ! is_array( $menu ) ) {
		return;
	}

	$count = teckform_unread_message_count();

	if ( ! $count ) {
		return;
	}

	foreach ( $menu as $index => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=teckform_message' === $item[2] ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Adding the count bubble is the standard way.
			$menu[ $index ][0] .= sprintf(
				' <span class="awaiting-mod count-%1$d"><span class="pending-count">%2$s</span></span>',
				$count,
				esc_html( number_format_i18n( $count ) )
			);
			break;
		}
	}
}
add_action( 'admin_menu', 'teckform_message_menu_count', 99 );

/**
 * Columns of the message list.
 *
 * @param array $columns List table columns.
 * @return array
 */
function teckform_message_columns( $columns ) {
	return array(
		'cb'                    => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox">',
		'title'                 => __( 'Subject', 'teckform' ),
		'teckform_from'         => __( 'From', 'teckform' ),
		'teckform_page'         => __( 'Page', 'teckform' ),
		'teckform_message_read' => __( 'Status', 'teckform' ),
		'date'                  => __( 'Date', 'teckform' ),
	);
}
add_filter( 'manage_teckform_message_posts_columns', 'teckform_message_columns' );

/**
 * Columns of the subscriber list.
 *
 * @param array $columns List table columns.
 * @return array
 */
function teckform_subscriber_columns( $columns ) {
	return array(
		'cb'              => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox">',
		'title'           => __( 'Email', 'teckform' ),
		'teckform_name'   => __( 'Name', 'teckform' ),
		'teckform_source' => __( 'Form', 'teckform' ),
		'teckform_page'   => __( 'Signed Up On', 'teckform' ),
		'date'            => __( 'Date', 'teckform' ),
	);
}
add_filter( 'manage_teckform_subscriber_posts_columns', 'teckform_subscriber_columns' );

/**
 * Link to the page a form was sent from.
 *
 * @param int $post_id Message or subscriber ID.
 * @return string Escaped HTML.
 */
function teckform_form_entry_page_link( $post_id ) {
	$url     = get_post_meta( $post_id, '_teckform_page_url', true );
	$page_id = (int) get_post_meta( $post_id, '_teckform_page_id', true );
	$title   = $page_id ? get_the_title( $page_id ) : '';

	if ( ! $url ) {
		return '&mdash;';
	}

	if ( '' === $title ) {
		$title = wp_parse_url( $url, PHP_URL_PATH );
		$title = $title ? $title : $url;
	}

	return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $title ) . '</a>';
}

/**
 * Print the message list columns.
 *
 * @param string $column  Column name.
 * @param int    $post_id Message ID.
 */
function teckform_message_column_content( $column, $post_id ) {
	if ( 'teckform_from' === $column ) {
		$name  = get_post_meta( $post_id, '_teckform_from_name', true );
		$email = get_post_meta( $post_id, '_teckform_from_email', true );

		if ( $name ) {
			echo '<strong>' . esc_html( $name ) . '</strong><br>';
		}

		if ( $email ) {
			echo '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>';
		}

		if ( ! $name && ! $email ) {
			echo '&mdash;';
		}
	}

	if ( 'teckform_page' === $column ) {
		echo teckform_form_entry_page_link( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the function.

		$form = get_post_meta( $post_id, '_teckform_form_name', true );

		if ( $form ) {
			echo '<br><span class="description">' . esc_html( $form ) . '</span>';
		}
	}

	if ( 'teckform_message_read' === $column ) {
		echo '1' === get_post_meta( $post_id, '_teckform_read', true )
			? esc_html__( 'Read', 'teckform' )
			: '<strong>' . esc_html__( 'Unread', 'teckform' ) . '</strong>';

		if ( '0' === get_post_meta( $post_id, '_teckform_mail_sent', true ) ) {
			echo '<br><span style="color:#b32d2e;">' . esc_html__( 'Email not sent', 'teckform' ) . '</span>';
		}
	}
}
add_action( 'manage_teckform_message_posts_custom_column', 'teckform_message_column_content', 10, 2 );

/**
 * Print the subscriber list columns.
 *
 * @param string $column  Column name.
 * @param int    $post_id Subscriber ID.
 */
function teckform_subscriber_column_content( $column, $post_id ) {
	if ( 'teckform_name' === $column ) {
		$name = get_post_meta( $post_id, '_teckform_name', true );
		echo $name ? esc_html( $name ) : '&mdash;';
	}

	if ( 'teckform_source' === $column ) {
		$source = get_post_meta( $post_id, '_teckform_source', true );
		echo $source ? esc_html( $source ) : '&mdash;';
	}

	if ( 'teckform_page' === $column ) {
		echo teckform_form_entry_page_link( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the function.
	}
}
add_action( 'manage_teckform_subscriber_posts_custom_column', 'teckform_subscriber_column_content', 10, 2 );

/**
 * Row links: "View" instead of "Edit", no Quick Edit, and read/unread for messages.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Entry.
 * @return array
 */
function teckform_form_row_actions( $actions, $post ) {
	if ( ! in_array( $post->post_type, array( 'teckform_message', 'teckform_subscriber' ), true ) ) {
		return $actions;
	}

	unset( $actions['inline hide-if-no-js'], $actions['view'] );

	if ( isset( $actions['edit'] ) ) {
		$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'View', 'teckform' ) . '</a>';
	}

	if ( 'teckform_message' === $post->post_type && 'trash' !== $post->post_status ) {
		$read = '1' === get_post_meta( $post->ID, '_teckform_read', true );

		$actions['teckform_read'] = '<a href="' . esc_url( teckform_message_read_url( $post->ID, ! $read ) ) . '">'
			. ( $read ? esc_html__( 'Mark as unread', 'teckform' ) : esc_html__( 'Mark as read', 'teckform' ) )
			. '</a>';
	}

	return $actions;
}
add_filter( 'post_row_actions', 'teckform_form_row_actions', 10, 2 );

/**
 * Link that marks a message read or unread.
 *
 * @param int  $post_id Message ID.
 * @param bool $read    New state.
 * @return string
 */
function teckform_message_read_url( $post_id, $read ) {
	return wp_nonce_url(
		add_query_arg( array(
			'action' => 'teckform_message_read',
			'post'   => $post_id,
			'read'   => $read ? '1' : '0',
		), admin_url( 'admin-post.php' ) ),
		'teckform_message_read_' . $post_id
	);
}

/**
 * Mark a message read or unread from its row link.
 */
function teckform_handle_message_read() {
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

	check_admin_referer( 'teckform_message_read_' . $post_id );

	if ( 'teckform_message' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to change this message.', 'teckform' ), 403 );
	}

	update_post_meta( $post_id, '_teckform_read', isset( $_GET['read'] ) && '1' === $_GET['read'] ? '1' : '0' );

	$back = wp_get_referer();

	wp_safe_redirect( $back && false === strpos( $back, 'post.php' ) ? $back : admin_url( 'edit.php?post_type=teckform_message' ) );
	exit;
}
add_action( 'admin_post_teckform_message_read', 'teckform_handle_message_read' );

/**
 * "Mark as read" and "Mark as unread" bulk actions.
 *
 * @param array $actions Bulk actions.
 * @return array
 */
function teckform_message_bulk_actions( $actions ) {
	unset( $actions['edit'] );

	return array(
		'teckform_mark_read'   => __( 'Mark as read', 'teckform' ),
		'teckform_mark_unread' => __( 'Mark as unread', 'teckform' ),
	) + $actions;
}
add_filter( 'bulk_actions-edit-teckform_message', 'teckform_message_bulk_actions' );

/**
 * Remove "Edit" from the subscriber bulk actions; entries are read-only.
 *
 * @param array $actions Bulk actions.
 * @return array
 */
function teckform_subscriber_bulk_actions( $actions ) {
	unset( $actions['edit'] );

	return $actions;
}
add_filter( 'bulk_actions-edit-teckform_subscriber', 'teckform_subscriber_bulk_actions' );

/**
 * Run the read/unread bulk actions. WordPress checks the nonce before this filter runs.
 *
 * @param string $redirect Where to go afterwards.
 * @param string $action   Bulk action.
 * @param int[]  $post_ids Selected messages.
 * @return string
 */
function teckform_handle_message_bulk_actions( $redirect, $action, $post_ids ) {
	if ( ! in_array( $action, array( 'teckform_mark_read', 'teckform_mark_unread' ), true ) ) {
		return $redirect;
	}

	foreach ( array_map( 'absint', (array) $post_ids ) as $post_id ) {
		if ( 'teckform_message' === get_post_type( $post_id ) && current_user_can( 'edit_post', $post_id ) ) {
			update_post_meta( $post_id, '_teckform_read', 'teckform_mark_read' === $action ? '1' : '0' );
		}
	}

	return $redirect;
}
add_filter( 'handle_bulk_actions-edit-teckform_message', 'teckform_handle_message_bulk_actions', 10, 3 );

/**
 * Bold unread messages in the list.
 *
 * @param string[] $classes Row classes.
 * @param string[] $class   Extra classes.
 * @param int      $post_id Post ID.
 * @return string[]
 */
function teckform_message_row_class( $classes, $class, $post_id ) {
	if ( is_admin() && 'teckform_message' === get_post_type( $post_id ) && '0' === get_post_meta( $post_id, '_teckform_read', true ) ) {
		$classes[] = 'teckform-unread';
	}

	return $classes;
}
add_filter( 'post_class', 'teckform_message_row_class', 10, 3 );

/**
 * Small admin styles for the form screens.
 */
function teckform_form_admin_styles() {
	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->post_type, array( 'teckform_message', 'teckform_subscriber' ), true ) ) {
		return;
	}
	?>
	<style>
		.teckform-unread .row-title { font-weight: 700; }
		.teckform-entry-table { width: 100%; border-collapse: collapse; }
		.teckform-entry-table th,
		.teckform-entry-table td { padding: 12px 10px; border-bottom: 1px solid #f0f0f1; text-align: start; vertical-align: top; }
		.teckform-entry-table th { width: 200px; color: #50575e; font-weight: 600; }
		.teckform-entry-table td { white-space: pre-wrap; word-break: break-word; }
		.teckform-entry-title { margin: 0 0 12px; font-size: 1.3em; }
		.teckform-entry-meta p { margin: 0 0 10px; }
		.teckform-entry-meta .button { margin-inline-end: 6px; }
		.post-type-teckform_message .page-title-action,
		.post-type-teckform_subscriber .page-title-action { display: none; }
	</style>
	<?php
}
add_action( 'admin_head', 'teckform_form_admin_styles' );

/**
 * Read-only view: replace the edit boxes with the entry's details,
 * and mark a message as read when it is opened.
 *
 * @param string  $post_type Post type.
 * @param WP_Post $post      Entry.
 */
function teckform_form_entry_meta_boxes( $post_type, $post ) {
	if ( ! in_array( $post_type, array( 'teckform_message', 'teckform_subscriber' ), true ) || ! $post instanceof WP_Post ) {
		return;
	}

	remove_meta_box( 'submitdiv', $post_type, 'side' );
	remove_meta_box( 'slugdiv', $post_type, 'normal' );

	if ( 'teckform_message' === $post_type ) {
		add_meta_box( 'teckform-entry', __( 'Message', 'teckform' ), 'teckform_render_message_box', $post_type, 'normal', 'high' );

		if ( 'auto-draft' !== $post->post_status ) {
			update_post_meta( $post->ID, '_teckform_read', '1' );
		}
	} else {
		add_meta_box( 'teckform-entry', __( 'Subscriber', 'teckform' ), 'teckform_render_subscriber_box', $post_type, 'normal', 'high' );
	}

	add_meta_box( 'teckform-entry-meta', __( 'Details', 'teckform' ), 'teckform_render_entry_meta_box', $post_type, 'side', 'high' );
}
add_action( 'add_meta_boxes', 'teckform_form_entry_meta_boxes', 10, 2 );

/**
 * All fields of a message.
 *
 * @param WP_Post $post Message.
 */
function teckform_render_message_box( $post ) {
	$fields = get_post_meta( $post->ID, '_teckform_fields', true );

	echo '<h2 class="teckform-entry-title">' . esc_html( get_the_title( $post ) ) . '</h2>';

	if ( ! is_array( $fields ) || ! $fields ) {
		echo '<p>' . esc_html__( 'This message has no fields.', 'teckform' ) . '</p>';
		return;
	}

	echo '<table class="teckform-entry-table" role="presentation">';

	foreach ( $fields as $field ) {
		$label = isset( $field['label'] ) ? $field['label'] : '';
		$value = isset( $field['value'] ) ? $field['value'] : '';

		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';

		if ( '' === $value ) {
			echo '&mdash;';
		} elseif ( isset( $field['type'] ) && 'email' === $field['type'] && is_email( $value ) ) {
			echo '<a href="' . esc_url( 'mailto:' . $value ) . '">' . esc_html( $value ) . '</a>';
		} else {
			echo esc_html( $value );
		}

		echo '</td></tr>';
	}

	echo '</table>';
}

/**
 * Details of a subscriber.
 *
 * @param WP_Post $post Subscriber.
 */
function teckform_render_subscriber_box( $post ) {
	$consent = get_post_meta( $post->ID, '_teckform_consent', true );
	$rows    = array(
		__( 'Email', 'teckform' )   => get_post_meta( $post->ID, '_teckform_email', true ),
		__( 'Name', 'teckform' )    => get_post_meta( $post->ID, '_teckform_name', true ),
		__( 'Form', 'teckform' )    => get_post_meta( $post->ID, '_teckform_source', true ),
		__( 'Consent', 'teckform' ) => $consent
			/* translators: %s: date and time consent was given. */
			? sprintf( __( 'Given on %s', 'teckform' ), mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $consent ) )
			: __( 'Not asked', 'teckform' ),
	);

	echo '<table class="teckform-entry-table" role="presentation">';

	foreach ( $rows as $label => $value ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . ( '' === (string) $value ? '&mdash;' : esc_html( $value ) ) . '</td></tr>';
	}

	echo '</table>';
}

/**
 * Side box: date, page, email status and actions.
 *
 * @param WP_Post $post Entry.
 */
function teckform_render_entry_meta_box( $post ) {
	$list = admin_url( 'edit.php?post_type=' . $post->post_type );

	echo '<div class="teckform-entry-meta">';
	/* translators: %s: date and time. */
	echo '<p>' . esc_html( sprintf( __( 'Received: %s', 'teckform' ), get_the_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $post ) ) ) . '</p>';
	echo '<p>' . esc_html__( 'Page:', 'teckform' ) . ' ' . teckform_form_entry_page_link( $post->ID ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the function.

	if ( 'teckform_message' === $post->post_type ) {
		$sent = get_post_meta( $post->ID, '_teckform_mail_sent', true );

		if ( '1' === $sent ) {
			echo '<p>' . esc_html__( 'Email notification: sent', 'teckform' ) . '</p>';
		} elseif ( '0' === $sent ) {
			echo '<p style="color:#b32d2e;">' . esc_html__( 'Email notification: not sent. Check your site\'s email settings.', 'teckform' ) . '</p>';
		}
	}

	echo '<p><a class="button" href="' . esc_url( $list ) . '">' . esc_html__( 'Back to list', 'teckform' ) . '</a>';

	if ( 'teckform_message' === $post->post_type ) {
		echo '<a class="button" href="' . esc_url( teckform_message_read_url( $post->ID, false ) ) . '">' . esc_html__( 'Mark as unread', 'teckform' ) . '</a>';
	}

	echo '</p>';

	if ( current_user_can( 'delete_post', $post->ID ) && 'trash' !== $post->post_status ) {
		echo '<p><a class="submitdelete deletion" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">' . esc_html__( 'Move to Trash', 'teckform' ) . '</a></p>';
	}

	echo '</div>';
}

/**
 * "Export CSV" button above the subscriber list.
 *
 * @param string $which 'top' or 'bottom'.
 */
function teckform_subscriber_export_button( $which ) {
	$screen = get_current_screen();

	if ( 'top' !== $which || ! $screen || 'teckform_subscriber' !== $screen->post_type || ! current_user_can( teckform_form_capability() ) ) {
		return;
	}

	$url = wp_nonce_url( admin_url( 'admin-post.php?action=teckform_export_subscribers' ), 'teckform_export_subscribers' );

	echo '<div class="alignleft actions"><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Export CSV', 'teckform' ) . '</a></div>';
}
add_action( 'manage_posts_extra_tablenav', 'teckform_subscriber_export_button' );

/**
 * Keep spreadsheet apps from running a cell as a formula.
 *
 * @param string $value Cell value.
 * @return string
 */
function teckform_csv_cell( $value ) {
	$value = (string) $value;

	return preg_match( '/^[=+\-@\t\r]/', $value ) ? "'" . $value : $value;
}

/**
 * Download all subscribers as a CSV file.
 */
function teckform_export_subscribers() {
	check_admin_referer( 'teckform_export_subscribers' );

	if ( ! current_user_can( teckform_form_capability() ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to export subscribers.', 'teckform' ), 403 );
	}

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="subscribers-' . gmdate( 'Y-m-d' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );

	// Byte order mark so Excel reads accents correctly.
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	fputcsv( $out, array( __( 'Email', 'teckform' ), __( 'Name', 'teckform' ), __( 'Form', 'teckform' ), __( 'Page', 'teckform' ), __( 'Consent', 'teckform' ), __( 'Date', 'teckform' ) ) );

	$page = 1;

	do {
		$ids = get_posts( array(
			'post_type'      => 'teckform_subscriber',
			'post_status'    => 'any',
			'posts_per_page' => 500,
			'paged'          => $page,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'fields'         => 'ids',
		) );

		foreach ( $ids as $id ) {
			fputcsv( $out, array_map( 'teckform_csv_cell', array(
				get_post_meta( $id, '_teckform_email', true ),
				get_post_meta( $id, '_teckform_name', true ),
				get_post_meta( $id, '_teckform_source', true ),
				get_post_meta( $id, '_teckform_page_url', true ),
				get_post_meta( $id, '_teckform_consent', true ),
				get_post_field( 'post_date', $id ),
			) ) );
		}

		++$page;
	} while ( count( $ids ) === 500 );

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_teckform_export_subscribers', 'teckform_export_subscribers' );
