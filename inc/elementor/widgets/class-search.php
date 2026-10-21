<?php
/**
 * Search widget: a search icon that opens a full-screen search, or an inline search box.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Search extends Widget_Base {

	public function get_name() {
		return 'teckform-search';
	}

	public function get_title() {
		return esc_html__( 'Search', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return array( 'teckform-site' );
	}

	public function get_keywords() {
		return array( 'search', 'form', 'header', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-widgets' );
	}

	public function get_script_depends() {
		return array( 'teckform-widgets' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array(
			'label' => esc_html__( 'Search', 'teckform' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'layout', array(
			'label'   => esc_html__( 'Layout', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'icon',
			'options' => array(
				'icon' => esc_html__( 'Icon (opens full-screen search)', 'teckform' ),
				'form' => esc_html__( 'Search box', 'teckform' ),
			),
		) );

		$this->add_control( 'placeholder', array(
			'label'   => esc_html__( 'Placeholder', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Search...', 'teckform' ),
		) );

		$this->add_control( 'icon', array(
			'label'   => esc_html__( 'Icon', 'teckform' ),
			'type'    => Controls_Manager::ICONS,
			'default' => array(
				'value'   => 'fas fa-search',
				'library' => 'fa-solid',
			),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_icon', array(
			'label' => esc_html__( 'Icon', 'teckform' ),
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
			'condition' => array( 'layout' => 'icon' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-search' => 'text-align: {{VALUE}};',
			),
		) );

		$this->add_control( 'icon_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-search__toggle, {{WRAPPER}} .teckform-search--form .teckform-search__submit' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'icon_hover_color', array(
			'label'     => esc_html__( 'Hover Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-search__toggle:hover, {{WRAPPER}} .teckform-search__submit:hover' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'icon_size', array(
			'label'      => esc_html__( 'Size', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 10,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-search__toggle, {{WRAPPER}} .teckform-search--form .teckform-search__submit' => 'font-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_box', array(
			'label' => esc_html__( 'Search Box', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'form_width', array(
			'label'      => esc_html__( 'Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 120,
					'max' => 800,
				),
			),
			'condition'  => array( 'layout' => 'form' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-search--form .teckform-search__form' => 'width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'input_typography',
			'selector' => '{{WRAPPER}} .teckform-search__input',
		) );

		$this->add_control( 'input_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-search__form' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'input_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-search__input' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'input_border', array(
			'label'     => esc_html__( 'Border Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-search__form' => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'input_radius', array(
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
				'{{WRAPPER}} .teckform-search__form' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'overlay_bg', array(
			'label'     => esc_html__( 'Full-screen Background', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'condition' => array( 'layout' => 'icon' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-search__overlay' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'close_color', array(
			'label'     => esc_html__( 'Close Button Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'layout' => 'icon' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-search__close' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( 'form' === $settings['layout'] ) {
			echo '<div class="teckform-search teckform-search--form">';
			$this->render_form( $settings );
			echo '</div>';
			return;
		}
		?>
		<div class="teckform-search teckform-search--icon">
			<button type="button" class="teckform-search__toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open search', 'teckform' ); ?>">
				<?php $this->render_icon( $settings['icon'] ); ?>
			</button>
			<div class="teckform-search__overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Search', 'teckform' ); ?>">
				<button type="button" class="teckform-search__close" aria-label="<?php esc_attr_e( 'Close search', 'teckform' ); ?>"><span aria-hidden="true">&times;</span></button>
				<?php $this->render_form( $settings ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * The search form itself.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_form( $settings ) {
		$input_id = 'teckform-search-' . $this->get_id();
		?>
		<form role="search" method="get" class="teckform-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Search for:', 'teckform' ); ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" class="teckform-search__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $settings['placeholder'] ); ?>">
			<button type="submit" class="teckform-search__submit" aria-label="<?php esc_attr_e( 'Search', 'teckform' ); ?>">
				<?php $this->render_icon( $settings['icon'] ); ?>
			</button>
		</form>
		<?php
	}

	/**
	 * The chosen icon, or a plain magnifier when none is set.
	 *
	 * @param array $icon Icon control value.
	 */
	private function render_icon( $icon ) {
		if ( ! empty( $icon['value'] ) ) {
			Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) );
			return;
		}

		echo '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2a8 8 0 016.3 12.9l5.4 5.4-1.4 1.4-5.4-5.4A8 8 0 1110 2zm0 2a6 6 0 100 12 6 6 0 000-12z"/></svg>';
	}
}
