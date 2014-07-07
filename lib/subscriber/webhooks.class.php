<?php
/**
 * The class that is responsible for all the webhooks functionality.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Webhooks' ) ) {

	/**
	 * Muut Webhooks class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   NEXT_RELEASE
	 */
	class Muut_Webhooks
	{
		const DEFAULT_ENDPOINT_SLUG = 'muut-webhooks';

		/**
		 * @static
		 * @property Muut_Webhooks The instance of the class.
		 */
		protected static $instance;

		/**
		 * @property string The raw request body.
		 */
		protected $raw_request;

		/**
		 * @private
		 * @property string The shared secret.
		 */
		private $secret;

		/**
		 * The singleton method.
		 *
		 * @return Muut_Webhooks The instance.
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
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
		 * @return Muut_Webhooks
		 * @author Paul Hughes
		 * @since  NEXT_RELEASE
		 */
		protected function __construct() {
			$this->secret = muut()->getOption( 'webhooks_secret' );
			if ( $this->isWebhooksActivated() ) {
				Muut_Initializer::instance()->initCustomPostTypes();
			}
			$this->addActions();
			$this->addFilters();
		}

		/**
		 * The method for adding all actions.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addActions() {
			add_action( 'admin_init', array( $this, 'addWebhooksEndpoint' ) );

			add_action( 'template_redirect', array( $this, 'receiveRequest' ) );

			// Webhook actions.
			add_action( 'muut_webhook_request_post', array( $this, 'processPost' ), 10, 2 );
			add_action( 'muut_webhook_request_reply', array( $this, 'processReply' ), 10, 2 );

		}

		/**
		 * The method for adding all filters.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addFilters() {
			add_filter( 'muut_validate_setting_use_webhooks', array( $this, 'executeSettingSave' ) );
		}

		/**
		 * Adds the webhooks endpoint for receiving Muut HTTP requests.
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function addWebhooksEndpoint() {
			add_rewrite_rule( '^' . $this->getEndpointSlug() . '/?', 'index.php?muut_action=webhooks', 'top' );
		}

		/**
		 * Begins request processing if the muut_action query var is passed
		 *
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function receiveRequest() {
			if ( get_query_var( 'muut_action' ) == 'webhooks' && $this->isWebhooksActivated() ) {

				$status_code = $this->validateRequest();

				//error_log( 'Request status: ' . $status_code );
				if ( $status_code != 200 ) {
					status_header( $status_code );
					exit;
				}

				// Display the payload.
				//error_log( $this->getRequestBody( true ) );
				// Display the X-Muut-Signature header value.
				//error_log( $_SERVER['HTTP_X_MUUT_SIGNATURE'] );

				$body = $this->getRequestBody();

				do_action( 'muut_webhook_request_' . $body['event'], $body, $body['event'] );
				do_action( 'muut_webhook_request', $body, $body['event'] );
			}
		}

		/**
		 * Returns the request body content formatted as an array.
		 *
		 * @return array The request body content.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function getRequestBody( $raw = false ) {
			$body = file_get_contents('php://input');

			$request = false;
			if ( !empty( $body ) ) {
				if ( !$raw ) {
					$request = $this->parseRequestStructure( $body );
				} else {
					$request = $this->raw_request = $body;
				}
			}
			return $request;
		}

		/**
		 * Parses the request body into the proper PHP array/object based on the Webhooks Events Structure
		 * (that can be found int $this->getWebhookEventsStructure()
		 *
		 * @param string $body The body content of the request.
		 * @return array The parsed array/object.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function parseRequestStructure( $body ) {
			$request = json_decode( $body );

			if ( $request == null ) return false;

			$structures = $this->getWebhookEventsStructure();

			$event = $request[0];
			$parsed = array();
			for ( $i = 0; $i < count( $request ); $i++ ) {
				$parsed[$structures[$event][$i]] = $request[$i];
			}

			//error_log( print_r( $parsed, true ) );
			return $parsed;
		}

		/**
		 * Gets and filters the endpoint slug.
		 *
		 * @return string The endpoint slug.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getEndpointSlug() {
			return apply_filters( 'muut_webhooks_endpoint_slug', self::DEFAULT_ENDPOINT_SLUG );
		}

		/**
		 * Checks if webhooks are activated.
		 *
		 * @return bool Whether webhooks are activated or not.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function isWebhooksActivated() {
			return muut()->getOption( 'use_webhooks' );
		}

		/**
		 * On saving, lets make sure to create (or check for) a webhooks shared secret for the user to enter on the
		 * Muut settings end.
		 *
		 * @param int $value Whether the use_webhooks setting is being saved as active or not.
		 * @return int $value The same value—we aren't messing with it.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function executeSettingSave( $value ) {
			if ( $value == 1 && !muut()->getOption( 'use_webhooks' ) ) {
				$this->secret = $this->generateSecret();
				add_filter( 'muut_settings_validated', array( $this, 'saveSecret' ) );
				$notice_message = sprintf( __( 'You can now use the following secret in your Muut webhook settings: %s', 'muut' ), '<b>' . $this->secret . '</b>' );
				muut()->queueAdminNotice( 'updated', $notice_message );
			}

			return $value;
		}

		/**
		 * Adds the current instance's shared secret to the settings being saved.
		 *
		 * @param array $settings The current settings array.
		 * @return array The filtered settings array.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function saveSecret( $settings ) {
			$settings['webhooks_secret'] = $this->secret;

			return $settings;
		}

		/**
		 * Generate a (random) secret for shared secret use with Muut.
		 *
		 * @return string The secret.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function generateSecret() {
			$validCharacters = "abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ+-*#&@!?";
			$validCharNumber = strlen($validCharacters);

			$result = "";
			for ( $i = 0; $i < 20; $i++ ) {
				$index = mt_rand( 0, $validCharNumber - 1 );
				$result .= $validCharacters[$index];
			}

			return $result;
		}

		/**
		 * Validate HTTP request from Muut.
		 *
		 * @return int Whether the request is valid or not, the HTTP status code.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		protected function validateRequest() {
			if ( !isset( $_SERVER['HTTP_X_MUUT_SIGNATURE'] ) ) {
				return 412;
			}
			$request = $this->getRequestBody( true );

			$signature = hash_hmac( 'sha1', $request, $this->secret );

			if ( $signature != $_SERVER['HTTP_X_MUUT_SIGNATURE'] ) {
				return 403;
			}

			return 200;
		}

		/**
		 * Gets the expected webhook events and the returned array structure for each.
		 *
		 * @return array An array of webhook events.
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function getWebhookEventsStructure() {
			return apply_filters( 'muut_webhook_events_structure', array(
				'enter' => array(
					'event',
					'user',
					'arg1',
				),
				'leave' => array(
					'event',
					'user',
					'arg1',
				),
				'like' => array(
					'event',
					'path',
					'user',
				),
				'unlike' => array(
					'event',
					'path',
					'user',
				),
				'remove' => array(
					'event',
					'path',
					'user',
				),
				'spam' => array(
					'event',
					'path',
					'user',
				),
				'unspam' => array(
					'event',
					'path',
					'user',
				),
				'reply' => array(
					'event',
					'path',
					'post',
				),
				'post' => array(
					'event',
					'location',
					'thread',
				),
			) );
		}


		/**********
		 * Webhooks Processing Methods
		 **********/

		/**
		 * Process the 'post' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function processPost( $request, $event ) {
			$new_thread_args = array(
				'title' => $request['path'],
				'path' => $request['location']->path,
				'user' => $request['thread']->user->path,
				'body' => '',
			);

			$custom_posts_object = Muut_Custom_Post_Types::instance();

			$custom_posts_object->addMuutThreadData( $new_thread_args );
		}

		/**
		 * Process the 'post' Muut event.
		 *
		 * @param $request
		 * @param $event
		 * @return void
		 * @author Paul Hughes
		 * @since NEXT_RELEASE
		 */
		public function processReply( $request, $event ) {
			$new_reply_args = array(
				'title' => $request['post']->key,
				'path' => $request['path'],
				'user' => $request['thread']->user->path,
				'body' => $request['post']->body,
			);

			$custom_posts_object = Muut_Custom_Post_Types::instance();

			$custom_posts_object->addMuutReplyData( $new_reply_args );
		}
	}
}
