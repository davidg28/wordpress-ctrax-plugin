<?php

namespace C_Trax_Integration\Controllers;

use C_Trax_Integration\Models\Gateways\Square\Settings;
use C_Trax_Integration\Models\Gateways\Square\WC_Gateway_Square;
use C_Trax_Integration\Traits\Options;

class SquareController extends Controller {

	use Options;

	private $settings;

	const VERSION = C_TRAX_INTEGRATION_VERSION;

	/** plugin ID */
	const PLUGIN_ID = C_TRAX_INTEGRATION_ACTION . '_square';

	/** string gateway ID */
	const GATEWAY_ID = C_TRAX_INTEGRATION_ACTION . '_square_credit_card';

	/**
	 * Init actions for the square gateway
	 */
	public static function init_actions() {
		add_action( 'plugins_loaded', [ self::instance(), 'init_plugin' ] );
	}

	/**
	 * Initialize the filters
	 */
	public static function init_filters() {
		$instance = self::instance();
		add_filter( 'woocommerce_payment_gateways', [ $instance, 'load_gateways' ] );
	}

	/**
	 * Adds any gateways supported by this plugin to the list of available payment gateways.
	 *
	 * @param  array  $gateways
	 *
	 * @return array $gateways
	 */
	public function load_gateways( array $gateways ) {

		return array_merge( $gateways, [ WC_Gateway_Square::class ] );
	}

	/**
	 * Init the plugin settings for the square gateway
	 */
	public function init_plugin() {

		$this->settings = new Settings();
		//		$this->products_handler = new Products( $this );

		//		if ( ! $this->admin_handler && is_admin() ) {
		//			$this->admin_handler = new Admin( $this );
		//		}

		do_action( C_TRAX_INTEGRATION_ACTION . '_wc_square_initialized' );
	}
}

?>