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
			// Add the contextual help tab items for the main Muut settings page.
			add_action( 'load-toplevel_page_muut', array( $this, 'addMuutSettingsHelp' ) );

			// Add the contextual help tab items for the post editor (the Muut meta box).
			add_action( 'load-post.php', array( $this, 'addMuutPostEditorHelp' ) );
			add_action( 'load-post-new.php', array( $this, 'addMuutPostEditorHelp' ) );
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

			$help_general_tab_args = array(
				'id' => 'muut_settings_help_general_tab',
				'title' => __( 'General', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpGeneralTabContent' ),
			);

			$help_commenting_tab_args = array(
				'id' => 'muut_settings_help_commenting_tab',
				'title' => __( 'Commenting', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpCommentingTabContent' ),
			);

			$help_sso_tab_args = array(
				'id' => 'muut_settings_help_sso_tab',
				'title' => __( 'Single Sign-On', 'muut' ),
				'callback' => array( $this, 'renderSettingsHelpSsoTabContent' ),
			);

			// Add the help tabs for the Muut Settings page.
			$screen->add_help_tab( $help_overview_tab_args );

			$screen->add_help_tab( $help_general_tab_args );

			$screen->add_help_tab( $help_commenting_tab_args );

			$screen->add_help_tab( $help_sso_tab_args );

			// Set the "For More Information" sidebar up as well.
			ob_start();
				include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-sidebar.php' );
			$settings_help_sidebar_content = ob_get_clean();

			$screen->set_help_sidebar( $settings_help_sidebar_content );
		}

		/**
		 * Renders the content for the Overview help tab on the main Muut settings page.
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
		 * Renders the content for the General help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpGeneralTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-general.php' );
		}

		/**
		 * Renders the content for the Commenting help tab on the main Muut settings page.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderSettingsHelpCommentingTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-settings-commenting.php' );
		}

		/**
		 * Renders the content for the SSO help tab on the main Muut settings page.
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

		/**
		 * Adds the contextual help tab items to the post/page editing page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addMuutPostEditorHelp() {
			$screen = get_current_screen();

			$tabs = Muut_Admin_Post_Editor::instance()->getMetaBoxTabsForCurrentPostType();

			$help_tabs = array(
				'commenting-tab' => array(
					'id' => 'muut_post_editor_help_commenting_tab',
					'title' => __( 'Muut Commenting', 'muut' ),
					'callback' => array( $this, 'renderPostEditorHelpCommentingTabContent' ),
				),
				'channel-tab' => array(
					'id' => 'muut_post_editor_help_channel_tab',
					'title' => __( 'Muut Channel', 'muut' ),
					'callback' => array( $this, 'renderPostEditorHelpChannelTabContent' ),
				),
				'forum-tab' => array(
					'id' => 'muut_post_editor_help_forum_tab',
					'title' => __( 'Muut Forum', 'muut' ),
					'callback' => array( $this, 'renderPostEditorHelpForumTabContent' ),
				),
			);

			// Add the help tabs for the post editor, depending on which tabs are visible/being used.
			foreach ( $tabs as $tab ) {
				$screen->add_help_tab( $help_tabs[$tab['name']] );
			}
		}

		/**
		 * Renders the content for the Commenting-meta-box-tab help tab on the post editor.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderPostEditorHelpCommentingTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-post-editor-commenting.php' );
		}

		/**
		 * Renders the content for the Channel-meta-box-tab help tab on the post editor.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderPostEditorHelpChannelTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-post-editor-channel.php' );
		}

		/**
		 * Renders the content for the Forum-meta-box-tab help tab on the post editor.
		 *
		 * @param WP_Screen $screen The current screen.
		 * @param array $tab The current tab array.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderPostEditorHelpForumTabContent( $screen, $tab ) {
			include( muut()->getPluginPath() . 'views/blocks/help-tab-post-editor-forum.php' );
		}
	}
}