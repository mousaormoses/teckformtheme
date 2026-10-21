<?php
/**
 * Small helpers to build Elementor element trees for the demo pages.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A new 7-character element ID, like Elementor's own.
 *
 * @return string
 */
function teckform_el_id() {
	return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
}

/**
 * A widget element.
 *
 * @param string $type     Widget name, e.g. 'teckform-hero' or 'heading'.
 * @param array  $settings Widget settings.
 * @return array
 */
function teckform_el_widget( $type, $settings = array() ) {
	return array(
		'id'         => teckform_el_id(),
		'elType'     => 'widget',
		'widgetType' => $type,
		'settings'   => (object) $settings,
		'elements'   => array(),
	);
}

/**
 * A flexbox container.
 *
 * @param array $children Child elements.
 * @param array $settings Container settings.
 * @param bool  $inner    Whether it sits inside another container.
 * @return array
 */
function teckform_el_container( $children = array(), $settings = array(), $inner = false ) {
	return array(
		'id'       => teckform_el_id(),
		'elType'   => 'container',
		'isInner'  => $inner,
		'settings' => (object) $settings,
		'elements' => $children,
	);
}

/**
 * Padding/margin value for a DIMENSIONS control.
 *
 * @param int|string $top    Top.
 * @param int|string $right  Right (defaults to top).
 * @param int|string $bottom Bottom (defaults to top).
 * @param int|string $left   Left (defaults to right).
 * @return array
 */
function teckform_el_box( $top, $right = null, $bottom = null, $left = null ) {
	$right  = null === $right ? $top : $right;
	$bottom = null === $bottom ? $top : $bottom;
	$left   = null === $left ? $right : $left;

	return array(
		'unit'     => 'px',
		'top'      => (string) $top,
		'right'    => (string) $right,
		'bottom'   => (string) $bottom,
		'left'     => (string) $left,
		'isLinked' => false,
	);
}

/**
 * A full-width page section: an outer container with vertical padding and an
 * optional heading block, holding the given children in a boxed column.
 *
 * @param array $children Child elements.
 * @param array $args     Optional: 'eyebrow', 'title', 'text', 'bg', 'dark_bg', 'padding', 'full'.
 * @return array
 */
function teckform_el_section( $children, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'eyebrow' => '',
		'title'   => '',
		'text'    => '',
		'bg'      => '',
		'dark_bg' => '',
		'padding' => 96,
		'full'    => false,
	) );

	$intro = array();

	if ( $args['eyebrow'] ) {
		$intro[] = teckform_el_widget( 'heading', array(
			'title'                      => $args['eyebrow'],
			'header_size'                => 'p',
			'align'                      => 'center',
			'title_color'                => '#2563EB',
			'typography_typography'      => 'custom',
			'typography_font_size'       => array(
				'unit' => 'px',
				'size' => 14,
			),
			'typography_font_weight'     => '600',
			'typography_text_transform'  => 'uppercase',
			'typography_letter_spacing'  => array(
				'unit' => 'px',
				'size' => 1.5,
			),
		) );
	}

	if ( $args['title'] ) {
		$intro[] = teckform_el_widget( 'heading', array(
			'title'                  => $args['title'],
			'header_size'            => 'h2',
			'align'                  => 'center',
			'typography_typography'  => 'custom',
			'typography_font_size'   => array(
				'unit' => 'px',
				'size' => 40,
			),
			'typography_font_size_mobile' => array(
				'unit' => 'px',
				'size' => 30,
			),
			'typography_font_weight' => '700',
		) );
	}

	if ( $args['text'] ) {
		$intro[] = teckform_el_widget( 'text-editor', array(
			'editor' => '<p style="text-align:center;max-width:640px;margin:0 auto;">' . esc_html( $args['text'] ) . '</p>',
		) );
	}

	if ( $intro ) {
		$children = array_merge(
			array(
				teckform_el_container( $intro, array(
					'flex_gap'      => array(
						'unit'   => 'px',
						'size'   => 12,
						'column' => '12',
						'row'    => '12',
					),
					'margin'        => teckform_el_box( 0, 0, 40, 0 ),
					'padding'       => teckform_el_box( 0 ),
				), true ),
			),
			$children
		);
	}

	$settings = array(
		'content_width' => $args['full'] ? 'full' : 'boxed',
		'padding'       => teckform_el_box( $args['padding'], 20 ),
		'padding_mobile' => teckform_el_box( min( 64, (int) $args['padding'] ), 16 ),
	);

	if ( $args['full'] ) {
		$settings['padding']        = teckform_el_box( 0 );
		$settings['padding_mobile'] = teckform_el_box( 0 );
	}

	if ( $args['bg'] ) {
		$settings['background_background'] = 'classic';
		$settings['background_color']      = $args['bg'];
	}

	if ( $args['dark_bg'] ) {
		$settings['teckform_dark_bg'] = $args['dark_bg'];
	}

	return teckform_el_container( $children, $settings );
}

/**
 * A row of equal columns inside a section.
 *
 * @param array $children Child elements.
 * @param int   $columns  Columns on desktop (stacks to 1 on mobile).
 * @param int   $gap      Gap in px.
 * @return array
 */
function teckform_el_row( $children, $columns = 2, $gap = 48 ) {
	$cells = array();

	foreach ( $children as $child ) {
		$cells[] = teckform_el_container( is_array( $child ) && isset( $child[0] ) ? $child : array( $child ), array(
			'content_width' => 'full',
			'padding'       => teckform_el_box( 0 ),
		), true );
	}

	$fr = function ( $count ) {
		return array(
			'unit' => 'fr',
			'size' => $count,
		);
	};

	// Elementor's grid container: equal columns that drop to 2 on tablet and 1 on phones.
	return teckform_el_container( $cells, array(
		'container_type'           => 'grid',
		'content_width'            => 'full',
		'grid_columns_grid'        => $fr( $columns ),
		'grid_columns_grid_tablet' => $fr( min( 2, $columns ) ),
		'grid_columns_grid_mobile' => $fr( 1 ),
		'grid_rows_grid'           => $fr( 1 ),
		'grid_auto_flow'           => 'row',
		'grid_align_items'         => 'center',
		'grid_gaps'                => array(
			'unit'     => 'px',
			'column'   => (string) $gap,
			'row'      => (string) $gap,
			'isLinked' => true,
		),
		'padding'                  => teckform_el_box( 0 ),
	), true );
}
