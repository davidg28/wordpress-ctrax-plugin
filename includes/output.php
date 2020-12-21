<?php

namespace C_Trax_Integration\Includes;

use DateTime;
use C_Trax_Integration\Views\View;

/**
 * Output class which controls the look of the outputted data that the user is displayed.
 * @author Keith Andrews
 */
class Output {

	public function __construct() {
	}

	/**
	 * Escapes the passed value for use in HTML element attributes
	 *
	 * @param  mixed  $value
	 *
	 * @return string
	 */
	public static function value( $value ) {
		return \stripslashes_from_strings_only( $value );
	}

	/**
	 * Escapes the passed value for displaying to the end user
	 *
	 * @param  mixed  $value
	 *
	 * @return string
	 */
	public static function display( $value ) {
		return \stripslashes_from_strings_only( \esc_html( $value ) );
	}

	/**
	 * Method for returning an escaped value to be used in a textarea input element
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function value_text_area( $value ) {
		return \stripslashes_from_strings_only( \esc_textarea( $value ) );
	}

	/**
	 * Method for returning an escaped value to be used in a textarea input element
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function value_editor( $value ) {
		return \stripslashes_from_strings_only( \format_for_editor( $value ) );
	}

	/**
	 * Get the value of an array key
	 *
	 * @param $array
	 * @param $key
	 *
	 * @return null
	 */
	public static function key_val( $array, $key ) {
		$result = null;
		if ( is_array( $array ) && isset( $array[ $key ] ) ) {
			$result = $array[ $key ];
		}

		return $result;
	}

	/**
	 * Method for displaying an escaped value in a javascript environment
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function js( $value ) {
		return \esc_js( $value );
	}

	/**
	 * Unserialize a string value
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function as_array( $value ) {
		if ( $value == '' || $value == null || ! $value ) {
			return [];
		} else {
			return \stripslashes_deep( \maybe_unserialize( $value ) );
		}
	}

	/**
	 * Sanitize a string in order to use in a HTML element ID
	 *
	 * @param  string  $id
	 *
	 * @return mixed|string
	 */
	public static function sanitize_id( $id = '' ) {
		$string = trim( $id );
		$string = strip_tags( $string );
		$string = preg_replace( '/[^a-z0-9_-\s]+/i', '', $string );
		$string = strtoupper( $string );
		$string = str_replace( array( ' ', '-' ), array( '_', '_' ), $string );

		return $string;
	}

	/**
	 * Create HTML icons that will be used in 1-to-many tables to add and delete rows
	 *
	 * @param  mixed  $addName
	 * @param  mixed  $deleteName
	 *
	 * @return string
	 */
	public static function add_delete_icon( $addName, $deleteName ) {
		return View::make( 'misc/add-delete-icon.php', array(
			'addName'    => $addName,
			'deleteName' => $deleteName
		) );
	}

	/**
	 * Display the boolean value as a Font Awesome icon instead of value
	 *
	 * @param  bool  $value
	 *
	 * @return string
	 */
	public static function bool_to_icon( $value ) {
		$icon = ( $value ) ? 'check' : 'close';

		return View::make( 'misc/bool-to-icon.php', array(
			'icon' => $icon
		) );
	}

	/**
	 * Create the breadcrumb item
	 *
	 * @param  string      $text
	 * @param  string      $link
	 * @param  bool|false  $active
	 * @param  string      $iconStr
	 *
	 * @return string
	 */
	public static function bread_crumb_item( $text = '', $link = '#', $active = false, $iconStr = '' ) {
		$class    = ( $active ) ? ' active' : null;
		$textLink = ( $active ) ? $text : '<a href="' . $link . '">' . $text . '</a>';

		return View::make( 'misc/bread-crumb-item.php', array(
			'class'    => $class,
			'str'      => $iconStr,
			'textLink' => $textLink
		) );
	}

