<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Rehub {

	public function __construct() {

		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_customer_details' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_customer_details' ] );

	}

	public function remove_customer_details() {

		remove_action( 'woocommerce_checkout_before_customer_details', 'rehub_woo_before_checkout' );
		remove_action( 'woocommerce_checkout_after_customer_details', 'rehub_woo_average_checkout' );

	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Rehub(), 'rehub' );
