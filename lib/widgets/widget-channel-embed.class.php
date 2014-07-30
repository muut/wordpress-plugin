<?php
/**
 * The Individual Channel Embed widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Widget_Channel_Embed' ) ) {
	/**
	 * Muut Discussion Channel Embed widget class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Widget_Channel_Embed extends WP_Widget {

		/**
		 * The class constructor.
		 *
		 * @return Muut_Widget_Channel_Embed
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		function __construct() {
			parent::__construct(
				'muut_channel_embed_widget',
				__( 'Muut Discussion Channel', 'muut' ),
				array(
					'description' => __( 'Use this to embed a specific channel in a widget area.', 'muut' ),
				)
			);

			$this->addActions();
			$this->addFilters();
		}

		/**
		 * Adds actions related to this widget.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function addActions() {
			add_action( 'init', array( $this, 'maybeRequireMuutResources') );
		}

		/**
		 * Adds filters related to this widget.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function addFilters() {

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
			if ( ( Muut_Post_Utility::getForumPageId() == get_the_ID() && !apply_filters( 'muut_force_online_widget_display', false ) ) || apply_filters( 'muut_hide_channel_embed_display', false ) ) {
				return;
			}
			// Default to always NOT showing online users. Can be modified with filter.
			$embed_args['show-online'] = apply_filters( 'muut_channel_embed_widget_show_online', false, $args, $instance );

			if ( isset( $instance['disable_uploads'] ) ) {
				$embed_args['allow-uploads'] = !$instance['disable_uploads'] ? 'true' : 'false';
			}

			$embed_args['share'] = 'false';

			$embed_args['title'] = isset( $instance['title'] ) ? $instance['title'] : '';
			$embed_args['channel'] = $instance['title'] ? $instance['title'] : '';
			$path = $instance['muut_path'];

			// Render widget.
			echo $args['before_widget'];
			echo $args['before_title'] . $embed_args['title'] . $args['after_title'];
			echo '<div id="muut-widget-channel-embed-wrapper" class="muut_widget_wrapper muut_widget_channel_embed_wrapper">';
			Muut_Channel_Utility::getChannelEmbedMarkup( $path, $embed_args, true );
			echo '</div>';
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
			include( muut()->getPluginPath() . 'views/widgets/admin-widget-channel-embed.php' );
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
			$instance['disable_uploads'] = !empty( $new_instance['disable_uploads'] ) ? $new_instance['disable_uploads'] : '0';

			// Make sure that the path is saved as SOMETHING.
			$default_path = isset( $old_instance['muut_path'] ) ? $old_instance['muut_path'] : '';
			$default_path = empty( $default_path ) ? sanitize_title( $instance['title'] ) : $default_path;
			$default_path = empty( $default_path ) ? sanitize_title( $this->get_field_id( 'muut_path' ) ) : $default_path;
			$default_path = !isset( $old_instance['muut_path'] ) ? '' : $default_path;
			$instance['muut_path'] = !empty( $new_instance['muut_path'] ) ? Muut_Post_Utility::sanitizeMuutPath( $new_instance['muut_path'] ) : $default_path;

			return $instance;
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
	}
}