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
	 * @since   3.0.2
	 */
	class Muut_Channel_Utility {

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Channel_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		private function __construct() {}

		/**
		 * Gets a channel's full index URI.
		 *
		 * @param string $path The channel path.
		 * @return string The full index URI.
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		public static function getChannelIndexUri( $path ) {

			$base_uri = muut()->getForumIndexUri();

			$uri = $base_uri . $path;

			return apply_filters( 'muut_channel_index_uri', $uri, $path );
		}

		/**
		 * Gets a standalone channel's embed code.
		 *
		 * @param string $path The channel's remote path.
		 * @param array $args The channel's embed arguments.
		 * @param bool $echo Whether to echo or return the markup
		 * @return string|void The embed code or void, if echoing.
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		public static function getChannelEmbedMarkup( $path, $args = array(), $echo = false ) {
			$id_attr = muut()->getWrapperCssId() ? 'id="' . muut()->getWrapperCssId() . '"' : '';

			$settings = muut()->getEmbedAttributesString( $args );

			$embed = '<a ' . $id_attr . ' class="' . muut()->getWrapperCssClass() . '" ' . $settings . ' href="' . muut()->getContentPathPrefix() . 'i/' . muut()->getForumName() . '/' . $path . '">' . __( 'Comments', 'muut' ) . '</a>';
			$embed = apply_filters( 'muut_channel_embed_content', $embed, $path );

			if ( $echo ) {
				echo $embed;
				return;
			} else {
				return $embed;
			}
		}
	}

}