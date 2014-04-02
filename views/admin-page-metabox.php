<?php
/**
 * The markup for the Muut page editor metabox.
 *
 * @package   Muut
 * @copyright 2014 Moot Inc
 */

$is_forum = get_post_meta( get_the_ID(), 'muut_is_forum_page', true );

error_log( $is_forum );

if ( $is_forum != '1' ) {
	$forum_show_class = 'hidden';
}
?>
<p><strong><?php _e( 'Forum Page', 'muut' ); ?></strong></p>
<label class="screen-reader-text" for="muut_is_forum_true"><?php _e( 'Yes', 'muut' ); ?></label>
<label class="screen-reader-text" for="muut_is_forum_false"><?php _e( 'No', 'muut' ); ?></label>
<input type="radio" name="muut_is_forum_page" id="muut_is_forum_true" value="1" <?php checked( '1', $is_forum ); ?> />
<label for="muut_is_forum_true"><?php _e( 'Yes', 'muut' ); ?></label><br />
<input type="radio" name="muut_is_forum_page" id="muut_is_forum_false" value="0" <?php checked( '', $is_forum ); ?> />
<label for="muut_is_forum_false"><?php _e( 'No', 'muut' ); ?></label>
<div id="muut_page_specify_forum" class="<?php echo $forum_show_class; ?>">
	<p><strong><?php _e( 'Forum', 'muut' ); ?></strong></p>
	<label class="screen-reader-text" for="muut_forum"><?php _e( 'Forum', 'muut' ); ?></label>
	<input type="text" name="muut_forum" id="muut_forum" value="<?php echo get_post_meta( get_the_ID(), 'muut_forum', true ); ?>" />
</div>