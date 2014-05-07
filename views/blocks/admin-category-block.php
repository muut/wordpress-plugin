<?php
/**
 * The list item block used for the custom navigation administration screen for forum channels.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

if ( !isset( $channel_block_id ) || ( !is_string( $channel_block_id ) && !is_numeric( $channel_block_id ) ) ) {
	$channel_block_id = 'new';
}
if ( !isset( $channel_block_path ) || !is_string( $channel_block_path ) ) {
	$channel_block_path = '';
}
if ( $channel_block_path != '' ) {
	$channel_block_path = '/' . $channel_block_path;
}
if ( !isset( $channel_block_title ) || !is_string( $channel_block_title ) ) {
	$channel_block_title = '';
}

$defaults = muut()->getOption( 'forum_channel_defaults', array() );
if ( isset( $defaults['show_in_allposts'] ) ) {
	$show_in_allposts = $defaults['show_in_allposts'];
} else {
	$show_in_allposts = false;
}

$show_in_allposts = is_numeric( $channel_block_id ) ? get_post_meta( $channel_block_id, 'muut_show_in_allposts', true ) : $show_in_allposts;
?>
<li class="muut_forum_channel_item" id="channel_item-<?php echo $channel_block_id; ?>" data-id="<?php echo $channel_block_id; ?>">
	<div class="muut_channel_item_content">
		<div class="muut_channel_item_basic">
			<label class="screen-reader-text" for="channel-name-<?php echo $channel_block_id; ?>"><?php _e( 'Channel Name', 'muut' ); ?></label>
			<a href="#" class="muut-channel-title x-editable" id="channel-name-<?php echo $channel_block_id; ?>" data-type="text" data-url="#" data-value="<?php echo $channel_block_title; ?>"></a>
			<span class="delete-link"><a href="#"><?php _e( 'Delete', 'muut' ); ?></a></span>
			<span class="action-link advanced-options"><a href="#"><?php _e( 'Advanced', 'muut' ); ?></a></span>
		</div>
		<div class="inline-edit-col muut_channel_advanced_options">
			<h4><?php _e( 'Advanced Options', 'muut' ); ?></h4>
			<p>
				<input class="muut_show_in_allposts_check" id="muut_show_in_allposts_check-<?php echo $channel_block_id; ?>" type="checkbox" <?php checked( true, $show_in_allposts ); ?> />
				<label for="muut_show_in_allposts_check-<?php echo $channel_block_id; ?>"><?php _e( 'Feature in All Posts', 'muut' ); ?></label>
				<label class="screen-reader-text" for="muut_show_in_allposts_check-<?php echo $channel_block_id; ?>"><?php _e( 'Feature in All Posts', 'muut' ); ?></label>
			</p>
			<p>
				<label for="muut_channel_path-<?php echo $channel_block_id; ?>"><?php _e( 'Muut Path', 'muut' ); ?></label><br/>
				<input name="muut_channel_path" placeholder="<?php printf( __( '%sdefault%s', 'muut' ), '/(', ')' ); ?>" class="muut_channel_path_input" type="text" id="muut_channel_path-<?php echo $channel_block_id; ?>" value="<?php echo $channel_block_path; ?>" />
				<label class="screen-reader-text" for="muut_channel_path-<?php echo $channel_block_id; ?>"><?php _e( 'Muut Path', 'muut' ); ?></label>
			</p>
			<input type="button" class="button button-secondary muut-advanced-channel-settings-save" value="Save" />
		</div>
	</div>
</li>