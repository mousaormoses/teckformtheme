<?php
/**
 * Demo layouts: header, footer, mega menu, popup, and every page of the
 * "Teckform Studio" agency site, built from Teckform and Elementor widgets.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Small widget shortcuts
 * ---------------------------------------------------------------------- */

/**
 * Elementor Heading widget.
 *
 * @param string $text  Heading text.
 * @param string $tag   h1–h6 or p.
 * @param array  $extra Extra settings.
 * @return array
 */
function teckform_demo_heading( $text, $tag = 'h2', $extra = array() ) {
	$sizes = array(
		'h1' => 52,
		'h2' => 40,
		'h3' => 22,
		'h4' => 18,
		'p'  => 14,
	);

	return teckform_el_widget( 'heading', array_merge( array(
		'title'                       => $text,
		'header_size'                 => $tag,
		'typography_typography'       => 'custom',
		'typography_font_size'        => array(
			'unit' => 'px',
			'size' => isset( $sizes[ $tag ] ) ? $sizes[ $tag ] : 22,
		),
		'typography_font_size_mobile' => array(
			'unit' => 'px',
			'size' => min( 34, isset( $sizes[ $tag ] ) ? $sizes[ $tag ] : 22 ),
		),
		'typography_font_weight'      => 'p' === $tag ? '600' : '700',
	), $extra ) );
}

/**
 * Elementor Text Editor widget.
 *
 * @param string $html  Paragraph HTML.
 * @param array  $extra Extra settings.
 * @return array
 */
function teckform_demo_text( $html, $extra = array() ) {
	return teckform_el_widget( 'text-editor', array_merge( array( 'editor' => $html ), $extra ) );
}

/**
 * Elementor Button widget.
 *
 * @param string $text  Button text.
 * @param string $url   Link.
 * @param array  $extra Extra settings.
 * @return array
 */
function teckform_demo_button( $text, $url, $extra = array() ) {
	return teckform_el_widget( 'button', array_merge( array(
		'text'             => $text,
		'link'             => array( 'url' => $url ),
		'size'             => 'md',
		'background_color' => '#2563EB',
		'button_text_color' => '#FFFFFF',
		'border_radius'    => array(
			'unit'     => 'px',
			'top'      => '10',
			'right'    => '10',
			'bottom'   => '10',
			'left'     => '10',
			'isLinked' => true,
		),
	), $extra ) );
}

/**
 * A page header band: eyebrow, big title, short text.
 *
 * @param string $eyebrow Small text above.
 * @param string $title   Title.
 * @param string $text    Text below.
 * @return array
 */
function teckform_demo_page_header( $eyebrow, $title, $text ) {
	return teckform_el_section(
		array(
			teckform_el_widget( 'teckform-animated-headline', array(
				'effect'           => 'highlight',
				'before_text'      => '',
				'highlighted_text' => $title,
				'after_text'       => '',
				'tag'              => 'h1',
				'align'            => 'center',
				'shape'            => 'underline',
				'shape_color'      => '#2563EB',
				'headline_typography_typography' => 'custom',
				'headline_typography_font_size'  => array(
					'unit' => 'px',
					'size' => 56,
				),
				'headline_typography_font_size_mobile' => array(
					'unit' => 'px',
					'size' => 36,
				),
				'headline_typography_font_weight' => '800',
			) ),
			teckform_demo_text( '<p style="text-align:center;max-width:620px;margin:16px auto 0;font-size:18px;">' . esc_html( $text ) . '</p>' ),
		),
		array(
			'eyebrow' => $eyebrow,
			'bg'      => '#F8FAFC',
			'dark_bg' => '#0B1120',
			'padding' => 112,
		)
	);
}

/**
 * Call to Action band used at the bottom of most pages.
 *
 * @param array $content Demo content.
 * @return array
 */
function teckform_demo_cta( $content ) {
	return teckform_el_section( array(
		teckform_el_widget( 'teckform-call-to-action', array(
			'layout'        => 'split',
			'scheme'        => 'accent',
			'pattern'       => 'grid',
			'eyebrow'       => __( "Let's talk", 'teckform' ),
			'title'         => __( 'Ready to grow your business online?', 'teckform' ),
			'title_tag'     => 'h2',
			'description'   => __( 'Book a free 30-minute strategy call and get a clear plan for your next website.', 'teckform' ),
			'button_1_text' => __( 'Book a free call', 'teckform' ),
			'button_1_link' => array( 'url' => get_permalink( $content['pages']['contact'] ) ),
			'button_1_icon' => array(
				'value'   => 'fas fa-arrow-right',
				'library' => 'fa-solid',
			),
			'button_2_text' => __( 'See our work', 'teckform' ),
			'button_2_link' => array( 'url' => get_permalink( $content['pages']['portfolio'] ) ),
			'box_radius'    => array(
				'unit' => 'px',
				'size' => 28,
			),
		) ),
	), array( 'padding' => 80 ) );
}

/**
 * Gallery value from demo image names.
 *
 * @param array    $images Demo images.
 * @param string[] $names  Image names.
 * @return array
 */
function teckform_demo_gallery( $images, $names ) {
	$gallery = array();

	foreach ( $names as $name ) {
		if ( ! empty( $images[ $name ] ) ) {
			$gallery[] = teckform_demo_media( $images, $name );
		}
	}

	return $gallery;
}

/* -------------------------------------------------------------------------
 * Header, footer, mega menu and popup
 * ---------------------------------------------------------------------- */

/**
 * Create the header, footer, mega menu and popup templates.
 *
 * @param array $content Demo content.
 * @param array $images  Demo images.
 * @param array $menus   Demo menus.
 * @return array{header: int, footer: int, mega: int, popup: int}
 */
