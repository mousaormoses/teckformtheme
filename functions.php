<?php
/**
 * Teckform theme functions.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TECKFORM_VERSION', '1.4.0' );

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
	wp_enqueue_script( 'teckform-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), TECKFORM_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'teckform_enqueue_assets' );

/**
 * Theme options in Appearance > Customize > Teckform Options.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function teckform_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'teckform_options', array(
		'title'    => __( 'Teckform Options', 'teckform' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'teckform_back_to_top', array(
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );

	$wp_customize->add_control( 'teckform_back_to_top', array(
		'label'   => __( 'Show "back to top" button', 'teckform' ),
		'section' => 'teckform_options',
		'type'    => 'checkbox',
	) );
}
add_action( 'customize_register', 'teckform_customize_register' );

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
 * Under the title of a single post, service or project (not built with Elementor):
 * the date for posts, client/year/link for projects, then the featured image.
 */
function teckform_entry_header() {
	$type = get_post_type();

	if ( 'post' === $type ) {
		echo '<p class="entry-meta">' . esc_html( get_the_date() ) . '</p>';
	}

	if ( 'teckform_project' === $type ) {
		$details = array_filter( array(
			__( 'Client', 'teckform' ) => get_post_meta( get_the_ID(), '_teckform_client', true ),
			__( 'Year', 'teckform' )   => get_post_meta( get_the_ID(), '_teckform_year', true ),
		) );
		$url     = get_post_meta( get_the_ID(), '_teckform_url', true );

		if ( $details || $url ) {
			echo '<ul class="entry-details">';

			foreach ( $details as $label => $value ) {
				echo '<li><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $value ) . '</li>';
			}

			if ( $url ) {
				echo '<li><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Visit the live site', 'teckform' ) . '</a></li>';
			}

			echo '</ul>';
		}
	}

	if ( in_array( $type, array( 'post', 'teckform_service', 'teckform_project' ), true ) && has_post_thumbnail() ) {
		echo '<figure class="entry-image">';
		the_post_thumbnail( 'large' );
		echo '</figure>';
	}
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

require_once get_template_directory() . '/inc/dark-mode.php';
require_once get_template_directory() . '/inc/templates.php';
require_once get_template_directory() . '/inc/content-types.php';
require_once get_template_directory() . '/inc/mega-menu.php';
require_once get_template_directory() . '/inc/forms/helpers.php';
require_once get_template_directory() . '/inc/forms/post-types.php';
require_once get_template_directory() . '/inc/forms/contact-form.php';
require_once get_template_directory() . '/inc/forms/newsletter.php';
require_once get_template_directory() . '/inc/forms/login-form.php';
require_once get_template_directory() . '/inc/demo/importer.php';
require_once get_template_directory() . '/inc/elementor/widgets.php';
require_once get_template_directory() . '/inc/elementor/dark-mode-controls.php';
require_once get_template_directory() . '/inc/elementor/global-styles.php';
