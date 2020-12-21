<?php

namespace C_Trax_Integration\Includes;

/**
 * Session class that allows for the storage and getting of session variables
 * @author Keith Andrews
 */
class Session {

	/**
	 * Start the session if no session has already been started
	 */
	public static function start() {
		$lifetime = '10800';
		if ( ( ! session_id() && ! headers_sent() ) || ( ! isset( $_SESSION ) && ! headers_sent() ) ) {
			session_set_cookie_params( $lifetime );
			//session_start(['name' => C_TRAX_INTEGRATION_PREFIX]);
			header( 'Cache-Control: no-cache, no-store, must-revalidate' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
		} else
			self::refresh();
	}

	/**
	 * Refresh the session so that there is no timeout for active user
	 */
	public static function refresh() {
		if ( ! headers_sent() )
			session_regenerate_id(); // Refresh session on each page load
	}

	/**
	 * Stop the session and destroy it
	 */
	public static function end() {
		$_SESSION = [];

		if ( ! headers_sent() ) {
			if ( ini_get( "session.use_cookies" ) ) {
				$params = session_get_cookie_params();
				setcookie( session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
				);
			}

			// Burn it down
			session_destroy();
		}
	}

	/**
	 * Remove a variable from the session
	 *
	 * @param  mixed  $var
	 */
	public static function flush( $var ) {
		self::start();
		if ( self::var_isset( $var ) )
			unset( $_SESSION[ C_TRAX_INTEGRATION_SESSION_PREFIX . $var ] );
	}

	/**
	 * Set a session variable
	 *
	 * @param  mixed  $var
	 * @param  mixed  $value
	 */
	public static function set_var( $var, $value ) {
		self::start();
		$_SESSION[ C_TRAX_INTEGRATION_SESSION_PREFIX . $var ] = $value;
	}

	/**
	 * Get a session variable
	 * Returns false if no variable is not set
	 *
	 * @param  mixed  $var
	 *
	 * @return mixed|bool
	 */
	public static function get_var( $var ) {
		self::start();

		return ( self::var_isset( $var ) ) ? $_SESSION[ C_TRAX_INTEGRATION_SESSION_PREFIX . $var ] : false;
	}

	/**
	 * Check to see if the passed variable is set in the session
	 *
	 * @param  mixed  $var
	 *
	 * @return bool
	 */
	public static function var_isset( $var ) {
		self::start();

		return isset( $_SESSION[ C_TRAX_INTEGRATION_SESSION_PREFIX . $var ] );
	}
}