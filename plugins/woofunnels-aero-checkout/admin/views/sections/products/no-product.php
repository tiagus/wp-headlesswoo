<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wfacp_welcome_wrap">
    <div class="wfacp_welcome_wrap_in">
        <div class="wfacp_first_product">
            <div class="wfacp_welc_head">
                <div class="wfacp_welc_icon"><img src="<?php echo WFACP_PLUGIN_URL; ?>/admin/assets/img/clap.png" alt="" title=""/></div>
                <div class="wfacp_welc_title"> <?php _e( 'No Product Available', 'woofunnels-aero-checkout' ); ?>
                </div>
            </div>
            <div class="wfacp_welc_text">
                <p><?php _e( 'No product associated with this checkout. You need to add minimum one product to generate preview', 'woofunnels-aero-checkout' ); ?></p>
            </div>
            <a class="wfacp_step wfacp_button_add wfacp_button_inline wfacp_modal_open wfacp_welc_btn" href="
			<?php
			echo add_query_arg( [
				'page'     => 'wfacp',
				'wfacp_id' => WFACP_Common::get_id(),
				'section'  => 'product',
			], admin_url( 'admin.php' ) )
			?>
			">
				<?php _e( 'Go To Products', 'woofunnels-aero-checkout' ); ?>
            </a>
        </div>
    </div>
</div>
