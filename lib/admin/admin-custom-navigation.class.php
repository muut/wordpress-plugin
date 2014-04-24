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
			add_action( 'admin_head', array( $this, 'saveCustomNavigation' ) );
			add_action( 'admin_notices', array( $this, 'adminNotices' ) );
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
		 * Once navigation is saved, make sure to display the proper admin notice.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function adminNotices() {
			if ( did_action( 'muut_custom_nav_saved' ) > 0 ) {
				echo '<div class="updated">';
				echo '<p>' . __( 'Custom navigation successfully saved.', 'muut' ) . '</p>';
				echo '</div>';
			}

			if ( muut()->getOption( 'forum_home_id', 'false' ) < 1 ) {
				echo '<div class="error">';
				echo '<p>' . __( 'You need to set up a page as the Forum Home before custom navigation will have an effect.', 'muut' ) . '</p>';
				echo '</div>';
			}
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

			if ( !is_array( $posts ) || empty( $posts ) || is_null( $posts ) ) {
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
			$header_block_posts = $posts;

			ob_start();
			include ( muut()->getPluginPath() . 'views/blocks/admin-category-header-block.php' );

			$html = ob_get_clean();

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
			$header_block_title = $category_block_title = '';
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

		/**
		 * Saves the custom navigation settings.
		 * The JSON that has been passed is of the following format:
		 * [{ "id":<header_id>, "name":"Header Name", "categories":[{
		 *   "id":"<category_post_id>",
		 *   "name":"Category Name",
		 *   "args":{
		 *     "show_in_allposts":true
		 *   }}]
		 * }]
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function saveCustomNavigation() {
			if ( isset( $_POST['muut_customized_navigation_array'] ) && check_admin_referer( 'muut_save_custom_navigation', 'muut_custom_nav_nonce' ) ) {
				$save_data = json_decode( stripslashes( $_POST['muut_customized_navigation_array'] ) );

				$custom_navigation_header_id_order = array();

				$current_header_order = muut()->getOption( 'muut_category_headers', array() );

				// Save the data.
				foreach ( $save_data as $header_object ) {
					if ( isset( $header_object->id ) && is_string( $header_object->id ) && !is_numeric( $header_object->id ) && substr( $header_object->id, 0, 3 ) == 'new' && isset( $header_object->name ) ) {
						$header_id = Muut_Forum_Category_Utility::createNavigationHeader( $header_object->name );
					} elseif ( isset( $header_object->id ) && is_numeric( $header_object->id ) ) {
						$header_id = $header_object->id;
						if ( isset( $header_object->name ) ) {
							Muut_Forum_Category_Utility::updateNavigationHeader( $header_id, array( 'name' => $header_object->name ) );
						}
					}

					// If we've got a header id, let's attach it to the header order option array and  make sure to
					// evaluate the categories underneath.
					if ( isset( $header_id ) ) {
						$current_order_index = array_search( $header_id, $current_header_order );
						if ( $current_header_order !== false ) {
							unset( $current_header_order[$current_order_index]);
						}

						$header_term = get_term( $header_id, Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY );
						$custom_navigation_header_id_order[] = $header_id;

						// It is important to remember that categories in this context are ACTUALLY WP_Post objects
						// of the custom post type Muut_Forum_Category_Utility::FORUMCATEGORY_POSTTYPE.
						// (At time of writing, that is: 'muut_forum_category')
						if ( isset( $header_object->categories ) && is_array( $header_object->categories ) ) {

							$current_categories = get_posts( array(
								 'post_type' => Muut_Forum_Category_Utility::FORUMCATEGORY_POSTTYPE,
								 'tax_query' => array(
									 'taxonomy' => Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY,
									 'field' => 'term_id',
									 'terms' => $header_id,
								 ),
								'posts_per_page' => '-1',
							 ));

							$keep_categories_array = array();

							$menu_order = 0;
							foreach ( $header_object->categories as $category ) {

								// Set the base post args.
								$post_args = array(
									'tax_input' => array(
										Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY => $header_term->slug,
									),
									'menu_order' => $menu_order,
									'post_title' => $category->name,
								);

								// Create a new category post if it does not exist yet.
								if ( isset( $category->id ) && is_string( $category->id ) && substr( $category->id, 0, 3 ) == 'new' && isset( $category->name ) ) {

									$custom_args = isset( $category->args ) ? $category->args : array();

									$possible_post = get_posts( array(
										'name' => sanitize_title( $category->name ),
										'post_type' => Muut_Forum_Category_Utility::FORUMCATEGORY_POSTTYPE,
										'posts_per_page' => '1',
									));

									if ( count( $possible_post ) > 0 ) {
										$category_post_id = $possible_post[0]->ID;
										wp_set_post_terms( $category_post_id, $header_term->slug, Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY, false );
									} else {
										$category_post_id = Muut_Forum_Category_Utility::createForumCategory( $category->name, $custom_args, $post_args );
									}

									$keep_categories_array[] = $category_post_id;

									if ( is_int( $category_post_id ) ) {
										$category_id = $category_post_id;
										$menu_order++;
									}

								// If it does already exist, modify the existing one.
								} elseif ( isset( $category->id ) && is_numeric( $category->id ) ) {
									$category_id = $category->id;

									$custom_args = isset( $category->args ) ? get_object_vars( $category->args ) : array();

									$update = Muut_Forum_Category_Utility::updateForumCategory( $category_id, $custom_args, $post_args );

									wp_set_post_terms( $category_id, $header_term->slug, Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY, false );

									$keep_categories_array[] = $category_id;

									if ( $update == true ) {
										$menu_order++;
									}
								}
							}

							foreach( $current_categories as $current_category ) {
								if ( !in_array( $current_category->ID, $keep_categories_array ) ) {
									wp_remove_object_terms( $current_category->ID, $header_id, Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY );
								}
							}
						}
					}
				}
				$current_headers_superobject = Muut_Forum_Category_Utility::getForumCategoryHeaders();
				foreach( $current_header_order as $delete_header ) {
					wp_delete_term( $delete_header, Muut_Forum_Category_Utility::FORUMCATEGORYHEADER_TAXONOMY );
				}

				muut()->setOption( 'muut_category_headers', $custom_navigation_header_id_order );
				do_action( 'muut_custom_nav_saved' );
			}
		}
	}
}