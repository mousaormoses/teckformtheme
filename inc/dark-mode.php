<?php
/**
 * Site-wide dark mode.
 *
 * The visitor's choice is stored in localStorage and applied as
 * <html data-teckform-theme="dark"> before the page paints. Dark colors swap
 * the theme's CSS variables and, optionally, Elementor's Global Colors.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether dark mode is turned on in the Customizer.
 *
 * @return bool
 */
function teckform_dark_mode_enabled() {
	return (bool) get_theme_mod( 'teckform_dark_mode', true );
}

/**
 * Theme color variables and their dark defaults.
 *
 * @return array<string, array{label: string, default: string}>
 */
function teckform_dark_mode_colors() {
	return array(
		'bg'      => array(
			'label'   => __( 'Page background', 'teckform' ),
			'default' => '#0f172a',
		),
		'text'    => array(
			'label'   => __( 'Text', 'teckform' ),
			'default' => '#e5e7eb',
		),
		'muted'   => array(
			'label'   => __( 'Secondary text', 'teckform' ),
			'default' => '#94a3b8',
		),
		'surface' => array(
			'label'   => __( 'Dropdowns, mobile menu and search box', 'teckform' ),
			'default' => '#1e293b',
		),
		'border'  => array(
			'label'   => __( 'Borders', 'teckform' ),
			'default' => '#334155',
		),
		'accent'  => array(
			'label'   => __( 'Links and buttons', 'teckform' ),
			'default' => '#60a5fa',
		),
	);
}

/**
 * Elementor Global Colors of the active kit, keyed by their ID.
 *
 * @return array<string, string> Color ID => title.
 */
function teckform_elementor_global_colors() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return array();
	}

	$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();

	if ( ! $kit ) {
		return array();
	}

	$colors = array();

	foreach ( array( 'system_colors', 'custom_colors' ) as $group ) {
		foreach ( (array) $kit->get_settings( $group ) as $color ) {
			if ( ! empty( $color['_id'] ) ) {
				$colors[ sanitize_key( $color['_id'] ) ] = isset( $color['title'] ) ? $color['title'] : $color['_id'];
			}
		}
	}

	return $colors;
}

/**
 * Customizer section: Appearance > Customize > Dark Mode.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function teckform_dark_mode_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'teckform_dark_mode', array(
		'title'       => __( 'Dark Mode', 'teckform' ),
		'priority'    => 31,
		'description' => __( 'Visitors switch modes with the Dark Mode Toggle widget. Colors set with Elementor Global Colors change too when you give them a dark version below.', 'teckform' ),
	) );

	$wp_customize->add_setting( 'teckform_dark_mode', array(
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );

	$wp_customize->add_control( 'teckform_dark_mode', array(
		'label'   => __( 'Enable dark mode', 'teckform' ),
		'section' => 'teckform_dark_mode',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'teckform_dark_default', array(
		'default'           => 'light',
		'sanitize_callback' => 'teckform_sanitize_dark_default',
	) );

	$wp_customize->add_control( 'teckform_dark_default', array(
		'label'       => __( 'Mode for new visitors', 'teckform' ),
		'description' => __( 'Once a visitor uses the toggle, their choice is remembered.', 'teckform' ),
		'section'     => 'teckform_dark_mode',
		'type'        => 'select',
		'choices'     => array(
			'light' => __( 'Light', 'teckform' ),
			'dark'  => __( 'Dark', 'teckform' ),
			'auto'  => __( "Follow the visitor's device setting", 'teckform' ),
		),
	) );

	foreach ( teckform_dark_mode_colors() as $key => $color ) {
		$setting = 'teckform_dark_' . $key;

		$wp_customize->add_setting( $setting, array(
			'default'           => $color['default'],
			'sanitize_callback' => 'sanitize_hex_color',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting, array(
			/* translators: %s: color name. */
			'label'   => sprintf( __( 'Dark: %s', 'teckform' ), $color['label'] ),
			'section' => 'teckform_dark_mode',
		) ) );
	}

	foreach ( teckform_elementor_global_colors() as $id => $title ) {
		$setting = 'teckform_dark_elementor_' . $id;

		$wp_customize->add_setting( $setting, array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_hex_color',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting, array(
			/* translators: %s: Elementor global color name. */
			'label'       => sprintf( __( 'Dark: Elementor "%s" color', 'teckform' ), $title ),
			'description' => __( 'Leave empty to keep the same color in dark mode.', 'teckform' ),
			'section'     => 'teckform_dark_mode',
		) ) );
	}
}
add_action( 'customize_register', 'teckform_dark_mode_customize_register' );

