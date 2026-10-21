<?php
/**
 * Portfolio Grid widget: projects with a category filter bar.
 *
 * Shows Projects (teckform_project) as image tiles. The filter bar is built
 * from the Project Categories used by the shown projects and filters the grid
 * in the browser, without reloading the page. Each tile links to the project
 * page, or to its live URL when that option is on and the project has one.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Portfolio extends Widget_Base {

	/**
	 * Post type and taxonomy the widget reads from (see inc/content-types.php).
	 */
	const POST_TYPE = 'teckform_project';
	const TAXONOMY  = 'teckform_project_cat';

	public function get_name() {
		return 'teckform-portfolio';
	}

	public function get_title() {
		return esc_html__( 'Portfolio Grid', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'portfolio', 'projects', 'work', 'gallery', 'filter', 'grid', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-portfolio' );
	}

	public function get_script_depends() {
		return array( 'teckform-portfolio' );
	}

	/**
	 * Project categories for the category control.
	 *
	 * @return array<string, string> Term ID => name.
	 */
	private function get_term_options() {
		if ( ! taxonomy_exists( self::TAXONOMY ) ) {
			return array();
		}

		$terms = get_terms( array(
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
		) );

		return is_wp_error( $terms ) ? array() : wp_list_pluck( $terms, 'name', 'term_id' );
	}

	/**
	 * Registered image sizes for the image size control.
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
		$this->register_query_controls();
		$this->register_filter_controls();
		$this->register_layout_controls();

		$this->register_grid_style_controls();
		$this->register_filter_style_controls();
		$this->register_item_style_controls();
		$this->register_overlay_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_query_controls() {
		$this->start_controls_section( 'section_query', array(
			'label' => esc_html__( 'Projects', 'teckform' ),
		) );

		$this->add_control( 'posts_per_page', array(
			'label'   => esc_html__( 'Number of Projects', 'teckform' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 100,
			'default' => 9,
		) );

		$this->add_control( 'categories', array(
			'label'       => esc_html__( 'Only These Categories', 'teckform' ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'label_block' => true,
			'options'     => $this->get_term_options(),
			'description' => esc_html__( 'Leave empty to show projects from every category.', 'teckform' ),
		) );

		$this->add_control( 'orderby', array(
			'label'   => esc_html__( 'Order By', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'date',
			'options' => array(
				'date'       => esc_html__( 'Date', 'teckform' ),
				'title'      => esc_html__( 'Title', 'teckform' ),
				'menu_order' => esc_html__( 'Menu order', 'teckform' ),
				'rand'       => esc_html__( 'Random', 'teckform' ),
			),
		) );

		$this->add_control( 'order', array(
			'label'     => esc_html__( 'Order', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'DESC',
			'options'   => array(
				'DESC' => esc_html__( 'Descending', 'teckform' ),
				'ASC'  => esc_html__( 'Ascending', 'teckform' ),
			),
			'condition' => array( 'orderby!' => 'rand' ),
		) );

		$this->end_controls_section();
	}

	private function register_filter_controls() {
		$this->start_controls_section( 'section_filter', array(
			'label' => esc_html__( 'Filter Bar', 'teckform' ),
		) );

		$this->add_control( 'show_filter', array(
			'label'        => esc_html__( 'Show Filter Bar', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Buttons for the categories of the projects shown.', 'teckform' ),
		) );

		$this->add_control( 'all_label', array(
			'label'     => esc_html__( '"All" Button Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'All', 'teckform' ),
			'condition' => array( 'show_filter' => 'yes' ),
		) );

		$this->end_controls_section();
	}

	private function register_layout_controls() {
		$this->start_controls_section( 'section_layout', array(
			'label' => esc_html__( 'Layout', 'teckform' ),
		) );

		$this->add_responsive_control( 'columns', array(
			'label'          => esc_html__( 'Columns', 'teckform' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '3',
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
				'{{WRAPPER}} .teckform-portfolio' => '--teckform-portfolio-columns: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'image_ratio', array(
			'label'                => esc_html__( 'Image Aspect Ratio', 'teckform' ),
			'type'                 => Controls_Manager::SELECT,
			'default'              => '4-3',
			'options'              => array(
				'16-9' => '16:9',
				'4-3'  => '4:3',
				'1-1'  => '1:1',
				'3-4'  => '3:4',
			),
			'selectors_dictionary' => array(
				'16-9' => '16 / 9',
				'4-3'  => '4 / 3',
				'1-1'  => '1 / 1',
				'3-4'  => '3 / 4',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-portfolio' => '--teckform-portfolio-ratio: {{VALUE}};',
			),
		) );

		$this->add_control( 'image_size', array(
			'label'   => esc_html__( 'Image Size', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'medium_large',
			'options' => $this->get_image_size_options(),
		) );

		$this->add_control( 'overlay_style', array(
			'label'     => esc_html__( 'Title and Category', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'hover',
			'separator' => 'before',
			'options'   => array(
				'hover'   => esc_html__( 'Overlay on hover', 'teckform' ),
				'caption' => esc_html__( 'Always visible caption at the bottom', 'teckform' ),
			),
		) );

		$this->add_control( 'show_category', array(
			'label'        => esc_html__( 'Show Category', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
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
			),
		) );

		$this->add_control( 'hover_zoom', array(
			'label'        => esc_html__( 'Zoom Image on Hover', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'link_to', array(
			'label'     => esc_html__( 'Link To', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'page',
			'separator' => 'before',
			'options'   => array(
				'page' => esc_html__( 'Project page', 'teckform' ),
				'live' => esc_html__( 'Live project URL (when set)', 'teckform' ),
			),
		) );

		$this->add_control( 'live_new_tab', array(
			'label'        => esc_html__( 'Open Live URL in New Tab', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => array( 'link_to' => 'live' ),
		) );

		$this->add_control( 'link_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'The live URL is the "Live project URL" field on each project. Projects without one link to their page.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'link_to' => 'live' ),
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
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-portfolio__grid' => 'column-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'row_gap', array(
			'label'      => esc_html__( 'Rows Gap', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-portfolio__grid' => 'row-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_filter_style_controls() {
		$this->start_controls_section( 'section_style_filter', array(
			'label'     => esc_html__( 'Filter Bar', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_filter' => 'yes' ),
		) );

		$this->add_responsive_control( 'filter_align', array(
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
			'selectors'            => array(
				'{{WRAPPER}} .teckform-portfolio__filters' => 'justify-content: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'filter_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 100,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-portfolio__filters' => 'margin-block-end: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'filter_gap', array(
			'label'      => esc_html__( 'Space Between Buttons', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-portfolio__filters' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'filter_typography',
			'selector' => '{{WRAPPER}} .teckform-portfolio__filter',
		) );

		$this->add_responsive_control( 'filter_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-portfolio__filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'filter_radius', array(
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
				'{{WRAPPER}} .teckform-portfolio__filter' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'filter_border_width', array(
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
				'{{WRAPPER}} .teckform-portfolio__filter' => 'border-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'filter_tabs' );

		$states = array(
			'normal' => array( esc_html__( 'Normal', 'teckform' ), '{{WRAPPER}} .teckform-portfolio__filter' ),
			'hover'  => array( esc_html__( 'Hover', 'teckform' ), '{{WRAPPER}} .teckform-portfolio__filter:hover, {{WRAPPER}} .teckform-portfolio__filter:focus-visible' ),
			'active' => array( esc_html__( 'Active', 'teckform' ), '{{WRAPPER}} .teckform-portfolio__filter[aria-pressed="true"]' ),
		);

		foreach ( $states as $state => $info ) {
			list( $label, $selector ) = $info;

			$this->start_controls_tab( 'filter_tab_' . $state, array( 'label' => $label ) );

			$this->add_control( 'filter_' . $state . '_color', array(
				'label'     => esc_html__( 'Text Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'color: {{VALUE}};',
				),
			) );

			$this->add_control( 'filter_' . $state . '_bg', array(
				'label'     => esc_html__( 'Background Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'background-color: {{VALUE}};',
				),
			) );

			$this->add_control( 'filter_' . $state . '_border', array(
				'label'     => esc_html__( 'Border Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'border-color: {{VALUE}};',
				),
			) );

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_item_style_controls() {
		$this->start_controls_section( 'section_style_item', array(
			'label' => esc_html__( 'Item', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'item_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-portfolio__link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'item_tabs' );

		$this->start_controls_tab( 'item_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'item_border',
			'selector' => '{{WRAPPER}} .teckform-portfolio__link',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'item_shadow',
			'selector' => '{{WRAPPER}} .teckform-portfolio__link',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'item_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'item_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-portfolio__link:hover, {{WRAPPER}} .teckform-portfolio__link:focus-visible' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'item_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-portfolio__link:hover, {{WRAPPER}} .teckform-portfolio__link:focus-visible',
		) );

		$this->add_control( 'image_zoom', array(
			'label'     => esc_html__( 'Zoom Amount', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 1,
					'max'  => 1.3,
					'step' => 0.01,
				),
			),
			'condition' => array( 'hover_zoom' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-portfolio' => '--teckform-portfolio-zoom: {{SIZE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_overlay_style_controls() {
		$this->start_controls_section( 'section_style_overlay', array(
			'label' => esc_html__( 'Overlay and Text', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Background::get_type(), array(
			'name'     => 'overlay_bg',
			'types'    => array( 'classic', 'gradient' ),
			'exclude'  => array( 'image' ),
			'selector' => '{{WRAPPER}} .teckform-portfolio__overlay',
		) );

		$this->add_responsive_control( 'overlay_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-portfolio__overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'overlay_position', array(
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
			'condition'            => array( 'overlay_style' => 'hover' ),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-portfolio__overlay' => 'justify-content: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'overlay_align', array(
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
				'end'    => array(
					'title' => esc_html__( 'End', 'teckform' ),
					'icon'  => 'eicon-text-align-right',
				),
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-portfolio__overlay' => 'text-align: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'title_typography',
			'label'     => esc_html__( 'Title Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-portfolio__title',
		) );

		$this->add_control( 'title_color', array(
			'label'     => esc_html__( 'Title Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-portfolio__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'title_spacing', array(
			'label'      => esc_html__( 'Space Below Title', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-portfolio__title' => 'margin-block-end: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'category_typography',
			'label'     => esc_html__( 'Category Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-portfolio__category',
			'condition' => array( 'show_category' => 'yes' ),
		) );

		$this->add_control( 'category_color', array(
			'label'     => esc_html__( 'Category Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'show_category' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-portfolio__category' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = $this->get_project_items( $settings );

		if ( ! $items ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="teckform-portfolio__placeholder">' . esc_html__( 'No projects found. Add some under Projects in the dashboard (with a featured image and a category) and they will appear here.', 'teckform' ) . '</div>';
			}
			return;
		}

		$classes = array(
			'teckform-portfolio',
			'teckform-portfolio--' . ( 'caption' === $settings['overlay_style'] ? 'caption' : 'hover' ),
		);

		if ( 'yes' === $settings['hover_zoom'] ) {
			$classes[] = 'teckform-portfolio--zoom';
		}

		$args = array(
			'title_tag' => Utils::validate_html_tag( $settings['title_tag'] ),
			'size'      => $settings['image_size'],
			'category'  => 'yes' === $settings['show_category'],
			'live'      => 'live' === $settings['link_to'],
			'new_tab'   => 'yes' === $settings['live_new_tab'],
		);

		/* translators: %d: number of projects shown after filtering. */
		$status = __( 'Projects shown: %d', 'teckform' );
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-status="<?php echo esc_attr( $status ); ?>">
			<?php
			if ( 'yes' === $settings['show_filter'] ) {
				$this->render_filters( $items, $settings['all_label'] );
			}
			?>
			<div class="teckform-portfolio__grid">
				<?php
				foreach ( $items as $item ) {
					$this->render_item( $item, $args );
				}
				?>
			</div>
			<p class="teckform-portfolio__status screen-reader-text" aria-live="polite"></p>
		</div>
		<?php
	}

	/**
	 * Projects to show, each with its categories.
	 *
	 * @param array $settings Widget settings.
	 * @return array<int, array{post: \WP_Post, terms: \WP_Term[]}>
	 */
	private function get_project_items( $settings ) {
		if ( ! post_type_exists( self::POST_TYPE ) ) {
			return array();
		}

		$orderby = in_array( $settings['orderby'], array( 'date', 'title', 'menu_order', 'rand' ), true ) ? $settings['orderby'] : 'date';

		$args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => min( 100, max( 1, absint( $settings['posts_per_page'] ) ) ),
			'orderby'        => $orderby,
			'order'          => 'ASC' === $settings['order'] ? 'ASC' : 'DESC',
			'no_found_rows'  => true,
		);

		$terms = array_filter( array_map( 'absint', (array) $settings['categories'] ) );

		if ( $terms && taxonomy_exists( self::TAXONOMY ) ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'term_id',
					'terms'    => $terms,
				),
			);
		}

		$items = array();

		foreach ( get_posts( $args ) as $post ) {
			$post_terms = taxonomy_exists( self::TAXONOMY ) ? get_the_terms( $post, self::TAXONOMY ) : array();

			$items[] = array(
				'post'  => $post,
				'terms' => $post_terms && ! is_wp_error( $post_terms ) ? $post_terms : array(),
			);
		}

		return $items;
	}

	/**
	 * Filter buttons: "All", then every category used by the shown projects, by name.
	 *
	 * @param array  $items     Items from get_project_items().
	 * @param string $all_label Text of the "All" button.
	 */
	private function render_filters( $items, $all_label ) {
		$terms = array();

		foreach ( $items as $item ) {
			foreach ( $item['terms'] as $term ) {
				$terms[ $term->term_id ] = $term->name;
			}
		}

		if ( ! $terms ) {
			return;
		}

		natcasesort( $terms );

		if ( '' === trim( (string) $all_label ) ) {
			$all_label = __( 'All', 'teckform' );
		}
		?>
		<div class="teckform-portfolio__filters" role="group" aria-label="<?php esc_attr_e( 'Filter projects', 'teckform' ); ?>">
			<button type="button" class="teckform-portfolio__filter" data-filter="*" aria-pressed="true"><?php echo esc_html( $all_label ); ?></button>
			<?php foreach ( $terms as $id => $name ) : ?>
				<button type="button" class="teckform-portfolio__filter" data-filter="<?php echo esc_attr( $id ); ?>" aria-pressed="false"><?php echo esc_html( $name ); ?></button>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * One project tile.
	 *
	 * @param array $item Item from get_project_items().
	 * @param array $args Title tag, image size and link options.
	 */
	private function render_item( $item, $args ) {
		$post     = $item['post'];
		$url      = get_permalink( $post );
		$external = false;

		if ( $args['live'] ) {
			$live = get_post_meta( $post->ID, '_teckform_url', true );

			if ( $live ) {
				$url      = $live;
				$external = $args['new_tab'];
			}
		}

		$image = has_post_thumbnail( $post )
			? get_the_post_thumbnail( $post, $args['size'], array( 'class' => 'teckform-portfolio__image' ) )
			: '<span class="teckform-portfolio__no-image" aria-hidden="true"></span>';
		?>
		<article class="teckform-portfolio__item" data-terms="<?php echo esc_attr( implode( ' ', wp_list_pluck( $item['terms'], 'term_id' ) ) ); ?>">
			<a class="teckform-portfolio__link" href="<?php echo esc_url( $url ); ?>"<?php echo $external ? ' target="_blank" rel="noopener"' : ''; ?>>
				<div class="teckform-portfolio__media">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_the_post_thumbnail() or static. ?>
				</div>
				<div class="teckform-portfolio__overlay">
					<?php
					printf(
						'<%1$s class="teckform-portfolio__title">%2$s</%1$s>',
						esc_html( $args['title_tag'] ),
						esc_html( get_the_title( $post ) )
					);

					if ( $args['category'] && $item['terms'] ) {
						echo '<span class="teckform-portfolio__category">' . esc_html( implode( ', ', wp_list_pluck( $item['terms'], 'name' ) ) ) . '</span>';
					}

					if ( $external ) {
						echo '<span class="screen-reader-text">' . esc_html__( '(opens in a new tab)', 'teckform' ) . '</span>';
					}
					?>
				</div>
			</a>
		</article>
		<?php
	}
}
