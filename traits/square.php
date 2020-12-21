<?php

namespace C_Trax_Integration\Traits;

define( 'C_TRAX_INTEGRATION_OPTION_SQUARE_APP_ID', C_TRAX_INTEGRATION_PREFIX . '-square-app-id' );

/**
 * Trait Square
 * @package C_Trax_Integration\Traits
 */
trait Square {
	private $_square_app_id;

	/**
	 * Get the stored c-trax account
	 * @return \stdClass
	 */
	public function get_square_app_id(): ?string {
		if ( ! $this->_square_app_id ) {
			$this->_square_app_id = get_option( C_TRAX_INTEGRATION_OPTION_SQUARE_APP_ID, null );
		}

		return $this->_square_app_id;
	}

	/**
	 * Set the square app id option
	 *
	 * @param $squareAppId
	 */
	public function set_square_app_id( $squareAppId ) {
		if ( is_object( $squareAppId ) && isset( $squareAppId->square_app_id ) ) {
			$this->save_option( C_TRAX_INTEGRATION_OPTION_SQUARE_APP_ID, $squareAppId->square_app_id );
		}
	}
}

?>