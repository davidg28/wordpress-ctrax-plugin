<?php

namespace C_Trax_Integration\Traits;

use C_Trax_Integration\Includes\Output;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

define( 'C_TRAX_INTEGRATION_API_BASE', 'https://central-api.dev.cannatrax.net/api/v1/' );

/**
 * Trait WebService
 * @package C_Trax_Integration\Traits
 */
trait WebService {

	/**
	 * Make a call to the web service with the data passed
	 *
	 * @param  string  $uri
	 * @param  array   $fields
	 * @param  string  $type
	 * @param  string  $version  : base:ctrax centeral-api, instance-v1:client instance-api version 1, client instance-api version 2
	 * @param  string  $format
	 *
	 * @return array|mixed|object|string
	 * @throws \ErrorException
	 */
	public function webservice( string $uri, array $fields = [], string $type = 'post', string $version = 'base', string $format = 'json' ) {
		$result    = null;
		$exception = null;

		$params  = [];
		$baseUri = $this->get_api_base_uri( $version );

		// Check for the token
		/*if ( ! isset( $fields['token'] ) ) {
			$token = get_option( C_TRAX_INTEGRATION_TOKEN );
			// Don't allow connections without the token
			if ( ! $token ) {
				throw new \Exception( 'Cannot connect to the API without the token set.' );
			}
			$params = [
				'token' => $token,
			];
		}*/

		// Initialize the client
		$client = new Client( [
			'base_uri'    => $baseUri . '' . $uri,
			'form_params' => array_merge( $params, $fields ),
			'verify'      => false // TODO: Remove, only use for local
		] );

		try {
			if ( is_callable( [ $client, $type ] ) ) {
				$response = $client->{$type}( '' );
			} else {
				$response = $client->post( '' );
			}

			// Return result formatted to the desired type
			if ( $format == 'json' ) {
				$result = json_decode( $response->getBody()->getContents() );
			} else {
				$result = $response->getBody()->getContents();
			}
		} catch( ConnectException  $e ) {
			$handler = $e->getHandlerContext();
			if ( is_array( $handler ) && isset( $handler['error'] ) ) {
				$exception = $handler['error'];
			}
		} catch( RequestException $e ) {
			if ( $e->hasResponse() ) {
				$response  = json_decode( $e->getResponse()->getBody() );
				$exception = $response->message;
			} else {
				$exception = $e->getMessage();
			}
		}

		// Throw the nice error to the user
		if ( $exception ) {
			throw new \ErrorException( $exception );
		}

		return $result;
	}

	/**
	 * Get the appropriate uri based on the version passed
	 *
	 * @param  string  $version
	 *
	 * @return string
	 * @throws \ErrorException
	 */
	protected function get_api_base_uri( string $version ): string {
		$uri            = '';
		$clientInstance = $this->get_option( C_TRAX_INTEGRATION_INSTANCE_DOMAIN );

		if(!$clientInstance)
		{
			throw new \ErrorException('The client instance has not been set.');
		}

		switch ( $version ) {
			case 'instance-v1':
				$uri = $clientInstance . '/api/v1/';
				break;
			case 'instance-v2':
				$uri = $clientInstance . '/api/v2/';
				break;
			case 'base':
				$uri = C_TRAX_INTEGRATION_API_BASE;
				break;
		}

		return $uri;
	}

	/**
	 * Get the account based on the token passed
	 *
	 * @param $username
	 * @param $password
	 *
	 * @return array|mixed|object|string
	 * @throws \ErrorException
	 */
	public function get_ctrax_account( $username, $password ) {
		return $this->webservice( 'auth/wordpressPlugin/issueToken', [ 'username' => $username, 'password' => $password ], 'post', 'instance-v2' );
	}

	/**
	 * Get the refresh token to keep the login alive
	 *
	 * @param $refreshToken
	 *
	 * @return array|mixed|object|string
	 * @throws \ErrorException
	 */
	public function get_refresh_token( $refreshToken ) {
		return $this->webservice( 'auth/wordpressPlugin/refreshToken', [ 'refresh_token' => $refreshToken ], 'post', 'instance-v2' );
	}

	/**
	 * Get the ctrax square app id
	 * @return array|mixed|object|string
	 * @throws \ErrorException
	 */
	public function get_ctrax_square_api_id() {
		return $this->webservice( 'payments/vendors/square/appId?environment=sandbox', [], 'get' );
	}
}

?>
