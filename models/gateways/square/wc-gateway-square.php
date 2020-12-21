<?php

namespace C_Trax_Integration\Models\Gateways\Square;

use Automattic\Jetpack\Constants;
use C_Trax_Integration\Controllers\SquareController;
use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Square Class.
 */
class WC_Gateway_Square extends \WC_Payment_Gateway {

	/** @var API API base instance */
	private $api;

	/**
	 * As per documentation, as of now, SCA is enabled only for UK merchants, but to be implemented for Europe.
	 * As other currencies get supported, add them here.
	 *
	 * @since 2.2.0
	 *
	 * @var array $sca_supported_currencies Currencies for which SCA(3DS) is supported
	 */
	private $sca_supported_currencies = array( 'GBP' );

	/**
	 * Constructs the class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(
			SquareController::GATEWAY_ID,
			wc_square(),
			array(
				'method_title'       => __( 'Square', C_TRAX_INTEGRATION_PREFIX ),
				'method_description' => __( 'Allow customers to use Square to securely pay with their credit cards', C_TRAX_INTEGRATION_PREFIX ),
				'payment_type'       => 'credit-card',
				'supports'           => array(
					'products',
					'refunds',
					'products',
					'card_types',
					'tokenziation',
					'charge',
					'authorization',
					'subscriptions',
					'subscription_suspension',
					'subscription_cancellation',
					'subscription_reactivation',
					'subscription_amount_changes',
					'subscription_date_changes',
					'subscription_payment_method_change',
					'customer_decline_messages'
				),
			)
		);

		$this->view_transaction_url = 'https://squareup.com/dashboard/sales/transactions/%s';

		// log accept.js requests and responses
		add_action( 'wp_ajax_wc_' . C_TRAX_INTEGRATION_ACTION . '_log_js_data',        array( $this, 'log_js_data' ) );
		add_action( 'wp_ajax_nopriv_wc_' . C_TRAX_INTEGRATION_ACTION . '_log_js_data', array( $this, 'log_js_data' ) );

		// store the Square item variation ID to order items
		add_action( 'woocommerce_new_order_item', array( $this, 'store_new_order_item_square_meta' ), 10, 3 );

		// restore refunded Square inventory
		add_action( 'woocommerce_order_fully_refunded', array( $this, 'restore_refunded_inventory' ), 10, 2 );

		// AJAX Checkout validation handler.
		add_action( 'wc_ajax_' . C_TRAX_INTEGRATION_ACTION . '_checkout_handler', array( $this, 'wc_ajax_square_checkout_handler' ) );
	}


	/**
	 * Logs any data sent by the payment form JS via AJAX.
	 *
	 * @since 2.0.0
	 */
	public function log_js_data() {

		check_ajax_referer( 'wc_' . C_TRAX_INTEGRATION_ACTION . '_log_js_data', 'security' );

		$message = sprintf( "Square.js %1\$s:\n ", ! empty( $_REQUEST['type'] ) ? ucfirst( wc_clean( $_REQUEST['type'] ) ) : 'Request' );

		// add the data
		if ( ! empty( $_REQUEST['data'] ) ) {
			$message .= print_r( wc_clean( $_REQUEST['data'] ), true );
		}
// TODO Add Log
		//$this->get_plugin()->log( $message, C_TRAX_INTEGRATION_ACTION );
	}


	/**
	 * Stores the Square item variation ID to order items when added to orders.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 *
	 * @param int $item_id order item ID
	 * @param \WC_Order_Item $item order item object
	 * @param int $order_id order ID
	 */
	public function store_new_order_item_square_meta( $item_id, $item, $order_id ) {

		if ( ! $item instanceof \WC_Order_Item_Product ) {
			return;
		}

		$product = $item->get_product();

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		if ( ! Product::is_synced_with_square( $product ) ) {
			return;
		}

		if ( $square_id = $product->get_meta( Product::SQUARE_VARIATION_ID_META_KEY ) ) {
			$item->update_meta_data( Product::SQUARE_VARIATION_ID_META_KEY, $square_id );
		}

		$item->save_meta_data();
	}

