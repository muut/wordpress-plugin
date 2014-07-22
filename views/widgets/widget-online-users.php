<?php
/**
 * The markup for the frontend of the Online Users widget
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within the widget() method of the Muut_Widget_Online_Users class (which extends WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 *
 */


?>
<div id="muut-widget-online-users-wrapper" class="muut_widget_wrapper muut_widget_online_users_wrapper">
	<div class="m-users">
		<div class="m-logged-users"></div>
		<?php if ( isset( $instance['show_anonymous'] ) && $instance['show_anonymous'] ) { ?>
		<div class="m-anon-count">+<em></em> <?php _e( 'anonymous users', 'muut' ); ?></div>
		<?php } ?>
	</div>
</div>
