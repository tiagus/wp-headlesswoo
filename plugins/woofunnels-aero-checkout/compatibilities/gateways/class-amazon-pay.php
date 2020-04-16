<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Compatibility_With_Active_AmzPay {
	public $is_amazon_active = false;

	public function __construct() {

		add_filter( 'wfacp_skip_common_loading', function ( $status ) {
			if ( isset( $_REQUEST['wfacp_id'] ) && $_REQUEST['wfacp_id'] > 0 && isset( $_REQUEST['amazon_payments_advanced'] ) && wp_doing_ajax() ) {
				return true;
			}

			return $status;
		} );

		/* checkout page */
		//add_action( 'wfacp_checkout_page_found', [ $this, 'actions' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );

		//add_action( 'wfacp_checkout_before_order_review', [ $this, 're_add_payment_widget' ] );
	}

	public function amazon_internal_css( $selected_template_slug ) {

		if ( $selected_template_slug == 'layout_9' ) {
			?>

            <style>

                .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.wfacp_bred_active.wfacp_bred_visited.amazone_list_wrap:nth-last-child(2):before {
                    background: #000;
                }

                .wfacp_custom_breadcrumb .wfacp_steps_sec ul li.wfacp_bred_active.wfacp_bred_visited.amazone_list_wrap:before {
                    background: #fff;
                }
            </style>
			<?php
		}
	}


	public function actions() {
		if ( class_exists( 'WC_Amazon_Payments_Advanced' ) && class_exists( 'WC_Amazon_Payments_Advanced_API' ) ) {

			if ( $this->is_active_payment() ) {

				add_filter( 'wfacp_form_template', [ $this, 'replace_form_template' ] );
				add_filter( 'wfacp_layout_9_active_progress_bar', [ $this, 'active_progress_bar' ], 10, 2 );
				add_filter( 'wfacp_embed_active_progress_bar', [ $this, 'embedd_active_progress_bar' ], 10, 3 );
				add_filter( 'wfacp_checkout_fields', [ $this, 'add_custom_class_amazon_fileds' ], - 1, 2 );
				add_filter( 'wfacp_checkout_fields', array( $this, 'override_checkout_fields_in_amazone_sec' ) );
				add_action( 'wfacp_internal_css', [ $this, 'amazon_internal_css' ] );
				WFACP_Core()->public->is_amazon_express_active_session = true;
			}
		}
	}

	public function is_active_payment() {
		if ( ( '' !== WC_Amazon_Payments_Advanced_API::get_reference_id() || '' !== WC_Amazon_Payments_Advanced_API::get_access_token() ) && WFACP_Common::get_id() > 0 ) {
			return true;
		}

		return false;
	}

	public function add_custom_class_amazon_fileds( $template_fields, $fields ) {

		add_action( 'woocommerce_before_checkout_form', function () {
			wp_enqueue_script( 'wfacp_amazone_pay_js', WFACP_PLUGIN_URL . '/assets/compatibility/js/amazone-pay.js', [ 'wfacp_checkout_js' ] );
		} );
		$billing_details = [
			'billing' => [
				'billing_first_name',
				'billing_last_name',
				'billing_email',
			],
			'account' => [
				'account_password',
			],
		];

		foreach ( $billing_details as $section => $fields ) {
			if ( ! isset( $template_fields[ $section ] ) ) {
				continue;
			}
			foreach ( $fields as $key ) {

				if ( ! isset( $template_fields[ $section ][ $key ] ) ) {
					continue;
				}
				$template_fields[ $section ][ $key ]['class'][]       = 'wfacp-form-control-wrapper';
				$template_fields[ $section ][ $key ]['label_class'][] = 'wfacp-form-control-label';
				$template_fields[ $section ][ $key ]['input_class'][] = 'wfacp-form-control';
			}
		}

		return $template_fields;

	}

	public function override_checkout_fields_in_amazone_sec( $fields_data ) {
		if ( isset( $fields_data['account']['join_referral_program'] ) ) {
			$fields_data['account']['join_referral_program']['class'][] = 'wfacp-form-control-wrapper';
		}

		if ( isset( $fields_data['account']['termsandconditions'] ) ) {
			$fields_data['account']['termsandconditions']['class'][] = 'wfacp-form-control-wrapper';
		}
		if ( isset( $fields_data['account']['referral_code'] ) ) {
			$fields_data['account']['referral_code']['class'][]       = 'wfacp-form-control-wrapper';
			$fields_data['account']['referral_code']['label_class'][] = 'wfacp-form-control-label';
			$fields_data['account']['referral_code']['input_class'][] = 'wfacp-form-control';
		}

		return $fields_data;

	}

	public function active_progress_bar( $active, $step ) {

		if ( $step != '' && $step != null ) {
			$active = 'wfacp_bred_active wfacp_bred_visited ppec_express_checkout_m amazone_list_wrap';
		}

		return $active;
	}

	public function embedd_active_progress_bar( $active, $step_count, $num_of_steps ) {

		$active = '';
		if ( $step_count != '' && $step_count == $num_of_steps ) {

			$active = 'wfacp-active';
		}

		return $active;
	}

	public function replace_form_template( $template ) {
		$template = WFACP_TEMPLATE_COMMON . '/form-amazon-checkout.php';

		return $template;
	}

	public function re_add_payment_widget() {
		if ( function_exists( 'wc_apa' ) && wc_apa() instanceof WC_Amazon_Payments_Advanced ) {
			wc_apa()->checkout_message();

		}
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Active_AmzPay(), 'AmzPay' );
