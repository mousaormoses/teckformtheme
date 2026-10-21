<?php
/**
 * Nav Menu widget: a WordPress menu with dropdowns on desktop and a
 * slide-in panel on smaller screens. The vertical layout suits footer link lists.
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

class Nav_Menu extends Widget_Base {

	/**
	 * Whether submenu arrow buttons are printed in the current render.
	 *
	 * @var bool
	 */
	private $show_toggles = false;

	/**
	 * Whether mega menus are shown in the current render (horizontal layout only).
	 *
	 * @var bool
	 */
	private $mega_menus = false;

	public function get_name() {
		return 'teckform-nav-menu';
	}

	public function get_title() {
		return esc_html__( 'Nav Menu', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_categories() {
		return array( 'teckform-site' );
	}

	public function get_keywords() {
		return array( 'menu', 'nav', 'navigation', 'header', 'footer', 'links', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-widgets' );
	}

	public function get_script_depends() {
		return array( 'teckform-widgets' );
	}

	/**
	 * Menus created in Appearance > Menus.
	 *
	 * @return array<int, string>
	 */
	private function get_menus() {
		$options = array();

		foreach ( wp_get_nav_menus() as $menu ) {
			$options[ $menu->term_id ] = $menu->name;
		}

		return $options;
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_menu_style_controls();
		$this->register_dropdown_style_controls();
		$this->register_mega_style_controls();
		$this->register_mobile_style_controls();
	}

	private function register_content_controls() {
		$this->start_controls_section( 'section_menu', array(
			'label' => esc_html__( 'Menu', 'teckform' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		) );

		$menus = $this->get_menus();

		if ( $menus ) {
			$this->add_control( 'menu', array(
				'label'       => esc_html__( 'Menu', 'teckform' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $menus,
				'default'     => (string) array_key_first( $menus ),
				'description' => sprintf(
					/* translators: %s: link to the Menus screen. */
					esc_html__( 'Edit menus in %s.', 'teckform' ),
					'<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">' . esc_html__( 'Appearance > Menus', 'teckform' ) . '</a>'
				),
			) );
		} else {
			$this->add_control( 'menu_notice', array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					/* translators: %s: link to the Menus screen. */
					esc_html__( 'There are no menus yet. %s', 'teckform' ),
					'<a href="' . esc_url( admin_url( 'nav-menus.php?action=edit&menu=0' ) ) . '" target="_blank">' . esc_html__( 'Create a menu', 'teckform' ) . '</a>'
				),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			) );
		}

		$this->add_control( 'layout', array(
			'label'       => esc_html__( 'Layout', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'horizontal',
			'options'     => array(
				'horizontal' => esc_html__( 'Horizontal', 'teckform' ),
				'vertical'   => esc_html__( 'Vertical', 'teckform' ),
			),
			'description' => esc_html__( 'For a list of footer links, choose Vertical and set Mobile Menu to Never.', 'teckform' ),
		) );

		$this->add_control( 'pointer', array(
			'label'     => esc_html__( 'Hover Effect', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'none',
			'options'   => array(
				'none'      => esc_html__( 'None', 'teckform' ),
				'underline' => esc_html__( 'Underline', 'teckform' ),
			),
		) );

		$this->add_control( 'show_arrows', array(
			'label'        => esc_html__( 'Submenu Arrows', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'mega_width', array(
			'label'       => esc_html__( 'Mega Menu Width', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'container',
			'options'     => array(
				'container' => esc_html__( 'Same as the header content', 'teckform' ),
				'full'      => esc_html__( 'Full screen width', 'teckform' ),
				'custom'    => esc_html__( 'Custom (centered under the item)', 'teckform' ),
			),
			'description' => sprintf(
				/* translators: %s: link to the Menus screen. */
				esc_html__( 'Give a menu item a mega menu in %s: pick a Section template under "Mega menu".', 'teckform' ),
				'<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">' . esc_html__( 'Appearance > Menus', 'teckform' ) . '</a>'
			),
			'condition'   => array( 'layout' => 'horizontal' ),
		) );

		$this->add_control( 'breakpoint', array(
			'label'     => esc_html__( 'Mobile Menu', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'tablet',
			'separator' => 'before',
			'options'   => array(
				'tablet' => esc_html__( 'Tablet and mobile (1024px and below)', 'teckform' ),
				'mobile' => esc_html__( 'Mobile only (767px and below)', 'teckform' ),
				'none'   => esc_html__( 'Never', 'teckform' ),
			),
		) );

		$this->add_control( 'toggle_icon', array(
			'label'     => esc_html__( 'Menu Icon', 'teckform' ),
			'type'      => Controls_Manager::ICONS,
			'default'   => array(
				'value'   => 'fas fa-bars',
				'library' => 'fa-solid',
			),
			'condition' => array( 'breakpoint!' => 'none' ),
		) );

		$this->add_control( 'panel_side', array(
			'label'     => esc_html__( 'Menu Slides In From', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'right',
			'options'   => array(
				'right' => esc_html__( 'Right', 'teckform' ),
				'left'  => esc_html__( 'Left', 'teckform' ),
			),
			'condition' => array( 'breakpoint!' => 'none' ),
		) );

		$this->end_controls_section();
	}

	private function register_menu_style_controls() {
		$this->start_controls_section( 'section_style_menu', array(
			'label' => esc_html__( 'Menu', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'align', array(
			'label'     => esc_html__( 'Alignment', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => array(
				'flex-start'    => array(
					'title' => esc_html__( 'Start', 'teckform' ),
					'icon'  => 'eicon-h-align-left',
				),
				'center'        => array(
					'title' => esc_html__( 'Center', 'teckform' ),
					'icon'  => 'eicon-h-align-center',
				),
				'flex-end'      => array(
					'title' => esc_html__( 'End', 'teckform' ),
					'icon'  => 'eicon-h-align-right',
				),
				'space-between' => array(
					'title' => esc_html__( 'Stretch', 'teckform' ),
					'icon'  => 'eicon-h-align-stretch',
				),
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-nav-align: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'item_gap', array(
			'label'      => esc_html__( 'Space Between Items', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-nav-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'item_typography',
			'selector' => '{{WRAPPER}} .teckform-nav__menu > li > a',
		) );

		$this->add_responsive_control( 'item_padding', array(
			'label'      => esc_html__( 'Item Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__menu > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'item_radius', array(
			'label'      => esc_html__( 'Item Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__menu > li > a' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'item_tabs' );

		$this->start_controls_tab( 'item_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_responsive_control( 'item_color', array(
			'label'       => esc_html__( 'Color', 'teckform' ),
			'type'        => Controls_Manager::COLOR,
			'description' => esc_html__( 'Tip: set a different color for tablet and mobile if the mobile menu background is a different color.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-nav__menu > li > a, {{WRAPPER}} .teckform-nav__menu > li > .teckform-nav__sub-toggle, {{WRAPPER}} .teckform-nav--vertical .sub-menu a' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'item_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__menu > li > a' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'item_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_responsive_control( 'item_hover_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__menu > li > a:hover, {{WRAPPER}} .teckform-nav__menu > li > a:focus-visible, {{WRAPPER}} .teckform-nav--vertical .sub-menu a:hover' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'item_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__menu > li > a:hover, {{WRAPPER}} .teckform-nav__menu > li > a:focus-visible' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'pointer_color', array(
			'label'     => esc_html__( 'Underline Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'pointer' => 'underline' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-nav-pointer: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'item_tab_active', array( 'label' => esc_html__( 'Active', 'teckform' ) ) );

		$this->add_responsive_control( 'item_active_color', array(
			'label'       => esc_html__( 'Color', 'teckform' ),
			'type'        => Controls_Manager::COLOR,
			'description' => esc_html__( 'The page the visitor is on.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-nav__menu > li.current-menu-item > a, {{WRAPPER}} .teckform-nav__menu > li.current-menu-ancestor > a' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'item_active_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__menu > li.current-menu-item > a, {{WRAPPER}} .teckform-nav__menu > li.current-menu-ancestor > a' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control( 'pointer_thickness', array(
			'label'      => esc_html__( 'Underline Thickness', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 1,
					'max' => 8,
				),
			),
			'separator'  => 'before',
			'condition'  => array( 'pointer' => 'underline' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-nav-pointer-height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_dropdown_style_controls() {
		$this->start_controls_section( 'section_style_dropdown', array(
			'label'     => esc_html__( 'Dropdown', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'layout' => 'horizontal' ),
		) );

		$this->add_control( 'dropdown_width', array(
			'label'      => esc_html__( 'Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 120,
					'max' => 500,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__menu .sub-menu' => 'min-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'dropdown_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__menu .sub-menu' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'dropdown_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__menu .sub-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'dropdown_border',
			'selector' => '{{WRAPPER}} .teckform-nav__menu .sub-menu',
		) );

		$this->add_control( 'dropdown_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 30,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__menu .sub-menu' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'dropdown_shadow',
			'selector' => '{{WRAPPER}} .teckform-nav__menu .sub-menu',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'dropdown_typography',
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-nav__menu .sub-menu a',
		) );

		$this->add_responsive_control( 'dropdown_item_padding', array(
			'label'      => esc_html__( 'Item Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__menu .sub-menu a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'dropdown_tabs' );

		$this->start_controls_tab( 'dropdown_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'dropdown_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__menu .sub-menu a, {{WRAPPER}} .teckform-nav__menu .sub-menu .teckform-nav__sub-toggle' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'dropdown_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'dropdown_hover_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__menu .sub-menu a:hover, {{WRAPPER}} .teckform-nav__menu .sub-menu .current-menu-item > a' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'dropdown_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__menu .sub-menu a:hover, {{WRAPPER}} .teckform-nav__menu .sub-menu .current-menu-item > a' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_mega_style_controls() {
		$this->start_controls_section( 'section_style_mega', array(
			'label'     => esc_html__( 'Mega Menu', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'layout' => 'horizontal' ),
		) );

		$this->add_responsive_control( 'mega_custom_width', array(
			'label'      => esc_html__( 'Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 240,
					'max' => 1400,
				),
			),
			'condition'  => array( 'mega_width' => 'custom' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-mega-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'mega_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__mega' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'mega_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__mega' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'mega_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 30,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__mega' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'mega_shadow',
			'selector' => '{{WRAPPER}} .teckform-nav__mega',
		) );

		$this->end_controls_section();
	}

	private function register_mobile_style_controls() {
		$this->start_controls_section( 'section_style_mobile', array(
			'label'     => esc_html__( 'Mobile Menu', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'breakpoint!' => 'none' ),
		) );

		$this->add_control( 'toggle_color', array(
			'label'     => esc_html__( 'Menu Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__toggle'     => 'color: {{VALUE}};',
				'{{WRAPPER}} .teckform-nav__toggle svg' => 'fill: {{VALUE}};',
			),
		) );

		$this->add_control( 'toggle_size', array(
			'label'      => esc_html__( 'Menu Icon Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 12,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__toggle' => 'font-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'panel_width', array(
			'label'      => esc_html__( 'Panel Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'vw' ),
			'range'      => array(
				'px' => array(
					'min' => 200,
					'max' => 600,
				),
				'vw' => array(
					'min' => 30,
					'max' => 100,
				),
			),
			'separator'  => 'before',
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-nav-panel-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'panel_bg', array(
			'label'     => esc_html__( 'Panel Background', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-nav-panel-bg: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'panel_padding', array(
			'label'      => esc_html__( 'Panel Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-nav-panel-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'overlay_color', array(
			'label'     => esc_html__( 'Page Overlay Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav' => '--teckform-nav-overlay: {{VALUE}};',
			),
		) );

		$this->add_control( 'close_color', array(
			'label'     => esc_html__( 'Close Button Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array(
				'{{WRAPPER}} .teckform-nav__close' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'close_size', array(
			'label'      => esc_html__( 'Close Button Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 16,
					'max' => 64,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-nav__close' => 'font-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$menu_id  = isset( $settings['menu'] ) ? absint( $settings['menu'] ) : 0;
		$menu     = $menu_id ? wp_get_nav_menu_object( $menu_id ) : false;

		// A template imported from another site points at a menu ID that doesn't exist here.
		if ( ! $menu ) {
			$menus = wp_get_nav_menus();
			$menu  = $menus ? $menus[0] : false;
		}

		if ( ! $menu ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>' . esc_html__( 'Choose a menu in the widget settings.', 'teckform' ) . '</p>';
			}
			return;
		}

		$layout     = 'vertical' === $settings['layout'] ? 'vertical' : 'horizontal';
		$breakpoint = in_array( $settings['breakpoint'], array( 'tablet', 'mobile', 'none' ), true ) ? $settings['breakpoint'] : 'tablet';
		$has_mobile = 'none' !== $breakpoint;
		$panel_id   = 'teckform-nav-panel-' . $this->get_id();

		// Vertical menus show their submenus inline, so they never need the arrow buttons.
		$arrows = 'horizontal' === $layout && 'yes' === $settings['show_arrows'];

		$this->show_toggles = $arrows || $has_mobile;
		$this->mega_menus   = 'horizontal' === $layout && function_exists( 'teckform_get_mega_menu_id' );

		add_filter( 'walker_nav_menu_start_el', array( $this, 'add_submenu_toggle' ), 10, 2 );
		add_filter( 'nav_menu_css_class', array( $this, 'add_mega_class' ), 10, 2 );

		$menu_html = wp_nav_menu( array(
			'menu'        => $menu,
			'container'   => false,
			'menu_class'  => 'teckform-nav__menu',
			'menu_id'     => 'teckform-menu-' . $this->get_id(),
			'fallback_cb' => '__return_empty_string',
			'echo'        => false,
		) );

		remove_filter( 'walker_nav_menu_start_el', array( $this, 'add_submenu_toggle' ), 10 );
		remove_filter( 'nav_menu_css_class', array( $this, 'add_mega_class' ), 10 );

		$classes = array(
			'teckform-nav',
			'teckform-nav--' . $layout,
			'teckform-nav--break-' . $breakpoint,
		);

		if ( 'underline' === $settings['pointer'] ) {
			$classes[] = 'teckform-nav--pointer-underline';
		}

		if ( ! $arrows ) {
			$classes[] = 'teckform-nav--no-arrows';
		}

		if ( 'left' === $settings['panel_side'] ) {
			$classes[] = 'teckform-nav--panel-left';
		}
		?>
		<nav class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-label="<?php echo esc_attr( $menu->name ); ?>" data-mega-width="<?php echo esc_attr( $settings['mega_width'] ); ?>">
			<?php if ( $has_mobile ) : ?>
				<button type="button" class="teckform-nav__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" aria-label="<?php esc_attr_e( 'Open menu', 'teckform' ); ?>">
					<?php
					if ( ! empty( $settings['toggle_icon']['value'] ) ) {
						Icons_Manager::render_icon( $settings['toggle_icon'], array( 'aria-hidden' => 'true' ) );
					} else {
						echo '<span aria-hidden="true">&#9776;</span>';
					}
					?>
				</button>
				<div class="teckform-nav__overlay"></div>
			<?php endif; ?>

			<div class="teckform-nav__panel" id="<?php echo esc_attr( $panel_id ); ?>">
				<?php if ( $has_mobile ) : ?>
					<button type="button" class="teckform-nav__close" aria-label="<?php esc_attr_e( 'Close menu', 'teckform' ); ?>"><span aria-hidden="true">&times;</span></button>
				<?php endif; ?>
				<?php echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu() output. ?>
			</div>
		</nav>
		<?php
	}

	/**
	 * Add a button after menu items that have a submenu, to open it on touch screens and in the mobile menu.
	 *
	 * @param string   $item_output Menu item HTML.
	 * @param \WP_Post $item        Menu item.
	 * @return string
	 */
	public function add_submenu_toggle( $item_output, $item ) {
		$mega = $this->mega_menus ? teckform_get_mega_menu_id( $item ) : 0;

		if ( ! $mega && ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
			return $item_output;
		}

		if ( $this->show_toggles || $mega ) {
			/* translators: %s: menu item title. */
			$label        = sprintf( __( 'Show submenu for %s', 'teckform' ), wp_strip_all_tags( $item->title ) );
			$item_output .= '<button type="button" class="teckform-nav__sub-toggle" aria-expanded="false" aria-label="' . esc_attr( $label ) . '"></button>';
		}

		if ( $mega ) {
			$item_output .= '<div class="teckform-nav__mega">' . teckform_get_template_content( $mega ) . '</div>';
		}

		return $item_output;
	}

	/**
	 * Mark top-level items that show a mega menu.
	 *
	 * @param string[] $classes Menu item classes.
	 * @param \WP_Post $item    Menu item.
	 * @return string[]
	 */
	public function add_mega_class( $classes, $item ) {
		if ( $this->mega_menus && teckform_get_mega_menu_id( $item ) ) {
			$classes[] = 'teckform-has-mega';
		}

		return $classes;
	}
}
