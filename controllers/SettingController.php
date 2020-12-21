<?php

namespace C_Trax_Integration\Controllers;

use C_Trax_Integration\Includes\Input;
use C_Trax_Integration\Includes\Output;
use C_Trax_Integration\Models\User;
use C_Trax_Integration\Traits\Options;
use C_Trax_Integration\Traits\Account;
use C_Trax_Integration\Traits\Square;
use C_Trax_Integration\Traits\WebService;
use C_Trax_Integration\Views\View;

define( 'C_TRAX_INTEGRATION_SLUG_SETTINGS', C_TRAX_INTEGRATION_PREFIX . '-settings' );
define( 'C_TRAX_INTEGRATION_SLUG_SETUP', C_TRAX_INTEGRATION_PREFIX . '-setup' );
define( 'C_TRAX_INTEGRATION_OPTION_SETUP_PROGRESS', C_TRAX_INTEGRATION_PREFIX . '-setup-progress' );

class SettingController extends Controller {

	use WebService, Options, Account, Square;

	/**
	 * @var \string[][] Setup process steps in order
	 */
	private $steps = [
		[
			'title' => 'Connect Account',
			'slug'  => 'connect-account',
			'icon'  => 'fa-user'
		],
		[
			'title' => 'Square Gateway Setup',
			'slug'  => 'square-gateway-setup',
			'icon'  => 'fa-square'
		],
		[
			'title' => 'Complete',
			'slug'  => 'complete',
			'icon'  => 'fa-thumbs-up'
		]
	];

	public function __construct() {

	}

	/**
	 * Initialize the settings pages
	 */
	public static function init_pages() {
		$instance = self::instance();
		add_submenu_page( C_TRAX_INTEGRATION_PREFIX, __( 'Settings' ), __( 'Settings' ), C_TRAX_INTEGRATION_ADMIN_PERMISSION, C_TRAX_INTEGRATION_SLUG_SETTINGS, [ $instance, 'view_settings' ] );
		add_submenu_page( null, __( 'Setup' ), __( 'Setup' ), C_TRAX_INTEGRATION_ADMIN_PERMISSION, C_TRAX_INTEGRATION_SLUG_SETUP, [ $instance, 'view_setup' ] );
	}

	/**
	 * Initialize actions
	 */
	public static function init_actions() {
		$instance = self::instance();
		add_action( C_TRAX_INTEGRATION_ACTION . '_setup_needed', [ $instance, 'setup_needed_notice' ] );
		add_action( 'admin_head', [ $instance, 'setup_ready_js' ] );

		// Ajax request methods
		add_action( 'wp_ajax_c_trax_connect_account', [ $instance, 'connect_account' ] );
	}

	/**
	 * Ready the setup method on page load
	 */
	public function setup_ready_js() {
		if ( Input::get( 'page' ) == C_TRAX_INTEGRATION_PREFIX . '-setup' ) {
			?>
			<script type="text/javascript">
				jQuery(function()
				       {
					       com.c_trax_integration.model.setup.ready();
				       });
			</script>
			<?php
		}
	}

	/**
	 * Register scripts
	 */
	public static function register_scripts() {
		wp_register_script( C_TRAX_INTEGRATION_PREFIX . '-setup',
			C_TRAX_INTEGRATION_ASSETS_URL . 'js/model/setup.js',
			[ C_TRAX_INTEGRATION_PREFIX . '-app-js' ],
			C_TRAX_INTEGRATION_VERSION
		);

		if ( Input::get( 'page' ) == C_TRAX_INTEGRATION_PREFIX . '-setup' ) {
			wp_enqueue_script( C_TRAX_INTEGRATION_PREFIX . '-setup' );
		}
	}

	/**
	 * View the admin settings page
	 * @return string
	 */
	public function view_settings() {
		// If token is not set or the setup isn't set to the last step
		if ( ! $this->get_option( C_TRAX_INTEGRATION_OPTION_TOKEN ) || ! $this->check_setup_progress() ) {
			wp_redirect( Output::menu_page_url( C_TRAX_INTEGRATION_SLUG_SETUP, false ) );
			exit;
		}

		return View::make( 'admin/settings.php' );
	}

	/**
	 * View the setup process; Pickup where ever the user left off, if applicable
	 * @return bool|string
	 */
	public function view_setup() {
		if ( Input::has( 'step' ) ) {
			$currentStep = Input::get( 'step' );
		} else {
			$currentStep = $this->get_current_step();
		}

		$this->save_option( C_TRAX_INTEGRATION_OPTION_SETUP_PROGRESS, $currentStep );

		return View::make( 'admin/setup/template.php', [ 'steps' => $this->steps, 'currentStep' => $this->get_step_data( $currentStep ), 'nextStep' => $this->get_next_step( true ), 'ctraxUser' => $this->get_account() ] );
	}

