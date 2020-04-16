<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once $WC_Order_Export->path_plugin . '/classes/admin/tab/class-wc-table-schedules.php';

$t_schedules = new WC_Table_Schedules();
$pro_link = '<a href="https://algolplus.com/plugins/downloads/woocommerce-order-export/" target=_blank>'  . __( 'Pro version', 'woocommerce-order-export' ) . '</a>';

?>
<!-- <div class="tabs-content"><?php echo sprintf ( __( 'Buy %s to get access to Scheduled jobs', 'woocommerce-order-export' ), $pro_link ) ?></div> -->
<div class="tabs-content">
	<?php
	$t_schedules->output();
	?>
</div>


<script>
	jQuery( document ).ready( function( $ ) {
		$( '#add_schedule' ).click( function() {
			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=schedules&wc_oe=add_schedule' ) ?>';
		} )

		$( '.btn-trash' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to DELETE this job?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=schedules&wc_oe=delete_schedule&schedule_id=' ) ?>' + id;
			}
		} )
		$( '.btn-export' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			document.location = '<?php echo admin_url( 'admin-ajax.php?action=order_exporter&method=run_one_job&schedule=' ) ?>' + id;
		} )
		$( '.btn-edit' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=schedules&wc_oe=edit_schedule&schedule_id=' ) ?>' + id;
		} )
		$( '.btn-clone' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to CLONE this job?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=schedules&wc_oe=edit_schedule&clone=yes&schedule_id=' ) ?>' + id;
			}
		} )
		$( '#doaction' ).click( function() {
			var chosen_schedules = [];

			jQuery.each( $(' table th.check-column input '), function ( index, elem ) {
				if ( $( elem ).prop( "checked" ) ) {
					chosen_schedules.push($( elem ).val());
				}
			});

			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=schedules&wc_oe=change_status_schedules&chosen_schedules=' ) ?>' + chosen_schedules + '&doaction=' + $('#bulk-action-selector-top').val();
		});
	} )
</script>