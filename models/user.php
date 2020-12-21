<?php
namespace C_Trax_Integration\Models;

use C_Trax_Integration\Traits\Options;

/**
 * User Class.
 */
class User extends Model {

	use Options;

	public $instance_domain;
	public $username;
	public $auth_token;
	public $refresh_token;

	/**
	 * Save the user to the database
	 * @throws \ErrorException
	 */
	public function save()
	{
		$result = $this->save_option( C_TRAX_INTEGRATION_OPTION_ACCOUNT, $this );
		if(!$result)
		{
			throw new \ErrorException('The user was not saved.');
		}
	}
}
?>