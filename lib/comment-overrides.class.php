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
		 * @return Muut_Comment_Overrides
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

			$post_commenting_options = Muut_Post_Utility::getPostOption( $post_id, 'commenting_settings' );

			if ( $domain == '' ) {
				// Assign the domain name to the post for permanent reference.
				$domain = muut()->getOption( 'comments_base_domain' );
				update_post_meta( $post_id, 'muut_post_domain', apply_filters( 'muut_post_comments_domain', $domain, $post_id ) );
			}

			$path = $domain . '/' . $post_id;

			if ( !isset( $post_commenting_options['type'] ) || $post_commenting_options['type'] == 'flat' ) {
				$path .= ':comments';
			}

			if ( !$full_path ) {
				return $path;
			} else {
				return muut()->getForumName() . '/' . $path;
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
			global $post;
			$disabled_post_types = apply_filters( 'muut_disabled_comment_override_post_types', array() );

			if ( muut()->getOption( 'replace_comments', false )
				&& !in_array( $post->post_type, $disabled_post_types ) ) {

				if ( Muut_Post_Utility::isMuutCommentingPost( $post->ID ) ) {
					// TODO: Make it so it checks if the post type is supposed to be overridden.
					$template = Muut_Template_Loader::instance()->locateTemplate( 'comments.php' );
				}
			}

			return $template;
		}

		/**
		 * Gets the comments anchor for a given post.
		 *
		 * @param int $post_id The ID of the post we are getting the comments anchor for.
		 * @param bool $echo Whether to echo the value.
		 * @return string|void The markup for the Muut comments embed or void if echoed.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function commentsOverrideAnchor( $post_id, $echo = true ) {
			if ( !is_numeric( $post_id ) ) {
				return false;
			}

			$path = $this->getCommentsPath( $post_id, true );

			$post_type = get_post_type_object( get_post_type( $post_id ) );
			$post_type_name = $post_type->labels->singular_name;

			$post_commenting_options = Muut_Post_Utility::getPostOption( $post_id, 'commenting_settings' );

			$settings = 'data-show_online="false" data-channel="' . __( 'Comments', 'muut' ) . '" title="' . $post_type_name . ': ' . get_the_title( $post_id ) . '" ';

			if ( isset( $post_commenting_options['type'] ) && $post_commenting_options['type'] == 'threaded' ) {
				$settings .= 'data-show_title="false" ';
			} else {
				$settings .= 'data-show_title="true" ';
			}

			if ( isset( $post_commenting_options['disable_uploads'] ) && $post_commenting_options['disable_uploads'] == '1' ) {
				$settings .= 'data-upload="false" ';
			} else {
				$settings .= 'data-upload="true" ';
			}

			if ( !$path )
			return false;

			$id_attr = muut()->getWrapperCssId() ? 'id="' . muut()->getWrapperCssId() . '"' : '';
			$anchor = '<section id="comments"><a ' . $id_attr . ' class="' . muut()->getWrapperCssClass() . '" href="' . muut()->getContentPathPrefix() . 'i/' . $path . '" ' . $settings . '>' . __( 'Comments', 'muut' ) . '</a></section>';
			if ( $echo ) {
				echo $anchor;
			} else {
				return $anchor;
			}
		}
	}
}