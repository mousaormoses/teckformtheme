<?php
/**
 * Process / Timeline widget: numbered steps or dated milestones.
 *
 * Three layouts: horizontal steps joined by connector lines (stacked on phones),
 * a vertical timeline with the line on the start side, and an alternating
 * timeline that puts steps on both sides of the line on larger screens.
 * Steps can fade in as they scroll into view.
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

class Process extends Widget_Base {

	public function get_name() {
		return 'teckform-process';
	}

	public function get_title() {
		return esc_html__( 'Process / Timeline', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'process', 'steps', 'timeline', 'history', 'milestones', 'how it works', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-process' );
	}

	public function get_script_depends() {
		return array( 'teckform-process' );
	}

	/**
	 * Layout keys and labels.
	 *
	 * @return array<string, string>
	 */
	private function get_layouts() {
		return array(
			'horizontal'  => esc_html__( 'Horizontal steps', 'teckform' ),
			'vertical'    => esc_html__( 'Vertical timeline', 'teckform' ),
			'alternating' => esc_html__( 'Alternating timeline', 'teckform' ),
		);
	}

	protected function register_controls() {
		$this->register_step_controls();
		$this->register_layout_controls();

		$this->register_steps_style_controls();
		$this->register_marker_style_controls();
		$this->register_line_style_controls();
		$this->register_content_style_controls();
		$this->register_text_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_step_controls() {
		$this->start_controls_section( 'section_steps', array(
			'label' => esc_html__( 'Steps', 'teckform' ),
		) );

		$repeater = new Repeater();

		$repeater->add_control( 'label', array(
			'label'       => esc_html__( 'Label or Date', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Step', 'teckform' ),
			'description' => esc_html__( 'Small text above the title, such as "Step 1", "Week 2" or "2019".', 'teckform' ),
		) );

		$repeater->add_control( 'title', array(
			'label'       => esc_html__( 'Title', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'dynamic'     => array( 'active' => true ),
			'default'     => esc_html__( 'Step title', 'teckform' ),
		) );

		$repeater->add_control( 'text', array(
			'label'   => esc_html__( 'Text', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 3,
			'dynamic' => array( 'active' => true ),
			'default' => esc_html__( 'Describe what happens in this step.', 'teckform' ),
		) );

		$repeater->add_control( 'icon', array(
			'label'       => esc_html__( 'Icon', 'teckform' ),
			'type'        => Controls_Manager::ICONS,
			'description' => esc_html__( 'Used when Marker is set to Icon. Without one, the step number is shown.', 'teckform' ),
		) );

		$this->add_control( 'steps', array(
			'label'       => esc_html__( 'Steps', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ label }}} {{{ title }}}',
			'default'     => array(
				array(
					'label' => esc_html__( 'Step 1', 'teckform' ),
					'title' => esc_html__( 'Discover', 'teckform' ),
					'text'  => esc_html__( 'We learn about your business, your customers and your goals.', 'teckform' ),
					'icon'  => array(
						'value'   => 'fas fa-search',
						'library' => 'fa-solid',
					),
				),
				array(
					'label' => esc_html__( 'Step 2', 'teckform' ),
					'title' => esc_html__( 'Design', 'teckform' ),
					'text'  => esc_html__( 'We sketch, prototype and refine the look until it feels right.', 'teckform' ),
					'icon'  => array(
						'value'   => 'fas fa-pencil-ruler',
						'library' => 'fa-solid',
					),
				),
				array(
					'label' => esc_html__( 'Step 3', 'teckform' ),
					'title' => esc_html__( 'Build', 'teckform' ),
					'text'  => esc_html__( 'We develop a fast, accessible website you can edit yourself.', 'teckform' ),
					'icon'  => array(
						'value'   => 'fas fa-code',
						'library' => 'fa-solid',
					),
				),
				array(
					'label' => esc_html__( 'Step 4', 'teckform' ),
					'title' => esc_html__( 'Launch', 'teckform' ),
					'text'  => esc_html__( 'We go live, measure the results and keep improving.', 'teckform' ),
					'icon'  => array(
						'value'   => 'fas fa-rocket',
						'library' => 'fa-solid',
					),
				),
			),
		) );

		$this->end_controls_section();
	}

	private function register_layout_controls() {
		$this->start_controls_section( 'section_layout', array(
			'label' => esc_html__( 'Layout', 'teckform' ),
		) );

		$this->add_control( 'layout', array(
			'label'       => esc_html__( 'Layout', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'horizontal',
			'options'     => $this->get_layouts(),
			'description' => esc_html__( 'On phones every layout shows as a vertical timeline.', 'teckform' ),
		) );

		$this->add_control( 'align', array(
			'label'     => esc_html__( 'Step Alignment', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'start',
			'toggle'    => false,
			'options'   => array(
				'start'  => array(
					'title' => esc_html__( 'Start', 'teckform' ),
					'icon'  => 'eicon-text-align-left',
				),
				'center' => array(
					'title' => esc_html__( 'Center', 'teckform' ),
					'icon'  => 'eicon-text-align-center',
				),
			),
			'condition' => array( 'layout' => 'horizontal' ),
		) );

		$this->add_control( 'first_side', array(
			'label'     => esc_html__( 'First Step Side', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'end',
			'toggle'    => false,
			'options'   => array(
				'start' => array(
					'title' => esc_html__( 'Start', 'teckform' ),
					'icon'  => 'eicon-h-align-left',
				),
				'end'   => array(
					'title' => esc_html__( 'End', 'teckform' ),
					'icon'  => 'eicon-h-align-right',
				),
			),
			'condition' => array( 'layout' => 'alternating' ),
		) );

		$this->add_control( 'marker', array(
			'label'     => esc_html__( 'Marker', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'number',
			'separator' => 'before',
			'options'   => array(
				'number' => esc_html__( 'Step number', 'teckform' ),
				'icon'   => esc_html__( 'Icon', 'teckform' ),
				'dot'    => esc_html__( 'Small dot', 'teckform' ),
			),
		) );

		$this->add_control( 'number_format', array(
			'label'     => esc_html__( 'Number Format', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'plain',
			'options'   => array(
				'plain'   => '1, 2, 3',
				'leading' => '01, 02, 03',
			),
			'condition' => array( 'marker' => array( 'number', 'icon' ) ),
		) );

		$this->add_control( 'title_tag', array(
			'label'   => esc_html__( 'Title HTML Tag', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h3',
			'options' => array(
				'h2'  => 'H2',
				'h3'  => 'H3',
				'h4'  => 'H4',
				'h5'  => 'H5',
				'div' => 'div',
			),
		) );

		$this->add_control( 'animate', array(
			'label'        => esc_html__( 'Animate on Scroll', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
			'description'  => esc_html__( 'Steps fade in and the line draws as they scroll into view. Off for visitors who prefer reduced motion.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_steps_style_controls() {
		$this->start_controls_section( 'section_style_steps', array(
			'label' => esc_html__( 'Steps', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'gap_x', array(
			'label'      => esc_html__( 'Space Between Steps (Horizontal)', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 120,
				),
			),
			'condition'  => array( 'layout' => 'horizontal' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process' => '--teckform-process-gap-x: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'gap_y', array(
			'label'      => esc_html__( 'Space Between Steps (Vertical)', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 160,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process' => '--teckform-process-gap-y: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'content_gap', array(
			'label'      => esc_html__( 'Space Between Marker and Text', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process' => '--teckform-process-content-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_marker_style_controls() {
		$this->start_controls_section( 'section_style_marker', array(
			'label' => esc_html__( 'Number / Icon Circle', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'marker_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 8,
					'max' => 140,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process' => '--teckform-process-marker: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'marker_icon_size', array(
			'label'      => esc_html__( 'Icon Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 8,
					'max' => 80,
				),
			),
			'condition'  => array( 'marker' => 'icon' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process__marker' => '--teckform-process-icon-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'marker_typography',
			'label'     => esc_html__( 'Number Typography', 'teckform' ),
			'condition' => array( 'marker!' => 'dot' ),
			'selector'  => '{{WRAPPER}} .teckform-process__marker',
		) );

		$this->add_responsive_control( 'marker_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 70,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process__marker' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'marker_border',
			'selector' => '{{WRAPPER}} .teckform-process__marker',
		) );

		$this->start_controls_tabs( 'marker_tabs' );

		$this->start_controls_tab( 'marker_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'marker_color', array(
			'label'     => esc_html__( 'Number / Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__marker' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'marker_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__marker' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'marker_shadow',
			'selector' => '{{WRAPPER}} .teckform-process__marker',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'marker_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'marker_hover_color', array(
			'label'     => esc_html__( 'Number / Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__step:hover .teckform-process__marker' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'marker_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__step:hover .teckform-process__marker' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'marker_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__step:hover .teckform-process__marker' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'marker_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-process__step:hover .teckform-process__marker',
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_line_style_controls() {
		$this->start_controls_section( 'section_style_line', array(
			'label' => esc_html__( 'Line', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'line_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process' => '--teckform-process-line-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'line_style', array(
			'label'     => esc_html__( 'Style', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '',
			'options'   => array(
				''       => esc_html__( 'Default', 'teckform' ),
				'solid'  => esc_html__( 'Solid', 'teckform' ),
				'dashed' => esc_html__( 'Dashed', 'teckform' ),
				'dotted' => esc_html__( 'Dotted', 'teckform' ),
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-process' => '--teckform-process-line-style: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'line_width', array(
			'label'      => esc_html__( 'Thickness', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 1,
					'max' => 10,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process' => '--teckform-process-line-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'line_gap', array(
			'label'      => esc_html__( 'Space Between Line and Marker', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process' => '--teckform-process-line-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'hide_line', array(
			'label'        => esc_html__( 'Hide Line', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'selectors'    => array(
				'{{WRAPPER}} .teckform-process__line' => 'display: none;',
			),
		) );

		$this->end_controls_section();
	}

	private function register_content_style_controls() {
		$this->start_controls_section( 'section_style_content', array(
			'label' => esc_html__( 'Content Box', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'content_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'content_radius', array(
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
				'{{WRAPPER}} .teckform-process__content' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'content_border',
			'selector' => '{{WRAPPER}} .teckform-process__content',
		) );

		$this->start_controls_tabs( 'content_tabs' );

		$this->start_controls_tab( 'content_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'content_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__content' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'content_shadow',
			'selector' => '{{WRAPPER}} .teckform-process__content',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'content_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'content_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__step:hover .teckform-process__content' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'content_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__step:hover .teckform-process__content' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'content_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-process__step:hover .teckform-process__content',
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_text_style_controls() {
		$this->start_controls_section( 'section_style_text', array(
			'label' => esc_html__( 'Label, Title and Text', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'label_heading', array(
			'label' => esc_html__( 'Label', 'teckform' ),
			'type'  => Controls_Manager::HEADING,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'label_typography',
			'selector' => '{{WRAPPER}} .teckform-process__label',
		) );

		$this->add_control( 'label_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__label' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'label_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__label' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'label_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process__label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'label_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 50,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process__label' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'label_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process__label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'title_heading', array(
			'label'     => esc_html__( 'Title', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .teckform-process__title',
		) );

		$this->add_control( 'title_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'title_hover_color', array(
			'label'     => esc_html__( 'Hover Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__step:hover .teckform-process__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'title_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'text_heading', array(
			'label'     => esc_html__( 'Text', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'text_typography',
			'selector' => '{{WRAPPER}} .teckform-process__text',
		) );

		$this->add_control( 'text_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-process__text' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'text_width', array(
			'label'      => esc_html__( 'Max Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 120,
					'max' => 800,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-process__text' => 'max-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$steps    = is_array( $settings['steps'] ) ? $settings['steps'] : array();

		if ( ! $steps ) {
			return;
		}

		$layout = array_key_exists( $settings['layout'], $this->get_layouts() ) ? $settings['layout'] : 'horizontal';
		$marker = in_array( $settings['marker'], array( 'number', 'icon', 'dot' ), true ) ? $settings['marker'] : 'number';

		$classes = array(
			'teckform-process',
			'teckform-process--' . $layout,
			'teckform-process--marker-' . $marker,
		);

		if ( 'horizontal' === $layout && 'center' === $settings['align'] ) {
			$classes[] = 'teckform-process--align-center';
		}

		if ( 'alternating' === $layout && 'start' === $settings['first_side'] ) {
			$classes[] = 'teckform-process--reverse';
		}

		if ( 'yes' === $settings['animate'] ) {
			$classes[] = 'teckform-process--animate';
		}

		$tag   = Utils::validate_html_tag( $settings['title_tag'] );
		$count = count( $steps );
		?>
		<ol class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php foreach ( $steps as $index => $step ) : ?>
				<li class="teckform-process__step elementor-repeater-item-<?php echo esc_attr( $step['_id'] ); ?>">
					<div class="teckform-process__head">
						<?php $this->render_marker( $settings, $step, $index, $marker ); ?>
					</div>
					<div class="teckform-process__content">
						<?php if ( '' !== trim( $step['label'] ) ) : ?>
							<span class="teckform-process__label"><?php echo esc_html( $step['label'] ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== trim( $step['title'] ) ) : ?>
							<<?php echo esc_html( $tag ); ?> class="teckform-process__title"><?php echo esc_html( $step['title'] ); ?></<?php echo esc_html( $tag ); ?>>
						<?php endif; ?>
						<?php if ( '' !== trim( $step['text'] ) ) : ?>
							<p class="teckform-process__text"><?php echo wp_kses_post( $step['text'] ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $index < $count - 1 ) : ?>
						<span class="teckform-process__line" aria-hidden="true"></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Number, icon or dot. The list itself is an <ol>, so the number is decorative.
	 *
	 * @param array  $settings Widget settings.
	 * @param array  $step     Repeater item.
	 * @param int    $index    Step index.
	 * @param string $marker   Marker type.
	 */
	private function render_marker( $settings, $step, $index, $marker ) {
		echo '<span class="teckform-process__marker" aria-hidden="true">';

		if ( 'icon' === $marker && ! empty( $step['icon']['value'] ) ) {
			Icons_Manager::render_icon( $step['icon'], array( 'aria-hidden' => 'true' ) );
		} elseif ( 'dot' !== $marker ) {
			$number = $index + 1;
			echo esc_html( 'leading' === $settings['number_format'] ? sprintf( '%02d', $number ) : (string) $number );
		}

		echo '</span>';
	}
}
