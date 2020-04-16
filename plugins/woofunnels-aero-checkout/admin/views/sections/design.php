<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div id="wfacp_design_container">
    <div class="wfacp_p20 wfacp_box_size">
        <div class="wfacp_wrap_r" v-if="wfacp.tools.ol(wfacp_data.products)==0 && false==wfacp.is_global_checkout()">
			<?php include_once __DIR__ . '/products/no-product.php'; ?>
        </div>
        <div class="wfacp_wrap_inner wfacp_wrap_inner_offers" v-if="wfacp.tools.ol(wfacp_data.products)>0 || wfacp.is_global_checkout()">
            <div class="wfacp_wrap_l">
                <div class="wfacp_steps_title"><?php _e( 'Choose your checkout page look & feel', 'woofunnels-aero-checkout' ); ?></div>
                <div class="wfacp_steps">

                    <div class="wfacp_step wfacp_button_add wfacp_modal_open" v-for="(design_name,type) in design_types" v-if="wfacp.tools.ol(designs[type])>0" v-on:click="setTemplateType(type)" v-bind:data-select="(current_template_type==type)?'selected':''">
                        <span class="custom_design_radio"></span>
                        {{design_name}}
                    </div>
                </div>
            </div>
            <div class="wfacp_wrap_r">
                <div class="wfacp_fsetting_table_head">
                    <div class="wfacp_fsetting_table_head_in wfacp_clearfix">
                        <div class="wfacp_fsetting_table_title">
                            <strong><?php _e( 'Customize Design for', 'woofunnels-aero-checkout' ); ?>&nbsp;<?php echo $wfacp_post->post_title; ?></strong>
                        </div>
                        <div class="bwf_ajax_save_buttons bwf_form_submit" v-if="" style="display: none">
                            <span class="wfacp_spinner spinner"></span>
                            <a href="javascript:void(0)" class="wfacp_save_btn_style" v-on:click="save()"><?php _e( 'Save Design', 'woofunnels-aero-checkout' ); ?></a>
                        </div>
                    </div>
                </div>
                <div class="wfacp_template_box_holder">
                    <div class="wfacp_template_holder_head"><h2 class="wfacp_screen_title"><?php _e( 'Template', 'woofunnels-aero-checkout' ); ?>
                            <span>{{designs[selected_type][selected]['name']}}</span></h2>
                        <div class="wfacp_screen_desc"><?php _e( 'Customize current selected template or activate a new template.', 'woofunnels-aero-checkout' ); ?></div>
                    </div>
					<?php include_once __DIR__ . '/design/template.php'; ?>
                </div>

				<?php do_action( 'wfacp_builder_design_after_template' ); ?>

                <div  class="bwf_ajax_btn_bottom_container bwf_form_submit wfacp_btm_grey_area wfacp_clearfix" v-if="" style="display: none;">
                    <div class=" wfacp_btm_save_wrap wfacp_clearfix">
                        <span class="wfacp_spinner spinner"></span>
                        <a href="javascript:void(0)" class="wfacp_save_btn_style" v-on:click="save()"><?php _e( 'Save Design', 'woofunnels-aero-checkout' ); ?></a>
                    </div>
                </div>
            </div>
            <div class="wfacp_clear"></div>
        </div>
    </div>
	<?php include_once __DIR__ . '/design/models.php'; ?>
</div>
