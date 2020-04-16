<?php

abstract class WFACP_Common_Helper {
	/**
	 * Disabled finale execution on our discounting
	 */
	public static function disable_wcct_pricing() {

		if ( function_exists( 'WCCT_Core' ) && class_exists( 'WCCT_discount' ) ) {

			add_filter( 'wcct_force_do_not_run_campaign', function ( $status, $instance ) {
				$products = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );
				if ( is_array( $products ) && count( $products ) > 0 ) {

					foreach ( $products as $index => $data ) {
						$product_id = absint( $data['id'] );
						if ( $data['parent_product_id'] && $data['parent_product_id'] > 0 ) {
							$product_id = absint( $data['parent_product_id'] );
						}
						unset( $instance->single_campaign[ $product_id ] );
						$status = false;
					}
				}

				return $status;

			}, 10, 2 );
		}
	}

	/**
	 * Restrict discount apply on these our ajax action
	 *
	 * @param $actions
	 *
	 * @return array
	 */
	public static function wcct_get_restricted_action( $actions ) {
		$actions[] = 'wfacp_add_product';
		$actions[] = 'wfacp_remove_product';
		$actions[] = 'wfacp_save_products';

		$actions[] = 'wfacp_addon_product';
		$actions[] = 'wfacp_remove_addon_product';
		$actions[] = 'wfacp_switch_product_addon';
		$actions[] = 'wfacp_update_product_qty';
		$actions[] = 'wfacp_quick_view_ajax';

		return $actions;
	}

	public static function handling_post_data( $post_data ) {
		if ( isset( $post_data['ship_to_different_address'] ) && isset( $post_data['wfacp_billing_same_as_shipping'] ) && $post_data['wfacp_billing_same_as_shipping'] == 0 ) {
			$address_fields = [ 'address_1', 'address_2', 'city', 'postcode', 'country', 'state' ];
			foreach ( $address_fields as $key => $val ) {
				if ( isset( $_POST[ 's_' . $val ] ) ) {
					$_POST[ $val ] = $_POST[ 's_' . $val ];
				}
			}
		}
	}

	public static function merge_page_product_settings( $settings ) {
		if ( ! isset( $settings['settings']['product_switcher_template'] ) ) {
			$settings['settings']['product_switcher_template'] = 'default';
		}

		return $settings;
	}


	public static function wcs_cart_totals_shipping_calculator_html() {
		include WFACP_TEMPLATE_COMMON . '/checkout/wcs_cart_totals_shipping_calculator_html.php';
	}


	public static function wcs_cart_totals_shipping_html() {
		include WFACP_TEMPLATE_COMMON . '/checkout/wcs_cart_totals_shipping_html.php';
	}


	public static function print_custom_field_at_thankyou( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		include WFACP_TEMPLATE_COMMON . '/thankyou-custom-field.php';
	}


	public static function print_custom_field_at_email( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		include WFACP_TEMPLATE_COMMON . '/email-custom-field.php';
	}

	public static function check_wc_validations_billing( $address_fields, $type ) {

		$woocommerce_checkout_address_2_field = get_option( 'woocommerce_checkout_address_2_field', 'optional' );
		$woocommerce_checkout_company_field   = get_option( 'woocommerce_checkout_company_field', 'optional' );
		$requiredFor                          = false;
		$requiredForCompany                   = false;
		if ( 'required' === $woocommerce_checkout_address_2_field ) {
			$requiredFor = true;
		}
		if ( 'required' === $woocommerce_checkout_company_field ) {
			$requiredForCompany = true;
		}

		if ( isset( $address_fields['billing_address_2'] ) ) {
			if ( ( isset( $address_fields['billing_address_2']['required'] ) && false === $requiredFor ) ) {
				unset( $address_fields['billing_address_2']['required'] );
			}
		}

		if ( isset( $address_fields['billing_company'] ) ) {
			if ( ( isset( $address_fields['billing_company']['required'] ) && false === $requiredForCompany ) ) {
				unset( $address_fields['billing_company']['required'] );
			}
		}

		return $address_fields;
	}

	public static function check_wc_validations_shipping( $address_fields, $type ) {

		$woocommerce_checkout_address_2_field = get_option( 'woocommerce_checkout_address_2_field', 'optional' );
		$woocommerce_checkout_company_field   = get_option( 'woocommerce_checkout_company_field', 'optional' );

		$requiredFor        = false;
		$requiredForCompany = false;
		if ( 'required' === $woocommerce_checkout_address_2_field ) {
			$requiredFor = true;
		}

		if ( 'required' === $woocommerce_checkout_company_field ) {
			$requiredForCompany = true;
		}

		if ( isset( $address_fields['shipping_address_2'] ) ) {
			if ( ( isset( $address_fields['shipping_address_2']['required'] ) && false === $requiredFor ) ) {
				unset( $address_fields['shipping_address_2']['required'] );
			}
		}
		if ( isset( $address_fields['shipping_company'] ) ) {
			if ( ( isset( $address_fields['shipping_company']['required'] ) && false === $requiredForCompany ) ) {
				unset( $address_fields['shipping_company']['required'] );
			}
		}

		return $address_fields;
	}

	/** Do not sustain deleted item in remove_cart_item_object
	 *
	 * @param $cart_item_key
	 * @param $cart WC_Cart
	 */
	public static function remove_item_deleted_items( $cart_item_key, $cart ) {
		unset( $cart->removed_cart_contents[ $cart_item_key ] );
	}

	public static function remove_src_set( $attr ) {
		if ( isset( $attr['srcset'] ) ) {
			unset( $attr['srcset'] );
		}

		return $attr;
	}

	/**
	 * Re apply aero checkout product settings when payment failed for subscription and user click on pay now button from  order list at my-account
	 *All discount and other setting automatically applied
	 *
	 * @param $cart_item_data
	 *
	 * @return mixed
	 */
	public static function re_apply_aero_checkout_settings( $cart_item_data ) {
		if ( isset( $cart_item_data['subscription_initial_payment'] ) && isset( $cart_item_data['subscription_initial_payment']['custom_line_item_meta'] ) ) {

			$line_data = $cart_item_data['subscription_initial_payment']['custom_line_item_meta'];
			if ( isset( $line_data['_wfacp_product'] ) ) {
				$cart_item_data['_wfacp_product']     = $line_data['_wfacp_product'];
				$cart_item_data['_wfacp_product_key'] = $line_data['_wfacp_product_key'];
				$cart_item_data['_wfacp_options']     = $line_data['_wfacp_options'];

			}
		}

		return $cart_item_data;

	}


	public static function get_product_image( $product_obj, $size = 'woocommerce_thumbnail', $cart_item = [], $cart_item_key = '' ) {
		$image = '';
		if ( ! $product_obj instanceof WC_Product ) {
			return $image;
		}
		if ( $product_obj->get_image_id() ) {
			$image = wp_get_attachment_image_src( $product_obj->get_image_id(), $size, false );
		} elseif ( $product_obj->get_parent_id() ) {
			$parent_product = wc_get_product( $product_obj->get_parent_id() );
			$image          = self::get_product_image( $parent_product, $size, $cart_item, $cart_item_key );
		}

		if ( is_array( $image ) && isset( $image[0] ) ) {

			$image_src = apply_filters( 'wfacp_cart_item_thumbnail', $image[0], $cart_item, $cart_item_key );

			$image_html = '<img src="' . esc_attr( $image_src ) . '" alt="' . esc_html( $product_obj->get_name() ) . '" width="' . esc_attr( $image[1] ) . '" height="' . esc_attr( $image[2] ) . '" />';

			return $image_html;
		}

		$image = wc_placeholder_img( $size );

		return $image;
	}

	public static function array_insert_after( array $array, $key, array $new ) {
		$keys  = array_keys( $array );
		$index = array_search( $key, $keys );

		$pos = false === $index ? count( $array ) : $index + 1;

		return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
	}


	public static function sort_shipping( $available_methods ) {


		$global_settings = get_option( '_wfacp_global_settings', [] );
		if ( isset( $global_settings['wfacp_set_shipping_method'] ) && false === wc_string_to_bool( $global_settings['wfacp_set_shipping_method'] ) ) {
			if ( true === apply_filters( 'wfacp_disable_shipping_sorting', true ) ) {
				return $available_methods;
			}
		}

		uasort( $available_methods, [ __CLASS__, 'short_shipping_method' ] );

		return $available_methods;
	}

	/**
	 * Short shipping method low to high Cost
	 *
	 * @param $p1
	 * @param $p2
	 */
	public static function short_shipping_method( $p1, $p2 ) {
		if ( $p1 instanceof WC_Shipping_Rate && $p2 instanceof WC_Shipping_Rate ) {
			if ( $p1->get_cost() == $p2->get_cost() ) {
				return 0;
			}

			return ( $p1->get_cost() < $p2->get_cost() ) ? - 1 : 1;
		}

		return 0;
	}


	public static function assign_minimum_value_sipping_method( $default, $rates, $chosen_method ) {

		if ( true === apply_filters( 'wfacp_disable_minimum_value_shipping', true ) ) {
			return $default;
		}
		if ( is_array( $rates ) && count( $rates ) > 0 ) {
			//	uasort( $rates, 'WFACP_Common::short_shipping_method' );
			$rates   = WFACP_Common::sort_shipping( $rates );
			$default = current( array_keys( $rates ) );

		}

		return $default;
	}


	/**
	 * @param $cart_item
	 * @param $pro WC_Product
	 * @param $product_data
	 * @param $cart_variation_id String
	 *
	 * @return array
	 */
	public final static function get_cart_item_attributes( $cart_item, $pro, $product_data, $cart_variation_id ) {
		global $wfacp_products_attributes_data;
		$product_attributes   = [];
		$variation_attributes = [];
		if ( ! is_null( $cart_item ) && isset( $cart_item['variation_id'] ) ) {

			if ( is_array( $cart_item['variation'] ) && count( $cart_item['variation'] ) ) {
				$product_attributes = $cart_item['variation'];
			} elseif ( 'variation' == $cart_item['data']->get_type() ) {
				$product_attributes = $cart_item['data']->get_attributes();
			}
		} elseif ( 'variation' == $pro->get_type() ) {
			$product_attributes = $pro->get_attributes();
		}

		$is_variation_error = '';
		$attributes_keys    = [];
		if ( count( $product_attributes ) > 0 ) {
			$wfacp_products_attributes_data[ $pro->get_id() ]['attributes'] = $product_attributes;
			foreach ( $product_attributes as $a_key => $a_val ) {
				if ( '' != $a_val ) {
					$variation_attributes[] = $a_val;
				} else {
					if ( in_array( $product_data['type'], WFACP_Common::get_variable_product_type() ) && $cart_variation_id > 0 ) {
						$is_variation_error = 'wfacp_incomplete_variation';
						$attributes_keys[]  = ucwords( str_replace( [ 'attribute_', 'attribute_pa_' ], '', $a_key ) );
					}
				}
			}
		}

		return [ $product_attributes, $is_variation_error, $attributes_keys, $variation_attributes ];
	}


	/**
	 * Get a shipping methods full label including price.
	 *
	 * @param WC_Shipping_Rate $method Shipping method rate data.
	 *
	 * @return string
	 */
	public static function wc_cart_totals_shipping_method_cost( $method ) {
		$output    = __( 'Free', 'woocommerce' );
		$has_cost  = 0 < $method->cost;
		$hide_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'free_shipping', 'local_pickup' ), true );

		if ( $has_cost && ! $hide_cost ) {
			$output = '';
			if ( WC()->cart->display_prices_including_tax() ) {
				$output .= wc_price( $method->cost + $method->get_shipping_tax() );
				if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
					$output .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
				$output .= wc_price( $method->cost );
				if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
					$output .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
		}

		return apply_filters( 'wc_cart_totals_shipping_method_cost', $output, $method );
	}

	/**
	 * Get a shipping methods full label including price.
	 *
	 * @param WC_Shipping_Rate $method Shipping method rate data.
	 *
	 * @return string
	 */
	public static function shipping_method_label( $method ) {

		$status = apply_filters( 'wfacp_show_shipping_method_label_without_tax_string', true, $method );
		if ( true == $status ) {
			$output = $method->get_label();
		} else {
			$output = wc_cart_totals_shipping_method_label( $method );
		}

		return apply_filters( 'woocommerce_cart_shipping_method_full_label', $output, $method );
	}


	public static function get_cart_count( $items ) {
		$count = 0;
		if ( is_array( $items ) && count( $items ) > 0 ) {
			foreach ( $items as $item ) {
				if ( isset( $item['_wfob_product'] ) || apply_filters( 'wfacp_exclude_product_cart_count', false, $item ) ) {
					continue;
				}
				$count ++;
			}
		}

		return $count;

	}

	public static function wc_cart_totals_shipping_html( $colspan_attr = '' ) {
		$packages = WC()->shipping->get_packages();
		$first    = true;

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
			$product_names = array();
			if ( count( $packages ) > 1 ) {
				foreach ( $package['contents'] as $item_id => $values ) {
					$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
				}
				$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
			}

			wc_get_template( 'wfacp/checkout/cart-shipping.php', array(
				'package'                  => $package,
				'available_methods'        => $package['rates'],
				'show_package_details'     => count( $packages ) > 1,
				'show_shipping_calculator' => is_cart() && $first,
				'package_details'          => implode( ', ', $product_names ),
				'package_name'             => apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping %d', 'shipping packages', 'woocommerce' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'woocommerce' ), $i, $package ),
				'index'                    => $i,
				'chosen_method'            => $chosen_method,
				'formatted_destination'    => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
				'has_calculated_shipping'  => WC()->customer->has_calculated_shipping(),
				'colspan_attr'             => $colspan_attr,
			) );

			$first = false;
		}
	}

	/**
	 * Remove action for without instance method  class found and return object of class
	 *
	 * @param $hook
	 * @param $cls CLASS || FUNCTION
	 * @param string $function
	 *
	 * @return |null
	 */
	public static function remove_actions( $hook, $cls, $function = '' ) {

		global $wp_filter;
		$object = null;
		if ( class_exists( $cls ) && isset( $wp_filter[ $hook ] ) && ( $wp_filter[ $hook ] instanceof WP_Hook ) ) {
			$hooks = $wp_filter[ $hook ]->callbacks;
			foreach ( $hooks as $priority => $reference ) {
				if ( is_array( $reference ) && count( $reference ) > 0 ) {
					foreach ( $reference as $index => $calls ) {
						if ( isset( $calls['function'] ) && is_array( $calls['function'] ) && count( $calls['function'] ) > 0 ) {
							if ( is_object( $calls['function'][0] ) ) {
								$cls_name = get_class( $calls['function'][0] );
								if ( $cls_name == $cls && $calls['function'][1] == $function ) {
									$object = $calls['function'][0];
									unset( $wp_filter[ $hook ]->callbacks[ $priority ][ $index ] );
								}
							} elseif ( $index == $cls . '::' . $function ) {
								// For Static Classess
								$object = $cls;
								unset( $wp_filter[ $hook ]->callbacks[ $priority ][ $cls . '::' . $function ] );
							}
						}
					}
				}
			}
		} elseif ( function_exists( $cls ) && isset( $wp_filter[ $hook ] ) && ( $wp_filter[ $hook ] instanceof WP_Hook ) ) {

			$hooks = $wp_filter[ $hook ]->callbacks;
			foreach ( $hooks as $priority => $reference ) {
				if ( is_array( $reference ) && count( $reference ) > 0 ) {
					foreach ( $reference as $index => $calls ) {
						$remove = false;
						if ( $index == $cls ) {
							$remove = true;
						} elseif ( isset( $calls['function'] ) && $cls == $calls['function'] ) {
							$remove = true;
						}
						if ( true == $remove ) {
							unset( $wp_filter[ $hook ]->callbacks[ $priority ][ $cls ] );
						}
					}
				}
			}
		}

		return $object;

	}


	/**
	 * @param $cart_item
	 * @param $cart_item_key
	 * @param $pro WC_Product
	 * @param $switcher_settings
	 * @param $product_data
	 *
	 * @return mixed|void
	 */
	public static function get_product_switcher_item_title( $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data ) {

		$item_key = isset( $product_data['item_key'] ) ? $product_data['item_key'] : '';
		if ( isset( $switcher_settings['products'][ $item_key ] ) ) {
			$title     = $switcher_settings['products'][ $item_key ]['title'];
			$old_title = $switcher_settings['products'][ $item_key ]['old_title'];
			if ( '' !== $title && $title !== $old_title ) {
				return "<span class='wfacp_product_switcher_item'>" . $title . "</span>";
			}
		}

		return "<span class='wfacp_product_switcher_item'>" . $pro->get_title() . "</span>";
	}

	/**
	 * Filter callback for finding variation attributes.
	 *
	 * @param WC_Product_Attribute $attribute Product attribute.
	 *
	 * @return bool
	 */
	private static function filter_variation_attributes( $attribute ) {
		return true === $attribute->get_variation();
	}

	/**
	 * @param $cart_item
	 * @param $cart_item_key
	 * @param $pro WC_Product
	 * @param $switcher_settings
	 * @param $product_data
	 *
	 * @return mixed|void
	 */
	public final static function get_attribute_html( $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data ) {

		if ( apply_filters( 'wfacp_hide_product_switcher_attributes', ( ! isset( $product_data['variable'] ) || 'yes' !== $product_data['variable'] ), $cart_item, $cart_item_key, $pro, $switcher_settings, $product_data ) ) {
			return;
		}
		if ( ! in_array( $pro->get_type(), WFACP_Common::get_variation_product_type() ) ) {
			return;
		}

		$is_product_is_variable = ( isset( $product_data['variable'] ) && 'yes' == $product_data['variable'] );

		$parent_id   = $pro->get_parent_id();
		$product_obj = WFACP_Common::wc_get_product( $parent_id, $product_data['item_key'] );

		$variation_attributes = array_filter( $product_obj->get_attributes(), array( __CLASS__, 'filter_variation_attributes' ) );

		if ( empty( $variation_attributes ) ) {
			return;
		}


		$item_in_cart            = false;
		$cart_product_attributes = [];
		if ( ! empty( $cart_item ) && isset( $cart_item['data'] ) ) {
			/**
			 * @var $cart_product_object
			 */
			$item_in_cart            = true;
			$cart_product_attributes = $cart_item['variation'];
		}

		$attribute_string     = '';
		$attributes_array     = [];
		$incomplete_variation = '';
		$only_attribute       = [];
		$cart_variation_id    = 0;
		if ( ! is_null( $cart_item ) ) {
			if ( isset( $cart_item['variation_id'] ) ) {
				$cart_variation_id = $cart_item['variation_id'];
			}
		}


		$output = [ 'selected' => '', 'not_selected' => '' ];
		/**
		 * @var $attribute WC_Product_Attribute
		 */

		foreach ( $variation_attributes as $slug => $attribute ) {

			$only_attribute[] = wc_attribute_label( $attribute->get_name() );
			if ( false == $item_in_cart && true == $is_product_is_variable ) {

				continue;
			}

			$temp_terms = [];
			$terms      = $attribute->get_terms();
			if ( ! is_null( $terms ) ) {
				foreach ( $terms as $term ) {
					$temp_terms[ $term->slug ] = $term->name;
				}
			}
			$attr_value          = ( $is_product_is_variable ) ? __( 'Select', 'woocommerce' ) : '';
			$temp_slug           = 'attribute_' . $slug;
			$value_not_available = '';
			if ( ! empty( $cart_product_attributes ) && isset( $cart_product_attributes[ $temp_slug ] ) && '' !== $cart_product_attributes[ $temp_slug ] ) {
				$attr_value = $cart_product_attributes[ $temp_slug ];
				if ( isset( $temp_terms[ $attr_value ] ) ) {
					$attr_value = $temp_terms[ $attr_value ];
				}
			} else {
				if ( $is_product_is_variable ) {
					$value_not_available  = 'wfacp_attr_value_not_available';
					$incomplete_variation = 'wfacp_incomplete_variation';
				}
			}


			if ( '' !== $attr_value ) {

				$attribute_string .= sprintf( '<div class="wfacp_pro_attr_single"><span class="wfacp_attribute_id">%s</span><span class="wfacp_attributes_sep">: </span><span class="wfacp_attribute_value %s">%s</span><span>, </span></div>', wc_attribute_label( $attribute->get_name() ), $value_not_available, $attr_value );


			}
		}


		if ( '' != $attribute_string && true == $item_in_cart ) {

			$output['selected'] = sprintf( '<div class="wfacp_selected_attributes %s">%s</div>', $incomplete_variation, $attribute_string );

		}


		if ( true == $is_product_is_variable && ! empty( $only_attribute ) && self::display_not_selected_attribute( $product_data, $pro ) ) {

			$not_selected = __( 'Select', 'woofunnels-aero-checkout' );
			if ( count( $only_attribute ) > 1 ) {
				$last = end( $only_attribute );
				$size = count( $only_attribute );
				unset( $only_attribute[ $size - 1 ] );
				$not_selected .= ' ' . implode( ', ', $only_attribute ) . ' &amp; ' . $last;
			} else {
				$not_selected .= ' ' . $only_attribute[0];
			}


			$choose_label           = sprintf( "<a href='#' class='wfacp_qv-button var_product' qv-id='%d' qv-var-id='%d'>%s</a>", $product_data['id'], $cart_variation_id, apply_filters( 'wfacp_choose_option_text', $not_selected ) );
			$output['not_selected'] = sprintf( '<div class="wfacp_not_selected_attributes">%s</div>', $choose_label );


		} else {
			do_action( 'wfacp_display_not_selected_attribute_placeholder', $only_attribute, $product_data, $pro );
		}

		return $output;

	}


	/**
	 * @param $you_save_text
	 * @param $price_data
	 * @param $pro WC_Product
	 * @param $product_data
	 * @param $cart_item
	 * @param $cart_item_key
	 *
	 * @return array
	 */
	public final static function get_product_switcher_item_you_save( $you_save_text, $price_data, $pro, $product_data, $cart_item, $cart_item_key ) {
		$you_save_text_html = '';
		if ( '' !== $you_save_text ) {
			$subscription_tryl   = 0;
			$subscription_signup = 0;
			if ( in_array( $pro->get_type(), WFACP_Common::get_subscription_product_type() ) ) {
				$subscription_tryl   = WC_Subscriptions_Product::get_trial_length( $pro );
				$subscription_signup = WC_Subscriptions_Product::get_sign_up_fee( $pro );
			}

			$have_saving_value_merge_tag      = strpos( $you_save_text, '{{saving_value}}' );
			$have_saving_percentage_merge_tag = strpos( $you_save_text, '{{saving_percentage}}' );
			if ( ( false !== $have_saving_value_merge_tag || false !== $have_saving_percentage_merge_tag ) ) {
				if ( $subscription_tryl > 0 || $subscription_signup > 0 ) {
					//available  for future updates
				} else {
					$save_html = WFACP_Common::product_switcher_merge_tags( $you_save_text, $price_data, $pro, $product_data, $cart_item, $cart_item_key );
					if ( '' !== $save_html ) {
						$you_save_text_html = sprintf( '<div class="wfacp_you_save_text">%s</div>', $save_html );
					}
				}
			} else {
				// do not have merge tag Or Static you save text
				$save_html = WFACP_Common::product_switcher_merge_tags( $you_save_text, $price_data, $pro, $product_data, $cart_item, $cart_item_key );
				if ( '' !== $save_html ) {
					$you_save_text_html = sprintf( '<div class="wfacp_you_save_text">%s</div>', $save_html );
				}
			}
		}
		$subscription_product_string = '';
		if ( in_array( $pro->get_type(), WFACP_Common::get_subscription_product_type() ) ) {
			$subscription_product_string = sprintf( "<div class='wfacp_product_subs_details'>%s</div>", WFACP_Common::subscription_product_string( $pro, $product_data, $cart_item, $cart_item_key ) );
		}

		return [ $you_save_text_html, $subscription_product_string ];
	}


	public static final function display_not_selected_attribute( $product_data, $pro ) {
		return apply_filters( 'wfacp_display_not_selected_attribute', false, $product_data, $pro );
	}

}
