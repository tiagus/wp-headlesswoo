<?php
/*
  Plugin Name: AutomateWoo Addons MB Stripe
  Description: Automatewoo with Multibanco Stripe.
  Version: 1.0
  Author: Jose Vieira
  License: GPLv2+
  Text Domain: automatewoo-addon 
*/


/**
 * Add the custom variable to the list
 */
add_filter( 'automatewoo/variables', 'ifthen_variables' );
add_filter( 'automatewoo/variables', 'ifthensms_variables' );
add_filter( 'automatewoo/variables', 'aftershipping_variables' );

/**
 * @param $variables array
 * @return array
 */
function ifthen_variables( $variables ) {
	// variable's string form is set here, it will be order.pluralize
	$variables['order']['ifthen_mb'] = dirname(__FILE__) . '/variable-ifthen-mb-ref.php';
	return $variables;
}

function ifthensms_variables( $variables ) {
	// variable's string form is set here, it will be order.pluralize
	$variables['order']['ifthen_mbsms'] = dirname(__FILE__) . '/variable-ifthen-mb-sms-ref.php';
	return $variables;
}

function aftershipping_variables( $variables ) {
	// variable's string form is set here, it will be order.pluralize
	$variables['order']['aftership_tracking'] = dirname(__FILE__) . '/variable-aftership.php';
	return $variables;
}



?>