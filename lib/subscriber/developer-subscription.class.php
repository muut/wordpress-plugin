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
			add_action( 'wp_print_scripts', array( $this, 'printSsoJs' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueDeveloperScripts' ), 9 );
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

					$data = array(
						'user' => array(
							'id' => $user->user_login,
							'email' => $user->user_email,
							'avatar' => $avatar,
							'displayname' => $display_name,
							'is_admin' => is_super_admin()
						)
					);
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
				echo 'api: {';
				echo 'key: "' .  $api_key .'",';
				echo 'timestamp: "' . $this->timestamp . '",';
				echo 'signature: "' . $this->getSignature() . '",';
				echo 'message: "' . $this->getSigningMessage() . '"';
				echo '}';
				echo $additional_string;
				echo '};';
			}
			echo '</script>';
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
	}
}