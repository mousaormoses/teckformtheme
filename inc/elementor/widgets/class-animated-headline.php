<?php
/**
 * Animated Headline widget: a headline with words that change or get drawn on.
 *
 * Rotating effects (typing, slide up, fade, flip) cycle through a list of words.
 * The highlight effect draws a hand-drawn SVG shape (underline, circle, strike...)
 * under or around a single word.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Animated_Headline extends Widget_Base {

	public function get_name() {
		return 'teckform-animated-headline';
	}

	public function get_title() {
		return esc_html__( 'Animated Headline', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-animated-headline';
	}

	public function get_categories() {
		return array( 'teckform' );
	}

	public function get_keywords() {
		return array( 'headline', 'heading', 'animated', 'typing', 'rotate', 'highlight', 'underline', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-animated-headline' );
	}

	public function get_script_depends() {
		return array( 'teckform-animated-headline' );
	}

	/**
	 * Effect keys and labels.
	 *
	 * @return array<string, string>
	 */
	private function get_effects() {
		return array(
			'typing'    => esc_html__( 'Typing', 'teckform' ),
			'slide'     => esc_html__( 'Slide up', 'teckform' ),
			'fade'      => esc_html__( 'Fade', 'teckform' ),
			'flip'      => esc_html__( 'Flip', 'teckform' ),
			'highlight' => esc_html__( 'Highlight (hand-drawn shape)', 'teckform' ),
		);
	}

	/**
	 * Highlight shapes: label and SVG path(s) in a 500 x 150 box.
	 *
	 * The SVG is stretched over the word, so the paths only need to look right
	 * in proportion: y 15 is the top of the letters, y 115 the bottom.
	 *
	 * @return array<string, array{label: string, paths: string[]}>
	 */
	private function get_shapes() {
		return array(
			'underline'        => array(
				'label' => esc_html__( 'Underline', 'teckform' ),
				'paths' => array( 'M8 128C110 116 260 112 340 116C410 119 460 124 492 132' ),
			),
			'double-underline' => array(
				'label' => esc_html__( 'Double underline', 'teckform' ),
				'paths' => array(
					'M10 120C140 110 300 108 490 118',
					'M40 140C170 132 330 131 470 138',
				),
			),
			'circle'           => array(
				'label' => esc_html__( 'Circle', 'teckform' ),
				'paths' => array( 'M330 12C210 0 70 10 26 50C-6 80 40 128 200 140C340 150 480 128 492 86C504 44 420 14 300 10C230 8 150 16 110 26' ),
			),
			'curly'            => array(
				'label' => esc_html__( 'Curly underline', 'teckform' ),
				'paths' => array( 'M6 130C26 116 46 116 66 130S106 144 126 130 166 116 186 130 226 144 246 130 286 116 306 130 346 144 366 130 406 116 426 130 466 144 494 128' ),
			),
			'strikethrough'    => array(
				'label' => esc_html__( 'Strikethrough', 'teckform' ),
				'paths' => array( 'M6 80C160 72 340 74 494 66' ),
			),
		);
	}

	protected function register_controls() {
		$this->register_headline_controls();
		$this->register_animation_controls();

		$this->register_headline_style_controls();
		$this->register_words_style_controls();
		$this->register_shape_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_headline_controls() {
		$this->start_controls_section( 'section_headline', array(
			'label' => esc_html__( 'Headline', 'teckform' ),
		) );

		$this->add_control( 'effect', array(
			'label'   => esc_html__( 'Effect', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'typing',
			'options' => $this->get_effects(),
		) );

		$this->add_control( 'before_text', array(
			'label'     => esc_html__( 'Text Before', 'teckform' ),
			'type'      => Controls_Manager::TEXTAREA,
			'rows'      => 2,
			'default'   => esc_html__( 'We design', 'teckform' ),
			'dynamic'   => array( 'active' => true ),
			'separator' => 'before',
		) );

		$this->add_control( 'animated_words', array(
			'label'       => esc_html__( 'Animated Words', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => "websites\napps\nbrands",
			'description' => esc_html__( 'One per line. They take turns in the headline.', 'teckform' ),
			'condition'   => array( 'effect!' => 'highlight' ),
		) );

		$this->add_control( 'highlighted_text', array(
			'label'     => esc_html__( 'Highlighted Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'people love', 'teckform' ),
			'dynamic'   => array( 'active' => true ),
			'condition' => array( 'effect' => 'highlight' ),
		) );

		$this->add_control( 'after_text', array(
			'label'   => esc_html__( 'Text After', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'for growing businesses', 'teckform' ),
			'dynamic' => array( 'active' => true ),
		) );

		$this->add_control( 'tag', array(
			'label'     => esc_html__( 'HTML Tag', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'h2',
			'separator' => 'before',
			'options'   => array(
				'h1'   => 'H1',
				'h2'   => 'H2',
				'h3'   => 'H3',
				'h4'   => 'H4',
				'h5'   => 'H5',
				'h6'   => 'H6',
				'div'  => 'div',
				'p'    => 'p',
				'span' => 'span',
			),
		) );

		$this->add_responsive_control( 'align', array(
			'label'     => esc_html__( 'Alignment', 'teckform' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => array(
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
			'selectors' => array(
				'{{WRAPPER}} .teckform-animated-headline' => 'text-align: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_animation_controls() {
		$this->start_controls_section( 'section_animation', array(
			'label' => esc_html__( 'Animation', 'teckform' ),
		) );

		$this->add_control( 'interval', array(
			'label'       => esc_html__( 'Seconds per Word', 'teckform' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0.5,
			'max'         => 15,
			'step'        => 0.5,
			'default'     => 2.5,
			'description' => esc_html__( 'How long each word stays before the next one. For typing, the pause after a word is typed.', 'teckform' ),
			'condition'   => array( 'effect!' => 'highlight' ),
		) );

		$this->add_control( 'transition_speed', array(
			'label'      => esc_html__( 'Transition Speed (ms)', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min'  => 100,
					'max'  => 2000,
					'step' => 50,
				),
			),
			'default'    => array(
				'unit' => 'px',
				'size' => 500,
			),
			'condition'  => array( 'effect' => array( 'slide', 'fade', 'flip' ) ),
		) );

		$this->add_control( 'typing_speed', array(
			'label'       => esc_html__( 'Typing Speed (ms per letter)', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array(
				'px' => array(
					'min'  => 20,
					'max'  => 300,
					'step' => 5,
				),
			),
			'default'     => array(
				'unit' => 'px',
				'size' => 90,
			),
			'description' => esc_html__( 'Deleting runs twice as fast.', 'teckform' ),
			'condition'   => array( 'effect' => 'typing' ),
		) );

		$this->add_control( 'show_cursor', array(
			'label'        => esc_html__( 'Blinking Cursor', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => array( 'effect' => 'typing' ),
		) );

		$shapes = array();

		foreach ( $this->get_shapes() as $key => $shape ) {
			$shapes[ $key ] = $shape['label'];
		}

		$this->add_control( 'shape', array(
			'label'     => esc_html__( 'Shape', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'underline',
			'options'   => $shapes,
			'condition' => array( 'effect' => 'highlight' ),
		) );

		$this->add_control( 'draw_speed', array(
			'label'      => esc_html__( 'Drawing Time (ms)', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min'  => 300,
					'max'  => 5000,
					'step' => 100,
				),
			),
			'default'    => array(
				'unit' => 'px',
				'size' => 1200,
			),
			'condition'  => array( 'effect' => 'highlight' ),
		) );

		$this->add_control( 'loop', array(
			'label'        => esc_html__( 'Replay on a Loop', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Off: the shape is drawn once when the headline scrolls into view.', 'teckform' ),
			'condition'    => array( 'effect' => 'highlight' ),
		) );

		$this->add_control( 'loop_pause', array(
			'label'     => esc_html__( 'Seconds Before Replay', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 0.5,
			'max'       => 30,
			'step'      => 0.5,
			'default'   => 3,
			'condition' => array(
				'effect' => 'highlight',
				'loop'   => 'yes',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_headline_style_controls() {
		$this->start_controls_section( 'section_style_headline', array(
			'label' => esc_html__( 'Headline', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'headline_typography',
			'selector' => '{{WRAPPER}} .teckform-animated-headline__title',
		) );

		$this->add_control( 'headline_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-animated-headline__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'dark_heading', array(
			'label'     => esc_html__( 'Dark Mode', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'dark_headline_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-animated-headline__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'dark_words_color', array(
			'label'     => esc_html__( 'Animated Words Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-animated-headline__dynamic' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'dark_shape_color', array(
			'label'     => esc_html__( 'Shape Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'effect' => 'highlight' ),
			'selectors' => array(
				'html[data-teckform-theme="dark"] {{WRAPPER}} .teckform-animated-headline' => '--teckform-animated-headline-shape: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_words_style_controls() {
		$this->start_controls_section( 'section_style_words', array(
			'label' => esc_html__( 'Animated Words', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'words_typography',
			'selector' => '{{WRAPPER}} .teckform-animated-headline__dynamic',
		) );

		$this->add_control( 'words_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-animated-headline__dynamic' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'cursor_color', array(
			'label'     => esc_html__( 'Cursor Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array(
				'effect'      => 'typing',
				'show_cursor' => 'yes',
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-animated-headline' => '--teckform-animated-headline-cursor: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_shape_style_controls() {
		$this->start_controls_section( 'section_style_shape', array(
			'label'     => esc_html__( 'Shape', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'effect' => 'highlight' ),
		) );

		$this->add_control( 'shape_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-animated-headline' => '--teckform-animated-headline-shape: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'shape_width', array(
			'label'      => esc_html__( 'Line Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 1,
					'max' => 30,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-animated-headline' => '--teckform-animated-headline-shape-width: {{SIZE}};',
			),
		) );

		$this->add_control( 'shape_above', array(
			'label'        => esc_html__( 'Draw Over the Text', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Strikethrough is always drawn over the text.', 'teckform' ),
			'selectors'    => array(
				'{{WRAPPER}} .teckform-animated-headline__shape' => 'z-index: 1;',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$effect   = array_key_exists( $settings['effect'], $this->get_effects() ) ? $settings['effect'] : 'typing';
		$tag      = Utils::validate_html_tag( $settings['tag'] );
		$before   = trim( (string) $settings['before_text'] );
		$after    = trim( (string) $settings['after_text'] );

		if ( 'highlight' === $effect ) {
			$words = '' !== trim( (string) $settings['highlighted_text'] ) ? array( trim( $settings['highlighted_text'] ) ) : array();
		} else {
			$words = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $settings['animated_words'] ) ), 'strlen' ) );
		}

		if ( '' === $before && ! $words && '' === $after ) {
			return;
		}

		$this->add_render_attribute( 'wrapper', array(
			'class'       => array( 'teckform-animated-headline', 'teckform-animated-headline--' . $effect ),
			'data-effect' => $effect,
		) );

		$style = $this->get_timing_style( $settings, $effect );

		if ( '' !== $style ) {
			$this->add_render_attribute( 'wrapper', 'style', $style );
		}

		if ( 'highlight' === $effect ) {
			$this->add_render_attribute( 'wrapper', array(
				'data-draw' => $this->get_slider_ms( $settings, 'draw_speed', 1200 ),
				'data-loop' => 'yes' === $settings['loop'] ? (int) ( max( 0.5, (float) $settings['loop_pause'] ) * 1000 ) : 0,
			) );
		} else {
			$this->add_render_attribute( 'wrapper', 'data-interval', (int) ( max( 0.5, (float) $settings['interval'] ) * 1000 ) );
		}

		if ( 'typing' === $effect ) {
			$this->add_render_attribute( 'wrapper', 'data-typing-speed', $this->get_slider_ms( $settings, 'typing_speed', 90 ) );

			if ( 'yes' === $settings['show_cursor'] ) {
				$this->add_render_attribute( 'wrapper', 'class', 'teckform-animated-headline--cursor' );
			}
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<<?php echo esc_html( $tag ); ?> class="teckform-animated-headline__title">
				<?php
				if ( '' !== $before ) {
					echo '<span class="teckform-animated-headline__text">' . wp_kses_post( $before ) . '</span> ';
				}

				if ( 'highlight' === $effect ) {
					$this->render_highlight( $words, $settings['shape'] );
				} else {
					$this->render_words( $words, $effect );
				}

				if ( '' !== $after ) {
					echo ' <span class="teckform-animated-headline__text">' . wp_kses_post( $after ) . '</span>';
				}
				?>
			</<?php echo esc_html( $tag ); ?>>
		</div>
		<?php
	}

	/**
	 * Rotating words. Screen readers get the first word only; the animation is hidden from them.
	 *
	 * @param string[] $words  Words, already trimmed.
	 * @param string   $effect Effect key.
	 */
	private function render_words( $words, $effect ) {
		if ( ! $words ) {
			return;
		}

		if ( 1 === count( $words ) ) {
			echo '<span class="teckform-animated-headline__dynamic">' . esc_html( $words[0] ) . '</span>';
			return;
		}

		echo '<span class="screen-reader-text">' . esc_html( $words[0] ) . '</span>';

		if ( 'typing' === $effect ) {
			printf(
				'<span class="teckform-animated-headline__dynamic teckform-animated-headline__typed" aria-hidden="true" data-words="%1$s">%2$s</span>',
				esc_attr( wp_json_encode( $words ) ),
				esc_html( $words[0] )
			);
			return;
		}

		echo '<span class="teckform-animated-headline__dynamic teckform-animated-headline__words" aria-hidden="true">';

		foreach ( $words as $i => $word ) {
			printf( '<span class="teckform-animated-headline__word%s">%s</span>', 0 === $i ? ' is-active' : '', esc_html( $word ) );
		}

		echo '</span>';
	}

	/**
	 * Highlighted word with its hand-drawn shape.
	 *
	 * @param string[] $words Highlighted text (zero or one item).
	 * @param string   $shape Shape key.
	 */
	private function render_highlight( $words, $shape ) {
		if ( ! $words ) {
			return;
		}

		$shapes = $this->get_shapes();
		$shape  = isset( $shapes[ $shape ] ) ? $shape : 'underline';
		$paths  = '';

		foreach ( $shapes[ $shape ]['paths'] as $d ) {
			$paths .= '<path pathLength="1" d="' . esc_attr( $d ) . '"></path>';
		}

		printf(
			'<span class="teckform-animated-headline__dynamic teckform-animated-headline__highlight"><span class="teckform-animated-headline__highlight-text">%1$s</span><svg class="teckform-animated-headline__shape teckform-animated-headline__shape--%2$s" viewBox="0 0 500 150" preserveAspectRatio="none" aria-hidden="true" focusable="false">%3$s</svg></span>',
			esc_html( $words[0] ),
			esc_attr( $shape ),
			$paths // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
		);
	}

	/**
	 * Inline CSS variables for the transition and drawing times.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $effect   Effect key.
	 * @return string
	 */
	private function get_timing_style( $settings, $effect ) {
		if ( 'highlight' === $effect ) {
			return '--teckform-animated-headline-draw: ' . $this->get_slider_ms( $settings, 'draw_speed', 1200 ) . 'ms;';
		}

		if ( in_array( $effect, array( 'slide', 'fade', 'flip' ), true ) ) {
			return '--teckform-animated-headline-speed: ' . $this->get_slider_ms( $settings, 'transition_speed', 500 ) . 'ms;';
		}

		return '';
	}

	/**
	 * A millisecond slider value as a whole number, with a fallback.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $key      Control ID.
	 * @param int    $default  Value when the control is empty.
	 * @return int
	 */
	private function get_slider_ms( $settings, $key, $default ) {
		$value = isset( $settings[ $key ]['size'] ) && '' !== $settings[ $key ]['size'] ? (int) $settings[ $key ]['size'] : $default;

		return max( 1, $value );
	}
}
