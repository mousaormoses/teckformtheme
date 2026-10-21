<?php
/**
 * Team widget: people with photo, name, role, short bio and social links.
 *
 * Members come from the Team content type (teckform_member, in menu order)
 * or are typed into the widget. Two card styles: photo on top, or a round
 * photo centered. Social icons are inline SVG and can sit below the text or
 * slide up over the photo on hover.
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
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Team extends Widget_Base {

	/**
	 * Post type the "Team members" source reads from (see inc/content-types.php).
	 */
	const POST_TYPE = 'teckform_member';

	public function get_name() {
		return 'teckform-team';
	}

	public function get_title() {
		return esc_html__( 'Team', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'team', 'members', 'people', 'staff', 'about', 'social', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-team' );
	}

	/**
	 * Social networks in display order, with labels and 24x24 SVG paths.
	 *
	 * @return array<string, array{label: string, path: string}>
	 */
	private function get_networks() {
		return array(
			'facebook'  => array(
				'label' => __( 'Facebook', 'teckform' ),
				'path'  => 'M9.2 21.5h4v-8h3.6l.4-4h-4V7.5a1 1 0 0 1 1-1h3v-4h-3a5 5 0 0 0-5 5v2H7.2l-.4 4h2.4z',
			),
			'x'         => array(
				'label' => __( 'X', 'teckform' ),
				'path'  => 'M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.65l-5.21-6.82-5.97 6.82H1.68l7.73-8.84L1.25 2.25h6.83l4.71 6.23zm-1.16 17.52h1.83L7.08 4.13H5.12z',
			),
			'instagram' => array(
				'label' => __( 'Instagram', 'teckform' ),
				'path'  => 'M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3zm5 3.5a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9zm0 2a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zm5.25-3.25a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5z',
			),
			'linkedin'  => array(
				'label' => __( 'LinkedIn', 'teckform' ),
				'path'  => 'M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.75h4V21H3zm6.5 0h3.8v1.6h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21h-4v-4.95c0-1.18-.02-2.7-1.65-2.7-1.65 0-1.9 1.29-1.9 2.62V21h-4z',
			),
			'email'     => array(
				'label' => __( 'Email', 'teckform' ),
				'path'  => 'M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm1 2.24V17h16V7.24l-8 5.6zM5.76 7 12 11.37 18.24 7z',
			),
		);
	}

	/**
	 * Registered image sizes for the photo size control.
	 *
	 * @return array<string, string>
	 */
	private function get_image_size_options() {
		$options = array();

		foreach ( get_intermediate_image_sizes() as $size ) {
			$options[ $size ] = ucwords( str_replace( array( '_', '-' ), ' ', $size ) );
		}

		$options['full'] = esc_html__( 'Full', 'teckform' );

		return $options;
	}

	protected function register_controls() {
		$this->register_source_controls();
		$this->register_layout_controls();
		$this->register_social_controls();

		$this->register_grid_style_controls();
		$this->register_card_style_controls();
		$this->register_photo_style_controls();
		$this->register_text_style_controls();
		$this->register_social_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_source_controls() {
		$this->start_controls_section( 'section_source', array(
			'label' => esc_html__( 'Members', 'teckform' ),
		) );

		$this->add_control( 'source', array(
			'label'   => esc_html__( 'Source', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'manual',
			'options' => array(
				'cpt'    => esc_html__( 'Team members (dashboard)', 'teckform' ),
				'manual' => esc_html__( 'Manual', 'teckform' ),
			),
		) );

		$this->add_control( 'cpt_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Members are added under Team in the dashboard and shown in their "Order" (Page Attributes). The photo is the featured image, the bio is the content.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'source' => 'cpt' ),
		) );

		$this->add_control( 'members_count', array(
			'label'     => esc_html__( 'Number of Members', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 1,
			'max'       => 100,
			'default'   => 8,
			'condition' => array( 'source' => 'cpt' ),
		) );

		$this->add_control( 'bio_length', array(
			'label'     => esc_html__( 'Bio Length (words)', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 1,
			'max'       => 100,
			'default'   => 22,
			'condition' => array(
				'source'   => 'cpt',
				'show_bio' => 'yes',
			),
		) );

		$this->add_control( 'link_to_page', array(
			'label'        => esc_html__( 'Link Name to Member Page', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'condition'    => array( 'source' => 'cpt' ),
		) );

		$repeater = new Repeater();

		$repeater->add_control( 'photo', array(
			'label'   => esc_html__( 'Photo', 'teckform' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => array( 'url' => Utils::get_placeholder_image_src() ),
		) );

		$repeater->add_control( 'name', array(
			'label'       => esc_html__( 'Name', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'Member name', 'teckform' ),
		) );

		$repeater->add_control( 'role', array(
			'label'       => esc_html__( 'Role', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'Role', 'teckform' ),
		) );

		$repeater->add_control( 'bio', array(
			'label' => esc_html__( 'Short Bio', 'teckform' ),
			'type'  => Controls_Manager::TEXTAREA,
			'rows'  => 3,
		) );

		$repeater->add_control( 'link', array(
			'label'       => esc_html__( 'Name Link', 'teckform' ),
			'type'        => Controls_Manager::URL,
			'dynamic'     => array( 'active' => true ),
			'description' => esc_html__( 'Optional. Makes the name a link.', 'teckform' ),
		) );

		foreach ( $this->get_networks() as $network => $info ) {
			$repeater->add_control( $network, array(
				/* translators: %s: social network name, e.g. LinkedIn. */
				'label'       => 'email' === $network ? esc_html__( 'Email Address', 'teckform' ) : esc_html( sprintf( __( '%s URL', 'teckform' ), $info['label'] ) ),
				'type'        => Controls_Manager::TEXT,
				'input_type'  => 'email' === $network ? 'text' : 'url',
				'label_block' => true,
				'placeholder' => 'email' === $network ? 'name@example.com' : 'https://',
				'separator'   => 'facebook' === $network ? 'before' : 'default',
			) );
		}

		$defaults = array(
			array( esc_html__( 'Sara Ahmed', 'teckform' ), esc_html__( 'Founder and CEO', 'teckform' ) ),
			array( esc_html__( 'Omar Haddad', 'teckform' ), esc_html__( 'Lead Designer', 'teckform' ) ),
			array( esc_html__( 'Lina Saleh', 'teckform' ), esc_html__( 'Senior Developer', 'teckform' ) ),
			array( esc_html__( 'Yusuf Karim', 'teckform' ), esc_html__( 'Marketing Lead', 'teckform' ) ),
		);

		$this->add_control( 'members', array(
			'label'       => esc_html__( 'Members', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ name }}}',
			'condition'   => array( 'source' => 'manual' ),
			'default'     => array_map(
				function ( $member ) {
					return array(
						'name'     => $member[0],
						'role'     => $member[1],
						'bio'      => esc_html__( 'A sentence about what they do and what they love about it.', 'teckform' ),
						'linkedin' => '#',
						'x'        => '#',
					);
				},
				$defaults
			),
		) );

		$this->end_controls_section();
	}

	private function register_layout_controls() {
		$this->start_controls_section( 'section_layout', array(
			'label' => esc_html__( 'Layout', 'teckform' ),
		) );

		$this->add_control( 'card_style', array(
			'label'   => esc_html__( 'Card Style', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'top',
			'options' => array(
				'top'   => esc_html__( 'Photo on top', 'teckform' ),
				'round' => esc_html__( 'Round photo, centered', 'teckform' ),
			),
		) );

		$this->add_responsive_control( 'columns', array(
			'label'          => esc_html__( 'Columns', 'teckform' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '4',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options'        => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
			),
			'selectors'      => array(
				'{{WRAPPER}} .teckform-team' => '--teckform-team-columns: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'photo_ratio', array(
			'label'                => esc_html__( 'Photo Aspect Ratio', 'teckform' ),
			'type'                 => Controls_Manager::SELECT,
			'default'              => '4-5',
			'options'              => array(
				'1-1' => '1:1',
				'4-5' => '4:5',
				'3-4' => '3:4',
				'4-3' => '4:3',
			),
			'selectors_dictionary' => array(
				'1-1' => '1 / 1',
				'4-5' => '4 / 5',
				'3-4' => '3 / 4',
				'4-3' => '4 / 3',
			),
			'condition'            => array( 'card_style' => 'top' ),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-team' => '--teckform-team-ratio: {{VALUE}};',
			),
		) );

		$this->add_control( 'photo_size', array(
			'label'   => esc_html__( 'Photo Image Size', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'medium_large',
			'options' => $this->get_image_size_options(),
		) );

		$this->add_control( 'name_tag', array(
			'label'   => esc_html__( 'Name HTML Tag', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h3',
			'options' => array(
				'h2'  => 'H2',
				'h3'  => 'H3',
				'h4'  => 'H4',
				'h5'  => 'H5',
				'h6'  => 'H6',
				'div' => 'div',
			),
		) );

		$this->add_control( 'show_role', array(
			'label'        => esc_html__( 'Show Role', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );

		$this->add_control( 'show_bio', array(
			'label'        => esc_html__( 'Show Bio', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'photo_zoom', array(
			'label'        => esc_html__( 'Zoom Photo on Hover', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->end_controls_section();
	}

	private function register_social_controls() {
		$this->start_controls_section( 'section_social', array(
			'label' => esc_html__( 'Social Icons', 'teckform' ),
		) );

		$this->add_control( 'show_socials', array(
			'label'        => esc_html__( 'Show Social Icons', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'socials_position', array(
			'label'     => esc_html__( 'Position', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'below',
			'options'   => array(
				'below'   => esc_html__( 'Below the text', 'teckform' ),
				'overlay' => esc_html__( 'Slide up over the photo on hover', 'teckform' ),
			),
			'condition' => array(
				'show_socials' => 'yes',
				'card_style'   => 'top',
			),
		) );

		$this->add_control( 'socials_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Only filled-in links are shown. On touch screens, icons over the photo stay visible.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'show_socials' => 'yes' ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_grid_style_controls() {
		$this->start_controls_section( 'section_style_grid', array(
			'label' => esc_html__( 'Grid', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'column_gap', array(
			'label'      => esc_html__( 'Columns Gap', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 100,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__grid' => 'column-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'row_gap', array(
			'label'      => esc_html__( 'Rows Gap', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 100,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__grid' => 'row-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_card_style_controls() {
		$this->start_controls_section( 'section_style_card', array(
			'label' => esc_html__( 'Card', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'align', array(
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
				'start'  => 'text-align: start; --teckform-team-justify: flex-start;',
				'center' => 'text-align: center; --teckform-team-justify: center;',
				'end'    => 'text-align: end; --teckform-team-justify: flex-end;',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-team__card' => '{{VALUE}}',
			),
		) );

		$this->add_responsive_control( 'card_padding', array(
			'label'      => esc_html__( 'Content Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'card_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'card_tabs' );

		$this->start_controls_tab( 'card_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'card_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-team__card' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'card_border',
			'selector' => '{{WRAPPER}} .teckform-team__card',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_shadow',
			'selector' => '{{WRAPPER}} .teckform-team__card',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'card_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'card_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-team__card:hover' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'card_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-team__card:hover' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-team__card:hover',
		) );

		$this->add_control( 'card_lift', array(
			'label'      => esc_html__( 'Lift Distance', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 24,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team' => '--teckform-team-lift: -{{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_photo_style_controls() {
		$this->start_controls_section( 'section_style_photo', array(
			'label' => esc_html__( 'Photo', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'round_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 48,
					'max' => 320,
				),
			),
			'condition'  => array( 'card_style' => 'round' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team' => '--teckform-team-photo-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'round_spacing', array(
			'label'      => esc_html__( 'Space Above', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'condition'  => array( 'card_style' => 'round' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team--round .teckform-team__photo' => 'margin-block-start: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'photo_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__photo' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'photo_border',
			'selector' => '{{WRAPPER}} .teckform-team__photo',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'photo_shadow',
			'selector' => '{{WRAPPER}} .teckform-team__photo',
		) );

		$this->add_control( 'photo_zoom_amount', array(
			'label'     => esc_html__( 'Zoom Amount', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 1,
					'max'  => 1.3,
					'step' => 0.01,
				),
			),
			'condition' => array( 'photo_zoom' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-team' => '--teckform-team-zoom: {{SIZE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_text_style_controls() {
		$this->start_controls_section( 'section_style_text', array(
			'label' => esc_html__( 'Name, Role and Bio', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$parts = array(
			'name' => array( esc_html__( 'Name', 'teckform' ), '.teckform-team__name', array() ),
			'role' => array( esc_html__( 'Role', 'teckform' ), '.teckform-team__role', array( 'show_role' => 'yes' ) ),
			'bio'  => array( esc_html__( 'Bio', 'teckform' ), '.teckform-team__bio', array( 'show_bio' => 'yes' ) ),
		);

		foreach ( $parts as $part => $info ) {
			list( $label, $class, $condition ) = $info;

			$this->add_group_control( Group_Control_Typography::get_type(), array(
				'name'      => $part . '_typography',
				/* translators: %s: part of the card, e.g. Name. */
				'label'     => sprintf( esc_html__( '%s Typography', 'teckform' ), $label ),
				'separator' => 'name' === $part ? 'default' : 'before',
				'selector'  => '{{WRAPPER}} ' . $class,
				'condition' => $condition,
			) );

			$this->add_control( $part . '_color', array(
				/* translators: %s: part of the card, e.g. Name. */
				'label'     => sprintf( esc_html__( '%s Color', 'teckform' ), $label ),
				'type'      => Controls_Manager::COLOR,
				'condition' => $condition,
				'selectors' => array(
					'{{WRAPPER}} ' . $class => 'color: {{VALUE}};',
				),
			) );

			if ( 'name' === $part ) {
				$this->add_control( 'name_hover_color', array(
					'label'     => esc_html__( 'Name Link Hover Color', 'teckform' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .teckform-team__name a:hover, {{WRAPPER}} .teckform-team__name a:focus-visible' => 'color: {{VALUE}};',
					),
				) );
			}

			$this->add_responsive_control( $part . '_spacing', array(
				/* translators: %s: part of the card, e.g. Name. */
				'label'      => sprintf( esc_html__( 'Space Below %s', 'teckform' ), $label ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'condition'  => $condition,
				'selectors'  => array(
					'{{WRAPPER}} ' . $class => 'margin-block-end: {{SIZE}}{{UNIT}};',
				),
			) );
		}

		$this->end_controls_section();
	}

	private function register_social_style_controls() {
		$this->start_controls_section( 'section_style_social', array(
			'label'     => esc_html__( 'Social Icons', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_socials' => 'yes' ),
		) );

		$this->add_responsive_control( 'social_icon_size', array(
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
				'{{WRAPPER}} .teckform-team__socials' => '--teckform-team-icon: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'social_box_size', array(
			'label'      => esc_html__( 'Button Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 20,
					'max' => 72,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__socials' => '--teckform-team-icon-box: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'social_gap', array(
			'label'      => esc_html__( 'Space Between', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 30,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__socials' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'social_spacing', array(
			'label'      => esc_html__( 'Space Above', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__body .teckform-team__socials' => 'margin-block-start: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'social_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-team__social' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'social_border_width', array(
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
				'{{WRAPPER}} .teckform-team__social' => 'border-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'social_tabs' );

		$states = array(
			'normal' => array( esc_html__( 'Normal', 'teckform' ), '{{WRAPPER}} .teckform-team__social' ),
			'hover'  => array( esc_html__( 'Hover', 'teckform' ), '{{WRAPPER}} .teckform-team__social:hover, {{WRAPPER}} .teckform-team__social:focus-visible' ),
		);

		foreach ( $states as $state => $info ) {
			list( $label, $selector ) = $info;

			$this->start_controls_tab( 'social_tab_' . $state, array( 'label' => $label ) );

			$this->add_control( 'social_' . $state . '_color', array(
				'label'     => esc_html__( 'Icon Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'color: {{VALUE}};',
				),
			) );

			$this->add_control( 'social_' . $state . '_bg', array(
				'label'     => esc_html__( 'Background Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'background-color: {{VALUE}};',
				),
			) );

			$this->add_control( 'social_' . $state . '_border', array(
				'label'     => esc_html__( 'Border Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'border-color: {{VALUE}};',
				),
			) );

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->add_control( 'social_bar_bg', array(
			'label'     => esc_html__( 'Bar Background (over photo)', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'condition' => array(
				'socials_position' => 'overlay',
				'card_style'       => 'top',
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-team__photo .teckform-team__socials' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$members  = 'cpt' === $settings['source'] ? $this->get_cpt_members( $settings ) : $this->get_manual_members( $settings );

		if ( ! $members ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				$message = 'cpt' === $settings['source']
					? __( 'No team members found. Add some under Team in the dashboard, or switch the source to Manual.', 'teckform' )
					: __( 'Add members in the Members list.', 'teckform' );

				echo '<div class="teckform-team__placeholder">' . esc_html( $message ) . '</div>';
			}
			return;
		}

		$style   = 'round' === $settings['card_style'] ? 'round' : 'top';
		$overlay = 'top' === $style && 'yes' === $settings['show_socials'] && 'overlay' === $settings['socials_position'];
		$classes = array( 'teckform-team', 'teckform-team--' . $style );

		if ( $overlay ) {
			$classes[] = 'teckform-team--socials-overlay';
		}

		if ( 'yes' === $settings['photo_zoom'] ) {
			$classes[] = 'teckform-team--zoom';
		}

		$args = array(
			'name_tag' => Utils::validate_html_tag( $settings['name_tag'] ),
			'role'     => 'yes' === $settings['show_role'],
			'bio'      => 'yes' === $settings['show_bio'],
			'socials'  => 'yes' === $settings['show_socials'],
			'overlay'  => $overlay,
		);
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<div class="teckform-team__grid">
				<?php
				foreach ( $members as $index => $member ) {
					$this->render_member( $member, $args, $index );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Members from the Team content type, in menu order.
	 *
	 * @param array $settings Widget settings.
	 * @return array[] Normalized members (see render_member()).
	 */
	private function get_cpt_members( $settings ) {
		if ( ! post_type_exists( self::POST_TYPE ) ) {
			return array();
		}

		$posts = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => min( 100, max( 1, absint( $settings['members_count'] ) ) ),
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		) );

		$members = array();
		$link    = 'yes' === $settings['link_to_page'] && is_post_type_viewable( self::POST_TYPE );
		$size    = $settings['photo_size'];

		foreach ( $posts as $post ) {
			$name    = get_the_title( $post );
			$bio     = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $post->post_content ) ) ) ) );
			$socials = function_exists( 'teckform_member_socials' ) ? teckform_member_socials( $post->ID ) : array();

			$socials['email'] = get_post_meta( $post->ID, '_teckform_email', true );

			$members[] = array(
				'name'    => $name,
				'role'    => get_post_meta( $post->ID, '_teckform_role', true ),
				'bio'     => '' === $bio ? '' : wp_trim_words( $bio, max( 1, absint( $settings['bio_length'] ) ), '&hellip;' ),
				'url'     => $link ? get_permalink( $post ) : '',
				'link'    => array(),
				'photo'   => has_post_thumbnail( $post ) ? get_the_post_thumbnail( $post, $size, array( 'alt' => $name ) ) : '',
				'socials' => $socials,
			);
		}

		return $members;
	}

	/**
	 * Members typed into the widget.
	 *
	 * @param array $settings Widget settings.
	 * @return array[] Normalized members (see render_member()).
	 */
	private function get_manual_members( $settings ) {
		$members = array();

		foreach ( (array) $settings['members'] as $item ) {
			if ( '' === trim( (string) $item['name'] ) && empty( $item['photo']['url'] ) ) {
				continue;
			}

			$socials = array();

			foreach ( array_keys( $this->get_networks() ) as $network ) {
				$socials[ $network ] = isset( $item[ $network ] ) ? $item[ $network ] : '';
			}

			$members[] = array(
				'name'    => $item['name'],
				'role'    => $item['role'],
				'bio'     => $item['bio'],
				'url'     => '',
				'link'    => ! empty( $item['link']['url'] ) ? $item['link'] : array(),
				'photo'   => $this->get_photo_html( $item['photo'], $settings['photo_size'], $item['name'] ),
				'socials' => $socials,
			);
		}

		return $members;
	}

	/**
	 * One member card.
	 *
	 * @param array $member {
	 *     Normalized member.
	 *
	 *     @type string $name    Name.
	 *     @type string $role    Role.
	 *     @type string $bio     Short bio (plain text).
	 *     @type string $url     Member page URL, or empty.
	 *     @type array  $link    Elementor URL control value, or empty.
	 *     @type string $photo   Escaped <img> markup, or empty.
	 *     @type array  $socials Network => URL (or email address).
	 * }
	 * @param array $args   Display options from render().
	 * @param int   $index  Position, for unique render attribute keys.
	 */
	private function render_member( $member, $args, $index ) {
		$socials = $args['socials'] ? $this->get_socials_html( $member['socials'], $member['name'] ) : '';
		?>
		<article class="teckform-team__card">
			<div class="teckform-team__photo">
				<?php
				if ( $member['photo'] ) {
					echo $member['photo']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped when built.
				} else {
					echo '<span class="teckform-team__initials" aria-hidden="true">' . esc_html( $this->get_initials( $member['name'] ) ) . '</span>';
				}

				if ( $args['overlay'] ) {
					echo $socials; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_socials_html().
				}
				?>
			</div>
			<div class="teckform-team__body">
				<?php
				if ( '' !== trim( (string) $member['name'] ) ) {
					$name = esc_html( $member['name'] );

					if ( $member['url'] ) {
						$name = '<a href="' . esc_url( $member['url'] ) . '">' . $name . '</a>';
					} elseif ( $member['link'] ) {
						$key = 'member_link_' . $index;

						$this->add_link_attributes( $key, $member['link'] );
						$name = '<a ' . $this->get_render_attribute_string( $key ) . '>' . $name . '</a>';
					}

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
					echo '<' . esc_html( $args['name_tag'] ) . ' class="teckform-team__name">' . $name . '</' . esc_html( $args['name_tag'] ) . '>';
				}

				if ( $args['role'] && '' !== trim( (string) $member['role'] ) ) {
					echo '<p class="teckform-team__role">' . esc_html( $member['role'] ) . '</p>';
				}

				if ( $args['bio'] && '' !== trim( (string) $member['bio'] ) ) {
					echo '<p class="teckform-team__bio">' . esc_html( $member['bio'] ) . '</p>';
				}

				if ( ! $args['overlay'] ) {
					echo $socials; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_socials_html().
				}
				?>
			</div>
		</article>
		<?php
	}

	/**
	 * Social icon list. Only filled-in links are shown.
	 *
	 * @param array  $links Network => URL (email may be a plain address).
	 * @param string $name  Member name, for the link labels.
	 * @return string
	 */
	private function get_socials_html( $links, $name ) {
		$html = '';

		foreach ( $this->get_networks() as $network => $info ) {
			$value = isset( $links[ $network ] ) ? trim( (string) $links[ $network ] ) : '';
			$url   = 'email' === $network ? $this->get_email_url( $value ) : $value;

			if ( '' === $url ) {
				continue;
			}

			$label = '' !== trim( (string) $name )
				/* translators: 1: social network name, 2: team member name. */
				? sprintf( __( '%1$s: %2$s', 'teckform' ), $info['label'], $name )
				: $info['label'];

			$html .= sprintf(
				'<li><a class="teckform-team__social teckform-team__social--%1$s" href="%2$s" aria-label="%3$s"%4$s><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="%5$s"/></svg></a></li>',
				esc_attr( $network ),
				esc_url( $url ),
				esc_attr( $label ),
				'email' === $network || '#' === $url ? '' : ' target="_blank" rel="noopener"',
				esc_attr( $info['path'] )
			);
		}

		return $html ? '<ul class="teckform-team__socials">' . $html . '</ul>' : '';
	}

	/**
	 * mailto: link for an email address (with or without "mailto:").
	 *
	 * @param string $value Address.
	 * @return string Empty when it isn't a valid address.
	 */
	private function get_email_url( $value ) {
		$email = preg_replace( '/^mailto:/i', '', $value );

		return is_email( $email ) ? 'mailto:' . $email : '';
	}

	/**
	 * <img> markup for a photo from the Media control.
	 *
	 * @param array  $media Value with 'id' and/or 'url'.
	 * @param string $size  Image size.
	 * @param string $alt   Alternative text.
	 * @return string
	 */
	private function get_photo_html( $media, $size, $alt ) {
		if ( ! empty( $media['id'] ) ) {
			$html = wp_get_attachment_image( absint( $media['id'] ), $size, false, array( 'alt' => $alt ) );

			if ( $html ) {
				return $html;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img src="' . esc_url( $media['url'] ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy">';
		}

		return '';
	}

	/**
	 * Up to two initials, shown when a member has no photo.
	 *
	 * @param string $name Name.
	 * @return string
	 */
	private function get_initials( $name ) {
		$initials = '';

		foreach ( array_slice( preg_split( '/\s+/u', trim( (string) $name ), -1, PREG_SPLIT_NO_EMPTY ), 0, 2 ) as $word ) {
			$initials .= function_exists( 'mb_substr' ) ? mb_substr( $word, 0, 1 ) : substr( $word, 0, 1 );
		}

		return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $initials ) : strtoupper( $initials );
	}
}
