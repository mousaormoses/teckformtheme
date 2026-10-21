<?php
/**
 * FAQ widget: an accessible accordion of questions and answers.
 *
 * Each question is a button (aria-expanded / aria-controls) that opens a region
 * with the answer. One or several items can be open, the list can split into two
 * columns on desktop, and the questions can be added to the page as FAQPage
 * structured data for search engines.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Faq extends Widget_Base {

	public function get_name() {
		return 'teckform-faq';
	}

	public function get_title() {
		return esc_html__( 'FAQ', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'faq', 'questions', 'answers', 'accordion', 'toggle', 'schema', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-faq' );
	}

	public function get_script_depends() {
		return array( 'teckform-faq' );
	}

	protected function register_controls() {
		$this->register_item_controls();
		$this->register_settings_controls();

		$this->register_item_style_controls();
		$this->register_question_style_controls();
		$this->register_icon_style_controls();
		$this->register_answer_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_item_controls() {
		$this->start_controls_section( 'section_items', array(
			'label' => esc_html__( 'Questions', 'teckform' ),
		) );

		$repeater = new Repeater();

		$repeater->add_control( 'question', array(
			'label'       => esc_html__( 'Question', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'dynamic'     => array( 'active' => true ),
			'default'     => esc_html__( 'Your question?', 'teckform' ),
		) );

		$repeater->add_control( 'answer', array(
			'label'   => esc_html__( 'Answer', 'teckform' ),
			'type'    => Controls_Manager::WYSIWYG,
			'dynamic' => array( 'active' => true ),
			'default' => esc_html__( 'The answer to the question.', 'teckform' ),
		) );

		$this->add_control( 'items', array(
			'label'       => esc_html__( 'Questions', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ question }}}',
			'default'     => array(
				array(
					'question' => esc_html__( 'How long does a website project take?', 'teckform' ),
					'answer'   => esc_html__( 'Most websites take four to eight weeks from the first call to launch, depending on the number of pages and features.', 'teckform' ),
				),
				array(
					'question' => esc_html__( 'Do you offer support after launch?', 'teckform' ),
					'answer'   => esc_html__( 'Yes. Every project includes 30 days of free support, and we offer monthly care plans after that.', 'teckform' ),
				),
				array(
					'question' => esc_html__( 'Can I update the website myself?', 'teckform' ),
					'answer'   => esc_html__( 'Of course. We build on WordPress and Elementor and show you how to edit pages, posts and images.', 'teckform' ),
				),
				array(
					'question' => esc_html__( 'How much does a website cost?', 'teckform' ),
					'answer'   => esc_html__( 'Every project is different. Tell us what you need and we will send a fixed-price quote within one day.', 'teckform' ),
				),
			),
		) );

		$this->end_controls_section();
	}

	private function register_settings_controls() {
		$this->start_controls_section( 'section_settings', array(
			'label' => esc_html__( 'Settings', 'teckform' ),
		) );

		$this->add_control( 'behavior', array(
			'label'   => esc_html__( 'Open Items', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'single',
			'options' => array(
				'single'   => esc_html__( 'One at a time', 'teckform' ),
				'multiple' => esc_html__( 'Several at once', 'teckform' ),
			),
		) );

		$this->add_control( 'first_open', array(
			'label'        => esc_html__( 'First Item Open', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'icon', array(
			'label'   => esc_html__( 'Icon', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'plus',
			'options' => array(
				'plus'    => esc_html__( 'Plus / minus', 'teckform' ),
				'chevron' => esc_html__( 'Chevron', 'teckform' ),
				'none'    => esc_html__( 'None', 'teckform' ),
			),
		) );

		$this->add_control( 'icon_position', array(
			'label'     => esc_html__( 'Icon Position', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'end',
			'toggle'    => false,
			'options'   => array(
				'start' => array(
					'title' => esc_html__( 'Start', 'teckform' ),
					'icon'  => 'eicon-h-align-left',
				),
				'end'   => array(
					'title' => esc_html__( 'End', 'teckform' ),
					'icon'  => 'eicon-h-align-right',
				),
			),
			'condition' => array( 'icon!' => 'none' ),
		) );

		$this->add_control( 'columns', array(
			'label'       => esc_html__( 'Columns on Desktop', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => '1',
			'options'     => array(
				'1' => esc_html__( 'One', 'teckform' ),
				'2' => esc_html__( 'Two', 'teckform' ),
			),
			'description' => esc_html__( 'Two columns split the list in half. Phones and tablets always show one column.', 'teckform' ),
		) );

		$this->add_control( 'question_tag', array(
			'label'   => esc_html__( 'Question HTML Tag', 'teckform' ),
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

		$this->add_control( 'schema', array(
			'label'        => esc_html__( 'Add FAQ Structured Data for Google', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
			'description'  => esc_html__( 'Adds the questions to the page as FAQPage data. Turn it on for only one FAQ per page.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_item_style_controls() {
		$this->start_controls_section( 'section_style_items', array(
			'label' => esc_html__( 'Items', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'item_gap', array(
			'label'      => esc_html__( 'Space Between Items', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-faq' => '--teckform-faq-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'column_gap', array(
			'label'      => esc_html__( 'Space Between Columns', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 120,
				),
			),
			'condition'  => array( 'columns' => '2' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-faq' => '--teckform-faq-column-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'item_radius', array(
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
				'{{WRAPPER}} .teckform-faq__item' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'item_border',
			'selector' => '{{WRAPPER}} .teckform-faq__item',
		) );

		$this->start_controls_tabs( 'item_tabs' );

		$this->start_controls_tab( 'item_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'item_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__item' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'item_shadow',
			'selector' => '{{WRAPPER}} .teckform-faq__item',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'item_tab_open', array( 'label' => esc_html__( 'Open', 'teckform' ) ) );

		$this->add_control( 'item_open_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__item.is-open' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'item_open_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__item.is-open' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'item_open_shadow',
			'selector' => '{{WRAPPER}} .teckform-faq__item.is-open',
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_question_style_controls() {
		$this->start_controls_section( 'section_style_question', array(
			'label' => esc_html__( 'Question', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'question_typography',
			'selector' => '{{WRAPPER}} .teckform-faq__question',
		) );

		$this->add_responsive_control( 'question_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-faq' => '--teckform-faq-pad-top: {{TOP}}{{UNIT}}; --teckform-faq-pad-end: {{RIGHT}}{{UNIT}}; --teckform-faq-pad-bottom: {{BOTTOM}}{{UNIT}}; --teckform-faq-pad-start: {{LEFT}}{{UNIT}};',
			),
		) );

		$this->start_controls_tabs( 'question_tabs' );

		$this->start_controls_tab( 'question_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'question_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__question' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'question_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__question' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'question_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'question_hover_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__question:hover, {{WRAPPER}} .teckform-faq__question:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'question_hover_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__question:hover, {{WRAPPER}} .teckform-faq__question:focus-visible' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'question_tab_open', array( 'label' => esc_html__( 'Open', 'teckform' ) ) );

		$this->add_control( 'question_open_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__item.is-open .teckform-faq__question' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'question_open_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__item.is-open .teckform-faq__question' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_icon_style_controls() {
		$this->start_controls_section( 'section_style_icon', array(
			'label'     => esc_html__( 'Icon', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'icon!' => 'none' ),
		) );

		$this->add_responsive_control( 'icon_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 10,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-faq' => '--teckform-faq-icon-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'icon_circle', array(
			'label'        => esc_html__( 'Circle Behind Icon', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
		) );

		$this->start_controls_tabs( 'icon_tabs' );

		$this->start_controls_tab( 'icon_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'icon_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__icon' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'icon_bg', array(
			'label'     => esc_html__( 'Circle Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'icon_circle' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__icon' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'icon_tab_open', array( 'label' => esc_html__( 'Open', 'teckform' ) ) );

		$this->add_control( 'icon_open_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__item.is-open .teckform-faq__icon' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'icon_open_bg', array(
			'label'     => esc_html__( 'Circle Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'icon_circle' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__item.is-open .teckform-faq__icon' => 'background-color: {{VALUE}};',
			),
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function register_answer_style_controls() {
		$this->start_controls_section( 'section_style_answer', array(
			'label' => esc_html__( 'Answer', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'answer_typography',
			'selector' => '{{WRAPPER}} .teckform-faq__answer-inner',
		) );

		$this->add_control( 'answer_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__answer-inner' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'answer_link_color', array(
			'label'     => esc_html__( 'Link Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__answer-inner a' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'answer_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-faq__answer' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'answer_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-faq__answer-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'answer_divider', array(
			'label'        => esc_html__( 'Line Above Answer', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'selectors'    => array(
				'{{WRAPPER}} .teckform-faq__answer-inner' => 'border-top: 1px solid var(--teckform-border); padding-top: 16px;',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = array_values( array_filter( (array) $settings['items'], array( $this, 'has_question' ) ) );

		if ( ! $items ) {
			return;
		}

		$icon = in_array( $settings['icon'], array( 'plus', 'chevron', 'none' ), true ) ? $settings['icon'] : 'plus';

		$classes = array(
			'teckform-faq',
			'teckform-faq--icon-' . $icon,
			'teckform-faq--icon-at-' . ( 'start' === $settings['icon_position'] ? 'start' : 'end' ),
		);

		if ( 'yes' === $settings['icon_circle'] ) {
			$classes[] = 'teckform-faq--icon-circle';
		}

		$columns = array( $items );

		if ( '2' === $settings['columns'] && count( $items ) > 1 ) {
			$classes[] = 'teckform-faq--columns-2';
			$columns   = array_chunk( $items, (int) ceil( count( $items ) / 2 ), true );
		}

		$this->add_render_attribute( 'wrapper', array(
			'class'         => $classes,
			'data-multiple' => 'multiple' === $settings['behavior'] ? 'yes' : 'no',
		) );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php foreach ( $columns as $column ) : ?>
				<div class="teckform-faq__column">
					<?php
					foreach ( $column as $index => $item ) {
						$this->render_item( $settings, $item, $index, 0 === $index && 'yes' === $settings['first_open'] );
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		if ( 'yes' === $settings['schema'] ) {
			$this->render_schema( $items );
		}
	}

	/**
	 * One question and its answer.
	 *
	 * @param array $settings Widget settings.
	 * @param array $item     Repeater item.
	 * @param int   $index    Position in the whole list.
	 * @param bool  $open     Whether the item starts open.
	 */
	private function render_item( $settings, $item, $index, $open ) {
		$tag       = Utils::validate_html_tag( $settings['question_tag'] );
		$id        = 'teckform-faq-' . $this->get_id() . '-' . ( $index + 1 );
		$button_id = $id . '-question';
		$answer_id = $id . '-answer';
		?>
		<div class="teckform-faq__item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?><?php echo $open ? ' is-open' : ''; ?>">
			<<?php echo esc_html( $tag ); ?> class="teckform-faq__heading">
				<button type="button" class="teckform-faq__question" id="<?php echo esc_attr( $button_id ); ?>" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $answer_id ); ?>">
					<span class="teckform-faq__question-text"><?php echo esc_html( $item['question'] ); ?></span>
					<?php if ( 'none' !== $settings['icon'] ) : ?>
						<span class="teckform-faq__icon" aria-hidden="true"></span>
					<?php endif; ?>
				</button>
			</<?php echo esc_html( $tag ); ?>>
			<?php // "until-found" keeps closed answers searchable with the browser's find-in-page. ?>
			<div class="teckform-faq__answer" id="<?php echo esc_attr( $answer_id ); ?>" role="region" aria-labelledby="<?php echo esc_attr( $button_id ); ?>"<?php echo $open ? '' : ' hidden="until-found"'; ?>>
				<div class="teckform-faq__answer-inner">
					<?php echo $this->parse_text_editor( wp_kses_post( $item['answer'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Filtered with wp_kses_post() before shortcodes run. ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * FAQPage JSON-LD. Answers are plain text so markup can't leak into it.
	 *
	 * @param array $items Repeater items.
	 */
	private function render_schema( $items ) {
		$questions = array();

		foreach ( $items as $item ) {
			$question = trim( wp_strip_all_tags( $item['question'] ) );
			$answer   = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( do_shortcode( $item['answer'] ) ) ) );

			if ( '' === $question || '' === $answer ) {
				continue;
			}

			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $question,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $answer,
				),
			);
		}

		if ( ! $questions ) {
			return;
		}

		$data = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $questions,
		);

		// JSON_HEX_TAG turns < and > into < / >, so the data can't close the script tag.
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded with tags hex-escaped.
	}

	/**
	 * array_filter() callback: skip items without a question.
	 *
	 * @param array $item Repeater item.
	 * @return bool
	 */
	public function has_question( $item ) {
		return isset( $item['question'] ) && '' !== trim( (string) $item['question'] );
	}
}
