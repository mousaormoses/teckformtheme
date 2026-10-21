<?php
/**
 * Site header.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'teckform' ); ?></a>

<?php if ( ! teckform_render_template( 'header' ) && ! teckform_elementor_location( 'header' ) ) : ?>
<header class="site-header">
	<div class="site-container">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<p class="site-description"><?php bloginfo( 'description' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="main-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'teckform' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 1,
				) );
				?>
			</nav>
		<?php endif; ?>
	</div>
</header>
<?php endif; ?>

<main id="content" class="site-main">
	<div class="site-container">
