<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var $instance WFACP_Template_Common
 */
if ( apply_filters( 'wfacp_skip_form_printing', false ) ) {

	return;
}

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );

	return;
}
$instance       = WFACP_Core()->customizer->get_template_instance();
$totalStepCount = $instance->get_step_count();
$stepClassName = 'wfacp_single_step_form';
if ( $totalStepCount > 1 ) {
	$stepClassName = 'wfacp_single_multi_form';
}
?>
<div class="wfacp_main_form woocommerce <?php echo $stepClassName; ?>">
	<?php
	do_action( 'wfacp_outside_header' );
	$payment_needed = false;

	$global_setting = WFACP_Core()->public->get_settings();

	$preview_section_heading    = '';
	$preview_section_subheading = '';
	if ( isset( $global_setting['preview_section_heading'] ) && $global_setting['preview_section_heading'] != '' ) {
		$preview_section_heading = $global_setting['preview_section_heading'];

	}
	if ( isset( $global_setting['preview_section_subheading'] ) && $global_setting['preview_section_subheading'] != '' ) {
		$preview_section_subheading = $global_setting['preview_section_subheading'];
	}
	$stepData = [];


	$checkout  = WC()->checkout();
	$fieldsets = $instance->get_fieldsets();

	if ( ! is_array( $fieldsets ) ) {
		return;
	}
	$current_step           = $instance->get_current_step();
	$selected_template_slug = $instance->get_template_slug();
	$template_type          = $instance->get_template_type();

	/**
	 * previous form_internal_css calling via include_once
	 * Now calling via  include because of bug created in order bump suddenly payment gateway hides
	 * IN order form addon we use do_shortcode at wp hook form_internal_css included once that time
	 *But when printing form in page formal_internal_css not included again due include once
	 *
	 */
	include __DIR__ . '/form_internal_css.php';
	wc_print_notices();
	do_action( 'woocommerce_before_checkout_form', $checkout );
	$required_messages    = [];
	$billing_country_find = false;
	$permalink            = get_the_permalink();


	$cart_contains_subscription = 0;
	if ( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) {
		$cart_contains_subscription = 1;
	}


	?>

    <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( $permalink ); ?>" enctype="multipart/form-data" id="wfacp_checkout_form">
        <input type="hidden" name="_wfacp_post_id" class="_wfacp_post_id" value="<?php echo WFACP_Common::get_id(); ?>">
        <input type="hidden" name="wfacp_cart_hash" value="<?php echo WC()->session->get( 'wfacp_cart_hash', '' ); ?>">
        <input type="hidden" name="wfacp_has_active_multi_checkout" id="wfacp_has_active_multi_checkout" value="">
        <input type="hidden" id="billing_shipping_index" value="<?php echo $instance->get_shipping_billing_index(); ?>">
        <input type="hidden" id="wfacp_source" name="wfacp_source" value="<?php echo esc_url( $permalink ); ?>">
        <input type="hidden" id="product_switcher_need_refresh" name="product_switcher_need_refresh" value="0">
        <input type="hidden" id="wfacp_cart_contains_subscription" name="wfacp_cart_contains_subscription" value="<?php echo $cart_contains_subscription; ?>">


		<?php

		if ( $instance->have_billing_address() && 'billing' == $instance->get_shipping_billing_index() ) {
			?>
            <input type="hidden" name="wfacp_billing_same_as_shipping" id="wfacp_billing_same_as_shipping" value="0">
			<?php
		}
		if ( $instance->have_billing_address() ) {
			?>
            <input type="hidden" name="wfacp_billing_address_present" id="wfacp_billing_address_present" value="yes">
			<?php
		}


		?>
		<?php
		if ( ! $instance->have_shipping_address() && ! $instance->have_billing_address() && class_exists( 'WC_Geolocation' ) ) {
			$default_country      = WFACP_Common::get_base_country();
			$billing_country_find = true;
			?>
            <input type="hidden" name="billing_country" id='billing_country' value="<?php echo $default_country; ?>"/>
			<?php

		}
		?>
        <div class="wfacp_error_message">
			<?php _e( 'Please fill all required field', 'woofunnels-aero-checkout' ); ?>
        </div>
		<?php
		do_action( 'woocommerce_checkout_before_customer_details' );
		$formData = [];
		if ( isset( $instance->customizer_fields_data['wfacp_form'] ) ) {
			$formData = $instance->customizer_fields_data['wfacp_form'];
		}


		$border_cls = '';

		if ( isset( $formData['border']['form_head']['border-style'] ) && $formData['border']['form_head']['border-style'] != '' ) {
			$border_cls = $formData['border']['form_head']['border-style'];
		}

		$heading_talign      = '';
		$heading_font_weight = '';

		$sub_heading_talign      = '';
		$sub_heading_font_weight = '';

		if ( isset( $formData['heading_section']['heading_talign'] ) ) {
			$heading_talign = $formData['heading_section']['heading_talign'];
		}

		if ( isset( $formData['heading_section']['heading_talign'] ) ) {
			$heading_font_weight = $formData['heading_section']['heading_font_weight'];
		}


		if ( isset( $formData['sub_heading_section']['heading_talign'] ) ) {
			$sub_heading_talign = $formData['sub_heading_section']['heading_talign'];
		}

		if ( isset( $formData['sub_heading_section']['heading_font_weight'] ) ) {
			$sub_heading_font_weight = $formData['sub_heading_section']['heading_font_weight'];
		}

		$non_input_fields = [
			'',
			'wfacp_divider_',
			'shipping_calculator',
			'product_switching',
			'order_summary',
		];


		// check last is empty means user want last step to payment gateway
		if ( ! isset( $fieldsets[ $current_step ] ) ) {
			$fieldsets[ $current_step ] = [];

		}


		foreach ( $fieldsets as $step => $sections ) {

			do_action( 'wfacp_template_before_step', $step, $sections );
			$last_step = '';
			if ( $current_step == $step ) {
				$last_step = 'wfacp_last_page';
			}
			echo sprintf( '<div class="wfacp-left-panel wfacp_page %s %s %s" data-step="%s">', $template_type, $step, $last_step, $step );
			$count_increment = 0;
			if ( 'single_step' != $step ) {
				$instance->get_back_button( $step );
			}

			if ( 'single_step' != $step ) {
				?>
                <div class="wfacp_preview_content_box" data-step="<?php echo $step; ?>">
					<?php
					if ( $preview_section_heading != '' || $preview_section_subheading != '' ) {
						?>
                        <div class="wfacp_section">
                            <div class="wfacp-comm-title none">
                                <h2 class="wfacp_section_heading wfacp_section_title wfacp-normal"><?php echo $preview_section_heading; ?></h2>
                                <h4 class="wfacp-text-left wfacp-normal"><?php echo $preview_section_subheading; ?></h4>
                            </div>
                        </div>
						<?php
					}
					?>
                    <div class="wfacp_step_preview"></div>
                </div>
				<?php
			}

			if ( ! empty( $sections ) ) {
				foreach ( $sections as $section_index => $section ) {

					if ( ! isset( $section['fields'] ) || count( $section['fields'] ) == 0 ) {
						continue;
					}


					$sizeofSectionFields = sizeof( $section['fields'] );


					$lastKeyArr     = end( $section['fields'] );
					$sectionLastKey = isset( $lastKeyArr['id'] ) ? $lastKeyArr['id'] : '';


					$section = apply_filters( 'wfacp_form_section', $section, $section_index, $step );
					if ( apply_filters( 'wfacp_hide_section', false, $section, $section_index, $step ) ) {
						continue;
					}
					$fields        = $section['fields'];
					$custom_class  = 'step_' . $section_index;
					$section_class = 'form_section_' . $step . '_' . $section_index . '_' . $selected_template_slug . ' ' . $section['class'];
					echo sprintf( '<div class="wfacp-section  wfacp-hg-by-box %s %s" data-field-count="%d">
<div class="wfacp-comm-title %s"><h2 class="wfacp_section_heading wfacp_section_title %s">%s </h2><h4 class="%s">%s</h4></div>
<div class="wfacp-comm-form-detail clearfix">
<div class="wfacp-row">', $custom_class, $section_class, count( $fields ), $border_cls, $heading_font_weight . ' ' . $heading_talign, $section['name'], $sub_heading_talign . ' ' . $sub_heading_font_weight, ( isset( $section['sub_heading'] ) && '' != $section['sub_heading'] ) ? $section['sub_heading'] : '' );
					do_action( 'wfacp_template_before_section', $step, $section['fields'] );
					$counterInnerFields = 1;


					foreach ( $fields as $field ) {

						$payment_needed = true;
						$key            = isset( $field['id'] ) ? $field['id'] : '';
						$field          = apply_filters( 'wfacp_forms_field', $field, $key );

						if ( 'billing_email' === $key && isset( $field['placeholder'] ) && 'abc@exmple.com' === $field['placeholder'] ) {
							$field['placeholder'] = 'abc@example.com';
						}



						if ( empty( $field ) ) {
							continue;
						}


						if ( $sectionLastKey == $key ) {
							$field['class'][] = 'wfacp_last_section_feilds';
						}


						if ( 'billing_country' === $key ) {
							$billing_country_find = true;
						}

						if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
							$field['country'] = $checkout->get_value( $field['country_field'] );
						}

						$field_value = $checkout->get_value( $key );


						$field_value = apply_filters( 'wfacp_default_values', $field_value, $key, $field );

						if ( in_array( $key, [ 'billing_same_as_shipping', 'shipping_same_as_billing' ] ) ) {
							$field_value = null;
						}


						do_action( 'wfacp_before_' . $key . '_field', $key, $field, $field_value );


						if ( ! is_null( $field_value ) && '' !== $field_value ) {
							$field['class'][] = 'wfacp-anim-wrap';
						}
						if ( ( $key == 'shipping_country' || $key == 'billing_country' ) && ( is_array( $field['class'] ) && count( $field['class'] ) > 0 ) && in_array( 'wfacp-anim-wrap', $field['class'] ) ) {
							$lastKey = count( $field['class'] ) - 1;
							unset( $field['class'] [ $lastKey ] );
						}
						if ( isset( $field['is_wfacp_field'] ) && isset( $field['type'] ) && $field['type'] == 'select' ) {
							$field['class'][] = 'wfacp_drop_list';
						}

						woocommerce_form_field( $key, $field, $field_value );
						do_action( 'wfacp_after_' . $key . '_field', $key, $field, $field_value );
						$counterInnerFields ++;
					}

					do_action( 'wfacp_template_after_section', $step, $sections, $section_index );
					echo '<div class="wfacp_clear"></div></div></div>';
					echo( '</div>' );
					$count_increment ++;
				}
			}


			do_action( 'wfacp_template_after_step', $step, $current_step, $formData );


			if ( $step == $current_step ) {

				do_action( 'wfacp_before_payment_section' );
				include __DIR__ . '/payment.php';
				do_action( 'wfacp_after_payment_section' );
			}


			echo( '</div>' );


		}


		if ( false == $billing_country_find ) {
			$default_country = WFACP_Common::get_base_country();
			?>
            <input type="hidden" name="billing_country" id='billing_country' value="<?php echo $default_country; ?>">
			<?php

		}

		do_action( 'woocommerce_checkout_after_customer_details' );


		if ( $instance->have_shipping_address() ) {
			$temp_st    = $instance->have_billing_address();
			$is_checked = '';
			if ( ! $temp_st || 'billing' == $instance->get_shipping_billing_index() ) {
				$is_checked = 'checked';
			}

			echo "<div id='ship-to-different-address'>";
			echo '<input id="ship-to-different-address-checkbox" class="ship_to_different_address" type="checkbox" name="ship_to_different_address"  style="display:none" ' . $is_checked . '>';
			echo '</div>';
		}
		?>

    </form>
	<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

</div>

<?php
wp_enqueue_style( 'photoswipe' );
wp_enqueue_style( 'photoswipe-default-skin' );
wp_enqueue_script( 'wc-single-product' );
wp_enqueue_script( 'zoom' );
wp_enqueue_script( 'flexslider' );
wp_enqueue_script( 'photoswipe' );
wp_enqueue_script( 'photoswipe-ui-default' );
if ( WFACP_Core()->public->variable_product ) {
	wp_enqueue_script( 'wc-add-to-cart-variation' );
}
if ( function_exists( 'woocommerce_photoswipe' ) ) {
	woocommerce_photoswipe();
}

?>
