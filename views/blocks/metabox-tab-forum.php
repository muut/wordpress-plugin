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

$hide_online = isset( $forum_settings['hide_online'] ) ? $forum_settings['hide_online'] : $forum_defaults['hide_online'];
$disable_uploads = isset( $forum_settings['disable_uploads'] ) ? $forum_settings['disable_uploads'] : $forum_defaults['disable_uploads'];
?>
<div class="enabled_tab_wrapper">
	<p>
		<span class="description"><?php _e( 'This will embed your entire forum with navigation on this page.', 'muut' ); ?></span>
	</p>
	<p>
		<span class="checkbox_row"><input type="checkbox" name="<?php echo $meta_name; ?>[hide_online]" id="muut_forum_hide_online" value="1" <?php checked( $hide_online, '1' ); ?> /><label for="muut_forum_hide_online"><?php _e( 'Hide online users', 'muut' ); ?></label></span>
		<span class="checkbox_row"><input type="checkbox" name="<?php echo $meta_name; ?>[disable_uploads]" id="muut_forum_disable_uploads" value="1" <?php checked( $disable_uploads, '1' ); ?> /><label for="muut_forum_disable_uploads"><?php _e( 'Disable image uploads', 'muut' ); ?></label></span>
	</p>
</div>
<div class="disabled_tab_wrapper">
	<p>
		<span class="description"><?php printf( __( '%sClick here%s to enable a forum embed.', 'muut' ), '<a href="#" class="enable_forum_link">', '</a>' ); ?></span>
	</p>
	<p>
		<span class="description"><?php _e( 'This will embed your entire forum with navigation on this page.', 'muut' ); ?></span>
	</p>
</div>