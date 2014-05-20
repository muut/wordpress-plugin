<?php
/**
 * The content for the post editor contextual help tab explaining the Channel section of the Muut meta box.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<p>
	<?php _e( 'This tab is used to embed a single channel at this location. It acts like Channels on the main forum, but the forum\'s navigation is not visible. It will generate the channel path and name by default, based on the page slug, or you can enter a custom channel path if you want to embed an existing one, for example.', 'muut' ); ?>
</p>
<ul>
	<li>
		<p><strong><?php printf( __( 'Enable channel%s (a standalone discussion area)', 'muut' ), '</strong>' ); ?>
			<?php _e( 'Check this to enable a standalone Muut channel embed for the page. This will deactivate the other two tabs.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Hide online users', 'muut' ); ?></strong>
			<?php _e( 'Hides the "Online users" section. Online status (green dot) is still shown in the avatars for users who didn\'t hide it.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Disable image uploads', 'muut' ); ?></strong>
			<?php _e( 'Linking to external images is still possible when uploads are disabled. Images are always displayed in gallery view.', 'muut' ); ?></p>
	</li>
</ul>