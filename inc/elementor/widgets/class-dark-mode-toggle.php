<?php
/**
 * Dark Mode Toggle widget: lets visitors switch between light and dark mode.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dark_Mode_Toggle extends Widget_Base {

	public function get_name() {
		return 'teckform-dark-mode-toggle';
	}

	public function get_title() {
		return esc_html__( 'Dark Mode Toggle', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-adjust';
	}

	public function get_categories() {
		return array( 'teckform-site' );
	}

	public function get_keywords() {
		return array( 'dark', 'light', 'mode', 'theme', 'toggle', 'switch', 'teckform' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array(
			'label' => esc_html__( 'Toggle', 'teckform' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'style', array(
			'label'   => esc_html__( 'Style', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'switch',
			'options' => array(
				'switch' => esc_html__( 'Switch', 'teckform' ),
				'icon'   => esc_html__( 'Icon button', 'teckform' ),
			),
		) );

		$this->add_control( 'light_icon', array(
			'label' => esc_html__( 'Light Mode Icon', 'teckform' ),
			'type'  => Controls_Manager::ICONS,
		) );

		$this->add_control( 'dark_icon', array(
			'label' => esc_html__( 'Dark Mode Icon', 'teckform' ),
			'type'  => Controls_Manager::ICONS,
		) );

		$this->add_control( 'settings_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => sprintf(
				/* translators: %s: link to the Customizer. */
				esc_html__( 'Dark colors and the default mode are set in %s.', 'teckform' ),
				'<a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=teckform_dark_mode' ) ) . '" target="_blank">' . esc_html__( 'Customize > Dark Mode', 'teckform' ) . '</a>'
			),
			'content_classes' => 'elementor-descriptor',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array(
			'label' => esc_html__( 'Toggle', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'align', array(
			'label'     => esc_html__( 'Alignment', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => array(
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
			'selectors' => array(
				'{{WRAPPER}}' => 'text-align: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 10,
					'max' => 48,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-dark-toggle' => 'font-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'icon_color', array(
			'label'     => esc_html__( 'Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'style' => 'icon' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-dark-toggle--icon' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'icon_hover_color', array(
			'label'     => esc_html__( 'Icon Hover Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'style' => 'icon' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-dark-toggle--icon:hover' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'track_color', array(
			'label'     => esc_html__( 'Track Color (light mode)', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'style' => 'switch' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-dark-toggle' => '--teckform-toggle-track: {{VALUE}};',
			),
		) );

		$this->add_control( 'track_dark_color', array(
			'label'     => esc_html__( 'Track Color (dark mode)', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'style' => 'switch' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-dark-toggle' => '--teckform-toggle-track-dark: {{VALUE}};',
			),
		) );

		$this->add_control( 'thumb_color', array(
			'label'     => esc_html__( 'Knob Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'style' => 'switch' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-dark-toggle' => '--teckform-toggle-thumb: {{VALUE}};',
			),
		) );

		$this->add_control( 'thumb_icon_color', array(
			'label'     => esc_html__( 'Sun Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'style' => 'switch' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-dark-toggle' => '--teckform-toggle-icon: {{VALUE}};',
			),
		) );

		$this->add_control( 'thumb_icon_dark_color', array(
			'label'     => esc_html__( 'Moon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'style' => 'switch' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-dark-toggle' => '--teckform-toggle-icon-dark: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		if ( ! teckform_dark_mode_enabled() ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>' . esc_html__( 'Dark mode is turned off in Customize > Dark Mode.', 'teckform' ) . '</p>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts and Elementor icon markup.
		echo teckform_dark_mode_toggle_html(
			$settings['style'],
			$this->get_icon_html( $settings['light_icon'] ),
			$this->get_icon_html( $settings['dark_icon'] )
		);
	}

	/**
	 * Icon markup for an icon control, or '' to use the built-in icon.
	 *
	 * @param array $icon Icon control value.
	 * @return string
	 */
	private function get_icon_html( $icon ) {
		if ( empty( $icon['value'] ) ) {
			return '';
		}

		ob_start();
		Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) );

		return ob_get_clean();
	}
}
