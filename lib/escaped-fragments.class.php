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
		 * @property string The context of the embed for which we are getting the index:
		 * 						channel, forum, or commenting representing the page embed.
		 */
		protected $context;

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
			add_action( 'wp_head', array( $this, 'printEscapedFragmentMetaRequire' ) );
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
			add_filter( 'muut_forum_page_embed_content', array( $this, 'filterForumIndexContent' ), 10, 2 );
			add_filter( 'muut_comment_overrides_embed_content', array( $this, 'filterCommentsOverrideIndexContent' ), 10, 3 );
		}

		/**
		 * Prints the proper meta tag to make sure that googlebots run this page with the _escaped_fragment_ parameter.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function printEscapedFragmentMetaRequire() {
			global $post;
			if ( isset( $post ) && is_singular() && ( Muut_Post_Utility::isMuutPost( $post->ID ) || Muut_Post_Utility::isMuutCommentingPost( $post->ID ) ) ) {
				echo '<meta name="fragment" content="!">';
			}
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

				if ( isset( $_GET['_escaped_fragment_'] ) && isset ( $post ) && is_a( $post, 'WP_Post' ) && ( Muut_Post_Utility::isMuutPost( $post->ID ) || Muut_Post_Utility::isMuutCommentingPost( $post->ID ) ) ) {
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
				$this->context = 'channel';

				if ( $_GET['_escaped_fragment_'] ) {
					$remote_path = $_GET['_escaped_fragment_'][0] == '/' ? substr( $_GET['_escaped_fragment_'], 1 ) : $_GET['_escaped_fragment_'];
				} else {
					$remote_path = Muut_Post_Utility::getChannelRemotePath( $page_id );
				}

				$content = $this->getIndexContentForPath( $remote_path );
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
			if ( $this->isUsingEscapedFragments() ) {
				$this->context = 'forum';

				if ( $_GET['_escaped_fragment_'] ) {
					$remote_path = $_GET['_escaped_fragment_'][0] == '/' ? substr( $_GET['_escaped_fragment_'], 1 ) : $_GET['_escaped_fragment_'];
				} else {
					$remote_path = '';
				}

				$content = $this->getIndexContentForPath( $remote_path );
			}
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
				$this->context = 'commenting';

				if ( $_GET['_escaped_fragment_'] ) {
					$remote_path = substr( $_GET['_escaped_fragment_'], strrpos( $_GET['_escaped_fragment_'], ':' ) );
				} else {
					$remote_path = Muut_Comment_Overrides::instance()->getCommentsPath( $post_id );
				}

				$content = $this->getIndexContentForPath( $remote_path );
			}
			return $content;
		}

		/**
		 * Gets the body content of an index request for a given Muut path (relative path).
		 *
		 * @param string $path The path (relative to the registered forum name).
		 * @param bool $force_muut_server Will bypass any S3 bucket or other setup and go directly to the muut.com index.
		 * @return string The content.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getIndexContentForPath( $path, $force_muut_server = false ) {
			global $wp_version;
			$request_args = apply_filters( 'muut_request_path_index_content_args', array(
				'timeout' => 6,
				'user-agent' => 'WordPress/' . $wp_version . '; Muut Plugin/' . Muut::MUUTVERSION .'; ' . home_url(),
			) );

			$base_uri = muut()->getForumIndexUri( $force_muut_server );

			if ( $path == '' ) {
				$base_uri = untrailingslashit( $base_uri );
			}

			$uri = $base_uri . $path;

			$request_for_index = wp_remote_get( $uri, $request_args );

			$content = '';
			if ( wp_remote_retrieve_response_code( $request_for_index ) == 200 ) {
				$response_content = wp_remote_retrieve_body( $request_for_index );

				if ( $path == '' ) {
					$content = $this->getForumIndexContent( $response_content );
				} else {
					$colon_pos = strrpos( $path, ':' );
					$last_slash_pos = strrpos( $path, '/' );
					if ( $colon_pos && ( $colon_pos > $last_slash_pos || !$last_slash_pos ) ) {
						$content = $this->getFlatIndexContent( $response_content );
					} else {
						$content = $this->getThreadedIndexContent( $response_content, $path );
					}
				}
			}
			return $content;
		}

		/**
		 * Grabs the proper markup from the return body for Forum root that should be rendered.
		 *
		 * @param string $content The markup we will be filtering.
		 * @return string The content we actually want to display.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function getForumIndexContent( $content ) {
			// Make sure to only get the content we want.
			$new_content = $content;
			$new_content = strstr( $new_content, '<ul id="forums">' );
			$new_content = substr( $new_content, 0, strpos( $new_content, '</body>' ) );

			// Replace links within the threaded response with new hasbang urls (to lead to the "share" location.
			$new_content = str_replace( '<a href="/i/' . muut()->getForumName() . '/', '<a href="' . get_permalink() . '#!/', $new_content );

			if ( $new_content ) {
				$content = $new_content;
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
			$new_content = strstr( $new_content, '<article class="seed">' );
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
			if ( $this->context == 'channel' || $this->context == 'commenting' ) {
				$new_content = strstr( $new_content, '<ul id="moots">' );
			} else {
				$new_content = strstr( $new_content, '<div id="title">' );
			}
			$new_content = substr( $new_content, 0, strpos( $new_content, '</body>' ) );

			if ( $remote_path != '' ) {
				$slash_strpos = strrpos( $remote_path, '/' );
				if ( $slash_strpos ) {
					$remote_path = substr( $remote_path, 0, $slash_strpos + 1 );
				} else {
					$remote_path = '';
				}
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