html-<?php
/**
* Plain email instructions.
*
* @author  Jose Vieira
* @package Multibanco-for-woocommerce/Templates
* @version 0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}
?>

<?php

  _e('Payment instructions', 'woo-multibanco-gateway');
  echo "\n";
  _e('Entity', 'woo-multibanco-gateway'); echo ': '; echo $entidade;
  echo "\n";
  _e('Reference', 'woo-multibanco-gateway'); echo ': '; echo $referencia;
  echo "\n";
  _e('Value', 'woo-multibanco-gateway'); echo ': '; echo $order_total; echo '&euro;';
  echo "\n";
  _e('The receipt issued by the ATM machine is a proof of payment. Keep it.', 'woo-multibanco-gateway');
  
   ?>
