<?php
/**
 * Display the text that a given widget can only be active if Webhooks are activated.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

/**
 * This file assumes that we are within an instance of one of the Muut widgets (descendants of WP_Widget).
 * Knowing that, `$this` represents that widget instance.
 */

?>
<p class="muut_widget_error">
<?php printf( __( 'The %s requires that webhooks be enabled. Webhooks can be enabled at the %splugin settings%s page and set up in the Muut settings for your forum. Note that webhooks only can be set up on forums with a developer subscription.', 'muut' ), $this->name, '<a href="' . admin_url( 'admin.php?page=muut' ) . '">', '</a>' ); ?>
</p>
