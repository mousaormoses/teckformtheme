<?php
/**
 * One-click demo import: Appearance > Import Demo.
 *
 * Builds the "Teckform Studio" agency demo: images (generated on the server,
 * so there are no licensing issues), content, menus, header/footer templates,
 * pages built with Teckform widgets, and the Elementor colors and fonts.
 * Everything it creates is marked with `_teckform_demo` so it can be removed again.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/elementor-builder.php';
require_once __DIR__ . '/content.php';
require_once __DIR__ . '/pages.php';

/**
 * Admin page.
 */
function teckform_demo_admin_menu() {
	add_theme_page(
		__( 'Import Demo', 'teckform' ),
		__( 'Import Demo', 'teckform' ),
		'manage_options',
		'teckform-demo',
		'teckform_render_demo_page'
	);
}
add_action( 'admin_menu', 'teckform_demo_admin_menu' );

/**
 * Print the admin page.
 */
function teckform_render_demo_page() {
	$imported = (bool) get_option( 'teckform_demo_imported' );
	$message  = isset( $_GET['teckform-demo'] ) ? sanitize_key( $_GET['teckform-demo'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only.
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Demo', 'teckform' ); ?></h1>

		<?php if ( 'imported' === $message ) : ?>
			<div class="notice notice-success"><p>
				<?php esc_html_e( 'The demo is ready.', 'teckform' ); ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View your site', 'teckform' ); ?></a>
			</p></div>
		<?php elseif ( 'removed' === $message ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'The demo content was removed.', 'teckform' ); ?></p></div>
		<?php elseif ( 'no-elementor' === $message ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Install and activate Elementor first.', 'teckform' ); ?></p></div>
		<?php endif; ?>

		<div class="card" style="max-width: 720px;">
			<h2><?php esc_html_e( 'Teckform Studio: digital agency demo', 'teckform' ); ?></h2>
			<p><?php esc_html_e( 'Creates a complete agency website you can edit: Home, About, Services, Portfolio, Pricing, Blog, Contact, Careers and Coming Soon pages, plus services, projects, team members, testimonials, blog posts, menus, a header, a footer, a mega menu, a popup and the Teckform colors and fonts.', 'teckform' ); ?></p>
			<p><?php esc_html_e( 'Your existing pages and posts are not changed. The demo pages become your home page and menus.', 'teckform' ); ?></p>

			<?php if ( ! did_action( 'elementor/loaded' ) ) : ?>
				<p><strong><?php esc_html_e( 'The demo needs the free Elementor plugin. Install and activate it first.', 'teckform' ); ?></strong></p>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block;">
					<input type="hidden" name="action" value="teckform_demo_import">
					<?php wp_nonce_field( 'teckform_demo_import' ); ?>
					<?php submit_button( $imported ? __( 'Import again', 'teckform' ) : __( 'Import demo', 'teckform' ), 'primary', 'submit', false ); ?>
				</form>
			<?php endif; ?>

			<?php if ( $imported ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block; margin-inline-start: 8px;" onsubmit="return confirm('<?php echo esc_js( __( 'Delete everything the demo created, including its images? Your own content is kept.', 'teckform' ) ); ?>');">
					<input type="hidden" name="action" value="teckform_demo_remove">
					<?php wp_nonce_field( 'teckform_demo_remove' ); ?>
					<?php submit_button( __( 'Remove demo content', 'teckform' ), 'secondary', 'submit', false ); ?>
				</form>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Handle the Import button.
 */
function teckform_demo_import_action() {
	check_admin_referer( 'teckform_demo_import' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'teckform' ) );
	}

	$result = teckform_demo_import();

	wp_safe_redirect( admin_url( 'themes.php?page=teckform-demo&teckform-demo=' . ( $result ? 'imported' : 'no-elementor' ) ) );
	exit;
}
add_action( 'admin_post_teckform_demo_import', 'teckform_demo_import_action' );

/**
 * Handle the Remove button.
 */
function teckform_demo_remove_action() {
	check_admin_referer( 'teckform_demo_remove' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'teckform' ) );
	}

	teckform_demo_remove();

	wp_safe_redirect( admin_url( 'themes.php?page=teckform-demo&teckform-demo=removed' ) );
	exit;
}
add_action( 'admin_post_teckform_demo_remove', 'teckform_demo_remove_action' );

/**
 * Run the whole import. Safe to run again: earlier demo content is removed first.
 *
 * @return bool False when Elementor isn't active.
 */
function teckform_demo_import() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	teckform_demo_remove();

	$images  = teckform_demo_create_images();
	$content = teckform_demo_create_content( $images );
	$menus   = teckform_demo_create_menus( $content );
	$parts   = teckform_demo_create_templates( $content, $images, $menus );

	teckform_demo_create_pages( $content, $images, $parts );
	teckform_demo_link_menus( $content, $menus, $parts );

	update_option( 'blogname', 'Teckform Studio' );
	update_option( 'blogdescription', __( 'Websites, apps and brands that grow your business', 'teckform' ) );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $content['pages']['home'] );
	update_option( 'teckform_demo_imported', 1 );

	// No logo image: the Site Logo widget shows the site name in the heading font, which
	// looks cleaner than a generated logo. Buyers upload theirs in Customize > Site Identity.
	teckform_apply_elementor_palette( true );
	flush_rewrite_rules();

	return true;
}

