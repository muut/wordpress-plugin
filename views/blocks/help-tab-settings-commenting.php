<?php
/**
 * The content for the Muut settings contextual Commenting help tab.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<ul>
	<li>
		<p><strong><?php _e( 'Use Muut for post commenting', 'muut' ); ?></strong>
			<?php _e( 'Replaces default Wordpress commenting.', 'muut' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Use Muut commenting on posts with existing comments', 'muut' ); ?></strong>
			<?php printf( __( 'Posts that already have comments using the default WordPress discussion system will not default to using Muut. This can be changed on a post-by-post bases using the %sCommenting%s tab in the Muut metabox for that post.', 'muut' ), '<em>', '</em>' ); ?></p>
	</li>
</ul>