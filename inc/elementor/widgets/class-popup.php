<?php
/**
 * Popup widget: a dialog with content from a Section template or the text editor.
 *
 * It opens from its own button, any link to #popup-id, or automatically (after a
 * delay, after scrolling, or on exit intent), remembering for N days that it was
 * shown. In the Elementor editor the dialog is shown inline so it can be edited.
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
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popup extends Widget_Base {

	public function get_name() {
		return 'teckform-popup';
	}

	public function get_title() {
		return esc_html__( 'Popup', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-lightbox-expand';
	}

	public function get_categories() {
		return array( 'teckform' );
	}

	public function get_keywords() {
		return array( 'popup', 'modal', 'dialog', 'lightbox', 'newsletter', 'exit intent', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-popup' );
	}

	public function get_script_depends() {
		return array( 'teckform-popup' );
	}

	/**
	 * Position keys and labels.
	 *
	 * @return array<string, string>
	 */
	private function get_positions() {
		return array(
			'center'     => esc_html__( 'Center (modal)', 'teckform' ),
			'slide-in'   => esc_html__( 'Slide-in at the bottom corner', 'teckform' ),
			'fullscreen' => esc_html__( 'Full screen', 'teckform' ),
		);
	}

	/**
	 * Animation keys and labels.
	 *
	 * @return array<string, string>
	 */
	private function get_animations() {
		return array(
			'fade'     => esc_html__( 'Fade', 'teckform' ),
			'zoom'     => esc_html__( 'Zoom', 'teckform' ),
			'slide-up' => esc_html__( 'Slide up', 'teckform' ),
		);
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_trigger_controls();
		$this->register_popup_controls();

		$this->register_button_style_controls();
		$this->register_popup_style_controls();
		$this->register_overlay_style_controls();
		$this->register_close_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_content_controls() {
		$this->start_controls_section( 'section_content', array(
			'label' => esc_html__( 'Content', 'teckform' ),
		) );

		$this->add_control( 'popup_title', array(
			'label'       => esc_html__( 'Title', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'Stay in the loop', 'teckform' ),
			'dynamic'     => array( 'active' => true ),
		) );

		$this->add_control( 'show_title', array(
			'label'        => esc_html__( 'Show Title', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'When hidden, screen readers still announce it as the popup name.', 'teckform' ),
		) );

		$this->add_control( 'content_source', array(
			'label'     => esc_html__( 'Content From', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'editor',
			'toggle'    => false,
			'separator' => 'before',
			'options'   => array(
				'editor'   => array(
					'title' => esc_html__( 'Text editor', 'teckform' ),
					'icon'  => 'eicon-text',
				),
				'template' => array(
					'title' => esc_html__( 'Section template', 'teckform' ),
					'icon'  => 'eicon-section',
				),
			),
		) );

		$templates = function_exists( 'teckform_get_section_templates' ) ? teckform_get_section_templates() : array();

		$this->add_control( 'template_id', array(
			'label'     => esc_html__( 'Section Template', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '',
			'options'   => array( '' => esc_html__( '— Choose a template —', 'teckform' ) ) + $templates,
			'condition' => array( 'content_source' => 'template' ),
		) );

		$this->add_control( 'template_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => sprintf(
				/* translators: 1: opening link tag, 2: closing link tag. */
				esc_html__( 'Design the content as a template of the type Section under %1$sHeader & Footer%2$s, then choose it here. Save this page and reload the editor to see a new template in the list.', 'teckform' ),
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=teckform_template' ) ) . '" target="_blank">',
				'</a>'
			),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'content_source' => 'template' ),
		) );

		$this->add_control( 'editor_content', array(
			'label'     => esc_html__( 'Content', 'teckform' ),
			'type'      => Controls_Manager::WYSIWYG,
			'default'   => '<p>' . esc_html__( 'Get our best tips and offers once a month. No spam, and you can unsubscribe at any time.', 'teckform' ) . '</p>',
			'dynamic'   => array( 'active' => true ),
			'condition' => array( 'content_source' => 'editor' ),
		) );

		$this->add_control( 'close_link_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Tip: a link to #close-popup inside the content closes the popup.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
		) );

		$this->end_controls_section();
	}

	private function register_trigger_controls() {
		$this->start_controls_section( 'section_trigger', array(
			'label' => esc_html__( 'Open Popup', 'teckform' ),
		) );

		$this->add_control( 'popup_id', array(
			'label'       => esc_html__( 'Popup ID', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => 'newsletter',
			'description' => esc_html__( 'Any link on the page to #popup-id opens the popup. Leave empty to use popup- plus the widget ID (shown above the preview in the editor). Letters, numbers and dashes only.', 'teckform' ),
		) );

		$this->add_control( 'show_button', array(
			'label'        => esc_html__( 'Show Button', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
			'description'  => esc_html__( 'Turn off when the popup opens from links or automatically.', 'teckform' ),
		) );

		$this->add_control( 'button_text', array(
			'label'     => esc_html__( 'Button Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Subscribe', 'teckform' ),
			'dynamic'   => array( 'active' => true ),
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'button_icon', array(
			'label'     => esc_html__( 'Button Icon', 'teckform' ),
			'type'      => Controls_Manager::ICONS,
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'button_icon_position', array(
			'label'     => esc_html__( 'Icon Position', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'after',
			'toggle'    => false,
			'options'   => array(
				'before' => array(
					'title' => esc_html__( 'Before', 'teckform' ),
					'icon'  => 'eicon-h-align-left',
				),
				'after'  => array(
					'title' => esc_html__( 'After', 'teckform' ),
					'icon'  => 'eicon-h-align-right',
				),
			),
			'condition' => array(
				'show_button'         => 'yes',
				'button_icon[value]!' => '',
			),
		) );

		$this->add_responsive_control( 'button_align', array(
			'label'                => esc_html__( 'Button Alignment', 'teckform' ),
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => array(
				'start'   => array(
					'title' => esc_html__( 'Start', 'teckform' ),
					'icon'  => 'eicon-text-align-left',
				),
				'center'  => array(
					'title' => esc_html__( 'Center', 'teckform' ),
					'icon'  => 'eicon-text-align-center',
				),
				'end'     => array(
					'title' => esc_html__( 'End', 'teckform' ),
					'icon'  => 'eicon-text-align-right',
				),
				'stretch' => array(
					'title' => esc_html__( 'Full width', 'teckform' ),
					'icon'  => 'eicon-text-align-justify',
				),
			),
			'selectors_dictionary' => array(
				'start'   => 'justify-content: flex-start; --teckform-popup-button-grow: 0;',
				'center'  => 'justify-content: center; --teckform-popup-button-grow: 0;',
				'end'     => 'justify-content: flex-end; --teckform-popup-button-grow: 0;',
				'stretch' => 'justify-content: stretch; --teckform-popup-button-grow: 1;',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-popup__trigger' => '{{VALUE}}',
			),
			'condition'            => array( 'show_button' => 'yes' ),
		) );

		$this->add_control( 'auto_heading', array(
			'label'     => esc_html__( 'Open Automatically', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'open_on_load', array(
			'label'        => esc_html__( 'After the Page Loads', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
		) );

		$this->add_control( 'load_delay', array(
			'label'     => esc_html__( 'Delay (seconds)', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 0,
			'max'       => 300,
			'step'      => 0.5,
			'default'   => 5,
			'condition' => array( 'open_on_load' => 'yes' ),
		) );

		$this->add_control( 'open_on_scroll', array(
			'label'        => esc_html__( 'After Scrolling', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
		) );

		$this->add_control( 'scroll_percent', array(
			'label'      => esc_html__( 'Scrolled Down (%)', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( '%' ),
			'range'      => array(
				'%' => array(
					'min' => 1,
					'max' => 100,
				),
			),
			'default'    => array(
				'unit' => '%',
				'size' => 50,
			),
			'condition'  => array( 'open_on_scroll' => 'yes' ),
		) );

		$this->add_control( 'open_on_exit', array(
			'label'        => esc_html__( 'On Exit Intent', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'description'  => esc_html__( 'When the mouse leaves through the top of the window, as if to close the tab. Computers only.', 'teckform' ),
		) );

		$this->add_control( 'show_again_days', array(
			'label'       => esc_html__( 'Show Again After (days)', 'teckform' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 365,
			'step'        => 1,
			'default'     => 7,
			'description' => esc_html__( 'After opening automatically, wait this many days before doing it again for the same visitor. 0 shows it on every visit. The button and links always work.', 'teckform' ),
			'conditions'  => array(
				'relation' => 'or',
				'terms'    => array(
					array(
						'name'     => 'open_on_load',
						'operator' => '===',
						'value'    => 'yes',
					),
					array(
						'name'     => 'open_on_scroll',
						'operator' => '===',
						'value'    => 'yes',
					),
					array(
						'name'     => 'open_on_exit',
						'operator' => '===',
						'value'    => 'yes',
					),
				),
			),
		) );

		$this->end_controls_section();
	}

	private function register_popup_controls() {
		$this->start_controls_section( 'section_popup', array(
			'label' => esc_html__( 'Popup', 'teckform' ),
		) );

		$this->add_control( 'position', array(
			'label'       => esc_html__( 'Position', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'center',
			'options'     => $this->get_positions(),
			'description' => esc_html__( 'The slide-in leaves the page usable: no overlay, and the page keeps scrolling.', 'teckform' ),
		) );

		$this->add_control( 'animation', array(
			'label'   => esc_html__( 'Animation', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'zoom',
			'options' => $this->get_animations(),
		) );

		$this->add_control( 'animation_speed', array(
			'label'      => esc_html__( 'Animation Speed (ms)', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min'  => 100,
					'max'  => 1500,
					'step' => 50,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup' => '--teckform-popup-speed: {{SIZE}}ms;',
			),
		) );

		$this->add_control( 'close_on_overlay', array(
			'label'        => esc_html__( 'Close on Overlay Click', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => array( 'position!' => 'slide-in' ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_button_style_controls() {
		$this->start_controls_section( 'section_style_button', array(
			'label'     => esc_html__( 'Button', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_button' => 'yes' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'button_typography',
			'selector' => '{{WRAPPER}} .teckform-popup__button',
		) );

		$this->add_responsive_control( 'button_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'{{WRAPPER}} .teckform-popup__button' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'button_icon_spacing', array(
			'label'      => esc_html__( 'Icon Spacing', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'condition'  => array( 'button_icon[value]!' => '' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__button' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'button_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__button' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__button' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__button' => 'border-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'button_hover_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__button:hover, {{WRAPPER}} .teckform-popup__button:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__button:hover, {{WRAPPER}} .teckform-popup__button:focus-visible' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__button:hover, {{WRAPPER}} .teckform-popup__button:focus-visible' => 'border-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_popup_style_controls() {
		$this->start_controls_section( 'section_style_popup', array(
			'label' => esc_html__( 'Popup', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'popup_width', array(
			'label'      => esc_html__( 'Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%', 'vw' ),
			'range'      => array(
				'px' => array(
					'min' => 240,
					'max' => 1400,
				),
			),
			'condition'  => array( 'position!' => 'fullscreen' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__panel' => 'width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'popup_max_height', array(
			'label'       => esc_html__( 'Max Height', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'vh', 'px' ),
			'range'       => array(
				'vh' => array(
					'min' => 20,
					'max' => 100,
				),
				'px' => array(
					'min' => 200,
					'max' => 1400,
				),
			),
			'description' => esc_html__( 'Longer content scrolls inside the popup.', 'teckform' ),
			'condition'   => array( 'position!' => 'fullscreen' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-popup__panel' => 'max-height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'popup_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'popup_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__panel' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'popup_text_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__panel' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'popup_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 48,
				),
			),
			'condition'  => array( 'position!' => 'fullscreen' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__panel' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'popup_shadow',
			'selector' => '{{WRAPPER}} .teckform-popup__panel',
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'popup_border',
			'selector' => '{{WRAPPER}} .teckform-popup__panel',
		) );

		$this->add_control( 'title_heading', array(
			'label'     => esc_html__( 'Title', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => array( 'show_title' => 'yes' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'title_typography',
			'selector'  => '{{WRAPPER}} .teckform-popup__title',
			'condition' => array( 'show_title' => 'yes' ),
		) );

		$this->add_control( 'title_color', array(
			'label'     => esc_html__( 'Title Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'show_title' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'title_spacing', array(
			'label'      => esc_html__( 'Space Below Title', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'condition'  => array( 'show_title' => 'yes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'dark_heading', array(
			'label'     => esc_html__( 'Dark Mode', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'dark_popup_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-popup__panel' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'dark_popup_text', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-popup__panel, html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-popup__title' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_overlay_style_controls() {
		$this->start_controls_section( 'section_style_overlay', array(
			'label'     => esc_html__( 'Overlay', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'position!' => 'slide-in' ),
		) );

		$this->add_control( 'overlay_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup' => '--teckform-popup-overlay: {{VALUE}};',
			),
		) );

		$this->add_control( 'overlay_blur', array(
			'label'      => esc_html__( 'Background Blur', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 20,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup' => '--teckform-popup-blur: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'dark_overlay_color', array(
			'label'     => esc_html__( 'Color in Dark Mode', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-popup' => '--teckform-popup-overlay: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_close_style_controls() {
		$this->start_controls_section( 'section_style_close', array(
			'label' => esc_html__( 'Close Button', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'close_size', array(
			'label'      => esc_html__( 'Icon Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 10,
					'max' => 48,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__close' => '--teckform-popup-close-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'close_offset', array(
			'label'      => esc_html__( 'Distance From Corner', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__close' => 'top: {{SIZE}}{{UNIT}}; inset-inline-end: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'close_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-popup__close' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'close_tabs' );

		$this->start_controls_tab( 'close_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'close_color', array(
			'label'     => esc_html__( 'Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__close' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'close_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__close' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'close_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'close_hover_color', array(
			'label'     => esc_html__( 'Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__close:hover, {{WRAPPER}} .teckform-popup__close:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'close_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-popup__close:hover, {{WRAPPER}} .teckform-popup__close:focus-visible' => 'background-color: {{VALUE}};',
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
		$settings  = $this->get_settings_for_display();
		$editing   = Plugin::$instance->editor->is_edit_mode();
		$popup_id  = $this->get_popup_id( $settings );
		$position  = array_key_exists( $settings['position'], $this->get_positions() ) ? $settings['position'] : 'center';
		$animation = array_key_exists( $settings['animation'], $this->get_animations() ) ? $settings['animation'] : 'fade';
		$content   = $this->get_content_html( $settings );
		$title     = trim( (string) $settings['popup_title'] );

		// Nothing to show visitors: skip the button too, so it doesn't open an empty dialog.
		if ( '' === $content && '' === $title && ! $editing ) {
			return;
		}

		$this->add_render_attribute( 'wrapper', 'class', array(
			'teckform-popup',
			'teckform-popup--' . $position,
			'teckform-popup--anim-' . $animation,
		) );

		if ( $editing ) {
			$this->add_render_attribute( 'wrapper', 'class', 'teckform-popup--preview' );
		}

		if ( 'yes' === $settings['open_on_load'] ) {
			$this->add_render_attribute( 'wrapper', 'data-open-load', (int) ( max( 0, (float) $settings['load_delay'] ) * 1000 ) );
		}

		if ( 'yes' === $settings['open_on_scroll'] ) {
			$percent = isset( $settings['scroll_percent']['size'] ) && '' !== $settings['scroll_percent']['size'] ? (int) $settings['scroll_percent']['size'] : 50;

			$this->add_render_attribute( 'wrapper', 'data-open-scroll', min( 100, max( 1, $percent ) ) );
		}

		if ( 'yes' === $settings['open_on_exit'] ) {
			$this->add_render_attribute( 'wrapper', 'data-open-exit', '' );
		}

		$this->add_render_attribute( 'wrapper', 'data-show-again', max( 0, (int) $settings['show_again_days'] ) );

		if ( 'slide-in' !== $position && 'yes' === $settings['close_on_overlay'] ) {
			$this->add_render_attribute( 'wrapper', 'data-close-overlay', '' );
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php
			$this->render_button( $settings, $popup_id );

			if ( $editing ) {
				echo '<div class="teckform-popup__preview">';
				printf(
					'<span class="teckform-popup__preview-label">%1$s <code>#%2$s</code></span>',
					esc_html__( 'Popup preview', 'teckform' ),
					esc_html( $popup_id )
				);
			}

			$this->render_dialog( $settings, $popup_id, $position, $title, $content, $editing );

			if ( $editing ) {
				echo '</div>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * The button that opens the popup.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $popup_id Popup (dialog) ID.
	 */
	private function render_button( $settings, $popup_id ) {
		if ( 'yes' !== $settings['show_button'] || ( '' === trim( (string) $settings['button_text'] ) && empty( $settings['button_icon']['value'] ) ) ) {
			return;
		}

		$icon = '';

		if ( ! empty( $settings['button_icon']['value'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['button_icon'], array( 'aria-hidden' => 'true' ) );
			$icon = '<span class="teckform-popup__button-icon">' . ob_get_clean() . '</span>';
		}

		$text = '' !== trim( (string) $settings['button_text'] )
			? '<span class="teckform-popup__button-text">' . esc_html( $settings['button_text'] ) . '</span>'
			: '<span class="screen-reader-text">' . esc_html__( 'Open popup', 'teckform' ) . '</span>';

		$inner = 'before' === $settings['button_icon_position'] ? $icon . $text : $text . $icon;

		printf(
			'<div class="teckform-popup__trigger"><button type="button" class="teckform-popup__button" aria-haspopup="dialog" aria-controls="%1$s">%2$s</button></div>',
			esc_attr( $popup_id ),
			$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
		);
	}

	/**
	 * The dialog. Rendered once per widget; open (and shown inline) in the editor.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $popup_id Popup (dialog) ID.
	 * @param string $position Position key.
	 * @param string $title    Popup title.
	 * @param string $content  Content HTML.
	 * @param bool   $editing  Whether this is the Elementor editor.
	 */
	private function render_dialog( $settings, $popup_id, $position, $title, $content, $editing ) {
		$show_title = '' !== $title && 'yes' === $settings['show_title'];

		$this->add_render_attribute( 'dialog', array(
			'id'    => $popup_id,
			'class' => 'teckform-popup__dialog',
			'role'  => 'dialog',
		) );

		// The slide-in leaves the page usable, so it is not modal.
		if ( 'slide-in' !== $position ) {
			$this->add_render_attribute( 'dialog', 'aria-modal', 'true' );
		}

		if ( $show_title ) {
			$this->add_render_attribute( 'dialog', 'aria-labelledby', $popup_id . '-title' );
		} else {
			$this->add_render_attribute( 'dialog', 'aria-label', $this->get_accessible_name( $settings, $title ) );
		}

		if ( $editing ) {
			$this->add_render_attribute( 'dialog', 'open', '' );
		}
		?>
		<dialog <?php $this->print_render_attribute_string( 'dialog' ); ?>>
			<div class="teckform-popup__panel" tabindex="-1">
				<button type="button" class="teckform-popup__close" aria-label="<?php esc_attr_e( 'Close popup', 'teckform' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18.3 5.7L16.9 4.3 12 9.2 7.1 4.3 5.7 5.7 10.6 12l-4.9 4.9 1.4 1.4 4.9-4.9 4.9 4.9 1.4-1.4-4.9-4.9z"/></svg>
				</button>
				<div class="teckform-popup__body">
					<div class="teckform-popup__inner">
						<?php if ( $show_title ) : ?>
							<h2 class="teckform-popup__title" id="<?php echo esc_attr( $popup_id . '-title' ); ?>"><?php echo esc_html( $title ); ?></h2>
						<?php endif; ?>
						<?php if ( '' !== $content ) : ?>
							<div class="teckform-popup__content">
								<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor template or text editor output. ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</dialog>
		<?php
	}

	/**
	 * Content HTML from the chosen source. In the editor, a hint replaces missing content.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_content_html( $settings ) {
		$editing = Plugin::$instance->editor->is_edit_mode();

		if ( 'template' === $settings['content_source'] ) {
			$html = function_exists( 'teckform_get_template_content' ) ? teckform_get_template_content( absint( $settings['template_id'] ) ) : '';

			if ( '' === trim( $html ) && $editing ) {
				$html = '<p class="teckform-popup__placeholder">' . esc_html__( 'Choose a Section template in the Content tab. Its design appears here.', 'teckform' ) . '</p>';
			}

			return $html;
		}

		if ( '' === trim( wp_strip_all_tags( (string) $settings['editor_content'], true ) ) && false === strpos( (string) $settings['editor_content'], '<img' ) ) {
			return $editing ? '<p class="teckform-popup__placeholder">' . esc_html__( 'Add the popup content in the Content tab.', 'teckform' ) . '</p>' : '';
		}

		// Same handling as Elementor's own Text Editor widget (paragraphs and shortcodes).
		return $this->parse_text_editor( $settings['editor_content'] );
	}

	/**
	 * Popup ID: the one entered, or popup- plus the widget ID.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_popup_id( $settings ) {
		$id = sanitize_html_class( ltrim( trim( (string) $settings['popup_id'] ), '#' ) );

		return '' !== $id ? $id : 'popup-' . $this->get_id();
	}

	/**
	 * Name for screen readers when the title isn't shown.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $title    Popup title.
	 * @return string
	 */
	private function get_accessible_name( $settings, $title ) {
		if ( '' !== $title ) {
			return $title;
		}

		if ( 'template' === $settings['content_source'] && absint( $settings['template_id'] ) ) {
			$template_title = get_the_title( absint( $settings['template_id'] ) );

			if ( '' !== $template_title ) {
				return $template_title;
			}
		}

		return __( 'Popup', 'teckform' );
	}
}