function teckform_demo_create_templates( $content, $images, $menus ) {
	$contact = get_permalink( $content['pages']['contact'] );

	// Header.
	$header = teckform_el_container(
		array(
			teckform_el_widget( 'teckform-site-logo', array(
				'logo_source' => 'site',
				'width'       => array(
					'unit' => 'px',
					'size' => 170,
				),
				'width_mobile' => array(
					'unit' => 'px',
					'size' => 140,
				),
			) ),
			teckform_el_widget( 'teckform-nav-menu', array(
				'menu'         => (string) $menus['main'],
				'align'        => 'center',
				'align_tablet' => 'flex-end',
				'pointer'      => 'underline',
				'mega_width'   => 'container',
				'_flex_size'   => 'grow',
				'item_color_tablet' => '',
			) ),
			teckform_el_widget( 'teckform-search', array( 'layout' => 'icon' ) ),
			teckform_el_widget( 'teckform-dark-mode-toggle', array(
				'style' => 'icon',
				'size'  => array(
					'unit' => 'px',
					'size' => 20,
				),
				'_element_width' => 'auto',
			) ),
			teckform_demo_button( __( 'Get a quote', 'teckform' ), $contact, array(
				'size'                => 'sm',
				'hide_mobile'         => 'hidden-mobile',
				'_element_width'      => 'auto',
			) ),
		),
		array(
			'flex_direction'       => 'row',
			'flex_justify_content' => 'space-between',
			'flex_align_items'     => 'center',
			'flex_gap'             => array(
				'unit'   => 'px',
				'size'   => 20,
				'column' => '20',
				'row'    => '20',
			),
			'flex_wrap'            => 'nowrap',
			'background_background' => 'classic',
			'background_color'     => '#FFFFFF',
			'teckform_dark_bg'     => '#0B1120',
			'padding'              => teckform_el_box( 14, 20 ),
		)
	);

	$parts = array();

	$parts['header'] = teckform_demo_insert( array(
		'post_type'  => 'teckform_template',
		'post_title' => __( 'Demo: Site Header', 'teckform' ),
		'meta'       => array(
			'_teckform_type'        => 'header',
			'_teckform_display'     => 'entire',
			'_teckform_sticky'      => '1',
			'_teckform_shadow'      => '1',
			'_teckform_scrolled_bg' => '',
		),
		'elementor'  => array( $header ),
	) );

	// Mega menu: four services and a call-out card.
	$services = array();

	foreach ( array_slice( $content['services'], 0, 4 ) as $service_id ) {
		$services[] = teckform_el_widget( 'teckform-feature-box', array(
			'icon'          => array(
				'value'   => get_post_meta( $service_id, '_teckform_icon', true ),
				'library' => 'fa-solid',
			),
			'title'         => get_the_title( $service_id ),
			'description'   => get_the_excerpt( $service_id ),
			'link'          => array( 'url' => get_permalink( $service_id ) ),
			'title_tag'     => 'h4',
			'align'         => 'left',
			'icon_size'     => array(
				'unit' => 'px',
				'size' => 26,
			),
			'icon_spacing'  => array(
				'unit' => 'px',
				'size' => 10,
			),
			'title_typography_typography' => 'custom',
			'title_typography_font_size'  => array(
				'unit' => 'px',
				'size' => 16,
			),
			'description_typography_typography' => 'custom',
			'description_typography_font_size'  => array(
				'unit' => 'px',
				'size' => 14,
			),
		) );
	}

	$mega = teckform_el_container(
		array(
			teckform_el_container( array( teckform_el_row( $services, 2, 28 ) ), array(
				'content_width' => 'full',
				'width'         => array(
					'unit' => '%',
					'size' => 64,
				),
				'padding'       => teckform_el_box( 0 ),
			), true ),
			teckform_el_container(
				array(
					teckform_demo_heading( __( 'Not sure where to start?', 'teckform' ), 'h4' ),
					teckform_demo_text( '<p>' . esc_html__( 'Tell us about your project and we will suggest the right plan, free.', 'teckform' ) . '</p>' ),
					teckform_demo_button( __( 'Book a free call', 'teckform' ), $contact, array( 'size' => 'sm' ) ),
				),
				array(
					'content_width'         => 'full',
					'width'                 => array(
						'unit' => '%',
						'size' => 32,
					),
					'background_background' => 'classic',
					'background_color'      => '#EFF6FF',
					'teckform_dark_bg'      => '#1E293B',
					'border_radius'         => array(
						'unit'     => 'px',
						'top'      => '12',
						'right'    => '12',
						'bottom'   => '12',
						'left'     => '12',
						'isLinked' => true,
					),
					'padding'               => teckform_el_box( 24 ),
				),
				true
			),
		),
		array(
			'content_width'        => 'full',
			'flex_direction'       => 'row',
			'flex_justify_content' => 'space-between',
			'padding'              => teckform_el_box( 8 ),
		)
	);

	$parts['mega'] = teckform_demo_insert( array(
		'post_type'  => 'teckform_template',
		'post_title' => __( 'Demo: Services Mega Menu', 'teckform' ),
		'meta'       => array( '_teckform_type' => 'section' ),
		'elementor'  => array( $mega ),
	) );

	// Newsletter popup content.
	$parts['popup'] = teckform_demo_insert( array(
		'post_type'  => 'teckform_template',
		'post_title' => __( 'Demo: Newsletter Popup', 'teckform' ),
		'meta'       => array( '_teckform_type' => 'section' ),
		'elementor'  => array(
			teckform_el_container(
				array(
					teckform_demo_heading( __( 'Get one practical web tip a month', 'teckform' ), 'h3', array( 'align' => 'center' ) ),
					teckform_demo_text( '<p style="text-align:center">' . esc_html__( 'Join 2,000+ business owners. No spam, unsubscribe anytime.', 'teckform' ) . '</p>' ),
					teckform_el_widget( 'teckform-newsletter', array(
						'mode'              => 'wordpress',
						'layout'            => 'stacked',
						'email_placeholder' => __( 'Your email', 'teckform' ),
						'button_text'       => __( 'Subscribe', 'teckform' ),
						'list_name'         => __( 'Popup', 'teckform' ),
						'success_message'   => __( "You're in! Check your inbox soon.", 'teckform' ),
					) ),
				),
				array( 'padding' => teckform_el_box( 8 ) )
			),
		),
	) );

	// Footer.
	$light = '#CBD5E1';
	$white = array( 'title_color' => '#FFFFFF' );

	$footer_columns = teckform_el_row(
		array(
			array(
				teckform_el_widget( 'teckform-site-logo', array(
					'logo_source' => 'site',
					'width'       => array(
						'unit' => 'px',
						'size' => 160,
					),
					'name_color'  => '#FFFFFF',
				) ),
				teckform_demo_text( '<p>' . esc_html__( 'A digital studio building websites, apps and brands for growing businesses.', 'teckform' ) . '</p>', array( 'text_color' => $light ) ),
				teckform_el_widget( 'social-icons', array(
					'social_icon_list' => array(
						array(
							'_id'         => teckform_el_id(),
							'social_icon' => array(
								'value'   => 'fab fa-linkedin',
								'library' => 'fa-brands',
							),
							'link'        => array( 'url' => 'https://www.linkedin.com/' ),
						),
						array(
							'_id'         => teckform_el_id(),
							'social_icon' => array(
								'value'   => 'fab fa-instagram',
								'library' => 'fa-brands',
							),
							'link'        => array( 'url' => 'https://www.instagram.com/' ),
						),
						array(
							'_id'         => teckform_el_id(),
							'social_icon' => array(
								'value'   => 'fab fa-x-twitter',
								'library' => 'fa-brands',
							),
							'link'        => array( 'url' => 'https://x.com/' ),
						),
					),
					'shape'            => 'circle',
					'align'            => 'left',
					'icon_color'       => 'custom',
					'icon_primary_color'   => '#1E293B',
					'icon_secondary_color' => '#FFFFFF',
					'icon_size'        => array(
						'unit' => 'px',
						'size' => 15,
					),
				) ),
			),
			array(
				teckform_demo_heading( __( 'Services', 'teckform' ), 'h4', $white ),
				teckform_el_widget( 'teckform-nav-menu', array(
					'menu'             => (string) $menus['services'],
					'layout'           => 'vertical',
					'breakpoint'       => 'none',
					'item_color'       => $light,
					'item_hover_color' => '#FFFFFF',
				) ),
			),
			array(
				teckform_demo_heading( __( 'Company', 'teckform' ), 'h4', $white ),
				teckform_el_widget( 'teckform-nav-menu', array(
					'menu'             => (string) $menus['company'],
					'layout'           => 'vertical',
					'breakpoint'       => 'none',
					'item_color'       => $light,
					'item_hover_color' => '#FFFFFF',
				) ),
			),
			array(
				teckform_demo_heading( __( 'Stay in the loop', 'teckform' ), 'h4', $white ),
				teckform_demo_text( '<p>' . esc_html__( 'One practical web tip a month.', 'teckform' ) . '</p>', array( 'text_color' => $light ) ),
				teckform_el_widget( 'teckform-newsletter', array(
					'mode'              => 'wordpress',
					'layout'            => 'stacked',
					'email_placeholder' => __( 'Your email', 'teckform' ),
					'button_text'       => __( 'Subscribe', 'teckform' ),
					'list_name'         => __( 'Footer', 'teckform' ),
				) ),
			),
		),
		4,
		40
	);

	$footer_bottom = teckform_el_container(
		array(
			teckform_el_widget( 'teckform-copyright', array( 'color' => '#94A3B8' ) ),
			teckform_el_widget( 'teckform-dark-mode-toggle', array( '_element_width' => 'auto' ) ),
		),
		array(
			'content_width'        => 'full',
			'flex_direction'       => 'row',
			'flex_justify_content' => 'space-between',
			'flex_align_items'     => 'center',
			'border_border'        => 'solid',
			'border_width'         => teckform_el_box( 1, 0, 0, 0 ),
			'border_color'         => '#1E293B',
			'padding'              => teckform_el_box( 24, 0, 0, 0 ),
			'margin'               => teckform_el_box( 48, 0, 0, 0 ),
		),
		true
	);

	$parts['footer'] = teckform_demo_insert( array(
		'post_type'  => 'teckform_template',
		'post_title' => __( 'Demo: Site Footer', 'teckform' ),
		'meta'       => array(
			'_teckform_type'    => 'footer',
			'_teckform_display' => 'entire',
		),
		'elementor'  => array(
			teckform_el_container(
				array( $footer_columns, $footer_bottom ),
				array(
					'background_background' => 'classic',
					'background_color'      => '#0F172A',
					'teckform_dark_bg'      => '#020617',
					'padding'               => teckform_el_box( 72, 20, 32, 20 ),
				)
			),
		),
	) );

	return $parts;
}

