<?php
/**
 * The Forum Category static class that contains all the static methods required to interact with a Forum Category that
 * are Muut-related. The Forum Category registers a new post type that is responsible for the Forum Categories.
 * Note that it is mostly for organizational / storage purposes, rather than using the admin UX or frontend
 * display methods. While technically being a WP_Post, they will really act like a cross between posts and
 * categories.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Forum_Category_Utility' ) ) {

	/**
	 * Muut Forum Category Utility class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Forum_Category_Utility
	{

		/**
		 * The name for the custom post type for Forum Categories.
		 */
		const FORUMCATEGORY_POSTTYPE = 'muut_forum_category';

		/**
		 * The name for the forum category header taxonomy.
		 */
		const FORUMCATEGORYHEADER_TAXONOMY = 'muut_forum_category_header';

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Forum_Category_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function __construct() {}

		/**
		 * Register the Forum Category custom post type (CPT).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function registerPostType() {
			$post_type_labels = array(
				'name' => _x( 'Forum Categories', 'post type general name', 'muut' ),
				'singular_name' => _x( 'Forum Category', 'post type singular name', 'muut' ),
				'menu_name' => _x( 'Categories', 'admin menu', 'muut' ),
				'name_admin_bar' => _x( 'Category', 'add new on admin bar', 'muut' ),
				'add_new' => _x( 'Add New', 'book', 'muut' ),
				'add_new_item' => __( 'Add New Category', 'muut' ),
				'new_item' => __( 'New Forum Category', 'muut' ),
				'edit_item' => __( 'Edit Forum Category', 'muut' ),
				'view_item' => __( 'View Category', 'muut' ),
				'all_items' => __( 'All Forum Categories', 'muut' ),
				'search_items' => __( 'Search Forum Categories', 'muut' ),
				'parent_item_colon' => __( 'Parent Categories:', 'muut' ),
				'not_found' => __( 'No forum categories found.', 'muut' ),
				'not_found_in_trash' => __( 'No forum categories found in Trash.', 'muut' ),
			);

			$post_type_args = array(
				'labels' => $post_type_labels,
				'public' => false,
				'capability_type' => 'page',
				'hierarchical' => true,
				'taxonomies' => array(
					'muut_forum_category_header',
				),
			);

			register_post_type( self::FORUMCATEGORY_POSTTYPE, $post_type_args );


			$taxonomy_labels = array(
				'name' => _x( 'Forum Category Headers', 'taxonomy general name', 'muut' ),
				'singular_name' => _x( 'Forum Category Header', 'taxonomy singular name', 'muut' ),
				'search_items' => __( 'Search Category Headers', 'muut' ),
				'all_items' => __( 'All Category Headers', 'muut'),
				'parent_item' => __( 'Parent Category Header', 'muut' ),
				'parent_item_colon' => __( 'Parent Category Header:', 'muut' ),
				'edit_item' => __( 'Edit Category Header', 'muut' ),
				'update_item' => __( 'Update Category Header', 'muut' ),
				'add_new_item' => __( 'Add New Category Header', 'muut' ),
				'new_item_name' => __( 'New Category Header Name', 'muut' ),
				'menu_name' => __( 'Category Header', 'muut' ),
			);

			$taxonomy_args = array(
				'labels' => $taxonomy_labels,
				'hierarchical' => false,
				'public' => false,
			);

			register_taxonomy( self::FORUMCATEGORYHEADER_TAXONOMY, self::FORUMCATEGORY_POSTTYPE, $taxonomy_args );
		}

		/**
		 * Gets a nested array of category header IDs as keys containing the category posts as a sub-array for each
		 * header id.
		 *
		 * @return array The array of category headers and their categories.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getForumCategoryHeaders() {
			$args = array(
				'hide_empty' => false,
			);
			$all_category_headers = get_terms( self::FORUMCATEGORYHEADER_TAXONOMY, $args );

			$category_headers_keys = array();
			foreach ( $all_category_headers as $header ) {
				// Need to append 'id-' to keys to make sure they are interpreted as strings for re-ordering.
				$category_headers_keys[$header->slug] = $header;
			}

			$order_of_headers = muut()->getOption( 'muut_category_headers', array() );

			$category_headers_sorted = array_merge( array_flip( $order_of_headers ), $category_headers_keys );

			$category_headers = array();

			foreach( $category_headers_sorted as $header ) {
				$args = array(
					'posts_per_page' => '-1',
					self::FORUMCATEGORYHEADER_TAXONOMY => $header->slug,
					'orderby' => 'menu_order',
					'order' => 'asc',
					'post_type' => self::FORUMCATEGORY_POSTTYPE,
				);
				$category_posts = get_posts( $args );

				$category_headers[$header->term_id] = $category_posts;
			}

			return $category_headers;
		}
	}
}