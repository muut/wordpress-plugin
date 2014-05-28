<?php
/**
 * The Field Validation utility class, which contains all the static methods that can be used to
 * validate a given field.
 *
 * @package   Muut
 * @copyright 2014 Muut Inc
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Muut_Field_Validation' ) ) {

	/**
	 * Muut Field Validation Utility class.
	 *
	 * @package Muut
	 * @author  Paul Hughes
	 * @since   3.0.1
	 */
	class Muut_Field_Validation
	{

		/**
		 * Make it impossible to instantiate the class by declaring __construct() as private.
		 *
		 * @return Muut_Field_Validation (Except it can never be called).
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		private function __construct() {}

		/**
		 * Function to validate an external URI to make sure it returns a valid response code.
		 *
		 * @param string $string $value The value that we are validating.
		 * @param array $args The extra arguments that can be passed into the function.
		 *                    'response_codes' => (array) The valid response codes (default 200)
		 *                    'timeout' => (float) The number of seconds before timeout (default 5)
		 * @return bool Whether the field is valid or not.
		 * @author Paul Hughes
		 * @since 3.0.1
		 */
		public static function validateExternalUri( $value, $args = array() ) {
			if ( !is_string( $value ) ) {
				return false;
			}

			$default_arguments = array(
				'response_codes' => array( '200' ),
				'timeout' => 5,
			);

			$args = wp_parse_args( $args, $default_arguments );

			// Make sure the URI begins with http:// or https://. If not, add it.
			if ( substr( $value, 0, 7 ) != 'http://' || substr( $value, 0, 8 ) != 'https://' ) {
				$value = 'http://' . $value;
			}

			$request_args = apply_filters( 'muut_validate_external_uri_request_args', array(
				'timeout' => $args['timeout'],
			) );

			// Make the request.
			$response = wp_remote_get( $value, $request_args );

			$response_code = wp_remote_retrieve_response_code( $response );

			// Make sure the response code is valid and that it is one of the validated codes.
			if ( !empty( $response_code ) && in_array( $response_code, $args['response_codes'] ) ) {
				return true;
			} else {
				return false;
			}
		}
	}
}