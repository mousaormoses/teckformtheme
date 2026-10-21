<?php
/**
 * Login / Register widget: a front-end login form with show/hide password,
 * "remember me" and a lost-password link, plus an optional Register tab.
 *
 * Logged-in visitors see a short greeting with a log-out link, or nothing. The
 * Elementor editor always previews the logged-out form unless "Preview in Editor"
 * says otherwise. Submissions are handled in inc/forms/login-form.php.
 *
 * @package Teckform
 */

namespace Teckform\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/trait-form-style-controls.php';

class Login_Form extends Widget_Base {

	use Form_Style_Controls;

	/**
	 * BEM block of the widget's markup.
	 */
	const BLOCK = 'teckform-login';

	public function get_name() {
		return 'teckform-login-form';
	}

	public function get_title() {
		return esc_html__( 'Login / Register', 'teckform' );
	}

	public function get_icon() {
		return 'eicon-lock-user';
	}

	public function get_categories() {
		return array( 'teckform-sections' );
	}

	public function get_keywords() {
		return array( 'login', 'register', 'sign in', 'sign up', 'account', 'user', 'password', 'teckform' );
	}

	public function get_style_depends() {
		return array( 'teckform-login-form' );
	}

	public function get_script_depends() {
		return array( 'teckform-login-form' );
	}

	protected function register_controls() {
		$this->register_login_controls();
		$this->register_register_controls();
		$this->register_logged_in_controls();
		$this->register_message_controls();

		$this->register_box_style_controls();
		$this->register_tab_style_controls();
		$this->register_form_label_style_controls( self::BLOCK, array( 'condition' => array( 'show_labels' => 'yes' ) ) );
		$this->register_form_field_style_controls( self::BLOCK, array( 'columns' => false ) );
		$this->register_form_button_style_controls( self::BLOCK, array( 'full_width' => true ) );
		$this->register_link_style_controls();
		$this->register_form_message_style_controls( self::BLOCK );
	}

