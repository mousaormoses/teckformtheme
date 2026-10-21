<?php
/**
 * Header and footer templates built with Elementor.
 *
 * Templates are a post type edited in Elementor. Each one is a header or a
 * footer with display rules, and header.php / footer.php render the best
 * match for the current page.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the template post type.
 */
function teckform_register_template_post_type() {
	register_post_type( 'teckform_template', array(
		'labels'              => array(
			'name'               => __( 'Header & Footer', 'teckform' ),
			'singular_name'      => __( 'Template', 'teckform' ),
			'menu_name'          => __( 'Header & Footer', 'teckform' ),
			'add_new'            => __( 'Add New Template', 'teckform' ),
			'add_new_item'       => __( 'Add New Template', 'teckform' ),
			'edit_item'          => __( 'Edit Template', 'teckform' ),
			'new_item'           => __( 'New Template', 'teckform' ),
			'view_item'          => __( 'View Template', 'teckform' ),
			'search_items'       => __( 'Search Templates', 'teckform' ),
			'not_found'          => __( 'No templates found.', 'teckform' ),
			'not_found_in_trash' => __( 'No templates found in Trash.', 'teckform' ),
		),
		// Elementor needs a front-end URL to preview the template; visitors are redirected away from it.
		'public'              => true,
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'menu_icon'           => 'dashicons-layout',
		'menu_position'       => 60,
		'supports'            => array( 'title', 'elementor' ),
	) );
}
add_action( 'init', 'teckform_register_template_post_type' );

/**
 * Where a template can be shown.
 *
 * @return array<string, string>
 */
function teckform_template_display_options() {
	return array(
		'entire' => __( 'Entire site', 'teckform' ),
		'front'  => __( 'Front page only', 'teckform' ),
		'pages'  => __( 'Specific pages', 'teckform' ),
	);
}

/**
 * Sticky header choices. '1' means all devices.
 *
 * @return array<string, string>
 */
function teckform_sticky_options() {
	return array(
		''               => __( 'Off', 'teckform' ),
		'1'              => __( 'All devices', 'teckform' ),
		'desktop-tablet' => __( 'Desktop and tablet', 'teckform' ),
		'desktop'        => __( 'Desktop only', 'teckform' ),
	);
}

/**
 * Page IDs stored in a template's include/exclude list.
 *
 * @param int    $template_id Template ID.
 * @param string $key         Meta key.
 * @return int[]
 */
function teckform_get_template_page_ids( $template_id, $key ) {
	$ids = get_post_meta( $template_id, $key, true );

	return is_array( $ids ) ? array_map( 'absint', $ids ) : array();
}

/**
 * Settings box on the template edit screen.
 */
