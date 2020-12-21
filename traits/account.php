<?php

namespace C_Trax_Integration\Traits;

use C_Trax_Integration\Includes\Output;
use C_Trax_Integration\Models\User;

define( 'C_TRAX_INTEGRATION_OPTION_ACCOUNT', C_TRAX_INTEGRATION_PREFIX . '-account' );

/**
 * Trait User
 * @package C_Trax_Integration\Traits
 */
trait Account {
	private $_user;

	/**
	 * Get the stored c-trax account
	 * @return User
	 */
	public function get_account(): ?User {
		if ( ! $this->_user ) {
			$this->set_user();
		}

		return $this->_user;
	}

	public function set_user() {
		$this->_user = get_option( C_TRAX_INTEGRATION_OPTION_ACCOUNT, new User() );
		/*$this->_user->auth_token    = get_option( C_TRAX_INTEGRATION_OPTION_AUTH_TOKEN );
		$this->_user->refresh_token = get_option( C_TRAX_INTEGRATION_OPTION_REFRESH_TOKEN );*/
	}

	/**
	 * Delete the user option from the database
	 */
	public function delete_user() {
		delete_option( C_TRAX_INTEGRATION_OPTION_ACCOUNT);
	}

	/**
	 * Get a fake account for testing
	 * TODO: delete when ctrax api is connected
	 *
	 * @param $data
	 *
	 * @return \stdClass
	 */
	public function get_fake_account( $data ) {
		$account             = new \stdClass();
		$account->first_name = 'Test';
		$account->last_name  = 'Account';
		$account->id         = 10000;
		$account->email      = 'test_account@test.com';
		$account->_data      = $data;

		return $account;
	}
}

?>