	/* ---------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_login_controls() {
		$this->start_controls_section( 'section_login', array(
			'label' => esc_html__( 'Login Form', 'teckform' ),
		) );

		$this->add_control( 'show_labels', array(
			'label'        => esc_html__( 'Show Labels', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'description'  => esc_html__( 'Hidden labels are still read out by screen readers.', 'teckform' ),
		) );

		$this->add_control( 'login_label', array(
			'label'     => esc_html__( 'Username Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Username or email', 'teckform' ),
			'separator' => 'before',
		) );

		$this->add_control( 'login_placeholder', array(
			'label' => esc_html__( 'Username Placeholder', 'teckform' ),
			'type'  => Controls_Manager::TEXT,
		) );

		$this->add_control( 'password_label', array(
			'label'   => esc_html__( 'Password Label', 'teckform' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Password', 'teckform' ),
		) );

		$this->add_control( 'password_placeholder', array(
			'label' => esc_html__( 'Password Placeholder', 'teckform' ),
			'type'  => Controls_Manager::TEXT,
		) );

		$this->add_control( 'show_password_toggle', array(
			'label'        => esc_html__( 'Show / Hide Password Button', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'show_remember', array(
			'label'        => esc_html__( 'Remember Me', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'separator'    => 'before',
		) );

		$this->add_control( 'remember_text', array(
			'label'     => esc_html__( 'Remember Me Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Remember me', 'teckform' ),
			'condition' => array( 'show_remember' => 'yes' ),
		) );

		$this->add_control( 'show_lost_password', array(
			'label'        => esc_html__( 'Lost Password Link', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'lost_password_text', array(
			'label'     => esc_html__( 'Lost Password Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Forgot your password?', 'teckform' ),
			'condition' => array( 'show_lost_password' => 'yes' ),
		) );

		$this->add_control( 'login_button_text', array(
			'label'     => esc_html__( 'Button Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Log in', 'teckform' ),
			'separator' => 'before',
		) );

		$this->end_controls_section();
	}

	private function register_register_controls() {
		$this->start_controls_section( 'section_register', array(
			'label' => esc_html__( 'Register Tab', 'teckform' ),
		) );

		$this->add_control( 'show_register', array(
			'label'        => esc_html__( 'Show Register Tab', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->add_control( 'register_note_admin', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => get_option( 'users_can_register' )
				? esc_html__( 'Registration is open on this site (Settings > General > Membership).', 'teckform' )
				: esc_html__( 'Registration is closed on this site, so the tab is hidden. Turn on "Anyone can register" in Settings > General to show it.', 'teckform' ),
			'content_classes' => 'elementor-descriptor',
			'condition'       => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'login_tab_text', array(
			'label'     => esc_html__( 'Login Tab Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Log in', 'teckform' ),
			'condition' => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'register_tab_text', array(
			'label'     => esc_html__( 'Register Tab Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Create account', 'teckform' ),
			'condition' => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'reg_login_label', array(
			'label'     => esc_html__( 'Username Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Username', 'teckform' ),
			'separator' => 'before',
			'condition' => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'reg_email_label', array(
			'label'     => esc_html__( 'Email Label', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Email', 'teckform' ),
			'condition' => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'register_note', array(
			'label'     => esc_html__( 'Note', 'teckform' ),
			'type'      => Controls_Manager::TEXTAREA,
			'rows'      => 2,
			'default'   => esc_html__( 'We will email you a link to set your password.', 'teckform' ),
			'condition' => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'register_button_text', array(
			'label'     => esc_html__( 'Button Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Create account', 'teckform' ),
			'condition' => array( 'show_register' => 'yes' ),
		) );

		$this->end_controls_section();
	}

	private function register_logged_in_controls() {
		$this->start_controls_section( 'section_logged_in', array(
			'label' => esc_html__( 'After Login', 'teckform' ),
		) );

		$this->add_control( 'redirect_type', array(
			'label'   => esc_html__( 'After Logging In Go To', 'teckform' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'current',
			'options' => array(
				'current' => esc_html__( 'The same page', 'teckform' ),
				'custom'  => esc_html__( 'Another page', 'teckform' ),
			),
		) );

		$this->add_control( 'redirect_url', array(
			'label'     => esc_html__( 'Page URL', 'teckform' ),
			'type'      => Controls_Manager::URL,
			'dynamic'   => array( 'active' => true ),
			'options'   => false,
			'condition' => array( 'redirect_type' => 'custom' ),
		) );

		$this->add_control( 'logged_in_action', array(
			'label'     => esc_html__( 'When Already Logged In', 'teckform' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'message',
			'separator' => 'before',
			'options'   => array(
				'message' => esc_html__( 'Show a message', 'teckform' ),
				'hide'    => esc_html__( 'Hide the widget', 'teckform' ),
			),
		) );

		$this->add_control( 'logged_in_message', array(
			'label'       => esc_html__( 'Message', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 2,
			'default'     => esc_html__( 'Hello, {name}!', 'teckform' ),
			'description' => esc_html__( '{name} is replaced with the visitor\'s display name.', 'teckform' ),
			'condition'   => array( 'logged_in_action' => 'message' ),
		) );

		$this->add_control( 'show_logout', array(
			'label'        => esc_html__( 'Log Out Link', 'teckform' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => array( 'logged_in_action' => 'message' ),
		) );

		$this->add_control( 'logout_text', array(
			'label'     => esc_html__( 'Log Out Text', 'teckform' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => esc_html__( 'Log out', 'teckform' ),
			'condition' => array(
				'logged_in_action' => 'message',
				'show_logout'      => 'yes',
			),
		) );

		$this->add_control( 'editor_preview', array(
			'label'       => esc_html__( 'Preview in Editor', 'teckform' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'logged_out',
			'separator'   => 'before',
			'options'     => array(
				'logged_out' => esc_html__( 'Login form', 'teckform' ),
				'logged_in'  => esc_html__( 'Logged-in message', 'teckform' ),
			),
			'description' => esc_html__( 'Only changes what you see while editing.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	private function register_message_controls() {
		$this->start_controls_section( 'section_messages', array(
			'label' => esc_html__( 'Messages', 'teckform' ),
		) );

		$this->add_control( 'login_success_message', array(
			'label'   => esc_html__( 'Logged In', 'teckform' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 2,
			'default' => esc_html__( 'You are logged in. Taking you there now…', 'teckform' ),
		) );

		$this->add_control( 'register_success_message', array(
			'label'     => esc_html__( 'Registered', 'teckform' ),
			'type'      => Controls_Manager::TEXTAREA,
			'rows'      => 2,
			'default'   => esc_html__( 'Your account is ready. Check your email for a link to set your password.', 'teckform' ),
			'condition' => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'error_message', array(
			'label'       => esc_html__( 'Connection Error', 'teckform' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 2,
			'default'     => esc_html__( 'Something went wrong. Please check your connection and try again.', 'teckform' ),
			'description' => esc_html__( 'Wrong passwords and other login problems show WordPress\'s own message.', 'teckform' ),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_box_style_controls() {
		$this->start_controls_section( 'section_style_box', array(
			'label' => esc_html__( 'Box', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'box_width', array(
			'label'      => esc_html__( 'Max Width', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%' ),
			'range'      => array(
				'px' => array(
					'min' => 240,
					'max' => 900,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-login' => 'max-width: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'box_align', array(
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
				'{{WRAPPER}} .teckform-login' => '{{VALUE}}',
			),
		) );

		$this->add_control( 'box_bg', array(
			'label'     => esc_html__( 'Background Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array(
				'{{WRAPPER}} .teckform-login' => 'background-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'box_padding', array(
			'label'      => esc_html__( 'Padding', 'teckform' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-login' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'box_border',
			'selector' => '{{WRAPPER}} .teckform-login',
		) );

		$this->add_control( 'box_radius', array(
			'label'      => esc_html__( 'Border Radius', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 48,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-login' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'box_shadow',
			'selector' => '{{WRAPPER}} .teckform-login',
		) );

		$this->end_controls_section();
	}

	private function register_tab_style_controls() {
		$this->start_controls_section( 'section_style_tabs', array(
			'label'     => esc_html__( 'Tabs', 'teckform' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => array( 'show_register' => 'yes' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'tab_typography',
			'selector' => '{{WRAPPER}} .teckform-login__tab',
		) );

		$this->add_control( 'tab_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__tab' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'tab_active_color', array(
			'label'     => esc_html__( 'Active Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__tab[aria-selected="true"]' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'tab_indicator_color', array(
			'label'     => esc_html__( 'Active Line Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__tab[aria-selected="true"]' => 'border-bottom-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'tab_border_color', array(
			'label'     => esc_html__( 'Divider Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__tabs' => 'border-bottom-color: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'tab_spacing', array(
			'label'      => esc_html__( 'Space Below', 'teckform' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min' => 0,
					'max' => 60,
				),
			),
			'selectors'  => array(
				'{{WRAPPER}} .teckform-login__tabs' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	private function register_link_style_controls() {
		$this->start_controls_section( 'section_style_links', array(
			'label' => esc_html__( 'Links and Text', 'teckform' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'link_typography',
			'selector' => '{{WRAPPER}} .teckform-login__link',
		) );

		$this->add_control( 'link_color', array(
			'label'     => esc_html__( 'Link Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__link' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'link_hover_color', array(
			'label'     => esc_html__( 'Link Hover Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__link:hover, {{WRAPPER}} .teckform-login__link:focus-visible' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'toggle_color', array(
			'label'     => esc_html__( 'Show Password Icon Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'show_password_toggle' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__toggle' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'note_color', array(
			'label'     => esc_html__( 'Note Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'show_register' => 'yes' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__note' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'welcome_heading', array(
			'label'     => esc_html__( 'Logged-in Message', 'teckform' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => array( 'logged_in_action' => 'message' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'      => 'welcome_typography',
			'condition' => array( 'logged_in_action' => 'message' ),
			'selector'  => '{{WRAPPER}} .teckform-login__welcome-text',
		) );

		$this->add_control( 'welcome_color', array(
			'label'     => esc_html__( 'Text Color', 'teckform' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'logged_in_action' => 'message' ),
			'selectors' => array(
				'{{WRAPPER}} .teckform-login__welcome-text' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------ */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$editor   = teckform_form_is_editor();

