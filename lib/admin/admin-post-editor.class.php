<?php
/**
 * The class that is responsible for all the admin post editor (pages, posts, etc) functionality.
 * Includes functionality for creating and using the Muut meta box.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Admin_Post_Editor' ) ) {

	/**
	 * Muut Admin Most Editor class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Admin_Post_Editor
	{
		/**
		 * @static
		 * @property Muut_Admin_Post_Editor The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property array The default tabs for Muut post saving.
		 */
		protected $defaultTabs;

		/**
		 * @property array The current tabs.
		 */
		protected $metaboxTabs;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Admin_Post_Editor The instance.
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
		 * @return Muut_Admin_Post_Editor
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();

			$this->setDefaultTabs();
		}

		/**
		 * The method for adding all actions regarding the admin post editing functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addActions() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
			add_action( 'add_meta_boxes', array( $this, 'addPostMetaBoxes' ), 1, 10 );

			add_action( 'save_post', array( $this, 'saveMuutPostSettings' ), 2, 10 );
			add_action( 'muut_save_post_tab', array( $this, 'saveMuutPostTab' ), 3, 10 );
			add_action( 'transition_post_status', array( $this, 'maybeEnableSpecificComments' ), 3, 10 );
		}

		/**
		 * The method for adding all filters regarding the custom navigation admin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addFilters() {
			add_filter( 'muut_post_editor_metabox_tabs', array( $this, 'removeCommentsTabIfDisabled' ) );
		}

		/**
		 * Enqueues necessary admin scripts for the page/post editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueAdminScripts() {
			wp_enqueue_script ( 'jquery-ui-tabs' );
			wp_enqueue_script( 'muut-admin-functions' );
			wp_enqueue_script( 'muut-admin-post-edit' );
			wp_enqueue_style( 'jquery-ui-dialog-css' );
			wp_enqueue_style( 'muut-admin-style' );
		}

		/**
		 * Sets the default tabs for Muut post saves.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function setDefaultTabs() {
			$this->defaultTabs = array(
				'commenting' => array(
					'label' => __( 'Commenting', 'muut' ),
					'functionality_label' => __( 'Commenting', 'muut' ),
					'name' => 'commenting-tab',
					'slug' => 'commenting',
					'post_types' => apply_filters( 'muut_metabox_commenting_tab_post_types', array() ),
					'meta_name' => 'commenting_settings',
					'template_location' => muut()->getPluginPath() . 'views/blocks/metabox-tab-commenting.php',
					'enabled_callback' => array( $this, 'isCommentingTabEnabled' ),
					'enable_text' => __( 'Enable Muut commenting', 'muut' ),
				),
				'channel' => array(
					'label' => __( 'Channel', 'muut' ),
					'functionality_label' => __( 'Channel embedding', 'muut' ),
					'slug' => 'channel',
					'name' => 'channel-tab',
					'post_types' => apply_filters( 'muut_metabox_channel_tab_post_types', array( 'page' ) ),
					'meta_name' => 'channel_settings',
					'template_location' => muut()->getPluginPath() . 'views/blocks/metabox-tab-channel.php',
					'enabled_callback' => array( $this, 'isChannelTabEnabled' ),
					'enable_text' => __( 'Enable channel (a standalone discussion area)', 'muut' ),
				),
				'forum' => array(
					'label' => __( 'Forum', 'muut' ),
					'functionality_label' => __( 'Forum embedding', 'muut' ),
					'name' => 'forum-tab',
					'slug' => 'forum',
					'post_types' => apply_filters( 'muut_metabox_forum_tab_post_types', array( 'page' ) ),
					'meta_name' => 'forum_settings',
					'template_location' => muut()->getPluginPath() . 'views/blocks/metabox-tab-forum.php',
					'enabled_callback' => array( $this, 'isForumTabEnabled' ),
					'enable_text' => __( 'Enable forum', 'muut' ),
				),
			);
		}

		/**
		 * Adds the metaboxes for the Page/Post admin editor.
		 * You can filter post types into the filter 'muut_do_not_load_metabox_for_post_types' to make the meta box
		 * NOT load for that post type.
		 *
		 * @param string $post_type The current post type.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addPostMetaBoxes( $post_type ) {
			$do_not_load_for_post_types = apply_filters( 'muut_do_not_load_metabox_for_post_types', array() );
			if ( muut()->getForumName() != '' && !in_array( $post_type, (Array) $do_not_load_for_post_types ) && $this->getMetaBoxTabsForCurrentPostType() ) {
				add_meta_box(
					'muut-is-forum-page',
					__( 'Muut', 'muut' ),
					array( $this, 'renderMuutPostMetaBox' ),
					$post_type,
					'side',
					'high'
				);
			}
		}

		/**
		 * Renders the metabox content for the Page Editor.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function renderMuutPostMetaBox() {
			include( muut()->getPluginPath() . 'views/admin-post-metabox.php' );
		}

		/**
		 * Gets all meta box tabs, regardless of post type.
		 *
		 * @return array The tabs.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getAllMetaBoxTabs() {
			if ( !isset( $this->metaboxTabs ) ) {
				$this->metaboxTabs = apply_filters( 'muut_post_editor_metabox_tabs', $this->defaultTabs );
			}
			return $this->metaboxTabs;
		}

		/**
		 * Gets the meta box tabs to be rendered in the meta box (depending on post type, or other things).
		 * Filterable, so that tabs can be added or removed.
		 * The post_types property for tabs is the post types it should be displayed for.
		 * If it is an empty array, display that tab on all post types.
		 *
		 * @return array The meta box tabs to use.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getMetaBoxTabsForCurrentPostType() {

			$all_tabs = $this->getAllMetaBoxTabs();

			$post_type = $this->getCurrentPostType();

			$tabs = array();
			if ( !is_null( $post_type ) ) {
				foreach( $all_tabs as $slug => $tab ) {
					if ( !isset( $tab['post_types'] ) || empty( $tab['post_types'] ) || in_array( $post_type, (Array) $tab['post_types'] ) ) {
						$tabs[$slug] = $tab;
					}
				}
			}

			return $tabs;
		}

		/**
		 * Gets the current post type on the admin side. Not *specific* to Muut.
		 * Borrowed from http://themergency.com/wordpress-tip-get-post-type-in-admin/
		 *
		 * @return string|null The current post type we are working with.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function getCurrentPostType() {
			global $post, $typenow, $current_screen;

			//If we have a post so we can just get the post type from that.
			if ( $post && $post->post_type ) {
				return $post->post_type;
			// Otherwise, lets check the global $typenow - set in admin.php
			} elseif( $typenow ) {
				return $typenow;
			// Otherwise, lets check the global $current_screen object - set in sceen.php
			} elseif( $current_screen && $current_screen->post_type ) {
				return $current_screen->post_type;
			// Lastly check the post_type querystring
			} elseif( isset( $_REQUEST['post_type'] ) ) {
				return sanitize_key( $_REQUEST['post_type'] );
			}

			// If nothing worked, we don't know the post type!
			return null;
		}

		/**
		 *  Runs the actions for active tabspost/page's Muut information.
		 *
		 * @param int $post_id The id of the post (page) being saved.
		 * @param WP_Post $post The post (page) object that is being saved.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function saveMuutPostSettings( $post_id, $post ) {
			$tabs = $this->getMetaBoxTabsForCurrentPostType();

			$has_last_active = false;
			$last_active = '0';
			foreach( $tabs as $tab_slug => $tab ) {
				// Execute actions for active tabs.
				// Next line the $_POST index could be a new hidden, if multiple tabs should be saved.
				if ( isset( $_POST['muut_tab_last_active_' . $tab['name'] ] ) && $_POST['muut_tab_last_active_' . $tab['name'] ] == '1' ) {
					$has_last_active = true;
					$last_active = $tab['name'];
					update_post_meta( $post_id, 'muut_last_active_tab', $tab['name'] );
				}
				if ( isset( $_POST['muut_tab_last_active_' . $tab['name'] ] ) && $_POST['muut_tab_last_active_' . $tab['name'] ] ) {
					do_action( 'muut_save_post_tab', $tab, $post_id, $post );
					do_action( 'muut_save_post_tab_' . $tab['name'], $tab, $post_id, $post );
				}
			}
			if ( !$has_last_active ) {
				update_post_meta( $post_id, 'muut_last_active_tab', '0' );
			}
			if ( ( !$last_active || $last_active != 'forum-tab' ) && Muut_Post_Utility::getForumPageId() == $post_id ) {
				Muut_Post_Utility::removeAsForumPage( $post_id );
			}
		}

		/**
		 * Saves the settings on a given tab.
		 *
		 * @param array $tab The tab we are saving.
		 * @param int $post_id The ID of the post we are saving.
		 * @param WP_Post $post The post (or page) object that is being saved.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function saveMuutPostTab( $tab, $post_id, $post ) {
			$muut_tabs_to_save = array();
			foreach ( $this->defaultTabs as $default_tab ) {
				$muut_tabs_to_save[] = $default_tab['name'];
			}

			if ( !in_array( $tab['name'], $muut_tabs_to_save ) || ( !empty( $tab['post_types'] ) && !in_array( $post->post_type, (Array) $tab['post_types'] ) ) ) {
				return;
			}
			switch( $tab['name'] ) {
				case 'commenting-tab':
					$tab_options = array();
					if ( isset ( $_POST[$tab['meta_name']] ) ) {
						$tab_options = $_POST[$tab['meta_name']];
					}
					$boolean_values = array(
						'disable_uploads',
					);

					foreach ( $boolean_values as $boolean_value ) {
						$tab_options[$boolean_value] = isset( $tab_options[$boolean_value] ) ? $tab_options[$boolean_value] : '0';
					}

					Muut_Post_Utility::setPostOption( $post_id, $tab['meta_name'], $tab_options );

				break;

				case 'channel-tab':
					$tab_options = array();
					if ( isset( $_POST[$tab['meta_name']] ) ) {
						$tab_options = $_POST[$tab['meta_name']];
						$tab_current_options = Muut_Post_Utility::getPostOption( $post_id, $tab['meta_name'] );
					}
					$boolean_values = array(
						'hide_online',
						'disable_uploads',
					);

					foreach ( $boolean_values as $boolean_value ) {
						$tab_options[$boolean_value] = isset( $tab_options[$boolean_value] ) ? $tab_options[$boolean_value] : '0';
					}

					$channel_path = isset( $tab_options['channel_path'] ) ? $tab_options['channel_path'] : '';
					if ( !isset( $tab_options['channel_path'] )
						|| $tab_options['channel_path'] == '' ) {
						// If no path is saved yet, let's generate one and save it.
						if ( !Muut_Post_Utility::getChannelRemotePath( $post_id, true ) ) {
							$path = sanitize_title( $post->post_name );
							$ancestors = get_post_ancestors( $post );

							foreach ( $ancestors as $ancestor ) {
								if ( Muut_Post_Utility::isMuutChannelPage( $ancestor ) && Muut_Post_Utility::getChannelRemotePath( $ancestor, true ) ) {
									$path = Muut_Post_Utility::getChannelRemotePath( $ancestor, true ) . '/' . $path;
								}
							}
						$channel_path = $path;
						}
					} elseif ( isset( $tab_options['channel_path'] ) && $tab_options['channel_path'] != '' ) {
						$path = $tab_options['channel_path'];
						if ( substr( $path, 0, 1 ) == '/' ) {
							$path = substr( $path, 1 );
						}
						if ( substr( $path, -1 ) == '/' ) {
							$path = substr( $path, 0, -1 );
						}
						$path = implode('/', array_map('rawurlencode', explode( '/', $path ) ) );
						$channel_path = $path;
					}

					$tab_options['channel_path'] = $channel_path;

					Muut_Post_Utility::setPostOption( $post_id, $tab['meta_name'], $tab_options );
				break;

				case 'forum-tab':
					$tab_options = array();
					if ( isset( $_POST[$tab['meta_name']] ) ) {
						$tab_options = $_POST[$tab['meta_name']];
					}
					$boolean_values = array(
						'hide_online',
						'disable_uploads',
						'show_comments_in_forum',
					);

					foreach ( $boolean_values as $boolean_value ) {
						$tab_options[$boolean_value] = isset( $tab_options[$boolean_value] ) ? $tab_options[$boolean_value] : '0';
					}

					// Remove the default setting for showing comments in forum, if it was copied
					// over from an older version of the plugin.
					if ( muut()->getOption( 'show_comments_in_forum_default' ) ) {
						muut()->setOption( 'show_comments_in_forum_default', null );
					}

					Muut_Post_Utility::setPostOption( $post_id, $tab['meta_name'], $tab_options );
					Muut_Post_Utility::setAsForumPage( $post_id );
				break;
			}
		}

		/**
		 * Sets a meta option that enables Muut commenting for new posts, if "Use Muut for Commenting" is enabled.
		 *
		 * @param string $new_status The new post status.
		 * @param string $old_status The old post status.
		 * @param WP_Post $post The post that has just been saved.
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function maybeEnableSpecificComments( $new_status, $old_status, $post ) {
			if ( $new_status == 'publish' ) {
				if ( muut()->getOption( 'replace_comments' ) ) {
					update_post_meta( $post->ID, 'muut_use_muut_commenting', true );
				} else {
					update_post_meta( $post->ID, 'muut_use_muut_commenting', false );
				}
			}
		}

		/**
		 * Checks if a given tab should be enabled based on its enabled callback function.
		 *
		 * @param string $tab_slug The tab we are checking enabled status on.
		 * @return bool True if the tab should be enabled, false if disabled or failure.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function isTabEnabled( $tab_slug ) {
			$tabs = $this->getAllMetaBoxTabs();

			if ( isset( $tabs[$tab_slug]['enabled_callback'] ) ) {
				return call_user_func( $tabs[$tab_slug]['enabled_callback'] );
			} else {
				return false;
			}
		}

		/**
		 * Enabled callback for the commenting tab.
		 *
		 * @return bool True if it should be enabled, False if not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function isCommentingTabEnabled() {
			if ( !isset( $this->metaboxTabs['commenting']['enabled'] ) ) {
				global $post;
				$last_active_tab = get_post_meta( $post->ID, 'muut_last_active_tab', true );

				if ( $last_active_tab == $this->metaboxTabs['commenting']['name']
					|| Muut_Post_Utility::isMuutCommentingPost( $post->ID ) ) {
					$this->metaboxTabs['commenting']['enabled'] = true;
				} else {
					$this->metaboxTabs['commenting']['enabled'] = false;
				}
			}

			return $this->metaboxTabs['commenting']['enabled'];
		}

		/**
		 * Enabled callback for the channel tab.
		 *
		 * @return bool True if it should be enabled, False if not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function isChannelTabEnabled() {
			if ( !isset( $this->metaboxTabs['channel']['enabled'] ) ) {
				global $post;
				$last_active_tab = get_post_meta( $post->ID, 'muut_last_active_tab', true );

				if ( $last_active_tab != $this->metaboxTabs['channel']['name'] || ( muut()->getOption( 'replace_comments' ) && $post->comment_status == 'open' ) ) {
					$this->metaboxTabs['channel']['enabled'] = false;
				} else {
					$this->metaboxTabs['channel']['enabled'] = true;
				}
			}

			return $this->metaboxTabs['channel']['enabled'];
		}

		/**
		 * Enabled callback for the forum tab.
		 *
		 * @return bool True if it should be enabled, False if not.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function isForumTabEnabled() {
			if ( !isset( $this->metaboxTabs['forum']['enabled'] ) ) {
				global $post;
				$last_active_tab = get_post_meta( $post->ID, 'muut_last_active_tab', true );

				if ( $last_active_tab != $this->metaboxTabs['forum']['name'] || ( muut()->getOption( 'replace_comments' )  && $post->comment_status == 'open' ) ) {
					$this->metaboxTabs['forum']['enabled'] = false;
				} else {
					$this->metaboxTabs['forum']['enabled'] = true;
				}
			}

			return $this->metaboxTabs['forum']['enabled'];
		}

		/**
		 * Removes the commenting tab if replace commenting is not enabled.
		 *
		 * @param array $tabs The current array of tabs.
		 * @return array The modified array.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function removeCommentsTabIfDisabled( $tabs ) {
			if ( isset( $tabs['commenting'] ) && !muut()->getOption( 'replace_comments' ) ) {
				unset( $tabs['commenting'] );
			}

			return $tabs;
		}
	}
}