/* -------------------------------------------------------------------------
 * Pages
 * ---------------------------------------------------------------------- */

/**
 * Fill every demo page with its Elementor layout.
 *
 * @param array $content Demo content.
 * @param array $images  Demo images.
 * @param array $parts   Demo templates.
 */
function teckform_demo_create_pages( $content, $images, $parts ) {
	$url = function ( $key ) use ( $content ) {
		return get_permalink( $content['pages'][ $key ] );
	};

	$layouts = array(
		'home'        => teckform_demo_home( $content, $images, $parts, $url ),
		'about'       => teckform_demo_about( $content, $images, $url ),
		'services'    => teckform_demo_services( $content, $images, $url ),
		'portfolio'   => teckform_demo_portfolio( $content, $images ),
		'pricing'     => teckform_demo_pricing( $content, $url ),
		'blog'        => array(
			teckform_demo_page_header( __( 'Blog', 'teckform' ), __( 'Ideas and advice', 'teckform' ), __( 'Practical tips on websites, apps, branding and growing online.', 'teckform' ) ),
			teckform_el_section( array(
				teckform_el_widget( 'teckform-posts-grid', array(
					'post_type'      => 'post',
					'posts_per_page' => 6,
					'columns'        => '3',
					'columns_tablet' => '2',
					'columns_mobile' => '1',
					'image_ratio'    => '16-9',
					'show_excerpt'   => 'yes',
					'excerpt_length' => 18,
					'read_more_text' => __( 'Read article', 'teckform' ),
					'pagination'     => 'numbers',
				) ),
			) ),
		),
		'contact'     => teckform_demo_contact( $content ),
		'careers'     => teckform_demo_careers( $content, $url ),
		'account'     => array(
			teckform_demo_page_header( __( 'Clients', 'teckform' ), __( 'Your account', 'teckform' ), __( 'Log in to see your projects and invoices.', 'teckform' ) ),
			teckform_el_section( array(
				teckform_el_widget( 'teckform-login-form', array(
					'show_register'     => 'yes',
					'logged_in_action'  => 'message',
					'logged_in_message' => __( 'Hello, {name}! Good to see you again.', 'teckform' ),
					'box_width'         => array(
						'unit' => 'px',
						'size' => 440,
					),
					'box_align'         => 'center',
				) ),
			) ),
		),
		'coming-soon' => teckform_demo_coming_soon( $images ),
	);

	foreach ( $layouts as $key => $elements ) {
		teckform_demo_set_elementor( $content['pages'][ $key ], $elements );
	}

	// The coming-soon page stands alone: no header or footer.
	update_post_meta( $content['pages']['coming-soon'], '_teckform_header', 'hide' );
	update_post_meta( $content['pages']['coming-soon'], '_teckform_footer', 'hide' );
}

