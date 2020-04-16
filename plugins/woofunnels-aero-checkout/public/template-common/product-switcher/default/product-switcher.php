<?php
defined( 'ABSPATH' ) || exit;
WFACP_Core()->public->get_page_data( WFACP_Common::get_id() );
$switcher_settings = WFACP_Common::get_product_switcher_data( WFACP_Common::get_id() );


$products      = WC()->session->get( 'wfacp_product_objects_' . WFACP_Common::get_id() );
$products_data = WC()->session->get( 'wfacp_product_data_' . WFACP_Common::get_id() );

$field                   = WC()->session->get( 'wfacp_product_switcher_field_' . WFACP_Common::get_id() );
$best_value_data         = WC()->session->get( 'wfacp_product_best_value_' . WFACP_Common::get_id() );
$best_value_by_parameter = WC()->session->get( 'wfacp_product_best_value_by_parameter_' . WFACP_Common::get_id(), '' );
$hide_whats_included     = wc_string_to_bool( $switcher_settings['settings']['is_hide_additional_information'] );
$hide_quantity_switcher  = wc_string_to_bool( $switcher_settings['settings']['hide_quantity_switcher'] );
$hide_product_image      = '';
$add_to_cart_setting     = $switcher_settings['product_settings']['add_to_cart_setting'];
$sec_heading             = $switcher_settings['settings']['additional_information_title'];

$type = $add_to_cart_setting == 2 ? 'radio' : 'checkbox';

$ps_cls_settings = [
	'ps_productSelection'    => 'wfacp_not_force_all',
	'ps_other_image_setting' => 'wfacp_setting_not_image_hide',
	'ps_other_qty_setting'   => 'wfacp_setting_not_qty_hide',
	'ps_delete_item'         => 'wfacp_enable_delete_item',
];


$ps_productSelection    = 'wfacp_not_force_all';
$ps_other_image_setting = 'wfacp_setting_not_image_hide';
$ps_other_qty_setting   = 'wfacp_setting_not_qty_hide';
if ( 1 == $add_to_cart_setting ) {
	$type                                   = 'hidden';
	$ps_productSelection                    = 'wfacp_force_all';
	$ps_cls_settings['ps_productSelection'] = $ps_productSelection;
}

if ( isset( $switcher_settings['settings']['hide_product_image'] ) ) {
	$hide_product_image = wc_string_to_bool( $switcher_settings['settings']['hide_product_image'] );
	if ( $hide_product_image == true ) {
		$ps_other_image_setting                    = 'wfacp_setting_image_hide';
		$ps_cls_settings['ps_other_image_setting'] = $ps_other_image_setting;
	}
}


$enableDeleteItem   = wc_string_to_bool( $switcher_settings['settings']['enable_delete_item'] );
$enable_delete_item = '';

if ( isset( $enableDeleteItem ) && false === $enableDeleteItem ) {
	$enable_delete_item                = 'wfacp_disable_delete_item';
	$ps_cls_settings['ps_delete_item'] = $enable_delete_item;

}

if ( true === $hide_quantity_switcher ) {
	$ps_other_qty_setting                    = 'wfacp_setting_qty_hide';
	$ps_cls_settings['ps_other_qty_setting'] = $ps_other_qty_setting;
}

$best_value_text_color = $best_value_data['best_value_text_color'];
$best_value_bg_color   = $best_value_data['best_value_bg_color'];
if ( ! isset( $hide_section ) || '' == $hide_section ) {
	$hide_section = '';
}

$detectDevice             = new WFACP_Mobile_Detect();
$instance_temp            = WFACP_Core()->customizer->get_template_instance();
$template_type_temp       = $instance_temp->get_template_type();
$deviceType               = '';
$is_sold_individually_arr = [];
$deviceType               = 'wfacp_for_desktop_tablet desk_only ';

if ( $detectDevice->isMobile() && ! $detectDevice->istablet() ) {
	$deviceType = 'wfacp_for_desktop_tablet wfacp_for_mb_style ';

} elseif ( $template_type_temp == 'embed_form' ) {


	$selected_template_slug = $instance_temp->get_template_slug();
	$layout_key             = '';
	$layout_key             = '';
	if ( isset( $selected_template_slug ) && $selected_template_slug != '' ) {
		$layout_key = $selected_template_slug . '_';
	}
	$step_form_max_width = WFACP_Common::get_option( 'wfacp_form_section_' . $layout_key . 'step_form_max_width' );
	if ( $step_form_max_width <= 374 ) {
		$wfacp_hide_img_wrap = apply_filters( 'wfacp_hide_product_image_for_less_width_form', 'wfacp_hideimg_wrap' );
		$deviceType          = 'wfacp_for_desktop_tablet wfacp_for_mb_style ' . $wfacp_hide_img_wrap.' ';
	} elseif ( $step_form_max_width >= 375 && $step_form_max_width <= 600 ) {
		$deviceType = 'wfacp_for_desktop_tablet wfacp_for_mb_style wfacp_ps_mb_active ';
	}
}


