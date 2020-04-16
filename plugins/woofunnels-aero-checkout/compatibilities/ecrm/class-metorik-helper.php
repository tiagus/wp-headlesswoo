<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Metorik_Helper {
	/**
	 * @var Metorik_Custom;
	 */
	public $custom_instance = null;

	/**
	 * @var Metorik_Helper_Carts;
	 */
	public $cart_instance = null;

	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_metorki_hook' ] );
	}

	public function remove_metorki_hook() {
		if ( class_exists( 'Metorik_Helper_Carts' ) ) {
			$this->custom_instance = WFACP_Common::remove_actions( 'woocommerce_after_order_notes', 'Metorik_Custom', 'source_form_fields' );
			if ( $this->custom_instance instanceof Metorik_Custom ) {
				add_action( 'woocommerce_checkout_after_customer_details', [ $this->custom_instance, 'source_form_fields' ] );
			}
		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Metorik_Helper(), 'metorik-helper' );