/**
 * @param array    $content Demo content.
 * @param array    $images  Demo images.
 * @param array    $parts   Demo templates.
 * @param callable $url     Page URL by key.
 * @return array
 */
function teckform_demo_home( $content, $images, $parts, $url ) {
	$counter = function ( $number, $suffix, $title ) {
		return teckform_el_widget( 'counter', array(
			'starting_number' => 0,
			'ending_number'   => $number,
			'suffix'          => $suffix,
			'title'           => $title,
			'number_color'    => '#FFFFFF',
			'title_color'     => '#94A3B8',
		) );
	};

	return array(
		teckform_el_section( array(
			teckform_el_widget( 'teckform-hero', array(
				'layout'          => 'split',
				'badge_label'     => __( 'New', 'teckform' ),
				'badge_text'      => __( 'Now booking projects for 2027', 'teckform' ),
				'badge_link'      => array( 'url' => $url( 'contact' ) ),
				'title_before'    => __( 'We design and build', 'teckform' ),
				'title_words'     => "websites\napps\nbrands",
				'title_after'     => __( 'that grow your business', 'teckform' ),
				'highlight_style' => 'underline',
				'description'     => __( 'Teckform Studio is a small senior team of designers and developers. We turn ideas into fast, beautiful products your customers love.', 'teckform' ),
				'button_1_text'   => __( 'Start a project', 'teckform' ),
				'button_1_link'   => array( 'url' => $url( 'contact' ) ),
				'button_2_text'   => __( 'See our work', 'teckform' ),
				'button_2_link'   => array( 'url' => $url( 'portfolio' ) ),
				'image'           => teckform_demo_media( $images, 'hero' ),
				'pattern'         => 'glow',
				'trust_type'      => 'avatars',
				'avatars'         => teckform_demo_gallery( $images, array( 'person-1', 'person-2', 'person-3', 'person-4' ) ),
				'rating'          => 4.9,
				'rating_text'     => __( 'from 120+ happy clients', 'teckform' ),
			) ),
		), array( 'full' => true ) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-logo-carousel', array(
				'title'          => __( 'Trusted by 120+ growing brands', 'teckform' ),
				'title_tag'      => 'p',
				'logos'          => teckform_demo_gallery( $images, array( 'client-1', 'client-2', 'client-3', 'client-4', 'client-5', 'client-6' ) ),
				'mode'           => 'marquee',
				'speed'          => 35,
				'fade_edges'     => 'yes',
				'grayscale'      => 'yes',
			) ),
		), array( 'padding' => 48 ) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-posts-grid', array(
				'post_type'      => 'teckform_service',
				'card_layout'    => 'overlay',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'posts_per_page' => 6,
				'columns'        => '3',
				'columns_tablet' => '2',
				'columns_mobile' => '1',
				'image_ratio'    => '4-3',
				'show_badge'     => '',
				'show_date'      => '',
				'show_author'    => '',
				'show_excerpt'   => 'yes',
				'excerpt_length' => 14,
				'read_more_text' => __( 'Learn more', 'teckform' ),
				'pagination'     => 'none',
			) ),
		), array(
			'eyebrow' => __( 'What we do', 'teckform' ),
			'title'   => __( 'Everything you need to grow online', 'teckform' ),
			'text'    => __( 'From the first sketch to launch day and beyond, one team handles it all.', 'teckform' ),
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-process', array(
				'layout'      => 'horizontal',
				'align'       => 'center',
				'marker'      => 'icon',
				'animate'     => 'yes',
				'line_style'  => 'dashed',
				'steps'       => array(
					array(
						'_id'   => teckform_el_id(),
						'label' => __( 'Step 01', 'teckform' ),
						'title' => __( 'Discover', 'teckform' ),
						'text'  => __( 'Workshops to understand your goals, audience and competitors.', 'teckform' ),
						'icon'  => array(
							'value'   => 'fas fa-search',
							'library' => 'fa-solid',
						),
					),
					array(
						'_id'   => teckform_el_id(),
						'label' => __( 'Step 02', 'teckform' ),
						'title' => __( 'Design', 'teckform' ),
						'text'  => __( 'Wireframes and visual design, refined with your feedback.', 'teckform' ),
						'icon'  => array(
							'value'   => 'fas fa-pencil-ruler',
							'library' => 'fa-solid',
						),
					),
					array(
						'_id'   => teckform_el_id(),
						'label' => __( 'Step 03', 'teckform' ),
						'title' => __( 'Develop', 'teckform' ),
						'text'  => __( 'A fast, accessible build with an editor your team can use.', 'teckform' ),
						'icon'  => array(
							'value'   => 'fas fa-code',
							'library' => 'fa-solid',
						),
					),
					array(
						'_id'   => teckform_el_id(),
						'label' => __( 'Step 04', 'teckform' ),
						'title' => __( 'Launch and grow', 'teckform' ),
						'text'  => __( 'Go live, measure, and keep improving every month.', 'teckform' ),
						'icon'  => array(
							'value'   => 'fas fa-rocket',
							'library' => 'fa-solid',
						),
					),
				),
			) ),
		), array(
			'eyebrow' => __( 'How we work', 'teckform' ),
			'title'   => __( 'A simple, proven process', 'teckform' ),
			'bg'      => '#F8FAFC',
			'dark_bg' => '#0B1120',
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-portfolio', array(
				'posts_per_page' => 6,
				'show_filter'    => 'yes',
				'all_label'      => __( 'All work', 'teckform' ),
				'columns'        => '3',
				'columns_tablet' => '2',
				'columns_mobile' => '1',
				'image_ratio'    => '4-3',
				'overlay_style'  => 'hover',
				'filter_align'   => 'center',
			) ),
		), array(
			'eyebrow' => __( 'Our work', 'teckform' ),
			'title'   => __( 'Recent projects', 'teckform' ),
		) ),
		teckform_el_section( array(
			teckform_el_row( array(
				$counter( 120, '+', __( 'Projects delivered', 'teckform' ) ),
				$counter( 98, '%', __( 'Happy clients', 'teckform' ) ),
				$counter( 12, '', __( 'Team members', 'teckform' ) ),
				$counter( 10, '', __( 'Years in business', 'teckform' ) ),
			), 4, 24 ),
		), array(
			'bg'      => '#0F172A',
			'dark_bg' => '#020617',
			'padding' => 72,
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-testimonials', array(
				'source'                 => 'posts',
				'posts_number'           => 5,
				'posts_orderby'          => 'menu_order',
				'posts_order'            => 'ASC',
				'layout'                 => 'card',
				'slides_per_view'        => '3',
				'slides_per_view_tablet' => '2',
				'slides_per_view_mobile' => '1',
				'autoplay'               => 'yes',
				'autoplay_interval'      => 6,
				'show_arrows'            => 'yes',
				'arrows_position'        => 'bottom',
				'show_dots'              => 'yes',
			) ),
		), array(
			'eyebrow' => __( 'Testimonials', 'teckform' ),
			'title'   => __( 'Clients say it best', 'teckform' ),
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-posts-grid', array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'columns'        => '3',
				'columns_tablet' => '2',
				'columns_mobile' => '1',
				'image_ratio'    => '16-9',
				'show_excerpt'   => 'yes',
				'excerpt_length' => 16,
				'read_more_text' => __( 'Read article', 'teckform' ),
				'pagination'     => 'none',
			) ),
		), array(
			'eyebrow' => __( 'From the blog', 'teckform' ),
			'title'   => __( 'Ideas and advice', 'teckform' ),
			'bg'      => '#F8FAFC',
			'dark_bg' => '#0B1120',
		) ),
		teckform_demo_cta( $content ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-popup', array(
				'content_source'  => 'template',
				'template_id'     => (string) $parts['popup'],
				'popup_id'        => 'newsletter',
				'show_title'      => '',
				'show_button'     => '',
				'open_on_load'    => 'yes',
				'load_delay'      => 25,
				'open_on_exit'    => 'yes',
				'show_again_days' => 7,
				'position'        => 'center',
				'animation'       => 'zoom',
				'popup_width'     => array(
					'unit' => 'px',
					'size' => 480,
				),
			) ),
		), array( 'padding' => 0 ) ),
	);
}

