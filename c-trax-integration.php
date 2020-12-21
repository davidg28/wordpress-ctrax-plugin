<?php

namespace C_Trax_Integration;

use C_Trax_Integration\Controllers\SettingController;
use C_Trax_Integration\Controllers\SquareController;
use C_Trax_Integration\Includes\Input;
use C_Trax_Integration\Includes\Installer;
use C_Trax_Integration\Includes\Output;
use C_Trax_Integration\Includes\Response;
use C_Trax_Integration\Includes\Session;
use C_Trax_Integration\Traits\Account;
use C_Trax_Integration\Traits\Options;
use C_Trax_Integration\Traits\Square;
use C_Trax_Integration\Traits\WebService;
use C_Trax_Integration\Views\View;

/**
 * Plugin Name: C-Trax Integration
 * Plugin URI: https://www.c-trax.com/
 * Description: Wordpress plugin for clients in order to integrate Square and the C-Trax API.
 * Version: 0.1
 * Author: C-Trax
 * Author URI: https://www.c-trax.com/
 * Copyright: (c) 2020, C-Trax
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * @author    C-Trax
 * @copyright Copyright (c) 2020, C-Trax
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * WC requires at least: 4.0
 * WC tested up to: 4.0.1
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Plugin Definitions
 */
define( 'C_TRAX_INTEGRATION_VERSION', '0.1' );
define( 'C_TRAX_INTEGRATION_MINIMUM_PHP_VERSION', 5.6 );
define( 'C_TRAX_INTEGRATION_MINIMUM_WP_VERSION', 5.0 );
define( 'C_TRAX_INTEGRATION_MINIMUM_WC_VERSION', 4.0 );

