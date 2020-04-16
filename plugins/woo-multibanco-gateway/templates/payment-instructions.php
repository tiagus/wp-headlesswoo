<?php
/**
* Payment instructions.
*
* @author  Jose Vieira
* @package WooCommerce_Multibanco/Templates
* @version 0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}
?>
<style type="text/css">
table.woocommerce_multibanco_table { width: auto !important; margin: auto; }
table.woocommerce_multibanco_table td,	table.woocommerce_multibanco_table th { background-color: #FFFFFF; color: #000000; padding: 10px; vertical-align: middle; white-space: nowrap; }
table.woocommerce_multibanco_table th { text-align: center; font-weight: bold; }
table.woocommerce_multibanco_table th img { margin: auto; margin-top: 10px; }
</style>
  <table class="woocommerce_multibanco_table" cellpadding="0" cellspacing="0">
    <tr>
      <th colspan="2">
        <?php _e('Payment instructions', 'woo-multibanco-gateway'); ?>
        <br/>
        <img src="<?php echo plugins_url('images/multibanco_banner.png', dirname(__FILE__)); ?>" alt="<?php echo esc_attr($payment_name); ?>" title="<?php echo esc_attr($payment_name); ?>"/>
      </th>
    </tr>
    <tr>
      <td><?php _e('Entity', 'woo-multibanco-gateway'); ?>:</td>
      <td><?php echo $entidade; ?></td>
    </tr>
    <tr>
      <td><?php _e('Reference', 'woo-multibanco-gateway'); ?>:</td>
      <td><?php echo $referencia; ?></td>
    </tr>
    <tr>
      <td><?php _e('Value', 'woo-multibanco-gateway'); ?>:</td>
      <td><?php echo $order_total; ?> &euro;</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size: small;"><?php _e('The receipt issued by the ATM machine is a proof of payment. Keep it.', 'woo-multibanco-gateway'); ?></td>
    </tr>
  </table>
  