/**
 * Delete everything the demo created.
 */
function teckform_demo_remove() {
	$types = array_merge( array( 'page', 'post', 'attachment', 'teckform_template' ), array_keys( teckform_content_types() ) );

	$posts = get_posts( array(
		'post_type'      => $types,
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_teckform_demo',
	) );

	foreach ( $posts as $post_id ) {
		if ( 'attachment' === get_post_type( $post_id ) ) {
			wp_delete_attachment( $post_id, true );
		} else {
			wp_delete_post( $post_id, true );
		}
	}

	foreach ( (array) get_option( 'teckform_demo_menus', array() ) as $menu_id ) {
		wp_delete_nav_menu( (int) $menu_id );
	}

	foreach ( (array) get_option( 'teckform_demo_terms', array() ) as $term_id ) {
		wp_delete_term( (int) $term_id, 'teckform_project_cat' );
	}

	delete_option( 'teckform_demo_menus' );
	delete_option( 'teckform_demo_terms' );
	delete_option( 'teckform_demo_imported' );
}

/**
 * Insert a post marked as demo content.
 *
 * @param array $args wp_insert_post() arguments, plus optional 'meta' and 'elementor' (element tree).
 * @return int Post ID.
 */
function teckform_demo_insert( $args ) {
	$meta      = isset( $args['meta'] ) ? $args['meta'] : array();
	$elementor = isset( $args['elementor'] ) ? $args['elementor'] : null;

	unset( $args['meta'], $args['elementor'] );

	$post_id = wp_insert_post( wp_slash( wp_parse_args( $args, array(
		'post_status' => 'publish',
		'post_type'   => 'page',
	) ) ) );

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	update_post_meta( $post_id, '_teckform_demo', 1 );

	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	if ( null !== $elementor ) {
		teckform_demo_set_elementor( $post_id, $elementor );
	}

	return $post_id;
}

/**
 * Save an Elementor element tree on a post.
 *
 * @param int   $post_id  Post ID.
 * @param array $elements Element tree.
 */
function teckform_demo_set_elementor( $post_id, $elements ) {
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'page' === get_post_type( $post_id ) ? 'wp-page' : 'wp-post' );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	// Elementor expects slashed JSON in this meta.
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
}

/* -------------------------------------------------------------------------
 * Images
 * ---------------------------------------------------------------------- */

/**
 * Generate the demo images as JPEGs with GD: soft gradients with abstract shapes.
 *
 * @return array<string, int> Name => attachment ID (empty when GD isn't available).
 */