		// The editor user is always logged in, so the editor shows whichever state is picked for preview.
		$logged_in = $editor ? 'logged_in' === $settings['editor_preview'] : is_user_logged_in();

		if ( $logged_in ) {
			$this->render_logged_in( $settings, $editor );
			return;
		}

		$id           = $this->get_id();
		$flash        = teckform_form_get_flash( $id );
		$can_register = teckform_login_can_register( $settings );
		$active       = $can_register && $flash && 'register' === $flash['panel'] ? 'register' : 'login';
		$tabs         = array(
			'login'    => '' !== trim( (string) $settings['login_tab_text'] ) ? $settings['login_tab_text'] : __( 'Log in', 'teckform' ),
			'register' => '' !== trim( (string) $settings['register_tab_text'] ) ? $settings['register_tab_text'] : __( 'Create account', 'teckform' ),
		);
		?>
		<div class="teckform-login<?php echo $can_register ? ' teckform-login--has-register' : ''; ?>" id="teckform-<?php echo esc_attr( $id ); ?>">
			<?php if ( $can_register ) : ?>
				<?php // Tabs appear once the script runs; without it both forms are shown, one under the other. ?>
				<div class="teckform-login__tabs" role="tablist" hidden>
					<?php foreach ( $tabs as $panel => $label ) : ?>
						<button type="button" class="teckform-login__tab" role="tab" id="<?php echo esc_attr( "teckform-{$id}-tab-{$panel}" ); ?>" aria-controls="<?php echo esc_attr( "teckform-{$id}-panel-{$panel}" ); ?>" aria-selected="<?php echo $active === $panel ? 'true' : 'false'; ?>" tabindex="<?php echo $active === $panel ? '0' : '-1'; ?>" data-panel="<?php echo esc_attr( $panel ); ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="teckform-login__panel" id="<?php echo esc_attr( "teckform-{$id}-panel-login" ); ?>"<?php echo $can_register ? ' role="tabpanel" aria-labelledby="' . esc_attr( "teckform-{$id}-tab-login" ) . '"' : ''; ?> data-panel="login"<?php echo 'login' === $active ? ' data-active' : ''; ?>>
				<?php
				if ( $can_register ) {
					echo '<h3 class="teckform-login__panel-title">' . esc_html( $tabs['login'] ) . '</h3>';
				}