	/**
	 * Get the file icon for the passed file mime type
	 *
	 * @param $mime
	 *
	 * @return bool|string
	 */
	public static function get_file_icon( $mime ) {
		switch ( $mime ) {
			case 'image/jpeg':
			case 'image/gif':
			case 'image/png':
				$icon = 'file-image-o color-light-blue';
				break;
			case 'audio/mpeg':
				$icon = 'file-audio-o color-light-blue';
				break;
			case 'video/mpeg':
			case 'video/mp4':
				$icon = 'file-video-o color-light-blue';
				break;
			case 'text/plain':
				$icon = 'file-text-o color-black';
				break;
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
			case 'application/vnd.ms-word.document.macroEnabled.12':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.template':
			case 'application/vnd.ms-word.template.macroEnabled.12':
				$icon = 'file-word-o color-blue';
				break;
			case 'application/vnd.ms-powerpoint':
			case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
			case 'application/vnd.ms-powerpoint.presentation.macroEnabled.12':
			case 'application/vnd.openxmlformats-officedocument.presentationml.slideshow':
			case 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12':
			case 'application/vnd.openxmlformats-officedocument.presentationml.template':
			case 'application/vnd.ms-powerpoint.template.macroEnabled.12':
			case 'application/vnd.ms-powerpoint.addin.macroEnabled.12':
			case  'application/vnd.openxmlformats-officedocument.presentationml.slide':
			case 'application/vnd.ms-powerpoint.slide.macroEnabled.12':
				$icon = 'file-powerpoint-o color-orange';
				break;
			case 'application/vnd.ms-excel':
			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
			case 'application/vnd.ms-excel.sheet.macroEnabled.12':
			case 'application/vnd.ms-excel.sheet.binary.macroEnabled.12':
			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.template':
			case 'application/vnd.ms-excel.template.macroEnabled.12':
			case 'application/vnd.ms-excel.addin.macroEnabled.12':
			case 'text/csv':
				$icon = 'file-excel-o color-green';
				break;
			case 'application/pdf':
				$icon = 'file-pdf-o color-red';
				break;
			case 'application/zip':
				$icon = 'file-zip-o color-brown';
				break;
			default:
				$icon = 'file color-black';
				break;
		}

		return '<i class="fa fa-' . $icon . ' fa-2x"></i>';
	}

	/**
	 * Get the size of the number in readable bytes
	 *
	 * @param  int  $number
	 * @param  int  $decimals
	 *
	 * @return false|string
	 */
	public static function size_format( $number = 0, $decimals = 0 ) {
		return size_format( $number, $decimals );
	}

	/**
	 * Get the link for the process button functions
	 *
	 * @param  string|array  $params
	 *
	 * @return string
	 */
	public static function get_link( $params = [ C_TRAX_INTEGRATION_PROCESS_DISPATCH => false, 'pk' => false ] ) {
		$uri = \admin_url( 'admin.php?page=' . Input::get( 'page' ) );
		if ( is_array( $params ) ) {
			$link = \add_query_arg( $params, $uri );
		} else {
			// If param came through as string, dish out the right params
			switch ( $params ) {
				case 'Add':
					$args = [ C_TRAX_INTEGRATION_PROCESS_DISPATCH => 'create', 'pk' => false ];
					break;
				default:
					$args = [ C_TRAX_INTEGRATION_PROCESS_DISPATCH => false, 'pk' => false ];
					break;
			}

			$link = \add_query_arg( $args, $uri );
		}

		return $link;
	}

	/**
	 * Get the url to access a particular menu page based on the slug it was registered with
	 *
	 * @param        $menu_slug
	 * @param  bool  $echo
	 *
	 * @return string
	 */
	public static function menu_page_url( $menu_slug, $echo = true ) {
		global $_parent_pages;

		if ( isset( $_parent_pages[ $menu_slug ] ) ) {
			$parent_slug = $_parent_pages[ $menu_slug ];
			if ( $parent_slug && ! isset( $_parent_pages[ $parent_slug ] ) ) {
				$url = \admin_url( \add_query_arg( 'page', $menu_slug, $parent_slug ) );
			} else {
				$url = \admin_url( 'admin.php?page=' . $menu_slug );
			}
		} else {
			$url = '';
		}

		$url = \esc_url( $url );

		if ( $echo ) {
			echo $url;
		}

		return $url;
	}

	/**
	 * Create the option HTML for a select input
	 *
	 * @param  mixed        $key
	 * @param  mixed|array  $value
	 * @param  array        $list
	 *
	 * @return array
	 */
	public static function create_option_array( $key, $value, $list ) {
		$options = [ '' => '-- Select --' ];
		$display = '';

		// Loop through the list and add to the options array
		if ( ! empty( $list ) ) {
			foreach ( $list as $item ) {
				// If the $value was passed as an array, combine the text
				if ( is_array( $value ) ) {
					foreach ( $value as $col ) {
						$display .= $item->$col . ' ';
					}
					$display = trim( $display );
				} else {
					$display = $item->$value;
				}

				// Set the option key to the $key and $display
				$options[ $item->$key ] = $display;
			}
		}

		return $options;
	}

