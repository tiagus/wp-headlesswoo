<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wfacp_success_modal" style="display: none" id="modal-saved-data-success" data-iziModal-icon="icon-home"></div>
<div class="wfacp_izimodal_default" style="display: none" id="modal-checkout-page">
    <div class="sections">
        <form class="wfacp_add_funnel" data-bwf-action="add_new_funnel" id="add-new-form" v-on:submit.prevent="onSubmit">
            <div class="wfacp_vue_forms" id="part-add-funnel">
                <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
            </div>
            <fieldset>
                <div class="bwf_form_submit">
                    <button type="submit" class="wfacp_submit_btn_style" value="add_new">{{btn_name}}</button>
                </div>
                <div class="wfacp_form_response"></div>
            </fieldset>
        </form>
        <div class="wfacp-funnel-create-success-wrap">
            <div class="wfacp-funnel-create-success-logo">
                <!--                <i class="dashicons dashicons-yes"></i>-->
                <div class="swal2-icon swal2-success swal2-animate-success-icon" style="display: flex;">
                    <div class="swal2-success-circular-line-left" style="background-color: rgb(255, 255, 255);"></div>
                    <span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>
                    <div class="swal2-success-ring"></div>
                    <div class="swal2-success-fix" style="background-color: rgb(255, 255, 255);"></div>
                    <div class="swal2-success-circular-line-right" style="background-color: rgb(255, 255, 255);"></div>
                </div>
            </div>
            <div class="wfacp-funnel-create-message"><?php _e( 'Page Created Successfully. Launching  Editor...', 'woofunnels-aero-checkout' ); ?></div>
        </div>
    </div>
</div>
<style>
    .wfacp_checkout_url .field-wrap .wrapper:before {
        content: "<?php echo WFACP_Common::base_url(); ?>";
    }

    .wfacp_checkout_url .field-wrap .wrapper .form-control {
        width: 150px;
    }
</style>
