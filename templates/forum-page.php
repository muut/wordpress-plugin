<?php
/**
 * forum-page.php
 * The page template for forum pages.
 * To override this template, copy this file to a muut directory under your theme's root
 * (wp-content/themes/my-theme/muut/forum-page.php) and make any modifications you like!
 *
 * @package   Muut
 * @copyright 2014 Moot Inc
 */


get_header();

global $post;

$root_forum = muut_get_option( 'remote_forum_name', '' );
$sub_forum = Muut_Forum_Page_Utility::getRemoteForumPath( $post->ID );;
?>

	<div id="main-content" class="main-content">
		<div id="primary" class="content-area">
			<div id="content" class="site-content" role="main">
				<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
					<h1 class="entry-title"><?php echo get_the_title(); ?></h1>
				</header>
				<div class="entry-content">
				<?php
					if ( $sub_forum == '' ) {
						_e( 'This page has not been assigned a working forum.', 'muut' );
					} else {
						Muut_Forum_Page_Utility::forumPageAnchor( get_the_ID() );
					}
				?>
				</div>
				</article>
				<?php endwhile; ?>
			</div><!-- #content -->
		</div><!-- #primary -->
	</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