	/**
	 * Display a nice time format based on the datetime passed
	 *
	 * @param  string  $datetime
	 * @param  string  $format
	 *
	 * @return bool|string
	 */
	public static function nice_date_time( $datetime = '', $format = 'm/d/Y g:ia' ) {
		if ( $datetime instanceof DateTime ) {
			$niceDate = $datetime->format( $format );
		} else {
			$datetime = ( $datetime ) ? $datetime : date();
			$niceDate = date( $format, strtotime( $datetime ) );
		}

		return $niceDate;
	}

	/**
	 * Print or var_dump the data based into a readable text
	 *
	 * @param        $data
	 * @param  bool  $exit
	 * @param  bool  $varDump
	 */
	public static function debug( $data, $exit = false, $varDump = false ) {
		echo '<pre>';
		if ( $varDump ) {
			var_dump( $data );
		} else {
			print_r( $data );
		}
		echo '</pre>';

		if ( $exit ) {
			exit;
		}
	}

	/**
	 * Parse a query string into an array with text re-conversion
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function parse_query( $data ) {
		$data = preg_replace_callback( '/(?:^|(?<=&))[^=[]+/', function( $match )
		{
			return bin2hex( urldecode( $match[0] ) );
		}, $data );

		parse_str( $data, $values );

		return array_combine( array_map( 'hex2bin', array_keys( $values ) ), $values );
	}

	/**
	 * Check to see if the data passed is in JSON format
	 *
	 * @param  mixed  $data
	 *
	 * @return bool
	 */
	public static function is_json( $data ) {
		return ( is_string( $data ) && is_object( json_decode( $data ) ) ) ? true : false;
	}

	/**
	 * Display the input in JSON or if already in JSON, just display the input
	 *
	 * @param  mixed  $data
	 */
	public static function json_display( $data = array() ) {
		$pageContents = ob_get_clean();
		flush();

		if ( self::is_json( $data ) && $pageContents == '' ) {
			$info = $data;
		} elseif ( ! self::is_json( $data ) && $pageContents == '' ) {
			$info = json_encode( $data );
		} elseif ( self::is_json( $pageContents ) ) {
			$info = $pageContents;
		} elseif ( ! self::is_json( $data ) && $pageContents != '' ) {
			$data['page_contents'] = $pageContents;
			$info                  = ( ! is_array( $data ) ) ? json_decode( $data ) : json_encode( $data );
		} else {
			$info = '';
		}

		echo $info;
		exit;
	}

	/**
	 * Replace the values in the string where tags match
	 *
	 * @param  string  $string
	 * @param  array   $values
	 *
	 * @return null|string|string[]
	 */
	public static function replace_tags( $string = '', $values = [] ) {
		if ( ! empty( $values ) ) {
			$patterns     = [];
			$replacements = array_values( $values );
			foreach ( $values as $find => $replace ) {
				$patterns[] = '/%' . $find . '%/';
			}
			$string = preg_replace( $patterns, $replacements, $string );
		}

		return $string;
	}

	/**
	 * Get the first value that matches object property value
	 *
	 * @param       $array
	 * @param       $property
	 * @param       $value
	 * @param  int  $limit
	 *
	 * @return mixed
	 */
	public static function array_filter( $array, $property, $value, $limit = 1 ) {
		$filtered = array_filter( $array, function( $object, $key ) use ( $property, $value )
		{
			return ( $object->{$property} === $value );
		}, ARRAY_FILTER_USE_BOTH );

		return current( $filtered );
	}

	/**
	 * Get the data returned for an ajax response
	 *
	 * @param                   $data
	 * @param  string           $message
	 * @param  \Exception|null  $exception
	 */
	public static function json( $data, string $message = '', \Exception $exception = null ) {
		$result = [];
		if ( $exception ) {
			$result['status']    = 'error';
			$result['exception'] = [
				'message' => $exception->getMessage(),
				'code'    => $exception->getCode(),
				'line'    => $exception->getLine(),
			];
		} else {
			$result['status'] = 'success';
			$result['data']   = $data;
		}
		$result['message'] = $message;

		wp_send_json( $result );
	}
}

?>