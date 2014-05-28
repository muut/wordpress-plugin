<?php
/**
 * The content for the Muut settings contextual SEO help tab.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<ul>
	<li>
		<p><strong><?php _e( 'Allow search engines to crawl discussions at', 'muut' ); ?> -</strong>
			<?php _e( 'This setting should generally not be touched. When checked, it allows search engines to index your discussions under your domain as well.', 'muut' ); ?></p>
	</li>

	<li>
		<p><strong><?php _e( 'Serve from your own S3 Bucket', 'muut' ); ?> -</strong>
			<?php printf( __( 'If you have a developer subscription and have set up a custom S3 Bucket for your Muut forum, you can serve content directly from that bucket rather than from %s. Once enabled, the bucket name you enter must be the same S3 bucket registered for the forum in the Muut settings.', 'muut' ), Muut::MUUTSERVERS ); ?></p>
	</li>
</ul>
