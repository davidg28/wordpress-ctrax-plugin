<?php

namespace C_Trax_Integration\Includes;

use C_Trax_Integration\Views\View;

/**
 * Response class that stores data to be passed to the user.
 * This class uses View and Session.
 */
class Response {
	private       $response   = [];
	private       $status     = [];
	private       $hasNotice;
	public static $sessionVar = 'response';

	/**
	 * Set the class vars to the default values
	 */
	public function __construct() {
		if ( Session::var_isset( self::$sessionVar ) ) {
			//$this->response =  Session::getVar(self::$sessionVar);
			Output::debug( $this, 1);
		}
	}

	/**
	 * Set all of the class vars in one swoop
	 *
	 * @param  mixed   $response
	 * @param  string  $status
	 */
	public function set( $response, $status = 'info' ) {
		$this->set_response( $response );
		$this->set_status( $status );
		$this->set_has_notice( true );
	}

	/**
	 * Set all of the class vars in one swoop for displaying an error
	 *
	 * @param  mixed  $response
	 */
	public function error( $response ) {
		$this->set_response( $response );
		$this->set_status( 'error' );
		$this->set_has_notice( true );
	}

	/**
	 * Set the response message
	 *
	 * @param  mixed  $response
	 */
	public function set_response( $response ) {
		$this->response[] = $response;
	}

	/**
	 * Clear all of the responses
	 */
	public function clear_response() {
		$this->response = [];
	}

	/**
	 * Set the status of the response
	 *
	 * @param  string  $status
	 */
	public function set_status( $status ) {
		$this->status[] = $status;
	}

	/**
	 * Clear all of the statuses
	 */
	public function clear_status() {
		$this->status = [];
	}

	/**
	 * Set if the response has a notice
	 *
	 * @param  bool  $notice
	 */
	public function set_has_notice( $notice = false ) {
		$this->hasNotice = (bool) $notice;
	}

	/**
	 * Get the value for the hasNotice variable
	 * @return bool
	 */
	public function get_has_notice() {
		return $this->hasNotice;
	}

	/**
	 * Action that will display the response on the admin back-end
	 */
	public function admin_notice_action() {
		if ( $this->hasNotice ) {
			foreach ( $this->response as $key => $response ) {
				View::make( 'misc/admin-response.php', [ 'status' => $this->status[ $key ], 'response' => $response ] );
				// Remove the session var, just in case
			}
			$this->flush();
		}

		return;
	}

	/**
	 * Action that will display the response on the user front-end
	 */
	public function user_notice_action() {
		if ( $this->hasNotice ) {
			foreach ( $this->response as $key => $response ) {
				View::make( 'misc/alert-message.php', [ 'type' => $this->status[ $key ], 'message' => $response ] );
				// Remove the session var, just in case
			}
			$this->flush();
		}

		return;
	}

	/**
	 * Flush the class vars to the default values
	 */
	public function flush() {
		$this->clear_response();
		$this->clear_status();
		$this->set_has_notice( false );
	}

	/**
	 * Flush the class vars to the default values
	 */
	public function set_in_session() {
		Session::set_var( self::$sessionVar, $this );
		$this->clear_response();
		$this->clear_status();
		$this->set_has_notice( false );
	}

	/**
	 * Display any messages for a try catch block
	 *
	 * @param  null         $message
	 * @param  \Exception   $e
	 * @param  bool|string  $messageType
	 *
	 * @return string
	 */
	public function catch_message( $message = null, \Exception $e = null, $messageType = 'error' ) {
		$display = $message;

		switch ( $messageType ) {
			//case (is_bool($error) && true):
			case 'error':
				$type = 'error';
				break;
			case 'success':
				//case (is_bool($error) && false):
				$type = 'success';
				break;
			case 'info':
			case 'warning':
				$type = 'info';
				break;
			default:
				if ( is_bool( $messageType ) && $messageType )
					$type = 'error';
				elseif ( is_bool( $messageType ) && ! $messageType )
					$type = 'success';
				else
					$type = 'info';
				break;
		}

		echo View::make( 'misc/system-message.php', [ 'message' => $display, 'type' => $type, 'exception' => $e->getMessage() ] );
	}
}