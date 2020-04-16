<?php
defined( 'ABSPATH' ) || exit;
?>
<div id="wfacp_product_container">
    <div class="wfacp_p20 wfacp_box_size">
        <div class="wfacp_wrap_inner wfacp_wrap_inner_offers" style="margin-left: 0px;">

            <div class="wfacp_wrap_r">
                <div class="wfacp_fsetting_table_head">
                    <div class="wfacp_fsetting_table_head_in wfacp_clearfix">
                        <div class="wfacp_fsetting_table_title">
                            <strong><?php _e( 'Products', 'woofunnels-aero-checkout' ); ?></strong>
                        </div>
                        <div class="bwf_ajax_save_buttons bwf_form_submit" v-if="!isEmpty()">
                            <span class="wfacp_spinner spinner"></span>
                            <a href="javascript:void(0)" class="wfacp_save_btn_style" v-on:click="save_products()"><?php _e( 'Save Products', 'woofunnels-aero-checkout' ); ?></a>
                        </div>
                    </div>
                </div>
                <div class="products_container" v-if="!isEmpty()">
					<?php include_once __DIR__ . '/products/table.php'; ?>
                </div>
				<?php include_once __DIR__ . '/products/add-new.php'; ?>
                <div class="product_add_new" v-if="!isEmpty()">
                    <div class="wfacp_steps">
                        <div class="wfacp_step wfacp_button_add wfacp_modal_open" data-izimodal-open="#modal-add-product">
                            + <?php _e( 'Add Product', 'woofunnels-aero-checkout' ); ?>
                        </div>
                    </div>
                </div>
                <div class="wfacp_learn_how_wrap">

                    <a href="https://buildwoofunnels.com/docs/aerocheckout/getting-started/replace-default-checkout/" target="_blank" style="font-style: italic;"><?php _e( 'Learn how to set this page as a global checkout', 'woofunnels-aero-checkout' ); ?>
                        <span class="dashicons dashicons-external"></span>
                    </a>

                </div>
                <div class="product_settings" style="background: #fff" v-if="wfacp.tools.ol(products)>0">
                    <div class="product_settings_title"><?php _e( 'Settings' ); ?></div>
                    <div class="product_settings_checkout_behaviour">
                        <div class="product_settings_checkout_behavior_heading">
                            <strong><?php _e( 'Product Selection', 'woofunnels-aero-checkout' ); ?></strong>
                        </div>
                        <div class="product_settings_checkout_behavior_setting">
                            <p>
                                <label><input type="radio" v-model="add_to_cart_setting" name="add_to_cart_setting" value="2"><?php _e( 'Restrict buyer to select only one of the above products (e.g. when selling similar products with different pricing plans or quantity)', 'woofunnels-aero-checkout' ); ?>
                                </label>
                            </p>
                            <p>
                                <label><input type="radio" v-model="add_to_cart_setting" name="add_to_cart_setting" value="3"><?php _e( 'Allow buyer to select any of the above product(s) (e.g. when selling multiple products)', 'woofunnels-aero-checkout' ); ?>
                                </label>
                            </p>
                            <p>
                                <label><input type="radio" v-model="add_to_cart_setting" name="add_to_cart_setting" value="1"><?php _e( 'Force sell all of the above product(s) (e.g. when selling a fixed price bundle)', 'woofunnels-aero-checkout' ); ?>
                                </label>
                            </p>
                        </div>
                    </div>


                </div>

                <div class=" bwf_ajax_btn_bottom_containerbwf_form_submit wfacp_btm_grey_area wfacp_clearfix" v-if="!isEmpty()">
                    <div class=" wfacp_btm_save_wrap wfacp_clearfix">
                        <span class="wfacp_spinner spinner"></span>
                        <a href="javascript:void(0)" class="wfacp_save_btn_style" v-on:click="save_products()"><?php _e( 'Save Products', 'woofunnels-aero-checkout' ); ?></a>
                    </div>
                </div>
            </div>
            <div class="wfacp_clear"></div>
        </div>
    </div>

</div>
<?php
include_once __DIR__ . '/products/models.php'
?>
