<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Order_ID
 */
class Variable_Ref_ID extends Variable {


	function load_admin_details() {
		parent::load_admin_details();
		$this->description = __( "Displays the MB Referencia ID.", 'automatewoo');
	}


	/**
	 * @param $order \WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters ) {
         $order_id = Compat\Order::get_id( $order );
        global $wpdb;
        
        
        $ref_ifthen = '';
        $ref = get_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-reference', true);
	$ent = get_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-entity', true);
	
      $val = get_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-valor', true);
       //$ref = chunk_split($ref, 3, ' ');
        $ref_ifthen .= '<strong>Ent:</strong> ' . $ent . ' <br /><strong>Ref:</strong> ' . $ref . ' <br /><strong>Valor:</strong> ' . $val.'&euro;';


	return $ref_ifthen;
	
}
}

return new Variable_Ref_ID();