/**
 * @param array    $content Demo content.
 * @param array    $images  Demo images.
 * @param callable $url     Page URL by key.
 * @return array
 */
function teckform_demo_about( $content, $images, $url ) {
	$story = array();

	foreach ( array(
		array( '2016', __( 'Two friends, one laptop', 'teckform' ), __( 'Teckform starts in a shared office with its first three clients.', 'teckform' ) ),
		array( '2019', __( 'Our first app', 'teckform' ), __( 'We launch a fitness app that reaches 100,000 downloads in a year.', 'teckform' ) ),
		array( '2022', __( 'A team of ten', 'teckform' ), __( 'Designers, developers and marketers under one roof.', 'teckform' ) ),
		array( '2026', __( '120 projects later', 'teckform' ), __( 'Still small, still senior, still answering every email ourselves.', 'teckform' ) ),
	) as $step ) {
		$story[] = array(
			'_id'   => teckform_el_id(),
			'label' => $step[0],
			'title' => $step[1],
			'text'  => $step[2],
		);
	}

	return array(
		teckform_demo_page_header( __( 'About us', 'teckform' ), __( 'We build things people love to use', 'teckform' ), __( 'A small senior team of designers, developers and marketers.', 'teckform' ) ),
		teckform_el_section( array(
			teckform_el_row( array(
				teckform_el_widget( 'image', array(
					'image'         => teckform_demo_media( $images, 'about' ),
					'image_border_radius' => array(
						'unit'     => 'px',
						'top'      => '20',
						'right'    => '20',
						'bottom'   => '20',
						'left'     => '20',
						'isLinked' => true,
					),
				) ),
				array(
					teckform_demo_heading( __( 'Senior people on every project', 'teckform' ), 'h2' ),
					teckform_demo_text( '<p>' . esc_html__( 'No hand-offs to juniors and no surprises. The people you meet on the first call are the people who design and build your product.', 'teckform' ) . '</p>' ),
					teckform_el_widget( 'icon-list', array(
						'icon_list' => array(
							array(
								'_id'           => teckform_el_id(),
								'text'          => __( 'Weekly progress updates', 'teckform' ),
								'selected_icon' => array(
									'value'   => 'fas fa-check-circle',
									'library' => 'fa-solid',
								),
							),
							array(
								'_id'           => teckform_el_id(),
								'text'          => __( 'Fixed prices, no hidden costs', 'teckform' ),
								'selected_icon' => array(
									'value'   => 'fas fa-check-circle',
									'library' => 'fa-solid',
								),
							),
							array(
								'_id'           => teckform_el_id(),
								'text'          => __( 'Support long after launch', 'teckform' ),
								'selected_icon' => array(
									'value'   => 'fas fa-check-circle',
									'library' => 'fa-solid',
								),
							),
						),
						'icon_color' => '#2563EB',
					) ),
				),
			), 2, 64 ),
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-process', array(
				'layout'     => 'alternating',
				'first_side' => 'end',
				'marker'     => 'dot',
				'animate'    => 'yes',
				'steps'      => $story,
			) ),
		), array(
			'eyebrow' => __( 'Our story', 'teckform' ),
			'title'   => __( 'Ten years in the making', 'teckform' ),
			'bg'      => '#F8FAFC',
			'dark_bg' => '#0B1120',
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-team', array(
				'source'           => 'cpt',
				'members_count'    => 4,
				'card_style'       => 'top',
				'columns'          => '4',
				'columns_tablet'   => '2',
				'columns_mobile'   => '1',
				'photo_ratio'      => '4-5',
				'show_bio'         => 'yes',
				'bio_length'       => 12,
				'show_socials'     => 'yes',
				'socials_position' => 'overlay',
			) ),
		), array(
			'eyebrow' => __( 'The team', 'teckform' ),
			'title'   => __( 'Meet the people behind the work', 'teckform' ),
		) ),
		teckform_demo_cta( $content ),
	);
}

