<?php
/**
 * Posts Grid widget: posts, services, projects or any public post type as cards.
 *
 * Two card layouts (image on top, or image behind the text), category badge,
 * date, author, excerpt and read-more text. Numbered pagination uses its own
 * query argument per widget (?tfpg-{widget id}=2), so several grids can share
 * one page and it also works on a static front page.
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

class Posts_Grid extends Widget_Base {

	public function get_name() {
		return 'teckform-posts-grid';
	}

	public function get_title() {
		return esc_html__( 'Posts Grid', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'posts', 'blog', 'grid', 'news', 'articles', 'services', 'projects', 'cards', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-posts-grid' );
	}

	/**
	 * Post types that can be shown: every public type except attachments and
	 * Elementor's own templates.
	 *
	 * @return array<string, string> Post type => label.
	 */
	private function get_post_type_options() {
		$options = array();
		$skip    = array( 'attachment', 'elementor_library', 'e-landing-page', 'e-floating-buttons' );

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			if ( ! in_array( $type->name, $skip, true ) ) {
				$options[ $type->name ] = $type->labels->name;
			}
		}

		return $options ? $options : array( 'post' => esc_html__( 'Posts', 'teckform' ) );
	}

	/**
	 * Post types that have a category filter, with the control and taxonomy for each.
	 *
	 * @return array<string, array{0: string, 1: string}> Post type => [control ID, taxonomy].
	 */
	private function get_category_filters() {
		return array(
			'post'             => array( 'post_categories', 'category' ),
			'teckform_project' => array( 'project_categories', 'teckform_project_cat' ),
		);
	}

	/**
	 * Terms of a taxonomy for a SELECT2 control.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return array<string, string> Term ID => name.
	 */
	private function get_term_options( $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		) );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		return wp_list_pluck( $terms, 'name', 'term_id' );
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

	/**
	 * Aspect ratio choices shared by the image ratio control.
	 *
	 * @return array{options: array<string, string>, css: array<string, string>}
	 */
	private function get_ratios() {
		return array(
			'options' => array(
				'16-9' => '16:9',
				'4-3'  => '4:3',
				'1-1'  => '1:1',
				'3-4'  => '3:4',
			),
			'css'     => array(
				'16-9' => '16 / 9',
				'4-3'  => '4 / 3',
				'1-1'  => '1 / 1',
				'3-4'  => '3 / 4',
			),
		);
	}

	protected function register_controls() {
		$this->register_query_controls();
		$this->register_layout_controls();
		$this->register_card_content_controls();
		$this->register_pagination_controls();

		$this->register_grid_style_controls();
		$this->register_card_style_controls();
		$this->register_image_style_controls();
		$this->register_badge_style_controls();
		$this->register_text_style_controls();
		$this->register_read_more_style_controls();
		$this->register_pagination_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_query_controls() {
		$this->start_controls_section( 'section_query', array(
			'label' => esc_html__( 'Query', 'teckform' ),
		) );

		$this->add_control( 'post_type', array(
			'label'   => esc_html__( 'Source', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'post',
			'options' => $this->get_post_type_options(),
		) );

		foreach ( $this->get_category_filters() as $post_type => $filter ) {
			list( $control, $taxonomy ) = $filter;

			$this->add_control( $control, array(
				'label'       => esc_html__( 'Categories', 'teckform' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->get_term_options( $taxonomy ),
				'description' => esc_html__( 'Leave empty to show all categories.', 'teckform' ),
				'condition'   => array( 'post_type' => $post_type ),
			) );
		}

		$this->add_control( 'posts_per_page', array(
			'label'   => esc_html__( 'Number of Items', 'teckform' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 100,
			'default' => 6,
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

		$this->add_control( 'exclude_current', array(
			'label'        => esc_html__( 'Skip Current Post', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'On a single post page, leave that post out of the grid.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	private function register_layout_controls() {
		$this->start_controls_section( 'section_layout', array(
			'label' => esc_html__( 'Layout', 'teckform' ),
		) );

		$this->add_control( 'card_layout', array(
			'label'   => esc_html__( 'Card Layout', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'classic',
			'options' => array(
				'classic' => esc_html__( 'Image on top', 'teckform' ),
				'overlay' => esc_html__( 'Image as background, text on top', 'teckform' ),
			),
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
			),
			'selectors'      => array(
				'{{WRAPPER}} .teckform-posts-grid' => '--teckform-posts-grid-columns: {{VALUE}};',
			),
		) );

		$ratios = $this->get_ratios();

		$this->add_responsive_control( 'image_ratio', array(
			'label'                => esc_html__( 'Image Aspect Ratio', 'teckform' ),
			'type'                 => Controls_Manager::SELECT,
			'default'              => '16-9',
			'options'              => $ratios['options'],
			'selectors_dictionary' => $ratios['css'],
			'selectors'            => array(
				'{{WRAPPER}} .teckform-posts-grid' => '--teckform-posts-grid-ratio: {{VALUE}};',
			),
		) );

		$this->add_control( 'image_size', array(
			'label'   => esc_html__( 'Image Size', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'medium_large',
			'options' => $this->get_image_size_options(),
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

		$this->add_control( 'hover_lift', array(
			'label'        => esc_html__( 'Lift Card on Hover', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );

		$this->add_control( 'hover_zoom', array(
			'label'        => esc_html__( 'Zoom Image on Hover', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->end_controls_section();
	}

	private function register_card_content_controls() {
		$this->start_controls_section( 'section_card', array(
			'label' => esc_html__( 'Card Content', 'teckform' ),
		) );

		$this->add_control( 'show_image', array(
			'label'        => esc_html__( 'Image', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'show_badge', array(
			'label'        => esc_html__( 'Category Badge', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'badge_position', array(
			'label'     => esc_html__( 'Badge Position', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'image',
			'options'   => array(
				'image' => esc_html__( 'Over the image', 'teckform' ),
				'text'  => esc_html__( 'Above the title', 'teckform' ),
			),
			'condition' => array(
				'show_badge'  => 'yes',
				'show_image'  => 'yes',
				'card_layout' => 'classic',
			),
		) );

		$this->add_control( 'show_date', array(
			'label'        => esc_html__( 'Date', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'show_author', array(
			'label'        => esc_html__( 'Author', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
		) );

		$this->add_control( 'show_excerpt', array(
			'label'        => esc_html__( 'Excerpt', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'excerpt_length', array(
			'label'     => esc_html__( 'Excerpt Length (words)', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 1,
			'max'       => 100,
			'default'   => 18,
			'condition' => array( 'show_excerpt' => 'yes' ),
		) );

		$this->add_control( 'read_more_text', array(
			'label'       => esc_html__( 'Read More Text', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Read more', 'teckform' ),
			'description' => esc_html__( 'Leave empty to hide. The whole card is a link either way.', 'teckform' ),
			'separator'   => 'before',
		) );

		$this->end_controls_section();
	}

	private function register_pagination_controls() {
		$this->start_controls_section( 'section_pagination', array(
			'label' => esc_html__( 'Pagination', 'teckform' ),
		) );

		$this->add_control( 'pagination', array(
			'label'   => esc_html__( 'Pagination', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'none',
			'options' => array(
				'none'    => esc_html__( 'None', 'teckform' ),
				'numbers' => esc_html__( 'Numbered links', 'teckform' ),
			),
		) );

		$this->add_control( 'pagination_arrows', array(
			'label'        => esc_html__( 'Previous and Next Arrows', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => array( 'pagination' => 'numbers' ),
		) );

		$this->add_control( 'pagination_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Each grid uses its own page number in the address, so several grids on one page (and on the front page) page independently. Random order is not stable between pages.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'pagination' => 'numbers' ),
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
				'{{WRAPPER}} .teckform-posts-grid__items' => 'column-gap: {{SIZE}}{{UNIT}};',
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
				'{{WRAPPER}} .teckform-posts-grid__items' => 'row-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_card_style_controls() {
		$this->start_controls_section( 'section_style_card', array(
			'label' => esc_html__( 'Card', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'card_align', array(
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
				'start'  => 'text-align: start; --teckform-posts-grid-justify: flex-start;',
				'center' => 'text-align: center; --teckform-posts-grid-justify: center;',
				'end'    => 'text-align: end; --teckform-posts-grid-justify: flex-end;',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-posts-grid__body' => '{{VALUE}}',
			),
		) );

		$this->add_responsive_control( 'card_padding', array(
			'label'      => esc_html__( 'Content Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'card_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'card_tabs' );

		$this->start_controls_tab( 'card_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'card_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__card' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'card_border',
			'selector' => '{{WRAPPER}} .teckform-posts-grid__card',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_shadow',
			'selector' => '{{WRAPPER}} .teckform-posts-grid__card',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'card_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'card_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__card:hover' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'card_hover_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__card:hover' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'card_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-posts-grid__card:hover',
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
			'condition'  => array( 'hover_lift' => 'yes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid' => '--teckform-posts-grid-lift: -{{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control( 'overlay_heading', array(
			'label'     => esc_html__( 'Overlay', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => array( 'card_layout' => 'overlay' ),
		) );

		$this->add_group_control( Group_Control_Background::get_type(), array(
			'name'      => 'overlay_bg',
			'types'     => array( 'classic', 'gradient' ),
			'exclude'   => array( 'image' ),
			'selector'  => '{{WRAPPER}} .teckform-posts-grid__overlay',
			'condition' => array( 'card_layout' => 'overlay' ),
		) );

		$this->add_control( 'overlay_opacity', array(
			'label'     => esc_html__( 'Opacity', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 0,
					'max'  => 1,
					'step' => 0.05,
				),
			),
			'condition' => array( 'card_layout' => 'overlay' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__overlay' => 'opacity: {{SIZE}};',
			),
		) );

		$this->add_control( 'overlay_hover_opacity', array(
			'label'     => esc_html__( 'Opacity on Hover', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 0,
					'max'  => 1,
					'step' => 0.05,
				),
			),
			'condition' => array( 'card_layout' => 'overlay' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__card:hover .teckform-posts-grid__overlay' => 'opacity: {{SIZE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_image_style_controls() {
		$this->start_controls_section( 'section_style_image', array(
			'label'     => esc_html__( 'Image', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_image' => 'yes' ),
		) );

		$this->add_responsive_control( 'image_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'condition'  => array( 'card_layout' => 'classic' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'image_margin', array(
			'label'      => esc_html__( 'Margin', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'condition'  => array( 'card_layout' => 'classic' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__media' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
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
				'{{WRAPPER}} .teckform-posts-grid' => '--teckform-posts-grid-zoom: {{SIZE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_badge_style_controls() {
		$this->start_controls_section( 'section_style_badge', array(
			'label'     => esc_html__( 'Category Badge', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_badge' => 'yes' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'badge_typography',
			'selector' => '{{WRAPPER}} .teckform-posts-grid__badge',
		) );

		$this->add_responsive_control( 'badge_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'badge_radius', array(
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
				'{{WRAPPER}} .teckform-posts-grid__badge' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'badge_tabs' );

		$this->start_controls_tab( 'badge_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'badge_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__badge' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'badge_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__badge' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'badge_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'badge_hover_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__badge:hover, {{WRAPPER}} .teckform-posts-grid__badge:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'badge_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__badge:hover, {{WRAPPER}} .teckform-posts-grid__badge:focus-visible' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_text_style_controls() {
		$this->start_controls_section( 'section_style_text', array(
			'label' => esc_html__( 'Title, Meta and Excerpt', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'title_typography',
			'label'    => esc_html__( 'Title Typography', 'teckform' ),
			'selector' => '{{WRAPPER}} .teckform-posts-grid__title',
		) );

		$this->start_controls_tabs( 'title_tabs' );

		$this->start_controls_tab( 'title_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'title_color', array(
			'label'     => esc_html__( 'Title Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__title, {{WRAPPER}} .teckform-posts-grid__link' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'title_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'title_hover_color', array(
			'label'     => esc_html__( 'Title Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__card:hover .teckform-posts-grid__link, {{WRAPPER}} .teckform-posts-grid__link:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control( 'title_spacing', array(
			'label'      => esc_html__( 'Space Below Title', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__title' => 'margin-block-end: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'meta_typography',
			'label'     => esc_html__( 'Meta Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-posts-grid__meta',
		) );

		$this->add_control( 'meta_color', array(
			'label'     => esc_html__( 'Meta Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__meta' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'meta_spacing', array(
			'label'      => esc_html__( 'Space Below Meta', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__meta' => 'margin-block-end: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'excerpt_typography',
			'label'     => esc_html__( 'Excerpt Typography', 'teckform' ),
			'separator' => 'before',
			'selector'  => '{{WRAPPER}} .teckform-posts-grid__excerpt',
			'condition' => array( 'show_excerpt' => 'yes' ),
		) );

		$this->add_control( 'excerpt_color', array(
			'label'     => esc_html__( 'Excerpt Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'show_excerpt' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__excerpt' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'excerpt_spacing', array(
			'label'      => esc_html__( 'Space Below Excerpt', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'condition'  => array( 'show_excerpt' => 'yes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__excerpt' => 'margin-block-end: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_read_more_style_controls() {
		$this->start_controls_section( 'section_style_read_more', array(
			'label'     => esc_html__( 'Read More', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'read_more_text!' => '' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'read_more_typography',
			'selector' => '{{WRAPPER}} .teckform-posts-grid__read-more',
		) );

		$this->start_controls_tabs( 'read_more_tabs' );

		$this->start_controls_tab( 'read_more_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'read_more_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__read-more' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'read_more_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'read_more_hover_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-posts-grid__card:hover .teckform-posts-grid__read-more, {{WRAPPER}} .teckform-posts-grid__card:focus-within .teckform-posts-grid__read-more' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_pagination_style_controls() {
		$this->start_controls_section( 'section_style_pagination', array(
			'label'     => esc_html__( 'Pagination', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'pagination' => 'numbers' ),
		) );

		$this->add_responsive_control( 'pagination_align', array(
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
				'{{WRAPPER}} .teckform-posts-grid__pagination' => 'justify-content: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'pagination_spacing', array(
			'label'      => esc_html__( 'Space Above', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 120,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__pagination' => 'margin-block-start: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'pagination_gap', array(
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
				'{{WRAPPER}} .teckform-posts-grid__pagination' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'pagination_typography',
			'selector' => '{{WRAPPER}} .teckform-posts-grid__page',
		) );

		$this->add_responsive_control( 'pagination_size', array(
			'label'      => esc_html__( 'Button Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 24,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-posts-grid__page' => 'min-width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'pagination_radius', array(
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
				'{{WRAPPER}} .teckform-posts-grid__page' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'pagination_tabs' );

		$states = array(
			'normal' => array( esc_html__( 'Normal', 'teckform' ), '{{WRAPPER}} .teckform-posts-grid__page' ),
			'hover'  => array( esc_html__( 'Hover', 'teckform' ), '{{WRAPPER}} a.teckform-posts-grid__page:hover, {{WRAPPER}} a.teckform-posts-grid__page:focus-visible' ),
			'active' => array( esc_html__( 'Active', 'teckform' ), '{{WRAPPER}} .teckform-posts-grid__page.is-current' ),
		);

		foreach ( $states as $state => $info ) {
			list( $label, $selector ) = $info;

			$this->start_controls_tab( 'pagination_tab_' . $state, array( 'label' => $label ) );

			$this->add_control( 'pagination_' . $state . '_color', array(
				'label'     => esc_html__( 'Text Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'color: {{VALUE}};',
				),
			) );

			$this->add_control( 'pagination_' . $state . '_bg', array(
				'label'     => esc_html__( 'Background Color', 'teckform' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'background-color: {{VALUE}};',
				),
			) );

			$this->add_control( 'pagination_' . $state . '_border', array(
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

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$post_type = $this->get_post_type( $settings );
		$paginate  = 'numbers' === $settings['pagination'];
		$page_key  = 'tfpg-' . $this->get_id();
		$page      = $paginate ? $this->get_current_page( $page_key ) : 1;
		$query     = new \WP_Query( $this->get_query_args( $settings, $post_type, $page, $paginate ) );

		if ( ! $query->have_posts() ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="teckform-posts-grid__placeholder">' . esc_html__( 'No items found. Publish some content of the chosen type, or change the categories in the Query section.', 'teckform' ) . '</div>';
			}
			return;
		}

		$layout  = 'overlay' === $settings['card_layout'] ? 'overlay' : 'classic';
		$classes = array( 'teckform-posts-grid', 'teckform-posts-grid--' . $layout );

		if ( 'yes' === $settings['hover_lift'] ) {
			$classes[] = 'teckform-posts-grid--lift';
		}

		if ( 'yes' === $settings['hover_zoom'] ) {
			$classes[] = 'teckform-posts-grid--zoom';
		}

		$args = array(
			'layout'    => $layout,
			'title_tag' => Utils::validate_html_tag( $settings['title_tag'] ),
			'taxonomy'  => 'yes' === $settings['show_badge'] ? $this->get_badge_taxonomy( $post_type ) : '',
		);
		?>
		<div id="<?php echo esc_attr( $page_key ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<div class="teckform-posts-grid__items">
				<?php
				foreach ( $query->posts as $post ) {
					$this->render_card( $post, $settings, $args );
				}
				?>
			</div>
			<?php
			if ( $paginate ) {
				$this->render_pagination( (int) $query->max_num_pages, $page, $page_key, 'yes' === $settings['pagination_arrows'] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Chosen post type, falling back to posts if it no longer exists.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function get_post_type( $settings ) {
		$type = (string) $settings['post_type'];

		return post_type_exists( $type ) && is_post_type_viewable( $type ) ? $type : 'post';
	}

	/**
	 * Page number for this grid, read from its own query argument.
	 *
	 * @param string $key Query argument name.
	 * @return int
	 */
	private function get_current_page( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only page number.
		return isset( $_GET[ $key ] ) ? max( 1, absint( wp_unslash( $_GET[ $key ] ) ) ) : 1;
	}

	/**
	 * WP_Query arguments built from the Query section.
	 *
	 * @param array  $settings  Widget settings.
	 * @param string $post_type Post type.
	 * @param int    $page      Page number.
	 * @param bool   $paginate  Whether the total page count is needed.
	 * @return array
	 */
	private function get_query_args( $settings, $post_type, $page, $paginate ) {
		$orderby = in_array( $settings['orderby'], array( 'date', 'title', 'menu_order', 'rand' ), true ) ? $settings['orderby'] : 'date';

		$args = array(
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => min( 100, max( 1, absint( $settings['posts_per_page'] ) ) ),
			'paged'               => $page,
			'orderby'             => $orderby,
			'order'               => 'ASC' === $settings['order'] ? 'ASC' : 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => ! $paginate,
		);

		if ( 'yes' === $settings['exclude_current'] && is_singular() && get_queried_object_id() ) {
			$args['post__not_in'] = array( get_queried_object_id() );
		}

		$filters = $this->get_category_filters();

		if ( isset( $filters[ $post_type ] ) ) {
			list( $control, $taxonomy ) = $filters[ $post_type ];

			$terms = array_filter( array_map( 'absint', (array) $settings[ $control ] ) );

			if ( $terms ) {
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $terms,
					),
				);
			}
		}

		return $args;
	}

	/**
	 * Taxonomy used for the badge: the category filter's taxonomy, or the first
	 * public hierarchical taxonomy of the post type.
	 *
	 * @param string $post_type Post type.
	 * @return string Empty when the type has none.
	 */
	private function get_badge_taxonomy( $post_type ) {
		$filters = $this->get_category_filters();

		if ( isset( $filters[ $post_type ] ) ) {
			return $filters[ $post_type ][1];
		}

		foreach ( get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) {
			if ( $taxonomy->public && $taxonomy->hierarchical ) {
				return $taxonomy->name;
			}
		}

		return '';
	}

	/**
	 * One card. The title link stretches over the whole card, so the card is
	 * clickable while the badge stays a separate link.
	 *
	 * @param \WP_Post $post     Post.
	 * @param array    $settings Widget settings.
	 * @param array    $args     Layout, title tag and badge taxonomy.
	 */
	private function render_card( $post, $settings, $args ) {
		$image = '';

		if ( 'yes' === $settings['show_image'] && has_post_thumbnail( $post ) ) {
			$image = get_the_post_thumbnail( $post, $settings['image_size'], array( 'class' => 'teckform-posts-grid__image' ) );
		}

		$badge          = $args['taxonomy'] ? $this->get_badge_html( $post, $args['taxonomy'] ) : '';
		$badge_on_image = $badge && $image && 'classic' === $args['layout'] && 'image' === $settings['badge_position'];
		$classes        = 'teckform-posts-grid__card' . ( $image ? '' : ' teckform-posts-grid__card--no-image' );
		?>
		<article class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( $image ) : ?>
				<div class="teckform-posts-grid__media">
					<?php
					echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_the_post_thumbnail().

					if ( $badge_on_image ) {
						echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_badge_html().
					}
					?>
				</div>
			<?php endif; ?>
			<?php if ( 'overlay' === $args['layout'] ) : ?>
				<div class="teckform-posts-grid__overlay" aria-hidden="true"></div>
			<?php endif; ?>
			<div class="teckform-posts-grid__body">
				<?php
				if ( $badge && ! $badge_on_image ) {
					echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_badge_html().
				}

				printf(
					'<%1$s class="teckform-posts-grid__title"><a class="teckform-posts-grid__link" href="%2$s">%3$s</a></%1$s>',
					esc_html( $args['title_tag'] ),
					esc_url( get_permalink( $post ) ),
					esc_html( get_the_title( $post ) )
				);

				$this->render_meta( $post, $settings );

				if ( 'yes' === $settings['show_excerpt'] ) {
					$excerpt = $this->get_excerpt( $post, absint( $settings['excerpt_length'] ) );

					if ( '' !== $excerpt ) {
						echo '<p class="teckform-posts-grid__excerpt">' . esc_html( $excerpt ) . '</p>';
					}
				}

				if ( '' !== trim( (string) $settings['read_more_text'] ) ) {
					// Not a second link: the stretched title link already covers the card.
					echo '<span class="teckform-posts-grid__read-more" aria-hidden="true">' . esc_html( $settings['read_more_text'] ) . '<svg viewBox="0 0 24 24"><path d="M13.2 5.3l-1.4 1.4 4.3 4.3H4v2h12.1l-4.3 4.3 1.4 1.4 6.7-6.7z"/></svg></span>';
				}
				?>
			</div>
		</article>
		<?php
	}

	/**
	 * Category badge: the post's first term, skipping "Uncategorized".
	 *
	 * @param \WP_Post $post     Post.
	 * @param string   $taxonomy Taxonomy.
	 * @return string
	 */
	private function get_badge_html( $post, $taxonomy ) {
		$terms = get_the_terms( $post, $taxonomy );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}

		$default = 'category' === $taxonomy ? (int) get_option( 'default_category' ) : 0;
		$term    = null;

		foreach ( $terms as $candidate ) {
			if ( (int) $candidate->term_id !== $default ) {
				$term = $candidate;
				break;
			}
		}

		if ( ! $term ) {
			return '';
		}

		$link = get_term_link( $term );

		if ( is_wp_error( $link ) ) {
			return '<span class="teckform-posts-grid__badge">' . esc_html( $term->name ) . '</span>';
		}

		return '<a class="teckform-posts-grid__badge" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
	}

	/**
	 * Author and date line.
	 *
	 * @param \WP_Post $post     Post.
	 * @param array    $settings Widget settings.
	 */
	private function render_meta( $post, $settings ) {
		$items = array();

		if ( 'yes' === $settings['show_author'] ) {
			$items[] = '<span class="teckform-posts-grid__author">' . esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) . '</span>';
		}

		if ( 'yes' === $settings['show_date'] ) {
			$items[] = '<time class="teckform-posts-grid__date" datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( '', $post ) ) . '</time>';
		}

		if ( $items ) {
			echo '<div class="teckform-posts-grid__meta">' . implode( '', $items ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
		}
	}

	/**
	 * Plain-text excerpt. Reads the stored excerpt or content directly instead of
	 * get_the_excerpt(), which would render Elementor content (and possibly this
	 * widget again) just to cut it down.
	 *
	 * @param \WP_Post $post   Post.
	 * @param int      $length Number of words.
	 * @return string
	 */
	private function get_excerpt( $post, $length ) {
		$text = has_excerpt( $post ) ? $post->post_excerpt : strip_shortcodes( $post->post_content );
		$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( excerpt_remove_blocks( $text ) ) ) );

		return wp_trim_words( $text, max( 1, $length ), '&hellip;' );
	}

	/**
	 * Numbered page links that change only this grid's query argument.
	 *
	 * @param int    $total  Number of pages.
	 * @param int    $page   Current page.
	 * @param string $key    Query argument name.
	 * @param bool   $arrows Whether to show previous and next links.
	 */
	private function render_pagination( $total, $page, $key, $arrows ) {
		if ( $total < 2 ) {
			return;
		}

		$page  = min( $page, $total );
		$shown = array();

		// First, last, and the pages next to the current one.
		for ( $i = 1; $i <= $total; $i++ ) {
			if ( 1 === $i || $total === $i || abs( $i - $page ) <= 1 ) {
				$shown[] = $i;
			}
		}

		$arrow = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.4 7.4L14 6l-6 6 6 6 1.4-1.4L10.8 12z"/></svg>';
		?>
		<nav class="teckform-posts-grid__pagination" aria-label="<?php esc_attr_e( 'Pages', 'teckform' ); ?>">
			<?php
			if ( $arrows && $page > 1 ) {
				printf(
					'<a class="teckform-posts-grid__page teckform-posts-grid__page--prev" href="%1$s" aria-label="%2$s">%3$s</a>',
					esc_url( $this->get_page_url( $key, $page - 1 ) ),
					esc_attr__( 'Previous page', 'teckform' ),
					$arrow // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG.
				);
			}

			$previous = 0;

			foreach ( $shown as $n ) {
				if ( $n - $previous > 1 ) {
					echo '<span class="teckform-posts-grid__page teckform-posts-grid__page--dots" aria-hidden="true">&hellip;</span>';
				}

				if ( $n === $page ) {
					printf(
						'<span class="teckform-posts-grid__page is-current" aria-current="page"><span class="screen-reader-text">%1$s </span>%2$s</span>',
						esc_html__( 'Page', 'teckform' ),
						esc_html( number_format_i18n( $n ) )
					);
				} else {
					printf(
						'<a class="teckform-posts-grid__page" href="%1$s"><span class="screen-reader-text">%2$s </span>%3$s</a>',
						esc_url( $this->get_page_url( $key, $n ) ),
						esc_html__( 'Page', 'teckform' ),
						esc_html( number_format_i18n( $n ) )
					);
				}

				$previous = $n;
			}

			if ( $arrows && $page < $total ) {
				printf(
					'<a class="teckform-posts-grid__page teckform-posts-grid__page--next" href="%1$s" aria-label="%2$s">%3$s</a>',
					esc_url( $this->get_page_url( $key, $page + 1 ) ),
					esc_attr__( 'Next page', 'teckform' ),
					$arrow // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG.
				);
			}
			?>
		</nav>
		<?php
	}

	/**
	 * Current address with this grid's page number changed, jumping back to the grid.
	 *
	 * @param string $key  Query argument name.
	 * @param int    $page Page number.
	 * @return string
	 */
	private function get_page_url( $key, $page ) {
		$url = 1 === $page ? remove_query_arg( $key ) : add_query_arg( $key, $page );

		return $url . '#' . $key;
	}
}
