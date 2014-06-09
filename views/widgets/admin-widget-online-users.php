<?php
/**
 * The markup for the admin form of the Online Users widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within an instance of the Muut_Widget_Online_Users class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 */

$show_anonymous = !isset( $instance['show_anonymous'] ) ? '1' : $instance['show_anonymous'];
$show_number_online = !isset( $instance['show_number_online'] ) ? '1' : $instance['show_number_online'];
?>
<p>
	<span class="checkbox_row"><input type="checkbox" id="<?php echo $this->get_field_id( 'show_number_online' ); ?>" name="<?php echo $this->get_field_name( 'show_number_online' ); ?>" value="1" <?php checked( $show_number_online, '1' ); ?> /><label for="<?php echo $this->get_field_id( 'show_number_online' ); ?>"><?php _e( 'Show number of logged in users', 'muut' ); ?></label></span>
</p>
<p>
	<span class="checkbox_row"><input type="checkbox" id="<?php echo $this->get_field_id( 'show_anonymous' ); ?>" name="<?php echo $this->get_field_name( 'show_anonymous' ); ?>" value="1" <?php checked( $show_anonymous, '1' ); ?> /><label for="<?php echo $this->get_field_id( 'show_anonymous' ); ?>"><?php _e( 'Show anonymous user count', 'muut' ); ?></label></span>
</p>
