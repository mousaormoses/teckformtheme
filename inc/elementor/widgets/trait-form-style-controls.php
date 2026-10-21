<?php
/**
 * Style tab sections shared by the form widgets (Contact Form, Newsletter,
 * Login / Register): labels, fields, button and messages.
 *
 * Each widget passes its BEM block name, and its markup uses the same element
 * names: __label, __required, __check, __input, __button, __button-icon,
 * __message (with is-success / is-error) and __error.
 *
 * Loaded by the widget files themselves; it is not a widget.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Form_Style_Controls {

	/**
	 * Labels, required mark and checkbox text.
	 *
	 * @param string $block BEM block, e.g. 'teckform-contact-form'.
	 * @param array  $args  Optional 'condition' for the section, 'checkbox' to add checkbox controls.
	 */
	protected function register_form_label_style_controls( $block, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'condition' => array(),
			'checkbox'  => true,
		) );

		$this->start_controls_section( 'section_style_labels', array(
			'label'     => esc_html__( 'Labels', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => $args['condition'],
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'label_typography',
			'selector' => "{{WRAPPER}} .{$block}__label",
		) );

		$this->add_control( 'label_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__label" => 'color: {{VALUE}};',
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
				"{{WRAPPER}} .{$block}" => '--teckform-form-label-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'required_color', array(
			'label'     => esc_html__( 'Required Mark Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__required" => 'color: {{VALUE}};',
			),
		) );

		if ( $args['checkbox'] ) {
			$this->add_control( 'check_heading', array(
				'label'     => esc_html__( 'Checkboxes', 'teckform' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			) );

			$this->add_group_control( Group_Control_Typography::get_type(), array(
				'name'     => 'check_typography',
				'selector' => "{{WRAPPER}} .{$block}__check",
			) );

			$this->add_control( 'check_color', array(
				'label'     => esc_html__( 'Text Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					"{{WRAPPER}} .{$block}__check" => 'color: {{VALUE}};',
				),
			) );

			$this->add_control( 'check_accent', array(
				'label'     => esc_html__( 'Checkbox Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					"{{WRAPPER}} .{$block}__check input" => 'accent-color: {{VALUE}};',
				),
			) );

			$this->add_control( 'check_link_color', array(
				'label'     => esc_html__( 'Link Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					"{{WRAPPER}} .{$block}__check a" => 'color: {{VALUE}};',
				),
			) );
		}

		$this->end_controls_section();
	}

	/**
	 * Inputs, selects and text areas.
	 *
	 * @param string $block BEM block.
	 * @param array  $args  Optional 'columns' (column gap control), 'textarea' (height control).
	 */
	protected function register_form_field_style_controls( $block, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'columns'  => true,
			'textarea' => false,
		) );

		$this->start_controls_section( 'section_style_fields', array(
			'label' => esc_html__( 'Fields', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'row_gap', array(
			'label'      => esc_html__( 'Space Between Rows', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				"{{WRAPPER}} .{$block}" => '--teckform-form-row-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		if ( $args['columns'] ) {
			$this->add_responsive_control( 'column_gap', array(
				'label'      => esc_html__( 'Space Between Columns', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'  => array(
					"{{WRAPPER}} .{$block}" => '--teckform-form-col-gap: {{SIZE}}{{UNIT}};',
				),
			) );
		}

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'field_typography',
			'separator' => 'before',
			'selector'  => "{{WRAPPER}} .{$block}__input",
		) );

		$this->add_control( 'field_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__input" => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'placeholder_color', array(
			'label'     => esc_html__( 'Placeholder Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__input::placeholder" => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'field_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__input" => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'field_border_color', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__input" => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'field_border_width', array(
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
				"{{WRAPPER}} .{$block}__input" => 'border-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'field_focus_color', array(
			'label'       => esc_html__( 'Focus Color', 'teckform' ),
			'type'        => Controls_Manager::COLOR,
			'description' => esc_html__( 'Border and glow of the field being typed in.', 'teckform' ),
			'selectors'   => array(
				"{{WRAPPER}} .{$block}" => '--teckform-form-focus: {{VALUE}};',
			),
		) );

		$this->add_control( 'field_radius', array(
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
				"{{WRAPPER}} .{$block}__input" => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'field_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				"{{WRAPPER}} .{$block}__input" => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		if ( $args['textarea'] ) {
			$this->add_responsive_control( 'textarea_height', array(
				'label'      => esc_html__( 'Text Area Height', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 60,
						'max' => 500,
					),
				),
				'selectors'  => array(
					"{{WRAPPER}} textarea.{$block}__input" => 'min-height: {{SIZE}}{{UNIT}};',
				),
			) );
		}

		$this->end_controls_section();
	}

	/**
	 * Submit button with Normal / Hover colors.
	 *
	 * @param string $block BEM block.
	 * @param array  $args  Optional 'align' (full width and alignment controls), 'full_width'
	 *                      (default of the Full Width switch), 'icon' (icon spacing).
	 */
	protected function register_form_button_style_controls( $block, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'align'      => true,
			'full_width' => false,
			'icon'       => false,
		) );

		$this->start_controls_section( 'section_style_button', array(
			'label' => esc_html__( 'Button', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		if ( $args['align'] ) {
			$this->add_control( 'button_full_width', array(
				'label'        => esc_html__( 'Full Width', 'teckform' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => $args['full_width'] ? 'yes' : '',
				'return_value' => 'yes',
				'selectors'    => array(
					"{{WRAPPER}} .{$block}__button" => 'width: 100%;',
				),
			) );

			$this->add_responsive_control( 'button_align', array(
				'label'                => esc_html__( 'Alignment', 'teckform' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'start'  => array(
						'title' => esc_html__( 'Start', 'teckform' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'teckform' ),
						'icon'  => 'eicon-h-align-center',
					),
					'end'    => array(
						'title' => esc_html__( 'End', 'teckform' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors_dictionary' => array(
					'start'  => 'flex-start',
					'center' => 'center',
					'end'    => 'flex-end',
				),
				'condition'            => array( 'button_full_width!' => 'yes' ),
				'selectors'            => array(
					"{{WRAPPER}} .{$block}" => '--teckform-form-button-justify: {{VALUE}};',
				),
			) );
		}

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'button_typography',
			'selector' => "{{WRAPPER}} .{$block}__button",
		) );

		$this->start_controls_tabs( 'button_color_tabs' );

		$this->start_controls_tab( 'button_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'button_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__button" => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__button" => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_border_color', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__button" => 'border-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'button_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'button_hover_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__button:hover, {{WRAPPER}} .{$block}__button:focus-visible" => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__button:hover, {{WRAPPER}} .{$block}__button:focus-visible" => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'button_hover_border_color', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__button:hover, {{WRAPPER}} .{$block}__button:focus-visible" => 'border-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'      => 'button_border',
			'separator' => 'before',
			'exclude'   => array( 'color' ),
			'selector'  => "{{WRAPPER}} .{$block}__button",
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
				"{{WRAPPER}} .{$block}__button" => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'button_shadow',
			'selector' => "{{WRAPPER}} .{$block}__button",
		) );

		$this->add_responsive_control( 'button_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				"{{WRAPPER}} .{$block}__button" => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		if ( $args['icon'] ) {
			$this->add_control( 'button_icon_gap', array(
				'label'      => esc_html__( 'Icon Spacing', 'teckform' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'selectors'  => array(
					"{{WRAPPER}} .{$block}__button" => 'gap: {{SIZE}}{{UNIT}};',
				),
			) );
		}

		$this->end_controls_section();
	}

	/**
	 * Success and error messages, and the notes under invalid fields.
	 *
	 * @param string $block BEM block.
	 */
	protected function register_form_message_style_controls( $block ) {
		$this->start_controls_section( 'section_style_messages', array(
			'label' => esc_html__( 'Messages', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'message_typography',
			'selector' => "{{WRAPPER}} .{$block}__message",
		) );

		$this->add_control( 'success_heading', array(
			'label'     => esc_html__( 'Success', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'success_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__message.is-success" => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'success_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__message.is-success" => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'success_accent', array(
			'label'     => esc_html__( 'Accent Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}" => '--teckform-form-success: {{VALUE}};',
			),
		) );

		$this->add_control( 'error_heading', array(
			'label'     => esc_html__( 'Error', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'error_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__message.is-error" => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'error_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				"{{WRAPPER}} .{$block}__message.is-error" => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'error_accent', array(
			'label'       => esc_html__( 'Accent Color', 'teckform' ),
			'type'        => Controls_Manager::COLOR,
			'description' => esc_html__( 'Also used for the border and note of fields that need fixing.', 'teckform' ),
			'selectors'   => array(
				"{{WRAPPER}} .{$block}" => '--teckform-form-error: {{VALUE}};',
			),
		) );

		$this->add_control( 'message_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'separator'  => 'before',
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 30,
				),
			),
			'selectors'  => array(
				"{{WRAPPER}} .{$block}__message" => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	/**
	 * Editor-only note shown in place of a widget that has nothing to show yet.
	 *
	 * @param string $block BEM block.
	 * @param string $text  Note text.
	 */
	protected function render_editor_notice( $block, $text ) {
		if ( teckform_form_is_editor() ) {
			echo '<div class="' . esc_attr( $block . '__notice' ) . '">' . esc_html( $text ) . '</div>';
		}
	}

	/**
	 * Live region for success and error messages, filled by JavaScript or, without it,
	 * from the result of the last submission.
	 *
	 * @param string     $block BEM block.
	 * @param array|null $flash Result from teckform_form_get_flash().
	 */
	protected function render_form_message( $block, $flash ) {
		$class = $block . '__message';

		if ( $flash && '' !== $flash['message'] ) {
			$class .= $flash['success'] ? ' is-success' : ' is-error';
		}

		printf(
			'<div class="%1$s" role="status" aria-live="polite">%2$s</div>',
			esc_attr( $class ),
			$flash ? esc_html( $flash['message'] ) : ''
		);
	}

	/**
	 * Error note under a field after a submission without JavaScript.
	 *
	 * @param string     $block    BEM block.
	 * @param array|null $flash    Result from teckform_form_get_flash().
	 * @param string     $key      Field key.
	 * @param string     $input_id ID of the field's input.
	 * @return string[] Escaped attributes for the input and the note's HTML ('' and '' when the field is fine).
	 */
	protected function get_field_error( $block, $flash, $key, $input_id ) {
		if ( ! $flash || empty( $flash['errors'][ $key ] ) ) {
			return array( '', '' );
		}

		$note_id = $input_id . '-error';

		return array(
			' aria-invalid="true" aria-describedby="' . esc_attr( $note_id ) . '"',
			'<p class="' . esc_attr( $block . '__error' ) . '" id="' . esc_attr( $note_id ) . '">' . esc_html( $flash['errors'][ $key ] ) . '</p>',
		);
	}

	/**
	 * Value to refill a field with after a failed submission without JavaScript.
	 *
	 * @param array|null $flash Result from teckform_form_get_flash().
	 * @param string     $key   Field key.
	 * @return string|string[]
	 */
	protected function get_flash_value( $flash, $key ) {
		return $flash && isset( $flash['values'][ $key ] ) ? $flash['values'][ $key ] : '';
	}
}
