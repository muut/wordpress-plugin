<?php
/**
 * The markup for the Muut page editor metabox.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

$is_forum = Muut_Forum_Page_Utility::isForumPage( get_the_ID() );

$forum_show_class = '';
if ( $is_forum != '1' ) {
	$forum_show_class = 'hidden';
}
?>
<h4><?php _e( 'Forum Page', 'muut' ); ?></h4>
<p>
<label class="screen-reader-text" for="muut_is_forum_true"><?php _e( 'Yes', 'muut' ); ?></label>
<label class="screen-reader-text" for="muut_is_forum_false"><?php _e( 'No', 'muut' ); ?></label>
<input type="radio" name="muut_is_forum_page" id="muut_is_forum_true" value="1" <?php checked( '1', $is_forum ); ?> />
<label for="muut_is_forum_true"><?php _e( 'Yes', 'muut' ); ?></label><br />
<input type="radio" name="muut_is_forum_page" id="muut_is_forum_false" value="0" <?php checked( '', $is_forum ); ?> />
<label for="muut_is_forum_false"><?php _e( 'No', 'muut' ); ?></label>
</p>
<div id="muut_page_forum_settings" class="<?php echo $forum_show_class; ?>">
		<h4><?php _e( 'Forum Options', 'muut' ); ?></h4>
		<?php
		$forum_home_id = muut()->getOption( 'forum_home_id', false );
		$disabled = '';
		$disabled_class = '';
		if ( $forum_home_id != '0' && $forum_home_id != get_the_ID() ) {
			$disabled = 'disabled="disabled"';
			$disabled_class = 'disabled';
			$forum_home_post = get_post( $forum_home_id );
			$forum_home_post_title = $forum_home_post->post_title;
		} else if ( $forum_home_id === false ) {
			$forum_home_id = get_the_ID();
		}
		$root_forum_post_name = get_the_title( $forum_home_id );
		?>
		<p class="<?php echo $disabled_class; ?>">
			<input name="muut_forum_is_home" <?php echo $disabled; ?> type="checkbox" id="muut_forum_is_home" value="1" <?php checked( get_the_ID(), $forum_home_id ); ?> />
			<?php _e( 'Forum Home Page?', 'muut' ); ?><br />
			<label class="screen-reader-text" for="muut_forum_is_home"><?php _e( 'Forum Home Page?', 'muut' ); ?></label>
		</p>
		<p>
			<?php if ( $forum_home_id != get_the_ID() && $forum_home_id > 0 ) printf( __( 'Current Forum Home is %s', 'muut' ), '<a href="' . admin_url( 'post.php?post=' . $forum_home_id . '&action=edit' ) . '">' . $forum_home_post_title . '</a>' ); ?>
		</p>
		<p>
		<input name="muut_forum_is_threaded" type="checkbox" id="muut_forum_is_threaded" value="1" <?php checked( '1', Muut_Forum_Page_Utility::getForumPageOption( get_the_ID(), 'is_threaded' ) ); ?> />
		<?php _e( 'Threaded Posts', 'muut' ); ?><br />
		<label class="screen-reader-text" for="muut_forum_is_threaded"><?php _e( 'Threaded Posts', 'muut' ); ?></label>
		<input name="muut_forum_show_online" type="checkbox" id="muut_forum_show_online" value="1" <?php checked( '1', Muut_Forum_Page_Utility::getForumPageOption( get_the_ID(), 'show_online' ) ); ?> />
		<?php _e( 'Show Online Users', 'muut' ); ?><br />
		<label class="screen-reader-text" for="muut_forum_show_online"><?php _e( 'Show Online Users', 'muut' ); ?></label>
		<input name="muut_forum_allow_uploads" type="checkbox" id="muut_forum_allow_uploads" value="1" <?php checked( '1', Muut_Forum_Page_Utility::getForumPageOption( get_the_ID(), 'allow_uploads' ) ); ?> />
		<?php _e( 'Allow Image Uploads', 'muut' ); ?>
		<label class="screen-reader-text" for="muut_forum_allow_uploads"><?php _e( 'Allow Image Uploads', 'muut' ); ?></label>
		</p>
	</div>