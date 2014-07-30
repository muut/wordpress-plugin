<?php
/**
 * The class that is responsible for all the webhooks functionality.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Webhooks' ) ) {

	/**
	 * Muut Webhooks class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Webhooks
	{
		const DEFAULT_ENDPOINT_SLUG = 'muut-webhooks';

		/**
		 * @static
		 * @property Muut_Webhooks The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property string The raw request body.
		 */
		protected $raw_request;

		/**
		 * @private
		 * @property string The shared secret.
		 */
		private $secret;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Webhooks The instance.
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
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
		 * @return Muut_Webhooks
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
		 */
		protected function __construct() {
			$this->secret = muut()->getOption( 'webhooks_secret' );
			if ( $this->isWebhooksActivated() ) {
				Muut_Initializer::instance()->initCustomPostTypes();
			}
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addActions() {
			add_action( 'admin_init', array( $this, 'addWebhooksEndpoint' ) );

			add_action( 'wp', array( $this, 'receiveRequest' ), 20 );

			// Webhook actions.
			add_action( 'muut_webhook_request_post', array( $this, 'processPost' ), 10, 2 );
			add_action( 'muut_webhook_request_reply', array( $this, 'processReply' ), 10, 2 );
			add_action( 'muut_webhook_request_like', array( $this, 'processLikeUnlike' ), 10, 2 );
			add_action( 'muut_webhook_request_unlike', array( $this, 'processLikeUnlike' ), 10, 2 );
			add_action( 'muut_webhook_request_remove', array( $this, 'processRemove' ), 10, 2 );
		}

		/**
		 * The method for adding all filters.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addFilters() {
			add_filter( 'muut_validate_setting_use_webhooks', array( $this, 'executeSettingSave' ) );

			add_filter( 'post_type_link', array( $this, 'permalinkToForum' ), 10, 2 );
		}

		/**
		 * Adds the webhooks endpoint for receiving Muut HTTP requests.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addWebhooksEndpoint() {
			add_rewrite_rule( '^' . $this->getEndpointSlug() . '/?', 'index.php?muut_action=webhooks', 'top' );
		}

		/**
		 * Begins request processing if the muut_action query var is passed
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function receiveRequest() {
			if ( get_query_var( 'muut_action' ) == 'webhooks' && $this->isWebhooksActivated() ) {

				$status_code = $this->validateRequest();

				//error_log( 'Request status: ' . $status_code );
				if ( $status_code != 200 ) {
					status_header( $status_code );
					exit;
				}

				// Display the payload.
				//error_log( $this->getRequestBody( true ) );
				// Display the X-Muut-Signature header value.
				//error_log( $_SERVER['HTTP_X_MUUT_SIGNATURE'] );

				$body = $this->getRequestBody();

				do_action( 'muut_webhook_request_' . $body['event'], $body, $body['event'] );
				do_action( 'muut_webhook_request', $body, $body['event'] );

				exit;
			}
		}

		/**
		 * Returns the request body content formatted as an array.
		 *
		 * @return array The request body content.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function getRequestBody( $raw = false ) {
			$body = file_get_contents('php://input');

			$request = false;
			if ( !empty( $body ) ) {
				if ( !$raw ) {
					$request = $this->parseRequestStructure( $body );
				} else {
					$request = $this->raw_request = $body;
				}
			}
			return $request;
		}

		/**
		 * Parses the request body into the proper PHP array/object based on the Webhooks Events Structure
		 * (that can be found int $this->getWebhookEventsStructure()
		 *
		 * @param string $body The body content of the request.
		 * @return array The parsed array/object.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function parseRequestStructure( $body ) {
			$request = json_decode( $body );

			if ( $request == null ) return false;

			$structures = $this->getWebhookEventsStructure();

			$event = $request[0];
			$parsed = array();
			for ( $i = 0; $i < count( $request ); $i++ ) {
				$parsed[$structures[$event][$i]] = $request[$i];
			}

			//error_log( print_r( $parsed, true ) );
			return $parsed;
		}

		/**
		 * Gets and filters the endpoint slug.
		 *
		 * @return string The endpoint slug.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getEndpointSlug() {
			return apply_filters( 'muut_webhooks_endpoint_slug', self::DEFAULT_ENDPOINT_SLUG );
		}

		/**
		 * Checks if webhooks are activated.
		 *
		 * @return bool Whether webhooks are activated or not.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function isWebhooksActivated() {
			return muut()->getOption( 'use_webhooks' );
		}

		/**
		 * On saving, lets make sure to create (or check for) a webhooks shared secret for the user to enter on the
		 * Muut settings end.
		 *
		 * @param int $value Whether the use_webhooks setting is being saved as active or not.
		 * @return int $value The same value—we aren't messing with it.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function executeSettingSave( $value ) {
			if ( $value == 1 && !muut()->getOption( 'use_webhooks' ) ) {
				if ( empty( $this->secret ) ) {
					$this->secret = $this->generateSecret();
				}
				add_filter( 'muut_settings_validated', array( $this, 'saveSecret' ) );
				$notice_message =  __( 'Note that webhooks functionality will only work for posts made after activation.', 'muut' );
				muut()->queueAdminNotice( 'updated', $notice_message );
				add_action( 'wp_print_scripts', array( $this, 'printSavedJs' ) );
			}

			return $value;
		}

		/**
		 * Print the function to open the webhooks finish setup popup.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function printSavedJs() {
			echo '<script type="text/javascript">';
			echo 'var open_webhooks_setup_window = true;';
			echo '</script>';
		}



		/**
		 * Adds the current instance's shared secret to the settings being saved.
		 *
		 * @param array $settings The current settings array.
		 * @return array The filtered settings array.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function saveSecret( $settings ) {
			$settings['webhooks_secret'] = $this->secret;

			return $settings;
		}

		/**
		 * Generate a (random) secret for shared secret use with Muut.
		 *
		 * @return string The secret.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function generateSecret() {
			$validCharacters = "abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ0123456789";
			$validCharNumber = strlen($validCharacters);

			$result = "";
			for ( $i = 0; $i < 10; $i++ ) {
				$index = mt_rand( 0, $validCharNumber - 1 );
				$result .= $validCharacters[$index];
			}

			return $result;
		}

		/**
		 * Validate HTTP request from Muut.
		 *
		 * @return int Whether the request is valid or not, the HTTP status code.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function validateRequest() {
			if ( !isset( $_SERVER['HTTP_X_MUUT_SIGNATURE'] ) ) {
				return 412;
			}
			$request = $this->getRequestBody( true );

			$signature = hash_hmac( 'sha1', $request, $this->secret );

			if ( $signature != $_SERVER['HTTP_X_MUUT_SIGNATURE'] ) {
				return 403;
			}

			return 200;
		}

		/**
		 * Gets the expected webhook events and the returned array structure for each.
		 *
		 * @return array An array of webhook events.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getWebhookEventsStructure() {
			return apply_filters( 'muut_webhook_events_structure', array(
				'enter' => array(
					'event',
					'user',
					'arg1',
				),
				'leave' => array(
					'event',
					'user',
					'arg1',
				),
				'like' => array(
					'event',
					'path',
					'user',
				),
				'unlike' => array(
					'event',
					'path',
					'user',
				),
				'remove' => array(
					'event',
					'path',
					'user',
				),
				'spam' => array(
					'event',
					'path',
					'user',
				),
				'unspam' => array(
					'event',
					'path',
					'user',
				),
				'reply' => array(
					'event',
					'path',
					'post',
				),
				'post' => array(
					'event',
					'location',
					'thread',
				),
			) );
		}


		/**********
		 * Webhooks Processing Methods
		 **********/

		/**
		 * Process the 'post' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function processPost( $request, $event ) {

			$new_thread_args = array(
				'title' => $request['thread']->title,
				'path' => $request['location']->path,
				'user' => $request['thread']->user->path,
				'body' => '',
			);

			$custom_posts_object = Muut_Custom_Post_Types::instance();

			$inserted_post = $custom_posts_object->addMuutThreadData( $new_thread_args );

			if ( $inserted_post ) {
				update_post_meta( $inserted_post, 'muut_likes', '0' );
				update_post_meta( $inserted_post, 'muut_thread_likes', '0' );
			}
		}

		/**
		 * Process the 'post' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function processReply( $request, $event ) {
			$new_reply_args = array(
				'key' => $request['post']->key,
				'path' => $request['path'],
				'user' => $request['post']->user->path,
				'body' => join( '', $request['post']->body ),
			);

			$custom_posts_object = Muut_Custom_Post_Types::instance();

			$inserted_comment = $custom_posts_object->addMuutReplyData( $new_reply_args );

			if ( $inserted_comment ) {
				update_comment_meta( $inserted_comment, 'muut_likes', '0' );
			}

		}

		/**
		 * Process the 'post' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function processLikeUnlike( $request, $event ) {
			$path = $request['path'];

			$split_path = explode( '#', $path );
			$split_final = explode( '/', $split_path[1] );
			if ( count( $split_final ) > 1 ) {
				// The path leads to an individual reply.
				$comment_base = $split_path[0] . '#' . $split_final[0];
				$query_args = array(
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'muut_path',
							'value' => $comment_base,
						),
						array(
							'key' => 'muut_key',
							'value' => $split_final[1],
						),
					),
					'number' => 1,
				);

				// Get the comment data.
				$comment_query = new WP_Comment_Query;
				$comments = $comment_query->query( $query_args );

				// Update the number of likes and store it.
				$likes = (int) get_comment_meta( $comments[0]->comment_ID, 'muut_likes', true );
				$post_likes = (int) get_post_meta( $comments[0]->comment_post_ID, 'muut_thread_likes', true );
				if ( $event == 'like' ) {
					$likes++;
					$post_likes++;
				} elseif ( $event == 'unlike' ) {
					if ( $likes > 0 ) {
						$likes--;
					}
					if ( $post_likes > 0 ) {
						$post_likes--;
					}
				}
				update_comment_meta( $comments[0]->comment_ID, 'muut_likes', $likes );
				update_post_meta( $comments[0]->comment_post_ID, 'muut_thread_likes', $post_likes );
			} else {
				// The path leads to a top-level thread.
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

				// Update the number of likes and store it.
				$likes = get_post_meta( $posts[0]->ID, 'muut_likes', true );
				$total_likes = (int) get_post_meta( $posts[0]->ID, 'muut_thread_likes', true );
				if ( $event == 'like' ) {
					$likes++;
					$total_likes++;
				} elseif ( $event == 'unlike' ) {
					if ( $likes > 0 ) {
						$likes--;
					}
					if ( $total_likes > 0 ) {
						$total_likes--;
					}
				}
				update_post_meta( $posts[0]->ID, 'muut_likes', $likes );
				update_post_meta( $posts[0]->ID, 'muut_thread_likes', $total_likes );
			}
		}

		/**
		 * Process the 'remove' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function processRemove( $request, $event ) {
			$path = $request['path'];

			$split_path = explode( '#', $path );
			$split_final = explode( '/', $split_path[1] );
			if ( count( $split_final ) > 1 ) {
				// The path leads to an individual reply.
				$comment_base = $split_path[0] . '#' . $split_final[0];
				$query_args = array(
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'muut_path',
							'value' => $comment_base,
						),
						array(
							'key' => 'muut_key',
							'value' => $split_final[1],
						),
					),
					'number' => 1,
				);

				// Get the comment data.
				$comment_query = new WP_Comment_Query;
				$comments = $comment_query->query( $query_args );

				if ( isset( $comments[0] ) ) {
					$likes = (int) get_comment_meta( $comments[0]->comment_ID, 'muut_likes', true );
					$post_likes = (int) get_post_meta( $comments[0]->comment_post_ID, 'muut_thread_likes', true );

					// Remove the number of likes from the thread count of likes.
					$post_likes = $post_likes - $likes;
					update_post_meta( $comments[0]->comment_post_ID, 'muut_thread_likes', $post_likes );
					wp_delete_comment( $comments[0]->comment_ID, true );
				}
			} else {
				// The path leads to a top-level thread.
				$query_args = array(
					'post_type' => Muut_Custom_Post_Types::MUUT_THREAD_CPT_NAME,
					'post_status' => 'any',
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

				if ( isset( $posts[0] ) ) {
					wp_delete_post( $posts[0]->ID, true );
				}
			}
		}

		/**
		 * Redirect to the forum page for Muut threads.
		 *
		 * @param string $permalink The current permalink.
		 * @param WP_Post $post The post.
		 * @return string The filtered permalink.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function permalinkToForum( $permalink, $post ) {
			if ( $post->post_type == Muut_Custom_Post_Types::MUUT_THREAD_CPT_NAME ) {
				$forum_page_id = Muut_Post_Utility::getForumPageId();
				$path = get_post_meta( $post->ID, 'muut_path', true );
				$path = str_replace( array( '/' . muut()->getForumName(), '#' ) , array( '', ':'), $path );
				if ( !empty( $forum_page_id ) ) {
					$permalink = get_permalink( $forum_page_id ) . '#!' . $path;
				} else {
					$permalink = 'http://' . Muut::MUUTSERVERS . '/' . muut()->getForumName() . '/#!' . $path;
				}
			}
			return $permalink;
		}
	}
}
