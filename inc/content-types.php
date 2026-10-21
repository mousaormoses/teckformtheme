<?php
/**
 * Content types: Services, Projects (with categories), Team and Testimonials.
 *
 * Widgets such as Portfolio Grid, Team and Testimonials Carousel can pull
 * their items from these instead of typing them into each widget.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post type definitions, keyed by post type.
 *
 * @return array<string, array>
 */
function teckform_content_types() {
	return array(
		'teckform_service'     => array(
			'labels'   => array( __( 'Services', 'teckform' ), __( 'Service', 'teckform' ) ),
			'slug'     => 'services',
			'icon'     => 'dashicons-hammer',
			'public'   => true,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'elementor' ),
		),
		'teckform_project'     => array(
			'labels'   => array( __( 'Projects', 'teckform' ), __( 'Project', 'teckform' ) ),
			'slug'     => 'projects',
			'icon'     => 'dashicons-portfolio',
			'public'   => true,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'elementor' ),
		),
		'teckform_member'      => array(
			'labels'   => array( __( 'Team', 'teckform' ), __( 'Team Member', 'teckform' ) ),
			'slug'     => 'team',
			'icon'     => 'dashicons-groups',
			'public'   => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		),
		'teckform_testimonial' => array(
			'labels'   => array( __( 'Testimonials', 'teckform' ), __( 'Testimonial', 'teckform' ) ),
			'slug'     => 'testimonials',
			'icon'     => 'dashicons-format-quote',
			'public'   => false,
			'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		),
	);
}

/**
 * Extra fields shown in a box on each content type's edit screen.
 *
 * @return array<string, array<string, array{label: string, type: string}>>
 */
function teckform_content_type_fields() {
	$socials = array(
		'_teckform_facebook'  => array(
			'label' => __( 'Facebook URL', 'teckform' ),
			'type'  => 'url',
		),
		'_teckform_x'         => array(
			'label' => __( 'X (Twitter) URL', 'teckform' ),
			'type'  => 'url',
		),
		'_teckform_instagram' => array(
			'label' => __( 'Instagram URL', 'teckform' ),
			'type'  => 'url',
		),
		'_teckform_linkedin'  => array(
			'label' => __( 'LinkedIn URL', 'teckform' ),
			'type'  => 'url',
		),
	);

	return array(
		'teckform_service'     => array(
			'_teckform_icon' => array(
				'label' => __( 'Icon class (e.g. fas fa-code)', 'teckform' ),
				'type'  => 'text',
			),
		),
		'teckform_project'     => array(
			'_teckform_client' => array(
				'label' => __( 'Client', 'teckform' ),
				'type'  => 'text',
			),
			'_teckform_year'   => array(
				'label' => __( 'Year', 'teckform' ),
				'type'  => 'text',
			),
			'_teckform_url'    => array(
				'label' => __( 'Live project URL', 'teckform' ),
				'type'  => 'url',
			),
		),
		'teckform_member'      => array(
			'_teckform_role'  => array(
				'label' => __( 'Role', 'teckform' ),
				'type'  => 'text',
			),
			'_teckform_email' => array(
				'label' => __( 'Email', 'teckform' ),
				'type'  => 'email',
			),
		) + $socials,
		'teckform_testimonial' => array(
			'_teckform_role'   => array(
				'label' => __( 'Role and company (e.g. CEO, Acme)', 'teckform' ),
				'type'  => 'text',
			),
			'_teckform_rating' => array(
				'label' => __( 'Rating (1 to 5)', 'teckform' ),
				'type'  => 'rating',
			),
		),
	);
}

/**
 * Register the post types and the project category taxonomy.
 */
