<?php
/**
 * The template tags packaged with the Muut plugin.
 *
 * @package  Muut
 * @copyright 2014 Muut Inc
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
function muut_get_forum_name() {
	muut()->getForumName();
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

	return Muut_Post_Utility::isMuutForumPage( $page_id );
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

	return Muut_Post_Utility::getChannelRemotePath( $page_id );
}

/**
 * Get embed anchor for page.
 *
 * @param int $page_id The ID of the page we are getting the anchor for.
 * @param bool $echo Whether to echo the anchor or not.
 * @return void|string The anchor markup or void, we $echo is set to true (and we echo the markup).
 * @author Paul Hughes
 * @since 3.0
 */
function muut_forum_page_embed( $page_id = null, $echo = true ) {
	if ( is_null( $page_id ) ) {
		$page_id = get_the_ID();
	}

	return Muut_Post_Utility::forumPageEmbedMarkup( $page_id, $echo );
}

/**
 * Gets the comment path for a given post id.
 *
 * @param int $post_id The post we are getting the comments path for.
 * @param bool $full_path Whether to return the full path for the comments.
 * @return string|false The comments path or false if there was an error retrieving them (such as override not being enabled).
 * @author Paul Hughes
 * @since 3.0
 */
function muut_get_comments_path( $post_id, $full_path = false ) {
	if ( is_null( $post_id ) ) {
		$post_id = get_the_ID();
	}

	if ( class_exists( 'Muut_Comment_Overrides' ) ) {
		return Muut_Comment_Overrides::instance()->getCommentsPath( $post_id, $full_path );
	} else {
		return false;
	}
}

/**
 * Gets embed anchor for a given post comments override.
 *
 * @param int $post_id The post we are getting the comments anchor for.
 * @return void|string The anchor markup or void, we $echo is set to true (and we echo the markup).
 * @param bool $echo Whether to echo the anchor or not.
 * @author Paul Hughes
 * @since 3.0
 */
function muut_comments_override_anchor( $post_id = null, $echo = true ) {
	if ( is_null( $post_id ) ) {
		$post_id = get_the_ID();
	}

	if ( class_exists( 'Muut_Comment_Overrides' ) ) {
		return Muut_Comment_Overrides::instance()->commentsOverrideAnchor( $post_id, $echo );
	} else {
		return false;
	}
}