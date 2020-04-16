<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( $_SERVER['REMOTE_ADDR'] != '49.207.90.172' ) {
	return;
}

class WFACP_Compatibility_With_url_coupons_Sky_Verge {
	public function __construct() {



		add_filter( 'wc_url_coupons_url_matches_coupon', [ $this, 'disable_coupon_apply' ] );

	}

	public function disable_coupon_apply( $url_match ) {

		add_action( 'wp', [ $this, 're_apply_coupon' ], 10 );

		return false;
	}


	public function re_apply_coupon() {
		remove_filter( 'wc_url_coupons_url_matches_coupon', [ $this, 'disable_coupon_apply' ] );
		if ( function_exists( 'wc_url_coupons' ) && ! is_null( wc_url_coupons()->get_frontend_instance() ) ) {
			wc_url_coupons()->get_frontend_instance()->maybe_apply_coupon();
		}
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_url_coupons_Sky_Verge(), 'url_coupon_sky_verge' );
