<?php
/**
 * Teckform theme functions.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TECKFORM_VERSION', '1.0.0' );

if ( ! isset( $content_width ) ) {
	$content_width = 1140;
}

/**
 * Register theme supports and menus.
 */
function teckform_setup() {
	load_theme_textdomain( 'teckform', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 350,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style', 'navigation-widgets' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );

	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'teckform' ),
		'footer'  => esc_html__( 'Footer Menu', 'teckform' ),
	) );
}
add_action( 'after_setup_theme', 'teckform_setup' );

/**
 * Enqueue front-end styles.
 */
function teckform_enqueue_assets() {
	wp_enqueue_style( 'teckform-style', get_stylesheet_uri(), array(), TECKFORM_VERSION );
}
add_action( 'wp_enqueue_scripts', 'teckform_enqueue_assets' );

/**
 * Let Elementor Pro's Theme Builder replace the header, footer and other locations.
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager Locations manager.
 */
function teckform_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'teckform_register_elementor_locations' );

/**
 * Whether Elementor Pro handles a given theme location.
 *
 * @param string $location Location name, e.g. 'header' or 'footer'.
 * @return bool
 */
function teckform_elementor_location( $location ) {
	return function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location );
}

/**
 * Whether a post's content was built with Elementor.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function teckform_is_built_with_elementor( $post_id ) {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$document = \Elementor\Plugin::$instance->documents->get( $post_id );

	return $document && $document->is_built_with_elementor();
}

/**
 * Recommend Elementor in the dashboard when it is not active.
 */
function teckform_elementor_notice() {
	if ( did_action( 'elementor/loaded' ) || ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	$url = admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' );
	printf(
		'<div class="notice notice-info is-dismissible"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
		esc_html__( 'Teckform is designed to work with the free Elementor page builder.', 'teckform' ),
		esc_url( $url ),
		esc_html__( 'Install Elementor', 'teckform' )
	);
}
add_action( 'admin_notices', 'teckform_elementor_notice' );

require_once get_template_directory() . '/inc/elementor/widgets.php';
