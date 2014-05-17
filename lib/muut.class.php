<?php
/**
 * The main class file for the Muut plugin.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
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
		 * The version of Muut this was released with.
		 */
		const MUUTVERSION = 'latest';

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
		const MUUTSERVERS = 'muut.com';

		/**
		 * @property Whether develop mode was executed.
		 */
		private $developMode;

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
		 * @property string The Muut container element css class.
		 */
		protected $wrapperClass;

		/**
		 * @property string The Muut container element css id.
		 */
		protected $wrapperId;

		/**
		 * @property string The path prefix for fetching information from the Muut servers.
		 *                      Depends on if proxy rewriting is enabled.
		 */
		protected $contentPathPrefix;

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
			$this->wrapperClass = 'muut';
			$this->wrapperId = '';
			$this->adminNotices = array();

			$this->loadLibraries();
			$this->addActions();
			$this->addFilters();

			if ( defined( 'MUUT_DEVELOP_MODE' ) && MUUT_DEVELOP_MODE == true ) {
				$this->developMode();
			}
		}

		/**
		 * Adds the main actions for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addActions() {
			add_action( 'admin_init', array( $this, 'maybeAddRewriteRules' ) );
			add_action( 'admin_menu', array( $this, 'createAdminMenuItems' ) );
			add_action( 'admin_notices', array( $this, 'renderAdminNotices' ) );
			add_action( 'load-toplevel_page_' . self::SLUG, array( $this, 'saveSettings' ) );
			add_action( 'flush_rewrite_rules_hard', array( $this, 'removeRewriteAdded' ) );

			add_action( 'init', array( $this, 'registerScriptsAndStyles' ) );
			add_action( 'init', array( $this, 'disregardOldMoot' ), 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendScripts' ), 11 );

			add_action( 'wp_print_scripts', array( $this, 'printCurrentPageJs' ) );
		}

		/**
		 * Adds the main filters for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addFilters() {
			add_filter( 'body_class', array( $this, 'addBodyClasses' ) );
			add_filter( 'admin_body_class', array( $this, 'addAdminBodyClasses' ) );
			add_filter( 'the_content', array( $this, 'filterForumPageContent' ), 10 );
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
		 * Gets the content path prefix for fetching content from the Muut servers.
		 * Depends on if proxy rewriting is enabled.
		 *
		 * @return string The path prefix (full server is rewriting is disabled).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getContentPathPrefix() {
			if ( !isset( $this->contentPathPrefix ) ) {
				if ( !$this->getOption( 'enable_proxy_rewrites', true ) ) {
					$this->contentPathPrefix = 'https://' . self::MUUTSERVERS . '/';
				} else {
					$this->contentPathPrefix = '/';
				}
			}
			return $this->contentPathPrefix;
		}

		/**
		 * Gets the remote forum name that is registered.
		 *
		 * @return string The remote forum name.
		 */
		public function getForumName() {
			return $this->getOption( 'forum_name', '' );
		}

		/**
		 * Gets the Muut element wrapper class.
		 *
		 * @return string The container css class attribute.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getWrapperCssClass() {
			return ' ' . apply_filters( 'muut_wrapper_css_class', $this->wrapperClass );
		}

		/**
		 * Gets the Muut element wrapper id attribute.
		 *
		 * @return string The container id attribute.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getWrapperCssId() {
			return apply_filters( 'muut_wrapper_css_id', $this->wrapperId );
		}

		/**
		 * Adds the rewrite rules for indexing Muut posts locally if they are currently not already set.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function maybeAddRewriteRules() {
			if ( $this->getForumName() !== '' && !$this->getOption( 'added_rewrite_rules', false ) && $this->getOption( 'enable_proxy_rewrites', true ) ) {
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
					'RewriteRule ^i/(' . $this->getForumName() . ')(/.*)?$ http://' . self::MUUTSERVERS . '/i/$1$2 [P]',
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

			$muut_rules = "RewriteRule ^i/(" . $this->getForumName() . ")(/.*)?\$ http://" . self::MUUTSERVERS . "/i/\$1\$2 [P]\n";
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
		 * If the user is upgrading from the OLD plugin with namespace "moot" instead of "muut" and has not yet
		 * deactivated it, make sure to ignore it.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 2.0.13
		 */
		public function disregardOldMoot() {
			if ( class_exists( 'Moot' ) ) {
				remove_filter('the_content', array(Moot::get_instance(), 'default_comments'));

				remove_action('wp_enqueue_scripts', array(Moot::get_instance(), 'moot_includes'));
				remove_action('wp_head', array(Moot::get_instance(), 'moot_head'));
				remove_action('admin_menu', array(Moot::get_instance(), 'moot_admin_menu'));
				remove_action('admin_init', array(Moot::get_instance(), 'moot_settings'));
			}
		}

		/**
		 * Gets the Muut language equivalent of a given WordPress language abbreviation.
		 *
		 * @param string $wp_lang The WordPress abbreviation for a given language.
		 * @return string The Muut equivalent language string.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getMuutLangEquivalent( $wp_lang ) {
			// Format is wp_lang => muut_lang.
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
			$muut_version = $this->getMuutVersion();
			wp_register_script( 'muut', '//cdn.' . self::MUUTSERVERS . '/' . $muut_version . '/moot.' . $this->getOption( 'language', 'en' ) . '.min.js', array( 'jquery' ), $muut_version, true );
			wp_register_script( 'muut-admin-functions', $this->pluginUrl . 'resources/admin-functions.js', array( 'jquery' ), '1.0', true );
			wp_register_script( 'x-editable', $this->pluginUrl . 'vendor/jqueryui-editable/js/jqueryui-editable.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tooltip', 'jquery-ui-button' ), '1.5.1', true);
			wp_register_script( 'muut-admin-post-edit', $this->pluginUrl . 'resources/admin-post-edit.js', array( 'jquery', 'jquery-ui-dialog' ), '1.0', true );

			wp_register_script( 'muut-frontend-functions', $this->pluginUrl . 'resources/frontend-functions.js', array( 'jquery' ), '1.0', true );
			wp_register_script( 'muut-sso', $this->pluginUrl . 'resources/muut-sso.js', array( 'jquery', 'muut' ), '1.0', true );

			wp_register_style( 'muut-admin-style', $this->pluginUrl . 'resources/admin-style.css' );
			wp_register_style( 'x-editable-style', $this->pluginUrl . 'vendor/jqueryui-editable/css/jqueryui-editable.css' );
			wp_register_style( 'muut-forum-css', '//cdn.' . self::MUUTSERVERS . '/' . $muut_version . '/moot.css', array(), $muut_version );
			wp_register_style( 'jquery-ui-dialog-css', site_url('wp-includes/css/jquery-ui-dialog.css') );

			wp_register_style( 'muut-font-css', $this->pluginUrl . 'resources/muut-font.css', array(), $muut_version );
			// Localization rules.
			$localizations = array(
				'muut-admin-functions' => array(
					//'default_path' => sprintf( __( '%sdefault%s', 'muut' ), '/(', ')' ),
				),
				'muut-admin-post-edit' => array(
					'continue' => __( 'Go ahead!', 'muut' ),
					'cancel' => __( 'Don\'t do that!', 'muut' ),
				),
			);
			foreach ( $localizations as $key => $array ) {
				$new_key = str_replace( '-', '_', $key ) . '_localized';

				wp_localize_script( $key, $new_key, $array );
			}
		}

		/**
		 * Gets the version of Muut to use.
		 *
		 * @return string
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getMuutVersion() {
			$muut_version = defined( 'MUUT_VERSION' ) ? MUUT_VERSION : self::MUUTVERSION;

			return $muut_version;
		}

		/**
		 * Gets the default value of a given option.
		 *
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
				'forum_name' => '',
				// TODO: Make this match whatever language is set for the site.
				'language' => $default_lang,
				'replace_comments' => true,
				'use_threaded_commenting' => false,
				'override_all_comments' => false,
				'show_comments_in_forum' => '0',
				'commenting_defaults' => array(
					'type' => 'flat',
					'disable_uploads' => '0',
				),
				'forum_page_id' => false,
				'channel_defaults' => array(
					'hide_online' => '0',
					'disable_uploads' => '0',
				),
				'forum_defaults' => array(
					'hide_online' => '0',
					'disable_uploads' => '0',
				),
				'subscription_api_key' => '',
				'subscription_secret_key' => '',
				'subscription_use_sso' => false,
				'enable_proxy_rewrites' => '1',
				'comments_base_domain' => $_SERVER['SERVER_NAME'],
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
			if ( $screen->id == 'page'
				|| $screen->id == self::SLUG . '_page_muut_settings'
				|| $screen->id == 'toplevel_page_muut' ) {
				wp_enqueue_script( 'muut-admin-functions' );
				wp_enqueue_style( 'muut-admin-style' );
			}


			if ( $screen->id == self::SLUG . '_page_muut_custom_navigation' ) {
				wp_enqueue_script( 'muut-admin-functions' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'x-editable' );
				wp_enqueue_style( 'x-editable-style' );
				wp_enqueue_style( 'muut-admin-style' );
			}

			// Enqueue the font on all admin pages for the menu icon.
			wp_enqueue_style( 'muut-font-css' );
		}

		/**
		 * Enqueues scripts for the frontend.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueFrontendScripts() {
			if ( $this->needsMuutResources() ) {
				wp_enqueue_script( 'muut' );
				wp_enqueue_style( 'muut-forum-css' );
				wp_enqueue_script( 'muut-frontend-functions' );
			}


		}

		/**
		 * Prints the JS necessary on a given frontend page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function printCurrentPageJs() {
			if ( !is_admin() && get_post() ) {
				$page_id = get_the_ID();
				if ( Muut_Post_Utility::isMuutPost( $page_id ) ) {
					echo '<script type="text/javascript">';
					if ( Muut_Post_Utility::getForumPageId() == $page_id ) {
						echo 'var muut_current_page_permalink = "' . get_permalink( $page_id ) . '";';
						echo 'var muut_comments_base_domain = "' . $this->getOption( 'comments_base_domain' ) . '";';
						if ( $this->getOption( 'replace_comments' ) ) {
							echo 'var muut_show_comments_in_nav = ' . $this->getOption( 'show_comments_in_forum', '0' ) . ';';
						}
					}
					echo '</script>';
				}
			}
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

			if ( isset( $_POST['initial_save'] ) ) {
				return apply_filters( 'muut_settings_initial_save', apply_filters( 'muut_settings_validated', $settings ) );
			}

			$boolean_settings = apply_filters( 'muut_boolean_settings', array(
				'replace_comments',
				'use_threaded_commenting',
				'override_all_comments',
				'show_comments_in_forum',
				'is_threaded_default',
				'show_online_default',
				'allow_uploads_default',
				'subscription_use_sso',
				'enable_proxy_rewrites',
			) );

			foreach ( $boolean_settings as $boolean_setting ) {
				$settings[$boolean_setting] = isset( $settings[$boolean_setting]) ? $settings[$boolean_setting] : '0';
			}

			if ( ( isset( $settings['forum_name'] ) && $settings['forum_name'] != $this->getForumName() )
				|| ( isset( $settings['enable_proxy_rewrites'] ) && $settings['enable_proxy_rewrites'] != $this->getOption( 'enable_proxy_rewrites' ) ) ) {
				flush_rewrite_rules( true );
			}

			// Add depth to the settings that need to be further in on the actual settings array.
			$settings['forum_page_defaults']['is_threaded'] = $settings['is_threaded_default'];
			$settings['forum_page_defaults']['show_online'] = $settings['show_online_default'];
			$settings['forum_page_defaults']['allow_uploads'] = $settings['allow_uploads_default'];
			unset( $settings['is_threaded_default'] );
			unset( $settings['show_online_default'] );
			unset( $settings['allow_uploads_default'] );

			// If the Secret Key setting does not get submitted (i.e. is disabled), make sure to erase its value.
			$settings['subscription_secret_key'] = isset( $settings['subscription_secret_key']) ? $settings['subscription_secret_key'] : '';

			return apply_filters( 'muut_settings_validated', $settings );
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
				array( $this, 'renderAdminSettingsPage' )
			);
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
			if ( $this->adminBail( 'toplevel_page_' . self::SLUG ) ) {
				return;
			}

			include( $this->pluginPath . 'views/admin-settings.php');
		}

		/**
		 * Adds the proper body class(es) for admin depending on the admin page being loaded.
		 *
		 * @param string $classes The current string of body classes.
		 * @return string The modified array of body classes
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addAdminBodyClasses( $classes ) {
			$screen = get_current_screen();

			if ( $screen->id == self::SLUG . '_page_muut_settings' ) {
				$classes .= 'muut_settings';
			}

			return $classes;
		}

		/**
		 * Adds the proper body class(es) depending on the page being loaded.
		 *
		 * @param array $classes The current array of body classes.
		 * @return array The modified array of body classes
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addBodyClasses( $classes ) {
			if ( $this->needsMuutResources( get_the_ID() ) ) {
				$classes[] = 'muut-enabled';
				$classes[] = 'has-muut';
				$classes[] = 'has-moot';
				if ( Muut_Post_Utility::getForumPageId() == get_the_ID() ) {
					$classes[] = 'muut-forum-home';
				}
			}

			return $classes;
		}

		/**
		 * Checks if the a page needs to include the frontend Muut resources.
		 *
		 * @param int $page_id The page we are checking.
		 * @return bool Whether we need the frontend Muut resources or not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function needsMuutResources( $page_id = null ) {
			if ( is_null( $page_id ) ) {
				$page_id = get_the_ID();
			}

			if ( !is_numeric( $page_id ) ) {
				return false;
			}

			$return = false;
			if ( Muut_Post_Utility::isMuutPost( get_the_ID() )
				|| ( $this->getOption( 'replace_comments' ) && is_singular() && comments_open() ) ) {
				$return = true;
			}

			return apply_filters( 'muut_requires_muut_resources', $return, $page_id );
		}

		/**
		 * Filters the content on a page if it is a standalone forum page to include the embed.
		 *
		 * @param string $content The current content string.
		 * @return string The content.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function filterForumPageContent( $content ) {
			global $post;


			if ( $post->post_type == 'page' && ( Muut_Post_Utility::isMuutChannelPage( $post->ID ) || Muut_Post_Utility::isMuutForumPage( $post->ID ) ) ) {
				$content = Muut_Post_Utility::forumPageEmbedMarkup( $post->ID, false );
			}

			return $content;
		}

		/**
		 * Functions to run if we are running in Muut Development Mode
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function developMode() {
			if ( defined( 'MUUT_DEVELOP_MODE' ) && MUUT_DEVELOP_MODE == true && is_user_logged_in() && is_super_admin() ) {
				$this->developMode = true;
				add_action( 'admin_init', array( $this, 'developSettingsAddActions' ), 10 );
			}
		}

		/**
		 * Adds the actions to run on the Muut main/settings page when in develop mode.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function developSettingsAddActions() {
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'muut' && $this->developMode ) {
				if ( isset( $_GET['muut_development_action'] ) && check_admin_referer( 'muut_development_action', 'muut_nonce' ) ) {

					$this->developAction( $_GET['muut_development_action'] );
				}
				add_action( 'in_admin_header', array( $this, 'developSettingsBox' ) );
			}
		}

		/**
		 * Executes a given develop-mode action.
		 *
		 * @param string $action The action to execute.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function developAction( $action ) {
			if ( $this->developMode ) {
				switch ( $action ) {
					case 'settings_reset':
						delete_option( self::OPTIONNAME );
						if ( isset( $_GET['page'] ) ) {
							wp_redirect( admin_url( 'admin.php?page=' . $_GET['page'] ) );
						} else {
							wp_redirect( admin_url( 'admin.php' ) );
						}
					break;
				}
			}
		}

		/**
		 * Renders the items/buttons that are enabled if we are in develop mode.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function developSettingsBox() {
			if ( $this->developMode ) {
				echo '<div id="muut_develop_actions_wrapper"><div id="muut_develop_actions">';
					if ( isset( $_GET['page'] ) )
						echo '<a href="' . wp_nonce_url( admin_url( 'admin.php?page=' . $_GET['page'] . '&muut_development_action=settings_reset'), 'muut_development_action', 'muut_nonce' ) . '">Reset Muut Settings</a>';
					else
						echo '<a href="' . wp_nonce_url( admin_url( 'admin.php?muut_development_action=settings_reset'), 'muut_development_action', 'muut_nonce' ) . '">Reset Muut Settings</a>';
				echo '</div></div>';
			}
		}


	}
	/**
	 * END MAIN CLASS
	 */

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