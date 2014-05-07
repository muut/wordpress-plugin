<?php
/**
 * The list item block used for the custom navigation administration screen for channel headers.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

if ( !isset( $header_block_id ) || ( !is_string( $header_block_id ) && !is_numeric( $header_block_id ) ) ) {
	$header_block_id = 'new';
}
if ( !isset( $header_block_title ) || !is_string( $header_block_title ) ) {
	$header_block_title = '';
}
if ( !isset( $header_block_posts ) || !is_array( $header_block_posts ) ) {
	$header_block_posts = array();
}
?>

<li class="muut_forum_header_item" id="channel_header-<?php echo $header_block_id; ?>" data-id="<?php echo $header_block_id; ?>">
	<div class="muut-channel-header-actions">
			<label class="screen-reader-text" for="header-name-<?php echo $header_block_id; ?>"><?php _e( 'Header Text', 'muut' ); ?></label>
			<a href="#" class="muut-header-title x-editable" id="header-name-<?php echo $header_block_id; ?>" data-type="text" data-url="#" data-value="<?php echo $header_block_title; ?>"></a>
			<input type="button" class="button button-secondary new_channel_for_header" id="new_channel_in_<?php echo $header_block_id; ?>" title="<?php _e( 'New Channel', 'muut' ); ?>" value="<?php _e( 'New Channel', 'muut' ); ?>" />
			<span class="delete-link"><a href="#"><?php _e( 'Delete', 'muut' ); ?></a></span>
	</div>
	<div class="muut-channel-header-content">
		<ul id="muut_forum_nav_channel-<?php echo $header_block_id; ?>" class="muut_channel_list muut_channel_lists_connected">
			<?php foreach ( $header_block_posts as $channel_post ) {
				Muut_Admin_Custom_Navigation::instance()->forumChannelItem( $channel_post->ID );
			}
			?>
		</ul>
	</div>
</li>