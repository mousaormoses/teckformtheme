<?php
/**
 * Testimonials Carousel widget: customer quotes in a sliding row.
 *
 * Quotes come from the Testimonials content type or are typed into the widget.
 * The slider is a native scroll-snap row, so it swipes on touch screens without
 * JavaScript; assets/js/testimonials.js adds the arrows, dots, autoplay, mouse
 * dragging and keyboard control.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;
use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Testimonials extends Widget_Base {

	public function get_name() {
		return 'teckform-testimonials';
	}

	public function get_title() {
		return esc_html__( 'Testimonials Carousel', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-testimonial-carousel';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'testimonial', 'testimonials', 'review', 'quote', 'carousel', 'slider', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-testimonials' );
	}

	public function get_script_depends() {
		return array( 'teckform-testimonials' );
	}

	protected function register_controls() {
		$this->register_source_controls();
		$this->register_layout_controls();
		$this->register_carousel_controls();

		$this->register_carousel_style_controls();
		$this->register_card_style_controls();
		$this->register_quote_style_controls();
		$this->register_author_style_controls();
		$this->register_rating_style_controls();
		$this->register_arrow_style_controls();
		$this->register_dot_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_source_controls() {
		$this->start_controls_section( 'section_testimonials', array(
			'label' => esc_html__( 'Testimonials', 'teckform' ),
		) );

		$this->add_control( 'source', array(
			'label'   => esc_html__( 'Source', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'manual',
			'options' => array(
				'posts'  => esc_html__( 'Testimonials (from the dashboard)', 'teckform' ),
				'manual' => esc_html__( 'Manual (typed here)', 'teckform' ),
			),
		) );

		$this->add_control( 'posts_number', array(
			'label'     => esc_html__( 'Number of Testimonials', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 1,
			'max'       => 50,
			'default'   => 6,
			'condition' => array( 'source' => 'posts' ),
		) );

		$this->add_control( 'posts_orderby', array(
			'label'     => esc_html__( 'Order By', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'menu_order',
			'options'   => array(
				'menu_order' => esc_html__( 'Custom order (Order field)', 'teckform' ),
				'date'       => esc_html__( 'Date added', 'teckform' ),
				'title'      => esc_html__( 'Name', 'teckform' ),
				'rand'       => esc_html__( 'Random', 'teckform' ),
			),
			'condition' => array( 'source' => 'posts' ),
		) );

		$this->add_control( 'posts_order', array(
			'label'     => esc_html__( 'Order', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'ASC',
			'options'   => array(
				'ASC'  => esc_html__( 'Ascending', 'teckform' ),
				'DESC' => esc_html__( 'Descending', 'teckform' ),
			),
			'condition' => array(
				'source'         => 'posts',
				'posts_orderby!' => 'rand',
			),
		) );

		$this->add_control( 'posts_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Add testimonials under Testimonials in the dashboard: the title is the name, the text is the quote, the featured image is the photo.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'source' => 'posts' ),
		) );

		$repeater = new Repeater();

		$repeater->add_control( 'quote', array(
			'label'   => esc_html__( 'Quote', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 5,
			'default' => esc_html__( 'Working with this team was the best decision we made this year.', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$repeater->add_control( 'name', array(
			'label'   => esc_html__( 'Name', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Customer name', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$repeater->add_control( 'role', array(
			'label'   => esc_html__( 'Role and Company', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'CEO, Company', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$repeater->add_control( 'image', array(
			'label' => esc_html__( 'Photo', 'teckform' ),
			'type'  => Controls_Manager::MEDIA,
		) );

		$repeater->add_control( 'rating', array(
			'label'       => esc_html__( 'Rating', 'teckform' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 5,
			'step'        => 0.5,
			'default'     => 5,
			'description' => esc_html__( '0 hides the stars for this quote.', 'teckform' ),
		) );

		$this->add_control( 'testimonials', array(
			'label'       => esc_html__( 'Testimonials', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ name }}}',
			'condition'   => array( 'source' => 'manual' ),
			'default'     => array(
				array(
					'quote'  => esc_html__( 'They rebuilt our website in six weeks and our enquiries doubled. Clear communication from day one.', 'teckform' ),
					'name'   => esc_html__( 'Sarah Johnson', 'teckform' ),
					'role'   => esc_html__( 'Marketing Director, Northwind', 'teckform' ),
					'rating' => 5,
				),
				array(
					'quote'  => esc_html__( 'A team that actually listens. The new brand feels like us, only better.', 'teckform' ),
					'name'   => esc_html__( 'Omar Haddad', 'teckform' ),
					'role'   => esc_html__( 'Founder, Bloom Studio', 'teckform' ),
					'rating' => 5,
				),
				array(
					'quote'  => esc_html__( 'Fast, friendly and always on time. Our app launch went without a single hitch.', 'teckform' ),
					'name'   => esc_html__( 'Emily Chen', 'teckform' ),
					'role'   => esc_html__( 'Product Lead, Fieldly', 'teckform' ),
					'rating' => 4.5,
				),
				array(
					'quote'  => esc_html__( 'Our online sales grew 40% in the first quarter after the redesign.', 'teckform' ),
					'name'   => esc_html__( 'David Miller', 'teckform' ),
					'role'   => esc_html__( 'Owner, Miller & Co.', 'teckform' ),
					'rating' => 5,
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
			'label'   => esc_html__( 'Card Layout', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'card',
			'options' => array(
				'card'     => esc_html__( 'Quote card, photo and name at the bottom', 'teckform' ),
				'centered' => esc_html__( 'Centered big quote', 'teckform' ),
			),
		) );

		$this->add_control( 'show_quote_icon', array(
			'label'        => esc_html__( 'Quote Icon', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'show_rating', array(
			'label'        => esc_html__( 'Star Rating', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'show_photo', array(
			'label'        => esc_html__( 'Photo', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'carousel_label', array(
			'label'       => esc_html__( 'Name for Screen Readers', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Testimonials', 'teckform' ),
			'separator'   => 'before',
			'description' => esc_html__( 'Read out by screen readers when they reach the carousel.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	private function register_carousel_controls() {
		$this->start_controls_section( 'section_carousel', array(
			'label' => esc_html__( 'Carousel', 'teckform' ),
		) );

		$this->add_responsive_control( 'slides_per_view', array(
			'label'          => esc_html__( 'Slides per View', 'teckform' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options'        => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
			),
			'selectors'      => array(
				'{{WRAPPER}} .teckform-testimonials' => '--teckform-testimonials-per-view: {{VALUE}};',
			),
		) );

		$this->add_control( 'autoplay', array(
			'label'        => esc_html__( 'Autoplay', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
			'description'  => esc_html__( 'Turned off for visitors who ask their device for less motion.', 'teckform' ),
		) );

		$this->add_control( 'autoplay_interval', array(
			'label'     => esc_html__( 'Seconds per Slide', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 2,
			'max'       => 20,
			'step'      => 0.5,
			'default'   => 6,
			'condition' => array( 'autoplay' => 'yes' ),
		) );

		$this->add_control( 'pause_on_hover', array(
			'label'        => esc_html__( 'Pause on Hover', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Autoplay always pauses while the keyboard focus is inside the carousel.', 'teckform' ),
			'condition'    => array( 'autoplay' => 'yes' ),
		) );

		$this->add_control( 'loop', array(
			'label'        => esc_html__( 'Loop', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'After the last slide, go back to the first.', 'teckform' ),
		) );

		$this->add_control( 'show_arrows', array(
			'label'        => esc_html__( 'Arrows', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );

		$this->add_control( 'arrows_position', array(
			'label'     => esc_html__( 'Arrows Position', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'bottom',
			'options'   => array(
				'bottom' => esc_html__( 'Below, beside the dots', 'teckform' ),
				'sides'  => esc_html__( 'On the sides', 'teckform' ),
			),
			'condition' => array( 'show_arrows' => 'yes' ),
		) );

		$this->add_control( 'show_dots', array(
			'label'        => esc_html__( 'Dots', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_carousel_style_controls() {
		$this->start_controls_section( 'section_style_carousel', array(
			'label' => esc_html__( 'Carousel', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'slides_gap', array(
			'label'      => esc_html__( 'Space Between Slides', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials' => '--teckform-testimonials-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'track_padding', array(
			'label'       => esc_html__( 'Space Above and Below Cards', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'description' => esc_html__( 'Room for card shadows, which the sliding row would otherwise cut off.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-testimonials__track' => 'padding-block: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'nav_spacing', array(
			'label'      => esc_html__( 'Space Above Dots and Arrows', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__nav' => 'margin-top: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_card_style_controls() {
		$this->start_controls_section( 'section_style_card', array(
			'label' => esc_html__( 'Card', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'card_align', array(
			'label'                => esc_html__( 'Alignment', 'teckform' ),
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
				'end'    => array(
					'title' => esc_html__( 'End', 'teckform' ),
					'icon'  => 'eicon-text-align-right',
				),
			),
			'selectors_dictionary' => array(
				'start'  => 'text-align: start; --teckform-testimonials-justify: flex-start;',
				'center' => 'text-align: center; --teckform-testimonials-justify: center;',
				'end'    => 'text-align: end; --teckform-testimonials-justify: flex-end;',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-testimonials__card' => '{{VALUE}}',
			),
		) );

		$this->add_responsive_control( 'card_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'card_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'card_border',
			'selector' => '{{WRAPPER}} .teckform-testimonials__card',
		) );

		$this->start_controls_tabs( 'card_tabs' );

		$this->start_controls_tab( 'card_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'card_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__card' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_shadow',
			'selector' => '{{WRAPPER}} .teckform-testimonials__card',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'card_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'card_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__card:hover' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'card_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__card:hover' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-testimonials__card:hover',
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control( 'quote_icon_heading', array(
			'label'     => esc_html__( 'Quote Icon', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => array( 'show_quote_icon' => 'yes' ),
		) );

		$this->add_control( 'quote_icon_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'show_quote_icon' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__icon' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'quote_icon_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 12,
					'max' => 120,
				),
			),
			'condition'  => array( 'show_quote_icon' => 'yes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_quote_style_controls() {
		$this->start_controls_section( 'section_style_quote', array(
			'label' => esc_html__( 'Quote Text', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'quote_typography',
			'selector' => '{{WRAPPER}} .teckform-testimonials__quote',
		) );

		$this->add_control( 'quote_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__quote' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'quote_spacing', array(
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
				'{{WRAPPER}} .teckform-testimonials__quote' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_author_style_controls() {
		$this->start_controls_section( 'section_style_author', array(
			'label' => esc_html__( 'Name, Role and Photo', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'name_typography',
			'label'    => esc_html__( 'Name Typography', 'teckform' ),
			'selector' => '{{WRAPPER}} .teckform-testimonials__name',
		) );

		$this->add_control( 'name_color', array(
			'label'     => esc_html__( 'Name Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__name' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'role_typography',
			'label'     => esc_html__( 'Role Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-testimonials__role',
		) );

		$this->add_control( 'role_color', array(
			'label'     => esc_html__( 'Role Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__role' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'photo_size', array(
			'label'      => esc_html__( 'Photo Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 24,
					'max' => 160,
				),
			),
			'separator'  => 'before',
			'condition'  => array( 'show_photo' => 'yes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__photo' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'photo_radius', array(
			'label'      => esc_html__( 'Photo Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'condition'  => array( 'show_photo' => 'yes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__photo' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'      => 'photo_border',
			'condition' => array( 'show_photo' => 'yes' ),
			'selector'  => '{{WRAPPER}} .teckform-testimonials__photo',
		) );

		$this->add_responsive_control( 'author_gap', array(
			'label'      => esc_html__( 'Space Between Photo and Name', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 48,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__author' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_rating_style_controls() {
		$this->start_controls_section( 'section_style_rating', array(
			'label'     => esc_html__( 'Stars', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_rating' => 'yes' ),
		) );

		$this->add_responsive_control( 'star_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 8,
					'max' => 48,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__stars' => 'font-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'star_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__stars' => '--teckform-testimonials-star: {{VALUE}};',
			),
		) );

		$this->add_control( 'star_empty_color', array(
			'label'     => esc_html__( 'Empty Star Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__stars' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'star_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 48,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__stars' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_arrow_style_controls() {
		$this->start_controls_section( 'section_style_arrows', array(
			'label'     => esc_html__( 'Arrows', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_arrows' => 'yes' ),
		) );

		$this->add_responsive_control( 'arrow_size', array(
			'label'      => esc_html__( 'Button Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 24,
					'max' => 96,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'arrow_icon_size', array(
			'label'      => esc_html__( 'Icon Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 8,
					'max' => 64,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__arrow svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'arrow_offset', array(
			'label'      => esc_html__( 'Distance From Edge', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => -80,
					'max' => 80,
				),
			),
			'condition'  => array( 'arrows_position' => 'sides' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials' => '--teckform-testimonials-arrow-offset: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'arrow_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 50,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__arrow' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'arrow_border',
			'selector' => '{{WRAPPER}} .teckform-testimonials__arrow',
		) );

		$this->start_controls_tabs( 'arrow_tabs' );

		$this->start_controls_tab( 'arrow_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'arrow_color', array(
			'label'     => esc_html__( 'Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__arrow' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'arrow_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__arrow' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'arrow_shadow',
			'selector' => '{{WRAPPER}} .teckform-testimonials__arrow',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'arrow_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'arrow_hover_color', array(
			'label'     => esc_html__( 'Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__arrow:hover, {{WRAPPER}} .teckform-testimonials__arrow:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'arrow_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__arrow:hover, {{WRAPPER}} .teckform-testimonials__arrow:focus-visible' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'arrow_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__arrow:hover, {{WRAPPER}} .teckform-testimonials__arrow:focus-visible' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'arrow_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-testimonials__arrow:hover',
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_dot_style_controls() {
		$this->start_controls_section( 'section_style_dots', array(
			'label'     => esc_html__( 'Dots', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_dots' => 'yes' ),
		) );

		$this->add_responsive_control( 'dot_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 4,
					'max' => 24,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials' => '--teckform-testimonials-dot: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'dot_active_width', array(
			'label'      => esc_html__( 'Active Dot Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 4,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials' => '--teckform-testimonials-dot-active: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'dot_gap', array(
			'label'      => esc_html__( 'Space Between', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 32,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-testimonials__dots' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'dot_tabs' );

		$this->start_controls_tab( 'dot_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'dot_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__dot' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'dot_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'dot_hover_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__dot:hover' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'dot_tab_active', array( 'label' => esc_html__( 'Active', 'teckform' ) ) );

		$this->add_control( 'dot_active_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-testimonials__dot[aria-current="true"]' => 'background-color: {{VALUE}};',
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
		$items    = 'posts' === $settings['source'] ? $this->get_post_items( $settings ) : $this->get_manual_items( $settings );

		if ( ! $items ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="teckform-testimonials__notice">' . esc_html__( 'No testimonials yet. Add some under Testimonials in the dashboard, or set the source to Manual.', 'teckform' ) . '</div>';
			}

			return;
		}

		$layout      = 'centered' === $settings['layout'] ? 'centered' : 'card';
		$arrows      = 'yes' === $settings['show_arrows'];
		$arrows_side = $arrows && 'sides' === $settings['arrows_position'];
		$dots        = 'yes' === $settings['show_dots'];
		$autoplay    = 'yes' === $settings['autoplay'];
		$interval    = max( 2, (float) $settings['autoplay_interval'] ) * 1000;
		$label       = '' !== trim( $settings['carousel_label'] ) ? $settings['carousel_label'] : __( 'Testimonials', 'teckform' );

		$this->add_render_attribute( 'wrapper', array(
			'class'                => array(
				'teckform-testimonials',
				'teckform-testimonials--' . $layout,
				$arrows_side ? 'teckform-testimonials--arrows-sides' : 'teckform-testimonials--arrows-bottom',
			),
			'role'                 => 'region',
			'aria-roledescription' => __( 'carousel', 'teckform' ),
			'aria-label'           => $label,
			'data-autoplay'        => $autoplay ? '1' : '0',
			'data-interval'        => (string) (int) $interval,
			'data-pause-hover'     => 'yes' === $settings['pause_on_hover'] ? '1' : '0',
			'data-loop'            => 'yes' === $settings['loop'] ? '1' : '0',
			/* translators: %d: slide number. Keep the %d. */
			'data-dot-label'       => __( 'Go to slide %d', 'teckform' ),
		) );

		$count = count( $items );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="teckform-testimonials__viewport">
				<div class="teckform-testimonials__track" tabindex="0">
					<?php
					foreach ( array_values( $items ) as $i => $item ) {
						$this->render_slide( $item, $settings, $i + 1, $count );
					}
					?>
				</div>
				<?php
				if ( $arrows_side ) {
					$this->render_arrows();
				}
				?>
			</div>
			<?php if ( ( $arrows && ! $arrows_side ) || $dots ) : ?>
				<div class="teckform-testimonials__nav">
					<?php
					if ( $arrows && ! $arrows_side ) {
						$this->render_arrow( 'prev' );
					}

					if ( $dots ) {
						// Filled by testimonials.js: the number of dots depends on how many slides fit.
						echo '<div class="teckform-testimonials__dots"></div>';
					}

					if ( $arrows && ! $arrows_side ) {
						$this->render_arrow( 'next' );
					}
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * One slide: quote icon, stars, quote and author.
	 *
	 * @param array $item     Normalised testimonial (see get_manual_items()).
	 * @param array $settings Widget settings.
	 * @param int   $number   Position, starting at 1.
	 * @param int   $count    Number of slides.
	 */
	private function render_slide( $item, $settings, $number, $count ) {
		$photo = 'yes' === $settings['show_photo'] ? $this->get_image_html( $item['image'], 'teckform-testimonials__photo' ) : '';
		?>
		<div class="teckform-testimonials__slide" role="group" aria-roledescription="<?php echo esc_attr__( 'slide', 'teckform' ); ?>" aria-label="<?php /* translators: 1: slide number, 2: number of slides. */ echo esc_attr( sprintf( __( '%1$d of %2$d', 'teckform' ), $number, $count ) ); ?>">
			<figure class="teckform-testimonials__card">
				<?php if ( 'yes' === $settings['show_quote_icon'] ) : ?>
					<span class="teckform-testimonials__icon" aria-hidden="true">
						<svg viewBox="0 0 32 32"><path d="M13.2 6.4C7.6 8.6 3.5 13.3 3.5 19.6c0 3.7 2.3 6 5.3 6 2.8 0 4.9-2.1 4.9-4.8 0-2.6-1.9-4.5-4.4-4.6.6-2.8 2.9-5.2 5.6-6.4l-1.7-3.4zm14.4 0c-5.6 2.2-9.7 6.9-9.7 13.2 0 3.7 2.3 6 5.3 6 2.8 0 4.9-2.1 4.9-4.8 0-2.6-1.9-4.5-4.4-4.6.6-2.8 2.9-5.2 5.6-6.4l-1.7-3.4z"/></svg>
					</span>
				<?php endif; ?>

				<?php
				if ( 'yes' === $settings['show_rating'] ) {
					$this->render_stars( $item['rating'] );
				}
				?>

				<blockquote class="teckform-testimonials__quote">
					<?php echo wpautop( wp_kses_post( $item['quote'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitised by wp_kses_post(). ?>
				</blockquote>

				<?php if ( $photo || '' !== $item['name'] || '' !== $item['role'] ) : ?>
					<figcaption class="teckform-testimonials__author">
						<?php echo $photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html(). ?>
						<span class="teckform-testimonials__meta">
							<?php if ( '' !== $item['name'] ) : ?>
								<span class="teckform-testimonials__name"><?php echo esc_html( $item['name'] ); ?></span>
							<?php endif; ?>
							<?php if ( '' !== $item['role'] ) : ?>
								<span class="teckform-testimonials__role"><?php echo esc_html( $item['role'] ); ?></span>
							<?php endif; ?>
						</span>
					</figcaption>
				<?php endif; ?>
			</figure>
		</div>
		<?php
	}

	/**
	 * Five stars, filled by width so 4.5 shows four and a half.
	 *
	 * @param float $rating Rating from 0 to 5.
	 */
	private function render_stars( $rating ) {
		$rating = min( 5, max( 0, (float) $rating ) );

		if ( $rating <= 0 ) {
			return;
		}

		printf(
			'<span class="teckform-testimonials__stars" role="img" aria-label="%1$s"><span class="teckform-testimonials__stars-fill" style="width:%2$s%%"></span></span>',
			/* translators: %s: rating out of 5. */
			esc_attr( sprintf( __( 'Rated %s out of 5', 'teckform' ), $rating ) ),
			esc_attr( round( $rating / 5 * 100, 1 ) )
		);
	}

	/**
	 * Both arrows, for the "on the sides" position.
	 */
	private function render_arrows() {
		$this->render_arrow( 'prev' );
		$this->render_arrow( 'next' );
	}

	/**
	 * @param string $which 'prev' or 'next'.
	 */
	private function render_arrow( $which ) {
		$label = 'prev' === $which ? __( 'Previous testimonial', 'teckform' ) : __( 'Next testimonial', 'teckform' );
		$path  = 'prev' === $which ? 'M15.4 7.4L14 6l-6 6 6 6 1.4-1.4-4.6-4.6z' : 'M8.6 16.6L10 18l6-6-6-6-1.4 1.4 4.6 4.6z';

		printf(
			'<button type="button" class="teckform-testimonials__arrow teckform-testimonials__arrow--%1$s" aria-label="%2$s"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="%3$s"/></svg></button>',
			esc_attr( $which ),
			esc_attr( $label ),
			esc_attr( $path )
		);
	}

	/**
	 * Testimonials typed into the widget.
	 *
	 * @param array $settings Widget settings.
	 * @return array<int, array{quote: string, name: string, role: string, image: array, rating: float}>
	 */
	private function get_manual_items( $settings ) {
		$items = array();

		foreach ( (array) $settings['testimonials'] as $row ) {
			if ( '' === trim( (string) $row['quote'] ) && '' === trim( (string) $row['name'] ) ) {
				continue;
			}

			$items[] = array(
				'quote'  => (string) $row['quote'],
				'name'   => (string) $row['name'],
				'role'   => (string) $row['role'],
				'image'  => is_array( $row['image'] ) ? $row['image'] : array(),
				'rating' => (float) $row['rating'],
			);
		}

		return $items;
	}

	/**
	 * Testimonials from the Testimonials content type (teckform_testimonial).
	 *
	 * @param array $settings Widget settings.
	 * @return array<int, array{quote: string, name: string, role: string, image: array, rating: float}>
	 */
	private function get_post_items( $settings ) {
		$orderby = in_array( $settings['posts_orderby'], array( 'menu_order', 'date', 'title', 'rand' ), true ) ? $settings['posts_orderby'] : 'menu_order';

		$query = new \WP_Query( array(
			'post_type'           => 'teckform_testimonial',
			'post_status'         => 'publish',
			'posts_per_page'      => min( 50, max( 1, absint( $settings['posts_number'] ) ) ),
			'orderby'             => $orderby,
			'order'               => 'DESC' === $settings['posts_order'] ? 'DESC' : 'ASC',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		) );

		$items = array();

		foreach ( $query->posts as $post ) {
			$items[] = array(
				'quote'  => (string) $post->post_content,
				'name'   => wp_strip_all_tags( get_the_title( $post ) ),
				'role'   => (string) get_post_meta( $post->ID, '_teckform_role', true ),
				'image'  => array( 'id' => get_post_thumbnail_id( $post ) ),
				'rating' => (float) get_post_meta( $post->ID, '_teckform_rating', true ),
			);
		}

		return $items;
	}

	/**
	 * <img> markup for a media value.
	 *
	 * @param array  $media Value with 'id' and/or 'url'.
	 * @param string $class Class for the image.
	 * @return string
	 */
	private function get_image_html( $media, $class ) {
		if ( ! empty( $media['id'] ) ) {
			$html = wp_get_attachment_image( absint( $media['id'] ), 'thumbnail', false, array(
				'class' => $class,
				'alt'   => '',
			) );

			if ( $html ) {
				return $html;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img src="' . esc_url( $media['url'] ) . '" alt="" class="' . esc_attr( $class ) . '" loading="lazy">';
		}

		return '';
	}
}
