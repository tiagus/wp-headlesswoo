<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Active_WooChimp {
	public function __construct() {

		/* checkout page */
		add_action( 'wfacp_get_fragments', [ $this, 'actions' ] );
		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'actions' ] );

		add_action( 'wfacp_checkout_page_found', [ $this, 'hooks_actions' ] );

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'hooks_actions' ] );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );

	}

	public function actions() {
		if ( class_exists( 'WooChimp' ) && isset( $GLOBALS['WooChimp'] ) ) {
			$woochimp = $GLOBALS['WooChimp'];
			remove_action( 'woocommerce_checkout_before_customer_details', [ $woochimp, 'add_permission_question' ] );
			remove_action( 'woocommerce_checkout_after_customer_details', [ $woochimp, 'add_permission_question' ] );
			remove_action( 'woocommerce_review_order_before_submit', [ $woochimp, 'add_permission_question' ] );
			remove_action( 'woocommerce_review_order_after_submit', [ $woochimp, 'add_permission_question' ] );
			remove_action( 'woocommerce_review_order_before_order_total', [ $woochimp, 'add_permission_question' ] );
			remove_action( 'woocommerce_checkout_billing', [ $woochimp, 'add_permission_question' ] );
			remove_action( 'woocommerce_checkout_shipping', [ $woochimp, 'add_permission_question' ] );
			remove_action( 'woocommerce_after_checkout_billing_form', [ $woochimp, 'add_permission_question' ] );
			add_action( 'woocommerce_review_order_before_submit', [ $woochimp, 'add_permission_question' ] );
		}

	}


	public function hooks_actions() {
		$this->ibericode();

		$this->SSWCMC();
	}

	public function ibericode() {
		global $mc4wp;
		if ( ! is_null( $mc4wp ) && isset( $mc4wp['integrations'] ) && ( $mc4wp['integrations'] instanceof MC4WP_Integration_Manager ) ) {

			$integrations = $mc4wp['integrations']->get_enabled_integrations();

			if ( isset( $integrations['woocommerce'] ) ) {

				$wcommerce = $integrations['woocommerce'];
				if ( $wcommerce instanceof MC4WP_Integration_Fixture ) {
					$instance = $wcommerce->instance;
					if ( $instance instanceof MC4WP_WooCommerce_Integration ) {
						remove_action( $instance->options['position'], [ $instance, 'output_checkbox' ], 20 );
						add_action( 'wfacp_after_billing_email_field', [ $instance, 'output_checkbox' ], 20 );
					}
				}
			}
		}
	}

	public function SSWCMC() {
		if ( function_exists( 'SSWCMC' ) && class_exists( 'SS_WC_MailChimp_Handler' ) ) {
			$instance = SS_WC_MailChimp_Handler::get_instance();

			$opt_in_checkbox_display_location = $instance->sswcmc->opt_in_checkbox_display_location();

			// Maybe add an "opt-in" field to the checkout
			$opt_in_checkbox_display_location = ! empty( $opt_in_checkbox_display_location ) ? $opt_in_checkbox_display_location : 'woocommerce_review_order_before_submit';

			// Old opt-in checkbox display locations
			$old_opt_in_checkbox_display_locations = array(
				'billing' => 'woocommerce_after_checkout_billing_form',
				'order'   => 'woocommerce_review_order_before_submit',
			);
			// Map old billing/order checkbox display locations to new format
			if ( array_key_exists( $opt_in_checkbox_display_location, $old_opt_in_checkbox_display_locations ) ) {
				$opt_in_checkbox_display_location = $old_opt_in_checkbox_display_locations[ $opt_in_checkbox_display_location ];
			}

			remove_action( $opt_in_checkbox_display_location, array( $instance, 'maybe_add_checkout_fields' ) );
			add_action( 'wfacp_after_billing_email_field', array( $instance, 'maybe_add_checkout_fields' ) );
		}
	}

	public function internal_css( $selected_template_slug ) {

		$array_class = [
			'layout_1' => 15,
			'layout_2' => 15,
			'layout_4' => 15,
			'layout_9' => 12,
		];


		if ( (isset( $array_class[ $selected_template_slug ] )) ) {
			?>
            <style>
                body .wfacp_main_form .wfacp-form-control-wrapper.mailchimp-newsletter {
                    margin-left: -<?php echo $array_class[ $selected_template_slug ]; ?>px;
                    margin-right: -<?php echo $array_class[ $selected_template_slug ]; ?>px;
                }

            </style>

			<?php
		}
	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_WooChimp(), 'woochimp' );
