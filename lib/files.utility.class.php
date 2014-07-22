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
		 * @property string The uploads path.
		 */
		protected static $uploads_path = '';

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

			$dir_path = trailingslashit( $wp_upload_dir['basedir'] ) . self::UPLOADS_DIR_NAME;

			$sub_path = trailingslashit( $dir_path) . $sub_dir;

			// If the uploads directory (and specified subdirectory) do not exist, create them with proper permissions.
			if ( !file_exists( $sub_path ) ) {
				if (!mkdir( $sub_path, 0755, true ) ) {
					return false;
				}
			}

			// Verify that the directory is writeable.
			if ( !is_writable( $sub_path ) ) {
				return false;
			}

			// Store the directory path.
			self::$uploads_path = $dir_path;

			return true;
		}

		/**
		 * Gets the uploads complete path.
		 *
		 * @param string $sub_dir A subdirectory or path to check beneath the main Muut uploads directory.
		 * @return false|string The Muut uploads full path (and a sub-path if specified).
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public static function getUploadsPath( $sub_dir = '' ) {
			if ( !self::$uploads_path && !self::checkMuutUploadsDirectory( $sub_dir ) ) {
				return false;
			}

			return trailingslashit( self::$uploads_path ) . $sub_dir;
		}

		/**
		 * Gets the uploads URL.
		 *
		 * @return false|string The Muut uploads directory URL or false if it doesn't exist/isn't verified.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public static function getUploadsUrl() {
			if ( !self::$uploads_path && !self::checkMuutUploadsDirectory() ) {
				return false;
			}

			$wp_upload_dir = wp_upload_dir();

			$url = trailingslashit( $wp_upload_dir['baseurl'] ) . self::UPLOADS_DIR_NAME;

			return $url;
		}

		/**
		 * Creates (or overwrites) a file within the uploads directory (or a subdirectory).
		 *
		 * @param string $filename The filename / path relative to the uploads directory.
		 * @param string $content The content of the file that we are writing.
		 * @return false|string The created file path or false if failed.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public static function writeFile( $file, $content ) {
			$file_dir = ( dirname( $file ) != '/' && dirname( $file ) != '.' ) ? trailingslashit( dirname( $file ) ) : '';
			$file_name = basename( $file );

			// Make sure that we have a directory where we can write the file to.
			$uploads_path = self::getUploadsPath( $file_dir );
			if ( !$uploads_path ) {
				return false;
			}

			// Let's open the file stream (or create the file if it doesn't exist.
			$file_upload_path = trailingslashit( $uploads_path ) . $file_name;
			$handle = fopen( $file_upload_path, 'w' );

			$content = (string) $content;

			if ( !fwrite( $handle, $content) ) {
				return false;
			}

			return $file_upload_path;
		}
	}
}