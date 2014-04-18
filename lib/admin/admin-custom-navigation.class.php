<?php
/**
 * The class that is responsible for all the admin functionality of the custom navigation.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Admin_Custom_Navigation' ) ) {

	/**
	 * Muut Admin Custom Navigation class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Admin_Custom_Navigation
	{
		/**
		 * @static
		 * @property Muut_Admin_Custom_Navigation The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Admin_Custom_Navigation The instance.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public static function instance() {
			if ( !is_a( self::$instance, __CLASS__ ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * The class constructor.
		 *
		 * @return Muut_Admin_Custom_Navigation
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions regarding the custom navigation admin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addActions() {
			add_action( 'admin_head', array( $this, 'printCustomNavTemplatesJs' ) );
		}

		/**
		 * The method for adding all filters regarding the custom navigation admin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addFilters() {

		}


		/**
		 * Render an admin forum category header list item for the custom navigation editor.
		 *
		 * @param int $header_id The term ID for the category header.
		 * @param array $posts An array of posts to display within this category (optional).
		 * @param bool $echo Whether to output the markup or simply return it (false).
		 * @return string|false|void Returns the markup or void if it is echoed.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function forumCategoryHeaderItem( $header_id, $posts = null, $echo = true ) {
			if ( !is_numeric( $header_id ) ) {
				return false;
			}

			$term = get_term( $header_id, Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY );

			if ( !is_array( $posts ) ) {
				$args = array(
					'posts_per_page' => '-1',
					'post_type' => Muut_Forum_Category_Utility::FORUMCATEGORY_POSTTYPE,
					'orderby' => 'menu_order',
					'order' => 'ASC',
					Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY => $term->slug,
				);
				$posts = get_posts( $args );
			}

			$header_block_id = $header_id;
			$header_block_title = $term->name;
			$header_block_categories = $posts;

			ob_start();
			include ( muut()->getPluginPath() . 'views/blocks/admin-category-header-block.php' );

			$html = ob_get_clean();

			ob_end_clean();

			if ( $echo ) {
				echo $html;
			} else {
				return $html;
			}
		}

		/**
		 * Render an admin forum category list item for the custom navigation editor.
		 *
		 * @param int $category_id The post ID for the category.
		 * @param bool $echo Whether to output the markup or simply return it (false).
		 * @return string|false|void Returns the markup or void if it is echoed. False on error.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function forumCategoryItem( $category_id, $echo = true ) {
			if ( !is_numeric( $category_id ) ) {
				return false;
			}

			$category_post = get_post( $category_id );

			$category_block_id = $category_id;
			$category_block_title = $category_post->post_title;

			ob_start();
			include ( muut()->getPluginPath() . 'views/blocks/admin-category-block.php' );

			$html = ob_get_clean();

			ob_end_clean();

			if ( $echo ) {
				echo $html;
			} else {
				return $html;
			}
		}

		/**
		 * Saves an array of term IDs that represent the order that the category headers should be listed/retrieved.
		 *
		 * @param array $header_ids An array of term IDs representing the headers.
		 * @return bool Whether the save was successful or not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function setForumCategoryHeaderOrder( $header_ids = array() ) {
			if ( !is_array( $header_ids ) ) {
				return false;
			}

			$header_array = apply_filters( 'muut_set_category_headers', $header_ids );

			foreach ( $header_array as &$header ) {
				$header = 'id-' . $header;
			}

			return muut()->setOption( 'muut_category_headers', $header_array );
		}

		/**
		 * Echoes for js the markup for a dynamically generated header block.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function printCustomNavTemplatesJs() {
			$header_block_id = $category_block_id = '%ID%';
			$header_block_title = $category_block_id = '%TITLE%';
			$html = '<script type="text/javascript"> var categoryHeaderBlockTemplate = ';
			ob_start();
			include( muut()->getPluginPath() . 'views/blocks/admin-category-header-block.php' );
			$html .= json_encode( ob_get_clean() );
			$html .= '; ';

			$html .= 'var categoryBlockTemplate = ';
			ob_start();
			include( muut()->getPluginPath() . 'views/blocks/admin-category-block.php' );
			$html .= json_encode( ob_get_clean() );
			$html .= ';';
			$html .= '</script>';

			echo $html;
		}
	}
}