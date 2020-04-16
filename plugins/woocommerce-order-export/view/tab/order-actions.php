<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
include_once $WC_Order_Export->path_plugin . '/classes/admin/tab/class-wc-table-order-actions.php';

$table = new WC_Table_Order_Actions();
$pro_link = '<a href="https://algolplus.com/plugins/downloads/woocommerce-order-export/" target=_blank>'  . __( 'Pro version', 'woocommerce-order-export' ) . '</a>';
?>
<!-- <div class="tabs-content"><?php echo sprintf ( __( 'Buy %s to get access to Status change jobs', 'woocommerce-order-export' ), $pro_link ) ?></div> -->
<div class="tabs-content">
	<?php
	$table->output();
	?>
</div>


<script>
	jQuery( document ).ready( function( $ ) {
		$('[data-action=add-order-action]').click(function () {
			document.location = '<?php echo admin_url( "admin.php?page=wc-order-export&tab={$tab}&wc_oe=add_action" ) ?>';
		});
		$('[data-action=edit-order-action]').click(function () {
			var id = $(this).attr('data-id');
			document.location = '<?php echo admin_url( "admin.php?page=wc-order-export&tab={$tab}&wc_oe=edit_action&action_id=" ) ?>' + id;
		});
		$('[data-action=clone-order-action]').click(function () {
			var id = $(this).attr('data-id');
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to CLONE this job?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=order_actions&wc_oe=edit_action&clone=yes&action_id=' ) ?>' + id;
			}
		});
		$('[data-action=delete-order-action]').click(function () {
			var id = $(this).attr('data-id');
			var f = confirm('Are you sure you want to DELETE this job?');
			if (f) {
				document.location = '<?php echo admin_url( "admin.php?page=wc-order-export&tab={$tab}&wc_oe=delete&action_id=" ) ?>' + id;
			}
		});
		$( '#doaction' ).click( function() {
			var chosen_order_actions = [];

			jQuery.each( $(' table th.check-column input '), function ( index, elem ) {
				if ( $( elem ).prop( "checked" ) ) {
					chosen_order_actions.push($( elem ).val());
				}
			});

			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=order_actions&wc_oe=change_statuses&chosen_order_actions=' ) ?>' + chosen_order_actions + '&doaction=' + $('#bulk-action-selector-top').val();
		});
	} )
</script>