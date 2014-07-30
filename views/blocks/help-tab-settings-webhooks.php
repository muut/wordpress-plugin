<?php
/**
 * The content for the Muut settings contextual Webhooks help tab.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<p>
	<?php _e( 'Muut Webhooks are HTTP callbacks triggered by Muut events. They communicate with your WordPress website to send event notifications on Muut actions (replies, likes, etc.), that then add support for advanced features and widgets.', 'muut' ); ?>
</p>
<p>
	<?php printf( __( 'To use webhooks, you need to %supgrade%s your forum to a developer license.', 'muut' ), '<a class="muut_upgrade_to_developer_link" href="#">', '</a>' ); ?>
</p>
<p>
	<?php _e( 'Once you have enabled webhooks here, you need to visit your Muut forum settings and create a new integration under the Integrations section; give the integration a name, enter the URL and Secret specified on this page under "Webhooks" section and make sure that all the events are enabled.', 'muut' ); ?>
</p>