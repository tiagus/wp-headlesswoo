<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once $WC_Order_Export->path_plugin . '/classes/admin/tab/class-wc-table-profiles.php';

$t_p = new WC_Table_Profiles();
$pro_link = '<a href="https://algolplus.com/plugins/downloads/woocommerce-order-export/" target=_blank>'  . __( 'Pro version', 'woocommerce-order-export' ) . '</a>';
?>
<!-- <div class="tabs-content"><?php echo sprintf ( __( 'Buy %s to get access to profiles', 'woocommerce-order-export' ), $pro_link ) ?></div> -->
<div class="tabs-content">
	<?php
	$t_p->output();
	?>
</div>


<script>
	jQuery( document ).ready( function( $ ) {
		$( '#add_profile' ).click( function() {
			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=add_profile' ) ?>';
		} )

		$( '.btn-trash' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to DELETE this profile?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=delete_profile&profile_id=' ) ?>' + id;
			}
		} )
		$( '.btn-export' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			document.location = '<?php echo admin_url( 'admin-ajax.php?action=order_exporter&method=run_one_job&profile=' ) ?>' + id;
		} )
		$( '.btn-edit' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=edit_profile&profile_id=' ) ?>' + id;
		} )
		$( '.btn-clone' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to CLONE this profile?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=edit_profile&clone=yes&profile_id=' ) ?>' + id;
			}	
		} )
		$( '.btn-to-scheduled' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to COPY this profile to a Scheduled job?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=copy_profile_to_scheduled&profile_id=' ) ?>' + id;
			}
		} )
		$( '.btn-to-actions' ).click( function() {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to COPY this profile to a Status change job?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=copy_profile_to_actions&profile_id=' ) ?>' + id;
			}
		} )

		$( '#doaction' ).click( function() {
			var chosen_profiles = [];

			jQuery.each( $(' table.profiles th.check-column input '), function ( index, elem ) {
				if ( $( elem ).prop( "checked" ) ) {
					chosen_profiles.push($( elem ).val());
                }
			});

			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=change_profile_statuses&chosen_profiles=' ) ?>' + chosen_profiles + '&doaction=' + $('#bulk-action-selector-top').val();
		});

	} )
</script>