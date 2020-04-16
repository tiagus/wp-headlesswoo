<?php
$instance = WFACP_Core()->customizer->get_template_instance();
add_filter( 'wp_get_attachment_image_attributes', 'WFACP_Common::remove_src_set' );

?>
<table class="shop_table woocommerce-checkout-review-order-table_layout_9 wfacp_order_sum wfacp_template_9_cart_item_details <?php echo $instance->get_template_slug(); ?>">

    <tbody>
	<?php
	do_action( 'woocommerce_review_order_before_cart_contents' );

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$_product = apply_filters( 'woocommerce_cart_item_product1', $cart_item['data'], $cart_item, $cart_item_key );
		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			?>
            <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<?php
				if ( apply_filters( 'wfacp_cart_show_product_thumbnail', false ) ) {
					$thumbnail = WFACP_Common::get_product_image( $_product, [ 100, 100 ], $cart_item, $cart_item_key );
					?>
                    <td class="product-image">
                        <div class="wfacp-pro-thumb">
                            <div class="wfacp-qty-ball">
                                <div class="wfacp-qty-count">
                                    <span class="wfacp-pro-count"><?php echo $cart_item['quantity']; ?></span>
                                </div>
                            </div>
							<?php echo $thumbnail; ?>
                        </div>
                    </td>
				<?php } ?>
                <td class="product-name">

                    <span class="wfacp_order_summary_item_name"><?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ); ?></span>
					<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
					<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                </td>
                <td class="product-total">
					<?php
					if ( in_array( $_product->get_type(), WFACP_Common::get_subscription_product_type() ) ) {
						echo WFACP_Common::display_subscription_price( $_product, $cart_item, $cart_item_key );
					} else {
						echo apply_filters( 'woocommerce_cart_item_subtotal', WFACP_Common::get_product_subtotal( $_product, $cart_item ), $cart_item, $cart_item_key );
					}
					?>
                </td>
            </tr>
			<?php
		}
	}
	?>
    </tbody>
</table>
<?php
remove_filter( 'wp_get_attachment_image_attributes', 'WFACP_Common::remove_src_set' );
?>