	/**
	 * Overrides enqueue of the gateway-specific assets if present, including JS, CSS, and
	 * localized script params
	 *
	 * @since 2.1.7
	 */
	protected function enqueue_payment_form_assets() {
		// bail if *not* on add payment method page or checkout page.
		if ( ! ( is_add_payment_method_page() || is_checkout() ) ) {
			return;
		}

		parent::enqueue_payment_form_assets();
	}

	/**
	 * Enqueues the gateway JS.
	 *
	 * @since 2.0.0
	 */
	protected function enqueue_gateway_assets() {
		// bail if *not* on add payment method page or checkout page.
		if ( ! ( is_add_payment_method_page() || is_checkout() ) ) {
			return;
		}

		if ( $this->get_plugin()->get_settings_handler()->is_sandbox() ) {
			$url = 'https://js.squareupsandbox.com/v2/paymentform';
		} else {
			$url = 'https://js.squareup.com/v2/paymentform';
		}

		wp_enqueue_script( 'wc-' . $this->get_plugin()->get_id_dasherized() . '-payment-form', $url, array(), Plugin::VERSION );

		parent::enqueue_gateway_assets();
	}


	/**
	 * Validates the entered payment fields.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function validate_fields() {

		$is_valid = true;

		try {

			if ( $this->is_3d_secure_enabled() && ! Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-buyer-verification-token' ) ) {
				throw new Framework\SV_WC_Payment_Gateway_Exception( '3D Secure Verification Token is missing' );
			}

			if ( Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-payment-token' ) ) {
				return $is_valid;
			}

			if ( ! Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-payment-nonce' ) ) {
				throw new Framework\SV_WC_Payment_Gateway_Exception( 'Payment nonce is missing' );
			}
		} catch ( Framework\SV_WC_Payment_Gateway_Exception $exception ) {

			$is_valid = false;

			Framework\SV_WC_Helper::wc_add_notice( __( 'An error occurred, please try again or try an alternate form of payment.', C_TRAX_INTEGRATION_PREFIX ), 'error' );

			$this->add_debug_message( $exception->getMessage(), 'error' );
		}

		return $is_valid;
	}


	/**
	 * Gets the order object with payment information added.
	 *
	 * @since 2.0.0
	 *
	 * @param int|\WC_Order $order_id order ID or object
	 * @return \WC_Order
	 */
	public function get_order( $order_id ) {

		$order = parent::get_order( $order_id );

		if ( $this->is_3d_secure_enabled() ) {
			$order->payment->verification_token = Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-buyer-verification-token' );
		}

		if ( empty( $order->payment->token ) ) {

			$order->payment->nonce = Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-payment-nonce' );

			$order->payment->card_type      = Framework\SV_WC_Payment_Gateway_Helper::normalize_card_type( Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-card-type' ) );
			$order->payment->account_number = $order->payment->last_four = substr( Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-last-four' ), -4 );
			$order->payment->exp_month      = Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-exp-month' );
			$order->payment->exp_year       = Framework\SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-exp-year' );
		}

		$order->square_customer_id = $order->customer_id;
		$order->square_order_id    = $this->get_order_meta( $order, 'square_order_id' );
		$order->square_version     = $this->get_order_meta( $order, 'square_version' );

		// look up in the index for guest customers
		if ( ! $order->get_user_id() ) {

			$indexed_customers = Gateway\Customer_Helper::get_customers_by_email( $order->get_billing_email() );

			// only use an indexed customer ID if there was a single one returned, otherwise we can't know which to use
			if ( ! empty( $indexed_customers ) && count( $indexed_customers ) === 1 ) {
				$order->square_customer_id = $order->customer_id = $indexed_customers[0];
			}
		}

		// if no previous customer could be found, always create a new customer
		if ( empty( $order->square_customer_id ) ) {

			try {

				$response = $this->get_api()->create_customer( $order );

				$order->square_customer_id = $order->customer_id = $response->get_customer_id(); // set $customer_id since we know this customer can be associated with this user

				// store the guests customers in our index to avoid future duplicates
				if ( ! $order->get_user_id() ) {
					Gateway\Customer_Helper::add_customer( $order->square_customer_id, $order->get_billing_email() );
				}
			} catch ( \Exception $exception ) {

				// log the error, but continue with payment
				if ( $this->debug_log() ) {
					// TODO Add log
//					$this->get_plugin()->log( $exception->getMessage(), C_TRAX_INTEGRATION_ACTION );
				}
			}
		}

		return $order;
	}


	/**
	 * Do the transaction.
	 *
	 * @since 2.0.0
	 *
	 * @param \WC_Order $order
	 * @return bool
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	protected function do_transaction( $order ) {

		// if there is no associated Square order ID, create one
		if ( empty( $order->square_order_id ) ) {

			try {

				$location_id = $this->get_plugin()->get_settings_handler()->get_location_id();
				$response    = $this->get_api()->create_order( $location_id, $order );

				$order->square_order_id = $response->getId();

				// adjust order by difference between WooCommerce and Square order totals
				$wc_total     = Money_Utility::amount_to_cents( $order->get_total() );
				$square_total = $response->getTotalMoney()->getAmount();
				$delta_total  = $wc_total - $square_total;

				if ( abs( $delta_total ) > 0 ) {
					$response = $this->get_api()->adjust_order( $location_id, $order, $response->getVersion(), $delta_total );

					// since a downward adjustment causes (downward) tax recomputation, perform an additional (untaxed) upward adjustment if necessary
					$square_total = $response->getTotalMoney()->getAmount();
					$delta_total  = $wc_total - $square_total;

					if ( $delta_total > 0 ) {
						$response = $this->get_api()->adjust_order( $location_id, $order, $response->getVersion(), $delta_total );
					}
				}

				// reset the payment total to the total calculated by Square to prevent errors
				$order->payment_total = Framework\SV_WC_Helper::number_format( Money_Utility::cents_to_float( $response->getTotalMoney()->getAmount() ) );

			} catch ( \ErrorException $exception ) {

				// log the error, but continue with payment
				if ( $this->debug_log() ) {
					// TODO Add log
//					$this->get_plugin()->log( $exception->getMessage(), C_TRAX_INTEGRATION_ACTION );
				}
			}
		}

		return parent::do_transaction( $order );
	}


	/**
	 * Adds transaction data to the order.
	 *
	 * @since 2.0.0
	 *
	 * @param \WC_Order $order order object
	 * @param \WooCommerce\Square\Gateway\API\Responses\Charge $response API response object
	 */
	public function add_payment_gateway_transaction_data( $order, $response ) {

		$location_id = $response->get_location_id() ?: $this->get_plugin()->get_settings_handler()->get_location_id();

		if ( $location_id ) {
			$this->update_order_meta( $order, 'square_location_id', $location_id );
		}

		if ( $response->get_square_order_id() ) {
			$this->update_order_meta( $order, 'square_order_id', $response->get_square_order_id() );
		}

		// store the plugin version on the order
		$this->update_order_meta( $order, 'square_version', Plugin::VERSION );
	}


	/**
	 * Gets an order with capture data attached.
	 *
	 * @since 2.0.0
	 *
	 * @param int|\WC_Order $order order object
	 * @param null|float $amount amount to capture
	 * @return \WC_Order
	 */
	public function get_order_for_capture( $order, $amount = null ) {

		$order = parent::get_order_for_capture( $order, $amount );

		$order->capture->location_id = $this->get_order_meta( $order, 'square_location_id' );
		$order->square_version       = $this->get_order_meta( $order, 'square_version' );

		return $order;
	}


	/**
	 * Gets an order with refund data attached.
	 *
	 * @since 2.0.0
	 *
	 * @param int|\WC_Order $order order object
	 * @param float $amount amount to refund
	 * @param string $reason response for the refund
	 *
	 * @return \WC_Order|\WP_Error
	 */
	protected function get_order_for_refund( $order, $amount, $reason ) {

		$order                 = parent::get_order_for_refund( $order, $amount, $reason );
		$order->square_version = $this->get_order_meta( $order, 'square_version' );

		if ( $transaction_date = $this->get_order_meta( $order, 'trans_date' ) ) {
			// refunds with the Refunds API can be made up to 1 year after payment and up to 120 days with the Transactions API
			$max_refund_time = version_compare( $order->square_version, '2.2', '>=' ) ? '+1 year' : '+120 days';

			// throw an error if the payment cannot be refunded
			if ( current_time( 'timestamp' ) >= strtotime( $max_refund_time, strtotime( $transaction_date ) ) ) {
				return new \WP_Error( 'wc_square_refund_age_exceeded', sprintf( __( 'Refunds must be made within %s of the original payment date.', C_TRAX_INTEGRATION_PREFIX ), '+1 year' === $max_refund_time ? 'a year' : '120 days' ) );
			}
		}

		$order->refund->location_id = $this->get_order_meta( $order, 'square_location_id' );
		$order->refund->tender_id   = $this->get_order_meta( $order, 'authorization_code' );

		if ( ! $order->refund->tender_id ) {

			try {
				$response = version_compare( $order->square_version, '2.2', '>=' ) ? $this->get_api()->get_payment( $order->refund->trans_id ) : $this->get_api()->get_transaction( $order->refund->trans_id, $order->refund->location_id );

				if ( ! $response->get_authorization_code() ) {
					throw new Framework\SV_WC_Plugin_Exception( 'Tender missing' );
				}

				$this->update_order_meta( $order, 'authorization_code', $response->get_authorization_code() );
				$this->update_order_meta( $order, 'square_location_id', $response->get_location_id() );

				$order->refund->location_id = $response->get_location_id();
				$order->refund->tender_id   = $response->get_authorization_code();

			} catch ( Framework\SV_WC_Plugin_Exception $exception ) {

				return new \WP_Error( 'wc_square_refund_tender_missing', __( 'Could not find original transaction tender. Please refund this transaction from your Square dashboard.', C_TRAX_INTEGRATION_PREFIX ) );
			}
		}

		return $order;
	}


	/**
	 * Restores refunded Square inventory.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 *
	 * @param int $order_id order ID
	 * @param int $refund_id refund ID
	 */
	public function restore_refunded_inventory( $order_id, $refund_id ) {

		// no handling if inventory sync is disabled
		if ( ! $this->get_plugin()->get_settings_handler()->is_inventory_sync_enabled() ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		// check that the order was paid using our gateway
		if ( $order->get_payment_method() !== C_TRAX_INTEGRATION_ACTION ) {
			return;
		}

		$refund = wc_get_order( $refund_id );

		if ( ! $refund instanceof \WC_Order_Refund ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {

			if ( ! $item instanceof \WC_Order_Item_Product ) {
				continue;
			}

			// if this item has an associated Square ID, send a stock adjustment
			if ( $square_id = $item->get_meta( Product::SQUARE_VARIATION_ID_META_KEY ) ) {

				try {

					$this->get_plugin()->get_api()->add_inventory_from_refund( $square_id, $item->get_quantity() );

				} catch ( Framework\SV_WC_Plugin_Exception $exception ) {

					$this->get_plugin()->log( 'Could not send refund inventory adjustment. ' . $exception->getMessage() );
				}
			}
		}
	}


	/**
	 * Gets a mock order for adding a new payment method.
	 *
	 * @since 2.0.0
	 *
	 * @return \WC_Order
	 */
	protected function get_order_for_add_payment_method() {

		$order = parent::get_order_for_add_payment_method();

		// if the customer doesn't have a postcode yet, use the value returned by Square JS
		if ( ! $order->get_billing_postcode() && $postcode = Framework\SV_WC_Helper::get_post( 'wc-square-credit-card-payment-postcode' ) ) {
			$order->set_billing_postcode( $postcode );
		}

		return $order;
	}


	/**
	 * Builds the payment tokens handler instance.
	 *
	 * @since 2.0.0
	 *
	 * @return Card_Handler
	 */
	public function build_payment_tokens_handler() {

		return new Card_Handler( $this );
	}


	/** Admin methods *************************************************************************************************/


	/**
	 * Adds the tokenization form fields to the gateway settings.
	 *
	 * Overridden to change the setting name to "Customer Profiles."
	 *
	 * @since 2.0.0
	 *
	 * @param array $form_fields existing fields
	 * @return array
	 */
	protected function add_tokenization_form_fields( $form_fields ) {

		$form_fields = parent::add_tokenization_form_fields( $form_fields );

		if ( ! empty( $form_fields['tokenization'] ) ) {
			$form_fields['tokenization']['title'] = __( 'Customer Profiles', C_TRAX_INTEGRATION_PREFIX );
		}

		return $form_fields;
	}


	/**
	 * Clear the CSC field settings, as CSC is always required by Square.
	 *
	 * @since 2.0.0
	 *
	 * @param array $form_fields
	 * @return array
	 */
	protected function add_csc_form_fields( $form_fields ) {

		return $form_fields;
	}


	/**
	 * Adds the Card Types setting field.
	 *
	 * This removes Diners & Discovers from the defaults as they are not supported in the UK or Japan.
	 *
	 * @since 2.0.0
	 *
	 * @param array $form_fields
	 * @return array
	 */
	protected function add_card_types_form_fields( $form_fields ) {

		$form_fields = parent::add_card_types_form_fields( $form_fields );

		if ( isset( $form_fields['card_types']['default'] ) ) {

			foreach ( $form_fields['card_types']['default'] as $key => $type ) {

				if ( in_array( $type, array( 'DISC', 'DINERS' ), true ) ) {
					unset( $form_fields['card_types']['default'][ $key ] );
				}
			}
		}

		return $form_fields;
	}


	/** Conditional methods *******************************************************************************************/


	/**
	 * Determines if the gateway is available.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_available() {

		return parent::is_available() && $this->get_plugin()->get_settings_handler()->is_connected() && $this->get_plugin()->get_settings_handler()->get_location_id();
	}


	/**
	 * Determines whether the CSC field is enabled.
	 *
	 * This is always required by the Square payment form JS.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function csc_enabled() {

		return true;
	}


	/**
	 * Determines whether new payment customers/tokens should be created before processing a payment.
	 *
	 * Square requires we create a new customer & customer card before referencing that customer in a transaction.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function tokenize_before_sale() {

		return true;
	}


	/**
	 * Determines if 3d secure is enabled.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_3d_secure_enabled() {

		/**
		 * Filters whether or not 3d Secure should be enabled.
		 *
		 * @since 2.1.0
		 *
		 * @param bool $enabled
		 * @param Gateway $gateway_instance
		 */

		$base_currency = get_woocommerce_currency();

		$sca_enabled_currencies = in_array( $base_currency, $this->sca_supported_currencies, true );
		return apply_filters( 'wc_square_is_3d_secure_enabled', $sca_enabled_currencies, $this );
	}


	/** Getter methods ************************************************************************************************/


	/**
	 * Gets order meta.
	 *
	 * Overridden to handle any missing transaction ID meta from v1.
	 *
	 * @since 2.0.0
	 *
	 * @param \WC_Order|int $order order object or ID
	 * @param string $key meta key
	 * @return mixed
	 */
	public function get_order_meta( $order, $key ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		// migrate any missing transaction IDs
		if ( $order && 'trans_id' === $key && ! parent::get_order_meta( $order, $key ) && $order->get_transaction_id() ) {
			$this->update_order_meta( $order, 'trans_id', $order->get_transaction_id() );
		}

		return parent::get_order_meta( $order, $key );
	}


	/**
	 * Gets the authorization -> capture time window.
	 *
	 * Square limits captures to 6 days.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_authorization_time_window() {

		return 144;
	}


	/**
	 * Gets the payment form handler instance.
	 *
	 * @since 2.0.0
	 *
	 * @return Payment_Form
	 */
	public function get_payment_form_instance() {

		return new Payment_Form( $this );
	}


	/**
	 * Gets the API instance.
	 *
	 * @since 2.0.0
	 *
	 * @return Gateway\API
	 */
	public function get_api() {

		if ( ! $this->api ) {
			$settings  = $this->get_plugin()->get_settings_handler();
			$this->api = new Gateway\API( $settings->get_access_token(), $settings->get_location_id(), $settings->is_sandbox() );
		}

		return $this->api;
	}


	/**
	 * Gets the gateway settings fields.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_method_form_fields() {

		return array();
	}


	/**
	 * Gets a user's stored customer ID.
	 *
	 * Overridden to avoid auto-creating customer IDs, as Square generates them.
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id user ID
	 * @param array $args arguments
	 * @return string
	 */
	public function get_customer_id( $user_id, $args = array() ) {

		// Square generates customer IDs
		$args['autocreate'] = false;

		return parent::get_customer_id( $user_id, $args );
	}


	/**
	 * Gets a guest's customer ID.
	 *
	 * @since 2.0.0
	 *
	 * @param \WC_Order $order order object
	 * @return string|bool
	 */
	public function get_guest_customer_id( \WC_Order $order ) {

		// is there a customer id already tied to this order?
		$customer_id = $this->get_order_meta( $order, 'customer_id' );

		if ( $customer_id ) {
			return $customer_id;
		}

		return false;
	}


	/**
	 * Gets the configured environment ID.
	 *
	 * Square doesn't really support a sandbox, so we don't show a setting for this.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_environment() {

		return self::ENVIRONMENT_PRODUCTION;
	}

	/**
	 * Gets the configured application ID.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_application_id() {

		$square_application_id = 'sq0idp-wGVapF8sNt9PLrdj5znuKA';

		if ( $this->get_plugin()->get_settings_handler()->is_sandbox() ) {
			$square_application_id = $this->get_plugin()->get_settings_handler()->get_option( 'sandbox_application_id' );
		}

		/**
		 * Filters the configured application ID.
		 *
		 * @since 2.0.0
		 *
		 * @param string $application_id application ID
		 */
		return apply_filters( 'wc_square_application_id', $square_application_id );
	}

	/**
	 * AJAX WooCommerce checkout validation handler
	 *
	 * Tap into  woocommerce_after_checkout_validation hook
	 * and return WooCommerce checkout validation errors
	 *
	 * @since 2.2
	 */
	public function wc_ajax_square_checkout_handler() {
		// Nonce verfication.
		if ( ! check_ajax_referer( 'wc_' . C_TRAX_INTEGRATION_ACTION . '_checkout_validate', 'wc_' . C_TRAX_INTEGRATION_ACTION . '_checkout_validate_nonce', false ) ) {
			return wp_send_json_error( __( ' An error occurred, please try again or try an alternate form of payment.', C_TRAX_INTEGRATION_PREFIX ) );
		}

		// Nonce successfully verified. Proceed with validation.
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'wc_ajax_square_checkout_validate' ), 10, 2 );
		WC()->checkout->process_checkout();
	}

