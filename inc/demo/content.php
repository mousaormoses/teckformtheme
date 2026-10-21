<?php
/**
 * Demo content: services, projects, team, testimonials, blog posts, pages and menus.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create the demo posts and empty pages (pages get their Elementor layouts later,
 * once the menus and templates they refer to exist).
 *
 * @param array $images Demo images.
 * @return array{pages: array<string, int>, services: int[], projects: int[], members: int[], testimonials: int[], posts: int[]}
 */
function teckform_demo_create_content( $images ) {
	$lorem = __( 'We start every project by listening. Then we plan, design and build in short steps, sharing progress every week so you always know where things stand. The result is a product your customers enjoy using and your team enjoys running.', 'teckform' );

	$content = array(
		'pages'        => array(),
		'services'     => array(),
		'projects'     => array(),
		'members'      => array(),
		'testimonials' => array(),
		'posts'        => array(),
	);

	// Services.
	$services = array(
		array( __( 'Web Design', 'teckform' ), 'fas fa-pen-nib', __( 'Beautiful, fast websites that turn visitors into customers.', 'teckform' ) ),
		array( __( 'Web Development', 'teckform' ), 'fas fa-code', __( 'Custom WordPress and web apps built to grow with you.', 'teckform' ) ),
		array( __( 'Mobile Apps', 'teckform' ), 'fas fa-mobile-alt', __( 'iOS and Android apps your customers will love to open.', 'teckform' ) ),
		array( __( 'Branding', 'teckform' ), 'fas fa-palette', __( 'Logos, colors and a voice that make you memorable.', 'teckform' ) ),
		array( __( 'SEO and Marketing', 'teckform' ), 'fas fa-chart-line', __( 'Get found on Google and turn traffic into sales.', 'teckform' ) ),
		array( __( 'Support and Hosting', 'teckform' ), 'fas fa-shield-alt', __( 'Fast, secure hosting and a team on call when you need us.', 'teckform' ) ),
	);

	foreach ( $services as $i => $service ) {
		$content['services'][] = teckform_demo_insert( array(
			'post_type'    => 'teckform_service',
			'post_title'   => $service[0],
			'post_excerpt' => $service[2],
			'post_content' => '<p>' . $service[2] . '</p><p>' . $lorem . '</p>',
			'menu_order'   => $i,
			'meta'         => array(
				'_teckform_icon' => $service[1],
				'_thumbnail_id'  => isset( $images[ 'project-' . ( $i + 1 ) ] ) ? $images[ 'project-' . ( $i + 1 ) ] : 0,
			),
		) );
	}

	// Project categories and projects.
	$terms = array();

	foreach ( array( __( 'Web Design', 'teckform' ), __( 'Branding', 'teckform' ), __( 'Mobile Apps', 'teckform' ) ) as $name ) {
		$term = term_exists( $name, 'teckform_project_cat' );
		$term = $term ? $term : wp_insert_term( $name, 'teckform_project_cat' );

		if ( ! is_wp_error( $term ) ) {
			$terms[] = (int) $term['term_id'];
		}
	}

	update_option( 'teckform_demo_terms', $terms );

	$projects = array(
		array( 'Northwind Travel', 'Northwind', 0 ),
		array( 'Lumen Coffee', 'Lumen', 1 ),
		array( 'Orbital Fitness App', 'Orbital', 2 ),
		array( 'Kestrel Finance', 'Kestrel', 0 ),
		array( 'Vertex Architects', 'Vertex', 1 ),
		array( 'Halcyon Wellness App', 'Halcyon', 2 ),
	);

	foreach ( $projects as $i => $project ) {
		$id = teckform_demo_insert( array(
			'post_type'    => 'teckform_project',
			'post_title'   => $project[0],
			/* translators: %s: client name. */
			'post_excerpt' => sprintf( __( 'A new digital home for %s.', 'teckform' ), $project[1] ),
			'post_content' => '<p>' . $lorem . '</p>',
			'meta'         => array(
				'_teckform_client' => $project[1],
				'_teckform_year'   => (string) ( 2026 - ( $i % 3 ) ),
				'_thumbnail_id'    => isset( $images[ 'project-' . ( $i + 1 ) ] ) ? $images[ 'project-' . ( $i + 1 ) ] : 0,
			),
		) );

		if ( $id && isset( $terms[ $project[2] ] ) ) {
			wp_set_object_terms( $id, array( $terms[ $project[2] ] ), 'teckform_project_cat' );
		}

		$content['projects'][] = $id;
	}

	// Team.
	$members = array(
		array( 'Sara Haddad', __( 'Founder and CEO', 'teckform' ) ),
		array( 'Omar Khalil', __( 'Lead Developer', 'teckform' ) ),
		array( 'Lina Mansour', __( 'Design Director', 'teckform' ) ),
		array( 'Yousef Nasser', __( 'Marketing Lead', 'teckform' ) ),
	);

	foreach ( $members as $i => $member ) {
		$content['members'][] = teckform_demo_insert( array(
			'post_type'    => 'teckform_member',
			'post_title'   => $member[0],
			'post_content' => __( 'Ten years of turning ideas into products people love.', 'teckform' ),
			'menu_order'   => $i,
			'meta'         => array(
				'_teckform_role'     => $member[1],
				'_teckform_linkedin' => 'https://www.linkedin.com/',
				'_teckform_x'        => 'https://x.com/',
				'_thumbnail_id'      => isset( $images[ 'person-' . ( $i + 1 ) ] ) ? $images[ 'person-' . ( $i + 1 ) ] : 0,
			),
		) );
	}

	// Testimonials.
	$testimonials = array(
		array( 'Nadia Farouk', 'CEO, Northwind', __( 'They rebuilt our website in six weeks and our online bookings doubled. Clear, friendly and always on time.', 'teckform' ) ),
		array( 'James Carter', 'Founder, Lumen Coffee', __( 'The new brand finally feels like us. Customers keep telling us how much they love the packaging.', 'teckform' ) ),
		array( 'Maya Chen', 'Product Lead, Orbital', __( 'Our app went from idea to the App Store in three months, with a 4.8 star rating at launch.', 'teckform' ) ),
		array( 'Khaled Saleh', 'Director, Vertex', __( 'Professional from the first call. They understood what we needed better than we did.', 'teckform' ) ),
		array( 'Emma Laurent', 'Marketing Manager, Kestrel', __( 'Our traffic from Google tripled in a year. Best investment we made.', 'teckform' ) ),
	);

	foreach ( $testimonials as $i => $testimonial ) {
		$content['testimonials'][] = teckform_demo_insert( array(
			'post_type'    => 'teckform_testimonial',
			'post_title'   => $testimonial[0],
			'post_content' => $testimonial[2],
			'menu_order'   => $i,
			'meta'         => array(
				'_teckform_role'   => $testimonial[1],
				'_teckform_rating' => '5',
				'_thumbnail_id'    => isset( $images[ 'person-' . ( ( $i % 6 ) + 1 ) ] ) ? $images[ 'person-' . ( ( $i % 6 ) + 1 ) ] : 0,
			),
		) );
	}

	// Blog posts.
	$posts = array(
		__( '7 signs your website needs a redesign', 'teckform' ),
		__( 'How we plan a project in one week', 'teckform' ),
		__( 'Mobile app or website: which do you need first?', 'teckform' ),
	);

	foreach ( $posts as $i => $title ) {
		$content['posts'][] = teckform_demo_insert( array(
			'post_type'    => 'post',
			'post_title'   => $title,
			'post_excerpt' => __( 'Practical advice from our team, based on hundreds of projects.', 'teckform' ),
			'post_content' => '<p>' . $lorem . '</p><h2>' . __( 'Where to start', 'teckform' ) . '</h2><p>' . $lorem . '</p>',
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( $i * 9 + 2 ) . ' days' ) ),
			'meta'         => array(
				'_thumbnail_id' => isset( $images[ 'post-' . ( $i + 1 ) ] ) ? $images[ 'post-' . ( $i + 1 ) ] : 0,
			),
		) );
	}

	// Pages, filled in by teckform_demo_create_pages().
	$pages = array(
		'home'        => __( 'Home', 'teckform' ),
		'about'       => __( 'About', 'teckform' ),
		'services'    => __( 'Services', 'teckform' ),
		'portfolio'   => __( 'Portfolio', 'teckform' ),
		'pricing'     => __( 'Pricing', 'teckform' ),
		'blog'        => __( 'Blog', 'teckform' ),
		'contact'     => __( 'Contact', 'teckform' ),
		'careers'     => __( 'Careers', 'teckform' ),
		'account'     => __( 'My Account', 'teckform' ),
		'coming-soon' => __( 'Coming Soon', 'teckform' ),
	);

	foreach ( $pages as $key => $title ) {
		$content['pages'][ $key ] = teckform_demo_insert( array(
			'post_title' => $title,
			'post_name'  => 'home' === $key ? 'home-demo' : $key,
		) );
	}

	return $content;
}

