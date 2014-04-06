<?php
/**
 * The main class file for the Muut plugin.
 *
 * @package   Muut
 * @copyright 2014 Moot Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut' ) ) {

	/**
	 * Muut main class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut
	{

		/**
		 * The current version of urGuru
		 */
		const VERSION = '3.0';

		/**
		 * The plugin slug, for all intents and purposes.
		 */
		const SLUG = 'muut';

		/**
		 * The option name for the Muut plugin.
		 */
		const OPTIONNAME = 'muut_options';

		/**
		 * The Muut server location.
		 */
		const MUUTSERVERS = 'moot.it';

		/**
		 * @static
		 * @property Muut The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property string The directory of the plugin.
		 */
		protected $pluginDir;

		/**
		 * @property string The plugin path.
		 */
		protected $pluginPath;

		/**
		 * @property string The url of the plugin.
		 */
		protected $pluginUrl;

		/**
		 * @property array The plugin options array.
		 */
		protected $options;

		/**
		 * @property array An array of admin notices to render.
		 */
		protected $adminNotices;

		/**
		 * @property array The array of languages available for Muut.
		 */
		protected $languages;

		/**
		 * The singleton method.
		 *
		 * @return Muut The instance.
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
		 * @return Muut
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->pluginDir = trailingslashit( basename( dirname( dirname( __FILE__ ) ) ) );
			$this->pluginPath = trailingslashit( dirname( dirname( __FILE__ ) ) );
			$this->pluginUrl = trailingslashit( plugins_url( '', dirname( __FILE__ ) ) );
			$this->adminNotices = array();

			$this->addActions();
			$this->addFilters();
			$this->loadLibraries();
		}

		/**
		 * Adds the main actions for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addActions() {
			add_action( 'admin_init', array( $this, 'maybeSetVersion' ) );
			add_action( 'admin_init', array( $this, 'maybeAddRewriteRules' ) );
			add_action( 'admin_menu', array( $this, 'createAdminMenuItems' ) );
			add_action( 'admin_notices', array( $this, 'renderAdminNotices' ) );
			add_action( 'add_meta_boxes_page', array( $this, 'addPageMetaBoxes' ) );
			add_action( 'load-' . self::SLUG . '_page_muut_settings', array( $this, 'saveSettings' ) );
			add_action( 'flush_rewrite_rules_hard', array( $this, 'removeRewriteAdded' ) );
			add_action( 'save_post_page', array( $this, 'saveForumPage' ), 10, 2 );

			add_action( 'init', array( $this, 'registerScriptsAndStyles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendScripts' ) );
		}

		/**
		 * Adds the main filters for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addFilters() {

		}

		/**
		 * Determines whether a given admin function should continue to execute based on the current screen id.
		 *
		 * @param string $screen_id What the id of the screen should be.
		 * @return bool True if the screen id does not match (as in "YES, bail"); false if good to go.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		protected function adminBail( $screen_id ) {
			if ( get_current_screen()->id != $screen_id ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Load the necessary files for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function loadLibraries() {

			// Load the template tags.
			require_once( $this->pluginPath . 'public/template_tags.php' );

			// Load the initializer class.
			require_once( $this->pluginPath . 'lib/initializer.class.php' );
			Muut_Initializer::instance();
		}

		/**
		 * Gets the plugin directory.
		 *
		 * @return string The plugin directory.
		 */
		public function getPluginDir() {
			return $this->pluginDir;
		}

		/**
		 * Gets the plugin absolute path.
		 *
		 * @return string The plugin path.
		 */
		public function getPluginPath() {
			return $this->pluginPath;
		}

		/**
		 * Gets the plugin URL.
		 *
		 * @return string The plugin URL.
		 */
		public function getPluginUrl() {
			return $this->pluginUrl;
		}

		/**
		 * Gets the remote forum name that is registered.
		 *
		 * @return string The remote forum name.
		 */
		public function getRemoteForumName() {
			return $this->getOption( 'remote_forum_name', '' );
		}

		/**
		 * Adds the rewrite rules for indexing Muut posts locally if they are currently not already set.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function maybeAddRewriteRules() {
			if ( $this->getOption( 'remote_forum_name', '' ) !== '' && !$this->getOption( 'added_rewrite_rules', false ) ) {
				$this->addProxyRewrites();
			}
		}

		/**
		 * If using DEFAULT permalinks (i.e. no permalinks), we need to still add the rewrite such that data is
		 * indexed properly.
		 * Much of the design of this method comes from wp-admin/includes/misc.php, save_mod_rewrite_rules() function.
		 * If we are not using defaults, lets make sure to filter in proper rewrites by adding the filter and then
		 * flushing the rules (so it gets executed).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addProxyRewrites() {
			if ( get_option( 'permalink_structure', '') == '' ) {

				$home_path = get_home_path();
				$htaccess_file = $home_path.'.htaccess';

				$rules = array(
					'<IfModule mod_rewrite.c>',
					'RewriteEngine On',
					'RewriteBase /',
					'RewriteRule ^i/(' . $this->getOption( 'remote_forum_name' ) . ')(/.*)?$ http://' . self::MUUTSERVERS . '/i/$1$2 [P]',
					'RewriteRule ^m/(.*)$ http://' . self::MUUTSERVERS . '/m/$1 [P]',
					'</IfModule>',
				);

				if ( ( !file_exists( $htaccess_file ) && is_writable( $home_path ) ) || is_writable( $htaccess_file ) ) {
					insert_with_markers( $htaccess_file, 'WordPress', $rules );
				}
				$this->setOption( 'added_rewrite_rules', true );
			} else {
				add_filter( 'mod_rewrite_rules', array( $this, 'addProxyRewritesFilter' ) );
				flush_rewrite_rules( true );
			}
		}

		/**
		 * Adds the necessary rewrite rules to fix SEO issues such that Muut posts are indexed at this site
		 * even though the content is hosted on Muut's servers.
		 *
		 * @param string $rules The current string containing all of the re-write structure block.
		 * @return string The modified rewrite block (as a long string).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addProxyRewritesFilter( $rules ) {
			$permastruct = get_option( 'permalink_structure', '' );

			$muut_rules = "RewriteRule ^i/(" . $this->getOption( 'remote_forum_name' ) . ")(/.*)?\$ http://" . self::MUUTSERVERS . "/i/\$1\$2 [P]\n";
			$muut_rules .=	"RewriteRule ^m/(.*)$ http://" . self::MUUTSERVERS . "/m/\$1 [P]";

			if ( $permastruct == '' ) {
				$rules .= "<IfModule mod_rewrite.c>\n";
				$rules .= "RewriteEngine On\n";
				$rules .= "RewriteBase /\n";
				$rules .= $muut_rules . "\n";
				$rules .= "</IfModule>\n";
			} else {
				$split_rules = explode( "\n", $rules );
				$rule_before_index = array_search( 'RewriteRule ^index\.php$ - [L]', $split_rules );
				array_splice( $split_rules, $rule_before_index, 0, $muut_rules );
				$rules = implode( "\n", $split_rules ) . "\n";
			}

			$this->setOption( 'added_rewrite_rules', true );
			return $rules;
		}

		/**
		 * Removes the plugin setting that says we have added the necessary rewrite rules.
		 *
		 * @param bool $hard_flush Whether we are undergoing a hard flush or not.
		 * @return bool The same variable as the parameter passed in (we are treating this like and action).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function removeRewriteAdded( $hard_flush ) {
			if ( $hard_flush ) {
				$this->setOption( 'added_rewrite_rules', false );
				// Make have the rewrites double checked at top of page (in case we were in the middle of initial flush).
				add_action( 'admin_head', array( $this, 'maybeAddRewriteRules' ) );
			}
			return $hard_flush;
		}

		/**
		 * Gets the Muut language equivalent of a given WordPress language abbreviation.
		 *
		 * @param string $muut_lang The WordPress abbreviation for a given language.
		 * @return string The Muut equivalent language string.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getMuutLangEquivalent( $wp_lang ) {
			// Format is muut_lang => wp_lang.
			$muut_langs = apply_filters( 'muut_and_wp_langs', array(
				'ar' => 'ar',
				'pt_BR' => 'pt-br',
				'bg_BG' => 'bg',
				'zh_CN'=> 'ch',
				'zh_TW' => 'tw',
				'nl_NL' => 'nl',
				'en_US' => 'en',
				'fi' => 'fi',
				'fr_FR' => 'fr',
				'de_DE' => 'de',
				'hu_HU' => 'hu',
				'he_IL' => 'he',
				'id_ID' => 'id',
				'ja' => 'ja',
				'ko_KR' => 'ko',
				'pl_PL' => 'pl',
				'ru_RU' => 'ru',
				'es_ES' => 'es',
				'sv_SE' => 'se',
				'ta_IN' => 'ta',
				'tr_TR' => 'tr',
			) );

			if ( in_array( $wp_lang, array_keys( $muut_langs ) ) ) {
				return $muut_langs[$wp_lang];
			} else {
				return 'en';
			}
		}

		/**
		 * Registers the various scripts and styles that we may be using in the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function registerScriptsAndStyles() {
			wp_register_script( 'muut', '//cdn.moot.it/1/moot.' . $this->getOption( 'language', 'en' ) . '.min.js', array( 'jquery' ), '1', true );
			wp_register_script( 'muut-admin-functions', $this->pluginUrl . 'resources/admin-functions.js', array( 'jquery' ), '1.0', true );
		}

		/**
		 * Gets the default value of a given option.
		 *
		 * @param string $option_name Gets the default value of a given Muut option.
		 * @return array The array of default values for the Muut options.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		protected function getOptionsDefaults() {

			$default_lang = 'en';

			if ( defined( 'WPLANG' ) ) {
				$default_lang = $this->getMuutLangEquivalent( WPLANG );
			}

			$defaults = apply_filters( 'muut_options_defaults', array(
				'remote_forum_name' => '',
				// TODO: Make this match whatever language is set for the site.
				'language' => $default_lang,
				'replace_comments' => false,
				'forum_page_defaults' => array(
					'is_threaded' => false,
					'show_online' => true,
					'allow_uploads' => false,
				),
			) );

			return $defaults;
		}

		/**
		 * Enqueues the admin-side scripts we will be using.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueAdminScripts() {
			$screen = get_current_screen();
			if ( $screen->id == 'page' ) {
				wp_enqueue_script( 'muut-admin-functions' );
			}
		}

		/**
		 * Enqueues scripts for the frontend.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueFrontendScripts() {
			if ( Muut_Forum_Page_Utility::isForumPage( get_the_ID() ) ) {
				wp_enqueue_script( 'muut' );
			}
		}

		/**
		 * Adds the metaboxes for the Page admin editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addPageMetaBoxes() {
			add_meta_box(
				'muut-is-forum-page',
				__( 'Muut', 'muut' ),
				array( $this, 'renderMuutPageMetaBox' ),
				'page',
				'side',
				'high'
			);
		}

		/**
		 * Renders the metabox content for the Page Editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderMuutPageMetaBox() {
			include( $this->pluginPath . 'views/admin-page-metabox.php' );
		}

		/**
		 * Gets the array of possible languages.
		 *
		 * @return array The array of languages in the form of [abbrev] => [human_readable].
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getLanguages() {
			if ( !isset( $this->languages ) || is_null( $this->languages ) ){
				$this->languages = apply_filters( 'muut_languages', array(
						'ar' => __( 'Arabic', 'muut' ),
						'pt-br' => __( 'Brazil Portuguese', 'muut' ),
						'bg' => __( 'Bulgarian', 'muut' ),
						'ch' => __( 'Chinese', 'muut' ),
						'tw' => __( 'Chinese / Taiwan', 'muut' ),
						'nl' => __( 'Dutch', 'muut' ),
						'en' => __( 'English', 'muut' ),
						'fi' => __( 'Finnish', 'muut' ),
						'fr' => __( 'French', 'muut' ),
						'de' => __( 'German', 'muut' ),
						'hu' => __( 'Hungarian', 'muut' ),
						'he' => __( 'Hebrew', 'muut' ),
						'id' => __( 'Indonesian', 'muut' ),
						'ja' => __( 'Japanese', 'muut' ),
						'ko' => __( 'Korean', 'muut' ),
						'pl' => __( 'Polish', 'muut' ),
						'ru' => __( 'Russian', 'muut' ),
						'es' => __( 'Spanish', 'muut' ),
						'se' => __( 'Swedish', 'muut' ),
						'ta' => __( 'Tamil', 'muut' ),
						'tr' => __( 'Turkish', 'muut' ),
					)
				);
			}
			return $this->languages;
		}

		/**
		 * Queues an admin notice to be rendered when admin_notices is run.
		 *
		 * @param string $type The type of notice (really the class for the notice div).
		 * @param string $content The content of the admin notice.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function queueAdminNotice( $type, $content ) {
			$this->adminNotices[] = array(
				'type' => $type,
				'content' => $content,
			);
		}

		/**
		 * Renders the admin notices that have been queued.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderAdminNotices() {
			foreach ( $this->adminNotices as $notice ) {
				echo '<div class="' . $notice['type'] . '">';
				echo '<p>' . $notice['content'] . '</p>';
				echo '</div>';
			}
		}

		/**
		 * Gets and returns the plugin options (as an array).
		 *
		 * @return array The plugin options.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function getOptions() {
			if ( !isset( $this->options ) || is_null( $this->options ) ) {
				$options = get_option( self::OPTIONNAME, array() );
				$this->options = apply_filters( 'muut_get_options', $options );
			}
			return $this->options;
		}

		/**
		 * Gets and returns a specific plugin option.
		 *
		 * @param string $option_name The Muut option name.
		 * @param mixed  $default     The default value if none is returned.
		 * @return mixed The option value.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function getOption( $option_name, $default = '' ) {
			$options = $this->getOptions();

			$default_options = $this->getOptionsDefaults();

			if ( in_array( $option_name, array_keys( $default_options ) ) ) {
				$default = $default_options[$option_name];
			}

			if ( !is_string( $option_name ) )
				return false;

			$value = isset( $options[$option_name] ) ? $options[$option_name] : $default;

			return apply_filters( 'muut_get_option', $value, $option_name, $default );
		}

		/**
		 * Sets the plugin super-option array.
		 *
		 * @param array $options       The options array that we are saving.
		 * @param bool  $apply_filters Whether to apply filters to the options being saved or not.
		 * @return bool True on success, false on failure.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function setOptions( $options, $apply_filters = true ) {
			if ( !is_array( $options ) )
				return false;

			$options = $apply_filters ? apply_filters( 'muut_save_options', $options ) : $options;

			if ( update_option( self::OPTIONNAME, $options ) ) {
				$this->options = null; // Refresh options array.
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Sets an option or an array of options.
		 *
		 * @param string|array $option        The option name OR an array of option => value pairs.
		 * @param string       $value         If only option name was passed, this is the value we should be setting.
		 * @param bool         $apply_filters Whether to apply filters to the individual option(s) we are saving.
		 * @return bool True on success, false on failure.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function setOption( $option, $value = '', $apply_filters = true ) {
			if ( is_string( $option ) )
				$option = array( $option => $value );

			if ( !is_array( $option ) )
				return false;

			$current_options = $this->getOptions();

			if ( $apply_filters ) {
				// Make sure to filter each option before we save it.
				foreach ( $option as $name => $value ) {
					$option[$name] = apply_filters( 'muut_save_option-' . $name, $value );
				}
			}

			return $this->setOptions( wp_parse_args( $option, $current_options ) );
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
				$old_options = $this->getOptions();

				$settings = $this->settingsValidate( $_POST['setting'] );

				// Save all the options by passing an array into setOption.
				// Save all the options by passing an array into setOption.
				if ( $this->setOption( $settings ) || $old_options == $this->getOptions() ) {
					// Display success notice if they were updated or matched the previous settings.
					$this->queueAdminNotice( 'updated', __( 'Settings successfully saved.', 'muut' ) );
				} else {
					// Display error if the settings failed to save.
					$this->queueAdminNotice( 'error', __( 'Failed to save settings.', 'muut' ) );
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

			$boolean_settings = apply_filters( 'muut_boolean_settings', array(
				'replace_comments',
			) );

			foreach ( $boolean_settings as $boolean_setting ) {
				$settings[$boolean_setting] = isset( $settings[$boolean_setting]) ? $settings[$boolean_setting] : '0';
			}

			if ( isset( $settings['remote_forum_name'] ) && $settings['remote_forum_name'] != $this->getOption( 'remote_forum_name' ) ) {
				flush_rewrite_rules( true );
			}

			return apply_filters( 'muut_settings_validated', $settings );
		}

		/**
		 * Checks to see that the stored version number is the same as the current version number, and if not, it fires
		 * the version-chance action and updates the stored number.
		 *
		 * @return bool True if version was changed, False if not.
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function maybeSetVersion() {
			$current_version = $this->getOption( 'current_version', '0' );
			if ( version_compare( self::VERSION, $current_version ) != '0' ) {
				if ( $this->setOption( 'current_version', self::VERSION ) ) {
					do_action( 'muut_updated_current_version', self::VERSION, $current_version );
				}
			}
		}

		/**
		 * Creates the Muut admin menu section and menu items.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function createAdminMenuItems() {
			add_menu_page(
				__( 'Muut', 'muut' ),
				__( 'Muut', 'muut' ),
				'manage_options',
				self::SLUG,
				array( $this, 'renderAdminMainPage' )
			);

			add_submenu_page(
				self::SLUG,
				__( 'Muut Settings', 'muut' ),
				__( 'Settings', 'muut' ),
				'manage_options',
				'muut_settings',
				array( $this, 'renderAdminSettingsPage' )
			);

			add_submenu_page(
				self::SLUG,
				__( 'Muut Upgrades', 'muut' ),
				__( 'Upgrades', 'muut' ),
				'manage_options',
				'muut_upgrades',
				array( $this, 'renderAdminUpgradesPage' )
			);
		}

		/**
		 * Renders the Muut main menu page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function renderAdminMainPage() {
			// Confirm that this function is being called from a valid callback.
			if ( $this->adminBail( 'toplevel_page_' . self::SLUG ) ) {
				return;
			}

			include( $this->pluginPath . 'views/admin-main.php' );
		}

		/**
		 * Renders the Muut settings page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function renderAdminSettingsPage() {
			// Confirm that this function is being called from a valid callback.
			if ( $this->adminBail( self::SLUG . '_page_muut_settings' ) ) {
				return;
			}

			include( $this->pluginPath . 'views/admin-settings.php');
		}

		/**
		 * Renders the Muut upgrades page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		public function renderAdminUpgradesPage() {
			// Print the content for the Muut upgrades page.
		}

		/**
		 *  Saves a forum page's information.
		 *
		 * @param int $post_id The id of the post (page) being saved.
		 * @param WP_Post $post The post (page) object that is being saved.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function saveForumPage( $post_id, $post ) {
			if ( get_post_type( $post ) != 'page' )
				return;

			if ( $_POST['muut_is_forum_page'] == '0' ) {
				Muut_Forum_Page_Utility::removeAsForumPage( $post_id );
			} elseif ( $_POST['muut_is_forum_page'] == '1' ) {
				Muut_Forum_Page_Utility::setAsForumPage( $post_id );
			}

			if ( $_POST['muut_is_forum_page'] == '1' ) {
				Muut_Forum_Page_Utility::setForumPageRemotePath( $post_id, $_POST['muut_forum_remote_path'] );
			}
		}

	}


	/**
	 * Can be used to return the Muut instance.
	 *
	 * @return Muut
	 * @author Paul Hughes
	 * @since  3.0
	 */
	function muut() {
		return Muut::instance();
	}
}