<?php
/**
 * The Custom Navigation for the new Muut admin section.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

$channel_headers = Muut_Forum_Channel_Utility::getForumChannelHeaders();
?>
<div class="wrap">
	<h2><?php _e( 'Custom Navigation', 'muut' ); ?></h2>
	<form id="muut_custom_navigation_form" method="POST" action="#">
	<?php wp_nonce_field( 'muut_save_custom_navigation', 'muut_custom_nav_nonce' ); ?>
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap muut_admin_wrapper">
				<h3><?php _e( 'What is this?', 'muut' ); ?></h3>
				<p><?php _e( 'You can control the customized navigation on this page. This navigation controls the channels that are listed on the navigation for a fully embeded Muut forum. Make sure one of your pages is specified as the Forum Home, and the navigation that is customized on this page will automatically be used there, instead of the default Muut channels.', 'muut' ); ?></p>
				<p><?php _e( 'Note that you can re-order the channels and headers by dragging and dropping, and individual channels can be dragged from one header to the next, if you want to adjust that.', 'muut' ); ?></p>
				<p><?php _e( 'In future releases, this right column will contain deactivated/past channels that can be re-added to the headers on the left, and the help paragraph will be for the Help dropdown at the top right.', 'muut' ); ?></p>
			</div>
		</div>
		<div id="col-left">
			<div class="col-wrap">
				<h3><?php _e( 'Navigation', 'muut' ); ?></h3>
				<input type="button" class="button button-secondary" id="muut_add_channel_header" value="<?php _e( 'Insert New Header', 'muut' ); ?>" />
				<input type="hidden" name="muut_customized_navigation_array" id="muut_customized_navigation_array_field" value="" />
				<ul id="muut_forum_nav_headers">
					<?php
					foreach ( $channel_headers as $id => $header ) {
						Muut_Admin_Custom_Navigation::instance()->forumChannelHeaderItem( $id, $header, true );
					}
					?>
				</ul>
			</div>
		</div>
	</div>
	<input type="submit" class="button button-primary" value="<?php _e( 'Save Navigation' ); ?>" />
	</form>
</div><!-- /wrap -->