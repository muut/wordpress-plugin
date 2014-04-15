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
			$labels = array(
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

			$args = array(
				'labels' => $labels,
				'public' => false,
				'capability_type' => 'page',
				'hierarchical' => true,
			);

			register_post_type( self::FORUMCATEGORY_POSTTYPE, $args );
		}
	}
}