/**
 * Main menu and two footer link menus.
 *
 * @param array $content Demo content.
 * @return array{main: int, company: int, services: int, services_item: int}
 */
function teckform_demo_create_menus( $content ) {
	$menus = array();

	$create = function ( $name ) {
		$existing = wp_get_nav_menu_object( $name );

		if ( $existing ) {
			wp_delete_nav_menu( $existing->term_id );
		}

		return (int) wp_create_nav_menu( $name );
	};

	$add_page = function ( $menu_id, $page_id, $parent = 0 ) {
		return (int) wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-object-id' => $page_id,
			'menu-item-object'    => get_post_type( $page_id ),
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent,
		) );
	};

	$menus['main'] = $create( 'Teckform Main Menu' );

	$add_page( $menus['main'], $content['pages']['home'] );
	$add_page( $menus['main'], $content['pages']['about'] );
	$menus['services_item'] = $add_page( $menus['main'], $content['pages']['services'] );

	// Services also get normal children, used in the mobile menu and if the mega menu is removed.
	foreach ( array_slice( $content['services'], 0, 4 ) as $service_id ) {
		$add_page( $menus['main'], $service_id, $menus['services_item'] );
	}

	foreach ( array( 'portfolio', 'pricing', 'blog', 'contact' ) as $key ) {
		$add_page( $menus['main'], $content['pages'][ $key ] );
	}

	$menus['company'] = $create( 'Teckform Footer: Company' );

	foreach ( array( 'about', 'careers', 'blog', 'contact' ) as $key ) {
		$add_page( $menus['company'], $content['pages'][ $key ] );
	}

	$menus['services'] = $create( 'Teckform Footer: Services' );

	foreach ( $content['services'] as $service_id ) {
		$add_page( $menus['services'], $service_id );
	}

	update_option( 'teckform_demo_menus', array( $menus['main'], $menus['company'], $menus['services'] ) );

	set_theme_mod( 'nav_menu_locations', array(
		'primary' => $menus['main'],
		'footer'  => $menus['company'],
	) );

	return $menus;
}

/**
 * Hook the mega menu template onto the Services menu item.
 *
 * @param array $content Demo content.
 * @param array $menus   Demo menus.
 * @param array $parts   Demo templates.
 */
function teckform_demo_link_menus( $content, $menus, $parts ) {
	if ( ! empty( $parts['mega'] ) && ! empty( $menus['services_item'] ) ) {
		update_post_meta( $menus['services_item'], '_teckform_mega_menu', $parts['mega'] );
	}
}
