<?php
/**
 * Call to Action widget: a rounded banner with a title, text and two buttons.
 *
 * Centered or split (text at the start, buttons at the end), in the accent
 * color, as a light card or as a dark contrast block, with an optional
 * background image and overlay and the same decorative patterns as the Hero.
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

class Call_To_Action extends Widget_Base {

	public function get_name() {
		return 'teckform-call-to-action';
	}

	public function get_title() {
		return esc_html__( 'Call to Action', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'cta', 'call to action', 'banner', 'action', 'button', 'contact', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-call-to-action' );
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_button_controls();
		$this->register_design_controls();

		$this->register_box_style_controls();
		$this->register_eyebrow_style_controls();
		$this->register_text_style_controls();
		$this->register_button_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_content_controls() {
		$this->start_controls_section( 'section_content', array(
			'label' => esc_html__( 'Text', 'teckform' ),
		) );

		$this->add_control( 'eyebrow', array(
			'label'       => esc_html__( 'Small Text Above Title', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Ready when you are', 'teckform' ),
			'label_block' => true,
			'dynamic'     => array( 'active' => true ),
		) );

		$this->add_control( 'title', array(
			'label'   => esc_html__( 'Title', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'Let\'s build something great together', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$this->add_control( 'title_tag', array(
			'label'   => esc_html__( 'Title HTML Tag', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h2',
			'options' => array(
				'h2' => 'H2',
				'h3' => 'H3',
				'h4' => 'H4',
				'p'  => 'p',
			),
		) );

		$this->add_control( 'description', array(
			'label'   => esc_html__( 'Description', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 3,
			'default' => esc_html__( 'Tell us about your project and get a free quote within one business day.', 'teckform' ),
			'dynamic' => array( 'active' => true ),
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
				'default'   => 1 === $n ? esc_html__( 'Start a project', 'teckform' ) : esc_html__( 'Book a call', 'teckform' ),
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

	private function register_design_controls() {
		$this->start_controls_section( 'section_design', array(
			'label' => esc_html__( 'Layout and Background', 'teckform' ),
		) );

		$this->add_control( 'layout', array(
			'label'   => esc_html__( 'Layout', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'centered',
			'options' => array(
				'centered' => esc_html__( 'Centered', 'teckform' ),
				'split'    => esc_html__( 'Split: text at the start, buttons at the end', 'teckform' ),
			),
		) );

		$this->add_control( 'scheme', array(
			'label'       => esc_html__( 'Color Style', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'accent',
			'options'     => array(
				'accent'   => esc_html__( 'Accent color', 'teckform' ),
				'surface'  => esc_html__( 'Light card', 'teckform' ),
				'contrast' => esc_html__( 'Dark contrast', 'teckform' ),
			),
			'description' => esc_html__( 'Uses the theme colors, so it follows dark mode. Fine-tune in the Style tab.', 'teckform' ),
		) );

		$this->add_control( 'pattern', array(
			'label'   => esc_html__( 'Decorative Pattern', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'grid',
			'options' => array(
				'none' => esc_html__( 'None', 'teckform' ),
				'grid' => esc_html__( 'Grid lines', 'teckform' ),
				'dots' => esc_html__( 'Dots', 'teckform' ),
				'glow' => esc_html__( 'Soft color glow', 'teckform' ),
			),
		) );

		$this->add_control( 'bg_image', array(
			'label'       => esc_html__( 'Background Image', 'teckform' ),
			'type'        => Controls_Manager::MEDIA,
			'dynamic'     => array( 'active' => true ),
			'separator'   => 'before',
			'description' => esc_html__( 'Optional. Text turns white over the image; set the overlay in the Style tab.', 'teckform' ),
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

		$this->add_responsive_control( 'box_width', array(
			'label'      => esc_html__( 'Max Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 300,
					'max' => 1600,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta' => 'max-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'box_min_height', array(
			'label'      => esc_html__( 'Minimum Height', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'vh' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 800,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta' => 'min-height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'box_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'box_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 64,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'content_align', array(
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
				'start'  => 'text-align: start; --teckform-cta-justify: flex-start; --teckform-cta-margin: 0;',
				'center' => 'text-align: center; --teckform-cta-justify: center; --teckform-cta-margin: auto;',
			),
			'condition'            => array( 'layout' => 'centered' ),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-cta__inner' => '{{VALUE}}',
			),
		) );

		$this->add_responsive_control( 'split_gap', array(
			'label'      => esc_html__( 'Space Between Text and Buttons', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 160,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta' => '--teckform-cta-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'box_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array(
				'{{WRAPPER}} .teckform-cta' => '--teckform-cta-bg: {{VALUE}};',
			),
		) );

		$this->add_control( 'pattern_color', array(
			'label'     => esc_html__( 'Pattern Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'pattern!' => 'none' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-cta' => '--teckform-cta-pattern: {{VALUE}};',
			),
		) );

		$this->add_control( 'overlay_color', array(
			'label'     => esc_html__( 'Image Overlay Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'bg_image[url]!' => '' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-cta__overlay' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'overlay_opacity', array(
			'label'     => esc_html__( 'Image Overlay Opacity', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 0,
					'max'  => 1,
					'step' => 0.05,
				),
			),
			'condition' => array( 'bg_image[url]!' => '' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-cta__overlay' => 'opacity: {{SIZE}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'      => 'box_border',
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-cta',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'box_shadow',
			'selector' => '{{WRAPPER}} .teckform-cta',
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
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-cta' => '--teckform-cta-bg: {{VALUE}};',
			),
		) );

		$this->add_control( 'dark_text', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-cta' => '--teckform-cta-text: {{VALUE}}; --teckform-cta-muted: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_eyebrow_style_controls() {
		$this->start_controls_section( 'section_style_eyebrow', array(
			'label'     => esc_html__( 'Small Text Above Title', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'eyebrow!' => '' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'eyebrow_typography',
			'selector' => '{{WRAPPER}} .teckform-cta__eyebrow',
		) );

		$this->add_control( 'eyebrow_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-cta__eyebrow' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'eyebrow_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-cta__eyebrow' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'eyebrow_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta__eyebrow' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'eyebrow_radius', array(
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
				'{{WRAPPER}} .teckform-cta__eyebrow' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'eyebrow_border',
			'selector' => '{{WRAPPER}} .teckform-cta__eyebrow',
		) );

		$this->add_responsive_control( 'eyebrow_spacing', array(
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
				'{{WRAPPER}} .teckform-cta__eyebrow' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_text_style_controls() {
		$this->start_controls_section( 'section_style_text', array(
			'label' => esc_html__( 'Title and Description', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'title_typography',
			'label'    => esc_html__( 'Title Typography', 'teckform' ),
			'selector' => '{{WRAPPER}} .teckform-cta__title',
		) );

		$this->add_control( 'title_color', array(
			'label'     => esc_html__( 'Title Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-cta__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'title_spacing', array(
			'label'      => esc_html__( 'Title Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'description_typography',
			'label'     => esc_html__( 'Description Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-cta__description',
		) );

		$this->add_control( 'description_color', array(
			'label'     => esc_html__( 'Description Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-cta__description' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'content_width', array(
			'label'      => esc_html__( 'Text Max Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 200,
					'max' => 1200,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta__content' => 'max-width: {{SIZE}}{{UNIT}};',
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
			'selector' => '{{WRAPPER}} .teckform-cta__button',
		) );

		$this->add_responsive_control( 'button_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'{{WRAPPER}} .teckform-cta__button' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'button_border_width', array(
			'label'      => esc_html__( 'Border Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 6,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta__button' => 'border-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'button_gap', array(
			'label'      => esc_html__( 'Space Between Buttons', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-cta__buttons' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'button_shadow',
			'selector' => '{{WRAPPER}} .teckform-cta__button',
		) );

		$this->start_controls_tabs( 'button_tabs' );

		foreach ( array( 'normal', 'hover' ) as $state ) {
			$this->start_controls_tab( "button_tab_{$state}", array(
				'label' => 'normal' === $state ? esc_html__( 'Normal', 'teckform' ) : esc_html__( 'Hover', 'teckform' ),
			) );

			$suffix = 'hover' === $state ? ':hover, {{WRAPPER}} .teckform-cta__button--%1$s:focus-visible' : '';
			$prefix = 'hover' === $state ? 'hover_' : '';

			foreach ( array( 1 => 'primary', 2 => 'secondary' ) as $n => $variant ) {
				$selector = '{{WRAPPER}} .teckform-cta__button--' . $variant . sprintf( $suffix, $variant );

				$this->add_control( "button_{$n}_{$prefix}heading", array(
					/* translators: %d: button number. */
					'label'     => sprintf( esc_html__( 'Button %d', 'teckform' ), $n ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 2 === $n ? 'before' : 'default',
				) );

				$this->add_control( "button_{$n}_{$prefix}color", array(
					'label'     => esc_html__( 'Text Color', 'teckform' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$selector => 'color: {{VALUE}};',
					),
				) );

				$this->add_control( "button_{$n}_{$prefix}bg", array(
					'label'     => esc_html__( 'Background Color', 'teckform' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$selector => 'background-color: {{VALUE}};',
					),
				) );

				$this->add_control( "button_{$n}_{$prefix}border", array(
					'label'     => esc_html__( 'Border Color', 'teckform' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$selector => 'border-color: {{VALUE}};',
					),
				) );
			}

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$layout   = 'split' === $settings['layout'] ? 'split' : 'centered';
		$scheme   = in_array( $settings['scheme'], array( 'accent', 'surface', 'contrast' ), true ) ? $settings['scheme'] : 'accent';
		$image    = $this->get_image_html( $settings['bg_image'] );

		$classes = array(
			'teckform-cta',
			'teckform-cta--' . $layout,
			'teckform-cta--scheme-' . $scheme,
		);

		if ( in_array( $settings['pattern'], array( 'grid', 'dots', 'glow' ), true ) ) {
			$classes[] = 'teckform-cta--pattern-' . $settings['pattern'];
		}

		if ( $image ) {
			$classes[] = 'teckform-cta--has-image';
		}

		$tag = Utils::validate_html_tag( $settings['title_tag'] );
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php if ( $image ) : ?>
				<div class="teckform-cta__background" aria-hidden="true">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html(). ?>
					<span class="teckform-cta__overlay"></span>
				</div>
			<?php endif; ?>
			<div class="teckform-cta__inner">
				<div class="teckform-cta__content">
					<?php if ( '' !== trim( $settings['eyebrow'] ) ) : ?>
						<p class="teckform-cta__eyebrow"><?php echo esc_html( $settings['eyebrow'] ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== trim( $settings['title'] ) ) : ?>
						<<?php echo esc_html( $tag ); ?> class="teckform-cta__title"><?php echo wp_kses_post( $settings['title'] ); ?></<?php echo esc_html( $tag ); ?>>
					<?php endif; ?>
					<?php if ( '' !== trim( $settings['description'] ) ) : ?>
						<p class="teckform-cta__description"><?php echo wp_kses_post( $settings['description'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php $this->render_buttons( $settings ); ?>
			</div>
		</div>
		<?php
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

			$this->add_render_attribute( $key, 'class', array( 'teckform-cta__button', 'teckform-cta__button--' . $variant ) );

			if ( ! empty( $settings[ "button_{$n}_link" ]['url'] ) ) {
				$this->add_link_attributes( $key, $settings[ "button_{$n}_link" ] );
			}

			$icon = '';

			if ( ! empty( $settings[ "button_{$n}_icon" ]['value'] ) ) {
				ob_start();
				Icons_Manager::render_icon( $settings[ "button_{$n}_icon" ], array( 'aria-hidden' => 'true' ) );
				$icon = '<span class="teckform-cta__button-icon">' . ob_get_clean() . '</span>';
			}

			$buttons .= '<a ' . $this->get_render_attribute_string( $key ) . '><span>' . esc_html( $text ) . '</span>' . $icon . '</a>';
		}

		if ( $buttons ) {
			echo '<div class="teckform-cta__buttons">' . $buttons . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
		}
	}

	/**
	 * Decorative background <img> for a media value.
	 *
	 * @param array $media Value with 'id' and/or 'url'.
	 * @return string
	 */
	private function get_image_html( $media ) {
		if ( ! empty( $media['id'] ) ) {
			$html = wp_get_attachment_image( absint( $media['id'] ), 'full', false, array(
				'class' => 'teckform-cta__bg-image',
				'alt'   => '',
			) );

			if ( $html ) {
				return $html;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img class="teckform-cta__bg-image" src="' . esc_url( $media['url'] ) . '" alt="" loading="lazy">';
		}

		return '';
	}
}
