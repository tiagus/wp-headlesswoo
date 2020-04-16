<?php
defined( 'ABSPATH' ) || exit;
?>
<template>
    <div>
        <multiselect v-model="selected_coupon" id="coupon" label="coupon" track-by="coupon" placeholder="Type to search" open-direction="bottom" :options="coupons" :multiple="<?php echo( 'false' ); ?>" :searchable="true" :loading="isLoading" :internal-search="true" :clear-on-select="false" :close-on-select="true" :options-limit="300" :limit="3" :max-height="600" :show-no-results="true" :hide-selected="true" @search-change="asyncFind">
            <template slot="clear" slot-scope="props"></template>
            <span slot="noResult"><?php echo __( 'Oops! No coupons found. Consider changing the search query.', 'woofunnels-aero-checkout' ); ?></span>
        </multiselect>
    </div>
</template>
<div id="wfacp_setting_container" class="wfacp_inner_setting_wrap">
    <div class="wfacp_p20 wfacp_box_size clearfix">
        <div class="wfacp_wrap_inner wfacp_wrap_inner_offers" style="margin-left: 0px;">
            <div class="wfacp_wrap_r" v-if="wfacp.tools.ol(wfacp_data.products)==0 && false==wfacp.is_global_checkout()">
				<?php include_once __DIR__ . '/products/no-product.php'; ?>
            </div>
            <div class="wfacp_wrap_r" v-if="wfacp.tools.ol(wfacp_data.products)>0 || wfacp.is_global_checkout()">
                <form @change="changed()" v-on:submit.prevent="save()">
                    <div class="wfacp_fsetting_table_head">
                        <div class="wfacp_fsetting_table_head_in wfacp_clearfix">
                            <div class="wfacp_fsetting_table_title">
                                <strong><?php _e( 'Settings', 'woofunnels-aero-checkout' ); ?></strong>
                            </div>
                            <div class="bwf_ajax_save_buttons bwf_form_submit">
                                <span class="wfacp_spinner spinner"></span>
                                <button type="submit" class="wfacp_save_btn_style" style="margin: 0px;width: 110px"><?php _e( 'Save Settings', 'woofunnels-aero-checkout' ); ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="wfacp_settings_sections">
                        <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
                    </div>
                    <div class="bwf_ajax_btn_bottom_container bwf_form_submit wfacp_btm_grey_area wfacp_clearfix">
                        <div class=" wfacp_btm_save_wrap wfacp_clearfix">
                            <span class="wfacp_spinner spinner"></span>
                            <button type="submit" class="wfacp_save_btn_style" style="margin: 0px;width: 110px"><?php _e( 'Save Settings', 'woofunnels-aero-checkout' ); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
