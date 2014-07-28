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

		const CURRENT_CHANNELS_OPTION_NAME = 'muut_forum_channels';

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
			// Update the transient data when a post is liked, unliked, or replied to.
			//add_action( 'muut_webhook_request', array( $this, 'updateWidgetData' ), 100, 2 );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueWidgetScripts' ), 12 );
			add_action( 'wp_print_scripts', array( $this, 'printWidgetJs' ) );

			// Receive the AJAX action for storing the current channels
			add_action( 'wp_ajax_muut_store_current_channels', array( $this, 'ajaxStoreChannelList') );

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

			$query_args = array(
				'post_type' => Muut_Custom_Post_Types::MUUT_THREAD_CPT_NAME,
				'post_status' => Muut_Custom_Post_Types::MUUT_PUBLIC_POST_STATUS,
				'meta_query' => array(
					array(
						'key' => 'muut_channel_path',
						'value' => array_keys( $instance['channels'] ),
						'compare' => 'IN',
					),
				),
				'orderby' => 'comment_count meta_value_num',
				'meta_key' => 'muut_thread_likes',
				'order' => 'DESC',
				'posts_per_page' => $instance['number_of_posts'],
			);

			$posts_query = new WP_Query( $query_args );

			add_filter( 'posts_orderby', array( $this, 'popularPostsOrderby' ) );

			$popular_posts = $posts_query->get_posts();

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

			$forum_channels_list = $this->getCurrentChannelsOption();
			foreach( $new_instance['channels'] as $channel_value ) {
				if ( in_array( $channel_value, array_keys( $forum_channels_list ) ) ) {
					$instance['channels'][$channel_value] = $forum_channels_list[$channel_value];
				} else {
					$instance['channels'][$channel_value] = $channel_value;
				}
			}
			return $instance;
		}


		/********
		 * CUSTOM WIDGET METHODS
		 ********/

		/**
		 * If on the main forum page, print/pass the currently stored "channel list" as a JS variable for comparison.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function printWidgetJs() {
			if ( muut_is_webhooks_active() && !is_admin() && get_post() && muut_get_forum_page_id() == get_the_ID() && current_user_can( 'edit_theme_options' ) ) {
				$json = json_encode( $this->getCurrentChannelsOption() );

				echo '<script type="text/javascript">';
				echo 'var muut_stored_channel_list = ' . $json . ';';
				echo 'var muut_stored_channels_nonce = "' . wp_create_nonce( 'muut_stored_channels_request' ) . '";';
				echo '</script>';
			}
			// Only print the stuff once.
			remove_action( 'wp_print_scripts', array( $this, 'printWidgetJs' ) );
		}

		/**
		 * Receive the AJAX action to store the current channel list.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function ajaxStoreChannelList() {
			if ( check_ajax_referer( 'muut_stored_channels_request', 'security' ) && isset( $_POST['channel_list'] ) ) {
				if ( $this->storeChannelList( $_POST['channel_list'] ) ) {
					echo 'Stored successfully.';
					remove_action( 'wp_ajax_muut_store_current_channels', array( $this, 'ajaxStoreChannelList') );
				}
			}
			die(0);
		}

		/**
		 * Store a channel list.
		 *
		 * @param array $channel_list An array of channels of the form ['path'] => 'Channel Name'.
		 * @return bool Whether it was stored successfully or not.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function storeChannelList( $channel_list ) {
			if ( is_array( $channel_list ) ) {
				return update_option( self::CURRENT_CHANNELS_OPTION_NAME, $channel_list );
			}
			return false;
		}

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

			// Only execute actions on specific events.
			if ( $event == 'reply' ) {
				$path = $request['path'];
				$user = $request['post']->user;
			} elseif ( $event == 'like' ) {
				$path = $request['location']->path;
				$user = $request['thread']->user;
			} elseif ( $event == 'unlike' ) {
				$path = $request['location']->path;
				$user = $request['thread']->user;
			} elseif ( $event == 'remove' ) {
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
		 * Enqueues the JS required for this widget.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function enqueueWidgetScripts() {
			if ( muut_is_webhooks_active() ) {
				wp_enqueue_script( 'muut-widget-popular-posts', muut()->getPluginUrl() . 'resources/muut-widget-popular-posts.js', array( 'jquery', 'muut-widgets-initialize' ), Muut::VERSION, true );
			}
		}

		/**
		 * Get the currently stored channel list.
		 *
		 * @return array The array of currently stored "Channels".
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getCurrentChannelsOption() {
			return get_option( self::CURRENT_CHANNELS_OPTION_NAME, array() );
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

		/**
		 * Filter the orderby statement to order by comment count first and THEN number of likes as backup (for same comment count).
		 *
		 * @param string $orderby The current orderby statement.
		 * @return string The filtered orderby statement.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function popularPostsOrderby( $orderby ) {
			global $wpdb;
			$orderby = "{$wpdb->posts}.comment_count DESC, {$wpdb->postmeta}.meta_value+0 DESC";

			remove_filter( 'posts_orderby', array( $this, 'popularPostsOrderby' ) );

			return $orderby;
		}
	}
}