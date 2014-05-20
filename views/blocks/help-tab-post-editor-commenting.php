<?php
/**
 * The content for the post editor contextual help tab explaining the Commenting section of the Muut meta box.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<p>
	<?php _e( 'This tab can be used to set up Muut commenting on the page (or post). It is the only tab that is visible on all post types (not just pages).', 'muut' ); ?>
</p>
<ul>
	<li>
		<p><strong><?php _e( 'Enable Muut commenting', 'muut' ); ?></strong>
			<?php _e( 'Check this to enable Muut commenting for the page or post. If on a page, it will deactivate the other two tabs.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php printf( __( 'Flat%s commenting', 'muut' ), '</strong>' ); ?>
			<?php _e( 'A commenting section where all the posts appear in a single thread and will be listed in chronological order from top to bottom.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php printf( __( 'Threaded%s commenting', 'muut' ), '</strong>' ); ?>
			<?php _e( 'A commenting section where top level comments can have one level of replies. The threads with the newest replies appear on top.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Disable image uploads', 'muut' ); ?></strong>
			<?php _e( 'Linking to external images is still possible when uploads are disabled. Images are always displayed in gallery view.', 'muut' ); ?></p>
	</li>
</ul>