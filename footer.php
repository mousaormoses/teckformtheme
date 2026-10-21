<?php
/**
 * Site footer.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div>
</main>

<?php if ( ! teckform_render_template( 'footer' ) && ! teckform_elementor_location( 'footer' ) ) : ?>
<footer class="site-footer">
	<div class="site-container">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Footer Menu', 'teckform' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
				) );
				?>
			</nav>
		<?php endif; ?>
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<?php
		if ( teckform_dark_mode_enabled() ) {
			echo teckform_dark_mode_toggle_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
		}
		?>
	</div>
</footer>
<?php endif; ?>

<?php if ( get_theme_mod( 'teckform_back_to_top', true ) ) : ?>
	<button type="button" class="teckform-back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'teckform' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5l-7 7 1.4 1.4 4.6-4.6V19h2V8.8l4.6 4.6L19 12z"/></svg>
	</button>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
