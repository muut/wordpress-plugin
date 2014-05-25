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
			add_filter( 'muut_comment_overrides_embed_content', array( $this, 'filterCommentsOverrideIndexContent' ), 10, 2 );
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

				$index_uri = Muut_Post_Utility::getChannelIndexUri( $page_id );

				error_log( $index_uri );
				$request_args = array(
					'timeout' => 6,
					'user-agent' => 'WordPress/' . $wp_version . '; Muut Plugin/' . Muut::MUUTVERSION .'; ' . home_url(),
				);
				$request_for_index = wp_remote_get( $index_uri, $request_args );

				if ( wp_remote_retrieve_response_code( $request_for_index ) == 200 ) {
					$response_content = wp_remote_retrieve_body( $request_for_index );

					if ( $response_content != '' ) {
						$content = $response_content;
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
		public function filterCommentsOverrideIndexContent( $content, $post_id ) {
			if ( $this->isUsingEscapedFragments() )  {
				global $wp_version;

				$index_uri = Muut_Comment_Overrides::instance()->getCommentsIndexUri( $post_id );

				error_log( $index_uri );
				$request_args = array(
					'timeout' => 6,
					'user-agent' => 'WordPress/' . $wp_version . '; Muut Plugin/' . Muut::MUUTVERSION .'; ' . home_url(),
				);
				$request_for_index = wp_remote_get( $index_uri, $request_args );

				if ( wp_remote_retrieve_response_code( $request_for_index ) == 200 ) {
					$response_content = wp_remote_retrieve_body( $request_for_index );

					if ( $response_content != '' ) {
						$content = $response_content;
					}
				}
			}
			return $content;
		}
	}
}