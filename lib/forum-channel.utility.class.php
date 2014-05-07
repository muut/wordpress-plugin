<?php
/**
 * The Forum Channel static class that contains all the static methods required to interact with a Forum Channel that
 * are Muut-related. The Forum Channel registers a new post type that is responsible for the Forum Channel.
 * Note that it is mostly for organizational / storage purposes, rather than using the admin UX or frontend
 * display methods. While technically being a WP_Post, they will really act like a cross between posts and
 * "categories"/taxonomy.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Forum_Channel_Utility' ) ) {

	/**
	 * Muut Forum Channel Utility class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Forum_Channel_Utility
	{

		/**
		 * The name for the custom post type for Forum Channels.
		 */
		const FORUMCHANNEL_POSTTYPE = 'muut_forum_channel';

		/**
		 * The name for the forum channel header taxonomy.
		 */
		const FORUMCHANNELHEADER_TAXONOMY = 'muut_forum_channel_header';

		/**
		 * The Muut path meta name for a given Muut channel.
		 */
		const META_REMOTEPATH = 'muut_channel_remote_path';

		/**
		 * The Muut channel headers option name.
		 */
		const FORUMCHANNELHEADERS_OPTION = 'muut_channel_headers';

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Forum_Channel_Utility (Except it can never be called).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function __construct() {}

		/**
		 * Register the Forum Channel custom post type (CPT).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function registerPostType() {
			$post_type_labels = array(
				'name' => _x( 'Forum Channels', 'post type general name', 'muut' ),
				'singular_name' => _x( 'Forum Channel', 'post type singular name', 'muut' ),
				'menu_name' => _x( 'Channels', 'admin menu', 'muut' ),
				'name_admin_bar' => _x( 'Channel', 'add new on admin bar', 'muut' ),
				'add_new' => _x( 'Add New', 'new muut channel', 'muut' ),
				'add_new_item' => __( 'Add New Channel', 'muut' ),
				'new_item' => __( 'New Forum Channel', 'muut' ),
				'edit_item' => __( 'Edit Forum Channel', 'muut' ),
				'view_item' => __( 'View Channel', 'muut' ),
				'all_items' => __( 'All Forum Channels', 'muut' ),
				'search_items' => __( 'Search Forum Channels', 'muut' ),
				'parent_item_colon' => __( 'Parent Channels:', 'muut' ),
				'not_found' => __( 'No forum channels found.', 'muut' ),
				'not_found_in_trash' => __( 'No forum channels found in Trash.', 'muut' ),
			);

			$post_type_args = array(
				'labels' => $post_type_labels,
				'public' => false,
				'show_in_nav_menus' => true,
				'capability_type' => 'page',
				'hierarchical' => true,
				'taxonomies' => array(
					self::FORUMCHANNELHEADER_TAXONOMY,
				),
			);

			register_post_type( self::FORUMCHANNEL_POSTTYPE, $post_type_args );


			$taxonomy_labels = array(
				'name' => _x( 'Forum Channel Headers', 'taxonomy general name', 'muut' ),
				'singular_name' => _x( 'Forum Channel Header', 'taxonomy singular name', 'muut' ),
				'search_items' => __( 'Search Channel Headers', 'muut' ),
				'all_items' => __( 'All Channel Headers', 'muut'),
				'parent_item' => __( 'Parent Channel Header', 'muut' ),
				'parent_item_colon' => __( 'Parent Channel Header:', 'muut' ),
				'edit_item' => __( 'Edit Channel Header', 'muut' ),
				'update_item' => __( 'Update Channel Header', 'muut' ),
				'add_new_item' => __( 'Add New Channel Header', 'muut' ),
				'new_item_name' => __( 'New Channel Header Name', 'muut' ),
				'menu_name' => __( 'Channel Header', 'muut' ),
			);

			$taxonomy_args = array(
				'labels' => $taxonomy_labels,
				'hierarchical' => false,
				'public' => false,
			);

			register_taxonomy( self::FORUMCHANNELHEADER_TAXONOMY, self::FORUMCHANNEL_POSTTYPE, $taxonomy_args );
		}

		/**
		 * Gets a nested array of channel header IDs as keys containing the channel posts as a sub-array for each
		 * header id.
		 *
		 * @return array The array of channel headers and their channels.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getForumChannelHeaders() {
			$args = array(
				'hide_empty' => false,
			);
			$all_channel_headers = get_terms( self::FORUMCHANNELHEADER_TAXONOMY, $args );

			$channel_headers_keys = array();
			foreach ( $all_channel_headers as $header ) {
				// Need to append 'id-' to keys to make sure they are interpreted as strings for re-ordering.
				$channel_headers_keys['header-' . $header->term_id] = $header;
			}

			$order_of_headers = muut()->getOption( self::FORUMCHANNELHEADERS_OPTION, array() );

			foreach ( $order_of_headers as &$value ) {
				$value = 'header-' . $value;
			}

			$channel_headers_sorted = array_merge( array_flip( $order_of_headers ), $channel_headers_keys );

			$channel_headers = array();

			foreach( $channel_headers_sorted as $header ) {
				if ( is_object( $header ) ) {
					$args = array(
						'posts_per_page' => '-1',
						self::FORUMCHANNELHEADER_TAXONOMY => $header->slug,
						'orderby' => 'menu_order',
						'order' => 'asc',
						'post_type' => self::FORUMCHANNEL_POSTTYPE,
					);
					$channel_posts = get_posts( $args );

					$channel_headers[$header->term_id] = $channel_posts;
				}
			}

			return $channel_headers;
		}

		/**
		 * Gets the name title of a channel header.
		 *
		 * @param int $header_id The header id.
		 * @return string The title of the header id.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getChannelHeaderTitle( $header_id ) {
			if ( !is_numeric( $header_id ) ) {
				return '';
			}

			$term = get_term( $header_id, self::FORUMCHANNELHEADER_TAXONOMY );

			if ( $term === null || get_class( $term ) === 'WP_Error' ) {
				return '';
			} else {
				return $term->name;
			}
		}

		/**
		 * Checks if a given channel should not be displayed in all posts.
		 *
		 * @param int $channel_id The post id of the Muut channel.
		 * @return bool Whether the channel should be displayed in all posts.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function isAllpostsChannel( $channel_id ) {
			if ( !is_numeric( $channel_id ) ) {
				return false;
			}

			$show_in_posts_meta = get_post_meta( $channel_id, 'muut_show_in_allposts', true );

			if ( !$show_in_posts_meta ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Creates a new custom navigation header (adds a term to the header taxonomy).
		 *
		 * @param string $name The name for the header we are adding.
		 * @return int|false The term ID or false on error.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function createNavigationHeader( $name ) {
			if ( !is_string( $name ) ) {
				return false;
			}

			$args = apply_filters( 'muut_create_navigation_header_args', array(), $name );

			$term = wp_insert_term( $name, self::FORUMCHANNELHEADER_TAXONOMY, $args );

			if ( is_array( $term ) ) {
				return $term['term_id'];
			} else {
				return false;
			}
		}

		/**
		 * Updates an existing custom navigation header.
		 *
		 * @param int $term_id The Term ID of the header we are editing.
		 * @param array $args The args we are using to edit the existing header.
		 * @return bool Whether the update was successful.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function updateNavigationHeader( $term_id, $args = array() ) {
			if ( !is_int( $term_id ) ) {
				return false;
			}

			// Filter the args we will be passing to the wp_update_term() function.
			$args = apply_filters( 'muut_update_navigation_header_args', array(
					'term_id' => $term_id,
					'name' => $args['name'],
				),
				$term_id
			);

			$update = wp_update_term( $term_id, self::FORUMCHANNELHEADER_TAXONOMY, $args );

			// If it returns an array (the success result for updating a term), return true.
			if ( is_array( $update ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Creates a new forum channel (custom WP_Post type).
		 *
		 * @param string $name The channel name/title.
		 * @param array $custom_args The Muut-specific args for creation.
		 * @param array $post_args The args for creating the actual WP Post.
		 * @return int|false The post id for the "channel" or false on error.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function createForumChannel( $name, $custom_args = array(), $post_args = array() ) {
			$defaults = muut()->getOption( 'forum_channel_defaults', array() );

			$post_args_defaults = array(
				'post_title' => $name,
				'post_type' => self::FORUMCHANNEL_POSTTYPE,
				'post_status' => 'publish',
				'comment_status' => 'closed',
			);

			error_log( print_r( $post_args_defaults, true ) );

			// Filter the args we will be passing to the post insert.
			$post_args = wp_parse_args( apply_filters( 'muut_create_forum_channel_post_args', $post_args, $name ), $post_args_defaults );

			$forum_channel_id = wp_insert_post( $post_args );

			if ( !is_int( $forum_channel_id ) ) {
				return false;
			}

			$channel_post = get_post( $forum_channel_id );

			$custom_args_defaults = array(
				'show_in_allposts' => $defaults['show_in_allposts'],
				'channel_remote_path' => $channel_post->post_name,
			);

			$custom_args = wp_parse_args( apply_filters( 'muut_create_forum_channel_args', array_filter( $custom_args ), $name, $post_args ), $custom_args_defaults );

			// Save the custom args as post meta.
			foreach( $custom_args as $arg_name => $arg_value ) {
				update_post_meta( $forum_channel_id, 'muut_' . $arg_name, $arg_value );
			}

			return $forum_channel_id;
		}

		/**
		 * Updates an already existing Forum Channel (a custom WP_Post type).
		 *
		 * @param int $post_id the WP_Post id.
		 * @param array $custom_args The Muut-specific args to pass.
		 * @param array $post_args The WordPress post args for updating the WP_Post.
		 * @return bool True on success, false on failure.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function updateForumChannel( $post_id, $custom_args = array(), $post_args = array() ) {
			if ( !is_int( $post_id ) ) {
				return false;
			}

			$defaults = muut()->getOption( 'forum_channel_defaults', array() );

			$post_args_defaults = array(
				'ID' => $post_id,
				'post_status' => 'publish'
			);

			// Filter the args we will be passing to the update.
			$post_args = wp_parse_args( apply_filters( 'muut_update_forum_channel_post_args', $post_args, $post_id ), $post_args_defaults );

			$update = wp_update_post( $post_args );

			if ( !is_numeric( $update ) ) {
				return false;
			}

			$channel_post = get_post( $post_id );

			$custom_args_defaults = array(
				'show_in_allposts' => $defaults['show_in_allposts'],
				'channel_remote_path' => $channel_post->post_name,
			);

			$custom_args = wp_parse_args( apply_filters( 'muut_create_forum_channel_args', array_filter( $custom_args ), $post_args ), $custom_args_defaults );


			// Save the custom args as post meta.
			foreach( $custom_args as $arg_name => $arg_value ) {
				update_post_meta( $post_id, 'muut_' . $arg_name, $arg_value );
			}

			// Success.
			return true;
		}

		/**
		 * Gets the remote path for the channel.
		 *
		 * @param int $channel_id The post id of the channel.
		 * @return string The remote path.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getRemotePath( $channel_id ) {
			$path = get_post_meta( $channel_id, self::META_REMOTEPATH, true );

			if ( $path == '' ) {
				$channel_post = get_post( $channel_id );
				$path = $channel_post->post_name;
			}

			return apply_filters( 'muut_get_channel_remote_path', $path, $channel_id );
		}
	}
}