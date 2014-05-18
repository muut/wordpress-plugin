<?php
/**
 * The singleton class that contains all functionality regarding an upgrade of the plugin from an older version.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Updater' ) ) {

	/**
	 * Muut Updater class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Updater
	{
		/**
		 * @static
		 * @property Muut_Updater The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property array The array of version numbers for which an update action must be called. Filterable.
		 */
		protected $versionThresholds;

		/**
		 * @property string The old version from which we are updating.
		 */
		protected $oldVersion;

		/**
		 * @property string The new version to which we are updating.
		 */
		protected $newVersion;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Updater The instance.
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
		 * @return Muut_Updater
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
			$this->oldVersion = muut()->getOption( 'current_version', '0' );
			$this->newVersion = Muut::VERSION;

			$version_thresholds = apply_filters( 'muut_updater_version_thresholds', array(
				'2.0.13',
				'3.0',
			) );
			natsort( $version_thresholds );
			$this->versionThresholds = $version_thresholds;
		}

		/**
		 * Adds the actions used by this class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addActions() {
			add_action( 'admin_init', array( $this, 'doVersionThresholdActions' ), 11 );
			add_action( 'muut_plugin_update', array( $this, 'updateOptions' ), 10, 1 );
			add_action( 'muut_after_plugin_update_actions', array( $this, 'updateVersionNumber' ), 10, 2 );
		}

		/**
		 * Adds the filters used by this class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addFilters() {

		}

		/**
		 * Adds the do_action calls for the important version thresholds such that they can be hooked into
		 * so that specific update functionality can be called for each threshold.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function doVersionThresholdActions() {
			do_action( 'muut_before_plugin_update_actions', $this->oldVersion, $this->newVersion );
			foreach ( $this->versionThresholds as $version ) {
				if (version_compare( $this->oldVersion, $version, '<' ) ) {
					do_action( 'muut_plugin_update', $version );
				}
			}
			do_action( 'muut_after_plugin_update_actions', $this->oldVersion, $this->newVersion );
		}

		/**
		 * Updates old site options to new format, if necessary.
		 *
		 * @param string $to_version Version threshold we are updating "to". So, old versions of plugin BELOW this
		 *                        	 parameter will have their options updated "to" that version, and so on until
		 *                           the current version.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function updateOptions( $to_version ) {
			switch ( $to_version ) {
				case '2.0.13':

					update_option( 'muut_forum_name', get_option( 'moot_forum_name', '' ) );
					update_option( 'muut_api_key', get_option( 'moot_api_key', '' ) );
					update_option( 'muut_secret_key', get_option( 'moot_secret_key', '' ) );
					update_option( 'muut_language', get_option( 'moot_language', '' ) );
					update_option( 'muut_generate', get_option( 'moot_generate', '' ) );
					update_option( 'muut_comments_under_forums', get_option( 'moot_comments_under_forums', '' ) );

					delete_option( 'moot_forum_name' );
					delete_option( 'moot_api_key' );
					delete_option( 'moot_secret_key' );
					delete_option( 'moot_language' );
					delete_option( 'moot_generate' );
					delete_option( 'moot_comments_under_forums' );
				break;

				case '3.0':
					if ( get_option( 'muut_forum_name', '' ) != '' && $this->oldVersion == '0' ) {
						$this->oldVersion = '2.0.13';
					}

					$new_settings = array(
						'forum_name' => get_option( 'muut_forum_name', '' ),
						'language' => get_option( 'muut_language', '' ),
						'replace_comments' => get_option( 'muut_generate', '' ),
						'show_comments_in_forum_default' => get_option( 'muut_comments_under_forums', '' ),
						'subscription_api_key' => get_option( 'muut_api_key', '' ),
						'subscription_secret_key' => get_option( 'muut_secret_key', '' ),
						'subscription_use_sso' => get_option( 'muut_api_key', false ) && get_option( 'muut_secret_key', false ) ? true : false,
						'comments_base_domain' => $this->oldVersion == '0' ? $_SERVER['SERVER_NAME'] : 'wordpress',
					);

					// muut()->setOptions() is a protected method, so we have to do it one-by-one.
					foreach( $new_settings as $setting => $value ) {
						if ( $value != '' ) {
							$value = ( $value === 'true' ) ? 1 : $value;
							muut()->setOption( $setting, $value );
						}
					}

					delete_option( 'muut_forum_name' );
					delete_option( 'muut_language' );
					delete_option( 'muut_generate' );
					delete_option( 'muut_api_key' );
					delete_option( 'muut_secret_key' );
					delete_option( 'muut_comments_under_forums' );
				break;
			}
		}

		/**
		 * Updates the current version option for the installed plugin.
		 *
		 * @param string $old_version The version from which we are upgrading.
		 * @param string $new_version The version to which we are upgrading.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function updateVersionNumber( $old_version, $new_version ) {
			muut()->setOption( 'current_version', $new_version );
		}
	}
}