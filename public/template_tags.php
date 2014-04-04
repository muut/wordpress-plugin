<?php
/**
 * The template tags packaged with the Muut plugin.
 *
 * @package  Muut
 * @copyright 2014 Moot Inc
 */

/**
 * Gets an option for the Muut plugin.
 *
 * @param string $option The option name.
 * @param mixed $default The default value to return.
 * @return mixed The option value.
 * @author Paul Hughes
 * @since 3.0
 */
function muut_get_option( $option, $default = '' ) {
	return muut()->getOption( $option, $default );
}

/**
 * Gets the remote forum slug registered to the website.
 *
 * @return string The remote forum slug.
 * @author Paul Hughes
 * @since 3.0
 */
function muut_get_root_forum() {
	muut()->getRemoteForumName();
}

/**
 * Checks if the give page is a Muut forum page.
 *
 * @param int $page_id The ID of the page we are checking.
 * @return bool Whether the page is a Muut forum page or not.
 * @author Paul Hughes
 * @since 3.0
 */
function muut_is_forum_page( $page_id = null ) {
	if ( is_null( $page_id ) ){
		$page_id = get_the_ID();
	}

	return Muut_Forum_Page_Utility::isForumPage( $page_id );
}

/**
 * Gets the forum name saved for a given forum page.
 *
 * @param int $page_id The ID of the page we are retrieving the forum for.
 * @return string|false The name of the remote forum registered to the page or false if it is not a forum page.
 * @author Paul Hughes
 * @since 3.0
 */
function muut_get_page_forum_path( $page_id = null ) {
	if ( is_null( $page_id ) ) {
		$page_id = get_the_ID();
	}

	return Muut_Forum_Page_Utility::getRemoteForumPath( $page_id );
}