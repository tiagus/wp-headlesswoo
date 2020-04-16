<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Active_Woo {
	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_checkout_page_found', [ $this, 'dequeue_js' ] );

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'dequeue_js' ] );

		add_action( 'wfacp_after_checkout_page_found', function () {
			if ( function_exists( 'G3D_APP' ) && G3D_APP() instanceof G3D_APP ) {
				remove_filter( 'woocommerce_cart_item_thumbnail', array( G3D_APP(), 'cart_item_uses_large_image_link' ), 10 );
			}
		}, 999 );

	}

	public function dequeue_js() {
		if ( class_exists( 'WC_Active_Woo' ) ) {

			global $activewoo;
			remove_action( 'woocommerce_before_checkout_form', array( $activewoo->recover_cart, 'print_subscribe_form' ) );
			add_action( 'woocommerce_before_checkout_form', function () {
				wp_enqueue_script( 'aw_rc_cart_js' );
				wp_enqueue_script( 'wfacp_active_woo', WFACP_PLUGIN_URL . '/assets/compatibility/js/activewoo.js', [ 'wfacp_checkout_js' ], '1.5.3', true );
			} );
		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_Woo(), 'activewoo' );
