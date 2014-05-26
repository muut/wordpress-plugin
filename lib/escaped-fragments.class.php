<?php
/**
 * The singleton class that contains all functionality regarding support for SEO with Escaped Fragments.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Escaped_Fragments' ) ) {

	/**
	 * Muut Escaped Fragments class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Escaped_Fragments
	{
		/**
		 * @static
		 * @property Muut_Escaped_Fragments The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property bool Whether we need to do any further escaped fragment work.
		 */
		protected $maybeDoEscapedFragments;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Escaped_Fragments The instance.
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
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
		 * @return Muut_Escaped_Fragments
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * Adds the actions used by this class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
		 */
		protected function addActions() {

		}

		/**
		 * Adds the filters used by this class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
		 */
		protected function addFilters() {
			add_filter( 'muut_channel_embed_content', array( $this, 'filterChannelIndexContent' ), 10, 2 );
			add_filter( 'muut_forum_page_embed_content', array( $this, 'filterForumPageIndexContent' ), 10, 2 );
			add_filter( 'muut_comment_overrides_embed_content', array( $this, 'filterCommentsOverrideIndexContent' ), 10, 3 );
		}

		/**
		 * Checks whether we are/should be using escaped fragments on this page load.
		 *
		 * @return bool Whether we are using escaped fragments support on this page load.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function isUsingEscapedFragments() {
			if ( !isset( $this->maybeDoEscapedFragments ) ) {
				global $post;

				if ( isset ( $post ) && is_a( $post, 'WP_Post' ) && ( Muut_Post_Utility::isMuutPost( $post->ID ) || Muut_Post_Utility::isMuutCommentingPost( $post->ID ) ) ) {
					$this->maybeDoEscapedFragments = true;
				} else {
					$this->maybeDoEscapedFragments = false;
				}
			}
			return apply_filters( 'muut_is_using_escaped_fragments', $this->maybeDoEscapedFragments );
		}

		/**
		 * Filters the Channel embed content to render the index content rather than the JS anchor.
		 *
		 * @param string $content The current embed content (anchor).
		 * @param int $page_id The page for which we are filtering the embed.
		 * @return string The filtered content.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function filterChannelIndexContent( $content, $page_id ) {
			if ( $this->isUsingEscapedFragments() )  {
				global $wp_version;

				if ( $_GET['_escaped_fragment_'] && strrpos( $_GET['_escaped_fragment_'], ':' ) > strrpos( $_GET['_escaped_fragment_'], '/' ) ) {
					$type = 'flat';
					$index_uri = Muut_Post_Utility::getChannelIndexUri( $page_id ) . $remote_path = substr( $_GET['_escaped_fragment_'], strrpos( $_GET['_escaped_fragment_'], ':' ) );
				} else {
					$type = 'threaded';
					$index_uri = Muut_Post_Utility::getChannelIndexUri( $page_id );
				}

				$request_args = array(
					'timeout' => 6,
					'user-agent' => 'WordPress/' . $wp_version . '; Muut Plugin/' . Muut::MUUTVERSION .'; ' . home_url(),
				);
				$request_for_index = wp_remote_get( $index_uri, $request_args );

				if ( wp_remote_retrieve_response_code( $request_for_index ) == 200 ) {
					$response_content = wp_remote_retrieve_body( $request_for_index );

					if ( $response_content != '' ) {
						if ( $_GET['_escaped_fragment_'] != '' ) {
							$remote_path = $_GET['_escaped_fragment_'];
						} else {
							$remote_path = Muut_Post_Utility::getChannelRemotePath( $page_id );
						}
						switch ( $type ) {
							case 'flat':
								$content = $this->getFlatIndexContent( $response_content );
								break;
							case 'threaded':
							default:
								$content = $this->getThreadedIndexContent( $response_content, $remote_path );
								break;
						}
					}
				}
			}

			return $content;
		}

		/**
		 * Filters the Forum embed content to render the index content rather than the full Forum embed code.
		 *
		 * @param string $content The current embed content.
		 * @param int $page_id The page for which we are filtering the embed.
		 * @return string The filtered content.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function filterForumIndexContent( $content, $page_id ) {

			return $content;
		}

		/**
		 * Filters the commenting embed anchor to render the index content rather than the commenting anchor.
		 *
		 * @param string $content The current embed content (anchor).
		 * @param int $post_id The post for which we are filtering the embed.
		 * @return string The filtered content.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function filterCommentsOverrideIndexContent( $content, $post_id, $type ) {
			if ( $this->isUsingEscapedFragments() )  {
				global $wp_version;

				if ( $_GET['_escaped_fragment_'] && strrpos( $_GET['_escaped_fragment_'], ':' ) > strrpos( $_GET['_escaped_fragment_'], '/' ) ) {
					$type = 'flat';
					$index_uri = Muut_Comment_Overrides::instance()->getCommentsIndexUri( $post_id ) . $remote_path = substr( $_GET['_escaped_fragment_'], strrpos( $_GET['_escaped_fragment_'], ':' ) );
				} else {
					$type = 'threaded';
					$index_uri = Muut_Comment_Overrides::instance()->getCommentsIndexUri( $post_id );
				}
				$request_args = array(
					'timeout' => 6,
					'user-agent' => 'WordPress/' . $wp_version . '; Muut Plugin/' . Muut::MUUTVERSION .'; ' . home_url(),
				);
				$request_for_index = wp_remote_get( $index_uri, $request_args );

				if ( wp_remote_retrieve_response_code( $request_for_index ) == 200 ) {
					$response_content = wp_remote_retrieve_body( $request_for_index );
					if ( $response_content != '' ) {
						switch ( $type ) {
							case 'threaded':
								$remote_path = Muut_Comment_Overrides::instance()->getCommentsPath( $post_id );
								$remote_path = substr( $remote_path, 0, strrpos( $remote_path, '/' ) + 1 );
								$content =  $this->getThreadedIndexContent( $response_content, $remote_path );
								break;
							case 'flat':
							default:
								$content = $this->getFlatIndexContent( $response_content );
								break;

						}
					}
				}
			}
			return $content;
		}

		/**
		 * Grabs the proper markup from the return body (just the muuts) that should be rendered.
		 *
		 * @param string $content The markup we will be filtering.
		 * @return string The content we actually want to display.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function getFlatIndexContent( $content ) {
			// Make sure to only get the content we want.
			$new_content = $content;
			$new_content = substr( strstr( $new_content, '</header>' ), 9 );
			$new_content = substr( $new_content, 0, strpos( $new_content, '<body>' ) );

			if ( $new_content ) {
				$content = $new_content;
			}
			return $content;
		}

		/**
		 * Grabs the proper markup from the return body of the Muut indexes for Non-flat channels.
		 *
		 * @param string $content The markup we will be filtering.
		 * @return string The content we actually want to display.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function getThreadedIndexContent( $content, $remote_path = '' ) {
			// Make sure to only get the content we want.
			$new_content = $content;
			$new_content = strstr( $new_content, '<ul id="moots">' );
			$new_content = substr( $new_content, 0, strpos( $new_content, '</body>' ) );

			if ( $remote_path != '' ) {
				$remote_path = substr( $remote_path, 0, strrpos( $remote_path, '/' ) + 1 );
			}

			// Replace links within the threaded response with new hasbang urls (to lead to the "share" location.
			$new_content = str_replace( '<a href="./', '<a href="' . get_permalink() . '#!/' . $remote_path, $new_content );

			if ( $new_content ) {
				$content = $new_content;
			}
			return $content;
		}
	}
}