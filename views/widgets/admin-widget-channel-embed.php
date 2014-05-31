<?php
/**
 * The markup for the admin form of the Channel Embed widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within an instance of the Muut_Widget_Channel_Embed class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 */

$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

$hide_online = isset( $instance['hide_online'] ) ? $instance['hide_online'] : '0';
$disable_uploads = isset( $instance['disable_uploads'] ) ? $instance['disable_uploads'] : '0';

$muut_path = !empty( $instance['muut_path'] ) ? '/' . esc_attr( $instance['muut_path'] ) : '';

?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Display Title:', 'muut' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<span class="checkbox_row"><input type="checkbox" id="<?php echo $this->get_field_id( 'hide_online' ); ?>" name="<?php echo $this->get_field_name( 'hide_online' ); ?>" value="1" <?php checked( $hide_online, '1' ); ?> /><label for="<?php echo $this->get_field_id( 'hide_online' ); ?>"><?php _e( 'Hide online users', 'muut' ); ?></label></span>
	<span class="checkbox_row"><input type="checkbox" id="<?php echo $this->get_field_id( 'disable_uploads' ); ?>" name="<?php echo $this->get_field_name( 'disable_uploads' ); ?>" value="1" <?php checked( $disable_uploads, '1' ); ?> /><label for="<?php echo $this->get_field_id( 'disable_uploads' ); ?>"><?php _e( 'Disable image uploads', 'muut' ); ?></label></span>
</p>
<?php if ( apply_filters( 'muut_show_channel_widget_channel_path_field', false ) ) { ?>
<p>
	<label for="<?php echo $this->get_field_id( 'muut_path' ); ?>"><?php _e( 'Remote Path:', 'muut' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'muut_path' ); ?>" name="<?php echo $this->get_field_name( 'muut_path' ); ?>" placeholder="<?php printf( __( '%sdefault%s', 'muut' ), '/(', ')' ); ?>" type="text" value="<?php echo $muut_path; ?>" />
</p>
<?php } ?>