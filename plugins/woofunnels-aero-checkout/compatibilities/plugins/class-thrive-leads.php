<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Thrive_Leads {

	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_checkout_page_found', [ $this, 'add_styling' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'add_styling' ] );
	}

	public function add_styling() {

		if ( function_exists( 'tve_leads_display_form_lightbox' ) ) {
			add_action( 'wp_head', function () {
				?>
                <style>


                    body.wfacp_main_wrapper.wfacp_cls_layout_9.tve-o-hidden.tve-l-open.tve-hide-overflow,
                    body.wfacp_main_wrapper.wfacp_cls_layout_1.tve-o-hidden.tve-l-open.tve-hide-overflow,
                    body.wfacp_main_wrapper.tve-o-hidden.tve-hide-overflow.tve-l-open:not(.bp-t) {
                        height: 100% !important;
                    }

                    body.wfacp_main_wrapper.wfacp_cls_layout_9.tve-o-hidden.tve-l-open.tve-hide-overflow .wfacp_footer_sec_for_script,
                    body.wfacp_main_wrapper.wfacp_cls_layout_1.tve-o-hidden.tve-l-open.tve-hide-overflow .wfacp_footer_sec_for_script,
                    body.wfacp_main_wrapper.tve-o-hidden.tve-l-open.tve-hide-overflow .wfacp_footer_sec_for_script {
                        display: block;
                    }

                    .ic_loader:after, .ic_loader:before {
                        display: none;
                    }

                </style>
				<?php
			} );
		}
	}

}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Thrive_Leads(), 'thrive-leads' );
