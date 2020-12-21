<?php

namespace C_Trax_Integration\Includes;

/**
 * Input class which controls the data that is passed via the $_REQUEST, $_POST, and $_GET variables.
 * All data sanitizing is handled through this class, so no need to add any in other classes.
 * @author Keith Andrews
 */
class Input {

	public function __construct() {

	}

	/**
	 * Get the URL parameter or return the a default value if not set
	 *
	 * @param  mixed  $key
	 * @param  mixed  $default
	 * @param  bool   $raw
	 *
	 * @return mixed
	 */
	public static function get( $key, $default = null, $raw = false ) {
		if ( self::has( $key ) ) {
			if ( ! is_array( $_REQUEST[ $key ] ) && ! $raw ) {
				$data = \sanitize_text_field( $_REQUEST[ $key ] );
			} else {
				$data = $_REQUEST[ $key ];
			}
		} else {
			$data = $default;
		}

		return $data;
	}

	/**
	 * Get all of the URL parameters
	 *
	 * @param  array  $except
	 * @param  bool   $raw
	 *
	 * @return array
	 */
	public static function all( $except = [], $raw = false ) {
		$data = [];
		if ( count( $_REQUEST ) > 0 ) {
			$except = array_merge( [ 'page', '_wpnonce', '_wp_http_referer', C_TRAX_INTEGRATION_PROCESS_DISPATCH, '_model' ], $except );
			foreach ( $_REQUEST as $key => $value ) {
				if ( ! in_array( $key, $except ) ) {
					if ( ! is_array( $value ) && ! $raw ) {
						$data[ $key ] = \sanitize_text_field( $value );
					} else {
						$data[ $key ] = $value;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Check to see if the URL parameter is set
	 *
	 * @param  mixed  $key
	 *
	 * @return bool
	 */
	public static function has( $key ) {
		return isset( $_REQUEST[ $key ] );
	}

	/**
	 * Modify the password for database storage using salts and hashing
	 *
	 * @param  mixed  $pw
	 *
	 * @return string
	 */
	public static function password_encrypt( $pw ) {
		global $wp_hasher;
		require_once( ABSPATH . 'wp-includes/class-phpass.php' );

		$wp_hasher = new \PasswordHash( 16, false );
		$hashed    = \wp_hash_password( $pw );

		return $hashed;
	}

	/**
	 * Verify the password matches the hashed password
	 *
	 * @param  mixed  $pw
	 * @param  mixed  $hash
	 *
	 * @return bool
	 */
	public static function password_verify( $pw, $hash ) {
		global $wp_hasher;
		require_once( ABSPATH . 'wp-includes/class-phpass.php' );

		$wp_hasher = new \PasswordHash( 16, false );

		return ( $wp_hasher->CheckPassword( $pw, $hash ) ) ? true : false;
	}

	/**
	 * Get the file extension for the passed string
	 *
	 * @param        $fileName
	 * @param  bool  $withDot
	 *
	 * @return bool
	 */
	public static function get_file_ext( $fileName, $withDot = false ) {
		$parts = pathinfo( $fileName );

		if ( isset( $parts['extension'] ) ) {
			$str = ( $withDot ) ? '.' . $parts['extension'] : $parts['extension'];
		} else {
			$str = false;
		}

		return $str;
	}

	/**
	 * Config the array of name value arrays to an associative array
	 * Used when sending form serialized array data
	 *
	 * @param  array  $data
	 *
	 * @return array
	 */
	public static function name_value_to_assoc( $data = [] ) {
		$arr = [];
		try {
			if ( ! empty( $data ) ) {
				foreach ( $data as $datum ) {
					if ( isset( $datum['name'] ) && isset( $datum['value'] ) ) {
						$arr[ $datum['name'] ] = \is_string( $datum['value'] ) ? \sanitize_text_field( $datum['value'] ) : $datum['value'];
					}
				}
			}
		} catch( \Exception $e ) {
			echo 'Could not convert name value array to associative array. ' . $e->getMessage();
		}

		return $arr;
	}

}

?>