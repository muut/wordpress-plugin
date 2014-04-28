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
		}

		/**
		 * Adds the actions used by this class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addActions() {

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
	}
}