define( 'C_TRAX_INTEGRATION_PROJECT', 'C-Trax Integration' );
define( 'C_TRAX_INTEGRATION_PREFIX', 'c-trax-integration' );
define( 'C_TRAX_INTEGRATION_ACTION', 'c_trax_integration' );
define( 'C_TRAX_INTEGRATION_PATH', \plugin_dir_path( __FILE__ ) );
define( 'C_TRAX_INTEGRATION_URL', \plugin_dir_url( __FILE__ ) );
define( 'C_TRAX_INTEGRATION_ASSETS_PATH', C_TRAX_INTEGRATION_PATH . 'assets/' );
define( 'C_TRAX_INTEGRATION_ASSETS_URL', C_TRAX_INTEGRATION_URL . 'assets/' );
define( 'C_TRAX_INTEGRATION_ADMIN_PERMISSION', 'delete_pages' );
define( 'C_TRAX_INTEGRATION_SESSION_PREFIX', C_TRAX_INTEGRATION_PREFIX . '_' );
define( 'C_TRAX_INTEGRATION_AJAX_POSTFIX', C_TRAX_INTEGRATION_PREFIX . '-ajax' );
define( 'C_TRAX_INTEGRATION_AJAX_DOM', 'com.c_trax_integration' );
define( 'C_TRAX_INTEGRATION_PROCESS_DISPATCH', C_TRAX_INTEGRATION_PREFIX . '-process' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Autoload plugin and vendor files before all else
if ( ! file_exists( C_TRAX_INTEGRATION_PATH . 'vendor/autoload.php' ) ) {
	return;
}

$loader = require_once( C_TRAX_INTEGRATION_PATH . 'vendor/autoload.php' );

/**
 * Class C_Trax_Integration
 * @package C_Trax_Integration
 * TODO: Clean up and reorganize methods for cleaner look
 */
class C_Trax_Integration {

	use Account, Square, WebService, Options;

	public  $response;
	private $notices = [];

	/**
	 * C_Trax_Integration constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_uninstall_hook( __FILE__, 'self::uninstall' );

		add_action( 'admin_init', [ $this, 'check_environment' ] );
		add_action( 'admin_init', [ $this, 'add_plugin_notices' ] );

		add_action( 'admin_notices', [ $this, 'admin_notices' ], 15 );

		// Hooks and actions if compatible
		if ( $this->is_environment_compatible() ) {
			$this->init_actions();
			$this->init_shortcodes();
			$this->init_filters();
		}
	}

	/**
	 * Initialize the plugin shortcodes
	 */
	public function init_shortcodes() {
		// Controller Shortcodes
		SettingController::init_shortcodes();
	}

	/**
	 * Initialize the plugin actions
	 */
	public function init_actions() {
		// Check for any database updates
		if ( \is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			add_action( 'admin_init', [ $this, 'install' ] );
			add_action( 'admin_init', [ $this, 'update_check' ] );
			add_action( 'admin_init', [ $this, 'process_update' ] );
		}

		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
		//		add_action( 'plugins_loaded', [ $this, 'init_shortcodes' ] );
		//		add_action( 'plugins_loaded', [ $this, 'init_actions' ] );
		//		add_action( 'plugins_loaded', [ $this, 'init_filters' ] );
		add_action( 'admin_menu', [ $this, 'init_menu' ] );

		// Styling include
		add_action( 'wp_enqueue_scripts', [ $this, 'app_css' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );

		// Admin actions
		add_action( 'admin_enqueue_scripts', [ $this, 'app_css' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );

		// Front end ajax
		add_action( 'wp_ajax_' . C_TRAX_INTEGRATION_AJAX_POSTFIX, [ $this, 'parseAJAX' ] );
		add_action( 'wp_ajax_nopriv_' . C_TRAX_INTEGRATION_AJAX_POSTFIX, [ $this, 'parseAJAX' ] );

		// Controller Actions
		SettingController::init_actions();
		SquareController::init_actions();
	}

	/**
	 * Initialize the plugin filters
	 */
	public function init_filters() {
		// Controller filters
		SettingController::init_filters();
		SquareController::init_filters();
	}

	/**
	 * Initializes the plugin.
	 */
	public function init_plugin() {

		if ( ! $this->plugins_compatible() ) {
			return;
		}

		// Get the response from session or new
		$this->response = new Response();
	}

	/**
	 * Check if install environment is compatible
	 * @return mixed
	 */
	protected function is_environment_compatible() {

		return version_compare( PHP_VERSION, C_TRAX_INTEGRATION_MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Get initial plugin notices of out-of-date core/woocommerce code
	 */
	public function add_plugin_notices() {

		// Wordpress
		if ( ! $this->is_wp_compatible() ) {

			$this->add_admin_notice( 'update_wordpress', 'error', sprintf(
				'%s requires WordPress version %s or higher. Please %supdate WordPress &raquo;%s',
				'<strong>' . C_TRAX_INTEGRATION_PROJECT . '</strong>',
				C_TRAX_INTEGRATION_MINIMUM_WP_VERSION,
				'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>'
			) );
		}

		// WooCommerce TODO: Check if woocommerce is activated first
		if ( ! $this->is_wc_compatible() ) {

			$this->add_admin_notice( 'update_woocommerce', 'error', sprintf(
				'%1$s requires WooCommerce version %2$s or higher. Please %3$supdate WooCommerce%4$s to the latest version, or %5$sdownload the minimum required version &raquo;%6$s',
				'<strong>' . C_TRAX_INTEGRATION_PROJECT . '</strong>',
				C_TRAX_INTEGRATION_MINIMUM_WC_VERSION,
				'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>',
				'<a href="' . esc_url( 'https://downloads.wordpress.org/plugin/woocommerce.' . C_TRAX_INTEGRATION_MINIMUM_WC_VERSION . '.zip' ) . '">', '</a>'
			) );
		}
	}

	/**
	 * Check the environment for compatibility
	 */
	public function activation_check() {

		if ( ! $this->is_environment_compatible() ) {

			$this->deactivate_plugin();

			wp_die( C_TRAX_INTEGRATION_PROJECT . ' could not be activated. ' . $this->get_environment_message() );
		}
	}

	/**
	 * Checks the environment on loading WordPress, just in case the environment changes after activation.
	 */
	public function check_environment() {

		if ( ! $this->is_environment_compatible() && \is_plugin_active( C_TRAX_INTEGRATION_PREFIX ) ) {

			$this->deactivate_plugin();

			$this->add_admin_notice( 'bad_environment', 'error', C_TRAX_INTEGRATION_PROJECT . ' has been deactivated. ' . $this->get_environment_message() );
		}
	}

	/**
	 * Check of the plugin is compatible
	 * @return bool
	 */
	protected function plugins_compatible() {

		return $this->is_wp_compatible() && $this->is_wc_compatible();
	}

	/**
	 * Determines if the WordPress compatible.
	 * @return bool
	 */
	protected function is_wp_compatible() {

		if ( ! C_TRAX_INTEGRATION_MINIMUM_WP_VERSION ) {
			return true;
		}

		return version_compare( get_bloginfo( 'version' ), C_TRAX_INTEGRATION_MINIMUM_WP_VERSION, '>=' );
	}

	/**
	 * Determines if the WooCommerce compatible.
	 * @return bool
	 */
	protected function is_wc_compatible() {

		if ( ! C_TRAX_INTEGRATION_MINIMUM_WC_VERSION ) {
			return true;
		}

		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, C_TRAX_INTEGRATION_MINIMUM_WC_VERSION, '>=' );
	}

	/**
	 * Gets the message for display when the environment is incompatible with this plugin.
	 * @return string
	 */
	protected function get_environment_message() {
		$message = sprintf( 'The minimum PHP version required for this plugin is %1$s. You are running %2$s.', C_TRAX_INTEGRATION_MINIMUM_PHP_VERSION, PHP_VERSION );

		return $message;
	}

	/**
	 * Deactivates the plugin.
	 */
	protected function deactivate_plugin() {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( Input::has( 'activate' ) ) {
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Adds an admin notice to be displayed.
	 *
	 * @param  string  $slug     the slug for the notice
	 * @param  string  $class    the css class for the notice
	 * @param  string  $message  the notice message
	 */
	public function add_admin_notice( $slug, $class, $message ) {
		$this->notices[ $slug ] = [
			'class'   => $class,
			'message' => $message
		];
	}

	/**
	 * Displays any admin notices
	 */
	public function admin_notices() {
		foreach ( (array) $this->notices as $notice_key => $notice ) {
			?>
			<div class="<?php echo esc_attr( $notice['class'] ); ?>">
				<p>
					<?php echo wp_kses( $notice['message'], [ 'a' => [ 'href' => [] ] ] ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Install DB Table
	 */
	public function activate() {
		if ( ! $this->is_environment_compatible() ) {
			$this->deactivate_plugin();

			wp_die( C_TRAX_INTEGRATION_PROJECT . ' could not be activated. ' . $this->get_environment_message() );
		}
	}

	/**
	 * Install DB Table
	 */
	public function install() {
		try {
			$install = new Installer();
			$install->install();
		} catch( \Exception $e ) {
			$this->add_admin_notice( 'install_plugin', 'error', 'Error Installing ' . C_TRAX_INTEGRATION_PROJECT . ': ' . $e->getMessage() );
		}
	}

	/**
	 * Uninstall the plugin and remove all of it's data
	 * TODO: Add un-installer
	 */
	public static function uninstall() {
		//$uninstall = new Uninstaller();
		//$uninstall->run();
	}

	/**
	 * Check to see if there is a database update for the plugin
	 */
	public function update_check() {
		if ( ! \is_admin() || Input::has( 'update-db' ) ) {
			return;
		}

		$install = new Installer();
		if ( $install->has_update() ) {
			$this->response->set( 'There is a database update for ' . C_TRAX_INTEGRATION_PROJECT . '. <a href="' . Output::menu_page_url( C_TRAX_INTEGRATION_PREFIX, false ) . '&update-db=true">Update now!</a>', 'warning' );
		}
	}

	/**
	 * Process the database update
	 * @return bool|void
	 */
	public function process_update() {
		if ( ! \is_admin() ) {
			return;
		}

		$processed = false;

		// Run if the variable is set
		if ( Input::has( 'update-db' ) ) {
			try {
				$install = new Installer();
				$version = $install->update();

				if ( $version ) {
					$this->response->set( 'The ' . C_TRAX_INTEGRATION_PROJECT . ' database has been updated to version ' . $version . '.', 'success' );
					$processed = true;
				}
			} catch( \Exception $e ) {
				$this->response->error( __( $e->getMessage() ) );
			}
		}

		return $processed;
	}

	/**
	 * Initialize the admin menu; add main and sub items
	 */
	public function init_menu() {
		add_menu_page( __( C_TRAX_INTEGRATION_PROJECT ), __( C_TRAX_INTEGRATION_PROJECT ), C_TRAX_INTEGRATION_ADMIN_PERMISSION, C_TRAX_INTEGRATION_PREFIX, [ $this, 'view_dashboard' ], C_TRAX_INTEGRATION_ASSETS_URL . 'images/icons/admin-menu.png', 80 );
		add_submenu_page( C_TRAX_INTEGRATION_PREFIX, __( 'Dashboard' ), __( 'Dashboard' ), C_TRAX_INTEGRATION_ADMIN_PERMISSION, C_TRAX_INTEGRATION_PREFIX, [ $this, 'view_dashboard' ], 1 );
		SettingController::init_pages();
	}

	/**
	 * Register application styles
	 *
	 * @param $hook
	 */
	public function app_css( $hook ) {
		if ( strpos( $hook, C_TRAX_INTEGRATION_PREFIX ) === false ) {
			return;
		}

		wp_register_style( C_TRAX_INTEGRATION_PREFIX . '-font-awesome', 'https://use.fontawesome.com/b84d344cb5.css' );
		wp_register_style( C_TRAX_INTEGRATION_PREFIX . '-bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css' );
		wp_register_style( C_TRAX_INTEGRATION_PREFIX . '-select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css' );
		wp_register_style( C_TRAX_INTEGRATION_PREFIX . '-select2-bootstrap', 'https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.3.2/dist/select2-bootstrap4.min.css', [ C_TRAX_INTEGRATION_PREFIX . '-select2' ] );
		wp_register_style( C_TRAX_INTEGRATION_PREFIX . '-app', C_TRAX_INTEGRATION_ASSETS_URL . 'css/app.css', [ C_TRAX_INTEGRATION_PREFIX . '-font-awesome', C_TRAX_INTEGRATION_PREFIX . '-bootstrap', C_TRAX_INTEGRATION_PREFIX . '-select2', C_TRAX_INTEGRATION_PREFIX . '-select2-bootstrap' ],
			C_TRAX_INTEGRATION_VERSION );

		wp_enqueue_style( C_TRAX_INTEGRATION_PREFIX . '-app' );
	}

	/**
	 * Register javascript libraries
	 *
	 * @param $hook
	 */
	public function scripts( $hook ) {
		if ( strpos( $hook, C_TRAX_INTEGRATION_PREFIX ) === false ) {
			return;
		}

		// Font Awesome
		wp_register_script( C_TRAX_INTEGRATION_PREFIX . '-font-awesome-js', 'https://use.fontawesome.com/b84d344cb5.js' );
		// Bootstrap
		wp_register_script( C_TRAX_INTEGRATION_PREFIX . '-bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js', [ 'jquery' ] );
		// Select 2
		wp_register_script( C_TRAX_INTEGRATION_PREFIX . '-select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', [ 'jquery' ] );
		// Validation scripts
		wp_register_script( C_TRAX_INTEGRATION_PREFIX . '-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js', [ 'jquery' ] );
		wp_register_script( C_TRAX_INTEGRATION_PREFIX . '-validate-additional', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/additional-methods.min.js', [ 'jquery' ] );
		// Application main scripts
		wp_register_script( C_TRAX_INTEGRATION_PREFIX . '-app-js', C_TRAX_INTEGRATION_ASSETS_URL . 'js/app.js', [
			C_TRAX_INTEGRATION_PREFIX . '-font-awesome-js',
			C_TRAX_INTEGRATION_PREFIX . '-bootstrap-js',
			C_TRAX_INTEGRATION_PREFIX . '-select2-js',
			C_TRAX_INTEGRATION_PREFIX . '-validate',
			C_TRAX_INTEGRATION_PREFIX . '-validate-additional',
		], C_TRAX_INTEGRATION_VERSION );

		// Set the app
		wp_enqueue_script( C_TRAX_INTEGRATION_PREFIX . '-app-js' );

		// Var data
		wp_localize_script( C_TRAX_INTEGRATION_PREFIX . '-app-js', C_TRAX_INTEGRATION_ACTION, [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( C_TRAX_INTEGRATION_AJAX_POSTFIX ),
		] );

		SettingController::register_scripts();
	}

	/**
	 * Method to parse all of the AJAX requests for the plugin
	 * @return mixed|string|void
	 */
	public function parseAJAX() {
		if ( ! isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			exit( 'Direct access is not allowed, so stop it.' );
		}

		$result = '';

		// Try creating the new class and run the method
		try {
			if ( ! Input::has( 'module' ) ) {
				throw new \Exception( 'Invalid module set.', 0 );
			} elseif ( ! Input::has( 'method' ) ) {
				throw new \Exception( 'Invalid method set.', 1 );
			}

			$controller = 'C_Trax_Integration\Controllers\\' . Input::get( 'module' ) . 'Controller';
			$method     = Input::get( 'method' );
			$_raw       = Input::get( '_raw' );
			$data       = ( Input::has( 'data' ) ) ? Input::get( 'data' ) : Input::all();

			// Make sure the class and method actually exist
			if ( ! class_exists( $controller ) ) {
				throw new \Exception( 'Model "' . Input::get( 'module' ) . '" does not exist.', 2 );
			} elseif ( ! method_exists( $controller, $method ) ) {
				throw new \Exception( 'Method does not exist.', 3 );
			}

			$object = new $controller;
			$result = $object->$method( $data );

			// Put the response into an array for formatting
			if ( is_array( $result ) || is_object( $result ) ) {
				$message = null;
				$data    = $result;
			} else {
				$message = $result;
				$data    = null;
			}

			$response = [
				'status'  => 'success',
				'message' => $message,
				'data'    => $data
			];
			$error    = false;
		} catch( \Exception $e ) {
			if ( ! ( $e->getCode() >= 2000 && $e->getCode() < 3000 ) ) {
				$response = new Response();
				$message  = ( $e->getMessage() == "" ) ? 'There was an error processing AJAX method.' : null;
				$response->catch_message( $message, $e, true );
			}
			$response = [
				'status'  => ( $e->getCode() >= 2000 && $e->getCode() < 3000 ) ? 'success' : 'error', // Responses in the 2000s get a success status
				'code'    => $e->getCode(),
				'line'    => $e->getLine(),
				'message' => $e->getMessage()
			];
			$error    = true;
		}

		// Display the data
		if ( ! $error && isset( $_raw ) && $_raw ) {
			Output::json_display( $result );
		} else {
			Output::json_display( $response );
		}
	}

	/**
	 * Redirect the page via the Wordpress redirect method or javascript if the headers are already sent
	 * Stores the response in the session for later use
	 *
	 * @param  string  $link
	 */
	private function pageRedirect( $link = null ) {
		if ( $this->response->get_has_notice() ) {
			Session::set_var( Response::$sessionVar, $this->response );
		}

		$url = ( $link ) ? $link : Output::get_link();
		if ( headers_sent() ) {
			echo '<script>location.replace("' . $url . '"); </script>';
		} else {
			\wp_redirect( $url );
		}
		exit;
	}

	/**
	 * Get the view about the plugin page
	 * @return string
	 */
	public function view_dashboard() {
		$user = null;

		try {
			$user = $this->get_account();
			if ( $user->refresh_token ) {

				$response            = $this->get_refresh_token( $user->refresh_token );
				$user->auth_token    = $response->auth_token;
				$user->refresh_token = $response->refresh_token;
				$user->save();
			}
		} catch( \Exception $e ) {
			$this->delete_user();
			$this->add_admin_notice( 'refresh_token_error', 'error', 'Error refreshing ' . C_TRAX_INTEGRATION_PROJECT . ' account token. Please reconnect your account to resolve this issue.' );
		}

		return View::make( 'admin/dashboard.php', [ 'ctraxUser' => $user, 'squareAppId' => $this->get_square_app_id() ], false, true );
	}
}

// Initialize the main class
if ( class_exists( C_Trax_Integration::class ) ) {
	$GLOBALS['c_trax_integration'] = new C_Trax_Integration();
}

/*  Copyright 2020 C-Trax

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/