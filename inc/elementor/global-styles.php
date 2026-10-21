<?php
/**
 * Teckform's colors and fonts as Elementor Global Colors and Global Fonts.
 *
 * The theme and all Teckform widgets read Elementor's globals (see the body
 * variables in style.css), so changing them in Elementor > Site Settings
 * restyles the whole site. This file fills them with the Teckform palette.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Teckform palette and fonts, in Elementor's kit format.
 *
 * @return array{system_colors: array, system_typography: array}
 */
function teckform_elementor_palette() {
	$font = function ( $id, $title, $weight ) {
		return array(
			'_id'                    => $id,
			'title'                  => $title,
			'typography_typography'  => 'custom',
			'typography_font_family' => 'Inter',
			'typography_font_weight' => $weight,
		);
	};

	return array(
		'system_colors'     => array(
			array(
				'_id'   => 'primary',
				'title' => __( 'Primary', 'teckform' ),
				'color' => '#111827',
			),
			array(
				'_id'   => 'secondary',
				'title' => __( 'Secondary', 'teckform' ),
				'color' => '#6B7280',
			),
			array(
				'_id'   => 'text',
				'title' => __( 'Text', 'teckform' ),
				'color' => '#374151',
			),
			array(
				'_id'   => 'accent',
				'title' => __( 'Accent', 'teckform' ),
				'color' => '#2563EB',
			),
		),
		'system_typography' => array(
			$font( 'primary', __( 'Primary', 'teckform' ), '700' ),
			$font( 'secondary', __( 'Secondary', 'teckform' ), '600' ),
			$font( 'text', __( 'Text', 'teckform' ), '400' ),
			$font( 'accent', __( 'Accent', 'teckform' ), '500' ),
		),
	);
}

/**
 * Whether the active kit still has Elementor's out-of-the-box colors.
 *
 * @param \Elementor\Core\Kits\Documents\Kit $kit Active kit.
 * @return bool
 */
function teckform_kit_has_elementor_defaults( $kit ) {
	$defaults = array(
		'primary'   => '#6EC1E4',
		'secondary' => '#54595F',
		'text'      => '#7A7A7A',
		'accent'    => '#61CE70',
	);

	foreach ( (array) $kit->get_settings( 'system_colors' ) as $color ) {
		if ( isset( $color['_id'], $defaults[ $color['_id'] ] ) && strtoupper( (string) $color['color'] ) !== $defaults[ $color['_id'] ] ) {
			return false;
		}
	}

	return true;
}

/**
 * Write the palette into Elementor's active kit.
 *
 * @param bool $force Replace colors the site owner already changed.
 * @return bool Whether the kit was updated.
 */
function teckform_apply_elementor_palette( $force = false ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}

	$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();

	if ( ! $kit || ! $kit->get_id() || ( ! $force && ! teckform_kit_has_elementor_defaults( $kit ) ) ) {
		return false;
	}

	$kit->update_settings( teckform_elementor_palette() );
	\Elementor\Plugin::$instance->files_manager->clear_cache();
	update_option( 'teckform_palette_applied', 1 );

	return true;
}

/**
 * On theme activation, use the palette if Elementor's colors were never changed.
 */
function teckform_palette_on_activation() {
	teckform_apply_elementor_palette();
}
add_action( 'after_switch_theme', 'teckform_palette_on_activation' );

/**
 * Elementor may be installed after the theme: offer the palette until it's applied or dismissed.
 */
function teckform_palette_notice() {
	if ( ! did_action( 'elementor/loaded' ) || get_option( 'teckform_palette_applied' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$apply   = wp_nonce_url( admin_url( 'admin-post.php?action=teckform_palette&do=apply' ), 'teckform_palette' );
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=teckform_palette&do=dismiss' ), 'teckform_palette' );
	?>
	<div class="notice notice-info">
		<p><?php esc_html_e( 'Use the Teckform colors and fonts in Elementor? This sets Elementor\'s Global Colors and Global Fonts, which the whole site uses. You can change them any time in Elementor > Site Settings.', 'teckform' ); ?></p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $apply ); ?>"><?php esc_html_e( 'Use Teckform colors and fonts', 'teckform' ); ?></a>
			<a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Keep my current ones', 'teckform' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'teckform_palette_notice' );

/**
 * Handle the notice buttons.
 */
function teckform_palette_action() {
	check_admin_referer( 'teckform_palette' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'teckform' ) );
	}

	if ( isset( $_GET['do'] ) && 'apply' === $_GET['do'] ) {
		teckform_apply_elementor_palette( true );
	} else {
		update_option( 'teckform_palette_applied', 1 );
	}

	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_teckform_palette', 'teckform_palette_action' );
