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

			$ref_multibanco = '';



				$referencia     = get_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-reference', true);
				

				$order_value = get_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-valor', true);

				$entidade    = get_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-entity', true);

	$ref_multibanco .= 'Entidade: ' . $entidade . '<br />Refer&ecirc;ncia: ' . $referencia  . '<br />Valor: ' . $order_value . '€';

			






		return $ref_multibanco;
	}
}

return new Variable_Ref_ID();