<?php
/**
 * The initializer class that is responsible for initializing singleton classes only when necessary.
 *
 * @package   Muut
 * @copyright 2014 Moot Inc
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
					$class::instance();
				}
				$this->alreadyInit[] = $class;
			}
		}
	}
}