/**
 * @param array    $content Demo content.
 * @param array    $images  Demo images.
 * @param callable $url     Page URL by key.
 * @return array
 */
function teckform_demo_services( $content, $images, $url ) {
	$flips = array();

	foreach ( $content['services'] as $service_id ) {
		$flips[] = teckform_el_widget( 'teckform-flip-box', array(
			'front_graphic'    => 'icon',
			'front_icon'       => array(
				'value'   => get_post_meta( $service_id, '_teckform_icon', true ),
				'library' => 'fa-solid',
			),
			'front_title'      => get_the_title( $service_id ),
			'front_text'       => get_the_excerpt( $service_id ),
			'back_title'       => get_the_title( $service_id ),
			'back_text'        => __( 'Strategy, design, build and support, all from one senior team.', 'teckform' ),
			'back_button_text' => __( 'Learn more', 'teckform' ),
			'back_link'        => array( 'url' => get_permalink( $service_id ) ),
			'effect'           => 'flip',
			'direction'        => 'left',
			'height'           => array(
				'unit' => 'px',
				'size' => 300,
			),
			'back_bg'          => '#0F172A',
			'back_title_color' => '#FFFFFF',
			'back_text_color'  => '#CBD5E1',
		) );
	}

	$faq = function ( $question, $answer ) {
		return array(
			'_id'      => teckform_el_id(),
			'question' => $question,
			'answer'   => '<p>' . $answer . '</p>',
		);
	};

	return array(
		teckform_el_section( array(
			teckform_el_widget( 'teckform-hero', array(
				'layout'        => 'background',
				'bg_image'      => teckform_demo_media( $images, 'office' ),
				'show_badge'    => '',
				'title_before'  => __( 'Services that help you', 'teckform' ),
				'title_words'   => __( 'grow', 'teckform' ),
				'title_after'   => '',
				'description'   => __( 'Design, development, apps, branding and marketing, from one team that cares about your results.', 'teckform' ),
				'button_1_text' => __( 'Get a quote', 'teckform' ),
				'button_1_link' => array( 'url' => $url( 'contact' ) ),
				'button_2_text' => __( 'See pricing', 'teckform' ),
				'button_2_link' => array( 'url' => $url( 'pricing' ) ),
				'min_height'    => array(
					'unit' => 'vh',
					'size' => 70,
				),
			) ),
		), array( 'full' => true ) ),
		teckform_el_section( array(
			teckform_el_row( array_slice( $flips, 0, 3 ), 3, 24 ),
			teckform_el_row( array_slice( $flips, 3, 3 ), 3, 24 ),
		), array(
			'eyebrow' => __( 'What we do', 'teckform' ),
			'title'   => __( 'Hover a card to learn more', 'teckform' ),
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-faq', array(
				'behavior'   => 'single',
				'first_open' => 'yes',
				'icon'       => 'plus',
				'columns'    => '2',
				'schema'     => 'yes',
				'items'      => array(
					$faq( __( 'How long does a website project take?', 'teckform' ), __( 'Most sites launch in 4 to 8 weeks, depending on size.', 'teckform' ) ),
					$faq( __( 'Do you work with startups?', 'teckform' ), __( 'Yes, we have packages built for early-stage teams.', 'teckform' ) ),
					$faq( __( 'Can I edit the site myself?', 'teckform' ), __( 'Yes. We build on WordPress and Elementor and show your team how to use it.', 'teckform' ) ),
					$faq( __( 'Do you offer ongoing support?', 'teckform' ), __( 'Every project includes 30 days of support, then optional care plans.', 'teckform' ) ),
					$faq( __( 'What do you need from us to start?', 'teckform' ), __( 'A kickoff call, your brand files and access to any existing accounts.', 'teckform' ) ),
					$faq( __( 'How do payments work?', 'teckform' ), __( 'Half to start and half at launch. Care plans are billed monthly.', 'teckform' ) ),
				),
			) ),
		), array(
			'eyebrow' => __( 'Questions', 'teckform' ),
			'title'   => __( 'Frequently asked questions', 'teckform' ),
			'bg'      => '#F8FAFC',
			'dark_bg' => '#0B1120',
		) ),
		teckform_demo_cta( $content ),
	);
}

/**
 * @param array $content Demo content.
 * @param array $images  Demo images.
 * @return array
 */
function teckform_demo_portfolio( $content, $images ) {
	$spot = function ( $title, $text, $x, $y ) {
		return array(
			'_id'    => teckform_el_id(),
			'title'  => $title,
			'text'   => $text,
			'x'      => array(
				'unit' => '%',
				'size' => $x,
			),
			'y'      => array(
				'unit' => '%',
				'size' => $y,
			),
			'marker' => 'dot',
		);
	};

	return array(
		teckform_demo_page_header( __( 'Portfolio', 'teckform' ), __( 'Work we are proud of', 'teckform' ), __( 'Websites, apps and brands for clients around the world.', 'teckform' ) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-portfolio', array(
				'posts_per_page' => 9,
				'show_filter'    => 'yes',
				'all_label'      => __( 'All work', 'teckform' ),
				'columns'        => '3',
				'columns_tablet' => '2',
				'columns_mobile' => '1',
				'image_ratio'    => '4-3',
				'overlay_style'  => 'hover',
				'filter_align'   => 'center',
			) ),
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-before-after', array(
				'before_image'  => teckform_demo_media( $images, 'project-4' ),
				'after_image'   => teckform_demo_media( $images, 'project-1' ),
				'orientation'   => 'horizontal',
				'labels'        => 'always',
				'before_label'  => __( 'Old site', 'teckform' ),
				'after_label'   => __( 'Redesign', 'teckform' ),
				'slider_label'  => __( 'Compare the old and new website', 'teckform' ),
				'height'        => array(
					'unit' => 'px',
					'size' => 520,
				),
				'height_mobile' => array(
					'unit' => 'px',
					'size' => 280,
				),
			) ),
		), array(
			'eyebrow' => __( 'Before and after', 'teckform' ),
			'title'   => __( 'Drag to see the difference', 'teckform' ),
			'bg'      => '#F8FAFC',
			'dark_bg' => '#0B1120',
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-hotspots', array(
				'image'            => teckform_demo_media( $images, 'dashboard' ),
				'trigger'          => 'hover',
				'tooltip_position' => 'auto',
				'pulse'            => 'yes',
				'hotspots'         => array(
					$spot( __( 'Fast loading', 'teckform' ), __( 'Pages load in under a second.', 'teckform' ), 22, 30 ),
					$spot( __( 'Easy to edit', 'teckform' ), __( 'Change text and images yourself.', 'teckform' ), 58, 55 ),
					$spot( __( 'Built to sell', 'teckform' ), __( 'Clear calls to action on every page.', 'teckform' ), 80, 26 ),
				),
			) ),
		), array(
			'eyebrow' => __( 'Inside a project', 'teckform' ),
			'title'   => __( 'Every detail has a job', 'teckform' ),
		) ),
		teckform_demo_cta( $content ),
	);
}

