<?php
defined( 'ABSPATH' ) || exit;
?>
<style>
    .wfacp_input_fields {
        margin-top: 25px;
    }
</style>
<div id="wfacp_layout_container">
    <div class="wfacp_p20 wfacp_box_size">
        <div class="wfacp_wrap_inner wfacp_wrap_inner_offers <?php echo ( isset( $_REQUEST['section'] ) ) ? 'wfacp_wrap_inner_' . $_REQUEST['section'] : ''; ?>" style="margin-left: 0;">

            <div class="wfacp_wrap_r" v-if="wfacp.tools.ol(wfacp_data.products)==0 && false==wfacp.is_global_checkout()">
				<?php include_once __DIR__ . '/products/no-product.php'; ?>
            </div>
            <div class="wfacp_wrap_r" v-if="wfacp.tools.ol(wfacp_data.products)>0 || wfacp.is_global_checkout()">
                <div class="wfacp_fsetting_table_head">
                    <div class="wfacp_fsetting_table_head_in wfacp_clearfix">
                        <div class="wfacp_fsetting_table_title">
                            <strong><span class="wfacp_template_friendly_name">{{steps[current_step]['friendly_name']}}</span></strong>
                        </div>
                        <div class="bwf_ajax_save_buttons bwf_form_submit">
                            <span class="wfacp_spinner spinner"></span>
                            <a href="javascript:void(0)" class="wfacp_save_btn_style" v-on:click="save_template()"><?php _e( 'Save Form', 'woofunnels-aero-checkout' ); ?></a>
                        </div>
                    </div>
                </div>


                <div class="template_field_holder" style="min-height: 500px">
                    <div class="template_steps_container" style="float: left;width:70%">

                        <div v-for="(d,m) in global_dependency_messages" v-if="d.show=='yes'" v-bind:class="'wfacp_field_dependency_messages '+d.type">
                            <div class="wfacp_dependency_alert_icon">
                                <img src="<?php echo WFACP_PLUGIN_URL . '/admin/assets/img/form-tab/danger.svg' ?>" alt="">
                            </div>
                            <div class="notice_msg_wrap">
                                <p v-html="d.message"></p>
                            </div>
                            <span v-if="d.dismissible==true" v-on:click="remove_dependency_messages(m)" class="wfacp_close_icon">x</span>
                        </div>
						<?php include_once __DIR__ . '/fields/field_container.php'; ?>
                        <div class="wfacp_form_note_sec">
                            <p><i>
									<?php
									_e( 'Note: Payment Information containing gateways will be automatically added to the end of order form' );
									?>
                                </i>
                            </p>
                        </div>
                    </div>
                    <div class="template_field_selecter" style="float: right; width:28%">
						<?php include_once __DIR__ . '/fields/input_fields.php'; ?>
                    </div>
                    <div style="clear: both"></div>
                </div>

                <div class="bwf_ajax_btn_bottom_container bwf_form_submit wfacp_btm_grey_area wfacp_clearfix">
                    <div class="wfacp_btm_save_wrap wfacp_clearfix">
                        <span class="wfacp_spinner spinner"></span>
                        <a href="javascript:void(0)" class="wfacp_save_btn_style" v-on:click="save_template()"><?php _e( 'Save Form', 'woofunnels-aero-checkout' ); ?></a>
                    </div>
                </div>
            </div>
            <div style="clear: both"></div>
        </div>
    </div>

</div>
<?php include_once __DIR__ . '/fields/models.php'; ?>