function teckform_demo_create_images() {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return array();
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$palettes = array(
		array( '#1e3a8a', '#2563eb' ),
		array( '#0f766e', '#22d3ee' ),
		array( '#7c3aed', '#ec4899' ),
		array( '#b45309', '#f59e0b' ),
		array( '#0f172a', '#334155' ),
		array( '#be123c', '#fb7185' ),
		array( '#065f46', '#34d399' ),
		array( '#1d4ed8', '#a855f7' ),
	);

	$images = array();
	$specs  = array(
		'hero'      => array( 1600, 1000, 0 ),
		'office'    => array( 1600, 1000, 4 ),
		'dashboard' => array( 1600, 1000, 7 ),
		'about'     => array( 1200, 900, 1 ),
	);

	for ( $i = 1; $i <= 6; $i++ ) {
		$specs[ 'project-' . $i ] = array( 1200, 900, $i + 1 );
		$specs[ 'post-' . $i ]    = array( 1200, 675, $i );
	}

	for ( $i = 1; $i <= 6; $i++ ) {
		$specs[ 'person-' . $i ] = array( 600, 600, $i + 2 );
	}

	foreach ( $specs as $name => $spec ) {
		list( $width, $height, $palette ) = $spec;

		$id = teckform_demo_generate_image( $name, $width, $height, $palettes[ $palette % count( $palettes ) ], 0 === strpos( $name, 'person-' ) );

		if ( $id ) {
			$images[ $name ] = $id;
		}
	}

	for ( $i = 1; $i <= 6; $i++ ) {
		$client = teckform_demo_generate_client_logo( $i );

		if ( $client ) {
			$images[ 'client-' . $i ] = $client;
		}
	}

	return $images;
}

/**
 * @param string $hex Hex color.
 * @return int[] RGB.
 */
function teckform_demo_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );

	return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

/**
 * One gradient image with soft circles; portraits get a simple head-and-shoulders shape.
 *
 * @param string $name     File name.
 * @param int    $width    Width.
 * @param int    $height   Height.
 * @param array  $colors   Two hex colors.
 * @param bool   $portrait Draw a person silhouette.
 * @return int Attachment ID.
 */
function teckform_demo_generate_image( $name, $width, $height, $colors, $portrait ) {
	$image = imagecreatetruecolor( $width, $height );
	$from  = teckform_demo_rgb( $colors[0] );
	$to    = teckform_demo_rgb( $colors[1] );

	// Diagonal gradient, drawn as vertical bands.
	for ( $x = 0; $x < $width; $x += 4 ) {
		$t     = $x / $width;
		$color = imagecolorallocate(
			$image,
			(int) ( $from[0] + ( $to[0] - $from[0] ) * $t ),
			(int) ( $from[1] + ( $to[1] - $from[1] ) * $t ),
			(int) ( $from[2] + ( $to[2] - $from[2] ) * $t )
		);
		imagefilledrectangle( $image, $x, 0, $x + 3, $height, $color );
	}

	$white = imagecolorallocatealpha( $image, 255, 255, 255, 105 );

	if ( $portrait ) {
		$skin = imagecolorallocatealpha( $image, 255, 255, 255, 60 );
		imagefilledellipse( $image, (int) ( $width / 2 ), (int) ( $height * 0.4 ), (int) ( $width * 0.36 ), (int) ( $width * 0.36 ), $skin );
		imagefilledellipse( $image, (int) ( $width / 2 ), (int) ( $height * 1.02 ), (int) ( $width * 0.8 ), (int) ( $height * 0.75 ), $skin );
	} else {
		mt_srand( crc32( $name ) );

		for ( $i = 0; $i < 6; $i++ ) {
			$size = mt_rand( (int) ( $width * 0.15 ), (int) ( $width * 0.45 ) );
			imagefilledellipse( $image, mt_rand( 0, $width ), mt_rand( 0, $height ), $size, $size, $white );
		}

		mt_srand(); // Back to random seeding for everything else.

		// A few "interface" bars so it reads like a screen or a project shot.
		$card = imagecolorallocatealpha( $image, 255, 255, 255, 80 );
		imagefilledrectangle( $image, (int) ( $width * 0.12 ), (int) ( $height * 0.2 ), (int) ( $width * 0.88 ), (int) ( $height * 0.8 ), $card );
		imagefilledrectangle( $image, (int) ( $width * 0.16 ), (int) ( $height * 0.28 ), (int) ( $width * 0.5 ), (int) ( $height * 0.34 ), $white );
		imagefilledrectangle( $image, (int) ( $width * 0.16 ), (int) ( $height * 0.4 ), (int) ( $width * 0.84 ), (int) ( $height * 0.72 ), $white );
	}

	return teckform_demo_save_image( $image, 'teckform-demo-' . $name . '.jpg', 'jpg' );
}

