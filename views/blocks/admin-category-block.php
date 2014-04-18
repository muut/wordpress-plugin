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
?>
<li class="muut_forum_category_item" id="category_item_<?php echo $category_block_id; ?>">
	<div class="muut_category_item_content">
		<label class="screen-reader-text" for="category-name-<?php echo $category_block_id; ?>"><?php _e( 'Category Name', 'muut' ); ?></label>
		<a href="#" class="x-editable" id="category-name-<?php echo $category_block_id; ?>" data-type="text" data-url="#" data-value="<?php echo $category_block_title; ?>"></a>
	</div>
</li>