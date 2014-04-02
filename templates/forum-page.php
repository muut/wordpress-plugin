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
$sub_forum = muut_get_page_forum_name( $post->ID );
?>

	<div id="main-content" class="main-content">
		<div id="primary" class="content-area">
			<div id="content" class="site-content" role="main">
				<?php
						if ( $sub_forum == '' ) {
							_e( 'Forum not found!', 'muut' );
						} else {
							echo '<a class="moot" href="https://moot.it/i/' . $root_forum . '/' . $sub_forum .'">' . get_the_title( $post->ID ) . '</a>';
						}
				?>
			</div><!-- #content -->
		</div><!-- #primary -->
	</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
