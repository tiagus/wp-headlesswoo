<?php


class WFACP_handle_billing_address {

	private $temp_field = [
		'first_name',
		'last_name',
		'address_1',
		'address_2',
		'city',
		'postcode',
		'country',
		'state'
	];

	public function __construct() {
		add_action( 'wfacp_before_process_checkout_template_loader', function () {
			add_action( 'woocommerce_checkout_order_processed', [ $this, 'woocommerce_checkout_update_order_meta' ],100 );
		} );
		add_action( 'wfacp_outside_header', [ $this, 'attach_hooks' ] );
	}

	public function attach_hooks() {
		add_action( 'woocommerce_checkout_after_customer_details', [ $this, 'print_billing_fields' ] );
		add_action( 'wp_footer', [ $this, 'enable_js' ], 256 );
	}


	public function print_billing_fields() {
		$instance = WFACP_Core()->customizer->get_template_instance();

		if ( false == $instance->have_billing_address() && $instance->have_shipping_address() ) {

			foreach ( $this->temp_field as $item ) {
				$b_key = 'billing_' . $item;
				$s_key = 'shipping_' . $item;
				if ( isset( $fields['billing'][ $b_key ] ) ) {
					continue;
				}
				if ( ! isset( $fields['shipping'][ $s_key ] ) ) {
					continue;
				}
				echo sprintf( '<input type="hidden"name="%s" id="%s" class="wfacp_hidden_fields">', $b_key, $b_key );
			}
		}

		if ( $instance->have_shipping_address() && $instance->have_billing_address() ) {
			$fields = $instance->get_checkout_fields();
			if ( isset( $fields['shipping']['shipping_first_name'] ) && ! isset( $fields['billing']['billing_first_name'] ) ) {
				echo sprintf( '<input type="hidden"name="%s" id="%s" class="wfacp_hidden_fields">', 'billing_first_name', 'billing_first_name' );
			}
			if ( isset( $fields['shipping']['shipping_last_name'] ) && ! isset( $fields['billing']['billing_last_name'] ) ) {
				echo sprintf( '<input type="hidden"name="%s" id="%s" class="wfacp_hidden_fields">', 'billing_last_name', 'billing_last_name' );
			}

			if ( isset( $fields['billing']['billing_first_name'] ) && ! isset( $fields['shipping']['shipping_first_name'] ) ) {
				echo sprintf( '<input type="hidden"name="%s" id="%s" class="wfacp_hidden_fields">', 'shipping_first_name', 'shipping_first_name' );
			}
			if ( isset( $fields['billing']['billing_last_name'] ) && ! isset( $fields['shipping']['shipping_last_name'] ) ) {
				echo sprintf( '<input type="hidden"name="%s" id="%s" class="wfacp_hidden_fields">', 'shipping_last_name', 'shipping_last_name' );
			}
		}


	}

	public function enable_js() {
		?>
        <script>
            (function ($) {
                var fields = {
                    'shipping_first_name': 'billing_first_name',
                    'shipping_last_name': 'billing_last_name',
                    'shipping_address_1': 'billing_address_1',
                    'shipping_address_2': 'billing_address_2',
                    'shipping_city': 'billing_city',
                    'shipping_postcode': 'billing_postcode',
                    'shipping_country': 'billing_country',
                    'shipping_state': 'billing_state',
                    'billing_first_name': 'shipping_first_name',
                    'billing_last_name': 'shipping_last_name',
                };

                function refill() {
                    for (var i in fields) {
                        setTimeout(function (i) {
                            var s = $('#' + i);
                            var b = $('#' + fields[i]);
                            if (s.length > 0 && b.length > 0 && false == b.is(":visible")) {
                                b.val(s.val());
                            }
                        }, 100, i);
                    }
                }

                function unset() {
                    for (var i in fields) {
                        if ('billing_country' == fields[i] || 'billing_state' == fields[i]) {
                            continue;
                        }
                        var b = $('#' + fields[i]);
                        if (true == b.is(":visible")) {
                            continue;
                        }
                        if (b.length > 0) {
                            b.val('');
                        }
                    }
                }


                $('#billing_same_as_shipping').on('change', function () {
                    if ($(this).is(':checked')) {
                        //unset();
                    } else {
                        refill();
                    }
                });

                for (var i in fields) {
                    $('#' + i).on('change', function () {
                        var id = $(this).attr('id');
                        var billing = $('#' + fields[id]);
                        if (billing.length > 0 && false == billing.is(':visible')) {
                            var vl = $(this).val();
                            billing.val(vl);
                        }
                    });
                }


                $(window).on('load', function () {
                    refill();
                });
            })(jQuery);
        </script>
		<?php


	}

	public function woocommerce_checkout_update_order_meta( $order_id ) {
		// handle first,and last name
		$arr_data = [];
		$arr      = [
			'billing_first_name'  => 'shipping_first_name',
			'billing_last_name'   => 'shipping_last_name',
			'shipping_first_name' => 'billing_first_name',
			'shipping_last_name'  => 'shipping_last_name',
		];

		foreach ( $arr as $a_key => $second_key ) {
			if ( isset( $_REQUEST[ $a_key ] ) ) {
				$arr_data[ '_' . $a_key ] = $_REQUEST[ $a_key ];
				continue;
			}
			if ( isset( $_REQUEST[ $second_key ] ) ) {
				$arr_data[ '_' . $a_key ] = $_REQUEST[ $second_key ];
				continue;
			}
		}


		if ( ! empty( $arr_data ) ) {
			foreach ( $arr_data as $meta_key => $meta_value ) {
				update_post_meta( $order_id, $meta_key, wc_clean( $meta_value ) );
			}
		}

	}
}

new WFACP_handle_billing_address();