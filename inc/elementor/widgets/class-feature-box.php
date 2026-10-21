<?php
/**
 * Feature Box widget: icon, title, description and optional link.
 *
 * Use this file as the pattern for new widgets.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Feature_Box extends Widget_Base {

	public function get_name() {
		return 'teckform-feature-box';
	}

	public function get_title() {
		return esc_html__( 'Feature Box', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-icon-box';
	}

	public function get_categories() {
		return array( 'teckform' );
	}

	public function get_keywords() {
		return array( 'feature', 'box', 'icon', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-widgets' );
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Content tab: what the widget shows.
	 */
	private function register_content_controls() {
		$this->start_controls_section( 'section_content', array(
			'label' => esc_html__( 'Content', 'teckform' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'icon', array(
			'label'   => esc_html__( 'Icon', 'teckform' ),
			'type'    => Controls_Manager::ICONS,
			'default' => array(
				'value'   => 'fas fa-star',
				'library' => 'fa-solid',
			),
		) );

		$this->add_control( 'title', array(
			'label'       => esc_html__( 'Title', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Feature title', 'teckform' ),
			'label_block' => true,
			'dynamic'     => array( 'active' => true ),
		) );

		$this->add_control( 'description', array(
			'label'   => esc_html__( 'Description', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => esc_html__( 'Describe this feature in a sentence or two.', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$this->add_control( 'link', array(
			'label'       => esc_html__( 'Link', 'teckform' ),
			'type'        => Controls_Manager::URL,
			'placeholder' => 'https://your-link.com',
			'dynamic'     => array( 'active' => true ),
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

		$this->end_controls_section();
	}

	/**
	 * Style tab: how the widget looks.
	 */
	private function register_style_controls() {
		$this->start_controls_section( 'section_style', array(
			'label' => esc_html__( 'Box', 'teckform' ),
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
				'{{WRAPPER}} .teckform-feature-box' => 'text-align: {{VALUE}};',
			),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_icon', array(
			'label' => esc_html__( 'Icon', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'icon_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-feature-box__icon'     => 'color: {{VALUE}};',
				'{{WRAPPER}} .teckform-feature-box__icon svg' => 'fill: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'icon_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array(
					'min' => 8,
					'max' => 200,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-feature-box__icon' => 'font-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'icon_spacing', array(
			'label'      => esc_html__( 'Spacing', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 100,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-feature-box__icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_text', array(
			'label' => esc_html__( 'Text', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'title_color', array(
			'label'     => esc_html__( 'Title Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-feature-box__title, {{WRAPPER}} .teckform-feature-box__title a' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .teckform-feature-box__title',
		) );

		$this->add_control( 'description_color', array(
			'label'     => esc_html__( 'Description Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array(
				'{{WRAPPER}} .teckform-feature-box__description' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'description_typography',
			'selector' => '{{WRAPPER}} .teckform-feature-box__description',
		) );

		$this->end_controls_section();
	}

	/**
	 * Front-end output.
	 */
	protected function render() {
		$settings  = $this->get_settings_for_display();
		$title_tag = Utils::validate_html_tag( $settings['title_tag'] );
		$link_tag  = 'span';

		if ( ! empty( $settings['link']['url'] ) ) {
			$link_tag = 'a';
			$this->add_link_attributes( 'title_text', $settings['link'] );
		}

		$this->add_inline_editing_attributes( 'title_text', 'none' );
		$this->add_inline_editing_attributes( 'description', 'basic' );
		$this->add_render_attribute( 'description', 'class', 'teckform-feature-box__description' );
		?>
		<div class="teckform-feature-box">
			<?php if ( ! empty( $settings['icon']['value'] ) ) : ?>
				<div class="teckform-feature-box__icon">
					<?php Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $settings['title'] ) : ?>
				<<?php echo esc_html( $title_tag ); ?> class="teckform-feature-box__title">
					<<?php echo esc_html( $link_tag ); ?> <?php $this->print_render_attribute_string( 'title_text' ); ?>><?php echo wp_kses_post( $settings['title'] ); ?></<?php echo esc_html( $link_tag ); ?>>
				</<?php echo esc_html( $title_tag ); ?>>
			<?php endif; ?>

			<?php if ( '' !== $settings['description'] ) : ?>
				<p <?php $this->print_render_attribute_string( 'description' ); ?>><?php echo wp_kses_post( $settings['description'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Live preview in the Elementor editor. Mirrors render().
	 */
	protected function content_template() {
		?>
		<#
		var iconHTML = elementor.helpers.renderIcon( view, settings.icon, { 'aria-hidden': true }, 'i', 'object' ),
			titleTag = elementor.helpers.validateHTMLTag( settings.title_tag ),
			linkTag = settings.link.url ? 'a' : 'span';

		if ( settings.link.url ) {
			view.addRenderAttribute( 'title_text', 'href', settings.link.url );
		}

		view.addInlineEditingAttributes( 'title_text', 'none' );
		view.addInlineEditingAttributes( 'description', 'basic' );
		view.addRenderAttribute( 'description', 'class', 'teckform-feature-box__description' );
		#>
		<div class="teckform-feature-box">
			<# if ( iconHTML && iconHTML.rendered ) { #>
				<div class="teckform-feature-box__icon">{{{ iconHTML.value }}}</div>
			<# } #>

			<# if ( settings.title ) { #>
				<{{ titleTag }} class="teckform-feature-box__title">
					<{{ linkTag }} {{{ view.getRenderAttributeString( 'title_text' ) }}}>{{{ settings.title }}}</{{ linkTag }}>
				</{{ titleTag }}>
			<# } #>

			<# if ( settings.description ) { #>
				<p {{{ view.getRenderAttributeString( 'description' ) }}}>{{{ settings.description }}}</p>
			<# } #>
		</div>
		<?php
	}
}
