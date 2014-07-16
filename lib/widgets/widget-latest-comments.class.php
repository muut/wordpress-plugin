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
			add_action( 'muut_webhook_request_reply', array( $this, 'updateWidgetData' ), 100, 2 );
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
			// Make sure the Muut resources get loaded (only stuff in the footer will work, as this happens
			// partway through page load).
			add_filter( 'muut_requires_muut_resources', '__return_true' );
			muut()->enqueueFrontendScripts();

			$title = isset( $instance['title'] ) ? $instance['title'] : '';

			// Render widget.
			echo $args['before_widget'];
			echo $args['before_title'] . $title . $args['after_title'];
			include( muut()->getPluginPath() . 'views/widgets/widget-latest-comments.php' );
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
			include( muut()->getPluginPath() . 'views/widgets/admin-widget-latest-comments.php' );
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

			$instance['number_of_comments'] = !empty( $new_instance['number_of_comments'] ) ? $new_instance['number_of_comments'] : '5';

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
			// Check if a WP post exists in the database that would match the path of the reply request.

			// If so, make sure it is a post with commenting enabled.

			// Add/update a meta for the post with the time of the last comment.

			// Update the transient with array of the posts and their data for the "latest comments."

		}

		/**
		 * Refreshes the Latest Posts array transient.
		 *
		 * @param int $number_of_posts The number of posts to set in the transient.
		 * @return array The new transient array/value.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function refreshLatestPostsTransient( $number_of_posts = 5 ) {
			$number_of_posts = is_numeric( $number_of_posts ) ? $number_of_posts : 5;

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

			// Use the returned posts to generate the new transient data.
		}
	}
}