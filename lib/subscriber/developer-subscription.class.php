<?php
/**
 * The class that is responsible for all the developer subscription functionality..
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Developer_Subscription' ) ) {

	/**
	 * Muut Developer Subscription class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0
	 */
	class Muut_Developer_Subscription
	{
		/**
		 * @static
		 * @property Muut_Developer_Subscription The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property string The Signed message.
		 */
		private $signed_message;

		/**
		 * @property int The timestamp for SSO.
		 */
		private $timestamp;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Developer_Subscription The instance.
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
		 * @return Muut_Developer_Subscription
		 * @author Paul Hughes
		 * @since  3.0
		 */
		protected function __construct() {
			$this->addActions();
			$this->addFilters();
			$this->timestamp = time();
		}

		/**
		 * The method for adding all actions regarding the custom navigation admin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addActions() {
			add_action( 'wp_print_scripts', array( $this, 'printSsoJs' ), 11 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueDeveloperScripts' ), 9 );
			add_action( 'wp_ajax_muut_get_signed', array( $this, 'ajaxGetSignedMessage' ) );
			add_action( 'wp_ajax_nopriv_muut_get_signed', array( $this, 'ajaxGetSignedMessage' ) );
		}

		/**
		 * The method for adding all filters regarding the custom navigation admin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addFilters() {
			add_filter( 'muut_wrapper_css_class', array( $this, 'filterWrapperClass' ) );
		}

		/**
		 * Enqueue the JS script that handles developer functions (like SSO).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function enqueueDeveloperScripts() {
			if ( muut()->needsMuutResources() ) {
				wp_enqueue_script( 'muut-sso', muut()->getPluginUrl() . 'resources/muut-sso.js', array( 'jquery', 'muut' ), Muut::VERSION, true );
			}
		}

		/**
		 * Function for generating the message for a given SSO request for the current user.
		 *
		 * @return string|false The message encoded, or false on failure (not logged in and whatnot).
		 * @author Paul Hughes
		 * @since 3.0
		*/
		private function getSigningMessage() {
			if ( isset( $this->signed_message ) ) {
				return $this->signed_message;
			}

			if ( !muut()->getOption( 'subscription_api_key', '' )
				|| !muut()->getOption( 'subscription_use_signed_setup', '0' )
			) {
				return false;
			}

			if ( muut()->getOption( 'subscription_use_sso', '0' ) ) {
				if ( !is_user_logged_in() ) {
					$data = array( 'user' => array() );
				} else {

					$user = wp_get_current_user();
					$key = muut()->getOption( 'subscription_api_key' );
					$display_name = $user->display_name;
					$avatar = get_user_meta( $user->ID, 'profilepicture', true );
					if ( !$avatar ) {
						$avatar = function_exists( 'bp_core_fetch_avatar' ) ? bp_core_fetch_avatar( array( 'item_id' => $user->ID, 'html' => false ) ) : "//gravatar.com/avatar/" . md5( $user->user_email );
					}
					$avatar = apply_filters( 'muut_fedid_user_avatar_url', $avatar, $user->ID, $user );

					$data = array(
						'user' => array(
							'id' => apply_filters( 'muut_fedid_user_id', $user->user_login, $user->ID, $user ),
							'email' => apply_filters( 'muut_fedid_user_email', $user->user_email, $user->ID, $user ),
							'avatar' => $avatar,
							'displayname' => apply_filters( 'muut_fedid_user_email', $display_name, $user->ID, $user ),
							'is_admin' => apply_filters( 'muut_fedid_user_is_admin', is_super_admin(), $user->ID, $user ),
						)
					);

					$data = apply_filters( 'muut_fedid_user_data', $data, $user->ID, $user );
				}
			} else {
				$data = array();
			}

			$this->signed_message = base64_encode( json_encode( $data ) );
			return $this->signed_message;
		}

		/**
		 * Get the signature for a given SSO request.
		 *
		 * @return string|false The signature for the request, or false on failure (not logged in and whatnot).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function getSignature() {
			if ( !muut()->getOption( 'subscription_secret_key', '' )
				|| !muut()->getOption( 'subscription_use_signed_setup', '0' )
			) {
				return false;
			}

			return sha1( muut()->getOption( 'subscription_secret_key', '' ) . ' ' . $this->getSigningMessage() . ' ' . $this->timestamp );
		}

		/**
		 * Prints the JS necessary for SSO requests.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function printSsoJs() {
			echo '<script type="text/javascript">';
			if ( muut()->getOption( 'subscription_api_key', '' )
				&& muut()->getOption( 'subscription_use_signed_setup', '0' )
			) {
				$api_key = muut()->getOption( 'subscription_api_key', '' );
				$additional = array();
				if ( muut()->getOption( 'subscription_use_sso' ) ) {
					$additional['login_url'] = apply_filters( 'muut_sso_login_url', wp_login_url( get_permalink() ) );
				}
				$additional = apply_filters( 'muut_addional_signed_conf_parameters', $additional );
				$additional_string = '';
				foreach( $additional as $key => $value ) {
					$additional_string .= ', ' . $key . ': "' . $value . '"';
				}

				echo 'var muut_conf = {';
				if ( !muut()->getOption( 'website_uses_caching', '0' ) ) {
					echo 'api:';
					echo json_encode( $this->getSignedObjectArray() );
					echo $additional_string;
				}
				echo '};';
			}
			echo '</script>';
		}

		/**
		 * Gets the full Javascript object (as string) for the api property of the muut conf.
		 *
		 * @return string The JS object as a string.
		 * @author Paul Hughes
		 * @since 3.0.5
		 */
		public function getSignedObjectArray() {
			$api_key = muut()->getOption( 'subscription_api_key', '' );
			$signed = array(
				'key' => $api_key,
				'timestamp' => $this->timestamp,
				'signature' => $this->getSignature(),
				'message' => $this->getSigningMessage(),
			);

			return $signed;
		}

		/**
		 * Filters the container css class to work properly for SSO.
		 *
		 * @param string $class The current container attribution string.
		 * @return string The modified attribute string.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function filterWrapperClass( $class ) {
			if ( muut()->getOption( 'subscription_use_signed_setup', '0' ) ) {
				$class = 'muut_sso';
			}
			return $class;
		}

		/**
		 * Gets the signed data over AJAX (for sites with caching plugins).
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0.5
		 */
		public function ajaxGetSignedMessage() {
			check_ajax_referer( 'muut_get_signed', 'security' );


			$result = array(
				'success' => true,
				'data' => array(
					'api' => $this->getSignedObjectArray(),
				),
			);

			$additional = array();
			if ( muut()->getOption( 'subscription_use_sso' ) ) {
				$additional['login_url'] = apply_filters( 'muut_sso_login_url', wp_login_url( get_permalink() ) );
			}
			$additional = apply_filters( 'muut_addional_signed_conf_parameters', $additional );
			foreach( $additional as $key => $value ) {
				$result['data'][$key] = $value;
			}

			exit( json_encode( $result ) );

		}
	}
}