function teckform_register_content_types() {
	foreach ( teckform_content_types() as $type => $args ) {
		list( $plural, $singular ) = $args['labels'];

		register_post_type( $type, array(
			'labels'             => array(
				'name'          => $plural,
				'singular_name' => $singular,
				/* translators: %s: content type name, e.g. Project. */
				'add_new_item'  => sprintf( __( 'Add New %s', 'teckform' ), $singular ),
				/* translators: %s: content type name, e.g. Project. */
				'edit_item'     => sprintf( __( 'Edit %s', 'teckform' ), $singular ),
				/* translators: %s: content type name, e.g. Projects. */
				'search_items'  => sprintf( __( 'Search %s', 'teckform' ), $plural ),
				/* translators: %s: content type name, e.g. projects. */
				'not_found'     => sprintf( __( 'No %s found.', 'teckform' ), strtolower( $plural ) ),
			),
			'public'             => $args['public'],
			'publicly_queryable' => $args['public'],
			'show_ui'            => true,
			'show_in_rest'       => true,
			// No automatic archive lists: /services/ or /projects/ would hide a page with the same
			// address. Lists are built on pages with the Posts Grid, Portfolio and Team widgets.
			'has_archive'        => false,
			'rewrite'            => $args['public'] ? array( 'slug' => $args['slug'] ) : false,
			'menu_icon'          => $args['icon'],
			'menu_position'      => 25,
			'supports'           => $args['supports'],
		) );
	}

	register_taxonomy( 'teckform_project_cat', 'teckform_project', array(
		'labels'            => array(
			'name'          => __( 'Project Categories', 'teckform' ),
			'singular_name' => __( 'Project Category', 'teckform' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'project-category' ),
	) );
}
add_action( 'init', 'teckform_register_content_types' );

/**
 * New permalinks only work after the rewrite rules are rebuilt.
 */
function teckform_flush_rewrites_on_activation() {
	teckform_register_content_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'teckform_flush_rewrites_on_activation' );

/**
 * Field box on each content type's edit screen.
 */
function teckform_add_content_type_meta_boxes() {
	foreach ( teckform_content_type_fields() as $type => $fields ) {
		add_meta_box( 'teckform-details', __( 'Details', 'teckform' ), 'teckform_render_content_type_meta_box', $type, 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'teckform_add_content_type_meta_boxes' );

/**
 * @param WP_Post $post Post being edited.
 */
function teckform_render_content_type_meta_box( $post ) {
	$fields = teckform_content_type_fields();

	wp_nonce_field( 'teckform_details', 'teckform_details_nonce' );

	echo '<table class="form-table" role="presentation">';

	foreach ( $fields[ $post->post_type ] as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		$id    = 'teckform' . $key;

		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';

		if ( 'rating' === $field['type'] ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">';

			foreach ( array( '', '5', '4', '3', '2', '1' ) as $rating ) {
				echo '<option value="' . esc_attr( $rating ) . '"' . selected( $value, $rating, false ) . '>' . esc_html( '' === $rating ? __( 'No rating', 'teckform' ) : str_repeat( '★', (int) $rating ) ) . '</option>';
			}

			echo '</select>';
		} else {
			echo '<input type="' . esc_attr( $field['type'] ) . '" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
		}

		echo '</td></tr>';
	}

	echo '</table>';
}

/**
 * @param int $post_id Post ID.
 */
function teckform_save_content_type_fields( $post_id ) {
	$fields = teckform_content_type_fields();
	$type   = get_post_type( $post_id );

	if (
		! isset( $fields[ $type ], $_POST['teckform_details_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['teckform_details_nonce'] ) ), 'teckform_details' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	foreach ( $fields[ $type ] as $key => $field ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized per type below.

		switch ( $field['type'] ) {
			case 'url':
				$value = esc_url_raw( $raw );
				break;
			case 'email':
				$value = sanitize_email( $raw );
				break;
			case 'rating':
				$value = in_array( $raw, array( '1', '2', '3', '4', '5' ), true ) ? $raw : '';
				break;
			default:
				$value = sanitize_text_field( $raw );
		}

		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post', 'teckform_save_content_type_fields' );

/**
 * Social links of a team member that are filled in.
 *
 * @param int $post_id Team member ID.
 * @return array<string, string> Network => URL.
 */
function teckform_member_socials( $post_id ) {
	$links = array();

	foreach ( array( 'facebook', 'x', 'instagram', 'linkedin' ) as $network ) {
		$url = get_post_meta( $post_id, '_teckform_' . $network, true );

		if ( $url ) {
			$links[ $network ] = $url;
		}
	}

	return $links;
}