/**
 * @param array    $content Demo content.
 * @param callable $url     Page URL by key.
 * @return array
 */
function teckform_demo_pricing( $content, $url ) {
	$plan = function ( $name, $description, $monthly, $yearly, $features, $featured = false ) use ( $url ) {
		return array(
			'_id'            => teckform_el_id(),
			'name'           => $name,
			'description'    => $description,
			'currency'       => '$',
			'price_monthly'  => $monthly,
			'period_monthly' => is_numeric( $monthly ) ? __( '/month', 'teckform' ) : '',
			'price_yearly'   => $yearly,
			'period_yearly'  => is_numeric( $yearly ) ? __( '/month, billed yearly', 'teckform' ) : '',
			'features'       => $features,
			'button_text'    => $featured ? __( 'Choose Growth', 'teckform' ) : __( 'Get started', 'teckform' ),
			'button_link'    => array( 'url' => $url( 'contact' ) ),
			'featured'       => $featured ? 'yes' : '',
			'badge'          => $featured ? __( 'Most popular', 'teckform' ) : '',
		);
	};

	return array(
		teckform_demo_page_header( __( 'Pricing', 'teckform' ), __( 'Simple, honest pricing', 'teckform' ), __( 'Pick a plan that fits today. Change it anytime.', 'teckform' ) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-countdown', array(
				'mode'              => 'evergreen',
				'evergreen_days'    => 2,
				'evergreen_hours'   => 0,
				'evergreen_minutes' => 0,
				'layout'            => 'inline',
				'align'             => 'center',
				'expire_action'     => 'hide',
			) ),
			teckform_demo_text( '<p style="text-align:center">' . esc_html__( 'Launch offer: 20% off yearly plans ends soon.', 'teckform' ) . '</p>' ),
		), array( 'padding' => 32 ) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-pricing-table', array(
				'show_switch'    => 'yes',
				'default_period' => 'monthly',
				'label_monthly'  => __( 'Monthly', 'teckform' ),
				'label_yearly'   => __( 'Yearly', 'teckform' ),
				'save_note'      => __( 'Save 20%', 'teckform' ),
				'plans'          => array(
					$plan( 'Launch', __( 'A sharp one-page site for new brands.', 'teckform' ), '490', '390', "1-page website\nMobile-first design\nBasic SEO setup\n-Blog and editor\n-Monthly reporting" ),
					$plan( 'Growth', __( 'A multi-page site plus content and SEO.', 'teckform' ), '1290', '990', "Up to 12 pages\nCustom design system\nAdvanced SEO\nBlog and editor\nMonthly reporting", true ),
					$plan( 'Scale', __( 'A full digital team on demand.', 'teckform' ), __( 'Custom', 'teckform' ), __( 'Custom', 'teckform' ), "Unlimited pages\nWeb app features\nConversion optimisation\nDedicated strategist\nPriority support" ),
				),
			) ),
		), array( 'padding' => 48 ) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-faq', array(
				'behavior' => 'single',
				'schema'   => 'yes',
				'items'    => array(
					array(
						'_id'      => teckform_el_id(),
						'question' => __( 'Can I change plans later?', 'teckform' ),
						'answer'   => '<p>' . esc_html__( 'Yes, upgrade or downgrade at any time.', 'teckform' ) . '</p>',
					),
					array(
						'_id'      => teckform_el_id(),
						'question' => __( 'Is hosting included?', 'teckform' ),
						'answer'   => '<p>' . esc_html__( 'Growth and Scale include fast managed hosting.', 'teckform' ) . '</p>',
					),
					array(
						'_id'      => teckform_el_id(),
						'question' => __( 'Do you offer refunds?', 'teckform' ),
						'answer'   => '<p>' . esc_html__( 'If you are not happy after the first month, we refund it in full.', 'teckform' ) . '</p>',
					),
				),
			) ),
		), array(
			'title'   => __( 'Pricing questions', 'teckform' ),
			'bg'      => '#F8FAFC',
			'dark_bg' => '#0B1120',
		) ),
		teckform_demo_cta( $content ),
	);
}

/**
 * @param array $content Demo content.
 * @return array
 */
