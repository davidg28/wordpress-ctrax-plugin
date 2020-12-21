<?php

namespace C_Trax_Integration\Traits;

define( 'C_TRAX_INTEGRATION_OPTIONS', C_TRAX_INTEGRATION_PREFIX . '-options' );
define( 'C_TRAX_INTEGRATION_OPTION_AUTH_TOKEN', C_TRAX_INTEGRATION_PREFIX . '-auth_token' );
define( 'C_TRAX_INTEGRATION_OPTION_REFRESH_TOKEN', C_TRAX_INTEGRATION_PREFIX . '-refresh_token' );
define( 'C_TRAX_INTEGRATION_INSTANCE_DOMAIN', C_TRAX_INTEGRATION_PREFIX . '-instance-domain' );

/**
 * Trait Options
 * @package C_Trax_Integration\Traits
 */
trait Options {
	private $_settings;

	/**
	 * Get the settings from the database
	 *
	 * @param  bool  $reload
	 *
	 * @return \stdClass
	 */
	public function get_settings( $reload = false ): \stdClass {
		if ( ! $this->_settings || $reload ) {
			$this->_settings = ( ! get_option( C_TRAX_INTEGRATION_OPTIONS ) ) ? new \stdClass() : maybe_unserialize( get_option( C_TRAX_INTEGRATION_OPTIONS ) );
		}

		return $this->_settings;
	}

	/**
	 * Save a new/existing key value pair in the wp options table
	 *
	 * @param        $key
	 * @param  null  $value
	 *
	 * @return bool
	 */
	public function save_option( $key, $value = null ) : bool {
		$result = false;
		try {
			if ( ! update_option( $key, $value ) ) {
				throw new \Exception( 'The option ' . $key . ' could not be saved.' );
			}

			$result = true;
		} catch( \Exception $e ) {

		}

		return $result;
	}

	/**
	 * Get the option value
	 *
	 * @param          $key
	 * @param  string  $default
	 *
	 * @return bool|mixed|void
	 */
	public function get_option( $key, $default = '' ) {
		return get_option( $key, $default );
	}
}

?>