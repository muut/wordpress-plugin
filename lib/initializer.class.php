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
			add_action( 'init', array( $this, 'initForumCategoryUtility' ), 10 );
			add_action( 'init', array( 'Muut_Forum_Category_Utility', 'registerPostType' ), 20 );
			add_action( 'init', array( $this, 'initCommentOverrides' ) );
			add_action( 'init', array( $this, 'initDeveloperSubscription' ) );
			add_filter( 'comments_template', array( $this, 'initTemplateLoader' ) );

			add_action( 'admin_init', array( $this, 'adminInits' ) );
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
		 * Initializes the Forum Category utility class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initForumCategoryUtility() {
			$class = 'Muut_Forum_Category_Utility';
			if ( !in_array( $class, $this->alreadyInit ) ) {
				require_once( muut()->getPluginPath() . 'lib/forum-category.utility.class.php' );
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
		 * Initializes the Admin Custom Navigation class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function initAdminCustomNav() {
			$class = 'Muut_Admin_Custom_Navigation';
			if ( !in_array( $class, $this->alreadyInit ) ) {
				require_once( muut()->getPluginPath() . 'lib/admin/admin-custom-navigation.class.php' );
				if ( class_exists( $class ) ) {
					Muut_Admin_Custom_Navigation::instance();
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
			$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
			if ( $page == 'muut_custom_navigation' ) {
				$this->initAdminCustomNav();
			}
		}
	}
}