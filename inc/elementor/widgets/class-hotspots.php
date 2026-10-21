<?php
/**
 * Image Hotspots widget: an image with markers that open small info boxes.
 *
 * Each marker is a button (aria-expanded) that opens a tooltip with a title,
 * text and an optional link, on hover or on click. assets/js/hotspots.js opens
 * and closes them, closes them with Escape, and moves a tooltip to the side
 * with room so it never leaves the image on small screens.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hotspots extends Widget_Base {

	public function get_name() {
		return 'teckform-hotspots';
	}

	public function get_title() {
		return esc_html__( 'Image Hotspots', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-image-hotspot';
	}

	public function get_categories() {
		return array( 'teckform' );
	}

	public function get_keywords() {
		return array( 'hotspot', 'hotspots', 'image', 'tooltip', 'map', 'pin', 'marker', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-hotspots' );
	}

	public function get_script_depends() {
		return array( 'teckform-hotspots' );
	}

	/**
	 * Tooltip positions and labels.
	 *
	 * @return array<string, string>
	 */
	private function get_positions() {
		return array(
			'auto'   => esc_html__( 'Automatic (where there is room)', 'teckform' ),
			'top'    => esc_html__( 'Above', 'teckform' ),
			'bottom' => esc_html__( 'Below', 'teckform' ),
			'start'  => esc_html__( 'Start side (left)', 'teckform' ),
			'end'    => esc_html__( 'End side (right)', 'teckform' ),
		);
	}

	protected function register_controls() {
		$this->register_image_controls();
		$this->register_hotspot_controls();
		$this->register_settings_controls();

		$this->register_image_style_controls();
		$this->register_marker_style_controls();
		$this->register_tooltip_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_image_controls() {
		$this->start_controls_section( 'section_image', array(
			'label' => esc_html__( 'Image', 'teckform' ),
		) );

		$this->add_control( 'image', array(
			'label'   => esc_html__( 'Image', 'teckform' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => array( 'url' => Utils::get_placeholder_image_src() ),
			'dynamic' => array( 'active' => true ),
		) );

		$this->end_controls_section();
	}

	private function register_hotspot_controls() {
		$this->start_controls_section( 'section_hotspots', array(
			'label' => esc_html__( 'Hotspots', 'teckform' ),
		) );

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'hotspot_tabs' );

		$repeater->start_controls_tab( 'hotspot_tab_content', array( 'label' => esc_html__( 'Content', 'teckform' ) ) );

		$repeater->add_control( 'title', array(
			'label'       => esc_html__( 'Title', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'Hotspot', 'teckform' ),
			'dynamic'     => array( 'active' => true ),
		) );

		$repeater->add_control( 'text', array(
			'label'   => esc_html__( 'Text', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 3,
			'default' => esc_html__( 'A short description of this spot.', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$repeater->add_control( 'link', array(
			'label'       => esc_html__( 'Link', 'teckform' ),
			'type'        => Controls_Manager::URL,
			'dynamic'     => array( 'active' => true ),
			'description' => esc_html__( 'Optional. Shown as a link at the bottom of the tooltip.', 'teckform' ),
		) );

		$repeater->add_control( 'link_text', array(
			'label'     => esc_html__( 'Link Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Learn more', 'teckform' ),
			'condition' => array( 'link[url]!' => '' ),
		) );

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'hotspot_tab_position', array( 'label' => esc_html__( 'Position', 'teckform' ) ) );

		$repeater->add_control( 'x', array(
			'label'       => esc_html__( 'From Left', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( '%' ),
			'range'       => array(
				'%' => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 0.5,
				),
			),
			'default'     => array(
				'unit' => '%',
				'size' => 50,
			),
			'description' => esc_html__( 'Measured from the left edge of the image, also on right-to-left sites.', 'teckform' ),
		) );

		$repeater->add_control( 'y', array(
			'label'      => esc_html__( 'From Top', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( '%' ),
			'range'      => array(
				'%' => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 0.5,
				),
			),
			'default'    => array(
				'unit' => '%',
				'size' => 50,
			),
		) );

		$repeater->add_control( 'position', array(
			'label'   => esc_html__( 'Tooltip Position', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => '',
			'options' => array( '' => esc_html__( 'Default (from Settings)', 'teckform' ) ) + $this->get_positions(),
		) );

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'hotspot_tab_marker', array( 'label' => esc_html__( 'Marker', 'teckform' ) ) );

		$repeater->add_control( 'marker', array(
			'label'   => esc_html__( 'Marker', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'dot',
			'options' => array(
				'dot'  => esc_html__( 'Plain dot', 'teckform' ),
				'icon' => esc_html__( 'Icon', 'teckform' ),
			),
		) );

		$repeater->add_control( 'icon', array(
			'label'     => esc_html__( 'Icon', 'teckform' ),
			'type'      => Controls_Manager::ICONS,
			'default'   => array(
				'value'   => 'fas fa-plus',
				'library' => 'fa-solid',
			),
			'condition' => array( 'marker' => 'icon' ),
		) );

		$repeater->add_control( 'color', array(
			'label'       => esc_html__( 'Color', 'teckform' ),
			'type'        => Controls_Manager::COLOR,
			'description' => esc_html__( 'Optional. Overrides the marker color from the Style tab for this hotspot.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} {{CURRENT_ITEM}}' => '--teckform-hotspot-color: {{VALUE}};',
			),
		) );

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control( 'hotspots', array(
			'label'       => esc_html__( 'Hotspots', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array(
					'title' => esc_html__( 'Fast loading', 'teckform' ),
					'text'  => esc_html__( 'Pages load in under a second, even on phones.', 'teckform' ),
					'x'     => array(
						'unit' => '%',
						'size' => 22,
					),
					'y'     => array(
						'unit' => '%',
						'size' => 30,
					),
				),
				array(
					'title' => esc_html__( 'Easy to edit', 'teckform' ),
					'text'  => esc_html__( 'Change text and images yourself, no developer needed.', 'teckform' ),
					'x'     => array(
						'unit' => '%',
						'size' => 58,
					),
					'y'     => array(
						'unit' => '%',
						'size' => 55,
					),
				),
				array(
					'title' => esc_html__( 'Built to sell', 'teckform' ),
					'text'  => esc_html__( 'Clear calls to action on every page.', 'teckform' ),
					'x'     => array(
						'unit' => '%',
						'size' => 80,
					),
					'y'     => array(
						'unit' => '%',
						'size' => 25,
					),
				),
			),
		) );

		$this->end_controls_section();
	}

	private function register_settings_controls() {
		$this->start_controls_section( 'section_settings', array(
			'label' => esc_html__( 'Settings', 'teckform' ),
		) );

		$this->add_control( 'trigger', array(
			'label'       => esc_html__( 'Open Tooltip On', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'hover',
			'options'     => array(
				'hover' => esc_html__( 'Hover', 'teckform' ),
				'click' => esc_html__( 'Click', 'teckform' ),
			),
			'description' => esc_html__( 'A tap always opens it on touch screens, and Escape closes it.', 'teckform' ),
		) );

		$this->add_control( 'tooltip_position', array(
			'label'   => esc_html__( 'Tooltip Position', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'auto',
			'options' => $this->get_positions(),
		) );

		$this->add_control( 'pulse', array(
			'label'        => esc_html__( 'Pulse Animation', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Not shown to visitors who ask their device for less motion.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_image_style_controls() {
		$this->start_controls_section( 'section_style_image', array(
			'label' => esc_html__( 'Image', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'image_width', array(
			'label'      => esc_html__( 'Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( '%', 'px' ),
			'range'      => array(
				'%'  => array(
					'min' => 10,
					'max' => 100,
				),
				'px' => array(
					'min' => 100,
					'max' => 2000,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hotspots__inner' => 'width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'image_align', array(
			'label'     => esc_html__( 'Alignment', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => array(
				'flex-start' => array(
					'title' => esc_html__( 'Start', 'teckform' ),
					'icon'  => 'eicon-h-align-left',
				),
				'center'     => array(
					'title' => esc_html__( 'Center', 'teckform' ),
					'icon'  => 'eicon-h-align-center',
				),
				'flex-end'   => array(
					'title' => esc_html__( 'End', 'teckform' ),
					'icon'  => 'eicon-h-align-right',
				),
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots' => 'justify-content: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'image_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hotspots__image' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'image_border',
			'selector' => '{{WRAPPER}} .teckform-hotspots__image',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'image_shadow',
			'selector' => '{{WRAPPER}} .teckform-hotspots__image',
		) );

		$this->end_controls_section();
	}

	private function register_marker_style_controls() {
		$this->start_controls_section( 'section_style_marker', array(
			'label' => esc_html__( 'Hotspot Markers', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'marker_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 12,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hotspots' => '--teckform-hotspot-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'marker_icon_size', array(
			'label'      => esc_html__( 'Icon Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 6,
					'max' => 48,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hotspots__marker' => 'font-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'marker_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'%' => array(
					'min' => 0,
					'max' => 50,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hotspots__marker' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'marker_border',
			'selector' => '{{WRAPPER}} .teckform-hotspots__marker',
		) );

		$this->start_controls_tabs( 'marker_tabs' );

		$this->start_controls_tab( 'marker_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'marker_color', array(
			'label'     => esc_html__( 'Marker Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots' => '--teckform-hotspot-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'marker_icon_color', array(
			'label'     => esc_html__( 'Dot / Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots' => '--teckform-hotspot-icon: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'marker_shadow',
			'selector' => '{{WRAPPER}} .teckform-hotspots__marker',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'marker_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'marker_hover_color', array(
			'label'       => esc_html__( 'Marker Color', 'teckform' ),
			'type'        => Controls_Manager::COLOR,
			'description' => esc_html__( 'Also used while its tooltip is open.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-hotspots__marker:hover, {{WRAPPER}} .teckform-hotspots__marker[aria-expanded="true"]' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'marker_hover_icon_color', array(
			'label'     => esc_html__( 'Dot / Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots__marker:hover, {{WRAPPER}} .teckform-hotspots__marker[aria-expanded="true"]' => '--teckform-hotspot-icon: {{VALUE}};',
			),
		) );

		$this->add_control( 'marker_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots__marker:hover, {{WRAPPER}} .teckform-hotspots__marker[aria-expanded="true"]' => 'border-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_tooltip_style_controls() {
		$this->start_controls_section( 'section_style_tooltip', array(
			'label' => esc_html__( 'Tooltip', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'tooltip_width', array(
			'label'       => esc_html__( 'Max Width', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array(
				'px' => array(
					'min' => 120,
					'max' => 600,
				),
			),
			'description' => esc_html__( 'Narrower automatically when the image is small.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-hotspots' => '--teckform-hotspot-tooltip-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'tooltip_distance', array(
			'label'      => esc_html__( 'Distance From Marker', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hotspots' => '--teckform-hotspot-distance: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'tooltip_align', array(
			'label'     => esc_html__( 'Text Alignment', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => array(
				'start'  => array(
					'title' => esc_html__( 'Start', 'teckform' ),
					'icon'  => 'eicon-text-align-left',
				),
				'center' => array(
					'title' => esc_html__( 'Center', 'teckform' ),
					'icon'  => 'eicon-text-align-center',
				),
				'end'    => array(
					'title' => esc_html__( 'End', 'teckform' ),
					'icon'  => 'eicon-text-align-right',
				),
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots__tooltip' => 'text-align: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'tooltip_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hotspots__tooltip' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'tooltip_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hotspots__tooltip' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'tooltip_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots' => '--teckform-hotspot-tooltip-bg: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'tooltip_border',
			'selector' => '{{WRAPPER}} .teckform-hotspots__tooltip',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'tooltip_shadow',
			'selector' => '{{WRAPPER}} .teckform-hotspots__tooltip',
		) );

		$this->add_control( 'tooltip_title_heading', array(
			'label'     => esc_html__( 'Title', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'tooltip_title_typography',
			'selector' => '{{WRAPPER}} .teckform-hotspots__title',
		) );

		$this->add_control( 'tooltip_title_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'tooltip_text_heading', array(
			'label'     => esc_html__( 'Text', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'tooltip_text_typography',
			'selector' => '{{WRAPPER}} .teckform-hotspots__text',
		) );

		$this->add_control( 'tooltip_text_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots__text' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'tooltip_link_heading', array(
			'label'     => esc_html__( 'Link', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'tooltip_link_typography',
			'selector' => '{{WRAPPER}} .teckform-hotspots__link',
		) );

		$this->start_controls_tabs( 'tooltip_link_tabs' );

		$this->start_controls_tab( 'tooltip_link_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'tooltip_link_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots__link' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'tooltip_link_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'tooltip_link_hover_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hotspots__link:hover, {{WRAPPER}} .teckform-hotspots__link:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$image    = $this->get_image_html( $settings['image'] );

		if ( ! $image ) {
			return;
		}

		$positions = $this->get_positions();
		$default   = array_key_exists( $settings['tooltip_position'], $positions ) ? $settings['tooltip_position'] : 'auto';
		$classes   = array(
			'teckform-hotspots',
			'teckform-hotspots--' . ( 'click' === $settings['trigger'] ? 'click' : 'hover' ),
		);

		if ( 'yes' === $settings['pulse'] ) {
			$classes[] = 'teckform-hotspots--pulse';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-trigger="<?php echo esc_attr( 'click' === $settings['trigger'] ? 'click' : 'hover' ); ?>">
			<div class="teckform-hotspots__inner">
				<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html(). ?>
				<?php
				foreach ( (array) $settings['hotspots'] as $index => $hotspot ) {
					$position = ! empty( $hotspot['position'] ) && array_key_exists( $hotspot['position'], $positions ) ? $hotspot['position'] : $default;

					$this->render_hotspot( $hotspot, $index, $position );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * One marker button and its tooltip.
	 *
	 * @param array  $hotspot  Repeater row.
	 * @param int    $index    Row number, from 0.
	 * @param string $position Tooltip position key.
	 */
	private function render_hotspot( $hotspot, $index, $position ) {
		$x          = isset( $hotspot['x']['size'] ) && '' !== $hotspot['x']['size'] ? min( 100, max( 0, (float) $hotspot['x']['size'] ) ) : 50;
		$y          = isset( $hotspot['y']['size'] ) && '' !== $hotspot['y']['size'] ? min( 100, max( 0, (float) $hotspot['y']['size'] ) ) : 50;
		$tooltip_id = 'teckform-hotspot-' . $this->get_id() . '-' . ( $index + 1 );
		$title      = trim( (string) $hotspot['title'] );
		$text       = trim( (string) $hotspot['text'] );
		$has_link   = ! empty( $hotspot['link']['url'] );
		$has_body   = '' !== $title || '' !== $text || $has_link;
		/* translators: %d: hotspot number. */
		$label = '' !== $title ? wp_strip_all_tags( $title ) : sprintf( __( 'Hotspot %d', 'teckform' ), $index + 1 );
		$key   = 'hotspot_link_' . $index;
		?>
		<div class="teckform-hotspots__spot elementor-repeater-item-<?php echo esc_attr( $hotspot['_id'] ); ?> is-pos-<?php echo esc_attr( 'auto' === $position ? 'top' : $position ); ?>" style="<?php echo esc_attr( 'left:' . $x . '%;top:' . $y . '%;' ); ?>" data-position="<?php echo esc_attr( $position ); ?>">
			<button type="button" class="teckform-hotspots__marker teckform-hotspots__marker--<?php echo esc_attr( 'icon' === $hotspot['marker'] ? 'icon' : 'dot' ); ?>"<?php echo $has_body ? ' aria-expanded="false" aria-controls="' . esc_attr( $tooltip_id ) . '"' : ''; ?>>
				<?php
				if ( 'icon' === $hotspot['marker'] && ! empty( $hotspot['icon']['value'] ) ) {
					Icons_Manager::render_icon( $hotspot['icon'], array( 'aria-hidden' => 'true' ) );
				} else {
					echo '<span class="teckform-hotspots__dot" aria-hidden="true"></span>';
				}
				?>
				<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
			</button>
			<?php if ( $has_body ) : ?>
				<div class="teckform-hotspots__tooltip" id="<?php echo esc_attr( $tooltip_id ); ?>">
					<?php if ( '' !== $title ) : ?>
						<p class="teckform-hotspots__title"><?php echo esc_html( $title ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $text ) : ?>
						<p class="teckform-hotspots__text"><?php echo wp_kses_post( $text ); ?></p>
					<?php endif; ?>
					<?php
					if ( $has_link ) {
						$this->add_link_attributes( $key, $hotspot['link'] );
						$this->add_render_attribute( $key, 'class', 'teckform-hotspots__link' );

						$link_text = '' !== trim( (string) $hotspot['link_text'] ) ? $hotspot['link_text'] : __( 'Learn more', 'teckform' );

						echo '<a ' . $this->get_render_attribute_string( $key ) . '>' . esc_html( $link_text ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes escaped by Elementor.
					}
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * <img> markup for the main image.
	 *
	 * @param array $media Value with 'id' and/or 'url'.
	 * @return string
	 */
	private function get_image_html( $media ) {
		if ( ! empty( $media['id'] ) ) {
			$html = wp_get_attachment_image( absint( $media['id'] ), 'full', false, array( 'class' => 'teckform-hotspots__image' ) );

			if ( $html ) {
				return $html;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img src="' . esc_url( $media['url'] ) . '" alt="" class="teckform-hotspots__image">';
		}

		return '';
	}
}
