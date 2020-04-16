<?php
defined( 'ABSPATH' ) || exit;
$selected_template_slug = $instance->get_template_slug();
$heading_talign         = '';
$heading_font_weight    = '';
$sub_heading_talign     = '';
if ( isset( $formData['heading_section']['heading_talign'] ) ) {
	$heading_talign = $formData['heading_section']['heading_talign'];
}
if ( isset( $formData['heading_section']['heading_font_weight'] ) ) {
	$heading_font_weight = $formData['heading_section']['heading_font_weight'];
}

if ( isset( $formData['sub_heading_section']['heading_talign'] ) ) {
	$sub_heading_talign = $formData['sub_heading_section']['heading_talign'];
}
if ( isset( $formData['sub_heading_section']['heading_font_weight'] ) ) {
	$sub_heading_font_weight = $formData['sub_heading_section']['heading_font_weight'];
}


$payment_des = '';
if ( isset( $formData['form_data']['text_below_placeorder_btn'] ) && $formData['form_data']['text_below_placeorder_btn'] != '' ) {
	$payment_des = $formData['form_data']['text_below_placeorder_btn'];
}

$border_cls = '';

if ( isset( $formData['border']['form_head']['border-style'] ) && $formData['border']['form_head']['border-style'] != '' ) {
	$border_cls = $formData['border']['form_head']['border-style'];
}
$payment_methods_heading = '';
if ( isset( $formData['form_data']['payment_methods_heading'] ) && $formData['form_data']['payment_methods_heading'] != '' ) {
	$payment_methods_heading = $formData['form_data']['payment_methods_heading'];
}
$payment_methods_sub_heading = '';
if ( isset( $formData['form_data']['payment_methods_sub_heading'] ) && $formData['form_data']['payment_methods_sub_heading'] != '' ) {
	$payment_methods_sub_heading = $formData['form_data']['payment_methods_sub_heading'];
}
$hide_payment_cls = '';
if ( $current_step !== 'single_step' ) {
	$hide_payment_cls = 'wfacp_hide_payment_part';
}

if ( WFACP_Core()->public->is_paypal_express_active_session ) {
	$hide_payment_cls = '';
}
if ( WFACP_Core()->public->is_amazon_express_active_session ) {
	$hide_payment_cls = '';
}



?>
<div class="wfacp-section wfacp_payment <?php echo $hide_payment_cls; ?> form_section_your_order_0_<?php echo $selected_template_slug; ?> wfacp-section-titl ex wfacp-hg-by-box">
    <div style="clear: both;"></div>
    <div class="wfacp-comm-title <?php echo $border_cls; ?>">
        <h2 class="wfacp_section_heading wfacp_section_title <?php echo $heading_talign . ' ' . $heading_font_weight; ?> ">
			<?php echo $payment_methods_heading; ?></h2>
        <h4 class="<?php echo $sub_heading_talign . '  ' . $sub_heading_font_weight; ?>"><?php echo $payment_methods_sub_heading; ?></h4>
    </div>
	<?php do_action( 'wfacp_checkout_before_order_review' ); ?>
    <div class="woocommerce-checkout-review-order wfacp-oder-detail clearfix">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
		<?php
		if ( isset( $payment_des ) && $payment_des != '' ) {
			?>
            <div class="wfacp-payment-dec">
				<?php echo $payment_des; ?>
            </div>
			<?php
		}
		?>
    </div>
	<?php do_action( 'wfacp_checkout_after_order_review' ); ?>
</div>