function teckform_demo_contact( $content ) {
	$field = function ( $type, $label, $placeholder, $width = '100', $required = true, $extra = array() ) {
		return array_merge( array(
			'_id'          => teckform_el_id(),
			'field_type'   => $type,
			'field_label'  => $label,
			'placeholder'  => $placeholder,
			'required'     => $required ? 'yes' : '',
			'width'        => $width,
			'width_mobile' => '100',
		), $extra );
	};

	$info = function ( $icon, $text ) {
		return array(
			'_id'           => teckform_el_id(),
			'text'          => $text,
			'selected_icon' => array(
				'value'   => $icon,
				'library' => 'fa-solid',
			),
		);
	};

	return array(
		teckform_demo_page_header( __( 'Contact', 'teckform' ), __( "Let's build something great", 'teckform' ), __( 'Tell us about your project. We reply within one business day.', 'teckform' ) ),
		teckform_el_section( array(
			teckform_el_row( array(
				array(
					teckform_demo_heading( __( 'Get in touch', 'teckform' ), 'h3' ),
					teckform_demo_text( '<p>' . esc_html__( 'Prefer email or a call? Reach us directly.', 'teckform' ) . '</p>' ),
					teckform_el_widget( 'icon-list', array(
						'icon_list'  => array(
							$info( 'fas fa-envelope', 'hello@teckform.studio' ),
							$info( 'fas fa-phone', '+1 (555) 010-2030' ),
							$info( 'fas fa-map-marker-alt', __( '100 Market Street, Suite 300', 'teckform' ) ),
							$info( 'fas fa-clock', __( 'Sunday to Thursday, 9:00 to 18:00', 'teckform' ) ),
						),
						'icon_color' => '#2563EB',
						'space_between' => array(
							'unit' => 'px',
							'size' => 14,
						),
					) ),
				),
				teckform_el_widget( 'teckform-contact-form', array(
					'fields'            => array(
						$field( 'text', __( 'Name', 'teckform' ), __( 'Your name', 'teckform' ), '50' ),
						$field( 'email', __( 'Email', 'teckform' ), 'you@company.com', '50' ),
						$field( 'tel', __( 'Phone', 'teckform' ), '+1 555 000 0000', '50', false ),
						$field( 'select', __( 'Topic', 'teckform' ), __( 'Choose a topic', 'teckform' ), '50', true, array( 'field_options' => "New website\nRedesign\nMobile app\nSomething else" ) ),
						$field( 'textarea', __( 'Message', 'teckform' ), __( 'Tell us about your project', 'teckform' ), '100', true, array( 'rows' => 6 ) ),
					),
					'submit_text'       => __( 'Send message', 'teckform' ),
					'submit_icon'       => array(
						'value'   => 'fas fa-paper-plane',
						'library' => 'fa-solid',
					),
					'email_subject'     => __( 'New {Topic} enquiry from {Name}', 'teckform' ),
					'form_name'         => __( 'Contact page', 'teckform' ),
					'success_message'   => __( "Thanks! We'll reply within one business day.", 'teckform' ),
					'button_full_width' => 'yes',
				) ),
			), 2, 64 ),
		) ),
		teckform_el_section( array(
			teckform_el_widget( 'google_maps', array(
				'address' => '100 Market Street, San Francisco',
				'zoom'    => array(
					'unit' => 'px',
					'size' => 14,
				),
				'height'  => array(
					'unit' => 'px',
					'size' => 420,
				),
			) ),
		), array( 'full' => true ) ),
	);
}

/**
 * @param array    $content Demo content.
 * @param callable $url     Page URL by key.
 * @return array
 */
function teckform_demo_careers( $content, $url ) {
	$job = function ( $title, $text ) {
		return array(
			'_id'      => teckform_el_id(),
			'question' => $title,
			'answer'   => '<p>' . $text . '</p>',
		);
	};

	return array(
		teckform_el_section( array(
			teckform_el_widget( 'teckform-hero', array(
				'layout'        => 'centered',
				'badge_label'   => __( 'Hiring', 'teckform' ),
				'badge_text'    => __( '3 open roles', 'teckform' ),
				'title_before'  => __( 'Do the best work of your', 'teckform' ),
				'title_words'   => __( 'career', 'teckform' ),
				'title_after'   => '',
				'description'   => __( 'Small team, real ownership, remote-friendly, and projects you will be proud to show.', 'teckform' ),
				'button_1_text' => __( 'See open roles', 'teckform' ),
				'button_1_link' => array( 'url' => '#open-roles' ),
				'button_2_text' => '',
				'trust_type'    => 'stats',
				'pattern'       => 'dots',
			) ),
		), array( 'full' => true ) ),
		teckform_el_section( array(
			teckform_el_widget( 'teckform-faq', array(
				'behavior' => 'single',
				'schema'   => '',
				'items'    => array(
					$job( __( 'Senior Product Designer (remote)', 'teckform' ), __( 'Lead design on client projects from first workshop to launch.', 'teckform' ) ),
					$job( __( 'WordPress Developer', 'teckform' ), __( 'Build fast, accessible sites with WordPress, Elementor and modern PHP.', 'teckform' ) ),
					$job( __( 'Project Manager', 'teckform' ), __( 'Keep projects on time and clients happy, with a calm head.', 'teckform' ) ),
				),
				'_element_id' => 'open-roles',
			) ),
		), array(
			'eyebrow' => __( 'Open roles', 'teckform' ),
			'title'   => __( 'Join the team', 'teckform' ),
		) ),
		teckform_demo_cta( $content ),
	);
}

/**
 * @param array $images Demo images.
 * @return array
 */
function teckform_demo_coming_soon( $images ) {
	return array(
		teckform_el_container(
			array(
				teckform_el_widget( 'teckform-site-logo', array(
					'logo_source' => 'site',
					'align'       => 'center',
					'width'       => array(
						'unit' => 'px',
						'size' => 180,
					),
				) ),
				teckform_el_widget( 'teckform-animated-headline', array(
					'effect'         => 'typing',
					'before_text'    => __( 'Something new is', 'teckform' ),
					'animated_words' => "coming\nalmost here\nworth the wait",
					'after_text'     => '',
					'tag'            => 'h1',
					'align'          => 'center',
				) ),
				teckform_el_widget( 'teckform-countdown', array(
					'mode'          => 'fixed',
					'due_date'      => wp_date( 'Y-m-d H:i', strtotime( '+30 days' ) ),
					'layout'        => 'boxes',
					'align'         => 'center',
					'separators'    => 'yes',
					'expire_action' => 'message',
				) ),
				teckform_el_widget( 'teckform-newsletter', array(
					'mode'              => 'wordpress',
					'layout'            => 'inline',
					'email_placeholder' => __( 'Get notified at launch', 'teckform' ),
					'button_text'       => __( 'Notify me', 'teckform' ),
					'list_name'         => __( 'Coming soon', 'teckform' ),
					'form_align'        => 'center',
					'form_width'        => array(
						'unit' => 'px',
						'size' => 480,
					),
					// The centered container would otherwise shrink the widget to its content.
					'_element_width'        => 'initial',
					'_element_custom_width' => array(
						'unit' => 'px',
						'size' => 480,
					),
					'_element_custom_width_mobile' => array(
						'unit' => '%',
						'size' => 100,
					),
				) ),
			),
			array(
				'min_height'           => array(
					'unit' => 'vh',
					'size' => 100,
				),
				'flex_justify_content' => 'center',
				'flex_align_items'     => 'center',
				'flex_gap'             => array(
					'unit'   => 'px',
					'size'   => 32,
					'column' => '32',
					'row'    => '32',
				),
				'background_background' => 'classic',
				'background_color'     => '#F8FAFC',
				'teckform_dark_bg'     => '#0B1120',
				'padding'              => teckform_el_box( 48, 20 ),
			)
		),
	);
}
