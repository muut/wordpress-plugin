<?php
/**
 * The class that is responsible for all functionality regarding the Muut settings.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Admin_Settings' ) ) {

	/**
	 * Muut Admin Settings class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Admin_Settings
	{
		/**
		 * @static
		 * @property Muut_Admin_Settings The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Admin_Settings The instance.
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
		 * @return Muut_Admin_Settings
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions regarding the admin settings functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addActions() {
			add_action( 'load-toplevel_page_' . Muut::SLUG, array( $this, 'saveSettings' ) );
		}

		/**
		 * The method for adding all filters regarding the admin settings.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addFilters() {

		}

		/**
		 * Saves the settings specified on the Muut settings page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function saveSettings() {
			if ( isset( $_POST['muut_settings_save'] )
				&& $_POST['muut_settings_save'] == 'true'
				&& check_admin_referer( 'muut_settings_save', 'muut_settings_nonce' )
			) {

				$settings = $this->settingsValidate( $_POST['setting'] );

				// Save all the options by passing an array into setOption.
				if ( muut()->setOption( $settings ) ) {
					// Display success notice if they were updated or matched the previous settings.
					muut()->queueAdminNotice( 'updated', __( 'Settings successfully saved.', 'muut' ) );
				} else {
					// Display error if the settings failed to save.
					muut()->queueAdminNotice( 'error', __( 'Failed to save settings.', 'muut' ) );
				}
			}
		}

		/**
		 * Deals with settings-specific validation functionality.
		 *
		 * @param array $settings an array of key => value pairs that define what settings are being changed to.
		 * @return array A modified array defining the settings after validation.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		protected function settingsValidate( $settings ) {

			if ( isset( $_POST['initial_save'] ) ) {
				return apply_filters( 'muut_settings_initial_save', apply_filters( 'muut_settings_validated', $settings ) );
			}

			$boolean_settings = apply_filters( 'muut_boolean_settings', array(
				'replace_comments',
				'use_threaded_commenting',
				'override_all_comments',
				'is_threaded_default',
				'show_online_default',
				'allow_uploads_default',
				'subscription_use_sso',
				'enable_proxy_rewrites',
				'use_custom_s3_bucket',
			) );

			foreach ( $boolean_settings as $boolean_setting ) {
				$settings[$boolean_setting] = isset( $settings[$boolean_setting]) ? $settings[$boolean_setting] : '0';
			}

			if ( ( isset( $settings['forum_name'] ) && $settings['forum_name'] != muut()->getForumName() )
				|| ( isset( $settings['enable_proxy_rewrites'] ) && $settings['enable_proxy_rewrites'] != muut()->getOption( 'enable_proxy_rewrites' ) )
				|| ( isset( $settings['use_custom_s3_bucket'] ) && (
						$settings['use_custom_s3_bucket'] != muut()->getOption( 'use_custom_s3_bucket' )
						|| $settings['custom_s3_bucket_name'] != muut()->getOption( 'custom_s3_bucket_name' ) ) ) ) {
				flush_rewrite_rules( true );
			}

			// If the Secret Key setting does not get submitted (i.e. is disabled), make sure to erase its value.
			$settings['subscription_secret_key'] = isset( $settings['subscription_secret_key']) ? $settings['subscription_secret_key'] : '';

			foreach ( $settings as $name => $value ) {
				apply_filters( 'muut_validate_setting_' . $name, $value );
				apply_filters( 'muut_validate_setting', $value, $name );
			}

			return apply_filters( 'muut_settings_validated', $settings );
		}
	}
}