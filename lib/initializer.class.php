<?php
/**
 * The initializer class that is responsible for initializing singleton classes only when necessary.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Initializer' ) ) {

	/**
	 * Muut Initializer class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Initializer
	{
		/**
		 * @static
		 * @property Muut_Initializer The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property array An array of classes that have already been initialized.
		 */
		protected $alreadyInit;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Initializer The instance.
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
		 * @return Muut_Initializer
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->alreadyInit = array();
			$this->addInitListeners();
		}

		/**
		 * Adds the main actions and filters (init listeners) for the plugin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addInitListeners() {
			add_action( 'template_redirect', array( $this, 'initTemplateLoader' ) );
			add_action( 'init', array( $this, 'initForumPageUtility' ) );
			add_action( 'init', array( $this, 'initCommentOverrides' ) );
			add_action( 'init', array( $this, 'initDeveloperSubscription' ) );
			add_filter( 'comments_template', array( $this, 'initTemplateLoader' ) );

			// Deprecating these, scheduled for full removal.
			add_action( 'init', array( $this, 'initMuutShortcodes' ) );

			add_action( 'admin_init', array( $this, 'adminInits' ), 3 );
			add_action( 'load-post.php', array( $this, 'initPostEditor') );
		}

		/**
		 * Initializes the Template Loader class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initTemplateLoader() {
			$class = 'Muut_Template_Loader';
			if ( !in_array( $class, $this->alreadyInit ) ) {
				require_once( muut()->getPluginPath() . 'lib/template-loader.class.php');
				if ( class_exists( $class ) ) {
					Muut_Template_Loader::instance();
				}
				$this->alreadyInit[] = $class;
			}
		}

		/**
		 * Initializes the Forum Page utility.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initForumPageUtility() {
			$class = 'Muut_Forum_Page_Utility';
			if ( !in_array( $class, $this->alreadyInit ) ) {
				require_once( muut()->getPluginPath() . 'lib/forum-page.utility.class.php');
				$this->alreadyInit[] = $class;
			}
		}

		/**
		 * Initializes the Comment Overrides class (when WP comments over overridden by Muut).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initCommentOverrides() {
			$class = 'Muut_Comment_Overrides';
			if ( !in_array( $class, $this->alreadyInit ) && muut()->getOption( 'replace_comments', false ) ) {
				require_once( muut()->getPluginPath() . 'lib/comment-overrides.class.php');
				if ( class_exists( $class ) ) {
					Muut_Comment_Overrides::instance();
				}
				$this->alreadyInit[] = $class;
			}
		}

		/**
		 * Initializes the Developer Subscription class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initDeveloperSubscription() {
			$class = 'Muut_Developer_Subscription';
			if ( !in_array( $class, $this->alreadyInit )
				&& muut()->getOption( 'subscription_api_key', '' )
				&& muut()->getOption( 'subscription_secret_key', '' ) ) {
				require_once( muut()->getPluginPath() . 'lib/subscriber/developer-subscription.class.php');
				if ( class_exists( $class ) ) {
					Muut_Developer_Subscription::instance();
				}
				$this->alreadyInit[] = $class;
			}
		}

		/**
		 * Initializes the plugin Updater class, to pass along old options and other functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initUpdater() {
			$class = 'Muut_Updater';
			if ( !in_array( $class, $this->alreadyInit ) ) {
				require_once( muut()->getPluginPath() . 'lib/updater.class.php' );
				if ( class_exists( $class ) ) {
					Muut_Updater::instance();
				}
				$this->alreadyInit[] = $class;
			}
		}

		/**
		 * Initializes the Muut Shortcodes. We will be getting rid of these soon.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initMuutShortcodes() {
			$class = 'Muut_Shortcodes';
			if ( !in_array( $class, $this->alreadyInit ) ) {
				require_once( muut()->getPluginPath() . 'lib/shortcodes.class.php' );
				if ( class_exists( $class ) ) {
					Muut_Shortcodes::instance();
				}
				$this->alreadyInit[] = $class;
			}
		}

		/**
		 * Initializes the admin post editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initPostEditor() {
			$class = 'Muut_Admin_Post_Editor';
			if ( !in_array( $class, $this->alreadyInit ) ) {
				require_once( muut()->getPluginPath() . 'lib/admin/admin-post-editor.class.php' );
				if ( class_exists( $class ) ) {
					Muut_Admin_Post_Editor::instance();
				}
				$this->alreadyInit[] = $class;
			}
		}

		/**
		 * Checks some things in the admin and, from there, knows which libraries to initialize.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function adminInits() {
			if ( version_compare( Muut::VERSION, muut()->getOption( 'current_version', '0' ), '>' ) ) {
				$this->initUpdater();
			}
		}
	}
}