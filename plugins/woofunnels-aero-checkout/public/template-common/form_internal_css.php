<?php
defined( 'ABSPATH' ) || exit;
?>
<style>
    .wfacp_page.<?php echo $template_type; ?> {
        display: none;
    }

    html {
        overflow: auto !important;
    }

    span.wfacp_input_error_msg {
        color: red;
        font-size: 13px;
    }

    .wfacp_page.single_step {
        display: block;
    }

    .wfacp_payment {
        display: block;

    }

    .wfacp_payment.wfacp_hide_payment_part {
        visibility: hidden;
        position: fixed;
        z-index: -600;
        left: -200%;
    }

    .wfacp_payment.wfacp_show_payment_part {
        visibility: visible;
    }

    .wfacp_error_message {
        display: none !important;
        clear: both;
        padding: 0 45px;
        color: red;
    }

    .checkout.woocommerce-checkout .woocommerce-NoticeGroup.woocommerce-NoticeGroup-checkout {
        /*display: none;*/
    }

    .wfacp_page.<?php echo $current_step; ?> .wfacp_payment {
        display: block;
    }

    .wfacp_page.<?php echo $current_step; ?> .wfacp_next_page_button {
        display: none;
    }

    p#shipping_same_as_billing_field .optional {
        display: none;
    }

    p#billing_same_as_shipping_field .optional {
        display: none;
    }

    .wfacp_shipping_fields.wfacp_shipping_field_hide {
        display: none !important;
    }

    .wfacp_billing_fields.wfacp_billing_field_hide {
        display: none !important;
    }

   

    span.optional {
        display: none !important;
    }

    span.wfacp_required_field_message {
        display: none;
    }

    .woocommerce-invalid-required-field span.wfacp_required_field_message {
        display: inline;
    }

    .wfacp_country_field_hide {
        display: none !important;
    }

    .wfacp_main_form .wfacp_shipping_table tr.shipping.wfacp_single_methods td.wfacp_shipping_package_name > p {
        padding: 0 0 10px;
    }

    .wfacp_main_form .wfacp_shipping_table tr.shipping.wfacp_single_methods td.wfacp_shipping_package_name {
        padding: 0 0 15px;
    }

    <?php
    if(WFACP_Common::is_cart_is_virtual()){
    ?>
    #shipping_same_as_billing_field {
        display: none;
    }

    <?php
    }
    ?>

</style>
<?php
do_action( 'wfacp_internal_css', $selected_template_slug );
?>
