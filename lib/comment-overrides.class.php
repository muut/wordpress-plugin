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
			add_filter( 'get_comments_link', array( $this, 'commentsLink' ), 10, 2 );
			add_filter( 'get_comments_number', array( $this, 'muutCommentsNumber' ), 10, 2 );
			add_filter( 'the_posts', array( $this, 'fetchCommentCountForMuutPosts' ) );
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

			$post = get_post( $post_id );
			$update_timestamps = muut()->getOption( 'update_timestamps', array() );
			if ( isset( $update_timestamps['3.0'] ) && get_post_time( 'U', false, $post ) < $update_timestamps['3.0'] ) {
				$path = $domain . ':' . sanitize_title( $post->post_title );
			} else {
				$path = $domain . '/' . $post_id;
				if ( !isset( $post_commenting_options['type'] ) || $post_commenting_options['type'] == 'flat' ) {
					$path .= ':comments';
				}
			}

			if ( !$full_path ) {
				return $path;
			} else {
				return muut()->getForumName() . '/' . $path;
			}
		}



		/**
		 * Gets a comment section's full index URI.
		 *
		 * @param int $post_id The post whose comment section remote URI we are fetching.
		 * @return string The full index URI.
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function getCommentsIndexUri( $post_id ) {
			if( !is_numeric( $post_id ) ) {
				return false;
			}

			$base_uri = muut()->getForumIndexUri();

			$uri = $base_uri . $this->getCommentsPath( $post_id, false );

			return apply_filters( 'muut_comments_index_uri', $uri, $post_id );
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

			if ( muut()->getForumName() != ''
				&& muut()->getOption( 'replace_comments', false )
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

			$settings = 'data-show_online="false" ';

			if ( isset( $post_commenting_options['type'] ) && $post_commenting_options['type'] == 'threaded' ) {
				$settings .= 'data-show_title="false" title="Comments on ' . $post_type_name . ': ' . get_the_title( $post_id ) . '" data-channel="' . $post_type_name . ': ' . get_the_title( $post_id ) . '"';
			} else {
				$settings .= 'data-show_title="true" title="' . $post_type_name . ': ' . get_the_title( $post_id ) . '" data-channel="' . __( 'Comments', 'muut' ) . '" ';
			}

			if ( isset( $post_commenting_options['disable_uploads'] ) && $post_commenting_options['disable_uploads'] == '1' ) {
				$settings .= 'data-upload="false" ';
			} else {
				$settings .= 'data-upload="true" ';
			}

			if ( !$path )
			return false;

			$id_attr = muut()->getWrapperCssId() ? 'id="' . muut()->getWrapperCssId() . '_comments"' : '';
			$type = isset( $post_commenting_options['type'] ) && $post_commenting_options['type'] ? $post_commenting_options['type'] : 'flat';
			$anchor = '<div id="respond"><section id="muut_comments"><a ' . $id_attr . ' class="' . muut()->getWrapperCssClass() . '" href="' . muut()->getContentPathPrefix() . 'i/' . $path . '" ' . $settings . '>' . __( 'Comments', 'muut' ) . '</a></section></div>';
			$anchor = apply_filters( 'muut_comment_overrides_embed_content', $anchor, $post_id, $type );
			$anchor = apply_filters( 'muut_embed_content', $anchor, $post_id, $type );

			if ( $echo ) {
				echo $anchor;
			} else {
				return $anchor;
			}
		}

		/**
		 * Filters the link for the WP get_comments_link function. Posts with Muut comments should link to
		 * their anchor.
		 *
		 * @param string $link The current link.
		 * @param int $post_id The post ID.
		 * @return string The filtered link.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function commentsLink( $link, $post_id ) {
			if ( Muut_Post_Utility::isMuutCommentingPost( $post_id ) ) {
				$link = get_permalink( $post_id ) . '#' . muut()->getWrapperCssId() . '_comments';
			}

			return $link;
		}

		/**
		 * For posts that have Muut commenting enabled, set the number of comments to zero so that it does not
		 * (in most themes) show a comment count, but rather sticks with "Leave a reply."
		 *
		 * @param int $count The current comment count.
		 * @param int $post_id The post ID.
		 * @return int The filtered count.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function muutCommentsNumber( $count, $post_id ) {
			if ( Muut_Post_Utility::isMuutCommentingPost( $post_id ) ) {
				// If there is no cached value, let's go get it.
				if ( wp_cache_get( "muut-comments-{$post_id}" , 'counts' ) === false ) {
					$post = get_post( $post_id );

					if ( is_a( $post, 'WP_Post' ) ) {
						$this->fetchCommentCountForMuutPosts( array( $post ) );
					}
				}

				$count = wp_cache_get( "muut-comments-{$post_id}" , 'counts' );
			}

			return $count;
		}

		/**
		 * Gets (and caches) the comment counts for posts in the main query.
		 *
		 * @param array $posts The array of WP_Post objects that were fetched in the main query.
		 * @return array The same array.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function fetchCommentCountForMuutPosts( $posts ) {
			// Only execute this functionality if "do not fetch" is not set.
			// That filter can be used (set to true) to prevent any of this from executing.
			if ( !apply_filters( 'muut_do_not_fetch_post_counts', false ) && is_main_query() ) {
				$post_count_queue = array();
				foreach ( $posts as $post ) {
					if ( Muut_Post_Utility::isMuutCommentingPost( $post->ID ) && wp_cache_get( "muut-comments-{$post->ID}" , 'counts' ) === false ) {
						$path = '/' . $this->getCommentsPath( $post->ID, true );
						$post_count_queue[$post->ID] = $path;
					}
				}

				// As long as there is at least one post that uses Muut commenting and doesn't have a cached value...
				if ( count( $post_count_queue ) > 0 ) {
					global $wp_version;
					$api_endpoint = 'https://' . Muut::MUUTAPISERVER . '/postcounts';
					$api_args = '?path=' . join( '&path=', $post_count_queue );

					$api_call = $api_endpoint . $api_args;

					$fetch_args = array(
						'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) . ' MuutForum/' . muut()->getForumName(),
						'timeout' => apply_filters( 'muut_api_post_counts_timeout', '2' ),
					);
					$response = wp_remote_get( $api_call, $fetch_args );

					if ( is_wp_error( $response ) && ( muut()->isInDevelopMode() || !apply_filters( 'muut_suppress_api_errors', true ) ) ) {
						error_log( 'Something went wrong fetching Muut API: ' . $response->get_error_message() );
					} else {
						if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
							$body = wp_remote_retrieve_body( $response );

							$return_array = json_decode( $body );

							// Cache values for each returned post comment count.
							if ( !is_null( $return_array ) ) {
								$post_array = array_flip( $post_count_queue );
								foreach ( $post_array as $url => $id ) {
									wp_cache_set( "muut-comments-{$id}", $return_array->$url->size, 'counts' );
								}
							}
						}
					}
				}
			}

			return $posts;
		}
	}
}