<?php
/**
 * The Muut Channel static class that contains all the static methods required to interact with Muut channels,
 * from embeds to API stuff (down the road).
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Channel_Utility' ) ) {

	/**
	 * Muut Channel Utility class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Channel_Utility {

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Channel_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		private function __construct() {}

		/**
		 * Gets a channel's full index URI.
		 *
		 * @param string $path The channel path.
		 * @return string The full index URI.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public static function getChannelIndexUri( $path ) {

			$base_uri = muut()->getForumIndexUri();

			$uri = $base_uri . $path;

			return apply_filters( 'muut_channel_index_uri', $uri, $path );
		}

	}

}