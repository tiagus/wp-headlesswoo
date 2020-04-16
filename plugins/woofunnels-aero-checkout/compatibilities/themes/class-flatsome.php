<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Theme_Flatsome {

	public function __construct() {

		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_customizer_fields' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_customizer_fields' ] );

	}

	public function remove_customizer_fields() {

		if ( function_exists( 'flatsome_checkout_scripts' ) ) {
			remove_action( 'wp_enqueue_scripts', 'flatsome_checkout_scripts', 100 );
		}
		if ( ! WFACP_Common::is_customizer() ) {
			return;
		}

	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Theme_Flatsome(), 'flatsome' );
