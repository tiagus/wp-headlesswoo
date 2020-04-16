<?php
defined( 'ABSPATH' ) || exit;
?>
<?php
/**
 * @var $this WFACP_admin
 */
$wfacp_id      = WFACP_Common::get_id();
$wfacp_section = WFACP_Common::get_current_step();
$wfacp_post    = get_post( $wfacp_id );

$localize_data = $this->get_localize_data();

$selected_design = $localize_data['design']['selected_type'];

if ( is_null( $wfacp_post ) ) {
	return;
}
$steps    = WFACP_Common::get_admin_menu();
$products = WFACP_Common::get_page_product( WFACP_Common::get_id() );
?>
<div class="wfacp_body wfacp_funnels" id="wfacp_control" data-id="<?php echo $wfacp_id; ?>">

    <div class="wfacp_fixed_header">
        <div class="wfacp_p20_wrap wfacp_box_size wfacp_table">
            <div class="wfacp_head_m wfacp_tl wfacp_table_cell">
                <div class="wfacp_head_mr" data-status="live">
                    <div class="funnel_state_toggle wfacp_toggle_btn">
                        <input name="offer_state" id="state_<?php echo $wfacp_id; ?>" data-id="<?php echo $wfacp_id; ?>" type="checkbox" class="wfacp-tgl wfacp-tgl-ios wfacp_checkout_page_status" <?php echo( $wfacp_post->post_status == 'publish' ? 'checked="checked"' : '' ); ?>>
                        <label for="state_<?php echo $wfacp_id; ?>" class="wfacp-tgl-btn wfacp-tgl-btn-small"></label>
                    </div>
                    <span class="wfacp_head_funnel_state_on" <?php echo ( $wfacp_post->post_status !== 'publish' ) ? ' style="display:none"' : ''; ?>><?php _e( 'Live', 'woofunnels-aero-checkout' ); ?></span>
                    <span class="wfacp_head_funnel_state_off" <?php echo ( $wfacp_post->post_status == 'publish' ) ? 'style="display:none"' : ''; ?>> <?php _e( 'Sandbox', 'woofunnels-aero-checkout' ); ?></span>
                </div>

                <div class="wfacp_head_ml">
                    <span class="wfacp_now_editing"><?php _e( 'Now Editing', 'woofunnels-aero-checkout' ); ?></span>
                    <span class="wfacp_page_title wfacp_bold_here"><?php echo WFACP_Common::get_page_name(); ?></span>
                    <a href="javascript:void(0)" data-izimodal-open="#modal-checkout-page" data-iziModal-title="Create New Checkout page" data-izimodal-transitionin="fadeInDown"><span class="dashicons dashicons-edit"></span></a>
                    <a href="<?php echo get_the_permalink( $wfacp_id ); ?>" target="_blank" class="wfacp-preview" style="<?php echo count( $products ) == 0 ? 'display:none' : ''; ?>"></a>
                </div>
            </div>

            <div class="wfacp_head_r wfacp_tr wfacp_table_cell">
                <a href="<?php echo admin_url( 'admin.php?page=wfacp' ); ?>" class="wfacp_head_close"><i class="dashicons dashicons-no-alt"></i></a>
            </div>
        </div>
    </div>
    <div class="wfacp_fixed_sidebar">
		<?php

		foreach ( $steps as $step ) {

			$href = add_query_arg( [
				'page'     => 'wfacp',
				'wfacp_id' => $wfacp_id,
				'section'  => $step['slug'],
			], admin_url( 'admin.php' ) );

			$stop_class = '';
			if ( count( $products ) == 0 ) {
				$stop_class = 'wfacp_stop_navigation';
			}

			?>
            <a data-slug="<?php echo $step['slug']; ?>" class="wfacp_s_menu <?php echo( $step['slug'] == $wfacp_section ? 'active' : '' ); ?> wfacp_s_menu_rules <?php echo $stop_class; ?>" href="<?php echo $href; ?>">
				<span class="wfacp_s_menu_i">
					<?php echo '<img src="' . $step['icon'] . '"/>'; ?>
				</span>
                <span class="wfacp_s_menu_n"><?php echo $step['name']; ?></span>
            </a>
			<?php
		}
		?>
    </div>
    <div class="wfacp_wrap wfacp_box_size">
        <div class="wfacp_loader"><span class="spinner"></span></div>
		<?php include_once $this->current_section; ?>
		<?php include_once __DIR__ . '/global/model.php'; ?>
    </div>
</div>
