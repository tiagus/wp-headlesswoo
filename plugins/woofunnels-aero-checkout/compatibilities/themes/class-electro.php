<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Electro {

	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_checkout_page_found', [ $this, 'remove_electro_hooks' ] );

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_electro_hooks' ] );
	}

	public function remove_electro_hooks() {

		remove_action( 'customize_controls_print_styles', 'x_customizer_preloader' );

	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Electro(), 'electro' );
