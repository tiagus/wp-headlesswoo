<?php
defined( 'ABSPATH' ) || exit;
/**
 * @var $product_obj WC_Product;
 */



if ( isset( $data['is_added_cart'] ) && isset( $data['whats_included'] ) && ! isset( WC()->cart->removed_cart_contents[ $data['is_added_cart'] ] ) ) {
	?>
    <div class="wfacp_product_switcher_description" data-item-key="<?php echo $data['item_key']; ?>">
		<?php
		if ( isset( $data['title'] ) && ! empty( $data['title'] ) ) {
			echo '<h4>' . $data['title'] . '</h4>';
		}

		if ( isset( $data['whats_included'] ) && ! empty( $data['whats_included'] ) ) {
			echo '<div class="wfacp_description">' . apply_filters( 'wfacp_the_content', $data['whats_included'], $data ) . '</div>';
		}
		?>
    </div>
	<?php
}
