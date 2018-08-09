<?php

/**
 * wordpress environment functions
 *
 * @generated by function-mocker environment generation tool on 2018-08-07 08:29:38 (UTC)
 * @link      https://github.com/lucatume/function-mocker
 */

if (!function_exists('__return_true')) {
	/**
	 * Returns true.
	 *
	 * Useful for returning true to filters easily.
	 *
	 * @since 3.0.0
	 *
	 * @see __return_false()
	 *
	 * @return true True.
	 */
	function __return_true() {
		return true;
	}
}

if (!function_exists('__return_false')) {
	/**
	 * Returns false.
	 *
	 * Useful for returning false to filters easily.
	 *
	 * @since 3.0.0
	 *
	 * @see __return_true()
	 *
	 * @return false False.
	 */
	function __return_false() {
		return false;
	}
}

if (!function_exists('__return_zero')) {
	/**
	 * Returns 0.
	 *
	 * Useful for returning 0 to filters easily.
	 *
	 * @since 3.0.0
	 *
	 * @return int 0.
	 */
	function __return_zero() {
		return 0;
	}
}

if (!function_exists('__return_empty_array')) {
	/**
	 * Returns an empty array.
	 *
	 * Useful for returning an empty array to filters easily.
	 *
	 * @since 3.0.0
	 *
	 * @return array Empty array.
	 */
	function __return_empty_array() {
		return array();
	}
}

if (!function_exists('__return_null')) {
	/**
	 * Returns null.
	 *
	 * Useful for returning null to filters easily.
	 *
	 * @since 3.4.0
	 *
	 * @return null Null value.
	 */
	function __return_null() {
		return null;
	}
}

if (!function_exists('__return_empty_string')) {
	/**
	 * Returns an empty string.
	 *
	 * Useful for returning an empty string to filters easily.
	 *
	 * @since 3.7.0
	 *
	 * @see __return_null()
	 *
	 * @return string Empty string.
	 */
	function __return_empty_string() {
		return '';
	}
}

if (!function_exists('trailingslashit')) {
	/**
	 * Appends a trailing slash.
	 *
	 * Will remove trailing forward and backslashes if it exists already before adding
	 * a trailing forward slash. This prevents double slashing a string or path.
	 *
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $string What to add the trailing slash to.
	 * @return string String with trailing slash added.
	 */
	function trailingslashit($string) {
		return untrailingslashit($string) . '/';
	}
}

if (!function_exists('untrailingslashit')) {
	/**
	 * Removes trailing forward slashes and backslashes if they exist.
	 *
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 *
	 * @since 2.2.0
	 *
	 * @param  string $string What to remove the trailing slashes from.
	 * @return string String without the trailing slashes.
	 */
	function untrailingslashit($string) {
		return rtrim($string, '/\\');
	}
}
