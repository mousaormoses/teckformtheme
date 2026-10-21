<?php
/**
 * Newsletter widget: an email sign-up form, inline or stacked, with an optional
 * name field and consent checkbox.
 *
 * Two modes: "Save in WordPress" stores sign-ups as Subscribers in the dashboard
 * (handled in inc/forms/newsletter.php, with CSV export), and "Send to a mailing
 * service" posts the form straight to a service such as Mailchimp.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/trait-form-style-controls.php';

class Newsletter extends Widget_Base {

	use Form_Style_Controls;

	/**
	 * BEM block of the widget's markup.
	 */
	const BLOCK = 'teckform-newsletter';

	public function get_name() {
		return 'teckform-newsletter';
	}

	public function get_title() {
		return esc_html__( 'Newsletter', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-email-field';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'newsletter', 'subscribe', 'email', 'signup', 'mailchimp', 'list', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-newsletter' );
	}

	public function get_script_depends() {
		return array( 'teckform-newsletter' );
	}

	protected function register_controls() {
		$this->register_form_controls();
		$this->register_service_controls();
		$this->register_message_controls();

		$this->register_box_style_controls();
		$this->register_form_label_style_controls( self::BLOCK );
		$this->register_form_field_style_controls( self::BLOCK );
		$this->register_form_button_style_controls( self::BLOCK, array( 'icon' => true ) );
		$this->register_form_message_style_controls( self::BLOCK );
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_form_controls() {
		$this->start_controls_section( 'section_form', array(
			'label' => esc_html__( 'Form', 'teckform' ),
		) );

		$this->add_control( 'mode', array(
			'label'   => esc_html__( 'Sign-ups Go To', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'wordpress',
			'options' => array(
				'wordpress' => esc_html__( 'Save in WordPress (Messages > Subscribers)', 'teckform' ),
				'external'  => esc_html__( 'Send to a mailing service', 'teckform' ),
			),
		) );

		$this->add_control( 'layout', array(
			'label'   => esc_html__( 'Layout', 'teckform' ),
			'type'    => Controls_Manager::CHOOSE,
			'default' => 'inline',
			'toggle'  => false,
			'options' => array(
				'inline'  => array(
					'title' => esc_html__( 'Inline: fields and button in one row', 'teckform' ),
					'icon'  => 'eicon-ellipsis-h',
				),
				'stacked' => array(
					'title' => esc_html__( 'Stacked: one field per row', 'teckform' ),
					'icon'  => 'eicon-ellipsis-v',
				),
			),
		) );

		$this->add_control( 'show_labels', array(
			'label'        => esc_html__( 'Show Labels', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Hidden labels are still read out by screen readers.', 'teckform' ),
		) );

		$this->add_control( 'email_label', array(
			'label'     => esc_html__( 'Email Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Email', 'teckform' ),
			'separator' => 'before',
		) );

		$this->add_control( 'email_placeholder', array(
			'label'   => esc_html__( 'Email Placeholder', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Your email address', 'teckform' ),
		) );

		$this->add_control( 'show_name', array(
			'label'        => esc_html__( 'Name Field', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );

		$this->add_control( 'name_label', array(
			'label'     => esc_html__( 'Name Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Name', 'teckform' ),
			'condition' => array( 'show_name' => 'yes' ),
		) );

		$this->add_control( 'name_placeholder', array(
			'label'     => esc_html__( 'Name Placeholder', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Your name', 'teckform' ),
			'condition' => array( 'show_name' => 'yes' ),
		) );

		$this->add_control( 'name_required', array(
			'label'        => esc_html__( 'Name Required', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'condition'    => array( 'show_name' => 'yes' ),
		) );

		$this->add_control( 'show_consent', array(
			'label'        => esc_html__( 'Consent Checkbox', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
			'separator'    => 'before',
			'description'  => esc_html__( 'Visitors must tick it to subscribe.', 'teckform' ),
		) );

		$this->add_control( 'consent_text', array(
			'label'       => esc_html__( 'Consent Text', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => esc_html__( 'I agree to receive emails and can unsubscribe at any time.', 'teckform' ),
			'description' => esc_html__( 'Links are allowed, e.g. to your privacy policy.', 'teckform' ),
			'condition'   => array( 'show_consent' => 'yes' ),
		) );

		$this->add_control( 'button_text', array(
			'label'     => esc_html__( 'Button Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Subscribe', 'teckform' ),
			'separator' => 'before',
		) );

		$this->add_control( 'button_icon', array(
			'label' => esc_html__( 'Button Icon', 'teckform' ),
			'type'  => Controls_Manager::ICONS,
		) );

		$this->add_control( 'icon_position', array(
			'label'     => esc_html__( 'Icon Position', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'after',
			'options'   => array(
				'before' => esc_html__( 'Before text', 'teckform' ),
				'after'  => esc_html__( 'After text', 'teckform' ),
			),
			'condition' => array( 'button_icon[value]!' => '' ),
		) );

		$this->add_control( 'list_name', array(
			'label'       => esc_html__( 'Form Name', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Newsletter', 'teckform' ),
			'separator'   => 'before',
			'description' => esc_html__( 'Saved with each subscriber so you can tell forms apart.', 'teckform' ),
			'condition'   => array( 'mode' => 'wordpress' ),
		) );

		$this->end_controls_section();
	}

	private function register_service_controls() {
		$this->start_controls_section( 'section_service', array(
			'label'     => esc_html__( 'Mailing Service', 'teckform' ),
			'condition' => array( 'mode' => 'external' ),
		) );

		$this->add_control( 'action_url', array(
			'label'       => esc_html__( 'Form Action URL', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'placeholder' => 'https://example.us1.list-manage.com/subscribe/post?u=...&id=...',
			'description' => esc_html__( 'The "action" address from your service\'s embedded form code (Mailchimp: Audience > Signup forms > Embedded forms).', 'teckform' ),
		) );

		$this->add_control( 'email_field_name', array(
			'label'       => esc_html__( 'Email Field Name', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'EMAIL',
			'description' => esc_html__( 'The "name" of the email input in the embedded code. Mailchimp uses EMAIL.', 'teckform' ),
		) );

		$this->add_control( 'name_field_name', array(
			'label'     => esc_html__( 'Name Field Name', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => 'FNAME',
			'condition' => array( 'show_name' => 'yes' ),
		) );

		$this->add_control( 'hidden_fields', array(
			'label'       => esc_html__( 'Extra Hidden Fields', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'placeholder' => 'tags=123',
			'description' => esc_html__( 'Optional. One name=value per line, sent along with the form.', 'teckform' ),
		) );

		$this->add_control( 'new_tab', array(
			'label'        => esc_html__( 'Open in New Tab', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'The service shows its own confirmation page.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	private function register_message_controls() {
		$this->start_controls_section( 'section_messages', array(
			'label'     => esc_html__( 'Messages', 'teckform' ),
			'condition' => array( 'mode' => 'wordpress' ),
		) );

		$this->add_control( 'success_message', array(
			'label'   => esc_html__( 'Success', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'Thanks for subscribing! Watch your inbox for our next update.', 'teckform' ),
		) );

		$this->add_control( 'duplicate_message', array(
			'label'   => esc_html__( 'Already Subscribed', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'Good news: you are already on our list. Thank you!', 'teckform' ),
		) );

		$this->add_control( 'error_message', array(
			'label'   => esc_html__( 'Error', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'Sorry, we could not sign you up. Please check your email address and try again.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_box_style_controls() {
		$this->start_controls_section( 'section_style_box', array(
			'label' => esc_html__( 'Form', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'form_width', array(
			'label'      => esc_html__( 'Max Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 200,
					'max' => 1200,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-newsletter__form' => 'max-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'form_align', array(
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
				'start'  => 'margin-inline: 0 auto;',
				'center' => 'margin-inline: auto;',
				'end'    => 'margin-inline: auto 0;',
			),
			'selectors'            => array(
				'{{WRAPPER}} .teckform-newsletter__form' => '{{VALUE}}',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = $this->get_id();
		$external = 'external' === $settings['mode'];
		$action   = $external ? trim( (string) $settings['action_url'] ) : admin_url( 'admin-post.php' );

		if ( $external && ( ! preg_match( '#^https?://#i', $action ) || '' === esc_url( $action ) ) ) {
			$this->render_editor_notice( self::BLOCK, __( 'Paste the form action URL from your mailing service in the Mailing Service section.', 'teckform' ) );
			return;
		}

		$flash  = $external ? null : teckform_form_get_flash( $id );
		$layout = 'stacked' === $settings['layout'] ? 'stacked' : 'inline';
		$names  = array(
			'email'   => $external ? $this->field_name( $settings['email_field_name'], 'EMAIL' ) : 'teckform_email',
			'name'    => $external ? $this->field_name( $settings['name_field_name'], 'FNAME' ) : 'teckform_name',
			'consent' => $external ? '' : 'teckform_consent',
		);

		$this->add_render_attribute( 'form', array(
			'class'  => 'teckform-newsletter__form',
			'method' => 'post',
			'action' => esc_url( $action ),
		) );

		if ( $external ) {
			if ( 'yes' === $settings['new_tab'] ) {
				$this->add_render_attribute( 'form', array(
					'target' => '_blank',
					'rel'    => 'noopener',
				) );
			}
		} else {
			$this->add_render_attribute( 'form', array(
				'data-ajax-url'      => admin_url( 'admin-ajax.php', 'relative' ),
				'data-error-message' => $settings['error_message'],
			) );
		}
		?>
		<div class="teckform-newsletter teckform-newsletter--<?php echo esc_attr( $layout ); ?>" id="teckform-<?php echo esc_attr( $id ); ?>">
			<form <?php $this->print_render_attribute_string( 'form' ); ?>>
				<?php
				if ( $external ) {
					$this->render_hidden_fields( $settings['hidden_fields'] );
				} else {
					teckform_form_hidden_fields( 'newsletter', $id );
				}
				?>
				<div class="teckform-newsletter__row">
					<?php
					if ( 'yes' === $settings['show_name'] ) {
						$this->render_input( 'name', 'text', $names['name'], $settings['name_label'], $settings['name_placeholder'], 'yes' === $settings['name_required'], $settings, $flash );
					}

					$this->render_input( 'email', 'email', $names['email'], $settings['email_label'], $settings['email_placeholder'], true, $settings, $flash );
					?>
					<div class="teckform-newsletter__submit">
						<button type="submit" class="teckform-newsletter__button">
							<?php $this->render_button_content( $settings ); ?>
						</button>
					</div>
				</div>
				<?php
				if ( 'yes' === $settings['show_consent'] ) {
					$this->render_consent( $settings, $names['consent'], $flash );
				}

				if ( ! $external ) {
					$this->render_form_message( self::BLOCK, $flash );
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * A safe input name for a mailing service field.
	 *
	 * @param string $name     Name from the settings.
	 * @param string $fallback Name to use when empty.
	 * @return string
	 */
	private function field_name( $name, $fallback ) {
		$name = preg_replace( '/[^A-Za-z0-9_\-\[\]]/', '', (string) $name );

		return '' !== $name ? $name : $fallback;
	}

	/**
	 * Extra hidden fields for a mailing service, from "name=value" lines.
	 *
	 * @param string $lines Setting value.
	 */
	private function render_hidden_fields( $lines ) {
		foreach ( teckform_form_lines( $lines ) as $line ) {
			$parts = explode( '=', $line, 2 );
			$name  = $this->field_name( $parts[0], '' );

			if ( '' === $name ) {
				continue;
			}

			printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $name ), esc_attr( isset( $parts[1] ) ? trim( $parts[1] ) : '' ) );
		}
	}

	/**
	 * Name or email field.
	 *
	 * @param string     $key         Field key used for error messages ('name' or 'email').
	 * @param string     $type        Input type.
	 * @param string     $name        Input name.
	 * @param string     $label       Label.
	 * @param string     $placeholder Placeholder.
	 * @param bool       $required    Whether it must be filled in.
	 * @param array      $settings    Widget settings.
	 * @param array|null $flash       Result of the last submission.
	 */
	private function render_input( $key, $type, $name, $label, $placeholder, $required, $settings, $flash ) {
		$block    = self::BLOCK;
		$input_id = 'teckform-' . $this->get_id() . '-' . $key;
		$value    = $this->get_flash_value( $flash, $key );

		list( $error_attrs, $error_note ) = $this->get_field_error( $block, $flash, $key, $input_id );

		if ( '' === trim( (string) $label ) ) {
			$label = 'email' === $key ? __( 'Email', 'teckform' ) : __( 'Name', 'teckform' );
		}
		?>
		<div class="<?php echo esc_attr( "{$block}__field {$block}__field--{$key}" ); ?>" data-field="<?php echo esc_attr( $key ); ?>">
			<label class="<?php echo esc_attr( $block . '__label' . ( 'yes' === $settings['show_labels'] ? '' : ' screen-reader-text' ) ); ?>" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php
			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" class="%4$s" value="%5$s" placeholder="%6$s" autocomplete="%7$s"%8$s%9$s>',
				esc_attr( $type ),
				esc_attr( $input_id ),
				esc_attr( $name ),
				esc_attr( $block . '__input' ),
				esc_attr( is_string( $value ) ? $value : '' ),
				esc_attr( $placeholder ),
				'email' === $key ? 'email' : 'name',
				$required ? ' required' : '',
				$error_attrs // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
			);

			echo $error_note; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
			?>
		</div>
		<?php
	}

	/**
	 * Consent checkbox. For a mailing service it has no name, so it only stops the
	 * form from being sent until it is ticked.
	 *
	 * @param array      $settings Widget settings.
	 * @param string     $name     Input name ('' for none).
	 * @param array|null $flash    Result of the last submission.
	 */
	private function render_consent( $settings, $name, $flash ) {
		$block    = self::BLOCK;
		$input_id = 'teckform-' . $this->get_id() . '-consent';

		list( $error_attrs, $error_note ) = $this->get_field_error( $block, $flash, 'consent', $input_id );

		$text = '' !== trim( (string) $settings['consent_text'] ) ? $settings['consent_text'] : __( 'I agree to receive emails.', 'teckform' );

		printf(
			'<div class="%1$s" data-field="consent"><label class="%2$s" for="%3$s"><input type="checkbox" id="%3$s"%4$s value="1" required%5$s> <span>%6$s</span></label>%7$s</div>',
			esc_attr( "{$block}__field {$block}__field--consent" ),
			esc_attr( $block . '__check' ),
			esc_attr( $input_id ),
			'' !== $name ? ' name="' . esc_attr( $name ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped here.
			$error_attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
			wp_kses( $text, teckform_form_inline_html() ),
			$error_note // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
		);
	}

	/**
	 * Button text with the optional icon before or after it.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_button_content( $settings ) {
		$icon = '';

		if ( ! empty( $settings['button_icon']['value'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['button_icon'], array( 'aria-hidden' => 'true' ) );
			$icon = '<span class="teckform-newsletter__button-icon">' . ob_get_clean() . '</span>';
		}

		$text = '' !== trim( (string) $settings['button_text'] ) ? $settings['button_text'] : __( 'Subscribe', 'teckform' );
		$text = '<span class="teckform-newsletter__button-text">' . esc_html( $text ) . '</span>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon markup from Elementor; text escaped above.
		echo 'before' === $settings['icon_position'] ? $icon . $text : $text . $icon;
	}
}
