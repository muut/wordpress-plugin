<?php
/**
 * The Muut Files static class that contains all the static methods required to interact with Muut files,
 * generally stored within the site uploads folder.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Files_Utility' ) ) {

	/**
	 * Muut Files Utility class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Files_Utility {

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Files_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		private function __construct() {}

	}

}