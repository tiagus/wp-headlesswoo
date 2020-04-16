<?php
/**
* HTML email instructions.
*
* @author  Jose Vieira
* @package Multibanco-for-woocommerce/Templates
* @version 0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}
?>


  <table cellpadding="10" cellspacing="0" align="center" border="0" style="margin: auto; margin-top: 10px; margin-bottom: 10px; border-collapse: collapse; border: 1px solid #1465AA; border-radius: 4px !important; background-color: #FFFFFF;">
    <tr>
      <td style="border: 1px solid #1465AA; border-top-right-radius: 4px !important; border-top-left-radius: 4px !important; text-align: center; color: #000000; font-weight: bold;" colspan="2">
        <?php _e('Payment instructions', 'woo-multibanco-gateway'); ?>
        <br/>
        <img src="<?php echo plugins_url('images/multibanco_banner.png', dirname(dirname(__FILE__))); ?>" alt="<?php echo esc_attr($payment_name); ?>" title="<?php echo esc_attr($payment_name); ?>" style="margin-top: 10px;"/>
      </td>
    </tr>
    <tr>
      <td style="border: 1px solid #1465AA; color: #000000;"><?php _e('Entity', 'woo-multibanco-gateway'); ?>:</td>
      <td style="border: 1px solid #1465AA; color: #000000; white-space: nowrap;"><?php echo $entidade; ?></td>
    </tr>
    <tr>
      <td style="border: 1px solid #1465AA; color: #000000;"><?php _e('Reference', 'woo-multibanco-gateway'); ?>:</td>
      <td style="border: 1px solid #1465AA; color: #000000; white-space: nowrap;"><?php echo $referencia; ?></td>
    </tr>
    <tr>
      <td style="border: 1px solid #1465AA; color: #000000;"><?php _e('Value', 'woo-multibanco-gateway'); ?>:</td>
      <td style="border: 1px solid #1465AA; color: #000000; white-space: nowrap;"><?php echo $order_total; ?> &euro;</td>
    </tr>
    <tr>
      <td style="font-size: x-small; border: 1px solid #1465AA; border-bottom-right-radius: 4px !important; border-bottom-left-radius: 4px !important; color: #000000; text-align: center;" colspan="2"><?php _e('The receipt issued by the ATM machine is a proof of payment. Keep it.', 'woo-multibanco-gateway'); ?></td>
    </tr>
  </table>
