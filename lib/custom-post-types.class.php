<?php
/**
 * The singleton class responsible for registering the custom post types related to the Muut plugin.
 * Currently this is only tied to Webhooks and storing data directly related to those.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Custom_Post_Types' ) ) {

	/**
	 * Muut Custom Post Types class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Custom_Post_Types
	{
		/**
		 * @static
		 * @property Muut_Custom_Post_Types The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Custom_Post_Types The instance.
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
		 * @return Muut_Custom_Post_Types
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * Adds the actions used by this class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addActions() {
			add_action( 'init', array( $this, 'registerCustomPostTypes' ) );
		}

		/**
		 * Adds the filters used by this class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addFilters() {

		}

		/**
		 * Register the custom post types.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function registerCustomPostTypes() {
			// Only worrying about a couple of these, since the post type is TOTALLY not public at all.
			$muutPostLabels = array(
				'name' => __( 'Muut Threads', 'muut' ),
				'singular_name' => __( 'Muut Thread', 'muut' ),
			);

			$muutPostArgs = apply_filters( 'muut_thread_cpt_args', array(
				'label' => __( 'Muut Threads', 'muut' ),
				'labels' => $muutPostLabels,
				'description' => __( 'This post type is not for public use, but is mainly for storing Muut thread data passed to it via webhooks.', 'muut' ),
				'public' => false,
			) );

			$muutReplyLabels = array(
				'name' => __( 'Muut Replies', 'muut' ),
				'singular_name' => __( 'Muut Reply', 'muut' ),
			);

			$muutReplyArgs = apply_filters( 'muut_reply_cpt_args', array(
				'label' => __( 'Muut Replies', 'muut' ),
				'labels' => $muutReplyLabels,
				'description' => __( 'This post type is not for public use, but is mainly for storing Muut reply data passed to it via webhooks.', 'muut' ),
				'public' => false,
			) );

			register_post_type( 'muut_post', $muutPostArgs );
			register_post_type( 'muut_reply', $muutReplyArgs );
		}
	}
}