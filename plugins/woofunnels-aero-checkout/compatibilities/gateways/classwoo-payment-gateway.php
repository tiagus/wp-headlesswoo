<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Name: Braintree For WooCommerce
 * Class WFACP_Compatibility_With_Woo_Payment_Gateway
 */
class WFACP_Compatibility_With_Woo_Payment_Gateway {


	public function __construct() {
		add_action( 'wfacp_checkout_page_found', [ $this, 'add_body_clss' ], 999 );
	}

	public function add_body_clss() {

		if ( function_exists( 'requireBraintreeProDependencies' ) ) {
			add_filter( 'wfacp_body_class', function ( $class ) {
				$class[] = 'bfwc-body';

				return $class;
			} );
		}
	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Woo_Payment_Gateway(), 'woo-payment-gateway' );
