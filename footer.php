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

<?php if ( ! teckform_elementor_location( 'footer' ) ) : ?>
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
	</div>
</footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
