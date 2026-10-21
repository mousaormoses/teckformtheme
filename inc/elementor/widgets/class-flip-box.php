<?php
/**
 * Flip Box widget: a card that turns over to show a second side.
 *
 * The front has an icon or image, a title and text; the back has a title,
 * text and a button. It turns on hover, on keyboard focus, and on tap on touch
 * screens (assets/js/flip-box.js). Effects: 3D flip, slide or fade.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Flip_Box extends Widget_Base {

	public function get_name() {
		return 'teckform-flip-box';
	}

	public function get_title() {
		return esc_html__( 'Flip Box', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-flip-box';
	}

	public function get_categories() {
		return array( 'teckform' );
	}

	public function get_keywords() {
		return array( 'flip', 'box', 'card', 'hover', 'service', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-flip-box' );
	}

	public function get_script_depends() {
		return array( 'teckform-flip-box' );
	}

	protected function register_controls() {
		$this->register_front_controls();
		$this->register_back_controls();
		$this->register_settings_controls();

		$this->register_box_style_controls();
		$this->register_side_style_controls( 'front' );
		$this->register_side_style_controls( 'back' );
		$this->register_button_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_front_controls() {
		$this->start_controls_section( 'section_front', array(
			'label' => esc_html__( 'Front', 'teckform' ),
		) );

		$this->add_control( 'front_graphic', array(
			'label'   => esc_html__( 'Graphic', 'teckform' ),
			'type'    => Controls_Manager::CHOOSE,
			'default' => 'icon',
			'toggle'  => false,
			'options' => array(
				'none'  => array(
					'title' => esc_html__( 'None', 'teckform' ),
					'icon'  => 'eicon-ban',
				),
				'icon'  => array(
					'title' => esc_html__( 'Icon', 'teckform' ),
					'icon'  => 'eicon-star',
				),
				'image' => array(
					'title' => esc_html__( 'Image', 'teckform' ),
					'icon'  => 'eicon-image',
				),
			),
		) );

		$this->add_control( 'front_icon', array(
			'label'     => esc_html__( 'Icon', 'teckform' ),
			'type'      => Controls_Manager::ICONS,
			'default'   => array(
				'value'   => 'fas fa-pen-nib',
				'library' => 'fa-solid',
			),
			'condition' => array( 'front_graphic' => 'icon' ),
		) );

		$this->add_control( 'front_image', array(
			'label'     => esc_html__( 'Image', 'teckform' ),
			'type'      => Controls_Manager::MEDIA,
			'default'   => array( 'url' => Utils::get_placeholder_image_src() ),
			'condition' => array( 'front_graphic' => 'image' ),
		) );

		$this->add_control( 'front_title', array(
			'label'       => esc_html__( 'Title', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'Brand Design', 'teckform' ),
			'dynamic'     => array( 'active' => true ),
		) );

		$this->add_control( 'front_text', array(
			'label'   => esc_html__( 'Text', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 3,
			'default' => esc_html__( 'Logos and visual identities people remember.', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$this->add_control( 'front_bg_image', array(
			'label'       => esc_html__( 'Background Image', 'teckform' ),
			'type'        => Controls_Manager::MEDIA,
			'separator'   => 'before',
			'description' => esc_html__( 'Optional. Colors and an overlay are in the Style tab, under Front.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	private function register_back_controls() {
		$this->start_controls_section( 'section_back', array(
			'label' => esc_html__( 'Back', 'teckform' ),
		) );

		$this->add_control( 'back_title', array(
			'label'       => esc_html__( 'Title', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'From idea to identity', 'teckform' ),
			'dynamic'     => array( 'active' => true ),
		) );

		$this->add_control( 'back_text', array(
			'label'   => esc_html__( 'Text', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => esc_html__( 'Research, logo, colors, type and a brand guide your whole team can use.', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$this->add_control( 'back_button_text', array(
			'label'       => esc_html__( 'Button Text', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Learn more', 'teckform' ),
			'description' => esc_html__( 'Leave empty to hide the button.', 'teckform' ),
			'separator'   => 'before',
		) );

		$this->add_control( 'back_link', array(
			'label'     => esc_html__( 'Button Link', 'teckform' ),
			'type'      => Controls_Manager::URL,
			'dynamic'   => array( 'active' => true ),
			'default'   => array( 'url' => '#' ),
			'condition' => array( 'back_button_text!' => '' ),
		) );

		$this->add_control( 'back_bg_image', array(
			'label'       => esc_html__( 'Background Image', 'teckform' ),
			'type'        => Controls_Manager::MEDIA,
			'separator'   => 'before',
			'description' => esc_html__( 'Optional. Colors and an overlay are in the Style tab, under Back.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	private function register_settings_controls() {
		$this->start_controls_section( 'section_settings', array(
			'label' => esc_html__( 'Flip Settings', 'teckform' ),
		) );

		$this->add_control( 'effect', array(
			'label'   => esc_html__( 'Effect', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'flip',
			'options' => array(
				'flip'  => esc_html__( '3D flip', 'teckform' ),
				'slide' => esc_html__( 'Slide', 'teckform' ),
				'fade'  => esc_html__( 'Fade', 'teckform' ),
			),
		) );

		$this->add_control( 'direction', array(
			'label'     => esc_html__( 'Direction', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'left',
			'toggle'    => false,
			'options'   => array(
				'left'  => array(
					'title' => esc_html__( 'Left', 'teckform' ),
					'icon'  => 'eicon-arrow-left',
				),
				'right' => array(
					'title' => esc_html__( 'Right', 'teckform' ),
					'icon'  => 'eicon-arrow-right',
				),
				'up'    => array(
					'title' => esc_html__( 'Up', 'teckform' ),
					'icon'  => 'eicon-arrow-up',
				),
				'down'  => array(
					'title' => esc_html__( 'Down', 'teckform' ),
					'icon'  => 'eicon-arrow-down',
				),
			),
			'condition' => array( 'effect' => array( 'flip', 'slide' ) ),
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
				'h6'  => 'H6',
				'div' => 'div',
				'p'   => 'p',
			),
		) );

		$this->add_control( 'settings_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'The box turns on mouse hover, when reached with the keyboard, and on tap on touch screens.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
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

		$this->add_responsive_control( 'height', array(
			'label'      => esc_html__( 'Height', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'vh', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 120,
					'max' => 1000,
				),
				'vh' => array(
					'min' => 10,
					'max' => 100,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-flip-box' => 'height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'box_radius', array(
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
				'{{WRAPPER}} .teckform-flip-box' => '--teckform-flip-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'box_border',
			'selector' => '{{WRAPPER}} .teckform-flip-box__side',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'box_shadow',
			'selector' => '{{WRAPPER}} .teckform-flip-box__side',
		) );

		$this->add_control( 'duration', array(
			'label'      => esc_html__( 'Animation Duration (ms)', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min'  => 100,
					'max'  => 2000,
					'step' => 50,
				),
			),
			'separator'  => 'before',
			'selectors'  => array(
				'{{WRAPPER}} .teckform-flip-box' => '--teckform-flip-duration: {{SIZE}}ms;',
			),
		) );

		$this->end_controls_section();
	}

	/**
	 * Style section for one side. Both sides have the same controls,
	 * prefixed front_ or back_.
	 *
	 * @param string $side 'front' or 'back'.
	 */
	private function register_side_style_controls( $side ) {
		$is_front = 'front' === $side;
		$wrapper  = '{{WRAPPER}} .teckform-flip-box__' . $side;

		$this->start_controls_section( "section_style_{$side}", array(
			'label' => $is_front ? esc_html__( 'Front', 'teckform' ) : esc_html__( 'Back', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( "{$side}_bg", array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				$wrapper => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( "{$side}_overlay", array(
			'label'       => esc_html__( 'Image Overlay Color', 'teckform' ),
			'type'        => Controls_Manager::COLOR,
			'description' => esc_html__( 'Shown over the background image. Use a see-through color.', 'teckform' ),
			'condition'   => array( "{$side}_bg_image[url]!" => '' ),
			'selectors'   => array(
				$wrapper . ' .teckform-flip-box__overlay' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( "{$side}_padding", array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em', '%' ),
			'selectors'  => array(
				$wrapper . ' .teckform-flip-box__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( "{$side}_align", array(
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
				'start'  => 'text-align: start; align-items: flex-start;',
				'center' => 'text-align: center; align-items: center;',
				'end'    => 'text-align: end; align-items: flex-end;',
			),
			'selectors'            => array(
				$wrapper . ' .teckform-flip-box__content' => '{{VALUE}}',
			),
		) );

		$this->add_responsive_control( "{$side}_valign", array(
			'label'                => esc_html__( 'Vertical Position', 'teckform' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => array(
				'top'    => array(
					'title' => esc_html__( 'Top', 'teckform' ),
					'icon'  => 'eicon-v-align-top',
				),
				'middle' => array(
					'title' => esc_html__( 'Middle', 'teckform' ),
					'icon'  => 'eicon-v-align-middle',
				),
				'bottom' => array(
					'title' => esc_html__( 'Bottom', 'teckform' ),
					'icon'  => 'eicon-v-align-bottom',
				),
			),
			'selectors_dictionary' => array(
				'top'    => 'flex-start',
				'middle' => 'center',
				'bottom' => 'flex-end',
			),
			'selectors'            => array(
				$wrapper . ' .teckform-flip-box__content' => 'justify-content: {{VALUE}};',
			),
		) );

		if ( $is_front ) {
			$this->add_control( 'front_graphic_heading', array(
				'label'     => esc_html__( 'Icon or Image', 'teckform' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'front_graphic!' => 'none' ),
			) );

			$this->add_control( 'front_icon_color', array(
				'label'     => esc_html__( 'Icon Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'front_graphic' => 'icon' ),
				'selectors' => array(
					'{{WRAPPER}} .teckform-flip-box__icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .teckform-flip-box__icon svg' => 'fill: {{VALUE}};',
				),
			) );

			$this->add_control( 'front_icon_bg', array(
				'label'     => esc_html__( 'Icon Background', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'front_graphic' => 'icon' ),
				'selectors' => array(
					'{{WRAPPER}} .teckform-flip-box__icon' => 'background-color: {{VALUE}};',
				),
			) );

			$this->add_responsive_control( 'front_icon_size', array(
				'label'      => esc_html__( 'Icon Size', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 160,
					),
				),
				'condition'  => array( 'front_graphic' => 'icon' ),
				'selectors'  => array(
					'{{WRAPPER}} .teckform-flip-box__icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			) );

			$this->add_responsive_control( 'front_icon_padding', array(
				'label'      => esc_html__( 'Icon Padding', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'condition'  => array( 'front_graphic' => 'icon' ),
				'selectors'  => array(
					'{{WRAPPER}} .teckform-flip-box__icon' => 'padding: {{SIZE}}{{UNIT}};',
				),
			) );

			$this->add_control( 'front_icon_radius', array(
				'label'      => esc_html__( 'Icon Border Radius', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'condition'  => array( 'front_graphic' => 'icon' ),
				'selectors'  => array(
					'{{WRAPPER}} .teckform-flip-box__icon' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			) );

			$this->add_responsive_control( 'front_image_width', array(
				'label'      => esc_html__( 'Image Width', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 600,
					),
				),
				'condition'  => array( 'front_graphic' => 'image' ),
				'selectors'  => array(
					'{{WRAPPER}} .teckform-flip-box__image' => 'width: {{SIZE}}{{UNIT}};',
				),
			) );

			$this->add_control( 'front_image_radius', array(
				'label'      => esc_html__( 'Image Border Radius', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'condition'  => array( 'front_graphic' => 'image' ),
				'selectors'  => array(
					'{{WRAPPER}} .teckform-flip-box__image' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			) );

			$this->add_responsive_control( 'front_graphic_spacing', array(
				'label'      => esc_html__( 'Space Below', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'condition'  => array( 'front_graphic!' => 'none' ),
				'selectors'  => array(
					'{{WRAPPER}} .teckform-flip-box__graphic' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			) );
		}

		$this->add_control( "{$side}_title_heading", array(
			'label'     => esc_html__( 'Title', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => "{$side}_title_typography",
			'selector' => $wrapper . ' .teckform-flip-box__title',
		) );

		$this->add_control( "{$side}_title_color", array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				$wrapper . ' .teckform-flip-box__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( "{$side}_title_spacing", array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				$wrapper . ' .teckform-flip-box__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( "{$side}_text_heading", array(
			'label'     => esc_html__( 'Text', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => "{$side}_text_typography",
			'selector' => $wrapper . ' .teckform-flip-box__text',
		) );

		$this->add_control( "{$side}_text_color", array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				$wrapper . ' .teckform-flip-box__text' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_button_style_controls() {
		$this->start_controls_section( 'section_style_button', array(
			'label'     => esc_html__( 'Back Button', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'back_button_text!' => '' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'button_typography',
			'selector' => '{{WRAPPER}} .teckform-flip-box__button',
		) );

		$this->add_responsive_control( 'button_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-flip-box__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'{{WRAPPER}} .teckform-flip-box__button' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'button_spacing', array(
			'label'      => esc_html__( 'Space Above', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-flip-box__button' => 'margin-top: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'button_border',
			'selector' => '{{WRAPPER}} .teckform-flip-box__button',
		) );

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'button_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-flip-box__button' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-flip-box__button' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'button_shadow',
			'selector' => '{{WRAPPER}} .teckform-flip-box__button',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'button_hover_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-flip-box__button:hover, {{WRAPPER}} .teckform-flip-box__button:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-flip-box__button:hover, {{WRAPPER}} .teckform-flip-box__button:focus-visible' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-flip-box__button:hover, {{WRAPPER}} .teckform-flip-box__button:focus-visible' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'button_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-flip-box__button:hover',
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$effect    = in_array( $settings['effect'], array( 'flip', 'slide', 'fade' ), true ) ? $settings['effect'] : 'flip';
		$direction = in_array( $settings['direction'], array( 'left', 'right', 'up', 'down' ), true ) ? $settings['direction'] : 'left';
		$button    = '' !== trim( (string) $settings['back_button_text'] );

		$this->add_render_attribute( 'wrapper', 'class', array(
			'teckform-flip-box',
			'teckform-flip-box--' . $effect,
			'teckform-flip-box--' . $direction,
		) );

		// Without a button there's nothing inside to tab to, so the box itself takes keyboard focus.
		if ( ! $button ) {
			$this->add_render_attribute( 'wrapper', array(
				'tabindex' => '0',
				'role'     => 'group',
			) );

			if ( '' !== trim( (string) $settings['front_title'] ) ) {
				$this->add_render_attribute( 'wrapper', 'aria-label', wp_strip_all_tags( $settings['front_title'] ) );
			}
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="teckform-flip-box__inner">
				<div class="teckform-flip-box__side teckform-flip-box__front">
					<?php $this->render_background( $settings['front_bg_image'] ); ?>
					<div class="teckform-flip-box__content">
						<?php
						$this->render_graphic( $settings );
						$this->render_text( $settings, 'front' );
						?>
					</div>
				</div>
				<div class="teckform-flip-box__side teckform-flip-box__back">
					<?php $this->render_background( $settings['back_bg_image'] ); ?>
					<div class="teckform-flip-box__content">
						<?php
						$this->render_text( $settings, 'back' );

						if ( $button ) {
							$this->add_render_attribute( 'button', 'class', 'teckform-flip-box__button' );

							if ( ! empty( $settings['back_link']['url'] ) ) {
								$this->add_link_attributes( 'button', $settings['back_link'] );
								$tag = 'a';
							} else {
								$this->add_render_attribute( 'button', 'tabindex', '0' );
								$tag = 'span';
							}

							echo '<' . esc_html( $tag ) . ' ' . $this->get_render_attribute_string( 'button' ) . '>' . esc_html( $settings['back_button_text'] ) . '</' . esc_html( $tag ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes escaped by Elementor.
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Background image and its overlay for one side.
	 *
	 * @param array $media Media value.
	 */
	private function render_background( $media ) {
		$image = $this->get_image_html( $media, 'teckform-flip-box__bg', 'large', true );

		if ( ! $image ) {
			return;
		}

		echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html().
		echo '<span class="teckform-flip-box__overlay" aria-hidden="true"></span>';
	}

	/**
	 * Icon or image on the front.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_graphic( $settings ) {
		if ( 'icon' === $settings['front_graphic'] && ! empty( $settings['front_icon']['value'] ) ) {
			echo '<span class="teckform-flip-box__graphic teckform-flip-box__icon">';
			Icons_Manager::render_icon( $settings['front_icon'], array( 'aria-hidden' => 'true' ) );
			echo '</span>';
		} elseif ( 'image' === $settings['front_graphic'] ) {
			$image = $this->get_image_html( $settings['front_image'], 'teckform-flip-box__graphic teckform-flip-box__image', 'medium_large' );

			echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html().
		}
	}

	/**
	 * Title and text of one side.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $side     'front' or 'back'.
	 */
	private function render_text( $settings, $side ) {
		$tag = Utils::validate_html_tag( $settings['title_tag'] );

		if ( '' !== trim( (string) $settings[ "{$side}_title" ] ) ) {
			echo '<' . esc_html( $tag ) . ' class="teckform-flip-box__title">' . wp_kses_post( $settings[ "{$side}_title" ] ) . '</' . esc_html( $tag ) . '>';
		}

		if ( '' !== trim( (string) $settings[ "{$side}_text" ] ) ) {
			echo '<p class="teckform-flip-box__text">' . wp_kses_post( $settings[ "{$side}_text" ] ) . '</p>';
		}
	}

	/**
	 * <img> markup for a media value.
	 *
	 * @param array  $media      Value with 'id' and/or 'url'.
	 * @param string $class      Class for the image.
	 * @param string $size       Image size.
	 * @param bool   $decorative Hide it from screen readers.
	 * @return string
	 */
	private function get_image_html( $media, $class, $size = 'full', $decorative = false ) {
		$attrs = array( 'class' => $class );

		if ( $decorative ) {
			$attrs['alt']         = '';
			$attrs['aria-hidden'] = 'true';
		}

		if ( ! empty( $media['id'] ) ) {
			$html = wp_get_attachment_image( absint( $media['id'] ), $size, false, $attrs );

			if ( $html ) {
				return $html;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img src="' . esc_url( $media['url'] ) . '" alt="" class="' . esc_attr( $class ) . '"' . ( $decorative ? ' aria-hidden="true"' : '' ) . '>';
		}

		return '';
	}
}
