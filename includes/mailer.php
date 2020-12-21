<?php

namespace C_Trax_Integration\Includes;

/**
 * Mailer class which controls usage of sending mail in the plugin
 * @author Keith Andrews
 */
class Mailer {

	private $settings;

	public function __construct() {
		$this->settings = \maybe_unserialize( \get_option( C_TRAX_INTEGRATION_OPTIONS ) );
	}

	/**
	 * Send an email from the system to one or many emails, along with attachments
	 *
	 * @param  string|array  $to
	 * @param  string        $subject
	 * @param  string        $content
	 * @param  string        $extraHeaders
	 * @param  string|array  $attachments
	 *
	 * @return bool
	 * @throws \Exception
	 * TODO: Make use of settings to configure the email data
	 */
	public function send( $to, $subject = '', $content = '', $extraHeaders = '', $attachments = '' ) {
		$from     = $this->settings->core_email_from;
		$siteName = \get_option( 'blogname' );

		// Setup email to be sent
		$headers = '';

		// Parse the to emails or throw exception if there isn't one
		$sendTo = $this->parse_to( $to );
		if ( ! $sendTo ) {
			throw new \Exception( 'Could not send email since there is no email to send to.', 1500 );
		}

		// Fire that baby out
		$sent = \wp_mail( $sendTo, $subject, $content, $headers, $attachments );
		if ( ! $sent ) {
			$error = error_get_last();
			throw new \Exception( 'Could not send email. ' . @$error['message'], 0002 );
		}

		return $sent;
	}

	/**
	 * Parse the email sting or array for delivery
	 *
	 * @param $to
	 *
	 * @return array|bool|string
	 */
	private function parse_to( $to ) {
		$sendTo = '';

		switch ( $to ) {
			case '':
				$sendTo = false;
				break;
			case is_array( $to ):
				$sendTo = implode( ',', $to );
				break;
			default:
				$sendTo = $to;
				break;
		}

		return $sendTo;
	}
}

?>