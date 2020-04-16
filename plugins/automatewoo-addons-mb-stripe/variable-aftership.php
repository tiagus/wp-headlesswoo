<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * @class Variable_Aftership_Tracking
 */
class Variable_Aftership_Tracking extends AutomateWoo\Variable {
	


function load_admin_details() {
		$this->description = __( 'Get the AfterShip tracking number ', 'automatewoo');
	}


	/**
	 * @param $order \WC_Order
	 * @param $parameters array
	 * @return string
	 */
//function get_value( $order, $parameters ) {
//		return $this->get_shipment_tracking_field( $order, 'tracking_number' );
//	}

	function get_value( $order_id, $parameters ) {
				$tracking = get_post_meta($order_id, '_tracking_number', true);
               
                return $this->display_tracking_number($tracking_number);
                }
            }
	
return new Variable_Aftership_Tracking();