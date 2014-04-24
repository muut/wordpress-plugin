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
		<label class="screen-reader-text" for="category-name-<?php echo $category_block_id; ?>"><?php _e( 'Category Name', 'muut' ); ?></label>
		<a href="#" class="muut-category-title x-editable" id="category-name-<?php echo $category_block_id; ?>" data-type="text" data-url="#" data-value="<?php echo $category_block_title; ?>"></a>
		<span class="muut_category_item_setting"><input class="muut_show_in_allposts_check" type="checkbox" <?php checked( true, $show_in_allposts ); ?> /><?php _e( 'Feature in All Posts', 'muut' ); ?></span>
		<span class="delete-link"><a href="#"><?php _e( 'Delete', 'muut' ); ?></a></span>
	</div>
</li>