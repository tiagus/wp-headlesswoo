<?php
$order = new WC_Order( $post->ID );
$meta_values = get_post_meta($order->id);
echo '<p>';
switch ($order->payment_method) {
  case 'eupago_multibanco':
  // echo '<strong>'.$order->payment_method_title.'</strong><br />';
  echo '<img src="' . plugins_url('images/multibanco_banner.png', dirname(dirname(__FILE__))) . '" alt="' . esc_attr($order->payment_method_title) . '" title="' . esc_attr($order->payment_method_title) . '" /><br />';
  echo __('Entity', 'eupago-for-woocommerce').': '.trim(get_post_meta($post->ID, '_eupago_multibanco_entidade', true)).'<br/>';
  echo __('Reference', 'eupago-for-woocommerce').': '.chunk_split(trim(get_post_meta($post->ID, '_eupago_multibanco_referencia', true)), 3, ' ').'<br/>';
  echo __('Value', 'eupago-for-woocommerce').': '.$order->order_total;
  break;

  case 'eupago_payshop':
  // echo '<strong>'.$order->payment_method_title.'</strong><br />';
  echo '<img src="' . plugins_url('images/payshop_banner.png', dirname(dirname(__FILE__))) . '" alt="' . esc_attr($order->payment_method_title) . '" title="' . esc_attr($order->payment_method_title) . '" /><br />';
  echo __('Reference', 'eupago-for-woocommerce').': '.chunk_split(trim(get_post_meta($post->ID, '_eupago_payshop_referencia', true)), 3, ' ').'<br/>';
  echo __('Value', 'eupago-for-woocommerce').': '.$order->order_total;
  break;

  case 'eupago_mbway':
  // echo '<strong>'.$order->payment_method_title.'</strong><br />';
  echo '<img src="' . plugins_url('images/mbway_banner.png', dirname(dirname(__FILE__))) . '" alt="' . esc_attr($order->payment_method_title) . '" title="' . esc_attr($order->payment_method_title) . '" /><br />';
  echo __('Reference', 'eupago-for-woocommerce').': '.trim(get_post_meta($post->ID, '_eupago_mbway_referencia', true)).'<br/>';
  echo __('Value', 'eupago-for-woocommerce').': '.$order->order_total;
  break;

  default:
  echo __('No details available', 'eupago-for-woocommerce');
  break;
}
echo '</p>';
$desativar = true;
if (!$desativar && WP_DEBUG && in_array( $order->status, array('on-hold', 'pending'))) {
  $mb = new WC_Multibanco_euPago_WebAtual();
  $callback_url = $mb->debug_notify_url;
  $callback_url = str_replace('[IDENTIFICADOR]', $order->id, $callback_url);
  $callback_url = str_replace('[REFERENCIA]', trim($meta_values['_eupago_multibanco_referencia'][0]), $callback_url);
  $callback_url = str_replace('[VALOR]', $order->order_total, $callback_url);
  $callback_url = str_replace('[TRANSACAO]', '99', $callback_url);
  $callback_url = str_replace('[CANAL]', $mb->channel, $callback_url);
  $callback_url = str_replace('[CHAVEAPI]', $mb->api, $callback_url);
  ?>
  <hr/>
  <p>
    <?php _e('Callback URL', 'eupago-for-woocommerce'); ?>:<br/>
    <textarea readonly type="text" class="input-text" cols="20" rows="5" style="width: 100%; height: 50%; font-size: 10px;"><?php echo $callback_url; ?></textarea>
  </p>
  <script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery('#woocommerce_eupago_simulate_callback').click(function() {
      if (confirm('<?php _e('This is a testing tool and will set the order as paid. Are you sure you want to proceed?', 'eupago-for-woocommerce'); ?>')) {
        jQuery.get('<?php echo $callback_url; ?>', '', function(response) {
          //if (response==1) {
          //	alert('<?php _e('Success: The order is now set as paid and processing. This page will now reload.', 'eupago-for-woocommerce'); ?>');
          //	window.location.reload();
          //} else {
          //	alert('<?php _e('Error: Could not set the order as paid', 'eupago-for-woocommerce'); ?>');
          //}
          alert('<?php _e('This page will now reload. If the order is not set as paid and processing (or completed, if it only contains virtual and downloadable products) please check the debug logs.', 'eupago-for-woocommerce'); ?>');
          window.location.reload();
        }).fail(function() {
          alert('<?php _e('Error: Could not set the order as paid', 'eupago-for-woocommerce'); ?>');
        });
      }
    });
  });
  </script>
  <p align="center">
    <input type="button" class="button" id="woocommerce_eupago_simulate_callback" value="<?php echo esc_attr(__('Simulate callback payment', 'eupago-for-woocommerce')); ?>"/>
  </p>
  <?php
}
