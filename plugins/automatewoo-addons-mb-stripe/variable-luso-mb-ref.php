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

			$ref_lusopay = '';



			$table_name = $wpdb->prefix . 'magnimeiosreferences';



			$result = $wpdb->get_results( $wpdb->prepare( "SELECT refMB AS mb_reference, refPS AS ps_reference, value, entidade FROM $table_name WHERE id_order = %d", $order_id ) );// db call ok; no-cache ok.

			foreach ( $result as $row ) {

				$refs[2]     = $row->mb_reference;

				$refs[1]     = $row->ps_reference;

				$order_value = $row->value;

				$entidade    = $row->entidade;

	$ref_lusopay .= '<strong>Ent:</strong> ' . $entidade . ' | <br /><strong>Ref:</strong> ' . $refs[2] . ' | <br /><strong>Valor:</strong> ' . $order_value . '€';

			}




		return $ref_lusopay;
	}
}

return new Variable_Ref_ID();