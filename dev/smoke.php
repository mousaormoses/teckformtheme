<?php
/**
 * Dev only: put every Teckform widget (default settings) on one "Widget Smoke Test" page.
 * Run: docker compose -f dev/docker-compose.yml run --rm cli wp eval-file /seed/smoke.php
 */

$types = array();

foreach ( \Elementor\Plugin::instance()->widgets_manager->get_widget_types() as $name => $widget ) {
	if ( 0 === strpos( $name, 'teckform-' ) ) {
		$types[] = $name;
	}
}

$elements = array();

foreach ( $types as $i => $type ) {
	$elements[] = array(
		'id'       => 's' . substr( md5( $type ), 0, 6 ),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => (object) array(),
		'elements' => array(
			array(
				'id'         => 'w' . substr( md5( $type . 'w' ), 0, 6 ),
				'elType'     => 'widget',
				'widgetType' => $type,
				'settings'   => (object) array(),
				'elements'   => array(),
			),
		),
	);
}

$page = get_page_by_path( 'widget-smoke-test' );
$id   = $page ? $page->ID : wp_insert_post( array(
	'post_type'   => 'page',
	'post_status' => 'publish',
	'post_title'  => 'Widget Smoke Test',
	'post_name'   => 'widget-smoke-test',
) );

update_post_meta( $id, '_elementor_edit_mode', 'builder' );
update_post_meta( $id, '_elementor_template_type', 'wp-page' );
update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
\Elementor\Plugin::instance()->files_manager->clear_cache();

echo 'Widgets on page ' . $id . ': ' . implode( ', ', $types ) . "\n";
