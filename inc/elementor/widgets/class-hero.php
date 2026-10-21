<?php
/**
 * Hero widget: the first section of a page, in six layouts.
 *
 * Centered, split, full background, product screenshot, animated headline,
 * and split with form. Every part (badge, headline, buttons, media, trust row)
 * can be switched off, so one widget covers all the common hero styles.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hero extends Widget_Base {

	public function get_name() {
		return 'teckform-hero';
	}

	public function get_title() {
		return esc_html__( 'Hero', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-header';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'hero', 'banner', 'header', 'intro', 'landing', 'cta', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-hero' );
	}

	public function get_script_depends() {
		return array( 'teckform-hero' );
	}

	/**
	 * Layout keys and labels.
	 *
	 * @return array<string, string>
	 */
	private function get_layouts() {
		return array(
			'centered'   => esc_html__( 'Centered', 'teckform' ),
			'split'      => esc_html__( 'Split: text and image', 'teckform' ),
			'background' => esc_html__( 'Full background image or video', 'teckform' ),
			'screenshot' => esc_html__( 'Product screenshot', 'teckform' ),
			'animated'   => esc_html__( 'Animated headline and stats', 'teckform' ),
			'form'       => esc_html__( 'Split with form', 'teckform' ),
		);
	}

	protected function register_controls() {
		$this->register_layout_controls();
		$this->register_badge_controls();
		$this->register_headline_controls();
		$this->register_button_controls();
		$this->register_media_controls();
		$this->register_background_controls();
		$this->register_form_controls();
		$this->register_benefit_controls();
		$this->register_trust_controls();

		$this->register_box_style_controls();
		$this->register_badge_style_controls();
		$this->register_text_style_controls();
		$this->register_button_style_controls();
		$this->register_media_style_controls();
		$this->register_trust_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_layout_controls() {
		$this->start_controls_section( 'section_layout', array(
			'label' => esc_html__( 'Layout', 'teckform' ),
		) );

		$this->add_control( 'layout', array(
			'label'   => esc_html__( 'Layout', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'centered',
			'options' => $this->get_layouts(),
		) );

		$this->add_control( 'media_side', array(
			'label'     => esc_html__( 'Image / Form Side', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'right',
			'toggle'    => false,
			'options'   => array(
				'left'  => array(
					'title' => esc_html__( 'Left', 'teckform' ),
					'icon'  => 'eicon-h-align-left',
				),
				'right' => array(
					'title' => esc_html__( 'Right', 'teckform' ),
					'icon'  => 'eicon-h-align-right',
				),
			),
			'condition' => array( 'layout' => array( 'split', 'form' ) ),
		) );

		$this->add_responsive_control( 'content_align', array(
			'label'                => esc_html__( 'Text Alignment', 'teckform' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => array(
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
			'selectors_dictionary' => array(
				'left'   => 'text-align: left; --teckform-hero-justify: flex-start; margin-inline: 0 auto;',
				'center' => 'text-align: center; --teckform-hero-justify: center; margin-inline: auto;',
				'right'  => 'text-align: right; --teckform-hero-justify: flex-end; margin-inline: auto 0;',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-hero__content' => '{{VALUE}}',
			),
		) );

		$this->add_control( 'entrance_animation', array(
			'label'        => esc_html__( 'Fade In on Load', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );

		$this->add_control( 'scroll_indicator', array(
			'label'        => esc_html__( 'Scroll Down Arrow', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => array( 'layout' => 'background' ),
		) );

		$this->end_controls_section();
	}

	private function register_badge_controls() {
		$this->start_controls_section( 'section_badge', array(
			'label' => esc_html__( 'Badge', 'teckform' ),
		) );

		$this->add_control( 'show_badge', array(
			'label'        => esc_html__( 'Show Badge', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'badge_label', array(
			'label'       => esc_html__( 'Label', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'New', 'teckform' ),
			'description' => esc_html__( 'Optional small tag at the start of the badge.', 'teckform' ),
			'condition'   => array( 'show_badge' => 'yes' ),
		) );

		$this->add_control( 'badge_text', array(
			'label'     => esc_html__( 'Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'We just launched our new service', 'teckform' ),
			'condition' => array( 'show_badge' => 'yes' ),
		) );

		$this->add_control( 'badge_link', array(
			'label'     => esc_html__( 'Link', 'teckform' ),
			'type'      => Controls_Manager::URL,
			'dynamic'   => array( 'active' => true ),
			'condition' => array( 'show_badge' => 'yes' ),
		) );

		$this->end_controls_section();
	}

	private function register_headline_controls() {
		$this->start_controls_section( 'section_headline', array(
			'label' => esc_html__( 'Headline and Text', 'teckform' ),
		) );

		$this->add_control( 'title_before', array(
			'label'       => esc_html__( 'Headline', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 2,
			'default'     => esc_html__( 'We build modern', 'teckform' ),
			'dynamic'     => array( 'active' => true ),
		) );

		$this->add_control( 'title_words', array(
			'label'       => esc_html__( 'Highlighted Words', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => "websites\napps\nbrands",
			'description' => esc_html__( 'Shown in the highlight color after the headline. Put one per line to rotate between them.', 'teckform' ),
		) );

		$this->add_control( 'title_after', array(
			'label'   => esc_html__( 'Headline End', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'that grow your business', 'teckform' ),
		) );

		$this->add_control( 'title_tag', array(
			'label'   => esc_html__( 'Headline HTML Tag', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h1',
			'options' => array(
				'h1' => 'H1',
				'h2' => 'H2',
				'h3' => 'H3',
				'p'  => 'p',
			),
		) );

		$this->add_control( 'words_animation', array(
			'label'   => esc_html__( 'Word Animation', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'auto',
			'options' => array(
				'auto'   => esc_html__( 'Automatic (typing for the Animated layout, slide for others)', 'teckform' ),
				'slide'  => esc_html__( 'Slide', 'teckform' ),
				'typing' => esc_html__( 'Typing', 'teckform' ),
			),
		) );

		$this->add_control( 'words_interval', array(
			'label'   => esc_html__( 'Seconds per Word', 'teckform' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 10,
			'step'    => 0.5,
			'default' => 2.5,
		) );

		$this->add_control( 'description', array(
			'label'     => esc_html__( 'Description', 'teckform' ),
			'type'      => Controls_Manager::TEXTAREA,
			'rows'      => 4,
			'default'   => esc_html__( 'A short sentence or two that explains what you do and who it is for.', 'teckform' ),
			'dynamic'   => array( 'active' => true ),
			'separator' => 'before',
		) );

		$this->end_controls_section();
	}

	private function register_button_controls() {
		$this->start_controls_section( 'section_buttons', array(
			'label' => esc_html__( 'Buttons', 'teckform' ),
		) );

		foreach ( array( 1, 2 ) as $n ) {
			$this->add_control( "button_{$n}_text", array(
				/* translators: %d: button number. */
				'label'     => sprintf( esc_html__( 'Button %d Text', 'teckform' ), $n ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 1 === $n ? esc_html__( 'Get started', 'teckform' ) : esc_html__( 'See our work', 'teckform' ),
				'separator' => 2 === $n ? 'before' : 'default',
			) );

			$this->add_control( "button_{$n}_link", array(
				/* translators: %d: button number. */
				'label'   => sprintf( esc_html__( 'Button %d Link', 'teckform' ), $n ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
				'default' => array( 'url' => '#' ),
			) );

			$this->add_control( "button_{$n}_icon", array(
				/* translators: %d: button number. */
				'label'   => sprintf( esc_html__( 'Button %d Icon', 'teckform' ), $n ),
				'type'    => Controls_Manager::ICONS,
				'default' => 1 === $n
					? array(
						'value'   => 'fas fa-arrow-right',
						'library' => 'fa-solid',
					)
					: array(),
			) );
		}

		$this->add_control( 'buttons_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Leave a button text empty to hide that button.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
		) );

		$this->end_controls_section();
	}

	private function register_media_controls() {
		$this->start_controls_section( 'section_media', array(
			'label'     => esc_html__( 'Image or Video', 'teckform' ),
			'condition' => array( 'layout' => array( 'split', 'screenshot' ) ),
		) );

		$this->add_control( 'media_type', array(
			'label'   => esc_html__( 'Type', 'teckform' ),
			'type'    => Controls_Manager::CHOOSE,
			'default' => 'image',
			'toggle'  => false,
			'options' => array(
				'image' => array(
					'title' => esc_html__( 'Image', 'teckform' ),
					'icon'  => 'eicon-image',
				),
				'video' => array(
					'title' => esc_html__( 'Video', 'teckform' ),
					'icon'  => 'eicon-video-camera',
				),
			),
		) );

		$this->add_control( 'image', array(
			'label'     => esc_html__( 'Image', 'teckform' ),
			'type'      => Controls_Manager::MEDIA,
			'default'   => array( 'url' => Utils::get_placeholder_image_src() ),
			'condition' => array( 'media_type' => 'image' ),
		) );

		$this->add_control( 'video', array(
			'label'       => esc_html__( 'Video', 'teckform' ),
			'type'        => Controls_Manager::MEDIA,
			'media_types' => array( 'video' ),
			'description' => esc_html__( 'An MP4 file. It plays silently on a loop.', 'teckform' ),
			'condition'   => array( 'media_type' => 'video' ),
		) );

		$this->add_control( 'browser_frame', array(
			'label'        => esc_html__( 'Browser Window Frame', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Shows the image inside a browser window, good for screenshots.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	private function register_background_controls() {
		$this->start_controls_section( 'section_background', array(
			'label' => esc_html__( 'Background', 'teckform' ),
		) );

		$this->add_control( 'bg_image', array(
			'label'     => esc_html__( 'Background Image', 'teckform' ),
			'type'      => Controls_Manager::MEDIA,
			'default'   => array( 'url' => Utils::get_placeholder_image_src() ),
			'condition' => array( 'layout' => 'background' ),
		) );

		$this->add_control( 'bg_video', array(
			'label'       => esc_html__( 'Background Video', 'teckform' ),
			'type'        => Controls_Manager::MEDIA,
			'media_types' => array( 'video' ),
			'description' => esc_html__( 'Optional MP4. The image shows while it loads and on phones that block autoplay.', 'teckform' ),
			'condition'   => array( 'layout' => 'background' ),
		) );

		$this->add_control( 'pattern', array(
			'label'     => esc_html__( 'Background Pattern', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'grid',
			'options'   => array(
				'none' => esc_html__( 'None', 'teckform' ),
				'grid' => esc_html__( 'Grid lines', 'teckform' ),
				'dots' => esc_html__( 'Dots', 'teckform' ),
				'glow' => esc_html__( 'Soft color glow', 'teckform' ),
			),
			'condition' => array( 'layout!' => 'background' ),
		) );

		$this->add_control( 'background_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Background colors are in the Style tab, under Box.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
		) );

		$this->end_controls_section();
	}

	private function register_form_controls() {
		$this->start_controls_section( 'section_form', array(
			'label'     => esc_html__( 'Form', 'teckform' ),
			'condition' => array( 'layout' => 'form' ),
		) );

		$this->add_control( 'form_title', array(
			'label'   => esc_html__( 'Form Title', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Get a free quote', 'teckform' ),
		) );

		$this->add_control( 'form_text', array(
			'label'   => esc_html__( 'Form Text', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'Tell us about your project and we will reply within one day.', 'teckform' ),
		) );

		$this->add_control( 'form_shortcode', array(
			'label'       => esc_html__( 'Form Shortcode', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => '[contact-form-7 id="123"]',
			'description' => esc_html__( 'Paste the shortcode from your form plugin (Contact Form 7, WPForms, Fluent Forms...). Without one, a button is shown instead.', 'teckform' ),
		) );

		$this->add_control( 'form_button_text', array(
			'label'     => esc_html__( 'Button Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Request a quote', 'teckform' ),
			'condition' => array( 'form_shortcode' => '' ),
		) );

		$this->add_control( 'form_button_link', array(
			'label'     => esc_html__( 'Button Link', 'teckform' ),
			'type'      => Controls_Manager::URL,
			'dynamic'   => array( 'active' => true ),
			'default'   => array( 'url' => '#' ),
			'condition' => array( 'form_shortcode' => '' ),
		) );

		$this->end_controls_section();
	}

	private function register_benefit_controls() {
		$this->start_controls_section( 'section_benefits', array(
			'label'     => esc_html__( 'Benefits List', 'teckform' ),
			'condition' => array( 'layout' => array( 'split', 'form' ) ),
		) );

		$repeater = new Repeater();

		$repeater->add_control( 'text', array(
			'label'       => esc_html__( 'Text', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'A benefit for your customer', 'teckform' ),
		) );

		$this->add_control( 'benefits', array(
			'label'       => esc_html__( 'Benefits', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ text }}}',
			'default'     => array(
				array( 'text' => esc_html__( 'Free consultation and estimate', 'teckform' ) ),
				array( 'text' => esc_html__( 'Delivered on time, every time', 'teckform' ) ),
				array( 'text' => esc_html__( 'Support after launch', 'teckform' ) ),
			),
		) );

		$this->end_controls_section();
	}

	private function register_trust_controls() {
		$this->start_controls_section( 'section_trust', array(
			'label' => esc_html__( 'Trust Row', 'teckform' ),
		) );

		$this->add_control( 'trust_type', array(
			'label'   => esc_html__( 'Show', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'auto',
			'options' => array(
				'auto'    => esc_html__( 'Automatic (best for the layout)', 'teckform' ),
				'none'    => esc_html__( 'Nothing', 'teckform' ),
				'logos'   => esc_html__( 'Client logos', 'teckform' ),
				'avatars' => esc_html__( 'Customer photos and rating', 'teckform' ),
				'stats'   => esc_html__( 'Numbers', 'teckform' ),
			),
		) );

		$this->add_control( 'trust_title', array(
			'label'     => esc_html__( 'Title', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Trusted by teams at', 'teckform' ),
			'condition' => array( 'trust_type' => array( 'auto', 'logos' ) ),
		) );

		$this->add_control( 'logos', array(
			'label'     => esc_html__( 'Logos', 'teckform' ),
			'type'      => Controls_Manager::GALLERY,
			'condition' => array( 'trust_type' => array( 'auto', 'logos' ) ),
		) );

		$this->add_control( 'avatars', array(
			'label'     => esc_html__( 'Customer Photos', 'teckform' ),
			'type'      => Controls_Manager::GALLERY,
			'condition' => array( 'trust_type' => array( 'auto', 'avatars' ) ),
		) );

		$this->add_control( 'rating', array(
			'label'     => esc_html__( 'Rating', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 0,
			'max'       => 5,
			'step'      => 0.1,
			'default'   => 4.9,
			'condition' => array( 'trust_type' => array( 'auto', 'avatars' ) ),
		) );

		$this->add_control( 'rating_text', array(
			'label'     => esc_html__( 'Rating Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'from 200+ happy clients', 'teckform' ),
			'condition' => array( 'trust_type' => array( 'auto', 'avatars' ) ),
		) );

		$repeater = new Repeater();

		$repeater->add_control( 'number', array(
			'label'       => esc_html__( 'Number', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '100',
			'description' => esc_html__( 'Plain numbers count up when they appear.', 'teckform' ),
		) );

		$repeater->add_control( 'suffix', array(
			'label'   => esc_html__( 'After Number', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '+',
		) );

		$repeater->add_control( 'label', array(
			'label'   => esc_html__( 'Label', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Projects', 'teckform' ),
		) );

		$this->add_control( 'stats', array(
			'label'       => esc_html__( 'Numbers', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ number }}}{{{ suffix }}} {{{ label }}}',
			'default'     => array(
				array(
					'number' => '120',
					'suffix' => '+',
					'label'  => esc_html__( 'Projects delivered', 'teckform' ),
				),
				array(
					'number' => '98',
					'suffix' => '%',
					'label'  => esc_html__( 'Happy clients', 'teckform' ),
				),
				array(
					'number' => '10',
					'suffix' => '',
					'label'  => esc_html__( 'Years of experience', 'teckform' ),
				),
			),
			'condition'   => array( 'trust_type' => array( 'auto', 'stats' ) ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_box_style_controls() {
		$this->start_controls_section( 'section_style_box', array(
			'label' => esc_html__( 'Box', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'min_height', array(
			'label'      => esc_html__( 'Minimum Height', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'vh' ),
			'range'      => array(
				'px' => array(
					'min' => 200,
					'max' => 1200,
				),
				'vh' => array(
					'min' => 20,
					'max' => 100,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero' => 'min-height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em', 'vh' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'inner_width', array(
			'label'      => esc_html__( 'Content Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 400,
					'max' => 1600,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero' => '--teckform-hero-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'column_gap', array(
			'label'      => esc_html__( 'Space Between Text and Image', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 160,
				),
			),
			'condition'  => array( 'layout' => array( 'split', 'form', 'screenshot' ) ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero' => '--teckform-hero-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'box_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'pattern_color', array(
			'label'     => esc_html__( 'Pattern Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array(
				'layout!'  => 'background',
				'pattern!' => 'none',
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero' => '--teckform-hero-pattern: {{VALUE}};',
			),
		) );

		$this->add_control( 'overlay_color', array(
			'label'     => esc_html__( 'Overlay Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'layout' => 'background' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__overlay' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'overlay_opacity', array(
			'label'     => esc_html__( 'Overlay Opacity', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 0,
					'max'  => 1,
					'step' => 0.05,
				),
			),
			'condition' => array( 'layout' => 'background' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__overlay' => 'opacity: {{SIZE}};',
			),
		) );

		$this->add_control( 'dark_heading', array(
			'label'     => esc_html__( 'Dark Mode', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'dark_box_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-hero' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'dark_text', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-hero__title, html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-hero__description, html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-hero__content' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_badge_style_controls() {
		$this->start_controls_section( 'section_style_badge', array(
			'label'     => esc_html__( 'Badge', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_badge' => 'yes' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'badge_typography',
			'selector' => '{{WRAPPER}} .teckform-hero__badge',
		) );

		$this->add_control( 'badge_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__badge' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'badge_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__badge' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'badge_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__badge' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'badge_label_bg', array(
			'label'     => esc_html__( 'Tag Background', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__badge-label' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'badge_label_color', array(
			'label'     => esc_html__( 'Tag Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__badge-label' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_text_style_controls() {
		$this->start_controls_section( 'section_style_text', array(
			'label' => esc_html__( 'Headline and Text', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .teckform-hero__title',
		) );

		$this->add_control( 'title_color', array(
			'label'     => esc_html__( 'Headline Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'highlight_style', array(
			'label'     => esc_html__( 'Highlight Style', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'color',
			'separator' => 'before',
			'options'   => array(
				'color'     => esc_html__( 'Color only', 'teckform' ),
				'underline' => esc_html__( 'Underline', 'teckform' ),
				'marker'    => esc_html__( 'Marker', 'teckform' ),
			),
		) );

		$this->add_control( 'highlight_color', array(
			'label'     => esc_html__( 'Highlight Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero' => '--teckform-hero-highlight: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'description_typography',
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-hero__description',
		) );

		$this->add_control( 'description_color', array(
			'label'     => esc_html__( 'Description Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__description, {{WRAPPER}} .teckform-hero__benefits' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'description_width', array(
			'label'      => esc_html__( 'Description Max Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 200,
					'max' => 1000,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero__description' => 'max-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'benefit_icon_color', array(
			'label'     => esc_html__( 'Benefit Check Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'layout' => array( 'split', 'form' ) ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__benefits' => '--teckform-hero-check: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_button_style_controls() {
		$this->start_controls_section( 'section_style_buttons', array(
			'label' => esc_html__( 'Buttons', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'button_typography',
			'selector' => '{{WRAPPER}} .teckform-hero__button',
		) );

		$this->add_responsive_control( 'button_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'{{WRAPPER}} .teckform-hero__button' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_tab_primary', array( 'label' => esc_html__( 'Button 1', 'teckform' ) ) );

		$this->add_control( 'button_1_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--primary' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_1_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--primary' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_1_hover_color', array(
			'label'     => esc_html__( 'Hover Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--primary:hover' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_1_hover_bg', array(
			'label'     => esc_html__( 'Hover Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--primary:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_tab_secondary', array( 'label' => esc_html__( 'Button 2', 'teckform' ) ) );

		$this->add_control( 'button_2_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--secondary' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_2_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--secondary' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_2_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--secondary' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_2_hover_color', array(
			'label'     => esc_html__( 'Hover Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--secondary:hover' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_2_hover_bg', array(
			'label'     => esc_html__( 'Hover Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__button--secondary:hover' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_media_style_controls() {
		$this->start_controls_section( 'section_style_media', array(
			'label'     => esc_html__( 'Image and Form', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'layout' => array( 'split', 'screenshot', 'form' ) ),
		) );

		$this->add_responsive_control( 'media_width', array(
			'label'      => esc_html__( 'Max Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 200,
					'max' => 1400,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero__media, {{WRAPPER}} .teckform-hero__form' => 'max-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'media_radius', array(
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
				'{{WRAPPER}} .teckform-hero' => '--teckform-hero-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'media_shadow',
			'selector' => '{{WRAPPER}} .teckform-hero__frame, {{WRAPPER}} .teckform-hero__form',
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'media_border',
			'selector' => '{{WRAPPER}} .teckform-hero__frame, {{WRAPPER}} .teckform-hero__form',
		) );

		$this->add_control( 'form_bg', array(
			'label'     => esc_html__( 'Form Card Background', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'layout' => 'form' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__form' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'form_padding', array(
			'label'      => esc_html__( 'Form Card Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'condition'  => array( 'layout' => 'form' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero__form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_trust_style_controls() {
		$this->start_controls_section( 'section_style_trust', array(
			'label'     => esc_html__( 'Trust Row', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'trust_type!' => 'none' ),
		) );

		$this->add_responsive_control( 'trust_spacing', array(
			'label'      => esc_html__( 'Space Above', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 120,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero__trust' => 'margin-top: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'trust_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__trust' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'logo_height', array(
			'label'      => esc_html__( 'Logo Height', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 12,
					'max' => 80,
				),
			),
			'separator'  => 'before',
			'condition'  => array( 'trust_type' => array( 'auto', 'logos' ) ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-hero__logos img' => 'height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'logo_grayscale', array(
			'label'        => esc_html__( 'Gray Logos', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => array( 'trust_type' => array( 'auto', 'logos' ) ),
			'selectors'    => array(
				'{{WRAPPER}} .teckform-hero__logos img' => 'filter: grayscale(1); opacity: 0.6;',
			),
		) );

		$this->add_control( 'star_color', array(
			'label'     => esc_html__( 'Star Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'condition' => array( 'trust_type' => array( 'auto', 'avatars' ) ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__stars' => '--teckform-hero-star: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'stat_typography',
			'label'     => esc_html__( 'Number Typography', 'teckform' ),
			'separator' => 'before',
			'condition' => array( 'trust_type' => array( 'auto', 'stats' ) ),
			'selector'  => '{{WRAPPER}} .teckform-hero__stat-value',
		) );

		$this->add_control( 'stat_color', array(
			'label'     => esc_html__( 'Number Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'trust_type' => array( 'auto', 'stats' ) ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-hero__stat-value' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$layout   = array_key_exists( $settings['layout'], $this->get_layouts() ) ? $settings['layout'] : 'centered';

		$classes = array(
			'teckform-hero',
			'teckform-hero--' . $layout,
			'teckform-hero--highlight-' . sanitize_html_class( $settings['highlight_style'] ),
		);

		if ( in_array( $layout, array( 'split', 'form' ), true ) && 'left' === $settings['media_side'] ) {
			$classes[] = 'teckform-hero--media-left';
		}

		if ( 'yes' === $settings['entrance_animation'] ) {
			$classes[] = 'teckform-hero--animate';
		}

		if ( 'background' !== $layout && 'none' !== $settings['pattern'] ) {
			$classes[] = 'teckform-hero--pattern-' . sanitize_html_class( $settings['pattern'] );
		}
		?>
		<section class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php
			if ( 'background' === $layout ) {
				$this->render_background( $settings );
			}
			?>
			<div class="teckform-hero__inner">
				<div class="teckform-hero__content">
					<?php
					$this->render_badge( $settings );
					$this->render_title( $settings, $layout );

					if ( '' !== trim( $settings['description'] ) ) {
						echo '<p class="teckform-hero__description">' . wp_kses_post( $settings['description'] ) . '</p>';
					}

					if ( in_array( $layout, array( 'split', 'form' ), true ) ) {
						$this->render_benefits( $settings );
					}

					$this->render_buttons( $settings );
					$this->render_trust( $settings, $layout );
					?>
				</div>
				<?php
				if ( in_array( $layout, array( 'split', 'screenshot' ), true ) ) {
					$this->render_media( $settings );
				} elseif ( 'form' === $layout ) {
					$this->render_form( $settings );
				}
				?>
			</div>
			<?php if ( 'background' === $layout && 'yes' === $settings['scroll_indicator'] ) : ?>
				<button type="button" class="teckform-hero__scroll" aria-label="<?php esc_attr_e( 'Scroll down', 'teckform' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16.5l-7-7 1.4-1.4 5.6 5.6 5.6-5.6 1.4 1.4z"/></svg>
				</button>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Background image, optional video and the dark overlay.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_background( $settings ) {
		echo '<div class="teckform-hero__background" aria-hidden="true">';

		echo $this->get_image_html( $settings['bg_image'], 'teckform-hero__bg-image' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html().

		if ( ! empty( $settings['bg_video']['url'] ) ) {
			printf(
				'<video class="teckform-hero__bg-video" src="%s" autoplay muted loop playsinline></video>',
				esc_url( $settings['bg_video']['url'] )
			);
		}

		echo '<div class="teckform-hero__overlay"></div></div>';
	}

	/**
	 * @param array $settings Widget settings.
	 */
	private function render_badge( $settings ) {
		if ( 'yes' !== $settings['show_badge'] || ( '' === $settings['badge_text'] && '' === $settings['badge_label'] ) ) {
			return;
		}

		$tag = 'span';

		if ( ! empty( $settings['badge_link']['url'] ) ) {
			$tag = 'a';
			$this->add_link_attributes( 'badge', $settings['badge_link'] );
		}

		$this->add_render_attribute( 'badge', 'class', 'teckform-hero__badge' );
		?>
		<<?php echo esc_html( $tag ); ?> <?php $this->print_render_attribute_string( 'badge' ); ?>>
			<?php if ( '' !== $settings['badge_label'] ) : ?>
				<span class="teckform-hero__badge-label"><?php echo esc_html( $settings['badge_label'] ); ?></span>
			<?php endif; ?>
			<span class="teckform-hero__badge-text"><?php echo esc_html( $settings['badge_text'] ); ?></span>
			<?php if ( 'a' === $tag ) : ?>
				<svg class="teckform-hero__badge-arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.3 5.3L8 6.7l5.3 5.3L8 17.3l1.4 1.4 6.7-6.7z"/></svg>
			<?php endif; ?>
		</<?php echo esc_html( $tag ); ?>>
		<?php
	}

	/**
	 * Headline with optional highlighted (and rotating) words.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $layout   Current layout.
	 */
	private function render_title( $settings, $layout ) {
		$words = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $settings['title_words'] ) ), 'strlen' ) );
		$tag   = Utils::validate_html_tag( $settings['title_tag'] );

		if ( '' === trim( $settings['title_before'] ) && ! $words && '' === trim( $settings['title_after'] ) ) {
			return;
		}

		$animation = $settings['words_animation'];

		if ( 'auto' === $animation ) {
			$animation = 'animated' === $layout ? 'typing' : 'slide';
		}

		$interval = max( 1, (float) $settings['words_interval'] ) * 1000;

		echo '<' . esc_html( $tag ) . ' class="teckform-hero__title">';

		if ( '' !== trim( $settings['title_before'] ) ) {
			echo wp_kses_post( $settings['title_before'] ) . ' ';
		}

		if ( 1 === count( $words ) ) {
			echo '<span class="teckform-hero__highlight">' . esc_html( $words[0] ) . '</span>';
		} elseif ( $words ) {
			// Screen readers get the first word only; the animation is hidden from them.
			echo '<span class="screen-reader-text">' . esc_html( $words[0] ) . '</span>';

			if ( 'typing' === $animation ) {
				printf(
					'<span class="teckform-hero__highlight teckform-hero__typed" aria-hidden="true" data-words="%1$s" data-interval="%2$d">%3$s</span>',
					esc_attr( wp_json_encode( $words ) ),
					(int) $interval,
					esc_html( $words[0] )
				);
			} else {
				printf( '<span class="teckform-hero__highlight teckform-hero__words" aria-hidden="true" data-interval="%d">', (int) $interval );

				foreach ( $words as $i => $word ) {
					printf( '<span class="teckform-hero__word%s">%s</span>', 0 === $i ? ' is-active' : '', esc_html( $word ) );
				}

				echo '</span>';
			}
		}

		if ( '' !== trim( $settings['title_after'] ) ) {
			echo ' ' . wp_kses_post( $settings['title_after'] );
		}

		echo '</' . esc_html( $tag ) . '>';
	}

	/**
	 * @param array $settings Widget settings.
	 */
	private function render_benefits( $settings ) {
		if ( empty( $settings['benefits'] ) ) {
			return;
		}

		echo '<ul class="teckform-hero__benefits">';

		foreach ( $settings['benefits'] as $benefit ) {
			if ( '' === trim( $benefit['text'] ) ) {
				continue;
			}

			echo '<li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 16.2l-3.5-3.5L4 14.1l5 5 11-11-1.4-1.4z"/></svg>' . esc_html( $benefit['text'] ) . '</li>';
		}

		echo '</ul>';
	}

	/**
	 * @param array $settings Widget settings.
	 */
	private function render_buttons( $settings ) {
		$buttons = '';

		foreach ( array( 1 => 'primary', 2 => 'secondary' ) as $n => $variant ) {
			$text = $settings[ "button_{$n}_text" ];

			if ( '' === trim( $text ) ) {
				continue;
			}

			$key = "button_{$n}";

			$this->add_render_attribute( $key, 'class', array( 'teckform-hero__button', 'teckform-hero__button--' . $variant ) );

			if ( ! empty( $settings[ "button_{$n}_link" ]['url'] ) ) {
				$this->add_link_attributes( $key, $settings[ "button_{$n}_link" ] );
			}

			$icon = '';

			if ( ! empty( $settings[ "button_{$n}_icon" ]['value'] ) ) {
				ob_start();
				Icons_Manager::render_icon( $settings[ "button_{$n}_icon" ], array( 'aria-hidden' => 'true' ) );
				$icon = '<span class="teckform-hero__button-icon">' . ob_get_clean() . '</span>';
			}

			$buttons .= '<a ' . $this->get_render_attribute_string( $key ) . '><span>' . esc_html( $text ) . '</span>' . $icon . '</a>';
		}

		if ( $buttons ) {
			echo '<div class="teckform-hero__buttons">' . $buttons . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
		}
	}

	/**
	 * Logos, customer photos with rating, or numbers.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $layout   Current layout.
	 */
	private function render_trust( $settings, $layout ) {
		$type = $settings['trust_type'];

		if ( 'auto' === $type ) {
			$defaults = array(
				'centered'   => 'logos',
				'split'      => 'avatars',
				'screenshot' => 'logos',
				'animated'   => 'stats',
				'form'       => 'avatars',
				'background' => 'none',
			);
			$type     = $defaults[ $layout ];
		}

		if ( 'logos' === $type ) {
			$this->render_logos( $settings );
		} elseif ( 'avatars' === $type ) {
			$this->render_avatars( $settings );
		} elseif ( 'stats' === $type ) {
			$this->render_stats( $settings );
		}
	}

	/**
	 * @param array $settings Widget settings.
	 */
	private function render_logos( $settings ) {
		$logos = array_filter( (array) $settings['logos'] );

		if ( ! $logos && ! Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}

		echo '<div class="teckform-hero__trust teckform-hero__logos">';

		if ( '' !== $settings['trust_title'] ) {
			echo '<p class="teckform-hero__trust-title">' . esc_html( $settings['trust_title'] ) . '</p>';
		}

		echo '<div class="teckform-hero__logo-row">';

		if ( $logos ) {
			foreach ( $logos as $logo ) {
				echo $this->get_image_html( $logo, '', 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html().
			}
		} else {
			// Placeholder so the layout is visible in the editor until logos are added.
			for ( $i = 1; $i <= 5; $i++ ) {
				echo '<span class="teckform-hero__logo-placeholder">' . esc_html__( 'Logo', 'teckform' ) . '</span>';
			}
		}

		echo '</div></div>';
	}

	/**
	 * @param array $settings Widget settings.
	 */
	private function render_avatars( $settings ) {
		$avatars = array_filter( (array) $settings['avatars'] );
		$rating  = min( 5, max( 0, (float) $settings['rating'] ) );

		echo '<div class="teckform-hero__trust teckform-hero__avatars">';

		if ( $avatars ) {
			echo '<div class="teckform-hero__avatar-row">';

			foreach ( array_slice( $avatars, 0, 5 ) as $avatar ) {
				echo $this->get_image_html( $avatar, '', 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html().
			}

			echo '</div>';
		} elseif ( Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="teckform-hero__avatar-row"><span></span><span></span><span></span><span></span></div>';
		}

		echo '<div class="teckform-hero__rating">';

		if ( $rating > 0 ) {
			// Stars are filled by width so a 4.9 rating shows 4.9 stars.
			printf(
				'<span class="teckform-hero__stars" role="img" aria-label="%1$s"><span class="teckform-hero__stars-fill" style="width:%2$s%%"></span></span>',
				/* translators: %s: rating out of 5. */
				esc_attr( sprintf( __( 'Rated %s out of 5', 'teckform' ), $rating ) ),
				esc_attr( round( $rating / 5 * 100, 1 ) )
			);

			echo '<strong class="teckform-hero__rating-value">' . esc_html( number_format_i18n( $rating, 1 ) ) . '</strong>';
		}

		if ( '' !== $settings['rating_text'] ) {
			echo '<span class="teckform-hero__rating-text">' . esc_html( $settings['rating_text'] ) . '</span>';
		}

		echo '</div></div>';
	}

	/**
	 * @param array $settings Widget settings.
	 */
	private function render_stats( $settings ) {
		if ( empty( $settings['stats'] ) ) {
			return;
		}

		echo '<div class="teckform-hero__trust teckform-hero__stats">';

		foreach ( $settings['stats'] as $stat ) {
			$number = trim( (string) $stat['number'] );
			$count  = is_numeric( $number ) ? ' data-count="' . esc_attr( $number ) . '"' : '';

			echo '<div class="teckform-hero__stat">';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $count is escaped above.
			echo '<span class="teckform-hero__stat-value"><span class="teckform-hero__stat-number"' . $count . '>' . esc_html( $number ) . '</span>' . esc_html( $stat['suffix'] ) . '</span>';
			echo '<span class="teckform-hero__stat-label">' . esc_html( $stat['label'] ) . '</span>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Image or video beside (split) or below (screenshot) the text.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_media( $settings ) {
		if ( 'video' === $settings['media_type'] && ! empty( $settings['video']['url'] ) ) {
			$media = sprintf( '<video src="%s" autoplay muted loop playsinline></video>', esc_url( $settings['video']['url'] ) );
		} else {
			$media = $this->get_image_html( $settings['image'], '', 'full', true );
		}

		if ( ! $media ) {
			return;
		}

		$frame = 'yes' === $settings['browser_frame'];
		?>
		<div class="teckform-hero__media">
			<div class="teckform-hero__frame<?php echo $frame ? ' teckform-hero__frame--browser' : ''; ?>">
				<?php if ( $frame ) : ?>
					<div class="teckform-hero__frame-bar" aria-hidden="true"><span></span><span></span><span></span></div>
				<?php endif; ?>
				<?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Form card: a form plugin's shortcode, or a call-to-action button.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_form( $settings ) {
		?>
		<div class="teckform-hero__form">
			<?php if ( '' !== $settings['form_title'] ) : ?>
				<h3 class="teckform-hero__form-title"><?php echo esc_html( $settings['form_title'] ); ?></h3>
			<?php endif; ?>
			<?php if ( '' !== $settings['form_text'] ) : ?>
				<p class="teckform-hero__form-text"><?php echo esc_html( $settings['form_text'] ); ?></p>
			<?php endif; ?>
			<?php
			if ( '' !== trim( $settings['form_shortcode'] ) ) {
				// Same handling as Elementor's own Shortcode widget.
				echo do_shortcode( shortcode_unautop( $settings['form_shortcode'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode output.
			} elseif ( '' !== $settings['form_button_text'] ) {
				$this->add_render_attribute( 'form_button', 'class', array( 'teckform-hero__button', 'teckform-hero__button--primary', 'teckform-hero__form-button' ) );

				if ( ! empty( $settings['form_button_link']['url'] ) ) {
					$this->add_link_attributes( 'form_button', $settings['form_button_link'] );
				}

				echo '<a ' . $this->get_render_attribute_string( 'form_button' ) . '>' . esc_html( $settings['form_button_text'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes escaped by Elementor.
			}
			?>
		</div>
		<?php
	}

	/**
	 * <img> markup for a media or gallery value.
	 *
	 * @param array  $media    Value with 'id' and/or 'url'.
	 * @param string $class    Extra class.
	 * @param string $size     Image size.
	 * @param bool   $priority Whether this is the main image (loads first).
	 * @return string
	 */
	private function get_image_html( $media, $class = '', $size = 'full', $priority = false ) {
		$attrs = array( 'class' => $class );

		if ( $priority || 'teckform-hero__bg-image' === $class ) {
			$attrs['loading']       = 'eager';
			$attrs['fetchpriority'] = 'high';
		}

		if ( ! empty( $media['id'] ) ) {
			$html = wp_get_attachment_image( absint( $media['id'] ), $size, false, array_filter( $attrs ) );

			if ( $html ) {
				return $html;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img src="' . esc_url( $media['url'] ) . '" alt=""' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>';
		}

		return '';
	}
}
