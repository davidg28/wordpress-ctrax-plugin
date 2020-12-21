<?php

namespace C_Trax_Integration\Includes;

use DateTime;
use C_Trax_Integration\Views\View;

/**
 * Output class which controls the look of the outputted data that the user is displayed.
 * @author Keith Andrews
 */
class Form {

	public function __construct() {
	}

	/**
	 * Create the input from the base file
	 * @param  string  $type
	 * @param  string  $name
	 * @param  string  $label
	 * @param  null    $value
	 * @param  array   $attrs
	 * @param  string  $labelPosition
	 *
	 * @return false|string
	 */
	public static function input( string $type, string $name, string $label = '', $value = null, array $attrs = [], string $labelPosition = 'top' ) {
		$options = ( isset( $attrs['options'] ) ) ? $attrs['options'] : null;
		unset( $attrs['options'] );

		return View::make( 'input/base.php', [
			'type'          => $type,
			'name'          => $name,
			'options'       => $options,
			'value'         => $value,
			'label'         => $label,
			'attrs'         => $attrs,
			'labelPosition' => $labelPosition
		] );
	}

	/**
	 * Create a text type input
	 * @param  string  $name
	 * @param  string  $label
	 * @param          $value
	 * @param  array   $attrs
	 * @param  string  $labelPosition
	 *
	 * @return false|string
	 */
	public static function text( string $name, string $label, $value, array $attrs = [], string $labelPosition = 'top' ) {
		return self::input( 'text', $name, $label, $value, $attrs, $labelPosition );
	}

	/**
	 * Create a password type input
	 * @param  string  $name
	 * @param  string  $label
	 * @param          $value
	 * @param  array   $attrs
	 * @param  string  $labelPosition
	 *
	 * @return false|string
	 */
	public static function password( string $name, string $label, $value, array $attrs = [], string $labelPosition = 'top' ) {
		return self::input( 'password', $name, $label, $value, $attrs, $labelPosition );
	}

	/**
	 * Display the option for a select input and set the value
	 *
	 * @param  mixed  $value
	 * @param  mixed  $display
	 * @param  mixed  $setVal
	 *
	 * @return string
	 */
	public static function display_select_option( $value, $display, $setVal = '' ) {
		$selected = false;
		if ( is_array( $setVal ) && in_array( $value, $setVal ) ) {
			$selected = true;
		} elseif ( ! is_array( $setVal ) && $value == $setVal ) {
			$selected = true;
		}

		return View::make( 'input/option.php', array(
			'value'      => $value,
			'display'    => $display,
			'isSelected' => $selected
		) );
	}

	/**
	 * Create HTML radio input buttons for a Yes/No option
	 *
	 * @param  mixed  $name
	 * @param  mixed  $id
	 * @param  mixed  $setVal
	 * @param  bool   $isBool
	 *
	 * @return string
	 */
	public static function input_yes_no_radio( $name, $id, $setVal = null, $isBool = false ) {
		return View::make( 'input/yes-no-radio.php', array(
			'name'   => $name,
			'id'     => $id,
			'isYes'  => ( 'Yes' == $setVal || ( $isBool && $setVal == 1 ) ),
			'isNo'   => ( 'No' == $setVal || ( $isBool && $setVal == 0 ) ),
			'isBool' => $isBool
		) );
	}

	/**
	 * Create HTML checkbox input and set if value is in data passed
	 *
	 * @param  mixed  $id
	 * @param  mixed  $value
	 * @param  array  $setArray
	 *
	 * @return string
	 */
	public static function input_checkbox( $id, $value, $setArray = array() ) {

		return View::make( 'input/checkbox.php', array(
			'name'      => $id . '[]',
			'id'        => $id . '-' . Output::sanitize_id( $value ),
			'value'     => $value,
			'isChecked' => ( is_array( $setArray ) && in_array( $value, $setArray ) )
		) );
	}

	/**
	 * Display a file input with the current file below
	 *
	 * @param $id
	 * @param $current
	 *
	 * @return string
	 */
	public static function input_file( $id, $current ) {
		$append = '__FILE';

		return View::make( 'input/file.php', array(
			'name'    => $id . $append,
			'id'      => $id . $append,
			'current' => $current,
		) );
	}
}

?>