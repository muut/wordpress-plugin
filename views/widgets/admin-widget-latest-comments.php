<?php
/**
 * The markup for the admin form of the Latest Comments widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within an instance of the Muut_Widget_Latest_Comments class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 */

$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

$number_of_comments = isset( $instance['number_of_comments'] ) ? $instance['number_of_comments'] : '5';
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Display Title:', 'muut' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'number_of_comments' ); ?>"><?php _e( 'Number of comments to show:', 'muut' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'number_of_comments' ); ?>" name="<?php echo $this->get_field_name( 'number_of_comments' ); ?>" size="3" type="text" value="<?php echo $number_of_comments; ?>" />
</p>