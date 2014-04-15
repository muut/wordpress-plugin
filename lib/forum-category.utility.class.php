<?php
/**
 * The Forum Category static class that contains all the static methods required to interact with a Forum Category that
 * are Muut-related. The Forum Category registers a new post type that is responsible for the Forum Categories.
 * Note that it is mostly for organizational / storage purposes, rather than using the admin UX or frontend
 * display methods. While technically being a WP_Post, they will really act like a cross between posts and
 * categories.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Forum_Category_Utility' ) ) {

	/**
	 * Muut Forum Category Utility class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Forum_Category_Utility
	{

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Forum_Category_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function __construct() {}

		
	}
}