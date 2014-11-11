<?php
/**
 * The class that is responsible for all functionality regarding the Muut settings.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Admin_Settings' ) ) {

	/**
	 * Muut Admin Settings class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0.1
	 */
	class Muut_Admin_Settings
	{
		/**
		 * @static
		 * @property Muut_Admin_Settings The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property array An array of errors that will get queued for the admin notices.
		 */
		protected $errorQueue = array();

		/**
		 * @property array The current submitted settings and values (the $_POST['setting']) variable, generally).
		 */
		protected $submittedSettings = array();

		/**
		 * The singleton method.
		 *
		 * @return Muut_Admin_Settings The instance.
		 * @author Paul Hughes
		 * @since  3.0.1
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
		 * @return Muut_Admin_Settings
		 * @author Paul Hughes
		 * @since  3.0.1
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions regarding the admin settings functionality.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function addActions() {
			add_action( 'load-toplevel_page_' . Muut::SLUG, array( $this, 'saveSettings' ) );
			add_action( 'load-toplevel_page_' . Muut::SLUG, array( $this, 'maybeShowS3RemoveNotice' ), 9 );
			add_action( 'admin_notices', array( $this, 'prepareAdminNotices' ), 9 );
			add_action( 'admin_notices', array( $this, 'maybeShowReviewRequestNotice' ), 8 );
			add_action( 'admin_print_scripts', array( $this, 'printJsFieldNames') );
			add_action( 'wp_ajax_dismiss_review_request_notice', array( $this, 'ajaxDismissReviewRequestNotice' ) );
		}

		/**
		 * The method for adding all filters regarding the admin settings.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function addFilters() {
			add_filter( 'muut_validate_setting', array( $this, 'validateSettings' ), 10, 2 );
		}

		/**
		 * Saves the settings specified on the Muut settings page.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function saveSettings() {
			if ( isset( $_POST['muut_settings_save'] )
				&& $_POST['muut_settings_save'] == 'true'
				&& check_admin_referer( 'muut_settings_save', 'muut_settings_nonce' )
			) {
				$this->submittedSettings = $_POST['setting'];

				$settings = $this->settingsValidate( $this->getSubmittedSettings() );

				// Save all the options by passing an array into setOption.
				if ( muut()->setOption( $settings ) ) {
					if ( !empty( $this->errorQueue ) ) {
						// Display partial success notice if they were updated or matched the previous settings.
						muut()->queueAdminNotice( 'updated', __( 'Settings successfully saved, other than the errors below:', 'muut' ) );
					} else {
						// Display success notice if they were updated or matched the previous settings.
						muut()->queueAdminNotice( 'updated', __( 'Settings successfully saved.', 'muut' ) );
					}
				} else {
					// Display error if the settings failed to save.
					muut()->queueAdminNotice( 'error', __( 'Failed to save settings.', 'muut' ) );
				}
			}
		}

		/**
		 * Gets the submitted settings.
		 *
		 * @return array The submitted settings.
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function getSubmittedSettings() {
			return $this->submittedSettings;
		}

		/**
		 * Gets the error queue.
		 *
		 * @return array The error queue
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function getErrorQueue() {
			return apply_filters( 'muut_get_error_queue', $this->errorQueue );
		}

		/**
		 * Deals with settings-specific validation functionality.
		 *
		 * @param array $settings an array of key => value pairs that define what settings are being changed to.
		 * @return array A modified array defining the settings after validation.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		protected function settingsValidate( $settings ) {

			if ( isset( $_POST['initial_save'] ) ) {
				return apply_filters( 'muut_settings_initial_save', apply_filters( 'muut_settings_validated', $settings ) );
			}

			$boolean_settings = apply_filters( 'muut_boolean_settings', array(
				'replace_comments',
				'use_threaded_commenting',
				'override_all_comments',
				'is_threaded_default',
				'show_online_default',
				'allow_uploads_default',
				'subscription_use_signed_setup',
				'use_custom_s3_bucket',
				'subscription_use_sso',
				'enable_proxy_rewrites',
				'use_webhooks',
			) );

			foreach ( $boolean_settings as $boolean_setting ) {
				$settings[$boolean_setting] = isset( $settings[$boolean_setting]) ? $settings[$boolean_setting] : '0';
			}

			if ( ( isset( $settings['forum_name'] ) && $settings['forum_name'] != muut()->getForumName() )
				|| ( isset( $settings['enable_proxy_rewrites'] ) && $settings['enable_proxy_rewrites'] != muut()->getOption( 'enable_proxy_rewrites' ) )
				|| ( isset( $settings['use_custom_s3_bucket'] ) && $settings['use_custom_s3_bucket'] != muut()->getOption( 'use_custom_s3_bucket' ) )
				|| ( isset( $settings['custom_s3_bucket_name'] ) && $settings['custom_s3_bucket_name'] != muut()->getOption( 'custom_s3_bucket_name' ) )
				|| ( isset( $settings['use_webhooks'] ) && $settings['use_webhooks'] != muut()->getOption( 'use_webhooks' ) )
			) {
				flush_rewrite_rules( true );
			}

			// If the Secret Key setting does not get submitted (i.e. is disabled), make sure to erase its value.
			$settings['subscription_secret_key'] = isset( $settings['subscription_secret_key']) ? $settings['subscription_secret_key'] : '';

			foreach ( $settings as $name => &$value ) {
				$value = apply_filters( 'muut_validate_setting_' . $name, $value );
				$value = apply_filters( 'muut_validate_setting', $value, $name );
			}

			return apply_filters( 'muut_settings_validated', $settings );
		}

		/**
		 * Adds an error to the error queue to be displayed as admin notices.
		 * The error array passed to the method should take the form:
		 * 'name' => (slug of error)
		 * 'code' => (the error code) // This is not being used
		 * 'message' => The text to be output
		 * 'field' => The ID of the element that should be highlighted
		 * 'new_value' => The submitted value
		 * 'old_value' => The invalid value
		 *
		 * @param array|string $error The error array. It should take the form of the above outline.
		 *                            Can also be a string, in which case it is used as just the
		 *                            message field (to generate a simple error string).
		 * @return bool Whether the queuing was successful.
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function addErrorToQueue( $error ) {
			if ( !is_array( $error) && !is_string( $error ) ) {
				return false;
			}

			if ( is_string( $error ) ) {
				$error = array(
					'message' => $error,
					'name' => sanitize_title( $error ),
				);
			}

			$default_values = array(
				'name' => 'random',
				'code' => '1',
				'message' => '',
				'field' => '',
				'new_value' => '',
				'old_value' => '',
			);

			$error = wp_parse_args( $error, $default_values );

			if ( is_string( $error['message'] ) && !empty( $error['message'] ) ) {
				$this->errorQueue[$error['name']] = $error;
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Validates the settings (the default validation).
		 *
		 * @param mixed $value The value of the submitted setting.
		 * @param string $name The name (and key) of the submitted setting.
		 * @return mixed The filtered value.
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function validateSettings( $value, $name ) {
			switch( $name ) {
				//This first case is deprecated and should no longer be used. Delete after next release.
				case 'custom_s3_bucket_name':
					$value = trim( $value );
					$submitted_settings = $this->getSubmittedSettings();
					if ( isset( $submitted_settings['use_custom_s3_bucket'] ) && isset( $submitted_settings['enable_proxy_rewrites'] ) && $submitted_settings['use_custom_s3_bucket'] && $submitted_settings['enable_proxy_rewrites'] ) {
						if ( isset( $submitted_settings['forum_name'] ) ) {
							$forum_name = $submitted_settings['forum_name'];
						} elseif ( muut()->getForumName() != '' ) {
							$forum_name = muut()->getForumName();
						} else {
							$forum_name = '';
						}
						$url = $value . '/' . $forum_name;
						$valid = Muut_Field_Validation::validateExternalUri( $url );
						if ( !$valid ) {
							$error_args = array(
								'name' => $name,
								'message' => __( 'Custom S3 Bucket name must be associated with a valid server. Make sure it matches the bucket you set up with Muut from your forum page settings.'),
								'field' => 'muut_custom_s3_bucket_name',
								'new_value' => $value,
								'old_value' => muut()->getOption( $name ),
							);
							$this->addErrorToQueue( $error_args );

							// Unset the value, so nothing *breaks* (defaults to Muut.com indexing).
							$value = '';
						} else {
							$value = sanitize_text_field( $value );
						}
					}
					break;

				case 'forum_name':
					$value = trim( $value );
					// Make sure the forum name has no whitespace.
					$valid = Muut_Field_Validation::validateHasNoWhitespace( $value );

					if ( !$valid ) {
						$error_args = array(
							'name' => $name,
							'message' => __( 'Forum name must contain no spaces of any kind. Make sure the forum name is the name you registered with Muut when you set up the forum.', 'muut' ),
							'field' => 'muut_forum_name',
							'new_value' => $value,
							'old_value' => muut()->getForumName(),
						);
						$this->addErrorToQueue( $error_args );

						$value = muut()->getForumName();
					} else {
						$value = sanitize_text_field( $value );
					}
					break;
				case 'subscription_api_key':
					$validations = array(
						'validateHasNoWhitespace',
						'validateAlphaNumeric',
					);
					$value = trim( $value );
					$valid = true;
					foreach ( $validations as $validation ) {
						if ( method_exists( 'Muut_Field_Validation', $validation ) && $valid ) {
							$valid = Muut_Field_Validation::$validation( $value );
						}
					}

					if ( !$valid ) {
						$error_args = array(
							'name' => $name,
							'message' => __( 'API Key must have no spaces and only alphanumeric characters.', 'muut' ),
							'field' => 'muut_subscription_api_key',
							'new_value' => $value,
							'old_value' => muut()->getOption( $name ),
						);
						$this->addErrorToQueue( $error_args );

						$value = muut()->getOption( 'subscription_api_key', '' );
					} else {
						$value = sanitize_text_field( $value );
					}

					break;
				case 'subscription_secret_key':
					$validations = array(
						'validateHasNoWhitespace',
						'validateAlphaNumeric',
					);
					$value = trim( $value );
					$valid = true;
					foreach ( $validations as $validation ) {
						if ( method_exists( 'Muut_Field_Validation', $validation ) && $valid ) {
							$valid = Muut_Field_Validation::$validation( $value );
						}
					}

					if ( !$valid ) {
						$error_args = array(
							'name' => $name,
							'message' => __( 'Secret Key must have no spaces and only alphanumeric characters.', 'muut' ),
							'field' => 'muut_subscription_secret_key',
							'new_value' => $value,
							'old_value' => muut()->getOption( $name ),
						);
						$this->addErrorToQueue( $error_args );

						$value = muut()->getOption( 'subscription_secret_key', '' );
					} else {
						$value = sanitize_text_field( $value );
					}

					break;
			}

			return $value;
		}

		/**
		 * Prints the field names that need to be assigned the "muut_field_error" class.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function printJsFieldNames() {
			if ( count( $this->errorQueue ) > 0 ) {
				$error_fields = array();
				foreach ( $this->errorQueue as $error ) {
					$error_fields[] = $error['field'];
				}

				$error_field_list = '"' . join( '","', $error_fields ) . '"';
				echo '<script type="text/javascript">';
				echo 'var muut_error_fields = [' . $error_field_list . '];';
				echo '</script>';
			}
		}

		/**
		 * Displays the S3-bucket setting removal notice on first load of admin settings page after removal.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.2
		 */
		/* COMMENTED OUT IN 3.0.2.3 when s3 was re-added
		 * public function maybeShowS3RemoveNotice() {
			if ( muut()->getOption( 'removed_s3_support' ) ) {
				muut()->queueAdminNotice( 'updated', __("S3 bucket preferences have been removed in this version in the interests of simpler and better SEO; don't worry, it's a good thing!", 'muut' ) );
				muut()->deleteOption( 'removed_s3_support' );
			}
		}*/

		/**
		 * Queues the admin notices generated here to the main Muut admin notices renderer.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public function prepareAdminNotices() {
			$error_queue = array_reverse( $this->errorQueue );
			foreach( $error_queue as $name => $error ) {
				muut()->queueAdminNotice( 'error', $error['message'] );
			}
		}

		/**
		 * Displays the dismissible admin notice requesting a review.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function maybeShowReviewRequestNotice() {
			if ( !muut()->getOption( 'dismissed_review_request', false ) ) {
				$update_timestamps = muut()->getOption( 'update_timestamps', array() );
				$update_time = !empty( $update_timestamps ) ? array_pop( $update_timestamps ) : false;

				if ( $update_time && ( time() - $update_time > 604800 ) ) {
					echo '<div class="updated" id="muut_dismiss_review_request_notice">';
					wp_nonce_field( 'muut_dismiss_review_request', 'dismiss_review_request_nonce' );
					echo '<p>' . sprintf( __( 'Enjoying Muut? We\'d love it you would pop over to the %splugin page%s and leave a rating and review!', 'muut' ), '<a class="dismiss_review_request" target="_blank" href="https://wordpress.org/plugins/muut/">', '</a>' ) . '<span style="float: right"><a href="#" class="dismiss_review_request">X</a></span></p>';
					echo '</div>';
				}
			}
		}

		/**
		 * Dismisses the review request notice.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function ajaxDismissReviewRequestNotice() {
			check_ajax_referer( 'muut_dismiss_review_request', 'security' );
			if ( isset( $_POST['dismiss'] ) && $_POST['dismiss'] ) {
				muut()->setOption( 'dismissed_review_request', true );
			}
			die();
		}
	}
}