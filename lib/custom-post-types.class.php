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
		const MUUT_THREAD_CPT_NAME = 'muut_thread';

		const MUUT_REPLY_CPT_NAME = 'muut_reply';

		const MUUT_PUBLIC_POST_STATUS = 'muut_public';

		const MUUT_SPAM_POST_STATUS = 'muut_spam';


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
			add_action( 'init', array( $this, 'registerCustomPostStatuses' ) );
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
			$muut_post_labels = array(
				'name' => __( 'Muut Threads', 'muut' ),
				'singular_name' => __( 'Muut Thread', 'muut' ),
			);

			$muut_post_args = apply_filters( 'muut_thread_cpt_args', array(
				'label' => __( 'Muut Threads', 'muut' ),
				'labels' => $muut_post_labels,
				'description' => __( 'This post type is not for public use, but is mainly for storing Muut thread data passed to it via webhooks.', 'muut' ),
				'public' => false,
			) );

			$muut_reply_labels = array(
				'name' => __( 'Muut Replies', 'muut' ),
				'singular_name' => __( 'Muut Reply', 'muut' ),
			);

			$muut_reply_args = apply_filters( 'muut_reply_cpt_args', array(
				'label' => __( 'Muut Replies', 'muut' ),
				'labels' => $muut_reply_labels,
				'description' => __( 'This post type is not for public use, but is mainly for storing Muut reply data passed to it via webhooks.', 'muut' ),
				'public' => false,
			) );

			register_post_type( self::MUUT_THREAD_CPT_NAME, $muut_post_args );
			register_post_type( self::MUUT_REPLY_CPT_NAME, $muut_reply_args );
		}

		/**
		 * Register the custom post types post statuses.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function registerCustomPostStatuses() {
			$muut_post_status_public_args = apply_filters( 'muut_public_post_status_args', array(
				'label' => __( 'Public Muut Post', 'muut' ),
				'public' => false,
			) );

			$muut_post_status_spam_args = apply_filters( 'muut_spam_post_status_args', array(
				'label' => __( 'Spammed Muut Post', 'muut' ),
				'public' => false,
			) );

			register_post_status( self::MUUT_PUBLIC_POST_STATUS, $muut_post_status_public_args );
			register_post_status( self::MUUT_SPAM_POST_STATUS, $muut_post_status_spam_args );
		}

		/**
		 * Add new Muut Thread.
		 *
		 * @param array $args The post args.
		 *                    This array should follow the following structure:
		 *                    'title' => "The Thread Title"
		 *                    'path' => The new thread path.
		 *                    'user' => The *Muut* username.
		 *                    'body' => The thread content.
		 * @return int The CPT post id.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addMuutThreadData( $args = array() ) {
			if ( empty( $args['title'] ) || empty( $args['path'] ) || empty( $args['user'] ) || !isset( $args['body'] ) ) {
				return false;
			}

			// If everything is there, lets add the thread.
			extract( $args );

			$post_args = array(
				'post_content' => $body,
				'post_name' => $path,
				'post_type' => self::MUUT_THREAD_CPT_NAME,
				'post_status' => self::MUUT_PUBLIC_POST_STATUS,
			);

			// Add the thread to the Posts table.
			$inserted_post = wp_insert_post( $post_args, false );

			if ( $inserted_post == 0 ) {
				return false;
			}

			// Add the muut user as post meta.
			update_post_meta( $inserted_post, 'muut_user', $user );

			// Return the WP post id.
			return $inserted_post;
		}

	}
}