<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Active_OxygenBuilder {

	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_skip_add_to_cart', [ $this, 'wfacp_skip_add_to_cart' ] );

	}

	public function wfacp_skip_add_to_cart( $status ) {
		if ( defined( 'CT_VERSION' ) && class_exists( 'CT_API' ) ) {
			if ( isset( $_REQUEST['xlink'] ) || isset( $_REQUEST['nouniversal'] ) ) {
				return true;
			}
		}

		return $status;
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_OxygenBuilder(), 'oxygen_builder' );
