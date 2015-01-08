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
	 * @since   3.0.2
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
		 * @since  3.0.2
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
		 * @since  3.0.2
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
		 * @since 3.0.2
		 */
		public function addActions() {
			add_action( 'init', array( $this, 'addWebhooksEndpoint' ), 6 );

			add_action( 'wp', array( $this, 'receiveRequest' ), 20 );

			// Webhook actions.
			add_action( 'muut_webhook_request_post', array( $this, 'processPost' ), 10, 2 );
			add_action( 'muut_webhook_request_reply', array( $this, 'processReply' ), 10, 2 );
			add_action( 'muut_webhook_request_like', array( $this, 'processLikeUnlike' ), 10, 2 );
			add_action( 'muut_webhook_request_unlike', array( $this, 'processLikeUnlike' ), 10, 2 );
			add_action( 'muut_webhook_request_remove', array( $this, 'processRemove' ), 10, 2 );
			add_action( 'muut_webhook_request_spam', array( $this, 'processSpam' ), 10, 2 );
			add_action( 'muut_webhook_request_unspam', array( $this, 'processUnspam' ), 10, 2 );
		}

		/**
		 * The method for adding all filters.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2
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
		 * @since 3.0.2
		 */
		public function addWebhooksEndpoint() {
			add_rewrite_rule( '^' . $this->getEndpointSlug() . '/?', 'index.php?muut_action=webhooks', 'top' );
		}

		/**
		 * Begins request processing if the muut_action query var is passed
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
		 */
		public function getEndpointSlug() {
			return apply_filters( 'muut_webhooks_endpoint_slug', self::DEFAULT_ENDPOINT_SLUG );
		}

		/**
		 * Checks if webhooks are activated.
		 *
		 * @return bool Whether webhooks are activated or not.
		 * @author Paul Hughes
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
		 */
		public function processPost( $request, $event ) {

			$body = '';
			if ( isset( $request['thread']->body ) ) {
				if( is_array( $request['thread']->body ) ) {
					$body = implode( ' ', $request['thread']->body );
				} elseif ( is_string( $request['thread']->body ) ) {
					$body = $request['thread']->body;
				}
			}

			$new_thread_args = array(
				'title' => $request['thread']->title,
				'path' => $request['location']->path,
				'user' => $request['thread']->user,
				'body' => $body,
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
		 * @since 3.0.2
		 */
		public function processReply( $request, $event ) {
			$new_reply_args = array(
				'key' => $request['post']->key,
				'path' => $request['path'],
				'user' => $request['post']->user,
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
		 * @since 3.0.2
		 */
		public function processLikeUnlike( $request, $event ) {
			$path = $request['path'];

			// The path leads to an individual reply.
			if ( self::webhookPathType( $path ) == 'reply' ) {
				$comment = self::webhookGetCommentFromPath( $path, 'approve' );

				if ( $comment ) {
					// Update the number of likes and store it.
					$likes = (int) get_comment_meta( $comment->comment_ID, 'muut_likes', true );
					$post_likes = (int) get_post_meta( $comment->comment_post_ID, 'muut_thread_likes', true );
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
					update_comment_meta( $comment->comment_ID, 'muut_likes', $likes );
					update_post_meta( $comment->comment_post_ID, 'muut_thread_likes', $post_likes );
				}
			// The path leads the a top-level thread.
			} else {
				$thread_post = self::webhookGetPostFromPath( $path, 'any' );

				if ( $thread_post ) {
					// Update the number of likes and store it.
					$likes = get_post_meta( $thread_post->ID, 'muut_likes', true );
					$total_likes = (int) get_post_meta( $thread_post->ID, 'muut_thread_likes', true );
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
					update_post_meta( $thread_post->ID, 'muut_likes', $likes );
					update_post_meta( $thread_post->ID, 'muut_thread_likes', $total_likes );
				}
			}
		}

		/**
		 * Process the 'remove' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		public function processRemove( $request, $event ) {
			$path = $request['path'];

			// The path leads to an individual reply.
			if ( self::webhookPathType( $path ) == 'reply' ) {
				// (null status means any status for commen queries)
				$comment = self::webhookGetCommentFromPath( $path, null );

				if ( $comment ) {
					$likes = (int) get_comment_meta( $comment->comment_ID, 'muut_likes', true );
					$post_likes = (int) get_post_meta( $comment->comment_post_ID, 'muut_thread_likes', true );

					// Remove the number of likes from the thread count of likes.
					$post_likes = $post_likes - $likes;
					update_post_meta( $comment->comment_post_ID, 'muut_thread_likes', $post_likes );
					wp_delete_comment( $comment->comment_ID, true );
				}
			// The path leads the a top-level thread.
			} else {
				$thread_post = self::webhookGetPostFromPath( $path, 'any' );

				if ( $thread_post ) {
					wp_delete_post( $thread_post->ID, true );
				}
			}
		}

		/**
		 * Process the 'spam' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2.1
		 */
		public function processSpam( $request, $event ) {
			$path = $request['path'];

			// The path leads to an individual reply.
			if ( self::webhookPathType( $path ) == 'reply' ) {
				$comment = self::webhookGetCommentFromPath( $path, 'approve' );

				if ( $comment ) {
					Muut_Custom_Post_Types::instance()->markCommentAsSpam( $comment->comment_ID );
				}
			// The path leads the a top-level thread.
			} else {
				$thread_post = self::webhookGetPostFromPath( $path, Muut_Custom_Post_Types::MUUT_PUBLIC_POST_STATUS );

				if ( $thread_post ) {
					Muut_Custom_Post_Types::instance()->markPostAsSpam( $thread_post->ID );
				}
			}
		}

		/**
		 * Process the 'unspam' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2.1
		 */
		public function processUnspam( $request, $event ) {
			$path = $request['path'];

			// The path leads to an individual reply.
			if ( self::webhookPathType( $path ) == 'reply' ) {
				$comment = self::webhookGetCommentFromPath( $path, Muut_Custom_Post_Types::MUUT_SPAM_POST_STATUS );

				if ( $comment ) {
					Muut_Custom_Post_Types::instance()->markCommentAsNotSpam( $comment->comment_ID );
				}
			// The path leads the a top-level thread.
			} else {
				$thread_post = self::webhookGetPostFromPath( $path, Muut_Custom_Post_Types::MUUT_SPAM_POST_STATUS );

				if ( $thread_post ) {
					Muut_Custom_Post_Types::instance()->markPostAsNotSpam( $thread_post->ID );
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
		 * @since 3.0.2
		 */
		public function permalinkToForum( $permalink, $post ) {
			if ( class_exists( 'Muut_Custom_Post_Types' ) && $post->post_type == Muut_Custom_Post_Types::MUUT_THREAD_CPT_NAME ) {
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

		/**
		 * Static function for checking if (and what is) the post ID that a given muut path is commenting on.
		 *
		 * @param string $path The path we are comparing/checking for.
		 * @return int|false The post ID, if it is a comment on a given post or false, if not found.
		 */
		public static function getPostIdRepliedTo( $path ) {
			// Used to allow the adding of other "allowed" comments base domains if it has changed or whatnot.
			$comments_base_domains = apply_filters( 'muut_webhooks_allowed_comments_base_domains', array( muut()->getOption( 'comments_base_domain' ) ) );

			$matches = array();

			// Check if the comment path is in the Muut post comment path format.
			foreach( $comments_base_domains as $base_domain ) {
				preg_match_all( '/^\/' . addslashes( muut()->getForumName() ) . '\/' . addslashes( $base_domain ) . '\/([0-9]+)(?:\/|\#)?.*$/', $path, $matches );
				// If there is a match, return it and exit this loop and function.
				if ( !empty( $matches ) && isset( $matches[1][0] ) && is_numeric( $matches[1][0] ) && Muut_Post_Utility::isMuutCommentingPost( $matches[1][0] ) ) {
					return $matches[1][0];
				}
			}
		}

		/**
		 * Static function for finding out if a given webhook path (with hash sign instead of colon) leads to a comment
		 * or a top-level thread.
		 *
		 * @param string $path The webhook path.
		 * @return string Either "post" or "reply".
		 * @author Paul Hughes
		 * @since 3.0.2.1
		 */
		public static function webhookPathType( $path ) {
			$split_path = explode( '#', $path );
			$split_final = explode( '/', $split_path[1] );

			if ( count( $split_final ) > 1 ) {
				return 'reply';
			} else {
				return 'post';
			}
		}

		/**
		 * Get the WP comment that a given webhook path points to.
		 *
		 * @param string $path The webhook path.
		 * @return false|object A WP Comment object or false on failure.
		 * @author Paul Hughes
		 * @since 3.0.2.1
		 */
		public static function webhookGetCommentFromPath( $path, $status = 'approve' ) {
			if ( self::webhookPathType( $path ) != 'reply' ) {
				return false;
			}

			// Get/create the necessary subpath required for querying the comment.
			$split_path = explode( '#', $path );
			$split_final = explode( '/', $split_path[1] );
			$comment_base = $split_path[0] . '#' . $split_final[0];

			// Define the query args.
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
				'status' => $status,
				'number' => 1,
			);

			// Get the comment data.
			$comment_query = new WP_Comment_Query;
			$comments = $comment_query->query( $query_args );

			if ( isset( $comments[0] ) ) {
				return $comments[0];
 			} else {
				return false;
			}
		}

		/**
		 * Get the WP Post that a given webhook path points to.
		 *
		 * @param string $path The webhook path.
		 * @return false|WP_Post A WordPress Post object or false on failure.
		 * @author Paul Hughes
		 * @since 3.0.2.1
		 */
		public static function webhookGetPostFromPath( $path, $status = Muut_Custom_Post_Types::MUUT_PUBLIC_POST_STATUS ) {
			if ( self::webhookPathType( $path ) != 'post' ) {
				return false;
			}

			// Define the query args.
			$query_args = array(
				'post_type' => Muut_Custom_Post_Types::MUUT_THREAD_CPT_NAME,
				'post_status' => $status,
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
				return $posts[0];
			} else {
				return false;
			}
		}
	}
}
