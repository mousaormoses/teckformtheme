<?php
/**
 * Copyright widget: footer text with the current year and site name filled in.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Copyright extends Widget_Base {

	public function get_name() {
		return 'teckform-copyright';
	}

	public function get_title() {
		return esc_html__( 'Copyright', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-site-identity';
	}

	public function get_categories() {
		return array( 'teckform-site' );
	}

	public function get_keywords() {
		return array( 'copyright', 'footer', 'year', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-widgets' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array(
			'label' => esc_html__( 'Copyright', 'teckform' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'text', array(
			'label'       => esc_html__( 'Text', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => esc_html__( '© {year} {site_name}. All rights reserved.', 'teckform' ),
			'description' => esc_html__( '{year} becomes the current year and {site_name} your site title. Links are allowed.', 'teckform' ),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array(
			'label' => esc_html__( 'Copyright', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'align', array(
			'label'     => esc_html__( 'Alignment', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => array(
				'left'   => array(
					'title' => esc_html__( 'Left', 'teckform' ),
					'icon'  => 'eicon-text-align-left',
				),
				'center' => array(
					'title' => esc_html__( 'Center', 'teckform' ),
					'icon'  => 'eicon-text-align-center',
				),
				'right'  => array(
					'title' => esc_html__( 'Right', 'teckform' ),
					'icon'  => 'eicon-text-align-right',
				),
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-copyright' => 'text-align: {{VALUE}};',
			),
		) );

		$this->add_control( 'color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-copyright' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'link_color', array(
			'label'     => esc_html__( 'Link Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-copyright a' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'link_hover_color', array(
			'label'     => esc_html__( 'Link Hover Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-copyright a:hover' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'typography',
			'selector' => '{{WRAPPER}} .teckform-copyright',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$text     = strtr( $settings['text'], array(
			'{year}'      => wp_date( 'Y' ),
			'{site_name}' => get_bloginfo( 'name' ),
		) );

		echo '<div class="teckform-copyright">' . wp_kses_post( $text ) . '</div>';
	}
}