				$this->render_login_form( $settings, $flash && 'login' === $flash['panel'] ? $flash : null );
				?>
			</div>

			<?php if ( $can_register ) : ?>
				<div class="teckform-login__panel" id="<?php echo esc_attr( "teckform-{$id}-panel-register" ); ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( "teckform-{$id}-tab-register" ); ?>" data-panel="register"<?php echo 'register' === $active ? ' data-active' : ''; ?>>
					<h3 class="teckform-login__panel-title"><?php echo esc_html( $tabs['register'] ); ?></h3>
					<?php $this->render_register_form( $settings, $flash && 'register' === $flash['panel'] ? $flash : null ); ?>
				</div>
			<?php endif; ?>

			<?php
			if ( 'yes' === $settings['show_register'] && ! get_option( 'users_can_register' ) ) {
				$this->render_editor_notice( self::BLOCK, __( 'The Register tab is hidden because registration is closed. Turn on "Anyone can register" in Settings > General. Only you see this note.', 'teckform' ) );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Greeting and log-out link for logged-in visitors.
	 *
	 * @param array $settings Widget settings.
	 * @param bool  $editor   Whether this is the Elementor editor.
	 */
	private function render_logged_in( $settings, $editor ) {
		if ( 'hide' === $settings['logged_in_action'] ) {
			$this->render_editor_notice( self::BLOCK, __( 'Logged-in visitors don\'t see this widget.', 'teckform' ) );
			return;
		}

		$user = wp_get_current_user();
		$name = $user->exists() ? $user->display_name : __( 'Jane', 'teckform' );
		$text = str_replace( '{name}', $name, (string) $settings['logged_in_message'] );
		?>
		<div class="teckform-login teckform-login--logged-in">
			<p class="teckform-login__welcome">
				<?php if ( '' !== trim( $text ) ) : ?>
					<span class="teckform-login__welcome-text"><?php echo esc_html( $text ); ?></span>
				<?php endif; ?>
				<?php if ( 'yes' === $settings['show_logout'] ) : ?>
					<a class="teckform-login__link teckform-login__logout" href="<?php echo esc_url( $editor ? '#' : wp_logout_url( teckform_form_current_url() ) ); ?>"><?php echo esc_html( '' !== $settings['logout_text'] ? $settings['logout_text'] : __( 'Log out', 'teckform' ) ); ?></a>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Opening <form> tag shared by the login and register forms.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_form_open( $settings ) {
		printf(
			'<form class="teckform-login__form" method="post" action="%1$s" data-ajax-url="%2$s" data-error-message="%3$s">',
			esc_url( admin_url( 'admin-post.php' ) ),
			esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
			esc_attr( $settings['error_message'] )
		);
	}

	/**
	 * @param array      $settings Widget settings.
	 * @param array|null $flash    Result of the last login attempt.
	 */
	private function render_login_form( $settings, $flash ) {
		$this->render_form_open( $settings );
		teckform_form_hidden_fields( 'login', $this->get_id() );

		$this->render_text_field( 'log', 'text', $settings['login_label'], __( 'Username or email', 'teckform' ), $settings['login_placeholder'], 'username', $settings, $flash );
		$this->render_password_field( $settings, $flash );

		$remember = 'yes' === $settings['show_remember'];
		$lost     = 'yes' === $settings['show_lost_password'];

		if ( $remember || $lost ) {
			echo '<div class="teckform-login__meta">';

			if ( $remember ) {
				printf(
					'<label class="teckform-login__check"><input type="checkbox" name="rememberme" value="forever"> <span>%s</span></label>',
					esc_html( '' !== $settings['remember_text'] ? $settings['remember_text'] : __( 'Remember me', 'teckform' ) )
				);
			}

			if ( $lost ) {
				printf(
					'<a class="teckform-login__link teckform-login__lost" href="%1$s">%2$s</a>',
					esc_url( wp_lostpassword_url( teckform_form_current_url() ) ),
					esc_html( '' !== $settings['lost_password_text'] ? $settings['lost_password_text'] : __( 'Forgot your password?', 'teckform' ) )
				);
			}

			echo '</div>';
		}

		$this->render_submit( $settings['login_button_text'], __( 'Log in', 'teckform' ) );
		$this->render_form_message( self::BLOCK, $flash );
		echo '</form>';
	}

	/**
	 * @param array      $settings Widget settings.
	 * @param array|null $flash    Result of the last registration attempt.
	 */
	private function render_register_form( $settings, $flash ) {
		$this->render_form_open( $settings );
		teckform_form_hidden_fields( 'register', $this->get_id() );

		$this->render_text_field( 'user_login', 'text', $settings['reg_login_label'], __( 'Username', 'teckform' ), '', 'username', $settings, $flash );
		$this->render_text_field( 'user_email', 'email', $settings['reg_email_label'], __( 'Email', 'teckform' ), '', 'email', $settings, $flash );

		if ( '' !== trim( (string) $settings['register_note'] ) ) {
			echo '<p class="teckform-login__note">' . esc_html( $settings['register_note'] ) . '</p>';
		}

		$this->render_submit( $settings['register_button_text'], __( 'Create account', 'teckform' ) );
		$this->render_form_message( self::BLOCK, $flash );
		echo '</form>';
	}

	/**
	 * Label and input, with an error note after a failed attempt without JavaScript.
	 *
	 * @param string     $name         Input name, also the error key.
	 * @param string     $type         Input type.
	 * @param string     $label        Label from the settings.
	 * @param string     $fallback     Label when the setting is empty.
	 * @param string     $placeholder  Placeholder.
	 * @param string     $autocomplete Autocomplete hint.
	 * @param array      $settings     Widget settings.
	 * @param array|null $flash        Result of the last attempt.
	 */
	private function render_text_field( $name, $type, $label, $fallback, $placeholder, $autocomplete, $settings, $flash ) {
		$input_id = 'teckform-' . $this->get_id() . '-' . $name;
		$value    = $this->get_flash_value( $flash, $name );

		list( $error_attrs, $error_note ) = $this->get_field_error( self::BLOCK, $flash, $name, $input_id );

		echo '<div class="teckform-login__field" data-field="' . esc_attr( $name ) . '">';
		$this->render_label( $input_id, $label, $fallback, $settings );

		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" class="teckform-login__input" value="%4$s" autocomplete="%5$s"%6$s required%7$s>',
			esc_attr( $type ),
			esc_attr( $input_id ),
			esc_attr( $name ),
			esc_attr( is_string( $value ) ? $value : '' ),
			esc_attr( $autocomplete ),
			'' !== (string) $placeholder ? ' placeholder="' . esc_attr( $placeholder ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped here.
			$error_attrs // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
		);

		echo $error_note . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
	}

	/**
	 * Password input with the show/hide button. The button stays hidden until the
	 * script runs, because it needs JavaScript to work.
	 *
	 * @param array      $settings Widget settings.
	 * @param array|null $flash    Result of the last login attempt.
	 */
	private function render_password_field( $settings, $flash ) {
		$input_id = 'teckform-' . $this->get_id() . '-pwd';

		list( $error_attrs, $error_note ) = $this->get_field_error( self::BLOCK, $flash, 'pwd', $input_id );

		echo '<div class="teckform-login__field" data-field="pwd">';
		$this->render_label( $input_id, $settings['password_label'], __( 'Password', 'teckform' ), $settings );
		echo '<div class="teckform-login__password">';

		printf(
			'<input type="password" id="%1$s" name="pwd" class="teckform-login__input" autocomplete="current-password" spellcheck="false" required%2$s%3$s>',
			esc_attr( $input_id ),
			'' !== (string) $settings['password_placeholder'] ? ' placeholder="' . esc_attr( $settings['password_placeholder'] ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped here.
			$error_attrs // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
		);

		if ( 'yes' === $settings['show_password_toggle'] ) {
			printf(
				'<button type="button" class="teckform-login__toggle" aria-controls="%1$s" aria-pressed="false" aria-label="%2$s" data-show-label="%2$s" data-hide-label="%3$s" hidden>'
				. '<svg class="teckform-login__eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5C6.5 5 2.7 9.2 1.5 12c1.2 2.8 5 7 10.5 7s9.3-4.2 10.5-7C21.3 9.2 17.5 5 12 5zm0 11.5A4.5 4.5 0 1 1 12 7.5a4.5 4.5 0 0 1 0 9zm0-7a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/></svg>'
				. '<svg class="teckform-login__eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.3 3.7l1.4-1.4 18 18-1.4 1.4-3.2-3.2A11.4 11.4 0 0 1 12 19C6.5 19 2.7 14.8 1.5 12a13 13 0 0 1 4-4.9L2.3 3.7zm5.9 5.9A4.5 4.5 0 0 0 14.4 15.8l-1.5-1.5a2.5 2.5 0 0 1-3.2-3.2L8.2 9.6zM12 5c5.5 0 9.3 4.2 10.5 7a13.2 13.2 0 0 1-3 4.3l-2.9-2.9A4.5 4.5 0 0 0 10.6 7.7L8.6 5.6C9.6 5.2 10.8 5 12 5z"/></svg>'
				. '</button>',
				esc_attr( $input_id ),
				esc_attr__( 'Show password', 'teckform' ),
				esc_attr__( 'Hide password', 'teckform' )
			);
		}

		echo '</div>' . $error_note . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_field_error().
	}

	/**
	 * Field label, visually hidden when labels are switched off.
	 *
	 * @param string $input_id Input ID.
	 * @param string $label    Label from the settings.
	 * @param string $fallback Label when the setting is empty.
	 * @param array  $settings Widget settings.
	 */
	private function render_label( $input_id, $label, $fallback, $settings ) {
		printf(
			'<label class="%1$s" for="%2$s">%3$s</label>',
			esc_attr( 'teckform-login__label' . ( 'yes' === $settings['show_labels'] ? '' : ' screen-reader-text' ) ),
			esc_attr( $input_id ),
			esc_html( '' !== trim( (string) $label ) ? $label : $fallback )
		);
	}

	/**
	 * Submit button.
	 *
	 * @param string $text     Text from the settings.
	 * @param string $fallback Text when the setting is empty.
	 */
	private function render_submit( $text, $fallback ) {
		printf(
			'<div class="teckform-login__actions"><button type="submit" class="teckform-login__button">%s</button></div>',
			esc_html( '' !== trim( (string) $text ) ? $text : $fallback )
		);
	}
}