/**
 * @param string $value Submitted value.
 * @return string
 */
function teckform_sanitize_dark_default( $value ) {
	return in_array( $value, array( 'light', 'dark', 'auto' ), true ) ? $value : 'light';
}

/**
 * Apply the saved or default mode before anything paints, so dark visitors
 * never see a flash of the light page.
 */
function teckform_dark_mode_head_script() {
	if ( ! teckform_dark_mode_enabled() ) {
		return;
	}

	$default = teckform_sanitize_dark_default( get_theme_mod( 'teckform_dark_default', 'light' ) );
	?>
	<script>
	( function () {
		var mode = null;
		window.teckformDarkDefault = <?php echo wp_json_encode( $default ); ?>;
		try { mode = localStorage.getItem( 'teckform-theme' ); } catch ( e ) {}
		if ( 'dark' !== mode && 'light' !== mode ) {
			mode = 'auto' === window.teckformDarkDefault
				? ( window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light' )
				: window.teckformDarkDefault;
		}
		document.documentElement.setAttribute( 'data-teckform-theme', mode );
	}() );
	</script>
	<?php
}
add_action( 'wp_head', 'teckform_dark_mode_head_script', 0 );

/**
 * Dark color variables, printed after the theme stylesheet.
 */
function teckform_dark_mode_styles() {
	if ( ! teckform_dark_mode_enabled() ) {
		return;
	}

	$css = 'html[data-teckform-theme="dark"]{color-scheme:dark;';

	foreach ( teckform_dark_mode_colors() as $key => $color ) {
		$value = sanitize_hex_color( get_theme_mod( 'teckform_dark_' . $key, $color['default'] ) );
		$css  .= $value ? '--teckform-' . $key . ':' . $value . ';' : '';
	}

	$css .= '}';

	$kit_id   = absint( get_option( 'elementor_active_kit' ) );
	$elements = '';

	foreach ( array_keys( teckform_elementor_global_colors() ) as $id ) {
		$value     = sanitize_hex_color( get_theme_mod( 'teckform_dark_elementor_' . $id, '' ) );
		$elements .= $value ? '--e-global-color-' . $id . ':' . $value . ';' : '';
	}

	if ( $kit_id && $elements ) {
		$css .= 'html[data-teckform-theme="dark"] .elementor-kit-' . $kit_id . '{' . $elements . '}';
	}

	wp_add_inline_style( 'teckform-style', $css );
}
add_action( 'wp_enqueue_scripts', 'teckform_dark_mode_styles', 20 );

/**
 * Markup for a dark mode toggle button.
 *
 * @param string $style      'switch' or 'icon'.
 * @param string $light_icon Icon HTML for light mode; a sun when empty.
 * @param string $dark_icon  Icon HTML for dark mode; a moon when empty.
 * @return string
 */
function teckform_dark_mode_toggle_html( $style = 'switch', $light_icon = '', $dark_icon = '' ) {
	if ( '' === $light_icon ) {
		$light_icon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7a5 5 0 110 10 5 5 0 010-10zm0-5l1 3h-2l1-3zm0 20l-1-3h2l-1 3zM2 12l3-1v2l-3-1zm20 0l-3 1v-2l3 1zM4.9 4.9l2.8 1.4-1.4 1.4-1.4-2.8zm14.2 14.2l-2.8-1.4 1.4-1.4 1.4 2.8zM4.9 19.1l1.4-2.8 1.4 1.4-2.8 1.4zM19.1 4.9l-1.4 2.8-1.4-1.4 2.8-1.4z"/></svg>';
	}

	if ( '' === $dark_icon ) {
		$dark_icon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 14.5A9 9 0 019.5 3a7 7 0 1011.5 11.5z"/></svg>';
	}

	$icons = '<span class="teckform-dark-toggle__icon teckform-dark-toggle__icon--light">' . $light_icon . '</span>'
		. '<span class="teckform-dark-toggle__icon teckform-dark-toggle__icon--dark">' . $dark_icon . '</span>';

	if ( 'switch' === $style ) {
		$icons = '<span class="teckform-dark-toggle__track" aria-hidden="true"><span class="teckform-dark-toggle__thumb">' . $icons . '</span></span>';
	}

	return '<button type="button" class="teckform-dark-toggle teckform-dark-toggle--' . esc_attr( 'switch' === $style ? 'switch' : 'icon' ) . '" aria-pressed="false" aria-label="' . esc_attr__( 'Dark mode', 'teckform' ) . '">' . $icons . '</button>';
}
