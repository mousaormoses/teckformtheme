<?php
/**
 * "Dark Mode" style section on Elementor containers, sections and columns, so
 * each block can have its own background and text color in dark mode.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the Dark Mode section after an element's background section.
 *
 * @param \Elementor\Element_Base $element Container, section or column.
 */
function teckform_add_dark_mode_controls( $element ) {
	if ( ! teckform_dark_mode_enabled() ) {
		return;
	}

	$dark = 'html[data-teckform-theme="dark"] {{WRAPPER}}';

	$element->start_controls_section( 'teckform_section_dark_mode', array(
		'label' => esc_html__( 'Dark Mode', 'teckform' ),
		'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
	) );

	$element->add_control( 'teckform_dark_note', array(
		'type'            => \Elementor\Controls_Manager::RAW_HTML,
		'raw'             => esc_html__( 'Used when a visitor switches to dark mode. Leave empty to keep the normal colors.', 'teckform' ),
		'content_classes' => 'elementor-descriptor',
	) );

	$element->add_control( 'teckform_dark_bg', array(
		'label'     => esc_html__( 'Background Color', 'teckform' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'selectors' => array(
			$dark => 'background-color: {{VALUE}}; background-image: none;',
		),
	) );

	$element->add_control( 'teckform_dark_text', array(
		'label'     => esc_html__( 'Text Color', 'teckform' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'selectors' => array(
			$dark => 'color: {{VALUE}};',
		),
	) );

	$element->add_control( 'teckform_dark_border', array(
		'label'     => esc_html__( 'Border Color', 'teckform' ),
		'type'      => \Elementor\Controls_Manager::COLOR,
		'selectors' => array(
			$dark => 'border-color: {{VALUE}};',
		),
	) );

	$element->end_controls_section();
}
add_action( 'elementor/element/container/section_background/after_section_end', 'teckform_add_dark_mode_controls' );
add_action( 'elementor/element/section/section_background/after_section_end', 'teckform_add_dark_mode_controls' );
add_action( 'elementor/element/column/section_style/after_section_end', 'teckform_add_dark_mode_controls' );