/**
 * A simple client logo: a shape and a made-up company name.
 *
 * GD's built-in font is only 15px tall, so the logo is drawn small and then
 * scaled up 3x, which makes the name readable at the size logos are shown.
 *
 * @param int $n Logo number.
 * @return int Attachment ID.
 */
function teckform_demo_generate_client_logo( $n ) {
	$names = array( 'NORTHWIND', 'LUMEN', 'ORBITAL', 'KESTREL', 'VERTEX', 'HALCYON' );
	$small = imagecreatetruecolor( 120, 32 );

	imagesavealpha( $small, true );
	imagealphablending( $small, false );
	imagefill( $small, 0, 0, imagecolorallocatealpha( $small, 0, 0, 0, 127 ) );
	imagealphablending( $small, true );

	$dark = imagecolorallocate( $small, 55, 65, 81 );

	if ( 0 === $n % 2 ) {
		imagefilledellipse( $small, 12, 16, 18, 18, $dark );
	} else {
		imagefilledpolygon( $small, array( 3, 25, 12, 7, 21, 25 ), $dark );
	}

	imagestring( $small, 5, 28, 8, $names[ ( $n - 1 ) % count( $names ) ], $dark );

	$image = imagecreatetruecolor( 360, 96 );

	imagesavealpha( $image, true );
	imagealphablending( $image, false );
	imagefill( $image, 0, 0, imagecolorallocatealpha( $image, 0, 0, 0, 127 ) );
	imagecopyresampled( $image, $small, 0, 0, 0, 0, 360, 96, 120, 32 );
	imagedestroy( $small );

	return teckform_demo_save_image( $image, 'teckform-demo-client-' . $n . '.png', 'png' );
}

/**
 * Write a GD image to the uploads folder and add it to the Media Library.
 *
 * @param resource|\GdImage $image    GD image.
 * @param string            $filename File name.
 * @param string            $format   'jpg' or 'png'.
 * @return int Attachment ID, or 0.
 */
function teckform_demo_save_image( $image, $filename, $format ) {
	ob_start();

	if ( 'png' === $format ) {
		imagepng( $image );
	} else {
		imagejpeg( $image, null, 85 );
	}

	$bytes = ob_get_clean();
	imagedestroy( $image );

	$upload = wp_upload_bits( $filename, null, $bytes );

	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment( array(
		'post_mime_type' => 'png' === $format ? 'image/png' : 'image/jpeg',
		'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
		'post_status'    => 'inherit',
	), $upload['file'] );

	if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
		return 0;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_post_meta( $attachment_id, '_teckform_demo', 1 );

	return $attachment_id;
}

/**
 * Elementor MEDIA control value for a demo image.
 *
 * @param array  $images Demo images.
 * @param string $name   Image name.
 * @return array
 */
function teckform_demo_media( $images, $name ) {
	if ( empty( $images[ $name ] ) ) {
		return array(
			'id'  => '',
			'url' => '',
		);
	}

	return array(
		'id'  => $images[ $name ],
		'url' => wp_get_attachment_url( $images[ $name ] ),
	);
}
