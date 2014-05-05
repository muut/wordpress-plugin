<?php
/**
 * The Forum Page static class that contains all the static methods required to interact with a Forum Page that
 * are Muut-related.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Forum_Page_Utility' ) ) {

	/**
	 * Muut Forum Page Utility class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Forum_Page_Utility
	{

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Forum_Page_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function __construct() {}

		/**
		 * The meta name for a forum page's remote forum name.
		 */
		const META_REMOTEPATH = 'muut_forum_page_remote_path';

		/**
		 * The meta name for whether a page is a forum page or not.
		 */
		const META_ISFORUMPAGE = 'muut_is_forum_page';

		/**
		 * The meta name for the super-options meta for individual forum pages.
		 * This post_meta will contain an array of the specific page options.
		 */
		const META_FORUMPAGESETTINGS = 'muut_forum_page_settings';

		/**
		 * The key for a forum page's specific setting
		 */

		/**
		 * The method for setting a forum page's remote path.
		 *
		 * @param int $page_id The page ID for the forum page.
		 * @param string $path The path to set (if custom).
		 * @return bool Whether the page's remote path was updated or not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function setForumPageRemotePath( $page_id, $path = '' ) {
			if ( !is_numeric( $page_id ) || !is_string( $path ) ) {
				return false;
			}

			if ( $path == '' ) {
				delete_post_meta( $page_id, self::META_REMOTEPATH );
				return true;
			}

			update_post_meta( $page_id, self::META_REMOTEPATH, $path );
			return true;
		}

		/**
		 * Sets the page as a forum page.
		 *
		 * @param int $page_id The page ID we are turning into a forum page.
		 * @return bool Whether the page was successfully turned into a forum page.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function setAsForumPage( $page_id ) {
			if ( !is_numeric( $page_id ) ) {
				return false;
			}

			update_post_meta( $page_id, self::META_ISFORUMPAGE, true );
			return true;
		}

		/**
		 * Sets page as no longer being a forum page.
		 *
		 * @param int $page_id The page ID that we are removing as being a forum page.
		 * @return bool Whether the page was successfully turned into NO LONGER being a forum page.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function removeAsForumPage( $page_id ) {
			if ( !is_numeric( $page_id ) ) {
				return false;
			}

			delete_post_meta( $page_id, self::META_ISFORUMPAGE );
			return true;
		}

		/**
		 * Returns whether a page is a forum page or not.
		 *
		 * @param int $page_id The page ID that we are checking if it is a forum page or not.
		 * @return bool Whether the page is a forum page or not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function isForumPage( $page_id ) {
			if( is_numeric( $page_id ) && get_post_meta( $page_id, self::META_ISFORUMPAGE, true ) != '' ) {
				$value = true;
			} else {
				$value = false;
			}
			return apply_filters( 'muut_is_forum_page', $value, $page_id );
		}

		/**
		 * Gets the forum page's remote path.
		 *
		 * @param int $page_id The page ID that we are getting the remote forum path for.
		 * @param bool $no_suffix Whether to include ':comments' for unthreaded.
		 * @return string|false Returns the path if one is found for the forum page or false if not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getRemoteForumPath( $page_id, $no_suffix = false ) {
			if( !is_numeric( $page_id ) ) {
				return false;
			}

			$path = get_post_meta( $page_id, self::META_REMOTEPATH, true );

			if ( muut()->getOption( 'forum_home_id', false ) != $page_id && !self::getForumPageOption( $page_id, 'is_threaded', false ) && !$no_suffix  ) {
				$path .= ':comments';
			}
			return $path;
		}

		/**
		 * Sets the other options for a forum page—cannot be called directly.
		 *
		 * @param int $page_id The page ID that we are saving the options for.
		 * @param string $option_name The option name for the setting we are saving.
		 * @param mixed $value The value we are setting for the option.
		 * @return bool Whether the options were saved or not.
		 */
		public static function setForumPageOption( $page_id, $option_name, $value ) {
			if ( !is_numeric( $page_id ) || !is_string( $option_name ) ) {
				return false;
			}

			$current_settings = get_post_meta( $page_id, self::META_FORUMPAGESETTINGS, true );

			$new_setting = apply_filters( 'muut_set_page_options', array( $option_name => $value ), $page_id );

			update_post_meta( $page_id, self::META_FORUMPAGESETTINGS, wp_parse_args( $new_setting, $current_settings ) );

			return true;
		}

		/**
		 * Gets a given setting for a forum page.
		 *
		 * @param int $page_id The page ID that we are getting a setting for.
		 * @param string $option_name The option name that we are getting for the forum page.
		 * @param string $default The default value if none is returned.
		 * @return mixed The value of the option.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getForumPageOption( $page_id, $option_name, $default = '' ) {
			if ( !is_numeric( $page_id ) || !is_string( $option_name ) ) {
				return false;
			}

			$forum_page_defaults = muut()->getOption( 'forum_page_defaults', array() );

			$current_settings = get_post_meta( $page_id, self::META_FORUMPAGESETTINGS, true );

			$settings = apply_filters( 'muut_forum_page_settings', wp_parse_args( $current_settings, $forum_page_defaults ), $page_id );

			return isset( $settings[$option_name] ) ? $settings[$option_name] : $default;
		}


		/**
		 * Renders a given forum page's forum (returns the Muut JS anchor element).
		 *
		 * @param int $page_id The page ID whose forum we are rendering.
		 * @param bool $echo Whether to echo the anchor or return the markup.
		 * @return string|void The anchor markup or void, if it is set to be echoed.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function forumPageEmbedMarkup( $page_id, $echo = true ) {
			if ( !is_numeric( $page_id ) || !self::isForumPage( $page_id ) ) {
				return false;
			}

			$path = self::getRemoteForumPath( $page_id );

			$settings = ' ';
			if ( !self::getForumPageOption( $page_id, 'show_online', true ) ) {
				$settings .= 'data-show_online="false" ';
			} else {
				$settings .= 'data-show_online="true" ';
			}
			if ( !self::getForumPageOption( $page_id, 'allow_uploads', false ) ) {
				$settings .= 'data-upload="false" ';
			} else {
				$settings .= 'data-upload="true" ';
			}

			$settings .= 'title="' . get_the_title( $page_id ) . '" ';
			$settings .= 'data-category="' . get_the_title( $page_id ) . '" ';

			if ( $path === false )
				return false;

			if ( muut()->getOption( 'forum_home_id', false ) == $page_id ) {
				ob_start();
				include ( muut()->getPluginPath() . 'views/blocks/custom-navigation-embed-block.php' );
				$embed = ob_get_clean();
			} else {
				$id_attr = muut()->getWrapperCssId() ? 'id="' . muut()->getWrapperCssId() . '"' : '';

				$embed = '<a ' . $id_attr . ' class="' . muut()->getWrapperCssClass() . '" href="' . muut()->getContentPathPrefix() . 'i/' . muut()->getRemoteForumName() . '/' . $path . '" ' . $settings . '>' . __( 'Comments', 'muut' ) . '</a>';
			}

			if ( $echo ) {
				echo $embed;
			} else {
				return $embed;
			}
		}

		/**
		 * Gets and echoes the markup for displaying the comments link on the forum navigation.
		 *
		 * @param array $category_headers The array of category header objects—just used to see if custom nav has been set.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function forumPageCommentsNavigationItem( $category_headers ) {
			if ( muut()->getOption( 'show_comments_in_forum' ) ) {
				if ( !empty( $category_headers ) ) {
					echo '<div class="m-h3">' . apply_filters( 'muut_forum_comments_navigation_header', __( 'Site', 'muut' ) ) . '</div>';
					$class = 'non-category ';
					echo '<a href="#!/' . muut()->getOption( 'comments_base_domain' ) . '" class="' . $class . '" title="' . __( 'Comments', 'muut' ) .'">' . __( 'Comments', 'muut' ) . '</a>';
				}
			}
		}
	}
}