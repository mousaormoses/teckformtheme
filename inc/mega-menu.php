<?php
/**
 * Mega menus: a menu item can show a Section template (designed in Elementor)
 * as its dropdown. Chosen in Appearance > Menus, shown by the Nav Menu widget.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Mega menu" field on each menu item in Appearance > Menus.
 *
 * @param int $item_id Menu item ID.
 */
function teckform_mega_menu_field( $item_id ) {
	$sections = function_exists( 'teckform_get_section_templates' ) ? teckform_get_section_templates() : array();
	$current  = (int) get_post_meta( $item_id, '_teckform_mega_menu', true );

	wp_nonce_field( 'teckform_mega_menu', 'teckform_mega_menu_nonce' );
	?>
	<p class="field-teckform-mega description description-wide">
		<label for="teckform-mega-<?php echo esc_attr( $item_id ); ?>">
			<?php esc_html_e( 'Mega menu', 'teckform' ); ?><br>
			<select id="teckform-mega-<?php echo esc_attr( $item_id ); ?>" name="teckform_mega_menu[<?php echo esc_attr( $item_id ); ?>]" class="widefat">
				<option value=""><?php esc_html_e( 'None (normal dropdown)', 'teckform' ); ?></option>
				<?php foreach ( $sections as $id => $title ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $current, $id ); ?>><?php echo esc_html( $title ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<span class="description">
			<?php
			if ( $sections ) {
				esc_html_e( 'Shows a Section template as this item\'s dropdown. Works on top-level items in the Teckform Nav Menu widget.', 'teckform' );
			} else {
				esc_html_e( 'Create a template of type "Section" in Header & Footer first.', 'teckform' );
			}
			?>
		</span>
	</p>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'teckform_mega_menu_field' );

/**
 * Save the field.
 *
 * @param int $menu_id Menu ID.
 * @param int $item_id Menu item ID.
 */
function teckform_save_mega_menu_field( $menu_id, $item_id ) {
	if (
		! isset( $_POST['teckform_mega_menu_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['teckform_mega_menu_nonce'] ) ), 'teckform_mega_menu' )
		|| ! current_user_can( 'edit_theme_options' )
	) {
		return;
	}

	$value = isset( $_POST['teckform_mega_menu'][ $item_id ] ) ? absint( $_POST['teckform_mega_menu'][ $item_id ] ) : 0;

	if ( $value && 'teckform_template' === get_post_type( $value ) ) {
		update_post_meta( $item_id, '_teckform_mega_menu', $value );
	} else {
		delete_post_meta( $item_id, '_teckform_mega_menu' );
	}
}
add_action( 'wp_update_nav_menu_item', 'teckform_save_mega_menu_field', 10, 2 );

/**
 * The Section template a top-level menu item shows as its mega menu.
 *
 * @param WP_Post $item Menu item.
 * @return int Template ID, or 0.
 */
function teckform_get_mega_menu_id( $item ) {
	if ( ! empty( $item->menu_item_parent ) ) {
		return 0;
	}

	$template_id = (int) get_post_meta( $item->ID, '_teckform_mega_menu', true );

	return $template_id && 'publish' === get_post_status( $template_id ) ? $template_id : 0;
}
