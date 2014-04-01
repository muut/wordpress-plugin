<?php
/**
 * The template tags packaged with the Muut plugin.
 *
 * @package  Muut
 * @copyright 2014 Moot Inc
 */

/**
 * Gets an option for the Muut plugin.
 *
 * @param string $option The option name.
 * @param mixed $default The default value to return.
 * @return mixed The option value.
 * @author Paul Hughes
 * @since 3.0
 */
function muut_get_option( $option, $default = '' ) {
	return muut()->getOption( $option, $default );
}