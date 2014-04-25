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
		 * @property string The SSO message.
		 */
		private $sso_message;

		/**
		 * @property string The SSO signature.
		 */
		private $sso_signature;

		/**
		 * @property int The timestamp for SSO.
		 */
		private $sso_timestamp;

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
			$this->sso_timestamp = time();
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
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueDeveloperScripts' ) );
		}

		/**
		 * The method for adding all filters regarding the custom navigation admin.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function addFilters() {
			add_filter( 'muut_wrapper_css_id', array( $this, 'filterWrapperId' ) );
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
			if ( Muut_Forum_Page_Utility::isForumPage( get_the_ID() )
				|| ( muut()->getOption( 'replace_comments' ) && is_singular() && comments_open() ) ) {
				wp_enqueue_script( 'muut' );
				wp_enqueue_script( 'muut-sso' );
			}
		}

		/**
		 * Function for generating the message for a given SSO request for the current user.
		 *
		 * @return string|false The message encoded, or false on failure (not logged in and whatnot).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function getSsoMessage() {
			if ( isset( $this->sso_message ) ) {
				return $this->sso_message;
			}

			if ( !is_user_logged_in()
				|| !muut()->getOption( 'subscription_api_key', '' )
				|| !muut()->getOption( 'subscription_use_sso', '0' )
			) {
				return false;
			}

			$user = wp_get_current_user();
			$key = muut()->getOption( 'subscription_api_key' );
			$display_name = $user->display_name;
			$avatar = get_user_meta($user->ID, 'profilepicture', true);
			if (!$avatar) {
				$avatar = function_exists( 'bp_core_fetch_avatar' ) ? bp_core_fetch_avatar( array( 'item_id' => $user->ID, 'html' => false ) ) : "//gravatar.com/avatar/" . md5( $user->user_email );
			}

			$sso = array(
				"user" => array(
					"id" => $user->user_login,
					"email" => $user->user_email,
					"avatar" => $avatar,
					"displayname" => $display_name,
					"is_admin" => is_super_admin()
				)
			);

			$this->sso_message = base64_encode( json_encode( $sso ) );
			return base64_encode(json_encode($sso));
		}

		/**
		 * Get the signature for a given SSO request.
		 *
		 * @return string|false The signature for the request, or false on failure (not logged in and whatnot).
		 * @author Paul Hughes
		 * @since 3.0
		 */
		private function getSsoSignature() {
			if ( !is_user_logged_in()
				|| !muut()->getOption( 'subscription_secret_key', '' )
				|| !muut()->getOption( 'subscription_use_sso', '0' )
			) {
				return false;
			}

			return sha1( muut()->getOption( 'subscription_secret_key', '' ) . ' ' . $this->getSsoMessage() . ' ' . $this->sso_timestamp );
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
			if ( is_user_logged_in()
				&& muut()->getOption( 'subscription_api_key', '' )
				&& muut()->getOption( 'subscription_use_sso', '0' )
			) {
				$key = muut()->getOption( 'subscription_api_key', '' );

				echo 'var muut_conf = {';
				echo 'login_url: "' . wp_login_url( get_permalink() ) . '",';
				echo 'sso: {';
				echo 'key: "' .  $key .'",';
				echo 'timestamp: "' . $this->sso_timestamp . '",';
				echo 'signature: "' . $this->getSsoSignature() . '",';
				echo 'message: "' . $this->getSsoMessage() . '"';
				echo '}';
				echo '}';
			} else {
				echo 'var muut_conf = {};';
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
			if ( muut()->getOption( 'subscription_use_sso' ) ) {
				$class = '';
			}
			return $class;
		}

		/**
		 * Filters the container element id value to work properly for SSO.
		 *
		 * @param string $id_string The current element id string.
		 * @return string The modified attribute string.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public function filterWrapperId( $id_string ) {
			if ( muut()->getOption( 'subscription_use_sso' ) ) {
				$id_string = 'muut_sso';
			}
			return $id_string;
		}
	}
}