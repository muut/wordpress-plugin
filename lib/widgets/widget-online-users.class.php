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

			// Render widget.
			echo $args['before_widget'];
			echo $args['before_title'] . __( 'Online Users', 'muut' ) . $args['after_title'];
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
			$instance['show_anonymous'] = !empty( $new_instance['show_anonymous'] ) ? $new_instance['show_anonymous'] : '0';

			return $instance;
		}
	}
}