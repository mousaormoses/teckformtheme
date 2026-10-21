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
		'class-hero.php'             => '\Teckform\Elementor\Hero',
		'class-feature-box.php'      => '\Teckform\Elementor\Feature_Box',
		'class-site-logo.php'        => '\Teckform\Elementor\Site_Logo',
		'class-nav-menu.php'         => '\Teckform\Elementor\Nav_Menu',
		'class-search.php'           => '\Teckform\Elementor\Search',
		'class-copyright.php'        => '\Teckform\Elementor\Copyright',
		'class-dark-mode-toggle.php' => '\Teckform\Elementor\Dark_Mode_Toggle',
	);
}

/**
 * Teckform groups in the Elementor widget panel. Widgets pick one in get_categories().
 *
 * @return array<string, string> Category ID => title.
 */
function teckform_elementor_categories() {
	return array(
		'teckform-sections' => __( 'Teckform Sections', 'teckform' ),
		'teckform-site'     => __( 'Teckform Header & Footer', 'teckform' ),
		'teckform'          => __( 'Teckform Elements', 'teckform' ),
	);
}

/**
 * Add the Teckform groups and move them to the top of the widget panel.
 *
 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
 */
function teckform_elementor_category( $elements_manager ) {
	foreach ( teckform_elementor_categories() as $id => $title ) {
		$elements_manager->add_category( $id, array(
			'title' => esc_html( $title ),
			'icon'  => 'fa fa-plug',
		) );
	}

	// Elementor lists groups in the order they were added and has no API to reorder them,
	// so move ours to the front. If Elementor changes its internals this is skipped and
	// the groups simply stay at the end.
	$reorder = function ( $ours ) {
		if ( ! isset( $this->categories ) || ! is_array( $this->categories ) ) {
			return;
		}

		$front = array_intersect_key( $this->categories, array_flip( $ours ) );

		// Keep Favorites first when it's there.
		if ( isset( $this->categories['favorites'] ) ) {
			$front = array( 'favorites' => $this->categories['favorites'] ) + $front;
		}

		$this->categories = $front + $this->categories;
	};

	try {
		\Closure::bind( $reorder, $elements_manager, get_class( $elements_manager ) )( array_keys( teckform_elementor_categories() ) );
	} catch ( \Throwable $e ) {
		return;
	}
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

	wp_register_style(
		'teckform-hero',
		get_template_directory_uri() . '/assets/css/hero.css',
		array(),
		TECKFORM_VERSION
	);
}
add_action( 'elementor/frontend/after_register_styles', 'teckform_register_elementor_widget_styles' );

/**
 * Register the widget script (mobile menu, full-screen search). Widgets load it
 * through get_script_depends().
 */
function teckform_register_elementor_widget_scripts() {
	wp_register_script(
		'teckform-widgets',
		get_template_directory_uri() . '/assets/js/widgets.js',
		array(),
		TECKFORM_VERSION,
		true
	);

	wp_register_script(
		'teckform-hero',
		get_template_directory_uri() . '/assets/js/hero.js',
		array(),
		TECKFORM_VERSION,
		true
	);
}
add_action( 'elementor/frontend/after_register_scripts', 'teckform_register_elementor_widget_scripts' );
