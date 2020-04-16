<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$instance = WFACP_Core()->customizer->get_template_instance();
$data     = $instance->get_checkout_fields();

$field = isset( $data['advanced']['order_summary'] ) ? $data['advanced']['order_summary'] : [];

$colspan_attr = '';
unset( $data );

if ( apply_filters( 'wfacp_cart_show_product_thumbnail', false ) ) {
	$colspan_attr1    = ' colspan="2"';
	$colspan_attr     = apply_filters( 'wfacp_order_summary_cols_span', $colspan_attr1 );
	$cellpadding_attr = ' cellpadding="20"';
}
$field     = apply_filters( 'wfacp_before_order_summary_html', $field );
$total_col = 2;

$section_key = '';

$cart_data = $this->customizer_fields_data['wfacp_form_cart'];

$rbox_border_type = '';
if ( isset( $cart_data['advance_setting']['rbox_border_type'] ) && $cart_data['advance_setting']['rbox_border_type'] != '' ) {
	$rbox_border_type = $cart_data['advance_setting']['rbox_border_type'];
}
?>
<div class="wfacp_form_cart <?php echo $rbox_border_type; ?> div_wrap_sec">
    <div class="wfacp_order_summary_layout_9">
		<?php
		if ( isset( $cart_data['heading_section']['heading'] ) && $cart_data['heading_section']['heading'] != '' && isset( $cart_data['heading_section']['enable_heading'] ) && $cart_data['heading_section']['enable_heading'] == true ) {
			$align_text         = $cart_data['heading_section']['heading_talign'];
			$font_weight        = $cart_data['heading_section']['heading_font_weight'];
			$heading_fs_desktop = $cart_data['heading_section']['heading_fs']['desktop'];
			$heading_fs_tablet  = $cart_data['heading_section']['heading_fs']['tablet'];
			$heading_fs_mobile  = $cart_data['heading_section']['heading_fs']['mobile'];
			?>
            <h2 class="wfacp-list-title wfacp_section_title <?php echo $align_text . ' ' . $font_weight; ?>">
				<?php echo isset( $field['label'] ) ? $field['label'] : __( 'Order Summary', 'woofunnels-aero-checkout' ); ?>
            </h2>

			<?php
		}
		?>

		<?php include __DIR__ . '/order-review.php'; ?>
		<?php include __DIR__ . '/form-coupon.php'; ?>


		<?php include __DIR__ . '/order-total.php'; ?>

    </div>
</div>
