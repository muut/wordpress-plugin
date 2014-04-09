<?php
/**
 * The markup for the Muut page editor metabox.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

$is_forum = Muut_Forum_Page_Utility::isForumPage( get_the_ID() );

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
<div id="muut_page_forum_settings" class="<?php echo $forum_show_class; ?>">
	<p><strong><?php _e( 'Forum Path', 'muut' ); ?></strong></p>
	<label class="screen-reader-text" for="muut_forum_remote_path"><?php _e( 'Forum Path', 'muut' ); ?></label>
	<input type="text" name="muut_forum_remote_path" id="muut_forum_remote_path" value="<?php echo Muut_Forum_Page_Utility::getRemoteForumPath( get_the_ID(), true ); ?>" />
	<p>
		<input name="muut_forum_is_threaded" type="checkbox" id="muut_forum_is_threaded" value="1" <?php checked( '1', Muut_Forum_Page_Utility::getForumPageOption( get_the_ID(), 'is_threaded' ) ); ?> />
		<strong><?php _e( 'Threaded Posts', 'muut' ); ?></strong>
		<label class="screen-reader-text" for="muut_forum_is_threaded"><?php _e( 'Threaded Posts', 'muut' ); ?></label>
	</p>
	<p>
		<input name="muut_forum_show_online" type="checkbox" id="muut_forum_show_online" value="1" <?php checked( '1', Muut_Forum_Page_Utility::getForumPageOption( get_the_ID(), 'show_online' ) ); ?> />
		<strong><?php _e( 'Show Online Users', 'muut' ); ?></strong>
		<label class="screen-reader-text" for="muut_forum_show_online"><?php _e( 'Show Online Users', 'muut' ); ?></label>
	</p>
	<p>
		<input name="muut_forum_allow_uploads" type="checkbox" id="muut_forum_allow_uploads" value="1" <?php checked( '1', Muut_Forum_Page_Utility::getForumPageOption( get_the_ID(), 'allow_uploads' ) ); ?> />
		<strong><?php _e( 'Allow Image Uploads', 'muut' ); ?></strong>
		<label class="screen-reader-text" for="muut_forum_allow_uploads"><?php _e( 'Allow Image Uploads', 'muut' ); ?></label>
	</p>
</div>