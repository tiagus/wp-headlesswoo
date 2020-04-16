<?php
defined( 'ABSPATH' ) || exit;
?>
<?php

//$billing_fields  = WFACP_Common::get_single_address_fields()['fields_options'];
//$shipping_fields = WFACP_Common::get_single_address_fields( 'shipping' )['fields_options'];

?>
<div class="wfacp_global_settings">

    <div class="wrap wfacp_funnels_listing wfacp_global">
        <div class="wfacp_page_heading"><img class="aero_checkout_logo" src="<?php echo WFACP_PLUGIN_URL; ?>/admin/assets/img/woo_checkout_logo.png" alt="Aero Checkout" style="max-height: 50px;"/>
        </div>
        <div class="wfacp_clear_10"></div>
        <div class="wfacp_head_bar">
            <div class="wfacp_bar_head"><?php _e( 'Global Settings' ); ?></div>
            <a href="<?php echo admin_url( 'admin.php?page=wfacp' ); ?>" class="button button-green button-large"><?php echo __( '<< Back to Listing', 'woofunnels-aero-checkout' ); ?></a>
        </div>
        <div class="wfacp_clear_10"></div>
        <div id="poststuff" class=" wfacp_global_settings_wrap wfacp_page_col2_wrap">
            <div class="wfacp_page_left_wrap" id="wfacp_global_setting_vue">
                <div class="wfacp_loader"><span class="spinner"></span></div>
                <div class="wfacp-product-tabs-view-vertical wfacp-product-widget-tabs">

                    <div class="wfacp-product-widget-container">
                        <div class="wfacp-product-tabs wfacp-tabs-style-line" role="tablist">
                            <div class="wfacp-product-tabs-wrapper wfacp-tab-center">
                                <div class="wfacp-tab-title wfacp-tab-desktop-title wfacp_tracking_analytics " id="wfacp-tracking-analytics" data-tab="1" role="tab" aria-controls="wfacp-tracking-analytics">
									<?php _e( 'Tracking & Analytics', 'woofunnels-aero-checkout' ); ?>
                                </div>

                                <div class="wfacp-tab-title wfacp-tab-desktop-title wfacp_miscellaneous " id="wfacp-miscellaneous" data-tab="2" role="tab" aria-controls="wfacp-miscellaneous">
									<?php _e( 'Miscellaneous', 'woofunnels-aero-checkout' ); ?>
                                </div>
                                <div class="wfacp-tab-title wfacp-tab-desktop-title wfacp_global_css " id="wfacp-global_css" data-tab="3" role="tab" aria-controls="wfacp-global_css">
									<?php _e( 'Global Custom CSS', 'woofunnels-aero-checkout' ); ?>
                                </div>
                                <div class="wfacp-tab-title wfacp-tab-desktop-title wfacp_global_external_script " id="wfacp-global_external_script" data-tab="4" role="tab" aria-controls="wfacp-global_external_script">
		                            <?php _e( 'External Script', 'woofunnels-aero-checkout' ); ?>
                                </div>
                            </div>
                            <div class="wfacp-product-tabs-content-wrapper">
                                <div class="wfacp_global_setting_inner">
                                    <div class="wfacp_global_container" id="wfacp_global_settings">
                                        <form id="modal-global-settings-form" class="wfacp_forms_global_settings" data-bwf-action="global_settings" v-on:submit.prevent="onSubmit">
                                            <div class="wfacp_vue_forms">
                                                <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
                                                <fieldset>
                                                    <div class="bwf_form_submit" style="display: inline-block">
                                                        <input type="submit" class="wfacp_submit_btn_style" value="<?php _e( 'Save Settings', 'woofunnels-aero-checkout' ); ?>"/>
                                                        <span class="wfacp_spinner spinner" style="float: right"></span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/global/model.php'; ?>
