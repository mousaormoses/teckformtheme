<?php
/**
 * Logo Carousel widget: client or partner logos, as a scrolling marquee or a grid.
 *
 * The marquee is pure CSS: the logo row is printed twice and slides by half its
 * width, so the second copy takes the first one's place and the loop is seamless.
 * No script is needed.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logo_Carousel extends Widget_Base {

	/**
	 * In marquee mode each copy of the row repeats the logos until it holds at
	 * least this many, so a short list still fills wide screens without a gap.
	 */
	const MIN_MARQUEE_ITEMS = 10;

	public function get_name() {
		return 'teckform-logo-carousel';
	}

	public function get_title() {
		return esc_html__( 'Logo Carousel', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-carousel';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'logo', 'logos', 'clients', 'partners', 'brands', 'marquee', 'carousel', 'ticker', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-logo-carousel' );
	}

	protected function register_controls() {
		$this->register_logo_controls();
		$this->register_marquee_controls();
		$this->register_grid_controls();

		$this->register_title_style_controls();
		$this->register_logo_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_logo_controls() {
		$this->start_controls_section( 'section_logos', array(
			'label' => esc_html__( 'Logos', 'teckform' ),
		) );

		$this->add_control( 'title', array(
			'label'       => esc_html__( 'Title', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'Trusted by teams at', 'teckform' ),
			'description' => esc_html__( 'Optional line above the logos. Leave empty to hide it.', 'teckform' ),
			'dynamic'     => array( 'active' => true ),
		) );

		$this->add_control( 'title_tag', array(
			'label'   => esc_html__( 'Title HTML Tag', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'p',
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

		$this->add_control( 'logos', array(
			'label'       => esc_html__( 'Logos', 'teckform' ),
			'type'        => Controls_Manager::GALLERY,
			'separator'   => 'before',
			'description' => esc_html__( 'SVG or transparent PNG files look best. The image Alt Text is read by screen readers.', 'teckform' ),
		) );

		$this->add_control( 'mode', array(
			'label'   => esc_html__( 'Display', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'marquee',
			'options' => array(
				'marquee' => esc_html__( 'Scrolling marquee', 'teckform' ),
				'grid'    => esc_html__( 'Grid', 'teckform' ),
			),
		) );

		$this->end_controls_section();
	}

	private function register_marquee_controls() {
		$this->start_controls_section( 'section_marquee', array(
			'label'     => esc_html__( 'Marquee', 'teckform' ),
			'condition' => array( 'mode' => 'marquee' ),
		) );

		$this->add_control( 'speed', array(
			'label'       => esc_html__( 'Seconds per Loop', 'teckform' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 5,
			'max'         => 200,
			'step'        => 1,
			'default'     => 40,
			'description' => esc_html__( 'Lower is faster.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-logo-carousel' => '--teckform-logos-duration: {{VALUE}}s;',
			),
		) );

		$this->add_control( 'direction', array(
			'label'       => esc_html__( 'Direction', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'start',
			'options'     => array(
				'start' => esc_html__( 'Toward the start (leftward)', 'teckform' ),
				'end'   => esc_html__( 'Toward the end (rightward)', 'teckform' ),
			),
			'description' => esc_html__( 'Mirrored automatically on right-to-left sites.', 'teckform' ),
		) );

		$this->add_control( 'pause_on_hover', array(
			'label'        => esc_html__( 'Pause on Hover', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'fade_edges', array(
			'label'        => esc_html__( 'Fade Edges', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'marquee_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Visitors who ask their device for less motion see the logos standing still.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
		) );

		$this->end_controls_section();
	}

	private function register_grid_controls() {
		$this->start_controls_section( 'section_grid', array(
			'label'     => esc_html__( 'Grid', 'teckform' ),
			'condition' => array( 'mode' => 'grid' ),
		) );

		$this->add_responsive_control( 'columns', array(
			'label'          => esc_html__( 'Columns', 'teckform' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '5',
			'tablet_default' => '4',
			'mobile_default' => '2',
			'options'        => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
				'6' => '6',
				'7' => '7',
				'8' => '8',
			),
			'selectors'      => array(
				'{{WRAPPER}} .teckform-logo-carousel' => '--teckform-logos-columns: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_title_style_controls() {
		$this->start_controls_section( 'section_style_title', array(
			'label'     => esc_html__( 'Title', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'title!' => '' ),
		) );

		$this->add_responsive_control( 'title_align', array(
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
				'{{WRAPPER}} .teckform-logo-carousel__title' => 'text-align: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .teckform-logo-carousel__title',
		) );

		$this->add_control( 'title_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-logo-carousel__title' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'title_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 100,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-logo-carousel__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_logo_style_controls() {
		$this->start_controls_section( 'section_style_logos', array(
			'label' => esc_html__( 'Logos', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'logo_height', array(
			'label'      => esc_html__( 'Logo Height', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 12,
					'max' => 160,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-logo-carousel' => '--teckform-logos-height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'logo_max_width', array(
			'label'      => esc_html__( 'Logo Max Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 40,
					'max' => 400,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-logo-carousel__logo img' => 'max-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'logo_gap', array(
			'label'      => esc_html__( 'Space Between Logos', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 160,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-logo-carousel' => '--teckform-logos-gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'fade_width', array(
			'label'      => esc_html__( 'Fade Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 300,
				),
				'%'  => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'condition'  => array(
				'mode'       => 'marquee',
				'fade_edges' => 'yes',
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-logo-carousel' => '--teckform-logos-fade: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'grayscale', array(
			'label'        => esc_html__( 'Gray Until Hovered', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
			'description'  => esc_html__( 'Logos show gray and faded, and in full color when the mouse is over them.', 'teckform' ),
		) );

		$this->add_responsive_control( 'item_padding', array(
			'label'      => esc_html__( 'Logo Box Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'separator'  => 'before',
			'selectors'  => array(
				'{{WRAPPER}} .teckform-logo-carousel__logo' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'item_radius', array(
			'label'      => esc_html__( 'Logo Box Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 50,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-logo-carousel__logo' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'item_border',
			'selector' => '{{WRAPPER}} .teckform-logo-carousel__logo',
		) );

		$this->start_controls_tabs( 'logo_tabs' );

		$this->start_controls_tab( 'logo_tab_normal', array( 'label' => esc_html__( 'Normal', 'teckform' ) ) );

		$this->add_control( 'logo_opacity', array(
			'label'     => esc_html__( 'Opacity', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 0.1,
					'max'  => 1,
					'step' => 0.05,
				),
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-logo-carousel' => '--teckform-logos-opacity: {{SIZE}};',
			),
		) );

		$this->add_control( 'item_bg', array(
			'label'     => esc_html__( 'Box Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-logo-carousel__logo' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'item_shadow',
			'selector' => '{{WRAPPER}} .teckform-logo-carousel__logo',
		) );

		$this->end_controls_tab();

		$this->start_controls_tab( 'logo_tab_hover', array( 'label' => esc_html__( 'Hover', 'teckform' ) ) );

		$this->add_control( 'logo_hover_opacity', array(
			'label'     => esc_html__( 'Opacity', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 0.1,
					'max'  => 1,
					'step' => 0.05,
				),
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-logo-carousel' => '--teckform-logos-hover-opacity: {{SIZE}};',
			),
		) );

		$this->add_control( 'item_hover_bg', array(
			'label'     => esc_html__( 'Box Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-logo-carousel__logo:hover' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'item_hover_border', array(
			'label'     => esc_html__( 'Box Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-logo-carousel__logo:hover' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'item_hover_shadow',
			'selector' => '{{WRAPPER}} .teckform-logo-carousel__logo:hover',
		) );

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$logos    = array_values( array_filter( (array) $settings['logos'], array( $this, 'has_image' ) ) );
		$editing  = Plugin::$instance->editor->is_edit_mode();

		if ( ! $logos && ! $editing ) {
			return;
		}

		$mode    = 'grid' === $settings['mode'] ? 'grid' : 'marquee';
		$classes = array( 'teckform-logo-carousel', 'teckform-logo-carousel--' . $mode );

		if ( 'yes' === $settings['grayscale'] ) {
			$classes[] = 'teckform-logo-carousel--grayscale';
		}

		if ( 'marquee' === $mode ) {
			if ( 'end' === $settings['direction'] ) {
				$classes[] = 'teckform-logo-carousel--reverse';
			}

			if ( 'yes' === $settings['pause_on_hover'] ) {
				$classes[] = 'teckform-logo-carousel--pause-hover';
			}

			if ( 'yes' === $settings['fade_edges'] ) {
				$classes[] = 'teckform-logo-carousel--fade';
			}
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php
			if ( '' !== trim( $settings['title'] ) ) {
				$tag = Utils::validate_html_tag( $settings['title_tag'] );

				echo '<' . esc_html( $tag ) . ' class="teckform-logo-carousel__title">' . esc_html( $settings['title'] ) . '</' . esc_html( $tag ) . '>';
			}

			if ( 'grid' === $mode ) {
				echo '<ul class="teckform-logo-carousel__grid" role="list">';
				$this->render_items( $logos, count( $logos ) ? count( $logos ) : 6, 'lazy' );
				echo '</ul>';
			} else {
				$this->render_marquee( $logos );
			}
			?>
		</div>
		<?php
	}

	/**
	 * The moving row: the logo list twice, the second copy hidden from screen readers.
	 *
	 * @param array $logos Gallery items.
	 */
	private function render_marquee( $logos ) {
		$original = count( $logos ) ? count( $logos ) : 6;
		$total    = (int) ( ceil( self::MIN_MARQUEE_ITEMS / $original ) * $original );
		?>
		<div class="teckform-logo-carousel__viewport">
			<div class="teckform-logo-carousel__track">
				<ul class="teckform-logo-carousel__group" role="list">
					<?php $this->render_items( $logos, $total, 'eager', $original ); ?>
				</ul>
				<ul class="teckform-logo-carousel__group" aria-hidden="true">
					<?php $this->render_items( $logos, $total, 'eager', 0 ); ?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * List items, repeating the logos until there are $total of them.
	 *
	 * @param array  $logos    Gallery items. Empty shows editor placeholders.
	 * @param int    $total    Number of items to print.
	 * @param string $loading  Image loading attribute.
	 * @param int    $readable How many items screen readers may read; the rest are repeats. -1 for all.
	 */
	private function render_items( $logos, $total, $loading, $readable = -1 ) {
		$count = count( $logos );

		for ( $i = 0; $i < $total; $i++ ) {
			$hidden = -1 !== $readable && $i >= $readable;

			echo '<li class="teckform-logo-carousel__logo"' . ( $hidden ? ' aria-hidden="true"' : '' ) . '>';

			if ( $count ) {
				echo $this->get_image_html( $logos[ $i % $count ], $loading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_image_html().
			} else {
				// Placeholder so the layout is visible in the editor until logos are added.
				echo '<span class="teckform-logo-carousel__placeholder">' . esc_html__( 'Logo', 'teckform' ) . '</span>';
			}

			echo '</li>';
		}
	}

	/**
	 * Whether a gallery item points to an image.
	 *
	 * @param mixed $item Gallery item.
	 * @return bool
	 */
	public function has_image( $item ) {
		return is_array( $item ) && ( ! empty( $item['id'] ) || ! empty( $item['url'] ) );
	}

	/**
	 * <img> markup for a gallery item.
	 *
	 * @param array  $media   Value with 'id' and/or 'url'.
	 * @param string $loading 'lazy' or 'eager'.
	 * @return string
	 */
	private function get_image_html( $media, $loading ) {
		if ( ! empty( $media['id'] ) ) {
			$html = wp_get_attachment_image( absint( $media['id'] ), 'medium', false, array(
				'class'    => 'teckform-logo-carousel__image',
				'loading'  => $loading,
				'decoding' => 'async',
			) );

			if ( $html ) {
				return $html;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img src="' . esc_url( $media['url'] ) . '" alt="" class="teckform-logo-carousel__image" loading="' . esc_attr( $loading ) . '" decoding="async">';
		}

		return '';
	}
}
