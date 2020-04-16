<?php
defined( 'ABSPATH' ) || exit;
?>
<?php include_once WFACP_PLUGIN_DIR . '/includes/class-wfacp-post-table.php'; ?>
<div class="wrap wfacp_funnels_listing wfacp_global">
    <div class="wfacp_page_heading"><img class="aero_checkout_logo" src="<?php echo WFACP_PLUGIN_URL; ?>/admin/assets/img/woo_checkout_logo.png" alt="Aero Checkout" style="max-height: 50px;"/>
    </div>
    <div class="wfacp_clear_10"></div>
    <div class="wfacp_head_bar">
        <div class="wfacp_bar_head"><?php _e( 'Aero Checkout Pages', 'woofunnels-aero-checkout' ); ?></div>
        <a href="javascript:void(0)" class="button button-green button-large" data-izimodal-open="#modal-checkout-page" data-iziModal-title="Create New Checkout page" data-izimodal-transitionin="fadeInDown"><?php echo __( 'Add New', 'woofunnels-aero-checkout' ); ?></a>
        <a href="
			<?php
		echo add_query_arg( [
			'tab' => 'settings',
		], admin_url( 'admin.php?page=wfacp' ) );
		?>
" class="button button-green button-large"><?php echo __( 'Global Settings', 'woofunnels-aero-checkout' ); ?></a>
    </div>

    <div id="poststuff">
        <div class="inside">
            <div class="wfacp_page_col2_wrap wfacp_clearfix">
                <div class="wfacp_page_left_wrap">
                    <form method="GET">
                        <input type="hidden" name="page" value="wfacp"/>
                        <input type="hidden" name="status" value="<?php echo( isset( $_GET['status'] ) ? $_GET['status'] : '' ); ?>"/>
						<?php

						$table = new WFACP_Post_Table();
						$table->render_trigger_nav();
						$table->search_box( 'Search' );
						$table->data = WFACP_Common::get_post_table_data();
						$table->prepare_items();
						$table->display();
						?>
                    </form>
					<?php $table->order_preview_template(); ?>
                </div>
                <div class="wfacp_page_right_wrap">
					<?php do_action( 'wfacp_page_right_content' ); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/global/model.php'; ?>
