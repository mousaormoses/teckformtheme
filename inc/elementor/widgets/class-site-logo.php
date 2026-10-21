<?php
/**
 * Site Logo widget: the site's logo, with an optional second logo for scrolled headers.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Site_Logo extends Widget_Base {

	public function get_name() {
		return 'teckform-site-logo';
	}

	public function get_title() {
		return esc_html__( 'Site Logo', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-site-logo';
	}

	public function get_categories() {
		return array( 'teckform-site' );
	}

	public function get_keywords() {
		return array( 'logo', 'brand', 'header', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-widgets' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array(
			'label' => esc_html__( 'Logo', 'teckform' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'logo_source', array(
			'label'   => esc_html__( 'Logo', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'site',
			'options' => array(
				'site'   => esc_html__( 'Site logo (Appearance > Customize)', 'teckform' ),
				'custom' => esc_html__( 'Custom image', 'teckform' ),
			),
		) );

		$this->add_control( 'custom_logo', array(
			'label'     => esc_html__( 'Image', 'teckform' ),
			'type'      => Controls_Manager::MEDIA,
			'condition' => array( 'logo_source' => 'custom' ),
		) );

		$this->add_control( 'scrolled_logo', array(
			'label'       => esc_html__( 'Logo After Scrolling', 'teckform' ),
			'type'        => Controls_Manager::MEDIA,
			'default'     => array( 'url' => '' ),
			'description' => esc_html__( 'Optional. Shown instead of the logo once a sticky or transparent header is scrolled.', 'teckform' ),
		) );

		$this->add_control( 'link', array(
			'label'   => esc_html__( 'Link to Home Page', 'teckform' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array(
			'label' => esc_html__( 'Logo', 'teckform' ),
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
				'{{WRAPPER}} .teckform-logo' => 'text-align: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'width', array(
			'label'      => esc_html__( 'Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 20,
					'max' => 600,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-logo img' => 'width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'scrolled_width', array(
			'label'       => esc_html__( 'Width After Scrolling', 'teckform' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px', '%' ),
			'range'       => array(
				'px' => array(
					'min' => 20,
					'max' => 600,
				),
			),
			'description' => esc_html__( 'Shrink the logo once a sticky header is scrolled.', 'teckform' ),
			'selectors'   => array(
				'.teckform-header.is-scrolled {{WRAPPER}} .teckform-logo img' => 'width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'hover_opacity', array(
			'label'     => esc_html__( 'Hover Opacity', 'teckform' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => array(
				'px' => array(
					'min'  => 0.1,
					'max'  => 1,
					'step' => 0.05,
				),
			),
			'selectors' => array(
				'{{WRAPPER}} .teckform-logo__link:hover img' => 'opacity: {{SIZE}};',
			),
		) );

		$this->add_control( 'name_color', array(
			'label'       => esc_html__( 'Site Name Color', 'teckform' ),
			'type'        => Controls_Manager::COLOR,
			'separator'   => 'before',
			'description' => esc_html__( 'Used when there is no logo image.', 'teckform' ),
			'selectors'   => array(
				'{{WRAPPER}} .teckform-logo__name' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'name_typography',
			'selector' => '{{WRAPPER}} .teckform-logo__name',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( 'custom' === $settings['logo_source'] ) {
			$logo = $this->get_image_html( $settings['custom_logo'] );
		} else {
			$logo = $this->get_image_html( array( 'id' => get_theme_mod( 'custom_logo' ) ) );
		}

		if ( ! $logo ) {
			$logo = '<span class="teckform-logo__name">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
		}

		$scrolled = $this->get_image_html( $settings['scrolled_logo'] );
		$tag      = 'yes' === $settings['link'] ? 'a' : 'span';
		$link     = 'a' === $tag ? ' href="' . esc_url( home_url( '/' ) ) . '" rel="home"' : '';
		?>
		<div class="teckform-logo<?php echo $scrolled ? ' teckform-logo--has-scrolled' : ''; ?>">
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $tag is fixed, $link is escaped above. ?>
			<<?php echo $tag . $link; ?> class="teckform-logo__link">
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts. ?>
				<span class="teckform-logo__default"><?php echo $logo; ?></span>
				<?php if ( $scrolled ) : ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts. ?>
					<span class="teckform-logo__scrolled"><?php echo $scrolled; ?></span>
				<?php endif; ?>
			</<?php echo esc_html( $tag ); ?>>
		</div>
		<?php
	}

	/**
	 * <img> markup for a media control value.
	 *
	 * @param array $media Media value with 'id' and/or 'url'.
	 * @return string
	 */
	private function get_image_html( $media ) {
		$alt = get_bloginfo( 'name' );

		if ( ! empty( $media['id'] ) ) {
			return wp_get_attachment_image( absint( $media['id'] ), 'full', false, array( 'alt' => $alt ) );
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img src="' . esc_url( $media['url'] ) . '" alt="' . esc_attr( $alt ) . '">';
		}

		return '';
	}
}
