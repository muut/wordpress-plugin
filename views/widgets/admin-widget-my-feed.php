<?php
/**
 * The markup for the admin form of the My Feed widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within an instance of the Muut_Widget_My_Feed class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 */

$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

$disable_uploads = isset( $instance['disable_uploads'] ) ? $instance['disable_uploads'] : '0';

?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Display Title:', 'muut' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<span class="checkbox_row"><input type="checkbox" id="<?php echo $this->get_field_id( 'disable_uploads' ); ?>" name="<?php echo $this->get_field_name( 'disable_uploads' ); ?>" value="1" <?php checked( $disable_uploads, '1' ); ?> /><label for="<?php echo $this->get_field_id( 'disable_uploads' ); ?>"><?php _e( 'Disable image uploads', 'muut' ); ?></label></span>
</p>