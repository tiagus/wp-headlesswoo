<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Shopkeeper {

	public function __construct() {

		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_inline_styling' ] );


	}

	public function remove_inline_styling() {
		if ( function_exists( 'shopkeeper_custom_styles' ) ) {

			remove_action( 'wp_head', 'shopkeeper_custom_styles', 99 );
		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Shopkeeper(), 'shopkeeper' );
