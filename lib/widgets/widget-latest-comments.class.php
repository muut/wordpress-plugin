<?php
/**
 * The Muut Latest Comments widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Widget_Latest_Comments' ) ) {
	/**
	 * Muut Latest Comments widget class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Widget_Latest_Comments extends WP_Widget {

		const LATEST_COMMENTS_TRANSIENT_NAME = 'muut_latest_comments';

		const REPLY_UPDATE_TIME_NAME = 'muut_last_reply_time';

		const REPLY_LAST_USER_DATA_NAME = 'muut_last_reply_user';

		const LATEST_COMMENTS_JSON_FILE_NAME = 'latest_comments.json';

		/**
		 * @property array The instance array of settings.
		 */
		protected $widget_instance;

		/**
		 * @static
		 * @property bool Whether the widget has been loaded.
		 */
		protected static $has_loaded = false;

		/**
		 * The class constructor.
		 *
		 * @return Muut_Widget_Online_Users
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		function __construct() {
			parent::__construct(
				'muut_latest_comments_widget',
				__( 'Muut Latest Comments', 'muut' ),
				array(
					'description' => __( 'Use this to show the latest posts with Muut comments.', 'muut' ),
				)
			);

			$this->addActions();
			$this->addFilters();
		}

		/**
		 * Adds the actions pertaining to the widget's functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addActions() {
			// Update the transient data when a reply is made.
			add_action( 'muut_webhook_request_reply', array( $this, 'updateWidgetData' ), 100, 2 );
			// The reason we have to worry about this below (post event) is in case it is on threaded commenting.
			add_action( 'muut_webhook_request_post', array( $this, 'updateWidgetData' ), 100, 2 );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueWidgetScripts' ), 12 );
			add_action( 'wp_print_scripts', array( $this, 'printWidgetJs' ) );
			add_action( 'init', array( $this, 'maybeRequireMuutResources') );
		}

		/**
		 * Adds the filters pertaining to the widget's functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addFilters() {

		}

		/**
		 * Render the widget frontend output.
		 *
		 * @param array $args The sidebar arguments.
		 * @param array $instance The widget instance parameters.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function widget( $args, $instance ) {
			// Make sure webhooks are active, or don't bother.
			if ( !muut_is_webhooks_active() || self::$has_loaded || apply_filters( 'muut_hide_latest_comments_widget_display', false ) ) {
				return;
			}
			// Make sure the Muut resources get loaded (only stuff in the footer will work, as this happens
			// partway through page load).
			add_filter( 'muut_requires_muut_resources', '__return_true' );
			muut()->enqueueFrontendScripts();

			$title = isset( $instance['title'] ) ? $instance['title'] : '';
			$latest_comments_data = array_slice( $this->getLatestCommentsData(), 0, $instance['number_of_comments'] );

			// Render widget.
			echo $args['before_widget'];
			echo '<script type="text/javascript">';
			echo 'var muut_latest_comments_num_posts = "' . $instance['number_of_comments'] . '";';
			echo 'var muut_latest_comments_path = "' . muut()->getOption( 'comments_base_domain') . '";';
			if ( get_the_ID() && Muut_Post_Utility::isMuutCommentingPost( get_the_ID() ) ) {
				echo 'var muut_wp_post_id = ' . get_the_ID() . ';';
				echo 'var muut_wp_post_permalink = "' . get_permalink() . '";';
				echo 'var muut_wp_post_title = "' . get_the_title() . '";';
			}
			echo '</script>';
			echo $args['before_title'] . $title . $args['after_title'];
			include( muut()->getPluginPath() . 'views/widgets/widget-latest-comments.php' );
			echo $args['after_widget'];

			self::$has_loaded = true;
		}

		/**
		 * Render the admin form for widget customization.
		 *
		 * @param array $instance The widget instance parameters.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function form( $instance ) {
			if ( muut_is_webhooks_active() ) {
				include( muut()->getPluginPath() . 'views/widgets/admin-widget-latest-comments.php' );
			} else {
				include( muut()->getPluginPath() . 'views/widgets/admin-error-widget-requires-webhooks.php' );
			}
		}

		/**
		 * Process the widget arguments to save the customization for that instance.
		 *
		 * @param array $new_instance The changed/new arguments.
		 * @param array $old_instance The previous/old arguments.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = !empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';

			if ( empty( $new_instance['number_of_comments'] ) || !is_numeric( $new_instance['number_of_comments'] ) || $new_instance['number_of_comments'] < 1 ) {
				$new_instance['number_of_comments'] = 5;
			} elseif ( $new_instance['number_of_comments'] > 10 ) {
				$new_instance['number_of_comments'] = 10;
			}
			$instance['number_of_comments'] = $new_instance['number_of_comments'];

			return $instance;
		}


		/********
		 * CUSTOM WIDGET METHODS
		 ********/

		/**
		 * Updates the widget data sources for displaying the widget content on the frontend.
		 *
		 * @param array $request The parsed webhook HTTP request data.
		 * @param string $event The event that was received via the webhook (in this case, should always be 'reply').
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function updateWidgetData( $request, $event ) {

			if ( $event == 'reply' ) {
				$path = $request['path'];
				$user = $request['post']->user;
			} elseif ( $event == 'post' ) {
				$path = $request['location']->path;
				$user = $request['thread']->user;
			}
			if ( !isset( $path ) ) {
				return;
			}

			// Check if a WP post exists in the database that would match the path of the "post" request (for threaded commenting).
			preg_match_all( '/^\/' . addslashes( muut()->getForumName() ) . '\/' . addslashes( muut()->getOption( 'comments_base_domain' ) ) . '\/([0-9]+)(?:\/|\#)?.*$/', $path, $matches );

			if ( empty( $matches ) || !isset( $matches[1][0] ) || !is_numeric( $matches[1][0] ) ) {
				return;
			}
			$post_id = $matches[1][0];

			// Make sure the post is a post with Muut commenting enabled.
			if ( !Muut_Post_Utility::isMuutCommentingPost( $post_id ) ) {
				return;
			}

			// Add/update a meta for the post with the time of the last comment and the user data responsible.
			update_post_meta( $post_id, self::REPLY_UPDATE_TIME_NAME, time() );
			update_post_meta( $post_id, self::REPLY_LAST_USER_DATA_NAME, $user );

			// Update the transient with array of the posts and their data for the "latest comments."
			$this->refreshCache();
		}

		/**
		 * Refreshes the Latest Comments caching items (transient and JSON file).
		 *
		 * @param int $number_of_posts The number of posts to set in the transient and JSON file.
		 * @return array The new data array.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function refreshCache( $number_of_posts = 10 ) {
			$number_of_posts = is_numeric( $number_of_posts ) ? $number_of_posts : 10;

			$number_of_posts = apply_filters( 'muut_latest_comments_number_of_posts_to_store', $number_of_posts );

			// Get the posts with the most recent Muut Reply Update Times.
			$query_args = apply_filters( 'muut_latest_posts_transient_args', array(
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'meta_key' => self::REPLY_UPDATE_TIME_NAME,
				'meta_query' => array(
					array(
						'key' => 'muut_use_muut_commenting',
						'value' => '1',
						'compare' => '=',
						'type' => 'NUMERIC',
					),
				),
				'posts_per_page' => $number_of_posts,
			) );

			$query = new WP_Query( $query_args );
			$posts = $query->get_posts();

			// Use the returned posts to generate the new transient data.
			$data_array = array();
			foreach ( $posts as $comments_post ) {
				$user = get_post_meta( $comments_post->ID, self::REPLY_LAST_USER_DATA_NAME, true );
				$data_array[] = array(
					'post_id' => $comments_post->ID,
					'post_title' => $comments_post->post_title,
					'post_permalink' => get_permalink( $comments_post ),
					'user' => $user,
					'timestamp' => get_post_meta( $comments_post->ID, self::REPLY_UPDATE_TIME_NAME, true ),
				);
			}

			// Update the transient with the data as well as the JSON file.
			$this->updateTransient( $data_array );
			$this->updateJsonFile( $data_array );

			return $data_array;
		}

		/**
		 * Sets/updates the latest comments transient value.
		 *
		 * @param array $data_array The data array to store in the transient.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function updateTransient( $data_array ) {
			if ( !is_array( $data_array ) ) {
				return;
			}

			// Set the transient, with expiration 12 hours from now.
			set_transient( self::LATEST_COMMENTS_TRANSIENT_NAME, $data_array, 60 * 60 * 12 );
		}

		/**
		 * Sets/updates the latest comments JSON cache file.
		 *
		 * @param array $data_array The data array to store in the file.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function updateJsonFile( $data_array ) {
			if ( !is_array( $data_array ) ) {
				return;
			}

			$content = json_encode( array(
				'latest_comments_posts' => $data_array,
			) );

			// Write the file.
			Muut_Files_Utility::writeFile( 'cache/' . self::LATEST_COMMENTS_JSON_FILE_NAME, $content );
		}

		/**
		 * Get the latest comments data array from the transient.
		 *
		 * @return array The transient array with the latest comments data.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getLatestCommentsData() {
			if ( false === ( $latest_comments_data = get_transient( self::LATEST_COMMENTS_TRANSIENT_NAME ) ) ) {
				$this->refreshCache();
			}

			return get_transient( self::LATEST_COMMENTS_TRANSIENT_NAME );
		}

		/**
		 * Prints the widget JS stuff (mostly, the array of latest comments items).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function printWidgetJs() {
			global $post;
			if ( is_active_widget( false, false, $this->id_base, true ) && !is_admin() && isset( $post ) ) {
				$poll_time = apply_filters( 'muut_latest_comments_poll_updates', '0' );
				$json = $content = json_encode( array(
					'latest_comments_posts' => $this->getLatestCommentsData(),
				) );
				echo '<script type="text/javascript">';
				echo 'var muut_latest_comments_poll_time = "' . $poll_time . '";';
				echo 'var muut_latest_comments_json = ' . $json . ';';
				echo 'var muut_latest_comments_request_endpoint = "' . trailingslashit( Muut_Files_Utility::getUploadsUrl() ) . 'cache/' . self::LATEST_COMMENTS_JSON_FILE_NAME . '";';
				$user_obj = new stdClass();
				$user_obj->path = '%USER_PATH%';
				$user_obj->displayname = '%USER_DISPLAYNAME%';
				$user_obj->img = '%USER_IMAGEURL%';
				echo 'var muut_latest_comments_row_template = \'' . $this->getRowMarkup( '%POSTID%', '%TIMESTAMP%', $user_obj ) . '\';';
				echo '</script>';
			}
		}

		/**
		 * Enqueues the JS required for this widget.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function enqueueWidgetScripts() {
			if ( is_active_widget( false, false, $this->id_base, true ) ) {
				wp_enqueue_script( 'muut-widget-latest-comments', muut()->getPluginUrl() . 'resources/muut-widget-latest-comments.js', array( 'jquery', 'muut-widgets-initialize' ), Muut::VERSION, true );
			}
		}

		/**
		 * Check if the widget is active, in which case make sure to include the Muut resources.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function maybeRequireMuutResources() {
			if ( is_active_widget( false, false, $this->id_base, true ) ) {
				add_filter( 'muut_requires_muut_resources', '__return_true' );
			}
		}

		/**
		 * Get a row markup for given row data.
		 *
		 *
		 */
		public function getRowMarkup( $post_id, $timestamp, $user_obj ) {
			if ( is_numeric( $timestamp ) ) {
				$time_since = time() - $timestamp;
				if ( $time_since < 60 ) {
					$list_time = 'just now';
				} elseif ( $time_since < ( 60 * 60 ) ) {
					$list_time = floor( $time_since / 60 ) . 'm';
				} elseif ( $time_since < ( 60 * 60 * 24 ) ) {
					$list_time = floor( $time_since / ( 60 * 60 ) ) . 'h';
				} elseif ( $time_since < ( 60 * 60 * 24 * 7 ) ) {
					$list_time = floor( $time_since / ( 60 * 60 * 24 ) ) . 'd';
				} else {
					$list_time = floor( $time_since / ( 60 * 60 * 24 * 7 ) ) . 'w';
				}
			} else {
				$list_time = '%LISTTIME%';
			}
			if ( is_numeric( $post_id ) ) {
				$permalink = get_permalink( $post_id );
				$title = get_the_title( $post_id );
			} else {
				$permalink = '%POST_PERMALINK%';
				$title = '%POST_TITLE%';
			}
			$user_link_path = Muut_Post_Utility::getForumPageId() && Muut_Post_Utility::getForumPageId() != get_the_ID() ? get_permalink( Muut_Post_Utility::getForumPageId() ) . '#!/' . $user_obj->path . '"' : false;
			$html = '<li class="muut_recentcomments" data-post-id="' . $post_id . '" data-timestamp="' . $timestamp. '" data-username="' . $user_obj->path . '">';
			$html .= muut_get_user_facelink_avatar( $user_obj->path, $user_obj->displayname, false, $user_link_path, $user_obj->img, false );
			$html .= '<span class="recent-comments-post-title"><a href="' . $permalink . '">' . $title . '</a></span>';
			$html .= '<div class="muut-post-time-since">' . $list_time . '</div>';
			$html .= '</li>';

			return $html;
		}
	}
}