$ps_setting_wrapper_class = '';
if ( is_array( $ps_cls_settings ) && count( $ps_cls_settings ) > 0 ) {
	$ps_setting_wrapper_class = implode( ' ', $ps_cls_settings );
}




if ( is_array( $products ) && count( $products ) > 0 ) {
	$classes = isset( $field['cssready'] ) ? implode( ' ', $field['cssready'] ) : '';
	
	?>
    <div class="<?php echo $deviceType; ?>shop_table wfacp-product-switch-panel <?php echo $classes . ' ' . $ps_setting_wrapper_class; ?>" cellspacing="0" id="product_switching_field">
		<?php
		do_action( 'wfacp_before_product_switcher_html' );
		?>
        <div class="wfacp-product-switch-title">

            <div class="product-remove"><?php _e( $field['label'], 'woocommerce' ); ?> </div>
            <div class="wfacp_qty_price_wrap">
				<?php if ( ! $hide_quantity_switcher ) { ?>
                    <div class="product-quantity"><?php _e( 'Qty', 'woocommerce' ); ?></div>
				<?php } ?>
                <div class="product-name"><?php _e( 'Price', 'woocommerce' ); ?></div>
            </div>
        </div>
        <div class="wfacp_product_switcher_container">
			<?php
			global $wfacp_products_attributes_data;
			$counter                           = 0;
			$best_value_counter                = 1;
			$product_switcher_description_html = '';
			$cart_count                        = count( WC()->cart->get_cart_contents() );

			foreach ( $products as $item_key => $product_obj ) {
				$product_data = $products_data[ $item_key ];


				if ( isset( $switcher_settings['products'][ $item_key ]['whats_included'] ) ) {
					$product_data['whats_included'] = $switcher_settings['products'][ $item_key ]['whats_included'];
				}
				$product_data['item_key'] = $item_key;
				$is_checked               = '';
				if ( isset( $product_data['is_added_cart'] ) ) {
					$is_checked = 'checked="checked"';
				}
				$product_data['is_checked'] = $is_checked;
				if ( ! isset( $is_sold_individually_arr['total_products'] ) ) {
					$is_sold_individually_arr['total_products'] = 1;
				} else {
					$is_sold_individually_arr['total_products'] ++;
				}
				if ( $product_obj->is_sold_individually() ) {
					if ( ! isset( $is_sold_individually_arr['is_sold_individual'] ) ) {
						$is_sold_individually_arr['is_sold_individual'] = 1;
					} else {
						$is_sold_individually_arr['is_sold_individual'] ++;
					}
				} else {
					if ( ! isset( $is_sold_individually_arr['not_sold_individual'] ) ) {
						$is_sold_individually_arr['not_sold_individual'] = 1;
					} else {
						$is_sold_individually_arr['not_sold_individual'] ++;
					}
				}
				$product_data['hide_product_image'] = $hide_product_image;
				WFACP_Common::get_product_switcher_row( $product_data, $item_key, $type, $switcher_settings );
				$product_switcher_description_html .= WFACP_Common::get_product_switcher_row_description( $product_data, $product_obj, $switcher_settings, true );
				$best_value_counter ++;
			}
			?>
        </div>
		<?php
		if ( '' != $product_switcher_description_html && false == $hide_whats_included ) {
			?>
            <div class="wfacp_whats_included ">
				<?php
				echo $sec_heading ? '<h3>' . $sec_heading . '</h3>' : '';
				echo $product_switcher_description_html;
				?>
            </div>
			<?php
		}
		do_action( 'wfacp_after_product_switcher_html' );
		?>
    </div>
	<?php

}

$total_products = 0;
if ( isset( $is_sold_individually_arr['total_products'] ) ) {
	$total_products = $is_sold_individually_arr['total_products'];
}


if ( ( is_array( $is_sold_individually_arr ) && count( $is_sold_individually_arr ) > 0 ) ) {
	if ( isset( $is_sold_individually_arr['is_sold_individual'] ) && $total_products > 0 ) {
		if ( $total_products == $is_sold_individually_arr['is_sold_individual'] ) {
			?>
            <style>.wfacp-product-switch-title .product-quantity {
                    display: none
                }
                body .wfacp_main_form .wfacp_qty_price_wrap .product-name {
                    width: 100%;
                    padding-left: 0;
                }
                body .wfacp_main_form #product_switching_field.wfacp_for_desktop_tablet .wfacp_product_switcher_col_2{
                    padding-right: 0;
                }
            </style>
			<?php
		}
	}
}

if ( wp_doing_ajax() ) {
	return;
}
global $wfacp_products_attributes_data;
?>
<script>
    var wfacp_variation_attributes_data =<?php echo wp_json_encode( $wfacp_products_attributes_data ); ?>;
</script>