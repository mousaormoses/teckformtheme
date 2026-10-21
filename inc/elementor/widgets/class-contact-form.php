<?php
/**
 * Contact Form widget: a form built from a list of fields that emails each
 * message and also saves it under Messages in the dashboard.
 *
 * Submissions are handled in inc/forms/contact-form.php, which reads this widget's
 * saved settings instead of trusting what the browser sends. The form works with
 * and without JavaScript.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/trait-form-style-controls.php';

class Contact_Form extends Widget_Base {

	use Form_Style_Controls;

	/**
	 * BEM block of the widget's markup.
	 */
	const BLOCK = 'teckform-contact-form';

	public function get_name() {
		return 'teckform-contact-form';
	}

	public function get_title() {
		return esc_html__( 'Contact Form', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'contact', 'form', 'email', 'message', 'enquiry', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-contact-form' );
	}

	public function get_script_depends() {
		return array( 'teckform-contact-form' );
	}

	protected function register_controls() {
		$this->register_field_controls();
		$this->register_submit_controls();
		$this->register_email_controls();
		$this->register_after_submit_controls();

		$this->register_form_label_style_controls( self::BLOCK, array( 'condition' => array( 'show_labels' => 'yes' ) ) );
		$this->register_form_field_style_controls( self::BLOCK, array( 'textarea' => true ) );
		$this->register_form_button_style_controls( self::BLOCK, array( 'icon' => true ) );
		$this->register_form_message_style_controls( self::BLOCK );
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_field_controls() {
		$this->start_controls_section( 'section_fields', array(
			'label' => esc_html__( 'Form Fields', 'teckform' ),
		) );

		$repeater = new Repeater();

		$repeater->add_control( 'field_type', array(
			'label'   => esc_html__( 'Type', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'text',
			'options' => teckform_contact_form_field_types(),
		) );

		$repeater->add_control( 'field_label', array(
			'label'       => esc_html__( 'Label', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'Field', 'teckform' ),
			'description' => esc_html__( 'Also names the field in the email and in {Label} subject placeholders.', 'teckform' ),
		) );

		$repeater->add_control( 'placeholder', array(
			'label'       => esc_html__( 'Placeholder', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'condition'   => array( 'field_type' => array( 'text', 'email', 'tel', 'number', 'textarea', 'select' ) ),
		) );

		$repeater->add_control( 'required', array(
			'label'        => esc_html__( 'Required', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => '',
			'return_value' => 'yes',
		) );

		$repeater->add_control( 'field_options', array(
			'label'       => esc_html__( 'Options', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => '',
			'description' => esc_html__( 'One per line. For checkboxes, leave empty to show a single yes/no checkbox.', 'teckform' ),
			'condition'   => array( 'field_type' => array( 'select', 'checkbox' ) ),
		) );

		$repeater->add_control( 'acceptance_text', array(
			'label'       => esc_html__( 'Consent Text', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => esc_html__( 'I agree that my details are stored to answer my message.', 'teckform' ),
			'description' => esc_html__( 'Links are allowed, e.g. to your privacy policy.', 'teckform' ),
			'condition'   => array( 'field_type' => 'acceptance' ),
		) );

		$repeater->add_control( 'rows', array(
			'label'     => esc_html__( 'Rows', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 2,
			'max'       => 30,
			'default'   => 5,
			'condition' => array( 'field_type' => 'textarea' ),
		) );

		$repeater->add_control( 'number_min', array(
			'label'     => esc_html__( 'Minimum', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'condition' => array( 'field_type' => 'number' ),
		) );

		$repeater->add_control( 'number_max', array(
			'label'     => esc_html__( 'Maximum', 'teckform' ),
			'type'      => Controls_Manager::NUMBER,
			'condition' => array( 'field_type' => 'number' ),
		) );

		$repeater->add_responsive_control( 'width', array(
			'label'                => esc_html__( 'Column Width', 'teckform' ),
			'type'                 => Controls_Manager::SELECT,
			'default'              => '100',
			'mobile_default'       => '100',
			'options'              => array(
				'100' => '100%',
				'50'  => '50%',
				'33'  => '33%',
			),
			'selectors_dictionary' => array(
				'100' => '1',
				'50'  => '0.5',
				'33'  => '0.3333',
			),
			'selectors'            => array(
				'{{WRAPPER}} {{CURRENT_ITEM}}' => '--teckform-form-fraction: {{VALUE}};',
			),
		) );

		$this->add_control( 'fields', array(
			'label'       => esc_html__( 'Fields', 'teckform' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ field_label }}}',
			'default'     => array(
				array(
					'field_type'   => 'text',
					'field_label'  => esc_html__( 'Name', 'teckform' ),
					'placeholder'  => esc_html__( 'Your name', 'teckform' ),
					'required'     => 'yes',
					'width'        => '50',
					'width_mobile' => '100',
				),
				array(
					'field_type'   => 'email',
					'field_label'  => esc_html__( 'Email', 'teckform' ),
					'placeholder'  => esc_html__( 'you@example.com', 'teckform' ),
					'required'     => 'yes',
					'width'        => '50',
					'width_mobile' => '100',
				),
				array(
					'field_type'  => 'textarea',
					'field_label' => esc_html__( 'Message', 'teckform' ),
					'placeholder' => esc_html__( 'How can we help?', 'teckform' ),
					'required'    => 'yes',
					'width'       => '100',
				),
			),
		) );

		$this->add_control( 'show_labels', array(
			'label'        => esc_html__( 'Show Labels', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
			'description'  => esc_html__( 'Hidden labels are still read out by screen readers.', 'teckform' ),
		) );

		$this->add_control( 'show_required_mark', array(
			'label'        => esc_html__( 'Required Mark (*)', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => array( 'show_labels' => 'yes' ),
		) );

		$this->end_controls_section();
	}

	private function register_submit_controls() {
		$this->start_controls_section( 'section_submit', array(
			'label' => esc_html__( 'Submit Button', 'teckform' ),
		) );

		$this->add_control( 'submit_text', array(
			'label'   => esc_html__( 'Text', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Send message', 'teckform' ),
		) );

		$this->add_control( 'submit_icon', array(
			'label' => esc_html__( 'Icon', 'teckform' ),
			'type'  => Controls_Manager::ICONS,
		) );

		$this->end_controls_section();
	}

	private function register_email_controls() {
		$this->start_controls_section( 'section_email', array(
			'label' => esc_html__( 'Email', 'teckform' ),
		) );

		$this->add_control( 'email_to', array(
			'label'       => esc_html__( 'Send To', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => get_option( 'admin_email' ),
			'placeholder' => get_option( 'admin_email' ),
			'description' => esc_html__( 'Separate several addresses with commas. Empty uses the site admin email.', 'teckform' ),
		) );

		$this->add_control( 'email_subject', array(
			'label'       => esc_html__( 'Subject', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'default'     => esc_html__( 'New message from {Name} on {site name}', 'teckform' ),
			'description' => esc_html__( 'Put a field label in braces, e.g. {Name}, to insert its value. {site name} and {page} also work.', 'teckform' ),
		) );

		$this->add_control( 'email_format', array(
			'label'   => esc_html__( 'Format', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'html',
			'options' => array(
				'html'  => esc_html__( 'HTML', 'teckform' ),
				'plain' => esc_html__( 'Plain text', 'teckform' ),
			),
		) );

		$this->add_control( 'form_name', array(
			'label'       => esc_html__( 'Form Name', 'teckform' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Contact form', 'teckform' ),
			'description' => esc_html__( 'Shown with each message in the dashboard, to tell forms apart.', 'teckform' ),
		) );

		$this->add_control( 'email_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Every message is also saved under Messages in the dashboard, so nothing is lost if an email does not arrive. The reply-to address is the first Email field.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
		) );

		$this->end_controls_section();
	}

	private function register_after_submit_controls() {
		$this->start_controls_section( 'section_after_submit', array(
			'label' => esc_html__( 'After Sending', 'teckform' ),
		) );

		$this->add_control( 'success_message', array(
			'label'   => esc_html__( 'Success Message', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'Thank you! Your message has been sent. We will get back to you soon.', 'teckform' ),
		) );

		$this->add_control( 'error_message', array(
			'label'   => esc_html__( 'Error Message', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'Sorry, your message could not be sent. Please try again later.', 'teckform' ),
		) );

		$this->add_control( 'redirect_url', array(
			'label'       => esc_html__( 'Redirect To', 'teckform' ),
			'type'        => Controls_Manager::URL,
			'dynamic'     => array( 'active' => true ),
			'options'     => false,
			'placeholder' => esc_html__( 'https://your-site.com/thank-you', 'teckform' ),
			'description' => esc_html__( 'Optional page to open after a successful message, instead of showing the success message.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$fields   = teckform_contact_form_fields( $settings );

		if ( ! $fields ) {
			$this->render_editor_notice( self::BLOCK, __( 'Add at least one field in the Form Fields section.', 'teckform' ) );
			return;
		}

		$id    = $this->get_id();
		$flash = teckform_form_get_flash( $id );
		?>
		<div class="teckform-contact-form" id="teckform-<?php echo esc_attr( $id ); ?>">
			<form class="teckform-contact-form__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php', 'relative' ) ); ?>" data-error-message="<?php echo esc_attr( $settings['error_message'] ); ?>">
				<?php teckform_form_hidden_fields( 'contact_form', $id ); ?>
				<div class="teckform-contact-form__fields">
					<?php
					foreach ( $fields as $field ) {
						$this->render_field( $field, $settings, $flash );
					}
					?>
				</div>
				<div class="teckform-contact-form__actions">
					<button type="submit" class="teckform-contact-form__button">
						<?php
						if ( ! empty( $settings['submit_icon']['value'] ) ) {
							echo '<span class="teckform-contact-form__button-icon">';
							Icons_Manager::render_icon( $settings['submit_icon'], array( 'aria-hidden' => 'true' ) );
							echo '</span>';
						}
						?>
						<span class="teckform-contact-form__button-text"><?php echo esc_html( '' !== $settings['submit_text'] ? $settings['submit_text'] : __( 'Send', 'teckform' ) ); ?></span>
					</button>
				</div>
				<?php $this->render_form_message( self::BLOCK, $flash ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * One field with its label and, after a failed submission without JavaScript,
	 * its error note and the visitor's value.
	 *
	 * @param array      $field    Field from teckform_contact_form_fields().
	 * @param array      $settings Widget settings.
	 * @param array|null $flash    Result of the last submission.
	 */
	private function render_field( $field, $settings, $flash ) {
		$block    = self::BLOCK;
		$input_id = 'teckform-' . $this->get_id() . '-' . $field['key'];
		$name     = 'teckform_fields[' . $field['key'] . ']';
		$value    = $this->get_flash_value( $flash, $field['key'] );
		$required = $field['required'] ? ' required' : '';

		list( $error_attrs, $error_note ) = $this->get_field_error( $block, $flash, $field['key'], $input_id );

		$classes = array( $block . '__field', $block . '__field--' . $field['type'] );

		if ( $field['item_id'] ) {
			$classes[] = 'elementor-repeater-item-' . $field['item_id'];
		}

		$label_class = $block . '__label' . ( 'yes' === $settings['show_labels'] ? '' : ' screen-reader-text' );
		$mark        = $field['required'] && 'yes' === $settings['show_labels'] && 'yes' === $settings['show_required_mark']
			? ' <span class="' . esc_attr( $block . '__required' ) . '" aria-hidden="true">*</span>'
			: '';

		// Checkbox group: a fieldset with one checkbox per option.
		if ( 'checkbox' === $field['type'] && $field['options'] ) {
			$checked = is_array( $value ) ? $value : array();

			echo '<fieldset class="' . esc_attr( implode( ' ', $classes ) ) . '" data-field="' . esc_attr( $field['key'] ) . '"' . ( $error_note ? ' aria-describedby="' . esc_attr( $input_id . '-error' ) . '"' : '' ) . '>';
			echo '<legend class="' . esc_attr( $label_class ) . '">' . esc_html( $field['label'] ) . $mark . '</legend>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $mark is escaped above.
			echo '<div class="' . esc_attr( $block . '__options' ) . '">';

			foreach ( $field['options'] as $i => $option ) {
				printf(
					'<label class="%1$s"><input type="checkbox" id="%2$s" name="%3$s[]" value="%4$s"%5$s> <span>%6$s</span></label>',
					esc_attr( $block . '__check' ),
					esc_attr( $input_id . '-' . $i ),
					esc_attr( $name ),
					esc_attr( $option ),
					checked( in_array( $option, $checked, true ), true, false ),
					esc_html( $option )
				);
			}

			echo '</div>' . $error_note . '</fieldset>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
			return;
		}

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-field="' . esc_attr( $field['key'] ) . '">';

		// Single checkbox or consent: the text sits beside the box.
		if ( in_array( $field['type'], array( 'checkbox', 'acceptance' ), true ) ) {
			$text = 'acceptance' === $field['type'] && '' !== trim( $field['text'] ) ? wp_kses( $field['text'], teckform_form_inline_html() ) : esc_html( $field['label'] );

			printf(
				'<label class="%1$s" for="%2$s"><input type="checkbox" id="%2$s" name="%3$s" value="1"%4$s%5$s%6$s> <span>%7$s%8$s</span></label>',
				esc_attr( $block . '__check' ),
				esc_attr( $input_id ),
				esc_attr( $name ),
				checked( '1', $value, false ),
				$required, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed string.
				$error_attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
				$text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
				$field['required'] && 'yes' === $settings['show_required_mark'] ? ' <span class="' . esc_attr( $block . '__required' ) . '" aria-hidden="true">*</span>' : ''
			);

			echo $error_note . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
			return;
		}

		echo '<label class="' . esc_attr( $label_class ) . '" for="' . esc_attr( $input_id ) . '">' . esc_html( $field['label'] ) . $mark . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $mark is escaped above.

		$attrs = sprintf(
			'id="%1$s" name="%2$s" class="%3$s"%4$s%5$s',
			esc_attr( $input_id ),
			esc_attr( $name ),
			esc_attr( $block . '__input' ),
			$required,
			$error_attrs
		);

		$placeholder = '' !== $field['placeholder'] ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
		$value       = is_string( $value ) ? $value : '';

		if ( 'textarea' === $field['type'] ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attrs and $placeholder are escaped above.
			echo '<textarea ' . $attrs . $placeholder . ' rows="' . esc_attr( $field['rows'] ) . '">' . esc_textarea( $value ) . '</textarea>';
		} elseif ( 'select' === $field['type'] ) {
			echo '<select ' . $attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
			echo '<option value="">' . esc_html( '' !== $field['placeholder'] ? $field['placeholder'] : __( 'Choose an option', 'teckform' ) ) . '</option>';

			foreach ( $field['options'] as $option ) {
				echo '<option value="' . esc_attr( $option ) . '"' . selected( $value, $option, false ) . '>' . esc_html( $option ) . '</option>';
			}

			echo '</select>';
		} else {
			$extra = '';

			if ( 'email' === $field['type'] ) {
				$extra = ' autocomplete="email"';
			} elseif ( 'tel' === $field['type'] ) {
				$extra = ' autocomplete="tel"';
			} elseif ( 'number' === $field['type'] ) {
				$extra .= '' !== $field['min'] ? ' min="' . esc_attr( $field['min'] ) . '"' : '';
				$extra .= '' !== $field['max'] ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
				$extra .= ' step="any"';
			}

			printf(
				'<input type="%1$s" %2$s value="%3$s"%4$s%5$s>',
				esc_attr( $field['type'] ),
				$attrs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
				esc_attr( $value ),
				$placeholder, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
				$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
			);
		}

		echo $error_note . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
	}
}
