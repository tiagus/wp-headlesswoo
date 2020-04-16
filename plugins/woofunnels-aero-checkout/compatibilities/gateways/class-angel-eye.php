<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Angel_Eye {
	public function __construct() {
		add_filter( 'wfacp_skip_add_to_cart', [ $this, 'check_angel_eye_checkout_enable' ], 10, 2 );
		add_filter( 'wfacp_form_template', [ $this, 'replace_form_template' ] );
	}

	/**
	 * @param $status  bool
	 * @param $instance WFACP_public
	 */
	public function check_angel_eye_checkout_enable( $status, $instance ) {
		if ( ! is_admin() ) {

			$paypal_express_checkout = WC()->session->get( 'paypal_express_checkout' );
			if ( isset( $paypal_express_checkout ) ) {
				$instance->is_checkout_override = true;
				$status                         = true;
			}
		}

		return $status;
	}


	public function wfacp_skip_product_switching( $status ) {
		$paypal_express_checkout = WC()->session->get( 'paypal_express_checkout' );

		if ( isset( $paypal_express_checkout ) ) {
			$status = true;
		}

		return $status;
	}


	public function replace_form_template( $template ) {
		$paypal_express_checkout = WC()->session->get( 'paypal_express_checkout' );
		if ( isset( $paypal_express_checkout ) && is_array( $paypal_express_checkout ) ) {
			WFACP_Core()->public->paypal_billing_address  = true;
			WFACP_Core()->public->paypal_shipping_address = true;
			WFACP_Core()->public->is_paypal_express_active_session = true;
			WFACP_Core()->public->shipping_details        = $paypal_express_checkout['shipping_details'];
			WFACP_Core()->public->billing_details         = $paypal_express_checkout['shipping_details'];
			$template                                     = WFACP_TEMPLATE_COMMON . '/form-express-checkout.php';
		}

		return $template;
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Angel_Eye(), 'angel_eye' );
