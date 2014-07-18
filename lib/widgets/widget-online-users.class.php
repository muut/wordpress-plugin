<?php
/**
 * The Online Users widget.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Widget_Online_Users' ) ) {
	/**
	 * Muut Online Users widget class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Widget_Online_Users extends WP_Widget {

		/**
		 * The class constructor.
		 *
		 * @return Muut_Widget_Online_Users
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		function __construct() {
			parent::__construct(
				'muut_online_users_widget',
				__( 'Muut Online Users', 'muut' ),
				array(
					'description' => __( 'Use this to show the online users in a widget.', 'muut' ),
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
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueWidgetScripts' ), 11 );
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
			// Make sure the Muut resources get loaded (only stuff in the footer will work, as this happens
			// partway through page load.
			add_filter( 'muut_requires_muut_resources', '__return_true' );
			muut()->enqueueFrontendScripts();

			$title = isset( $instance['title'] ) ? $instance['title'] : '';

			$num_online_html = '';
			if ( $instance['show_number_online'] && !empty( $title ) ) {
				$num_online_html = '<span class="num-logged-in"></span>';
			}

			// Render widget.
			echo $args['before_widget'];
			echo $args['before_title'] . $title . $num_online_html . $args['after_title'];
			include( muut()->getPluginPath() . 'views/widgets/widget-online-users.php' );
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
			include( muut()->getPluginPath() . 'views/widgets/admin-widget-online-users.php' );
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

			$instance['show_number_online'] = !empty( $new_instance['show_number_online'] ) ? $new_instance['show_number_online'] : '0';
			$instance['show_anonymous'] = !empty( $new_instance['show_anonymous'] ) ? $new_instance['show_anonymous'] : '0';

			return $instance;
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
				wp_enqueue_script( 'muut-widget-online-users', muut()->getPluginUrl() . 'resources/muut-widget-online-users.js', array( 'jquery', 'muut-frontend-functions' ), Muut::VERSION, true );

				// Localization translation strings.
				$localizations = array(
					'anonymous_users' => _x( 'anonymous', 'anonymous users', 'muut' ),
				);
				wp_localize_script( 'muut-widget-online-users', 'muut_widget_online_users_localized', $localizations );
			}
		}
	}
}