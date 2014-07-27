<?php
/**
 * The Muut Popular Posts widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Widget_Popular_Posts' ) ) {
	/**
	 * Muut Popular Posts widget class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Widget_Popular_Posts extends WP_Widget {

		const POPULAR_POSTS_TRANSIENT_NAME = 'muut_popular_posts';

		/**
		 * @property array The instance array of settings.
		 */
		protected $widget_instance;

		/**
		 * The class constructor.
		 *
		 * @return Muut_Widget_Popular_Posts
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		function __construct() {
			parent::__construct(
				'muut_popular_posts_widget',
				__( 'Muut Popular Posts', 'muut' ),
				array(
					'description' => __( 'Use this to show the Muut posts with the most activity.', 'muut' ),
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
			$title = isset( $instance['title'] ) ? $instance['title'] : '';

			// Render widget.
			echo $args['before_widget'];
			echo $args['before_title'] . $title . $args['after_title'];
			include( muut()->getPluginPath() . 'views/widgets/widget-popular-posts.php' );
			echo $args['after_widget'];
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
			include( muut()->getPluginPath() . 'views/widgets/admin-widget-popular-posts.php' );
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
			// Save the title.
			$instance['title'] = !empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
			// Save the number-of-posts to show.
			if ( empty( $new_instance['number_of_posts'] ) || !is_numeric( $new_instance['number_of_posts'] ) || $new_instance['number_of_posts'] < 1 ) {
				$new_instance['number_of_posts'] = 5;
			} elseif ( $new_instance['number_of_posts'] > 10 ) {
				$new_instance['number_of_posts'] = 10;
			}
			$instance['number_of_posts'] = $new_instance['number_of_posts'];

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

			// Update the transient with array of the posts and their data.
			$this->refreshCache();
		}

		/**
		 * Refreshes the Popular Posts caching items (transient and JSON file).
		 *
		 * @param int $number_of_posts The number of posts to set in the transient.
		 * @return array The new data array.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function refreshCache() {

		}

		/**
		 * Sets/updates the popular posts transient value.
		 *
		 * @param array $data_array The data array to store in the transient.
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function updateTransient( $data_array ) {

		}

		/**
		 * Get the popular posts array from the transient.
		 *
		 * @return array The transient array with the popular posts data.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getPopularPostsData() {
			if ( false === ( $popular_posts_data = get_transient( self::POPULAR_POSTS_TRANSIENT_NAME ) ) ) {
				$this->refreshCache();
			}

			return get_transient( self::POPULAR_POSTS_TRANSIENT_NAME );
		}
	}
}