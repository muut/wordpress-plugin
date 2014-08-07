<?php
/**
 * The Muut Trending Posts widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Widget_Trending_Posts' ) ) {
	/**
	 * Muut Trending Posts widget class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0.2
	 */
	class Muut_Widget_Trending_Posts extends WP_Widget {

		const CURRENT_CHANNELS_OPTION_NAME = 'muut_forum_channels';

		/**
		 * @property array The instance array of settings.
		 */
		protected $widget_instance;

		/**
		 * The class constructor.
		 *
		 * @return Muut_Widget_Trending_Posts
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		function __construct() {
			parent::__construct(
				'muut_trending_posts_widget',
				__( 'Muut Trending Posts', 'muut' ),
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
		 * @since 3.0.2
		 */
		public function addActions() {
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
		 * @since 3.0.2
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
		 * @since 3.0.2
		 */
		public function widget( $args, $instance ) {
			// Make sure webhooks are active, or don't bother.
			if ( !muut_is_webhooks_active() || apply_filters( 'muut_hide_trending_posts_widget_display', false ) ) {
				return;
			}
			$title = isset( $instance['title'] ) ? $instance['title'] : '';

			$date_since_string = apply_filters( 'muut_widget_trending_posts_since', "-1 week", $this->id );

			if ( isset( $instance['channels'] ) ) {
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
					'date_query' => array(
						array(
							'column' => 'post_modified_gmt',
							'after' => $date_since_string,
						),
					),
					'orderby' => 'comment_count meta_value_num',
					'meta_key' => 'muut_thread_likes',
					'order' => 'DESC',
					'posts_per_page' => $instance['number_of_posts'],
				);

				$posts_query = new WP_Query( $query_args );

				add_filter( 'posts_orderby', array( $this, 'trendingPostsOrderby' ) );

				$trending_posts = $posts_query->get_posts();

				// Render widget.
				echo $args['before_widget'];
				echo $args['before_title'] . $title . $args['after_title'];
				include( muut()->getPluginPath() . 'views/widgets/widget-trending-posts.php' );
				echo $args['after_widget'];
			}
		}

		/**
		 * Render the admin form for widget customization.
		 *
		 * @param array $instance The widget instance parameters.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		public function form( $instance ) {
			if ( muut_is_webhooks_active() ) {
				include( muut()->getPluginPath() . 'views/widgets/admin-widget-trending-posts.php' );
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
		 * @since 3.0.2
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

			if ( empty( $instance['channels'] ) ) {
				$instance['channels'] = $forum_channels_list;
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
		 * @since 3.0.2
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
		 * @since 3.0.2
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
		 * @since 3.0.2
		 */
		protected function storeChannelList( $channel_list ) {
			if ( is_array( $channel_list ) ) {
				return update_option( self::CURRENT_CHANNELS_OPTION_NAME, $channel_list );
			}
			return false;
		}

		/**
		 * Enqueues the JS required for this widget.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		public function enqueueWidgetScripts() {
			if ( muut_is_webhooks_active() ) {
				wp_enqueue_script( 'muut-widget-trending-posts', muut()->getPluginUrl() . 'resources/muut-widget-trending-posts.js', array( 'jquery', 'muut-widgets-initialize' ), Muut::VERSION, true );
			}
		}

		/**
		 * Get the currently stored channel list.
		 *
		 * @return array The array of currently stored "Channels".
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		public function getCurrentChannelsOption() {
			return get_option( self::CURRENT_CHANNELS_OPTION_NAME, array() );
		}

		/**
		 * Filter the orderby statement to order by comment count first and THEN number of likes as backup (for same comment count).
		 *
		 * @param string $orderby The current orderby statement.
		 * @return string The filtered orderby statement.
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		public function trendingPostsOrderby( $orderby ) {
			global $wpdb;
			$orderby = "{$wpdb->posts}.comment_count DESC, {$wpdb->postmeta}.meta_value+0 DESC";

			remove_filter( 'posts_orderby', array( $this, 'trendingPostsOrderby' ) );

			return $orderby;
		}
	}
}