<?php
/**
 * The singleton class that contains all functionality regarding the "override WordPress comments" functionality.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Comment_Overrides' ) ) {

	/**
	 * Muut Comment Overrides class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Comment_Overrides
	{
		/**
		 * @static
		 * @property Muut_Comment_Overrides The instance of the class.
		 */
		protected static $instance;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Comment_Overrides The instance.
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
		 * @return Muut_Initializer
		 * @author Paul Hughes
		 * @since  3.0
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
		 * @since  3.0
		 */
		protected function addActions() {

		}

		/**
		 * Adds the filters used by this class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function addFilters() {
			add_filter( 'comments_template', array( $this, 'commentsTemplate' ) );
		}

		/**
		 * Gets the post's comments path.
		 *
		 * @param int $post_id The ID of the post we are fetching the Muut comments path for.
		 * @param bool $full_path Whether to retrieve the full path, including the root forum.
		 * @return string The post's Muut comments path.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getCommentsPath( $post_id, $full_path = false ) {
			if ( !is_numeric( $post_id) ) {
				return false;
			}

			$domain = get_post_meta( $post_id, 'muut_post_domain', true );

			if ( $domain == '' ) {
				// Assign the domain name to the post for permanent reference.
				$domain = $_SERVER['SERVER_NAME'];
				update_post_meta( $post_id, 'muut_post_domain', apply_filters( 'muut_post_comments_domain', $domain, $post_id ) );
			}

			$path = $domain . '/' . $post_id . ':comments';
			if ( !$full_path ) {
				return $path;
			} else {
				return muut()->getRemoteForumName() . '/' . $path;
			}
		}

		/**
		 * Gets the proper comments template when overrides are on.
		 *
		 * @param string $template The current comments template being fetched.
		 * @return string The modified template to fetch.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function commentsTemplate( $template ) {
			if ( muut()->getOption( 'replace_comments', false ) ) {
				global $post;

				// TODO: Make it so it checks if the post type is supposed to be overridden.
				$template = Muut_Template_Loader::instance()->locateTemplate( 'comments.php' );
			}

			return $template;
		}

		/**
		 * Gets the comments anchor for a given post.
		 *
		 * @param int $post_id The ID of the post we are getting the comments anchor for.
		 * @return string The markup for the Muut comments embed.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function commentsOverrideAnchor( $post_id, $echo = true ) {
			if ( !is_numeric( $post_id ) ) {
				return false;
			}

			$path = $this->getCommentsPath( $post_id, true );

			$settings = 'data-show_online="false" data-upload="false" ';

			if ( !$path )
				return false;

			$anchor = '<a class="moot" href="/i/' . $path . '" ' . $settings . '>' . __( 'Comments', 'muut' ) . '</a>';
			if ( $echo ) {
				echo $anchor;
			} else {
				return $anchor;
			}
		}
	}
}