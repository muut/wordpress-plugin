<?php
/**
 * The Custom Navigation for the new Muut admin section.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

$category_headers = Muut_Forum_Category_Utility::getForumCategoryHeaders();
?>
<div class="wrap">
	<h2><?php _e( 'Custom Navigation', 'muut' ); ?></h2>
	<h3 style="color: red">NON-FUNCTIONAL</h3>
	<form id="muut_custom_navigation_form" method="POST" action="#">
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
				<h3><?php _e( 'Hidden Categories', 'muut' ); ?></h3>
			</div>
		</div>
		<div id="col-left">
			<div class="col-wrap">
				<h3><?php _e( 'Navigation', 'muut' ); ?></h3>
				<input type="button" class="button button-secondary" id="muut_add_category_header" value="<?php _e( 'Insert New Header', 'muut' ); ?>" />
				<input type="hidden" name="muut_customized_navigation_array" id="muut_customized_navigation_array_field" value="" />
				<ul id="muut_forum_nav_headers">
					<?php
					foreach ( $category_headers as $id => $header ) {
						Muut_Admin_Custom_Navigation::instance()->forumCategoryHeaderItem( $id, $header, true );
					}
					?>
				</ul>
			</div>
		</div>
	</div>
	<input type="submit" class="button button-primary" value="<?php _e( 'Save Navigation' ); ?>" />
	</form>
</div><!-- /wrap -->