<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Ocean {
	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_actions' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_actions' ] );
	}

	public function remove_actions() {
		if ( class_exists( 'OceanWP_WooCommerce_Config' ) ) {

			add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
			global $wp_filter;

			foreach ( $wp_filter['woocommerce_before_checkout_form']->callbacks as $key => $val ) {
				if ( 10 !== $key ) {
					continue;
				}
				foreach ( $val as $innerkey => $innerval ) {
					if ( isset( $innerval['function'] ) && is_array( $innerval['function'] ) ) {
						if ( is_a( $innerval['function']['0'], 'OceanWP_WooCommerce_Config' ) ) {
							$mk_customizer = $innerval['function']['0'];
							remove_action( 'woocommerce_before_checkout_form', array( $mk_customizer, 'checkout_timeline' ) );
							break;
						}
					}
				}
			}

			foreach ( $wp_filter['ocean_head_css']->callbacks as $key => $val ) {

				foreach ( $val as $innerkey => $innerval ) {
					if ( isset( $innerval['function'] ) && is_array( $innerval['function'] ) ) {
						if ( is_a( $innerval['function']['0'], 'OceanWP_WooCommerce_Customizer' ) ) {
							$mk_customizer = $innerval['function']['0'];
							remove_filter( 'ocean_head_css', array( $mk_customizer, 'head_css' ) );
							break;
						}
					}
				}
			}

			foreach ( $wp_filter['ocean_head_css']->callbacks as $key => $val ) {

				foreach ( $val as $innerkey => $innerval ) {
					if ( isset( $innerval['function'] ) && is_array( $innerval['function'] ) ) {
						if ( is_a( $innerval['function']['0'], 'OceanWP_General_Customizer' ) ) {
							$mk_customizer = $innerval['function']['0'];
							remove_filter( 'ocean_head_css', array( $mk_customizer, 'head_css' ) );
							break;
						}
					}
				}
			}
		}

	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Ocean(), 'ocean' );
