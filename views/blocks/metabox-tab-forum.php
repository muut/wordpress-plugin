<?php
/**
 * The post/page editor tab for forum preferences.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
global $post;
$tab;

$meta_name = $tab['meta_name'];
$forum_settings = Muut_Post_Utility::getPostOption( $post->ID, 'forum_settings' );
$forum_defaults = muut()->getOption( 'forum_defaults' );

$forum_update_default = muut()->getOption( 'show_comments_in_forum_default' );
$forum_defaults['show_comments_in_forum'] = $forum_update_default ? $forum_update_default : $forum_defaults['show_comments_in_forum'];

$hide_online = isset( $forum_settings['hide_online'] ) ? $forum_settings['hide_online'] : $forum_defaults['hide_online'];
$disable_uploads = isset( $forum_settings['disable_uploads'] ) ? $forum_settings['disable_uploads'] : $forum_defaults['disable_uploads'];
$forum_show_comments = isset( $forum_settings['show_comments_in_forum'] ) ? $forum_settings['show_comments_in_forum'] : $forum_defaults['show_comments_in_forum'];
?>
<?php $forum_page_id = Muut_Post_Utility::getForumPageId();
if ( !$forum_page_id || $forum_page_id == $post->ID ) { ?>
	<p>
		<span class="checkbox_row"><input type="checkbox" name="<?php echo $tab['meta_name']; ?>[enabled-tab]" class="muut_enable_<?php echo $tab['name']; ?>" id="muut_enable_tab-<?php echo $tab['name']; ?>" <?php checked( $active_tab, $tab['name'] ); ?> value="1" /><label for="muut_enable_tab-<?php echo $tab['name']; ?>"><?php echo $tab['enable_text']; ?></label></span>
	</p>
<?php } else {	?>
	<p>
		<span class="description"><?php printf( __( 'Current forum page is %s', 'muut' ), '<a href="' . add_query_arg( array( 'post' => $forum_page_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) . '">' . get_the_title( $forum_page_id ) . '</a>'); ?></span>
	</p>
<?php } ?>
<div class="enabled_tab_wrapper">
	<p>
		<span class="checkbox_row"><input type="checkbox" name="<?php echo $meta_name; ?>[hide_online]" id="muut_forum_hide_online" value="1" <?php checked( $hide_online, '1' ); ?> /><label for="muut_forum_hide_online"><?php _e( 'Hide online users', 'muut' ); ?></label></span>
		<span class="checkbox_row"><input type="checkbox" name="<?php echo $meta_name; ?>[disable_uploads]" id="muut_forum_disable_uploads" value="1" <?php checked( $disable_uploads, '1' ); ?> /><label for="muut_forum_disable_uploads"><?php _e( 'Disable image uploads', 'muut' ); ?></label></span>
		<span class="checkbox_row"><input type="checkbox" name="<?php echo $meta_name; ?>[show_comments_in_forum]" id="muut_forum_show_comments_in_forum" value="1" <?php checked( $forum_show_comments, '1' ); ?> /><label for="muut_forum_show_comments_in_forum"><?php _e( 'Show Comments', 'muut' ); ?></label></span>
	</p>
</div>
<div class="disabled_tab_wrapper">
	<?php do_action( 'muut_disabled_tab_content', $tab['name'] ); ?>
</div>