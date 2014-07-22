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

		const UPLOADS_DIR_NAME = 'muut';

		/**
		 * @static
		 * @property string The uploads directory.
		 */
		protected static $uploads_dir = '';

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Files_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		private function __construct() {}

		/**
		 * Check if the muut directory exists under the wp-content/uploads directory, and if not then create it.
		 *
		 * @param string $sub_dir A subdirectory or path to check beneath the main Muut uploads directory.
		 * @return bool Whether the directory exists or not (or was created if it previously didn't).
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public static function checkMuutUploadsDirectory( $sub_dir = '' ) {
			$wp_upload_dir = wp_upload_dir();

			$dir_path = trailingslashit( $wp_upload_dir['basedir'] ) . self::UPLOADS_DIR_NAME . '/' . $sub_dir;

			// If the uploads directory (and specified subdirectory) do not exist, create them with proper permissions.
			if ( !file_exists( $dir_path ) ) {
				if (!mkdir( $dir_path, 0755, true ) ) {
					return false;
				}
			}

			// Verify that the directory is writeable.
			if ( !is_writable( $dir_path ) ) {
				return false;
			}

			// Store the directory path.
			self::$uploads_dir = $dir_path;

			return true;
		}

		/**
		 * Gets the uploads URL.
		 *
		 * @return false|string The Muut uploads directory URL or false if it doesn't exist/isn't verified.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public static function getUploadsUrl() {
			if ( !self::$uploads_dir && !self::checkMuutUploadsDirectory() ) {
				return false;
			}

			$wp_upload_dir = wp_upload_dir();

			$url = trailingslashit( $wp_upload_dir['baseurl'] ) . self::UPLOADS_DIR_NAME;

			return $url;
		}

	}

}