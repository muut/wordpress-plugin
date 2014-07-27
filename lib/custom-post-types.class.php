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

		const MUUT_REPLY_TYPE_NAME = 'muut_post_reply';

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

			register_post_type( self::MUUT_THREAD_CPT_NAME, $muut_post_args );
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
				'post_title' => $title,
				'post_content' => $body,
				'post_name' => urlencode( $path ),
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

			// Add the Muut post path as meta (even though it is also stored in post_name on the posts table).
			update_post_meta( $inserted_post, 'muut_path', $path );

			// Return the WP post id.
			return $inserted_post;
		}

		/**
		 * Add new Muut Reply.
		 *
		 * @param array $args The comment args.
		 *                    This array should follow the following structure:
		 *                    'key' => The Muut thread key
		 *                    'path' => The main thread path.
		 *                    'user' => The *Muut* username.
		 *                    'body' => The reply content.
		 * @return int The comment id.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addMuutReplyData( $args = array() ) {
			if ( empty( $args['key'] ) || empty( $args['path'] ) || empty( $args['user'] ) || !isset( $args['body'] ) ) {
				return false;
			}

			$comment_type = self::MUUT_REPLY_TYPE_NAME;

			// If everything is there, lets add the thread.
			extract( $args );

			// Check if a WP post exists in the database that would match the path of the "post" request (for threaded commenting).
			$query_args = array(
				'post_type' => Muut_Custom_Post_Types::MUUT_THREAD_CPT_NAME,
				'post_status' => Muut_Custom_Post_Types::MUUT_PUBLIC_POST_STATUS,
				'meta_query' => array(
					array(
						'key' => 'muut_path',
						'value' => $path,
					),
				),
				'posts_per_page' => 1,
			);

			// Get the post data.
			$posts_query = new WP_Query;
			$posts = $posts_query->query( $query_args );

			$post_id = isset( $posts[0]->ID ) ? $posts[0]->ID : 0;

			$comment_args = array(
				'comment_post_ID' => $post_id,
				'comment_content' => $body,
				'comment_type' => $comment_type,
				'comment_agent' => 'Muut ' . muut()->getMuutVersion() . '; ' . muut()->getForumName(),
			);

			// Add the thread to the Posts table.
			$inserted_reply = wp_insert_comment( $comment_args );

			if ( $inserted_reply == 0 ) {
				return false;
			}

			// Add the muut comment meta.
			update_comment_meta( $inserted_reply, 'muut_user', $user );

			update_comment_meta( $inserted_reply, 'muut_key', $key );

			update_comment_meta( $inserted_reply, 'muut_path', $path );

			// Return the comment id.
			return $inserted_reply;
		}
	}
}