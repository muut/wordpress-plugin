<?php
/**
 * The list item block used for the custom navigation administration screen for forum categories.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

if ( !isset( $category_block_id ) || ( !is_string( $category_block_id ) && !is_numeric( $category_block_id ) ) ) {
	$category_block_id = 'new';
}
if ( !isset( $category_block_path ) || !is_string( $category_block_path ) ) {
	$category_block_path = '';
}
if ( $category_block_path != '' ) {
	$category_block_path = '/' . $category_block_path;
}
if ( !isset( $category_block_title ) || !is_string( $category_block_title ) ) {
	$category_block_title = '';
}

$defaults = muut()->getOption( 'forum_category_defaults', array() );
if ( isset( $defaults['show_in_allposts'] ) ) {
	$show_in_allposts = $defaults['show_in_allposts'];
} else {
	$show_in_allposts = false;
}

$show_in_allposts = is_numeric( $category_block_id ) ? get_post_meta( $category_block_id, 'muut_show_in_allposts', true ) : $show_in_allposts;
?>
<li class="muut_forum_category_item" id="category_item-<?php echo $category_block_id; ?>" data-id="<?php echo $category_block_id; ?>">
	<div class="muut_category_item_content">
		<div class="muut_category_item_basic">
			<label class="screen-reader-text" for="category-name-<?php echo $category_block_id; ?>"><?php _e( 'Category Name', 'muut' ); ?></label>
			<a href="#" class="muut-category-title x-editable" id="category-name-<?php echo $category_block_id; ?>" data-type="text" data-url="#" data-value="<?php echo $category_block_title; ?>"></a>
			<span class="delete-link"><a href="#"><?php _e( 'Delete', 'muut' ); ?></a></span>
			<span class="action-link advanced-options"><a href="#"><?php _e( 'Advanced', 'muut' ); ?></a></span>
		</div>
		<div class="inline-edit-col muut_category_advanced_options">
			<h4><?php _e( 'Advanced Options', 'muut' ); ?></h4>
			<p>
				<input class="muut_show_in_allposts_check" id="muut_show_in_allposts_check-<?php echo $category_block_id; ?>" type="checkbox" <?php checked( true, $show_in_allposts ); ?> />
				<label for="muut_show_in_allposts_check-<?php echo $category_block_id; ?>"><?php _e( 'Feature in All Posts', 'muut' ); ?></label>
				<label class="screen-reader-text" for="muut_show_in_allposts_check-<?php echo $category_block_id; ?>"><?php _e( 'Feature in All Posts', 'muut' ); ?></label>
			</p>
			<p>
				<label for="muut_category_path-<?php echo $category_block_id; ?>"><?php _e( 'Muut Path', 'muut' ); ?></label><br/>
				<input name="muut_category_path" placeholder="<?php printf( __( '%sdefault%s', 'muut' ), '/(', ')' ); ?>" class="muut_category_path_input" type="text" id="muut_category_path-<?php echo $category_block_id; ?>" value="<?php echo $category_block_path; ?>" />
				<label class="screen-reader-text" for="muut_category_path-<?php echo $category_block_id; ?>"><?php _e( 'Muut Path', 'muut' ); ?></label>
			</p>
			<input type="button" class="button button-secondary muut-advanced-category-settings-save" value="Save" />
		</div>
	</div>
</li>