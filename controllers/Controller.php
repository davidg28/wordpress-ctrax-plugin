<?php

namespace C_Trax_Integration\Controllers;

use C_Trax_Integration\Traits\Options;

abstract class Controller {
	use Options;

	public function __construct() {

	}

	/**
	 * Init the shortcodes to be used in the plugin
	 */
	public static function init_shortcodes() {
	}

	/**
	 * Init the actions to be used in the plugin
	 */
	public static function init_actions() {
	}

	/**
	 * Init the filters to be used in the plugin
	 */
	public static function init_filters() {
	}

	/**
	 * Register main scripts to be used on all plugin pages
	 */
	public static function register_scripts() {
		// Basic scripts for all controllers
	}

	/**
	 * Get a new instance
	 * @return static
	 */
	public static function instance() {
		return new static();
	}
}