<?php
/**
 * Main template. WordPress falls back to this for every view the theme
 * doesn't give a more specific template for.
 *
 * @package Teckform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$teckform_is_singular = is_singular();
$teckform_location    = $teckform_is_singular ? 'single' : 'archive';

if ( ! teckform_elementor_location( $teckform_location ) ) :
	if ( ! $teckform_is_singular ) :
		if ( is_search() ) :
			/* translators: %s: search query. */
			echo '<h1>' . sprintf( esc_html__( 'Search results for: %s', 'teckform' ), esc_html( get_search_query() ) ) . '</h1>';
		elseif ( is_archive() ) :
			the_archive_title( '<h1>', '</h1>' );
		endif;
	endif;

	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php
				// Elementor-built pages draw their own title inside the content.
				if ( ! teckform_is_built_with_elementor( get_the_ID() ) ) :
					if ( $teckform_is_singular ) :
						the_title( '<h1 class="entry-title">', '</h1>' );
					else :
						the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
					endif;
				endif;

				if ( $teckform_is_singular ) :
					the_content();
					wp_link_pages();
				else :
					the_excerpt();
				endif;
				?>
			</article>
			<?php
			if ( $teckform_is_singular && ( comments_open() || get_comments_number() ) ) :
				comments_template();
			endif;
		endwhile;

		if ( ! $teckform_is_singular ) :
			the_posts_pagination();
		endif;
	else :
		echo '<p>' . esc_html__( 'Nothing found.', 'teckform' ) . '</p>';
		get_search_form();
	endif;
endif;

get_footer();
