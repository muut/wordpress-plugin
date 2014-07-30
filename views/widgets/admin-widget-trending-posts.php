<?php
/**
 * The markup for the admin form of the Trending Posts widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within an instance of the Muut_Widget_Trending_Posts class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 */

$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

// If widget has just been created, select all the channels in advance.
$potential_channels = $this->getCurrentChannelsOption();
if ( !isset( $instance['number_of_posts'] ) ) {
	$current_channels = $potential_channels;
} else {
	$current_channels = isset( $instance['channels'] ) ? $instance['channels'] : array();
}

// Add default values for number of posts.
$number_of_posts = isset( $instance['number_of_posts'] ) ? $instance['number_of_posts'] : '5';

// Check if there are "current channels" not in the potential channels array, in which case add them to it (for display).
foreach( $current_channels as $channel_path => $channel_name ) {
	if ( !in_array( $channel_path, array_keys( $potential_channels ) ) ) {
		$potential_channels[$channel_path] = $channel_name;
	}
}
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Display Title:', 'muut' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'number_of_posts' ); ?>"><?php _e( 'Number of posts to show:', 'muut' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'number_of_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>" size="3" type="text" value="<?php echo $number_of_posts; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'channels' ); ?>"><?php _e( 'Channels:', 'muut' ); ?></label>
	<?php
	$i = 0;
	foreach( $potential_channels as $channel_path => $channel_name ) { ?>
	<span class="checkbox_row">
		<input type="checkbox" id="<?php echo $this->get_field_id( 'channels' ) . '_' . $i; ?>" name="<?php echo $this->get_field_name( 'channels' ); ?>[]" value="<?php echo $channel_path; ?>" <?php checked( in_array( $channel_path, array_keys( $current_channels ) ) ); ?> />
		<label for="<?php echo $this->get_field_id( 'channels' ) . '_' . $i; ?>"><?php echo $channel_name; ?></label>
	</span>
	<?php $i++; } ?>
</p>