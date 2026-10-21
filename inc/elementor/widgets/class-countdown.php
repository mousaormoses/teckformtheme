<?php
/**
 * Countdown widget: days, hours, minutes and seconds to a moment.
 *
 * "Fixed date" counts to a date and time in the site's time zone (Settings >
 * General). "Evergreen" gives each visitor their own deadline, a set time after
 * their first visit, remembered in their browser. When the time is up the
 * widget can hide itself, show a message or send the visitor to another page.
 * The current values are rendered on the server so nothing flashes empty.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Countdown extends Widget_Base {

	public function get_name() {
		return 'teckform-countdown';
	}

	public function get_title() {
		return esc_html__( 'Countdown', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-countdown';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'countdown', 'timer', 'clock', 'deadline', 'launch', 'sale', 'evergreen', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-countdown' );
	}

	public function get_script_depends() {
		return array( 'teckform-countdown' );
	}

	/**
	 * Units, largest first, with their length in seconds.
	 *
	 * @return array<string, int>
	 */
	private function get_units() {
		return array(
			'days'    => DAY_IN_SECONDS,
			'hours'   => HOUR_IN_SECONDS,
			'minutes' => MINUTE_IN_SECONDS,
			'seconds' => 1,
		);
	}

	/**
	 * Default unit labels.
	 *
	 * @return array<string, string>
	 */
	private function get_default_labels() {
		return array(
			'days'    => esc_html__( 'Days', 'teckform' ),
			'hours'   => esc_html__( 'Hours', 'teckform' ),
			'minutes' => esc_html__( 'Minutes', 'teckform' ),
			'seconds' => esc_html__( 'Seconds', 'teckform' ),
		);
	}

	protected function register_controls() {
		$this->register_timer_controls();
		$this->register_unit_controls();
		$this->register_expire_controls();

		$this->register_box_style_controls();
		$this->register_number_style_controls();
		$this->register_separator_style_controls();
		$this->register_message_style_controls();
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_timer_controls() {
		$this->start_controls_section( 'section_timer', array(
			'label' => esc_html__( 'Countdown', 'teckform' ),
		) );

		$this->add_control( 'mode', array(
			'label'   => esc_html__( 'Type', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'fixed',
			'options' => array(
				'fixed'     => esc_html__( 'Fixed date', 'teckform' ),
				'evergreen' => esc_html__( 'Evergreen (per visitor)', 'teckform' ),
			),
		) );

		$this->add_control( 'due_date', array(
			'label'          => esc_html__( 'Ends On', 'teckform' ),
			'type'           => Controls_Manager::DATE_TIME,
			'default'        => wp_date( 'Y-m-d H:i', time() + WEEK_IN_SECONDS ),
			'picker_options' => array(
				'enableTime' => true,
				'time_24hr'  => true,
			),
			/* translators: %s: site time zone, such as "Europe/London" or "+02:00". */
			'description'    => sprintf( esc_html__( 'In the site time zone (%s), set in Settings > General.', 'teckform' ), esc_html( wp_timezone_string() ) ),
			'condition'      => array( 'mode' => 'fixed' ),
		) );

		$this->add_control( 'evergreen_days', array(
			'label'     => esc_html__( 'Days', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 0,
			'max'       => 365,
			'default'   => 2,
			'condition' => array( 'mode' => 'evergreen' ),
		) );

		$this->add_control( 'evergreen_hours', array(
			'label'     => esc_html__( 'Hours', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 0,
			'max'       => 23,
			'default'   => 0,
			'condition' => array( 'mode' => 'evergreen' ),
		) );

		$this->add_control( 'evergreen_minutes', array(
			'label'       => esc_html__( 'Minutes', 'teckform' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 59,
			'default'     => 0,
			'description' => esc_html__( 'Each visitor gets this much time from their first visit. It is remembered in their browser, so reloading the page does not reset it. Changing the time starts everyone again.', 'teckform' ),
			'condition'   => array( 'mode' => 'evergreen' ),
		) );

		$this->end_controls_section();
	}

	private function register_unit_controls() {
		$this->start_controls_section( 'section_units', array(
			'label' => esc_html__( 'Display', 'teckform' ),
		) );

		$this->add_control( 'layout', array(
			'label'   => esc_html__( 'Layout', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'boxes',
			'options' => array(
				'boxes'  => esc_html__( 'Boxes', 'teckform' ),
				'inline' => esc_html__( 'Inline', 'teckform' ),
			),
		) );

		$this->add_responsive_control( 'align', array(
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
				'start'  => '--teckform-countdown-justify: flex-start; text-align: start;',
				'center' => '--teckform-countdown-justify: center; text-align: center;',
				'end'    => '--teckform-countdown-justify: flex-end; text-align: end;',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-countdown' => '{{VALUE}}',
			),
		) );

		foreach ( $this->get_default_labels() as $unit => $label ) {
			$this->add_control( "show_{$unit}", array(
				/* translators: %s: unit name, such as "Days". */
				'label'        => sprintf( esc_html__( 'Show %s', 'teckform' ), $label ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'separator'    => 'days' === $unit ? 'before' : 'default',
			) );
		}

		$this->add_control( 'units_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'A hidden unit is added to the one before it: without days, hours can go above 24.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
		) );

		$this->add_control( 'show_labels', array(
			'label'        => esc_html__( 'Show Labels', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );

		foreach ( $this->get_default_labels() as $unit => $label ) {
			$this->add_control( "label_{$unit}", array(
				/* translators: %s: unit name, such as "Days". */
				'label'     => sprintf( esc_html__( '%s Label', 'teckform' ), $label ),
				'type'      => Controls_Manager::TEXT,
				'default'   => $label,
				'condition' => array(
					'show_labels'  => 'yes',
					"show_{$unit}" => 'yes',
				),
			) );
		}

		$this->add_control( 'separators', array(
			'label'        => esc_html__( 'Separators', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );

		$this->add_control( 'separator_text', array(
			'label'     => esc_html__( 'Separator', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => ':',
			'condition' => array( 'separators' => 'yes' ),
		) );

		$this->end_controls_section();
	}

	private function register_expire_controls() {
		$this->start_controls_section( 'section_expire', array(
			'label' => esc_html__( 'When Finished', 'teckform' ),
		) );

		$this->add_control( 'expire_action', array(
			'label'   => esc_html__( 'Action', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'message',
			'options' => array(
				'none'     => esc_html__( 'Keep showing zeros', 'teckform' ),
				'hide'     => esc_html__( 'Hide the widget', 'teckform' ),
				'message'  => esc_html__( 'Show a message', 'teckform' ),
				'redirect' => esc_html__( 'Go to another page', 'teckform' ),
			),
		) );

		$this->add_control( 'expire_message', array(
			'label'     => esc_html__( 'Message', 'teckform' ),
			'type'      => Controls_Manager::TEXTAREA,
			'rows'      => 3,
			'default'   => esc_html__( 'This offer has ended. Thank you for your interest!', 'teckform' ),
			'dynamic'   => array( 'active' => true ),
			'condition' => array( 'expire_action' => 'message' ),
		) );

		$this->add_control( 'expire_url', array(
			'label'       => esc_html__( 'Page Address', 'teckform' ),
			'type'        => Controls_Manager::URL,
			'dynamic'     => array( 'active' => true ),
			'options'     => false,
			'placeholder' => 'https://your-site.com/offer-ended/',
			'condition'   => array( 'expire_action' => 'redirect' ),
		) );

		$this->add_control( 'expire_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Hiding and redirecting only happen on the live page, not in the editor.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'expire_action' => array( 'hide', 'redirect' ) ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_box_style_controls() {
		$this->start_controls_section( 'section_style_box', array(
			'label' => esc_html__( 'Boxes', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'unit_gap', array(
			'label'      => esc_html__( 'Space Between', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 80,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-countdown__units' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'unit_width', array(
			'label'      => esc_html__( 'Minimum Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 40,
					'max' => 240,
				),
			),
			'condition'  => array( 'layout' => 'boxes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-countdown__unit' => 'min-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'unit_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'condition'  => array( 'layout' => 'boxes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-countdown__unit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'unit_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'layout' => 'boxes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-countdown__unit' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'unit_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'condition'  => array( 'layout' => 'boxes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-countdown__unit' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'      => 'unit_border',
			'condition' => array( 'layout' => 'boxes' ),
			'selector'  => '{{WRAPPER}} .teckform-countdown__unit',
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'      => 'unit_shadow',
			'condition' => array( 'layout' => 'boxes' ),
			'selector'  => '{{WRAPPER}} .teckform-countdown__unit',
		) );

		$this->end_controls_section();
	}

	private function register_number_style_controls() {
		$this->start_controls_section( 'section_style_numbers', array(
			'label' => esc_html__( 'Numbers and Labels', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'number_typography',
			'label'    => esc_html__( 'Number Typography', 'teckform' ),
			'selector' => '{{WRAPPER}} .teckform-countdown__number',
		) );

		$this->add_control( 'number_color', array(
			'label'     => esc_html__( 'Number Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-countdown__number' => 'color: {{VALUE}};',
			),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'label_typography',
			'label'     => esc_html__( 'Label Typography', 'teckform' ),
			'separator' => 'before',
			'condition' => array( 'show_labels' => 'yes' ),
			'selector'  => '{{WRAPPER}} .teckform-countdown__label',
		) );

		$this->add_control( 'label_color', array(
			'label'     => esc_html__( 'Label Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'show_labels' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-countdown__label' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'label_spacing', array(
			'label'      => esc_html__( 'Space Between Number and Label', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 40,
				),
			),
			'condition'  => array( 'show_labels' => 'yes' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-countdown__unit' => 'gap: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_separator_style_controls() {
		$this->start_controls_section( 'section_style_separator', array(
			'label'     => esc_html__( 'Separators', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'separators' => 'yes' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'separator_typography',
			'selector' => '{{WRAPPER}} .teckform-countdown__separator',
		) );

		$this->add_control( 'separator_color', array(
			'label'     => esc_html__( 'Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-countdown__separator' => 'color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'separator_offset', array(
			'label'      => esc_html__( 'Vertical Offset', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => -40,
					'max' => 40,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-countdown__separator' => 'transform: translateY({{SIZE}}{{UNIT}});',
			),
		) );

		$this->end_controls_section();
	}

	private function register_message_style_controls() {
		$this->start_controls_section( 'section_style_message', array(
			'label'     => esc_html__( 'Finished Message', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'expire_action' => 'message' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'message_typography',
			'selector' => '{{WRAPPER}} .teckform-countdown__message',
		) );

		$this->add_control( 'message_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-countdown__message' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'message_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-countdown__message' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'message_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-countdown__message' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_control( 'message_radius', array(
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
				'{{WRAPPER}} .teckform-countdown__message' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'message_border',
			'selector' => '{{WRAPPER}} .teckform-countdown__message',
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$edit_mode = Plugin::$instance->editor->is_edit_mode();
		$action    = in_array( $settings['expire_action'], array( 'none', 'hide', 'message', 'redirect' ), true ) ? $settings['expire_action'] : 'none';
		$units     = $this->get_shown_units( $settings );

		$attributes = array(
			'class'       => array( 'teckform-countdown', 'teckform-countdown--' . ( 'inline' === $settings['layout'] ? 'inline' : 'boxes' ) ),
			'role'        => 'timer',
			'aria-label'  => __( 'Countdown', 'teckform' ),
			'data-expire' => $action,
			'data-units'  => implode( ',', $units ),
		);

		if ( 'evergreen' === $settings['mode'] ) {
			$duration  = max( 0, absint( $settings['evergreen_days'] ) * DAY_IN_SECONDS + absint( $settings['evergreen_hours'] ) * HOUR_IN_SECONDS + absint( $settings['evergreen_minutes'] ) * MINUTE_IN_SECONDS );
			$remaining = $duration;

			$attributes['data-duration'] = $duration;
			// Changing the duration changes the key, so every visitor starts again.
			$attributes['data-key'] = 'teckform-countdown-' . $this->get_id() . '-' . $duration;
		} else {
			$end       = $this->get_end_timestamp( $settings['due_date'] );
			$remaining = max( 0, $end - time() );

			$attributes['data-end'] = $end;
		}

		if ( 'redirect' === $action && ! empty( $settings['expire_url']['url'] ) ) {
			$attributes['data-redirect'] = esc_url_raw( $settings['expire_url']['url'] );
		}

		$expired = 'evergreen' !== $settings['mode'] && 0 === $remaining;

		// Already over when the page was built: skip the timer entirely where we can.
		if ( $expired && 'hide' === $action && ! $edit_mode ) {
			return;
		}

		$show_message = $expired && 'message' === $action;

		if ( $show_message ) {
			$attributes['class'][] = 'is-expired';
		}

		$this->add_render_attribute( 'wrapper', $attributes );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="teckform-countdown__units"<?php echo $show_message ? ' hidden' : ''; ?>>
				<?php $this->render_units( $settings, $units, $remaining ); ?>
			</div>
			<?php if ( 'message' === $action ) : ?>
				<div class="teckform-countdown__message"<?php echo $show_message ? '' : ' hidden'; ?>><?php echo wp_kses_post( $settings['expire_message'] ); ?></div>
			<?php endif; ?>
			<?php if ( $edit_mode && $expired && in_array( $action, array( 'hide', 'redirect' ), true ) ) : ?>
				<p class="teckform-countdown__editor-note"><?php echo 'hide' === $action ? esc_html__( 'The countdown has finished. Visitors no longer see this widget.', 'teckform' ) : esc_html__( 'The countdown has finished. Visitors are sent to the page you chose.', 'teckform' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Number boxes with their labels and optional separators.
	 *
	 * @param array $settings  Widget settings.
	 * @param array $units     Units to show.
	 * @param int   $remaining Seconds left.
	 */
	private function render_units( $settings, $units, $remaining ) {
		$values     = $this->split_seconds( $remaining, $units );
		$labels     = $this->get_default_labels();
		$show_label = 'yes' === $settings['show_labels'];
		$separator  = 'yes' === $settings['separators'] ? $settings['separator_text'] : '';

		foreach ( $units as $i => $unit ) {
			if ( $i > 0 && '' !== $separator ) {
				echo '<span class="teckform-countdown__separator" aria-hidden="true">' . esc_html( $separator ) . '</span>';
			}

			$label = '' !== trim( (string) $settings[ "label_{$unit}" ] ) ? $settings[ "label_{$unit}" ] : $labels[ $unit ];
			?>
			<div class="teckform-countdown__unit teckform-countdown__unit--<?php echo esc_attr( $unit ); ?>">
				<span class="teckform-countdown__number" data-unit="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( str_pad( (string) $values[ $unit ], 2, '0', STR_PAD_LEFT ) ); ?></span>
				<?php if ( $show_label ) : ?>
					<span class="teckform-countdown__label"><?php echo esc_html( $label ); ?></span>
				<?php else : ?>
					<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Units switched on, in order. Falls back to all of them if every one is off.
	 *
	 * @param array $settings Widget settings.
	 * @return string[]
	 */
	private function get_shown_units( $settings ) {
		$units = array();

		foreach ( array_keys( $this->get_units() ) as $unit ) {
			if ( 'yes' === $settings[ "show_{$unit}" ] ) {
				$units[] = $unit;
			}
		}

		return $units ? $units : array_keys( $this->get_units() );
	}

	/**
	 * Split seconds into the shown units. A hidden unit's time is carried into
	 * the next smaller shown unit (without days, hours can pass 24); time
	 * smaller than the last shown unit is dropped.
	 *
	 * @param int      $seconds Seconds left.
	 * @param string[] $units   Units to show.
	 * @return array<string, int>
	 */
	private function split_seconds( $seconds, $units ) {
		$values = array();

		foreach ( $this->get_units() as $unit => $length ) {
			if ( ! in_array( $unit, $units, true ) ) {
				continue;
			}

			$values[ $unit ] = (int) floor( $seconds / $length );
			$seconds        -= $values[ $unit ] * $length;
		}

		return $values;
	}

	/**
	 * Unix timestamp for a date entered in the site's time zone.
	 *
	 * @param string $date Date as saved by the DATE_TIME control ("Y-m-d H:i").
	 * @return int
	 */
	private function get_end_timestamp( $date ) {
		$date = trim( (string) $date );

		if ( '' === $date ) {
			return time();
		}

		try {
			$moment = new \DateTimeImmutable( $date, wp_timezone() );
		} catch ( \Exception $e ) {
			return time();
		}

		return $moment->getTimestamp();
	}
}
