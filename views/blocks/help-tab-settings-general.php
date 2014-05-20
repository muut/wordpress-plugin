<?php
/**
 * The content for the Muut settings contextual General help tab.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */
?>
<ul>
	<li>
		<p><strong><?php _e( 'Forum Name', 'muut' ); ?></strong>
			<?php printf( __( 'This is the forum name you registered with Muut.%sForum name:%smyforumname%s', 'muut' ), '<strong>', '</strong> muut.com/<strong><em>', '</em></strong>' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Language', 'muut' ); ?></strong>
			<?php printf( __( 'This is the language for all the Muut embeds. The plugin itself uses the language set for your entire WordPress website. Not seeing your language? Find out how to provide it yourself %shere!%s', 'muut' ), '<a href="https://github.com/moot/language">', '</a>' ); ?></p>
	</li>
	<li>
		<p><strong><?php _e( 'Allow search engines to crawl discussions at', 'muut' ); ?> -</strong>
			<?php _e( 'This setting should generally not be touched. When checked, it allows search engines to index your discussions under your domain as well.', 'muut' ); ?></p>
	</li>
</ul>
