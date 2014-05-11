<?php
/**
 * The markup for the Muut page editor metabox.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

$is_forum = Muut_Forum_Page_Utility::isForumPage( get_the_ID() );

$forum_show_class = '';
if ( $is_forum != '1' ) {
	$forum_show_class = 'hidden';
}

$remote_path = '/' . rawurldecode( Muut_Forum_Page_Utility::getRemoteForumPath( get_the_ID(), true ) );
?>
<div id="muut_metabox_tabs">
	<div class="wp-tab-bar">
		<ul id="category-tabs" class="category-tabs">
			<li class="tabs"><a href="#muut_comments"><?php _e( 'Commenting', 'muut' ); ?></a></li>
			<li><a href="#muut_channel"><?php _e( 'Channel', 'muut' ); ?></a></li>
			<li><a href="#muut_forum"><?php _e( 'Forum', 'muut' ); ?></a></li>
		</ul>
		<div id="muut_comments" class="wp-tab-panel muut-tab-panel">
			<p>This is the muut comments tab.<br/>Here is more text.</p>
		</div>
		<div id="muut_channel" class="hidden wp-tab-panel muut-tab-panel">
			<p>This is the muut channel tab.</p>
		</div>
		<div id="muut_forum" class="hidden wp-tab-panel muut-tab-panel">
			<p>This is the muut forum tab.</p>
		</div>
	</div>
</div>