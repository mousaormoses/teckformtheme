<?php
/**
 * Before / After widget: two images on top of each other with a handle that
 * reveals more of one or the other.
 *
 * The handle follows the mouse, finger or pen (assets/js/before-after.js). A real
 * <input type="range"> sits underneath, visually hidden, so keyboard and screen
 * reader users can move it too.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Before_After extends Widget_Base {

	public function get_name() {
		return 'teckform-before-after';
	}

	public function get_title() {
		return esc_html__( 'Before / After', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-image-before-after';
	}

	public function get_categories() {
		return array( 'teckform' );
	}

	public function get_keywords() {
		return array( 'before', 'after', 'compare', 'comparison', 'slider', 'image', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-before-after' );
	}

	public function get_script_depends() {
		return array( 'teckform-before-after' );
	}

	protected function register_controls() {
		$this->register_image_controls();
		$this->register_label_controls();

		$this->register_box_style_controls();
		$this->register_handle_style_controls();
		$this->register_label_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_image_controls() {
		$this->start_controls_section( 'section_images', array(
			'label' => esc_html__( 'Images', 'teckform' ),
		) );

		$this->add_control( 'before_image', array(
			'label'   => esc_html__( 'Before Image', 'teckform' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => array( 'url' => Utils::get_placeholder_image_src() ),
			'dynamic' => array( 'active' => true ),
		) );

		$this->add_control( 'after_image', array(
			'label'       => esc_html__( 'After Image', 'teckform' ),
			'type'        => Controls_Manager::MEDIA,
			'default'     => array( 'url' => Utils::get_placeholder_image_src() ),
			'dynamic'     => array( 'active' => true ),
			'description' => esc_html__( 'Use two images of the same size. The After image sets the height unless you set one in the Style tab.', 'teckform' ),
		) );

		$this->add_control( 'orientation', array(
			'label'     => esc_html__( 'Comparison', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'horizontal',
			'toggle'    => false,
			'separator' => 'before',
			'options'   => array(
				'horizontal' => array(
					'title' => esc_html__( 'Side by side (drag sideways)', 'teckform' ),
					'icon'  => 'eicon-h-align-stretch',
				),
				'vertical'   => array(
					'title' => esc_html__( 'Top and bottom (drag up and down)', 'teckform' ),
					'icon'  => 'eicon-v-align-stretch',
				),
			),
		) );

		$this->add_control( 'start_position', array(
			'label'       => esc_html__( 'Start Position', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( '%' ),
			'range'       => array(
				'%' => array(
					'min' => 0,
					'max' => 100,
				),
			),
			'default'     => array(
				'unit' => '%',
				'size' => 50,
			),
			'description' => esc_html__( 'How much of the Before image shows when the page loads.', 'teckform' ),
		) );

		$this->add_control( 'slider_label', array(
			'label'       => esc_html__( 'Name for Screen Readers', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Compare before and after', 'teckform' ),
			'separator'   => 'before',
			'description' => esc_html__( 'Read out for the slider that keyboard and screen reader users move.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	private function register_label_controls() {
		$this->start_controls_section( 'section_labels', array(
			'label' => esc_html__( 'Labels', 'teckform' ),
		) );

		$this->add_control( 'labels', array(
			'label'   => esc_html__( 'Show Labels', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'always',
			'options' => array(
				'always' => esc_html__( 'Always', 'teckform' ),
				'hover'  => esc_html__( 'On hover', 'teckform' ),
				'none'   => esc_html__( 'Never', 'teckform' ),
			),
		) );

		$this->add_control( 'before_label', array(
			'label'     => esc_html__( 'Before Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Before', 'teckform' ),
			'condition' => array( 'labels!' => 'none' ),
		) );

		$this->add_control( 'after_label', array(
			'label'     => esc_html__( 'After Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'After', 'teckform' ),
			'condition' => array( 'labels!' => 'none' ),
		) );

		$this->add_control( 'label_position', array(
			'label'       => esc_html__( 'Label Position', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'end',
			'options'     => array(
				'start'  => esc_html__( 'Top / start', 'teckform' ),
				'center' => esc_html__( 'Middle', 'teckform' ),
				'end'    => esc_html__( 'Bottom / end', 'teckform' ),
			),
			'description' => esc_html__( 'Where the labels sit along the handle: top, middle or bottom for side by side; start, middle or end for top and bottom.', 'teckform' ),
			'condition'   => array( 'labels!' => 'none' ),
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
			'label'       => esc_html__( 'Height', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px', 'vh' ),
			'range'       => array(
				'px' => array(
					'min' => 120,
					'max' => 1200,
				),
				'vh' => array(
					'min' => 10,
					'max' => 100,
				),
			),
			'description' => esc_html__( 'Empty: the height of the After image.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-before-after' => 'height: {{SIZE}}{{UNIT}};',
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
				'{{WRAPPER}} .teckform-before-after' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'box_border',
			'selector' => '{{WRAPPER}} .teckform-before-after',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'box_shadow',
			'selector' => '{{WRAPPER}} .teckform-before-after',
		) );

		$this->end_controls_section();
	}

	private function register_handle_style_controls() {
		$this->start_controls_section( 'section_style_handle', array(
			'label' => esc_html__( 'Handle', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'line_width', array(
			'label'      => esc_html__( 'Line Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 12,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-before-after' => '--teckform-ba-line-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'knob_size', array(
			'label'      => esc_html__( 'Knob Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 16,
					'max' => 96,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-before-after' => '--teckform-ba-knob: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'knob_radius', array(
			'label'      => esc_html__( 'Knob Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 50,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-before-after__knob' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'knob_border',
			'selector' => '{{WRAPPER}} .teckform-before-after__knob',
		) );

		$this->start_controls_tabs( 'handle_tabs' );

		$this->start_controls_tab( 'handle_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'line_color', array(
			'label'     => esc_html__( 'Line Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-before-after' => '--teckform-ba-line: {{VALUE}};',
			),
		) );

		$this->add_control( 'knob_bg', array(
			'label'     => esc_html__( 'Knob Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-before-after__knob' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'knob_icon', array(
			'label'     => esc_html__( 'Arrow Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-before-after__knob' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'knob_shadow',
			'selector' => '{{WRAPPER}} .teckform-before-after__knob',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'handle_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'line_hover_color', array(
			'label'     => esc_html__( 'Line Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-before-after:hover, {{WRAPPER}} .teckform-before-after.is-dragging' => '--teckform-ba-line: {{VALUE}};',
			),
		) );

		$this->add_control( 'knob_hover_bg', array(
			'label'     => esc_html__( 'Knob Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-before-after:hover .teckform-before-after__knob, {{WRAPPER}} .teckform-before-after.is-dragging .teckform-before-after__knob' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'knob_hover_icon', array(
			'label'     => esc_html__( 'Arrow Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-before-after:hover .teckform-before-after__knob, {{WRAPPER}} .teckform-before-after.is-dragging .teckform-before-after__knob' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_label_style_controls() {
		$this->start_controls_section( 'section_style_labels', array(
			'label'     => esc_html__( 'Labels', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'labels!' => 'none' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'label_typography',
			'selector' => '{{WRAPPER}} .teckform-before-after__label',
		) );

		$this->add_control( 'label_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-before-after__label' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'label_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-before-after__label' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'label_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-before-after__label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'label_radius', array(
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
				'{{WRAPPER}} .teckform-before-after__label' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'label_border',
			'selector' => '{{WRAPPER}} .teckform-before-after__label',
		) );

		$this->add_responsive_control( 'label_offset', array(
			'label'      => esc_html__( 'Distance From Edge', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-before-after' => '--teckform-ba-label-offset: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings    = $this->get_settings_for_display();
		$orientation = 'vertical' === $settings['orientation'] ? 'vertical' : 'horizontal';
		$position    = isset( $settings['start_position']['size'] ) && '' !== $settings['start_position']['size'] ? (float) $settings['start_position']['size'] : 50;
		$position    = min( 100, max( 0, $position ) );
		$labels      = in_array( $settings['labels'], array( 'always', 'hover' ), true ) ? $settings['labels'] : 'none';
		$label_pos   = in_array( $settings['label_position'], array( 'start', 'center', 'end' ), true ) ? $settings['label_position'] : 'end';
		$before      = $this->get_image_html( $settings['before_image'], 'teckform-before-after__image' );
		$after       = $this->get_image_html( $settings['after_image'], 'teckform-before-after__image' );

		if ( ! $before && ! $after ) {
			return;
		}

		$this->add_render_attribute( 'wrapper', array(
			'class' => array(
				'teckform-before-after',
				'teckform-before-after--' . $orientation,
				'teckform-before-after--labels-' . $labels,
				'teckform-before-after--labels-at-' . $label_pos,
			),
			'style' => '--teckform-ba-position: ' . $position . '%;',
		) );

		$this->add_render_attribute( 'range', array(
			'type'             => 'range',
			'class'            => 'teckform-before-after__range',
			'min'              => '0',
			'max'              => '100',
			'step'             => '1',
			'value'            => (string) round( $position ),
			'aria-label'       => '' !== trim( $settings['slider_label'] ) ? $settings['slider_label'] : __( 'Compare before and after', 'teckform' ),
			'aria-orientation' => $orientation,
			/* translators: %s: percentage of the Before image that is visible. Keep the %s. */
			'data-value-text'  => __( '%s%% before image', 'teckform' ),
		) );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="teckform-before-after__after"><?php echo $after; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html(). ?></div>
			<div class="teckform-before-after__before"><?php echo $before; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html(). ?></div>

			<?php if ( 'none' !== $labels ) : ?>
				<?php if ( '' !== trim( (string) $settings['before_label'] ) ) : ?>
					<span class="teckform-before-after__label teckform-before-after__label--before" aria-hidden="true"><?php echo esc_html( $settings['before_label'] ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== trim( (string) $settings['after_label'] ) ) : ?>
					<span class="teckform-before-after__label teckform-before-after__label--after" aria-hidden="true"><?php echo esc_html( $settings['after_label'] ); ?></span>
				<?php endif; ?>
			<?php endif; ?>

			<input <?php $this->print_render_attribute_string( 'range' ); ?>>

			<div class="teckform-before-after__handle" aria-hidden="true">
				<span class="teckform-before-after__knob">
					<svg viewBox="0 0 24 24"><path d="M9.4 7.4L8 6l-6 6 6 6 1.4-1.4L4.8 12zm5.2 0L16 6l6 6-6 6-1.4-1.4 4.6-4.6z"/></svg>
				</span>
			</div>
		</div>
		<?php
	}

	/**
	 * <img> markup for a media value. Both images are described by their own alt text.
	 *
	 * @param array  $media Value with 'id' and/or 'url'.
	 * @param string $class Class for the image.
	 * @return string
	 */
	private function get_image_html( $media, $class ) {
		if ( ! empty( $media['id'] ) ) {
			$html = wp_get_attachment_image( absint( $media['id'] ), 'full', false, array(
				'class'     => $class,
				'draggable' => 'false',
			) );

			if ( $html ) {
				return $html;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img src="' . esc_url( $media['url'] ) . '" alt="" class="' . esc_attr( $class ) . '" draggable="false">';
		}

		return '';
	}
}
