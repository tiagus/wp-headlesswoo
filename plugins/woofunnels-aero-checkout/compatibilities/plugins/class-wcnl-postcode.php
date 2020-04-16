<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Wcnl_Postcode {


	private $billing_fields_added = false;
	private $shipping_fields_added = false;
	private $billing_country_nl = false;
	private $shipping_country_nl = false;
	private $wcnl_postcode_field_keys = [];

	public function __construct() {


		add_filter( 'wfacp_form_section', [ $this, 'checkout_billing_sections' ], 8 );
		add_filter( 'wfacp_form_section', [ $this, 'checkout_shipping_sections' ], 12 );

		add_action( 'wfacp_before_process_checkout_template_loader', [ $this, 'validation_fields' ] );

		add_action( 'wfacp_after_checkout_page_found', [ $this, 'actions' ] );
		add_action( 'wfacp_internal_css', [ $this, 'internal_css' ] );


	}

	public function actions() {
		add_action( 'wp_footer', [ $this, 'add_js' ] );
	}


	public function validation_fields() {
		add_filter( 'wfacp_checkout_fields', [ $this, 'make_validation' ] );
	}

	public function make_validation( $template_fields ) {

		if ( ! class_exists( 'WPO\WC\Postcode_Checker\WC_NLPostcode_Fields' ) ) {
			return $template_fields;
		}

		$obj             = WFACP_Common::remove_actions( 'woocommerce_billing_fields', 'WPO\WC\Postcode_Checker\WC_NLPostcode_Fields', 'nl_billing_fields' );
		$billing_country = WC()->checkout()->get_value( 'billing_country' );

		$required = in_array( $billing_country, $obj->postcode_field_countries() ) ? true : false;
		if ( isset( $template_fields['billing'] ) ) {

			$form                                                 = 'billing';
			$template_fields['billing'][ $form . '_street_name' ] = [
				'label'       => __( 'Street name', 'wpo_wcnlpc' ),
				'placeholder' => __( 'Street name', 'wpo_wcnlpc' ),
				'class'       => apply_filters( 'nl_custom_address_field_class', array( 'form-row-first' ), $form, 'street_name' ),
				'required'    => $required,
			];


			$template_fields['billing'][ $form . '_house_number' ] = array(
				'label'             => __( 'Nr.', 'wpo_wcnlpc' ),
				'class'             => apply_filters( 'nl_custom_address_field_class', array( 'form-row-quart-first' ), $form, 'house_number' ),
				'required'          => $required, // Only required for NL
				'type'              => 'number',
				'custom_attributes' => array( 'pattern' => '[0-9]*' ),
			);
		}

		$shipping_country = WC()->checkout()->get_value( 'shipping_country' );
		$required         = in_array( $shipping_country, $obj->postcode_field_countries() ) ? true : false;
		if ( isset( $template_fields['shipping'] ) ) {

			$form                                                  = 'shipping';
			$template_fields['shipping'][ $form . '_street_name' ] = [
				'label'       => __( 'Street name', 'wpo_wcnlpc' ),
				'placeholder' => __( 'Street name', 'wpo_wcnlpc' ),
				'class'       => apply_filters( 'nl_custom_address_field_class', array( 'form-row-first' ), $form, 'street_name' ),
				'required'    => $required,
			];


			$template_fields['shipping'][ $form . '_house_number' ] = array(
				'label'             => __( 'Nr.', 'wpo_wcnlpc' ),
				'class'             => apply_filters( 'nl_custom_address_field_class', array( 'form-row-quart-first' ), $form, 'house_number' ),
				'required'          => $required, // Only required for NL
				'type'              => 'number',
				'custom_attributes' => array( 'pattern' => '[0-9]*' ),
			);
		}


		return $template_fields;
	}


	public function checkout_billing_sections( $sections ) {


		if ( $this->billing_fields_added ) {
			return $sections;
		}
		if ( count( $sections ) == 0 ) {
			return $sections;
		}
		if ( ! class_exists( 'WPO\WC\Postcode_Checker\WC_NLPostcode_Fields' ) ) {
			return $sections;
		}


		if ( isset( $sections['fields']['wfacp_end_divider_billing'] ) ) {
			try {

				$this->billing_fields_added = true;

				$end_address_found     = false;
				$end_address_closser   = $sections['fields']['wfacp_end_divider_billing'];
				$after_address_element = [];
				$is_hidedable          = false;

				$keysVal = [];
				foreach ( $sections['fields'] as $index => $field ) {
					if ( isset( $field['id'] ) && isset( $field['priority'] ) ) {
						$keysVal[ $field['id'] ] = $field['priority'];
					}

					if ( $end_address_found ) {
						$after_address_element[] = $field;
						unset( $sections['fields'][ $index ] );
					}
					if ( isset( $field['class'] ) && in_array( 'wfacp_billing_fields', $field['class'] ) ) {
						$is_hidedable = true;
					}
					if ( 'wfacp_end_divider_billing' === $index ) {
						unset( $sections['fields'][ $index ] );
						$end_address_found = true;
					}
				}

				if ( false == $end_address_found ) {
					return $sections;
				}


				$new_fields = array();
				$obj        = WFACP_Common::remove_actions( 'woocommerce_billing_fields', 'WPO\WC\Postcode_Checker\WC_NLPostcode_Fields', 'nl_billing_fields' );
				$country    = WC()->checkout()->get_value( 'billing_country' );
				// Set required to true if country is NL
				$required = in_array( $country, $obj->postcode_field_countries() ) ? true : false;

				if ( true == $required ) {
					$this->billing_country_nl = true;
				}
				$form = "billing";

				$base_priority = 50;
				if ( isset( $keysVal['billing_address_1'] ) ) {
					$base_priority = intval( $keysVal['billing_address_1'] );
				}

				$templateSlug = WFACP_Core()->customizer->get_template_instance()->get_template_slug();
				$class1       = 'wfacp-col-right-half';
				$class2       = 'wfacp-col-left-half';
				if ( strpos( $templateSlug, 'embed_forms_' ) !== false ) {
					$class1 = 'wfacp-col-full';
					$class2 = 'wfacp-col-full';
				}


				// Add Street name
				$new_fields[] = array(
					'label'       => __( 'Street name', 'wpo_wcnlpc' ),
					'placeholder' => __( 'Street name', 'wpo_wcnlpc' ),
					'cssready'    => [ "wfacp_postcode_checker $class1" ],
					'id'          => $form . '_street_name',
					'class'       => apply_filters( 'nl_custom_address_field_class', array( 'wfacp_postcode_checker form-row-first' ), $form, 'street_name' ),
					'required'    => $required, // Only required for NL
					'priority'    => $base_priority + 1,
				);

				// Add house number
				$new_fields[] = array(
					'label'             => __( 'Nr.', 'wpo_wcnlpc' ),
					'class'             => apply_filters( 'nl_custom_address_field_class', array( 'form-row-quart-first' ), $form, 'house_number' ),
					'required'          => $required, // Only required for NL
					'type'              => 'number',
					'id'                => $form . '_house_number',
					'cssready'          => [ "wfacp_postcode_checker $class1" ],
					'custom_attributes' => array( 'pattern' => '[0-9]*' ),
					'priority'          => $base_priority + 2,
				);

				// Add house number Suffix
				$new_fields[] = array(
					'label'    => __( 'Suffix', 'wpo_wcnlpc' ),
					'cssready' => [ "wfacp_postcode_checker $class2" ],
					'id'       => $form . '_house_number_suffix',
					'class'    => apply_filters( 'nl_custom_address_field_class', array( 'wfacp_postcode_checker form-row-quart' ), $form, 'house_number_suffix' ),
					'required' => false,
					'priority' => $base_priority + 3,
				);


				$this->wcnl_postcode_field_keys = array_merge( $this->wcnl_postcode_field_keys, $new_fields );


				if ( is_array( $new_fields ) && count( $new_fields ) > 0 ) {
					foreach ( $new_fields as $fkey => $fvalue ) {

						if ( $is_hidedable ) {
							$fvalue['class'][] = 'wfacp_billing_fields';
							$fvalue['class'][] = 'wfacp_billing_field_hide';
						}

						$sections['fields'][] = $fvalue;
					}
				}

				$sections['fields']['wfacp_end_divider_billing'] = $end_address_closser;
				if ( count( $after_address_element ) > 0 ) {

					$last_field_type = '';
					foreach ( $after_address_element as $element ) {

						if ( $element['type'] === 'wfacp_start_divider' ) {
							if ( false !== strpos( $element['id'], '_shipping' ) ) {
								$last_field_type            = 'shipping';
								$tid                        = 'wfacp_start_divider_shipping';
								$sections['fields'][ $tid ] = WFACP_Common::get_start_divider_field( 'shipping' );
							} elseif ( false !== strpos( $element['id'], '_billing' ) ) {
								$last_field_type            = 'billing';
								$tid                        = 'wfacp_start_divider_billing';
								$sections['fields'][ $tid ] = WFACP_Common::get_start_divider_field( 'billing' );
							}
						} elseif ( $element['type'] === 'wfacp_end_divider' ) {
							$tid                        = 'wfacp_end_divider_' . $last_field_type;
							$sections['fields'][ $tid ] = WFACP_Common::get_end_divider_field();
						} else {
							$sections['fields'][] = $element;
						}
					}

				}

			} catch ( Exception $e ) {

			}
		}


		return $sections;
	}

	public function checkout_shipping_sections( $sections ) {

		if ( $this->shipping_fields_added ) {

			return $sections;
		}
		if ( count( $sections ) == 0 ) {
			return $sections;
		}
		if ( ! class_exists( 'WPO\WC\Postcode_Checker\WC_NLPostcode_Fields' ) ) {
			return $sections;
		}

		$templateSlug = WFACP_Core()->customizer->get_template_instance()->get_template_slug();
		$class1       = 'wfacp-col-right-half';
		$class2       = 'wfacp-col-left-half';
		if ( strpos( $templateSlug, 'embed_forms_' ) !== false ) {
			$class1 = 'wfacp-col-full';
			$class2 = 'wfacp-col-full';
		}


		if ( isset( $sections['fields']['wfacp_end_divider_shipping'] ) ) {
			try {

				$this->shipping_fields_added = true;

				$end_address_found     = false;
				$end_address_closser   = $sections['fields']['wfacp_end_divider_shipping'];
				$after_address_element = [];
				$is_hidedable          = false;

				foreach ( $sections['fields'] as $index => $field ) {
					if ( $end_address_found ) {
						$after_address_element[] = $field;
						unset( $sections['fields'][ $index ] );
					}
					if ( isset( $field['class'] ) && in_array( 'wfacp_shipping_fields', $field['class'] ) ) {
						$is_hidedable = true;
					}
					if ( 'wfacp_end_divider_shipping' === $index ) {
						unset( $sections['fields'][ $index ] );
						$end_address_found = true;
					}
				}


				$new_fields = array();
				$obj        = WFACP_Common::remove_actions( 'woocommerce_shipping_fields', 'WPO\WC\Postcode_Checker\WC_NLPostcode_Fields', 'nl_shipping_fields' );


				$shipping_country = WC()->checkout()->get_value( 'shipping_country' );
				// Set required to true if country is NL
				$required = in_array( $shipping_country, $obj->postcode_field_countries() ) ? true : false;

				if ( true == $required ) {
					$this->shipping_country_nl = true;
				}
				$form = "shipping";
				// Add Street name
				$new_fields[] = array(
					'label'       => __( 'Street name', 'wpo_wcnlpc' ),
					'placeholder' => __( 'Street name', 'wpo_wcnlpc' ),
					'cssready'    => [ "wfacp_postcode_checker $class1" ],
					'id'          => $form . '_street_name',
					'class'       => apply_filters( 'nl_custom_address_field_class', array( 'form-row-first' ), $form, 'street_name' ),
					'required'    => $required, // Only required for NL
				);

				// Add house number
				$new_fields[] = array(
					'label'             => __( 'Nr.', 'wpo_wcnlpc' ),
					'class'             => apply_filters( 'nl_custom_address_field_class', array( 'form-row-quart-first' ), $form, 'house_number' ),
					'required'          => $required, // Only required for NL
					'type'              => 'number',
					'id'                => $form . '_house_number',
					'cssready'          => [ "wfacp_postcode_checker $class1" ],
					'custom_attributes' => array( 'pattern' => '[0-9]*' ),
				);

				// Add house number Suffix
				$new_fields[] = array(
					'label'    => __( 'Suffix', 'wpo_wcnlpc' ),
					'cssready' => [ "wfacp_postcode_checker $class2" ],
					'id'       => $form . '_house_number_suffix',
					'class'    => apply_filters( 'nl_custom_address_field_class', array( 'wfacp_postcode_checker form-row-quart' ), $form, 'house_number_suffix' ),
					'required' => false,
				);


				$this->wcnl_postcode_field_keys[] = $new_fields;


				if ( is_array( $new_fields ) && count( $new_fields ) > 0 ) {
					foreach ( $new_fields as $fkey => $fvalue ) {


						if ( $is_hidedable ) {
							$fvalue['class'][] = 'wfacp_shipping_fields';
							$fvalue['class'][] = 'wfacp_shipping_field_hide';
						}

						$sections['fields'][] = $fvalue;
					}
				}

				$sections['fields']['wfacp_end_divider_shipping'] = $end_address_closser;

				if ( count( $after_address_element ) > 0 ) {
					$last_field_type = '';
					foreach ( $after_address_element as $element ) {
						if ( isset( $element['type'] ) && $element['type'] === 'wfacp_start_divider' ) {
							if ( false !== strpos( $element['id'], '_shipping' ) ) {
								$last_field_type            = 'shipping';
								$tid                        = 'wfacp_start_divider_shipping';
								$sections['fields'][ $tid ] = WFACP_Common::get_start_divider_field( 'shipping' );
							} elseif ( false !== strpos( $element['id'], '_billing' ) ) {
								$last_field_type            = 'billing';
								$tid                        = 'wfacp_start_divider_billing';
								$sections['fields'][ $tid ] = WFACP_Common::get_start_divider_field( 'billing' );
							}
						} elseif ( isset( $element['type'] ) && $element['type'] === 'wfacp_end_divider' ) {
							$tid                        = 'wfacp_end_divider_' . $last_field_type;
							$sections['fields'][ $tid ] = WFACP_Common::get_end_divider_field();
						} else {
							$sections['fields'][] = $element;
						}
					}
				}
			} catch ( Exception $e ) {

			}
		}

		return $sections;
	}

	public function internal_css( $selected_template_slug ) {

		if ( ! class_exists( 'WPO\WC\Postcode_Checker\WC_NLPostcode_Fields' ) ) {
			return '';
		}


		$array_class = [
			'layout_1' => 15,
			'layout_2' => 15,
			'layout_4' => 15,
			'layout_9' => 12,
		];

		if ( is_array( $array_class ) && isset( $array_class[ $selected_template_slug ] ) ) {

			?>
            <style>


                <?php
				if(isset($array_class[ $selected_template_slug ])){


				?>
                body .wfacp_main_form p.wcnlpc-manual {
                    padding: 0 <?php echo $array_class[ $selected_template_slug ]; ?>px;
                }

                .wfacp_main_form .woocommerce-page form .form-row-quart-first, .woocommerce form .form-row-quart-first {
                    margin-right: 0 !important;
                }

                .wfacp_main_form .wfacp_main_form.woocommerce form.checkout input[readonly] {
                    background: transparent;
                }

                <?php
				}
				?>
                .woocommerce form .form-row-quart, .woocommerce-page form .form-row-quart, .woocommerce form .form-row-quart-first, .woocommerce-page form .form-row-quart-first {
                    width: auto;
                }


            </style>
			<?php
		}

	}

	public function add_js() {

		if ( ! class_exists( 'WPO\WC\Postcode_Checker\WC_NLPostcode_Fields' ) ) {
			return '';
		}


		?>
        <script>

            window.addEventListener('load', function () {
                (function ($) {

                    var billing_country_nl, shipping_country_nl;
                    billing_country_nl = "<?php echo $this->billing_country_nl; ?>";
                    shipping_country_nl = "<?php echo $this->shipping_country_nl; ?>";


                    $(".wfacp_steps_wrap a").on('click', function (e) {

                    });

                    $(document).ready(function () {
                        var status_active = "";


                        if (typeof billing_country_nl != "undefined" && billing_country_nl == 1) {
                            check_field_class($("#billing_country"));
                        }
                        if (typeof shipping_country_nl != "undefined" && shipping_country_nl == 1) {
                            check_field_class($("#shipping_country"));
                        }

                    });


                    $(document.body).on("change", "#shipping_country", function (e) {

                        check_field_class($(this));

                    });

                    $(document.body).on("change", "#billing_same_as_shipping_field", function (e) {

                        check_field_class($(this));

                    });
                    $(document.body).on("change", "#shipping_same_as_billing_field", function (e) {

                        check_field_class($(this));

                    });


                    $(document).on('change', '#billing_country', function () {

                        check_field_class($(this));

                    });

                    $(document.body).on('wpo_wcnlpc_fields_updated', function () {

                        remove_hide_animate();
                    });


                    function remove_hide_animate() {

                        var addresses = ['billing', 'shipping'];
                        for (var i in addresses) {
                            var key = addresses[i];
                            $(".wfacp_divider_" + key + " .form-row").each(function () {
                                let field_id = $(this).attr("id");
                                if (field_id != '') {
                                    let field_val_id1 = field_id.replace('_field', '');
                                    let field_val = $('#' + field_val_id1).val();
                                    if (field_val != '' && field_val != null && !$(this).hasClass('wfacp-anim-wrap')) {

                                        $(this).addClass("wfacp-anim-wrap");
                                    }
                                }
                            });
                        }


                    }

                    function check_field_class($this) {

                        if (wpo_wcnlpc.street_city_visibility == 'readonly') {

                            $('.form-row ').each(function () {
                                $(this).find('input[readonly]').parents('.form-row').addClass("wfacp_readonly");
                            });

                        }


                        var country_val = $this.val();
                        if (typeof country_val == 'undefined' || country_val == '') {
                            return;
                        }


                        var fieldCls = ['shipping_country_field', 'billing_country_field'];
                        $('.wfacp-row .form-row').each(function () {
                            var id = $(this).attr("id");
                            var billing_country = $('#billing_country').val();
                            var shipping_country = $('#shipping_country').val();
                            if ($.inArray(billing_country, wpo_wcnlpc.postcode_field_countries) === -1 || $.inArray(shipping_country, wpo_wcnlpc.postcode_field_countries) === -1) {
                                return;

                            }

                            if ($("#" + id).hasClass('wfacp-col-full')) {
                                return;
                            }

                            if (fieldCls.indexOf(id) > -1) {
                                $("#" + id).removeClass("wfacp-col-middle-third");
                                $("#" + id).addClass("wfacp-col-right-half");
                            }

                        });


                    }


                })(jQuery);
            });
        </script>
		<?php

	}


}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Wcnl_Postcode(), 'wcnl' );
