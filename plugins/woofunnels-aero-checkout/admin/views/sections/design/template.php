<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="design_container">
    <div class="template_wrapper" v-for="(templates,type) in designs" v-if="(current_template_type==type) && (wfacp.tools.ol(templates)>0)">
        <div class="wfacp_template_box" v-for="(template,slug) in templates" v-on:data-slug="slug" v-bind:data-select="(selected==slug)?'selected':''">
            <div class="wfacp_template_box_inner">
                <a href="javascript:void(0)" class="wfacp_template_img_cover">
                    <div class="wfacp_overlay" v-on:click="showPopup(template)">
                        <div class="wfacp_overlay_icon"><i class="dashicons dashicons-visibility"></i></div>
                    </div>
                    <div class="wfacp_template_thumbnail">
                        <div class="wfacp_img_thumbnail ">
                            <img v-bind:src="template.thumbnail">
                        </div>
                    </div>
                </a>
                <div class="wfacp_template_btm_strip wfacp_clearfix">
                    <div class="wfacp_template_name" id=""></div>
                    <b>{{template.name}}</b>
                    <div class="wfacp_template_button">
                        <a v-if="selected==slug" v-bind:href="wfacp_data.curtomize_url" target="_blank" class="button-primary"><?php _e( 'Customize', 'woofunnels-aero-checkout' ); ?></a>
                        <span v-else href="javascript:void(0)" class="button-primary" v-on:click="setTemplate(slug,type)"><?php echo _e( 'Activate', 'woofunnels-aero-checkout' ); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
</div>
