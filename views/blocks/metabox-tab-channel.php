<?php
/**
 * The post/page editor tab for channel preferences.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
global $post;
$tab;

$meta_name = $tab['meta_name'];
$channel_settings = get_post_meta( $post->ID, $meta_name, true );
$channel_defaults = muut()->getOption( 'channel_defaults' );

$hide_online = isset( $channel_settings['hide_online'] ) ? $channel_settings['hide_online'] : $channel_defaults['hide_online'];
$disable_uploads = isset( $channel_settings['disable_uploads'] ) ? $channel_settings['disable_uploads'] : $channel_defaults['disable_uploads'];
?>

<p>
	<span class="description"><?php _e( 'A channel is a standalone discussion area.', 'muut' ); ?></span>
</p>
<p>
	<span class="checkbox_row"><input type="checkbox" name="<?php echo $meta_name; ?>[hide_online]" id="muut_channel_hide_online" value="1" <?php checked( $hide_online, '1' ); ?> /><label for="muut_channel_hide_online"><?php _e( 'Hide online users', 'muut' ); ?></label></span>
	<span class="checkbox_row"><input type="checkbox" name="<?php echo $meta_name; ?>[disable_uploads]" id="muut_channel_disable_uploads" value="1" <?php checked( $disable_uploads, '1' ); ?> /><label for="muut_channel_disable_uploads"><?php _e( 'Disable image uploads', 'muut' ); ?></label></span>
</p>