<?php
/**
 * comments.php
 * The page template comment overrides.
 * To override this template, copy this file to a muut directory under your theme's root
 * (wp-content/themes/my-theme/muut/comments.php) and make any modifications you like!
 *
 * @package Muut
 * @copyright 2014 Muut Inc
 */


get_header();

$comments_path = Muut_Comment_Overrides::instance()->getCommentsPath( get_the_ID(), true );
?>
<div id="comments" class="comments-area">
	<?php muut_comments_override_anchor(); ?>
</div>