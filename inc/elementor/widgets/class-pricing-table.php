<?php
/**
 * Pricing Table widget: several plans side by side with a monthly/yearly switch.
 *
 * Each plan has its own name, prices for both billing periods, a feature list
 * (lines starting with "-" are shown as not included) and a button. One or more
 * plans can be featured: raised, outlined in the accent color and badged.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Pricing_Table extends Widget_Base {

	public function get_name() {
		return 'teckform-pricing-table';
	}

	public function get_title() {
		return esc_html__( 'Pricing Table', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'pricing', 'price', 'plans', 'table', 'packages', 'subscription', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-pricing-table' );
	}

	public function get_script_depends() {
		return array( 'teckform-pricing-table' );
	}

	protected function register_controls() {
		$this->register_plan_controls();
		$this->register_switch_controls();
		$this->register_layout_controls();

		$this->register_switch_style_controls();
		$this->register_card_style_controls();
		$this->register_featured_style_controls();
		$this->register_header_style_controls();
		$this->register_price_style_controls();
		$this->register_feature_style_controls();
		$this->register_button_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_plan_controls() {
		$this->start_controls_section( 'section_plans', array(
			'label' => esc_html__( 'Plans', 'teckform' ),
		) );

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'plan_tabs' );

		$repeater->start_controls_tab( 'plan_tab_content', array( 'label' => esc_html__( 'Content', 'teckform' ) ) );

		$repeater->add_control( 'name', array(
			'label'       => esc_html__( 'Plan Name', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'Plan', 'teckform' ),
		) );

		$repeater->add_control( 'description', array(
			'label'   => esc_html__( 'Short Description', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'Who this plan is for.', 'teckform' ),
		) );

		$repeater->add_control( 'features', array(
			'label'       => esc_html__( 'Features', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 6,
			'default'     => esc_html__( "First feature\nSecond feature\n-Not included feature", 'teckform' ),
			'description' => esc_html__( 'One per line. Start a line with "-" to show it as not included.', 'teckform' ),
		) );

		$repeater->add_control( 'button_text', array(
			'label'     => esc_html__( 'Button Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Get started', 'teckform' ),
			'separator' => 'before',
		) );

		$repeater->add_control( 'button_link', array(
			'label'   => esc_html__( 'Button Link', 'teckform' ),
			'type'    => Controls_Manager::URL,
			'dynamic' => array( 'active' => true ),
			'default' => array( 'url' => '#' ),
		) );

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'plan_tab_price', array( 'label' => esc_html__( 'Price', 'teckform' ) ) );

		$repeater->add_control( 'currency', array(
			'label'   => esc_html__( 'Currency Symbol', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '$',
		) );

		$repeater->add_control( 'price_monthly', array(
			'label'       => esc_html__( 'Monthly Price', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '29',
			'description' => esc_html__( 'A number, or text such as "Custom". The currency symbol is only shown next to numbers.', 'teckform' ),
		) );

		$repeater->add_control( 'period_monthly', array(
			'label'   => esc_html__( 'Monthly Period Label', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( '/month', 'teckform' ),
		) );

		$repeater->add_control( 'price_yearly', array(
			'label'       => esc_html__( 'Yearly Price', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '23',
			'separator'   => 'before',
			'description' => esc_html__( 'Used when the switch is on Yearly. Leave empty to keep the monthly price.', 'teckform' ),
		) );

		$repeater->add_control( 'period_yearly', array(
			'label'   => esc_html__( 'Yearly Period Label', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( '/month, billed yearly', 'teckform' ),
		) );

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'plan_tab_featured', array( 'label' => esc_html__( 'Featured', 'teckform' ) ) );

		$repeater->add_control( 'featured', array(
			'label'        => esc_html__( 'Featured Plan', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Raised and outlined in the accent color.', 'teckform' ),
		) );

		$repeater->add_control( 'badge', array(
			'label'     => esc_html__( 'Badge Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Most popular', 'teckform' ),
			'condition' => array( 'featured' => 'yes' ),
		) );

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control( 'plans', array(
			'label'       => esc_html__( 'Plans', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ name }}}',
			'default'     => array(
				array(
					'name'          => esc_html__( 'Starter', 'teckform' ),
					'description'   => esc_html__( 'For small sites and landing pages.', 'teckform' ),
					'price_monthly' => '29',
					'price_yearly'  => '23',
					'features'      => esc_html__( "1 website\n5 pages\nBasic SEO\n-Priority support\n-Custom integrations", 'teckform' ),
				),
				array(
					'name'          => esc_html__( 'Growth', 'teckform' ),
					'description'   => esc_html__( 'For growing businesses that need more.', 'teckform' ),
					'price_monthly' => '79',
					'price_yearly'  => '63',
					'features'      => esc_html__( "3 websites\nUnlimited pages\nAdvanced SEO\nPriority support\n-Custom integrations", 'teckform' ),
					'featured'      => 'yes',
				),
				array(
					'name'          => esc_html__( 'Enterprise', 'teckform' ),
					'description'   => esc_html__( 'For teams with custom needs.', 'teckform' ),
					'price_monthly' => '199',
					'price_yearly'  => '159',
					'features'      => esc_html__( "Unlimited websites\nUnlimited pages\nAdvanced SEO\nPriority support\nCustom integrations", 'teckform' ),
					'button_text'   => esc_html__( 'Contact sales', 'teckform' ),
				),
			),
		) );

		$this->add_control( 'name_tag', array(
			'label'   => esc_html__( 'Plan Name HTML Tag', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h3',
			'options' => array(
				'h2'  => 'H2',
				'h3'  => 'H3',
				'h4'  => 'H4',
				'div' => 'div',
				'p'   => 'p',
			),
		) );

		$this->end_controls_section();
	}

	private function register_switch_controls() {
		$this->start_controls_section( 'section_switch', array(
			'label' => esc_html__( 'Monthly / Yearly Switch', 'teckform' ),
		) );

		$this->add_control( 'show_switch', array(
			'label'        => esc_html__( 'Show Switch', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'default_period', array(
			'label'     => esc_html__( 'Selected First', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'monthly',
			'options'   => array(
				'monthly' => esc_html__( 'Monthly', 'teckform' ),
				'yearly'  => esc_html__( 'Yearly', 'teckform' ),
			),
			'condition' => array( 'show_switch' => 'yes' ),
		) );

		$this->add_control( 'label_monthly', array(
			'label'     => esc_html__( 'Monthly Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Monthly', 'teckform' ),
			'condition' => array( 'show_switch' => 'yes' ),
		) );

		$this->add_control( 'label_yearly', array(
			'label'     => esc_html__( 'Yearly Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Yearly', 'teckform' ),
			'condition' => array( 'show_switch' => 'yes' ),
		) );

		$this->add_control( 'save_note', array(
			'label'       => esc_html__( 'Savings Note', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Save 20%', 'teckform' ),
			'description' => esc_html__( 'Shown next to the yearly label. Leave empty to hide.', 'teckform' ),
			'condition'   => array( 'show_switch' => 'yes' ),
		) );

		$this->end_controls_section();
	}

	private function register_layout_controls() {
		$this->start_controls_section( 'section_layout', array(
			'label' => esc_html__( 'Layout', 'teckform' ),
		) );

		$this->add_responsive_control( 'columns', array(
			'label'          => esc_html__( 'Columns', 'teckform' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '',
			'mobile_default' => '1',
			'options'        => array(
				''  => esc_html__( 'Automatic', 'teckform' ),
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
			'description'    => esc_html__( 'Automatic puts all plans in one row on desktop, two per row on tablet and one per row on phones.', 'teckform' ),
			'selectors'      => array(
				'{{WRAPPER}} .teckform-pricing' => '--teckform-pricing-columns: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'align', array(
			'label'                => esc_html__( 'Text Alignment', 'teckform' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => array(
				'start'  => array(
					'title' => esc_html__( 'Start', 'teckform' ),
					'icon'  => 'eicon-text-align-left',
				),
				'center' => array(
					'title' => esc_html__( 'Center', 'teckform' ),
					'icon'  => 'eicon-text-align-center',
				),
			),
			'selectors_dictionary' => array(
				'start'  => 'text-align: start; --teckform-pricing-justify: flex-start;',
				'center' => 'text-align: center; --teckform-pricing-justify: center;',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-pricing__plan' => '{{VALUE}}',
			),
		) );

		$this->add_control( 'equal_buttons', array(
			'label'        => esc_html__( 'Buttons at the Bottom', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Lines the buttons up when plans have different numbers of features.', 'teckform' ),
		) );

		$this->add_control( 'button_position', array(
			'label'   => esc_html__( 'Button Position', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'after',
			'options' => array(
				'before' => esc_html__( 'Above features', 'teckform' ),
				'after'  => esc_html__( 'Below features', 'teckform' ),
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_switch_style_controls() {
		$this->start_controls_section( 'section_style_switch', array(
			'label'     => esc_html__( 'Switch', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_switch' => 'yes' ),
		) );

		$this->add_responsive_control( 'switch_align', array(
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
				'{{WRAPPER}} .teckform-pricing__switch' => 'justify-content: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'switch_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 120,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__switch' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'switch_typography',
			'selector' => '{{WRAPPER}} .teckform-pricing__switch-label',
		) );

		$this->add_control( 'switch_label_color', array(
			'label'     => esc_html__( 'Label Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__switch-label' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'switch_label_active_color', array(
			'label'     => esc_html__( 'Selected Label Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__switch-label.is-active' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'switch_track_color', array(
			'label'     => esc_html__( 'Track Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__toggle' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'switch_track_active_color', array(
			'label'     => esc_html__( 'Track Color When Yearly', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__toggle[aria-checked="true"]' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'switch_knob_color', array(
			'label'     => esc_html__( 'Knob Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__toggle-knob' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'save_typography',
			'label'     => esc_html__( 'Savings Note Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-pricing__save',
		) );

		$this->add_control( 'save_color', array(
			'label'     => esc_html__( 'Savings Note Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__save' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'save_bg', array(
			'label'     => esc_html__( 'Savings Note Background', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__save' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_card_style_controls() {
		$this->start_controls_section( 'section_style_card', array(
			'label' => esc_html__( 'Plan Cards', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'card_gap', array(
			'label'      => esc_html__( 'Space Between Cards', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing' => '--teckform-pricing-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'card_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__plan' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'card_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 48,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__plan' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'           => 'card_border',
			'selector'       => '{{WRAPPER}} .teckform-pricing__plan:not(.teckform-pricing__plan--featured)',
			'fields_options' => array(
				'border' => array(
					'description' => esc_html__( 'The featured plan has its own border in the Featured Plan section.', 'teckform' ),
				),
			),
		) );

		$this->start_controls_tabs( 'card_tabs' );

		$this->start_controls_tab( 'card_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'card_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'card_text', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_shadow',
			'selector' => '{{WRAPPER}} .teckform-pricing__plan',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'card_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'card_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan:hover' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'card_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan:not(.teckform-pricing__plan--featured):hover' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-pricing__plan:hover',
		) );

		$this->add_control( 'card_hover_lift', array(
			'label'        => esc_html__( 'Lift on Hover', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_featured_style_controls() {
		$this->start_controls_section( 'section_style_featured', array(
			'label' => esc_html__( 'Featured Plan', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'featured_raise', array(
			'label'       => esc_html__( 'Raise', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array(
				'px' => array(
					'min' => 0,
					'max' => 48,
				),
			),
			'description' => esc_html__( 'How far the featured plan sits above the others when they are in one row.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-pricing' => '--teckform-pricing-raise: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'featured_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan--featured, {{WRAPPER}} .teckform-pricing__plan--featured:hover' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'featured_text', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan--featured, {{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__name, {{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__price' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'featured_muted', array(
			'label'     => esc_html__( 'Secondary Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__description, {{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__period, {{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__feature' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'featured_border',
			'selector' => '{{WRAPPER}} .teckform-pricing__plan--featured',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'featured_shadow',
			'selector' => '{{WRAPPER}} .teckform-pricing__plan--featured',
		) );

		$this->add_control( 'badge_heading', array(
			'label'     => esc_html__( 'Badge', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'badge_typography',
			'selector' => '{{WRAPPER}} .teckform-pricing__badge',
		) );

		$this->add_control( 'badge_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__badge' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'badge_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__badge' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'badge_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'badge_radius', array(
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
				'{{WRAPPER}} .teckform-pricing__badge' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_header_style_controls() {
		$this->start_controls_section( 'section_style_header', array(
			'label' => esc_html__( 'Name and Description', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'name_typography',
			'label'    => esc_html__( 'Name Typography', 'teckform' ),
			'selector' => '{{WRAPPER}} .teckform-pricing__name',
		) );

		$this->add_control( 'name_color', array(
			'label'     => esc_html__( 'Name Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__name' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'description_typography',
			'label'     => esc_html__( 'Description Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-pricing__description',
		) );

		$this->add_control( 'description_color', array(
			'label'     => esc_html__( 'Description Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__description' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'header_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__header' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_price_style_controls() {
		$this->start_controls_section( 'section_style_price', array(
			'label' => esc_html__( 'Price', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'price_typography',
			'selector' => '{{WRAPPER}} .teckform-pricing__amount',
		) );

		$this->add_control( 'price_color', array(
			'label'     => esc_html__( 'Price Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__price' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'currency_typography',
			'label'     => esc_html__( 'Currency Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-pricing__currency',
		) );

		$this->add_control( 'currency_position', array(
			'label'   => esc_html__( 'Currency Position', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'before',
			'options' => array(
				'before' => esc_html__( 'Before the number', 'teckform' ),
				'after'  => esc_html__( 'After the number', 'teckform' ),
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'period_typography',
			'label'     => esc_html__( 'Period Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-pricing__period',
		) );

		$this->add_control( 'period_color', array(
			'label'     => esc_html__( 'Period Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__period' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'price_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'separator'  => 'before',
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__price' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_feature_style_controls() {
		$this->start_controls_section( 'section_style_features', array(
			'label' => esc_html__( 'Features', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'feature_typography',
			'selector' => '{{WRAPPER}} .teckform-pricing__feature',
		) );

		$this->add_control( 'feature_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__feature' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'feature_excluded_color', array(
			'label'     => esc_html__( 'Not Included Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__feature.is-excluded' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'feature_check_color', array(
			'label'     => esc_html__( 'Check Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing' => '--teckform-pricing-check: {{VALUE}};',
			),
		) );

		$this->add_control( 'feature_cross_color', array(
			'label'     => esc_html__( 'Cross Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing' => '--teckform-pricing-cross: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'feature_icon_size', array(
			'label'      => esc_html__( 'Icon Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 10,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__feature svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'feature_gap', array(
			'label'      => esc_html__( 'Space Between Items', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'separator'  => 'before',
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__features' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'feature_divider', array(
			'label'        => esc_html__( 'Line Above Features', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'selectors'    => array(
				'{{WRAPPER}} .teckform-pricing__features' => 'border-top: 1px solid var(--teckform-border); padding-top: 24px;',
			),
		) );

		$this->add_responsive_control( 'features_spacing', array(
			'label'              => esc_html__( 'Space Around List', 'teckform' ),
			'type'               => Controls_Manager::DIMENSIONS,
			'size_units'         => array( 'px' ),
			'allowed_dimensions' => 'vertical',
			'selectors'          => array(
				'{{WRAPPER}} .teckform-pricing__features' => 'margin-top: {{TOP}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_button_style_controls() {
		$this->start_controls_section( 'section_style_button', array(
			'label' => esc_html__( 'Button', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'button_typography',
			'selector' => '{{WRAPPER}} .teckform-pricing__button',
		) );

		$this->add_control( 'button_full_width', array(
			'label'        => esc_html__( 'Full Width', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'selectors'    => array(
				'{{WRAPPER}} .teckform-pricing__button' => 'width: 100%;',
			),
		) );

		$this->add_responsive_control( 'button_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-pricing__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'button_radius', array(
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
				'{{WRAPPER}} .teckform-pricing__button' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'button_border',
			'selector' => '{{WRAPPER}} .teckform-pricing__button',
		) );

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'button_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__button' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__button' => 'background-color: {{VALUE}};',
			),
		) );

		// Not "button_border_color": the Border group control above already uses that ID.
		$this->add_control( 'button_normal_border_color', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__button' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'featured_button_heading', array(
			'label'     => esc_html__( 'Featured Plan Button', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'featured_button_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__button' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'featured_button_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__button' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'button_hover_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__button:hover, {{WRAPPER}} .teckform-pricing__button:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__button:hover, {{WRAPPER}} .teckform-pricing__button:focus-visible' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_hover_border_color', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__button:hover, {{WRAPPER}} .teckform-pricing__button:focus-visible' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'featured_button_hover_heading', array(
			'label'     => esc_html__( 'Featured Plan Button', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'featured_button_hover_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__button:hover, {{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__button:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'featured_button_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__button:hover, {{WRAPPER}} .teckform-pricing__plan--featured .teckform-pricing__button:focus-visible' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
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
		$plans    = is_array( $settings['plans'] ) ? $settings['plans'] : array();

		if ( ! $plans ) {
			return;
		}

		$switch = 'yes' === $settings['show_switch'];
		$period = $switch && 'yearly' === $settings['default_period'] ? 'yearly' : 'monthly';

		$classes = array( 'teckform-pricing' );

		if ( 'yes' === $settings['equal_buttons'] ) {
			$classes[] = 'teckform-pricing--buttons-bottom';
		}

		if ( 'yes' === $settings['card_hover_lift'] ) {
			$classes[] = 'teckform-pricing--lift';
		}

		if ( 'after' === $settings['currency_position'] ) {
			$classes[] = 'teckform-pricing--currency-after';
		}

		$this->add_render_attribute( 'wrapper', array(
			'class'       => $classes,
			'data-period' => $period,
			// Plans per row in the Automatic setting (up to four).
			'style'       => '--teckform-pricing-count: ' . min( 4, count( $plans ) ) . ';',
		) );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php
			if ( $switch ) {
				$this->render_switch( $settings, $period );
			}
			?>
			<div class="teckform-pricing__plans">
				<?php
				foreach ( $plans as $index => $plan ) {
					$this->render_plan( $settings, $plan, $index, $period );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * The monthly / yearly switch. The button is the keyboard control;
	 * the labels next to it can also be clicked.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $period   Period selected on load.
	 */
	private function render_switch( $settings, $period ) {
		$id      = 'teckform-pricing-' . $this->get_id();
		$yearly  = 'yearly' === $period;
		$labeled = $id . '-yearly' . ( '' !== $settings['save_note'] ? ' ' . $id . '-save' : '' );
		?>
		<div class="teckform-pricing__switch">
			<span class="teckform-pricing__switch-label<?php echo $yearly ? '' : ' is-active'; ?>" data-period="monthly" id="<?php echo esc_attr( $id . '-monthly' ); ?>"><?php echo esc_html( $settings['label_monthly'] ); ?></span>
			<button type="button" class="teckform-pricing__toggle" role="switch" aria-checked="<?php echo $yearly ? 'true' : 'false'; ?>" aria-labelledby="<?php echo esc_attr( $labeled ); ?>">
				<span class="teckform-pricing__toggle-knob" aria-hidden="true"></span>
			</button>
			<span class="teckform-pricing__switch-label<?php echo $yearly ? ' is-active' : ''; ?>" data-period="yearly" id="<?php echo esc_attr( $id . '-yearly' ); ?>"><?php echo esc_html( $settings['label_yearly'] ); ?></span>
			<?php if ( '' !== $settings['save_note'] ) : ?>
				<span class="teckform-pricing__save" id="<?php echo esc_attr( $id . '-save' ); ?>"><?php echo esc_html( $settings['save_note'] ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * One plan card.
	 *
	 * @param array  $settings Widget settings.
	 * @param array  $plan     Repeater item.
	 * @param int    $index    Item index.
	 * @param string $period   Period selected on load.
	 */
	private function render_plan( $settings, $plan, $index, $period ) {
		$featured = 'yes' === $plan['featured'];
		$name_tag = Utils::validate_html_tag( $settings['name_tag'] );

		$classes = array( 'teckform-pricing__plan', 'elementor-repeater-item-' . $plan['_id'] );

		if ( $featured ) {
			$classes[] = 'teckform-pricing__plan--featured';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php if ( $featured && '' !== trim( $plan['badge'] ) ) : ?>
				<span class="teckform-pricing__badge"><?php echo esc_html( $plan['badge'] ); ?></span>
			<?php endif; ?>

			<div class="teckform-pricing__header">
				<?php if ( '' !== trim( $plan['name'] ) ) : ?>
					<<?php echo esc_html( $name_tag ); ?> class="teckform-pricing__name"><?php echo esc_html( $plan['name'] ); ?></<?php echo esc_html( $name_tag ); ?>>
				<?php endif; ?>
				<?php if ( '' !== trim( $plan['description'] ) ) : ?>
					<p class="teckform-pricing__description"><?php echo esc_html( $plan['description'] ); ?></p>
				<?php endif; ?>
			</div>

			<?php
			$this->render_price( $plan, $period );

			if ( 'before' === $settings['button_position'] ) {
				$this->render_button( $plan, $index );
			}

			$this->render_features( $plan['features'] );

			if ( 'before' !== $settings['button_position'] ) {
				$this->render_button( $plan, $index );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Price with both periods in data attributes for the switch.
	 *
	 * @param array  $plan   Repeater item.
	 * @param string $period Period selected on load.
	 */
	private function render_price( $plan, $period ) {
		$monthly = trim( (string) $plan['price_monthly'] );
		$yearly  = trim( (string) $plan['price_yearly'] );
		$yearly  = '' === $yearly ? $monthly : $yearly;

		if ( '' === $monthly ) {
			return;
		}

		$amount   = 'yearly' === $period ? $yearly : $monthly;
		$label    = 'yearly' === $period ? $plan['period_yearly'] : $plan['period_monthly'];
		$currency = trim( (string) $plan['currency'] );
		?>
		<div class="teckform-pricing__price">
			<span class="teckform-pricing__currency" data-monthly="<?php echo esc_attr( $this->is_number( $monthly ) ? $currency : '' ); ?>" data-yearly="<?php echo esc_attr( $this->is_number( $yearly ) ? $currency : '' ); ?>"><?php echo esc_html( $this->is_number( $amount ) ? $currency : '' ); ?></span>
			<span class="teckform-pricing__amount" data-monthly="<?php echo esc_attr( $monthly ); ?>" data-yearly="<?php echo esc_attr( $yearly ); ?>"><?php echo esc_html( $amount ); ?></span>
			<span class="teckform-pricing__period" data-monthly="<?php echo esc_attr( $plan['period_monthly'] ); ?>" data-yearly="<?php echo esc_attr( $plan['period_yearly'] ); ?>"><?php echo esc_html( $label ); ?></span>
		</div>
		<?php
	}

	/**
	 * Feature list. Lines starting with "-" are not included.
	 *
	 * @param string $text Features, one per line.
	 */
	private function render_features( $text ) {
		$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ), 'strlen' );

		if ( ! $lines ) {
			return;
		}

		echo '<ul class="teckform-pricing__features">';

		foreach ( $lines as $line ) {
			$excluded = 0 === strpos( $line, '-' );
			$line     = $excluded ? ltrim( substr( $line, 1 ) ) : $line;

			if ( '' === $line ) {
				continue;
			}

			if ( $excluded ) {
				echo '<li class="teckform-pricing__feature is-excluded"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.3 7.1l-1.4-1.4-4.9 4.9-4.9-4.9-1.4 1.4 4.9 4.9-4.9 4.9 1.4 1.4 4.9-4.9 4.9 4.9 1.4-1.4-4.9-4.9z"/></svg>';
				echo '<span><span class="screen-reader-text">' . esc_html__( 'Not included:', 'teckform' ) . ' </span>' . esc_html( $line ) . '</span></li>';
			} else {
				echo '<li class="teckform-pricing__feature"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 16.2l-3.5-3.5L4 14.1l5 5 11-11-1.4-1.4z"/></svg>';
				echo '<span>' . esc_html( $line ) . '</span></li>';
			}
		}

		echo '</ul>';
	}

	/**
	 * @param array $plan  Repeater item.
	 * @param int   $index Item index.
	 */
	private function render_button( $plan, $index ) {
		if ( '' === trim( $plan['button_text'] ) ) {
			return;
		}

		$key = 'button_' . $index;

		$this->add_render_attribute( $key, 'class', 'teckform-pricing__button' );

		if ( ! empty( $plan['button_link']['url'] ) ) {
			$this->add_link_attributes( $key, $plan['button_link'] );
		}

		echo '<div class="teckform-pricing__action"><a ' . $this->get_render_attribute_string( $key ) . '>' . esc_html( $plan['button_text'] ) . '</a></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes escaped by Elementor.
	}

	/**
	 * Whether a price is a number (thousands separators allowed), so the currency shows.
	 *
	 * @param string $value Price text.
	 * @return bool
	 */
	private function is_number( $value ) {
		return is_numeric( str_replace( array( ',', ' ' ), '', (string) $value ) );
	}
}
