<?php
/**
 * Custom Elementor widgets.
 *
 * Each widget lives in inc/elementor/widgets/class-*.php and is listed in
 * teckform_elementor_widgets(). Nothing here runs unless Elementor is active.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget files and class names, keyed by file name in inc/elementor/widgets/.
 *
 * @return array<string, string>
 */
function teckform_elementor_widgets() {
	return array(
		'class-feature-box.php' => '\Teckform\Elementor\Feature_Box',
	);
}

/**
 * Add a "Teckform" group to the Elementor widget panel.
 *
 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
 */
function teckform_elementor_category( $elements_manager ) {
	$elements_manager->add_category( 'teckform', array(
		'title' => esc_html__( 'Teckform', 'teckform' ),
		'icon'  => 'fa fa-plug',
	) );
}
add_action( 'elementor/elements/categories_registered', 'teckform_elementor_category' );

/**
 * Load and register every widget.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
 */
function teckform_register_elementor_widgets( $widgets_manager ) {
	foreach ( teckform_elementor_widgets() as $file => $class ) {
		require_once __DIR__ . '/widgets/' . $file;
		$widgets_manager->register( new $class() );
	}
}
add_action( 'elementor/widgets/register', 'teckform_register_elementor_widgets' );

/**
 * Register the widget stylesheet. Widgets load it through get_style_depends(),
 * so it is only printed on pages that use one of them.
 */
function teckform_register_elementor_widget_styles() {
	wp_register_style(
		'teckform-widgets',
		get_template_directory_uri() . '/assets/css/widgets.css',
		array(),
		TECKFORM_VERSION
	);
}
add_action( 'elementor/frontend/after_register_styles', 'teckform_register_elementor_widget_styles' );