function teckform_add_template_meta_box() {
	add_meta_box(
		'teckform-template-settings',
		__( 'Template Settings', 'teckform' ),
		'teckform_render_template_meta_box',
		'teckform_template',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_teckform_template', 'teckform_add_template_meta_box' );

/**
 * Print the settings box.
 *
 * @param WP_Post $post Template being edited.
 */
function teckform_render_template_meta_box( $post ) {
	$type        = get_post_meta( $post->ID, '_teckform_type', true ) ?: 'header';
	$display     = get_post_meta( $post->ID, '_teckform_display', true ) ?: 'entire';
	$include     = teckform_get_template_page_ids( $post->ID, '_teckform_include' );
	$exclude     = teckform_get_template_page_ids( $post->ID, '_teckform_exclude' );
	$sticky      = get_post_meta( $post->ID, '_teckform_sticky', true );
	$transparent = get_post_meta( $post->ID, '_teckform_transparent', true );
	$scrolled_bg = get_post_meta( $post->ID, '_teckform_scrolled_bg', true );
	$hide_scroll = get_post_meta( $post->ID, '_teckform_hide_on_scroll', true );
	$shadow      = get_post_meta( $post->ID, '_teckform_shadow', true );
	$pages       = get_pages();

	wp_nonce_field( 'teckform_template_settings', 'teckform_template_nonce' );
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="teckform-type"><?php esc_html_e( 'Type', 'teckform' ); ?></label></th>
			<td>
				<select id="teckform-type" name="teckform_type">
					<option value="header" <?php selected( $type, 'header' ); ?>><?php esc_html_e( 'Header', 'teckform' ); ?></option>
					<option value="footer" <?php selected( $type, 'footer' ); ?>><?php esc_html_e( 'Footer', 'teckform' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="teckform-display"><?php esc_html_e( 'Display on', 'teckform' ); ?></label></th>
			<td>
				<select id="teckform-display" name="teckform_display">
					<?php foreach ( teckform_template_display_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $display, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'When several templates match a page, the most specific one wins: specific pages, then front page, then entire site.', 'teckform' ); ?></p>
			</td>
		</tr>
		<tr class="teckform-row-pages">
			<th scope="row"><label for="teckform-include"><?php esc_html_e( 'Pages', 'teckform' ); ?></label></th>
			<td>
				<select id="teckform-include" name="teckform_include[]" multiple size="8" style="min-width: 320px;">
					<?php foreach ( $pages as $page ) : ?>
						<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( in_array( $page->ID, $include, true ) ); ?>><?php echo esc_html( $page->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Hold Ctrl (Cmd on Mac) to select more than one page.', 'teckform' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="teckform-exclude"><?php esc_html_e( 'Hide on', 'teckform' ); ?></label></th>
			<td>
				<select id="teckform-exclude" name="teckform_exclude[]" multiple size="8" style="min-width: 320px;">
					<?php foreach ( $pages as $page ) : ?>
						<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( in_array( $page->ID, $exclude, true ) ); ?>><?php echo esc_html( $page->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Pages that should not use this template.', 'teckform' ); ?></p>
			</td>
		</tr>
		<tr class="teckform-row-header">
			<th scope="row"><label for="teckform-sticky"><?php esc_html_e( 'Sticky', 'teckform' ); ?></label></th>
			<td>
				<select id="teckform-sticky" name="teckform_sticky">
					<?php foreach ( teckform_sticky_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $sticky, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Keep the header at the top of the screen while scrolling.', 'teckform' ); ?></p>
			</td>
		</tr>
		<tr class="teckform-row-header">
			<th scope="row"><?php esc_html_e( 'While scrolling', 'teckform' ); ?></th>
			<td>
				<label><input type="checkbox" name="teckform_hide_on_scroll" value="1" <?php checked( $hide_scroll, '1' ); ?>> <?php esc_html_e( 'Hide when scrolling down, show again when scrolling up (sticky headers)', 'teckform' ); ?></label><br>
				<label><input type="checkbox" name="teckform_shadow" value="1" <?php checked( '0' !== $shadow ); ?>> <?php esc_html_e( 'Show a shadow under the header after scrolling', 'teckform' ); ?></label>
			</td>
		</tr>
		<tr class="teckform-row-header">
			<th scope="row"><?php esc_html_e( 'Transparent', 'teckform' ); ?></th>
			<td>
				<label><input type="checkbox" name="teckform_transparent" value="1" <?php checked( $transparent, '1' ); ?>> <?php esc_html_e( 'Place the header over the top of the page', 'teckform' ); ?></label>
				<p class="description"><?php esc_html_e( 'Give the header sections a transparent background in Elementor so the page shows through.', 'teckform' ); ?></p>
			</td>
		</tr>
		<tr class="teckform-row-header">
			<th scope="row"><label for="teckform-scrolled-bg"><?php esc_html_e( 'Background after scrolling', 'teckform' ); ?></label></th>
			<td>
				<input type="text" id="teckform-scrolled-bg" class="teckform-color-field" name="teckform_scrolled_bg" value="<?php echo esc_attr( $scrolled_bg ); ?>">
				<p class="description"><?php esc_html_e( 'Background of a sticky or transparent header once the page is scrolled.', 'teckform' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Color picker and show/hide logic for the settings box.
 *
 * @param string $hook_suffix Current admin page.
 */
function teckform_template_admin_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) || 'teckform_template' !== get_current_screen()->post_type ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script( 'wp-color-picker', "
		jQuery( function ( $ ) {
			var type = $( '#teckform-type' );
			var display = $( '#teckform-display' );

			function update() {
				$( '.teckform-row-header' ).toggle( 'header' === type.val() );
				$( '.teckform-row-pages' ).toggle( 'pages' === display.val() );
			}

			type.add( display ).on( 'change', update );
			update();
			$( '.teckform-color-field' ).wpColorPicker();
		} );
	" );
}
add_action( 'admin_enqueue_scripts', 'teckform_template_admin_assets' );

/**
 * Save the settings box.
 *
 * @param int $post_id Template ID.
 */
function teckform_save_template_settings( $post_id ) {
	if (
		! isset( $_POST['teckform_template_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['teckform_template_nonce'] ) ), 'teckform_template_settings' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	$type    = isset( $_POST['teckform_type'] ) && 'footer' === $_POST['teckform_type'] ? 'footer' : 'header';
	$display = isset( $_POST['teckform_display'] ) ? sanitize_key( wp_unslash( $_POST['teckform_display'] ) ) : 'entire';

	if ( ! array_key_exists( $display, teckform_template_display_options() ) ) {
		$display = 'entire';
	}

	$include = isset( $_POST['teckform_include'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['teckform_include'] ) ) : array();
	$exclude = isset( $_POST['teckform_exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['teckform_exclude'] ) ) : array();
	$bg      = isset( $_POST['teckform_scrolled_bg'] ) ? sanitize_hex_color( wp_unslash( $_POST['teckform_scrolled_bg'] ) ) : '';

	update_post_meta( $post_id, '_teckform_type', $type );
	update_post_meta( $post_id, '_teckform_display', $display );
	update_post_meta( $post_id, '_teckform_include', $include );
	update_post_meta( $post_id, '_teckform_exclude', $exclude );
	$sticky = isset( $_POST['teckform_sticky'] ) ? sanitize_key( wp_unslash( $_POST['teckform_sticky'] ) ) : '';

	update_post_meta( $post_id, '_teckform_sticky', array_key_exists( $sticky, teckform_sticky_options() ) ? $sticky : '' );
	update_post_meta( $post_id, '_teckform_transparent', isset( $_POST['teckform_transparent'] ) ? '1' : '' );
	update_post_meta( $post_id, '_teckform_hide_on_scroll', isset( $_POST['teckform_hide_on_scroll'] ) ? '1' : '' );
	update_post_meta( $post_id, '_teckform_shadow', isset( $_POST['teckform_shadow'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_teckform_scrolled_bg', (string) $bg );
}
add_action( 'save_post_teckform_template', 'teckform_save_template_settings' );

/**
 * Add "Type" and "Display On" columns to the template list.
 *
 * @param array $columns List table columns.
 * @return array
 */
function teckform_template_columns( $columns ) {
	$new = array(
		'teckform_type'    => __( 'Type', 'teckform' ),
		'teckform_display' => __( 'Display On', 'teckform' ),
	);

	return array_slice( $columns, 0, 2, true ) + $new + array_slice( $columns, 2, null, true );
}
add_filter( 'manage_teckform_template_posts_columns', 'teckform_template_columns' );

/**
 * Print the custom column values.
 *
 * @param string $column  Column name.
 * @param int    $post_id Template ID.
 */
function teckform_template_column_content( $column, $post_id ) {
	if ( 'teckform_type' === $column ) {
		echo esc_html( 'footer' === get_post_meta( $post_id, '_teckform_type', true ) ? __( 'Footer', 'teckform' ) : __( 'Header', 'teckform' ) );
	}

	if ( 'teckform_display' === $column ) {
		$display = get_post_meta( $post_id, '_teckform_display', true );
		$options = teckform_template_display_options();
		$label   = $options[ $display ] ?? $options['entire'];

		if ( 'pages' === $display ) {
			$count = count( teckform_get_template_page_ids( $post_id, '_teckform_include' ) );
			/* translators: %d: number of pages. */
			$label = sprintf( _n( '%d page', '%d pages', $count, 'teckform' ), $count );
		}

		echo esc_html( $label );
	}
}
add_action( 'manage_teckform_template_posts_custom_column', 'teckform_template_column_content', 10, 2 );

/**
 * Post types that get the per-page "Header & Footer" box.
 *
 * @return string[]
 */
function teckform_page_override_post_types() {
	$types = get_post_types( array( 'public' => true ) );

	unset( $types['attachment'], $types['teckform_template'] );

	return array_values( $types );
}

/**
 * Per-page box to pick the header and footer, or hide them.
 */
function teckform_add_page_override_meta_box() {
	add_meta_box(
		'teckform-page-layout',
		__( 'Header & Footer', 'teckform' ),
		'teckform_render_page_override_meta_box',
		teckform_page_override_post_types(),
		'side'
	);
}
add_action( 'add_meta_boxes', 'teckform_add_page_override_meta_box' );

/**
 * Print the per-page box.
 *
 * @param WP_Post $post Post being edited.
 */
function teckform_render_page_override_meta_box( $post ) {
	wp_nonce_field( 'teckform_page_layout', 'teckform_page_layout_nonce' );

	foreach ( array( 'header' => __( 'Header', 'teckform' ), 'footer' => __( 'Footer', 'teckform' ) ) as $type => $label ) {
		$current   = get_post_meta( $post->ID, '_teckform_' . $type, true );
		$templates = get_posts( array(
			'post_type'      => 'teckform_template',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => '_teckform_type',
			'meta_value'     => $type,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		?>
		<p>
			<label for="teckform-page-<?php echo esc_attr( $type ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br>
			<select id="teckform-page-<?php echo esc_attr( $type ); ?>" name="teckform_page_<?php echo esc_attr( $type ); ?>" style="width: 100%;">
				<option value=""><?php esc_html_e( 'Default (display rules)', 'teckform' ); ?></option>
				<option value="hide" <?php selected( $current, 'hide' ); ?>><?php esc_html_e( 'Hide on this page', 'teckform' ); ?></option>
				<?php foreach ( $templates as $template ) : ?>
					<option value="<?php echo esc_attr( $template->ID ); ?>" <?php selected( (int) $current, $template->ID ); ?>><?php echo esc_html( $template->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}
}

/**
 * Save the per-page box.
 *
 * @param int $post_id Post ID.
 */
function teckform_save_page_override( $post_id ) {
	if (
		! isset( $_POST['teckform_page_layout_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['teckform_page_layout_nonce'] ) ), 'teckform_page_layout' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	foreach ( array( 'header', 'footer' ) as $type ) {
		$value = isset( $_POST[ 'teckform_page_' . $type ] ) ? sanitize_key( wp_unslash( $_POST[ 'teckform_page_' . $type ] ) ) : '';

		if ( 'hide' !== $value ) {
			$value = 'teckform_template' === get_post_type( absint( $value ) ) ? (string) absint( $value ) : '';
		}

		if ( '' === $value ) {
			delete_post_meta( $post_id, '_teckform_' . $type );
		} else {
			update_post_meta( $post_id, '_teckform_' . $type, $value );
		}
	}
}
add_action( 'save_post', 'teckform_save_page_override' );

/**
 * The current page's own header/footer choice.
 *
 * @param string $type 'header' or 'footer'.
 * @return string '' for the display rules, 'hide', or a template ID.
 */
function teckform_get_page_override( $type ) {
	if ( ! is_singular() ) {
		return '';
	}

	return (string) get_post_meta( get_queried_object_id(), '_teckform_' . $type, true );
}

/**
 * Keep visitors away from the template preview URLs.
 */
function teckform_protect_template_urls() {
	if ( is_singular( 'teckform_template' ) && ! current_user_can( 'edit_posts' ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
}
add_action( 'template_redirect', 'teckform_protect_template_urls' );

/**
 * Edit templates on Elementor's blank canvas instead of inside the site layout.
 *
 * @param string $template Template file.
 * @return string
 */
function teckform_template_canvas( $template ) {
	if ( is_singular( 'teckform_template' ) && defined( 'ELEMENTOR_PATH' ) ) {
		$canvas = ELEMENTOR_PATH . 'modules/page-templates/templates/canvas.php';

		if ( file_exists( $canvas ) ) {
			return $canvas;
		}
	}

	return $template;
}
add_filter( 'template_include', 'teckform_template_canvas', 99 );

/**
 * Find the template of the given type that best matches the current page.
 *
 * @param string $type 'header' or 'footer'.
 * @return int Template ID, or 0 when none matches or Elementor is inactive.
 */
function teckform_get_template_id( $type ) {
	static $cache = array();

	if ( isset( $cache[ $type ] ) ) {
		return $cache[ $type ];
	}

	$cache[ $type ] = 0;

	if ( ! did_action( 'elementor/loaded' ) ) {
		return 0;
	}

	$override = teckform_get_page_override( $type );

	if ( 'hide' === $override ) {
		return 0;
	}

	if ( $override && 'teckform_template' === get_post_type( (int) $override ) && 'publish' === get_post_status( (int) $override ) ) {
		$cache[ $type ] = (int) $override;
		return $cache[ $type ];
	}

	$template_ids = get_posts( array(
		'post_type'      => 'teckform_template',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_key'       => '_teckform_type',
		'meta_value'     => $type,
		'no_found_rows'  => true,
	) );

	$page_id    = is_singular() ? get_queried_object_id() : 0;
	$best_score = 0;

	foreach ( $template_ids as $template_id ) {
		if ( $page_id && in_array( $page_id, teckform_get_template_page_ids( $template_id, '_teckform_exclude' ), true ) ) {
			continue;
		}

		$display = get_post_meta( $template_id, '_teckform_display', true );

		if ( 'pages' === $display ) {
			$score = $page_id && in_array( $page_id, teckform_get_template_page_ids( $template_id, '_teckform_include' ), true ) ? 3 : 0;
		} elseif ( 'front' === $display ) {
			$score = is_front_page() ? 2 : 0;
		} else {
			$score = 1;
		}

		// Templates are newest first, so ties keep the newest one.
		if ( $score > $best_score ) {
			$best_score     = $score;
			$cache[ $type ] = $template_id;
		}
	}

	return $cache[ $type ];
}

/**
 * Load Elementor's CSS for the active header and footer in <head>,
 * so they don't flash unstyled.
 */
function teckform_enqueue_template_styles() {
	$template_ids = array_filter( array( teckform_get_template_id( 'header' ), teckform_get_template_id( 'footer' ) ) );

	if ( ! $template_ids ) {
		return;
	}

	\Elementor\Plugin::instance()->frontend->enqueue_styles();

	foreach ( $template_ids as $template_id ) {
		\Elementor\Core\Files\CSS\Post::create( $template_id )->enqueue();
	}
}
add_action( 'wp_enqueue_scripts', 'teckform_enqueue_template_styles' );

/**
 * Print the matching header or footer template.
 *
 * @param string $type 'header' or 'footer'.
 * @return bool Whether the header/footer was handled (printed, or hidden for this page).
 */
function teckform_render_template( $type ) {
	if ( 'hide' === teckform_get_page_override( $type ) ) {
		return true;
	}

	$template_id = teckform_get_template_id( $type );

	if ( ! $template_id ) {
		return false;
	}

	$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );

	if ( 'footer' === $type ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
		echo '<footer class="teckform-footer">' . $content . '</footer>';
		return true;
	}

	$classes = array( 'teckform-header' );
	$style   = '';

	$sticky = get_post_meta( $template_id, '_teckform_sticky', true );

	if ( $sticky ) {
		$classes[] = 'teckform-header--sticky';

		if ( in_array( $sticky, array( 'desktop', 'desktop-tablet' ), true ) ) {
			$classes[] = 'teckform-header--sticky-' . $sticky;
		}

		if ( get_post_meta( $template_id, '_teckform_hide_on_scroll', true ) ) {
			$classes[] = 'teckform-header--hide-on-scroll';
		}
	}

	if ( get_post_meta( $template_id, '_teckform_transparent', true ) ) {
		$classes[] = 'teckform-header--transparent';
	}

	if ( '0' !== get_post_meta( $template_id, '_teckform_shadow', true ) ) {
		$classes[] = 'teckform-header--shadow';
	}

	$scrolled_bg = get_post_meta( $template_id, '_teckform_scrolled_bg', true );

	if ( $scrolled_bg ) {
		$style = ' style="--teckform-header-scrolled-bg: ' . esc_attr( $scrolled_bg ) . ';"';
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $style is escaped above; $content is Elementor output.
	echo '<header class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $style . '>' . $content . '</header>';

	return true;
}
