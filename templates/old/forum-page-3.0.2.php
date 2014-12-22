<?php
/**
 * forum-page.php
 * The page template for the forum root page. It contains all the Muut UX.
 * To override this template, copy this file to a muut directory under your theme's root
 * (wp-content/themes/my-theme/muut/forum-page.php) and make any modifications you like!
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */


get_header();
?>
	<div id="main-content" class="main-content">
		<div id="primary" class="content-area">
			<div id="content" class="site-content" role="main">
				<?php if ( apply_filters( 'muut_show_title_on_forum_page', false ) ) { ?>
					<h1 class="entry-title"><?php the_title(); ?></h1>
				<?php } ?>
				<?php while ( have_posts() ) : the_post();
					if ( muut_is_forum_page() ) {
						muut_page_embed();
					}
				endwhile; ?>
			</div><!-- #content -->
		</div><!-- #primary -->
	</div><!-- #main-content -->
<?php
if ( apply_filters( 'muut_forum_template_use_primary_theme_sidebar', true ) ) {
	get_sidebar();
}
get_footer();