	/**
	 * Validate WooCommerce checkout data on Square JS AJAX call
	 *
	 * Returns validation errors (or success) as JSON and exits to prevent checkout
	 *
	 * @since 2.2
	 *
	 * @param array    $data WooCommerce checkout POST data.
	 * @param WP_Error $errors WooCommerce checkout errors.
	 */
	public function wc_ajax_square_checkout_validate( $data, $errors = null ) {
		$error_messages = null;
		if ( ! is_null( $errors ) ) {
			$error_messages = $errors->get_error_messages();
		}

		// Clear all existing notices.
		wc_clear_notices();

		if ( empty( $error_messages ) ) {
			wp_send_json_success( 'validation_successful' );
		} else {
			wp_send_json_error( array( 'messages' => $error_messages ) );
		}
		exit;
	}

	/**
	 * Returns the $order object with a unique transaction ref member added
	 *
	 * @since 2.2.1
	 * @param WC_Order $order the order object
	 * @return WC_Order order object with member named unique_transaction_ref
	 */
	protected function get_order_with_unique_transaction_ref( $order ) {
		$order_id = $order->get_id();

		// generate a unique retry count
		if ( is_numeric( $this->get_order_meta( $order_id, 'retry_count' ) ) ) {
			$retry_count = $this->get_order_meta( $order_id, 'retry_count' );
			$retry_count++;
		} else {
			$retry_count = 0;
		}

		// keep track of the retry count
		$this->update_order_meta( $order, 'retry_count', $retry_count );

		$order->unique_transaction_ref = $order_id . ( $retry_count >= 0 ? '_' . $retry_count : '' );
		return $order;
	}
}