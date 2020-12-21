<?php

namespace C_Trax_Integration\Views;

/**
 * View class that controls the making of the html pages that are displayed
 * Main responsibility is to control what processes are to be ran or displayed.
 * This class uses the ListTable class.
 * @author Keith Andrews
 */
class View {

	/**
	 * Method for making the view and including the passed file path and data
	 *
	 * @param  string  $path
	 * @param  array   $data
	 * @param  bool    $toVar
	 * @param  bool    $displayAdminNotices
	 *
	 * @return string
	 */
	public static function make( $path, $data = [], $toVar = false, $displayAdminNotices = false ) {
		$completeFile = C_TRAX_INTEGRATION_PATH . 'views/' . $path;

		if ( $toVar ) {
			ob_start();
		}

		try {
			if ( $displayAdminNotices ) {
				do_action( 'admin_notices' );
			}
			// Make sure it's there
			if ( ! file_exists( $completeFile ) ) {
				throw new \Exception( 'No file exists for the path "' . $path . '".' );
			}

			// Extract all of the data array elements into variable for the view
			extract( $data, EXTR_PREFIX_SAME, 'mod' );
			include $completeFile;
		} catch( \Exception $e ) {
			echo $e->getMessage();
		}

		// Get the output and place into variable; fix for wordpress shortcode always above content
		if ( $toVar ) {
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

		return false;
	}

	/**
	 * Make a notice
	 * @param          $message
	 * @param  string  $type
	 * @param  string  $icon
	 *
	 * @return bool|string
	 */
	public static function notice( $message, $type = 'info', $icon = 'fa-exclamation-circle' ) {
		return self::make( 'misc/notice.php', [
			'message' => $message,
			'type'    => $type,
			'icon'    => $icon
		] );
	}
}