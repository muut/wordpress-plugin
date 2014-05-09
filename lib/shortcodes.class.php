<?php
/**
 * The singleton class that contains all functionality regarding the old shortcode functionality.
 * Shortcodes are scheduled to be phased out.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Shortcodes' ) ) {

	/**
	 * Muut Shortcodes class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Shortcodes
	{
		/**
		 * @static
		 * @property Muut_Shortcodes The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Shortcodes The instance.
		 * @author Paul Hughes
		 * @since  3.0
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
		 * @return Muut_Shortcodes
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
			$this->addShortcodes();
		}

		/**
		 * Adds the proper actions.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addActions() {
			add_action( 'admin_head', array( $this, 'maybeShowShortcodeNotice' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendScripts' ) );
		}

		/**
		 * Adds the proper filters.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		protected function addFilters() {
			add_filter( 'muut_requires_muut_resources', array( $this, 'checkForShortcodes' ), 10 , 2 );
		}

		/**
		 * Adds the shortcodes.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addShortcodes() {
			add_shortcode( 'muut', array($this, 'shortcode' ) );
			add_shortcode( 'no-muut', array($this, 'disable' ) );

			// Deprecated
			add_shortcode( 'moot', array($this, 'shortcode' ) );
			add_shortcode( 'no-moot', array($this, 'disable' ) );
		}

		/**
		 * Checks if the current post/page being edited uses a Moot shortcode and, if so, it
		 * displays the "We're getting rid of shortcodes" notice.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function maybeShowShortcodeNotice() {
			if ( get_current_screen()->base == 'post' ) {
				global $post;

				if ( !is_object( $post ) || !is_a( $post, 'WP_Post' ) ) {
					$post = get_post( get_the_ID() );
				}

				if ( has_shortcode( $post->post_content, 'muut' ) || has_shortcode( $post->post_content, 'moot' ) ) {
					add_action( 'admin_notices', array( $this, 'deprecatingShortcodesNotice' ) );
				}


				if ( has_shortcode( $post->post_content, 'no-muut' ) || has_shortcode( $post->post_content, 'no-moot' ) ) {
					if ( isset( $post->comment_status ) && $post->comment_status == 'open' ) {
						$post_args = array(
							'ID' => $post->ID,
							'comment_status' => 'closed',
						);
						wp_update_post( $post_args );
					}
					add_action( 'admin_notices', array( $this, 'removedNoMuutShortcodeNotice' ) );
				}
			}
		}

		/**
		 * Displays the "Deprecating shortcodes" admin notice.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function deprecatingShortcodesNotice() {
			echo '<div class="update-nag">';
			printf( __( '%sMuut Notice:%s It looks like you\'re using old shortcodes in this post. Check out our new %sadmin page%s to check out your alternatives, as we will not be supporting shortcodes much longer!', 'muut' ), '<b>', '</b>', '<a href="' . add_query_arg( array( 'page' => 'muut' ), admin_url( 'admin.php' ) ) . '">', '</a>' );
			echo '</div>';
		}

		/**
		 * Displays the "[no-muut]" shortcode replacement notice.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function removedNoMuutShortcodeNotice() {
			echo '<div class="update-nag">';
			printf( __( '%sMuut Notice:%s The [no-moot]/[no-muut] shortcode has been replaced by simply deactivating comments for a given page or post. You can do that in the Discussion metabox on this editor and then remove the shortcode from the content.', 'muut' ), '<b>', '</b>' );
			echo '</div>';
		}

		/**
		 * Handles the enqueueing of frontend scripts for pages with shortcodes on them.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueFrontendScripts() {
			global $post;

			if ( !is_object( $post ) || !is_a( $post, 'WP_Post' ) ) {
				$post = get_post( get_the_ID() );
			}

			if ( has_shortcode( $post->post_content, 'moot' ) || has_shortcode( $post->post_content, 'muut' ) ) {
				wp_enqueue_script( 'muut' );
				wp_enqueue_style( 'muut-forum-css' );
			}
		}

		/**
		 * Disables Muut commenting on this post/page. Used to be controlled with JS,
		 * now should be done by disabling comments on the actual post/page.
		 * This currently does so for the user, and we should remind them with a notice that we are
		 * dumping this shortcode.
		 *
		 * @return string
		 * @author Paul Hughes
		 */
		public function disable() {
			global $post;

			if ( !is_object( $post ) || !is_a( $post, 'WP_Post' ) ) {
				$post = get_post( get_the_ID() );
			}

			if ( isset( $post->comment_status ) && $post->comment_status == 'open' ) {
				$post_args = array(
					'ID' => $post->ID,
					'comment_status' => 'closed',
				);
				wp_update_post( $post_args );
			}

			// Nowadays this actually does nothing.
			return '<span id="no-moot"></span>';
		}

		/**
		 * Includes the proper Muut embed achor.
		 * We also will be dumping this shortcode.
		 *
		 * @param array $params The parameters for the shortcode.
		 * @return string
		 * @author Paul Hughes
		 */
		public function shortcode($params) {

			extract( shortcode_atts( array(
				 'forum' => false,
				 'threaded' => false,
				 'path' => false

			 ), $params) );

			$forum_name = muut()->getForumName();

			if ($forum_name == null) return "";

			$id_attr = muut()->getWrapperCssId() ? 'id="' . muut()->getWrapperCssId() . '"' : '';

			$tag = '<a ' . $id_attr . ' class="' . muut()->getWrapperCssClass() . '" href="' . muut()->getContentPathPrefix() . 'i/' . $forum_name;
			$page_slug = sanitize_title( get_the_title() );

			// (bool ? this : that) not working
			if ( $forum )   return $tag .'">' . $forumname . 'forums</a>';
			if ( $threaded ) return $tag .'/wordpress/' . $page_slug . '">Comments</a>';
			if ( $path )    return $tag . '/' . $path .'">Comments are here</a>';
			return $tag . '/wordpress:' . $page_slug . '">Comments</a>';
		}

		/**
		 * Checks if the current post uses a shortcode and if so, returns true.
		 *
		 * @param bool $requires_resources Whether the current post is a forum page.
		 * @param int $page_id The page ID we are checking.
		 * @return bool Whether it is a forum page, considering shortcodes.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function checkForShortcodes( $requires_resources, $page_id ) {
			if ( !$requires_resources ) {
				if ( $page_id == get_the_ID() ) {
					global $post;

					if ( !is_object( $post ) || !is_a( $post, 'WP_Post' ) ) {
						$post = get_post( $page_id );
					}
				} else {
					$post = get_post( $page_id );
				}
				if ( has_shortcode( $post->post_content, 'muut' ) || has_shortcode( $post->post_content, 'moot' ) ) {
					$requires_resources = true;
				}
			}

			return $requires_resources;
		}
	}
}