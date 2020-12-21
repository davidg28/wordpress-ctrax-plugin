<?php

namespace C_Trax_Integration\Includes;

/**
 * URL class to parse urls
 * @author Keith Andrews
 */
class Url {

	private $url;
	private $has_query_string;
	private $query_vars;

	public function __construct( $url ) {
		$this->url              = $url;
		$this->has_query_string = false;
		$this->query_vars       = [];
	}

	/**
	 * Append a variable to the query
	 *
	 * @param $varname
	 * @param $val
	 */
	public function append_query_var( $varname, $val ) {
		if ( ! empty( $varname ) && ! empty( $val ) ) {
			$this->query_vars[ $varname ] = $val;
		}
	}

	/**
	 * Get the current URL
	 * @return string
	 */
	public function get_url() {
		if ( ! empty( $this->query_vars ) ) {
			$this->url .= "?";
			$this->url .= http_build_query( $this->query_vars );
		}

		return $this->url;
	}
}

?>