	/**
	 * Get the current setup step
	 * @return string
	 */
	public function get_current_step() {
		$step     = Input::get( 'step' );
		$progress = $this->get_option( C_TRAX_INTEGRATION_OPTION_SETUP_PROGRESS, 'setup' );

		if ( $step ) {
			$current = $step;
		} else {
			$current = $progress;
		}

		return $current;
	}

	/**
	 * Check to see if the setup process has finished the final step
	 * @return bool
	 */
	public function check_setup_progress() {
		$lastStep = end( $this->steps );
		$account  = $this->get_option( C_TRAX_INTEGRATION_OPTION_ACCOUNT );

		return ( $this->get_current_step() == $lastStep['slug'] && $account );
	}

	/**
	 * Display a notice if the setup process is still needed
	 */
	public function setup_needed_notice() {
		if ( ! $this->check_setup_progress() ) {
			return View::notice( 'The ' . C_TRAX_INTEGRATION_PROJECT . ' setup has yet to be completed. <a href="' . Output::menu_page_url( C_TRAX_INTEGRATION_SLUG_SETUP, false ) . '">Click here to begin the setup!</a>' );
		}

		return null;
	}

	/**
	 * Get the next step in the setup
	 *
	 * @param  bool  $full
	 *
	 * @return bool|string|string[]
	 */
	public function get_next_step( $full = false ) {
		$nextStep = false;

		if ( $this->get_current_step() == 'setup' ) {
			$firstStep = reset( $this->steps );
			$nextStep  = ( $full ) ? $firstStep : $firstStep['slug'];
		} elseif ( ! $this->check_setup_progress() ) {
			$_current = false;
			foreach ( $this->steps as $step ) {
				// The previous step was current, so set current to... current loop item
				if ( $_current ) {
					$nextStep = ( $full ) ? $step : $step['slug'];
					break;
				} elseif ( $step['slug'] == $this->get_current_step() ) {
					$_current = true;
				}
			}
		}

		return $nextStep;
	}

	/**
	 * Get the step data based in the passed slug
	 *
	 * @param $slug
	 *
	 * @return string[]|null
	 */
	public function get_step_data( $slug ) {
		$data = [
			'slug'  => 'setup',
			'title' => 'Setup',
		];

		foreach ( $this->steps as $step ) {
			if ( $step['slug'] == $slug ) {
				$data = $step;
				break;
			}
		}

		return $data;
	}

	/**
	 * Connect the user account based on the token set
	 */
	public function connect_account() {
		try {
			if ( check_ajax_referer( C_TRAX_INTEGRATION_AJAX_POSTFIX ) === false ) {
				throw new \Exception( 'The nonce has expired or could not be validated.' );
			}

			$instanceDomain = Input::get( 'instance_domain' );
			$username       = Input::get( 'username' );
			$password       = Input::get( 'password' );
			$token          = 'token_placeholder';

			// Validate the inputs
			if ( ! $instanceDomain || ! filter_var( $instanceDomain, FILTER_VALIDATE_URL ) ) {
				throw new \Exception( 'The C-Trax domain provided is not a proper URL.' );
			}
			if ( ! $username ) {
				throw new \Exception( 'The username provided was empty.' );
			}
			if ( ! $password ) {
				throw new \Exception( 'The password provided was empty.' );
			}

			// Save the instance domain for later use
			$this->save_option( C_TRAX_INTEGRATION_INSTANCE_DOMAIN, $instanceDomain );

			$account = $this->get_ctrax_account( $username, $password );
			if ( $account ) {
				$user = new User( [
					'instance_domain' => $instanceDomain,
					'username'        => $username,
					'auth_token'      => $account->auth_token,
					'refresh_token'   => $account->refresh_token
				] );
				$user->save();

				// Set the square app id option
				try {
					$this->set_square_app_id( $this->get_ctrax_square_api_id() );
				} catch(\Exception $e)
				{
					throw new \ErrorException('Error retrieving ' . C_TRAX_INTEGRATION_PROJECT . ' Square API ID: ' . $e->getMessage());
				}

				$this->save_option( C_TRAX_INTEGRATION_OPTION_SETUP_PROGRESS, $this->get_next_step() );

				$result = [ 'account' => $account, 'auth_token' => $user->auth_token, 'refresh_token' => $user->refresh_token ];
				Output::json( $result, 'Account has been connected! ' . $account->message );
			}

			throw new \Exception( 'Retrieving the account from C-Trax was unsuccessful.' );
		} catch( \Exception $e ) {
			// Remove everything since we have failed you
			delete_option(C_TRAX_INTEGRATION_INSTANCE_DOMAIN);
			$this->delete_user();

			Output::json( null, 'Could not connect account.', $e );
		}
	}
}

?>