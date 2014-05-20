<?php
/**
 * The content for the post editor contextual help tab explaining the Channel section of the Muut meta box.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<p>
	<?php _e( 'By using this tab, you will embed the full Muut forum using the rich user interface, which also allows a truly customizable look and feel using CSS.', 'muut' ); ?>
</p>
<ul>
	<li>
		<p><strong><?php _e( 'Enable forum', 'muut' ); ?></strong>
			<?php _e( 'Check this to enable the embed for the full Muut forum. It includes Muut\'s rich interface that is completely customizable with CSS.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Hide online users', 'muut' ); ?></strong>
			<?php _e( 'Hides the "Online users" section. Online status (green dot) is still shown in the avatars for users who didn\'t hide it.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Disable image uploads', 'muut' ); ?></strong>
			<?php _e( 'Linking to external images is still possible when uploads are disabled. Images are always displayed in gallery view.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Show Comments', 'muut' ); ?></strong>
			<?php printf( __( 'Shows a channel called %sComments%s at the bottom of the forum navigation that contains every active commenting section from posts and pages on the website. Comment threads that a user participated in or watched will always show up in her personal "My feed" regardless of this setting.', 'muut' ), '<em>', '</em>' ); ?></p>
	</li>
</ul>