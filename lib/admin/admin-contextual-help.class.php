<?php
/**
 * The class that is responsible for Adding the contextual help menu to the applicable admin screens.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Admin_Contextual_Help' ) ) {

	/**
	 * Muut Admin Contextual Help class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Admin_Contextual_Help
	{
		/**
		 * @static
		 * @property Muut_Admin_Contextual_Help The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Admin_Contextual_Help The instance.
		 * @author Paul Hughes
		 * @since  3.0
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
		 * @return Muut_Admin_Contextual_Help
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions regarding the admin contextual help menu functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addActions() {
			add_action( 'load-toplevel_page_muut', array( $this, 'addMuutSettingsHelp' ) );
		}

		/**
		 * The method for adding all filters regarding the admin contextual help menu.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addFilters() {

		}

		/**
		 * Adds the contextual help tab to the main Muut settings page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addMuutSettingsHelp() {
			$screen = get_current_screen();

			$help_overview_tab_args = array(
				'id' => 'muut_settings_help_overview_tab',
				'title' => __( 'Overview', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpOverviewTabContent' ),
			);

			$help_sso_tab_args = array(
				'id' => 'muut_settings_help_sso_tab',
				'title' => __( 'Single Sign-On', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpSsoTabContent' ),
			);

			$screen->add_help_tab( $help_overview_tab_args );

			$screen->add_help_tab( $help_sso_tab_args );

			ob_start();
				include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-sidebar.php' );
			$settings_help_sidebar_content = ob_get_clean();

			$screen->set_help_sidebar( $settings_help_sidebar_content );
		}

		/**
		 * Renders the content for the help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpOverviewTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-overview.php' );
		}

		/**
		 * Renders the content for the help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpSsoTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-sso.php' );
		}
	}
}