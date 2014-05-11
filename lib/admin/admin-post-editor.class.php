<?php
/**
 * The class that is responsible for all the admin post editor (pages, posts, etc) functionality.
 * Includes functionality for creating and using the Muut meta box.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Admin_Post_Editor' ) ) {

	/**
	 * Muut Admin Most Editor class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Admin_Post_Editor
	{
		/**
		 * @static
		 * @property Muut_Admin_Post_Editor The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Admin_Post_Editor The instance.
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
		 * @return Muut_Admin_Post_Editor
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions regarding the admin post editing functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addActions() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
			add_action( 'add_meta_boxes', array( $this, 'addPostMetaBoxes' ), 1, 10 );
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
		 * Enqueues necessary admin scripts for the page/post editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueAdminScripts() {
			wp_enqueue_script ( 'jquery-ui-tabs' );
			wp_enqueue_script( 'muut-admin-functions' );
			wp_enqueue_style( 'muut-admin-style' );
		}



		/**
		 * Adds the metaboxes for the Page/Post admin editor.
		 * You can filter post types into the filter 'muut_do_not_load_metabox_for_post_types' to make the meta box
		 * NOT load for that post type.
		 *
		 * @param string $post_type The current post type.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addPostMetaBoxes( $post_type ) {
			$do_not_load_for_post_types = apply_filters( 'muut_do_not_load_metabox_for_post_types', array() );
			if ( muut()->getForumName() != '' && !in_array( $post_type, (Array) $do_not_load_for_post_types ) ) {
				add_meta_box(
					'muut-is-forum-page',
					__( 'Muut', 'muut' ),
					array( $this, 'renderMuutPostMetaBox' ),
					$post_type,
					'side',
					'high'
				);
			}
		}

		/**
		 * Renders the metabox content for the Page Editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderMuutPostMetaBox() {
			include( muut()->getPluginPath() . 'views/admin-post-metabox.php' );
		}

		/**
		 * Gets the meta box tabs to be rendered in the meta box (depending on post type, or other things).
		 * Filterable, so that tabs can be added or removed.
		 * The post_types property for tabs is the post types it should be displayed for.
		 * If it is an empty array, display that tab on all post types.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getMetaBoxTabs() {
			$default_tabs = array(
				'commenting' => array(
					'label' => __( 'Commenting', 'muut' ),
					'name' => 'commenting-tab',
					'post_types' => apply_filters( 'muut_metabox_commenting_tab_post_types', array() ),
					'template_location' => muut()->getPluginPath() . 'views/blocks/metabox-tab-commenting.php',
				),
				'channel' => array(
					'label' => __( 'Channel', 'muut' ),
					'name' => 'channel-tab',
					'post_types' => apply_filters( 'muut_metabox_channel_tab_post_types', array( 'page' ) ),
					'template_location' => muut()->getPluginPath() . 'views/blocks/metabox-tab-channel.php',
				),
				'forum' => array(
					'label' => __( 'Forum', 'muut' ),
					'name' => 'forum-tab',
					'post_types' => apply_filters( 'muut_metabox_forum_tab_post_types', array( 'page' ) ),
					'template_location' => muut()->getPluginPath() . 'views/blocks/metabox-tab-forum.php',
				),
			);

			$all_tabs = apply_filters( 'muut_post_editor_metabox_tabs', $default_tabs );

			$post_type = $this->getCurrentPostType();

			$tabs = array();
			if ( !is_null( $post_type ) ) {
				foreach( $all_tabs as $slug => $tab ) {
					if ( !isset( $tab['post_types'] ) || empty( $tab['post_types'] ) || in_array( $post_type, (Array) $tab['post_types'] ) ) {
						$tabs[$slug] = $tab;
					}
				}
			}

			return $tabs;
		}

		/**
		 * Gets the current post type on the admin side. Not *specific* to Muut.
		 * Borrowed from http://themergency.com/wordpress-tip-get-post-type-in-admin/
		 *
		 * @return string|null The current post type we are working with.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getCurrentPostType() {
			global $post, $typenow, $current_screen;

			//If we have a post so we can just get the post type from that.
			if ( $post && $post->post_type ) {
				return $post->post_type;
			// Otherwise, lets check the global $typenow - set in admin.php
			} elseif( $typenow ) {
				return $typenow;
			// Otherwise, lets check the global $current_screen object - set in sceen.php
			} elseif( $current_screen && $current_screen->post_type ) {
				return $current_screen->post_type;
			// Lastly check the post_type querystring
			} elseif( isset( $_REQUEST['post_type'] ) ) {
				return sanitize_key( $_REQUEST['post_type'] );
			}

			// If nothing worked, we don't know the post type!
			return null;
		}
	}
}