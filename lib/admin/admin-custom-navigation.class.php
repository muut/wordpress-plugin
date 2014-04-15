<?php
/**
 * The class that is responsible for all the admin functionality of the custom navigation.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Admin_Custom_Navigation' ) ) {

	/**
	 * Muut Admin Custom Navigation class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Admin_Custom_Navigation
	{
		/**
		 * @static
		 * @property Muut_Admin_Custom_Navigation The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Admin_Custom_Navigation The instance.
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
		 * @return Muut_Admin_Custom_Navigation
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions regarding the custom navigation admin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addActions() {

		}

		/**
		 * The method for adding all filters regarding the custom navigation admin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addFilters() {

		}


		/**
		 * Render an admin forum category header list item for the custom navigation editor.
		 *
		 * @param int $header_id The term ID for the category header.
		 * @param bool $echo Whether to output the markup or simply return it (false).
		 * @return string|void Returns the markup or void if it is echoed.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function forumCategoryHeaderItem() {

		}
	}
}