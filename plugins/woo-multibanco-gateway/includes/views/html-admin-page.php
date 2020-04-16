<?php
global $woocommerce;
?>
<div id="wc_eupago">
<div id="wc_eupago_settings">
  <h3><?php echo $this->method_title; ?> <span style="font-size: 75%;">v.<?php echo WC_EuPago::VERSION; ?></span></h3>
  <p><strong><?php _e('In order to use this plugin you <u>must</u>:', 'eupago-for-woocommerce'); ?></strong></p>
  <ul class="wc_eupago_list">
    <li><?php printf( __('Set WooCommerce currency to <b>Euros (&euro;)</b> %1$s', 'eupago-for-woocommerce'), '<a href="admin.php?page=wc-settings&tab=general">&gt;&gt;</a>.'); ?></li>
    <li><?php printf( __('Register an account with %1$s. To get more informations on this service go to %2$s.', 'eupago-for-woocommerce'), '<b><a href="http://www.eupago.pt/registo" target="_blank">euPago</a></b>', '<a href="http://www.eupago.pt" target="_blank">http://www.eupago.pt/</a>'); ?></li>
    <li><?php _e('Fill in all details (channel name and API key) provided by <b>euPago</b> on the fields below.', 'eupago-for-woocommerce'); ?></li>
    <li><?php printf( __('In order to activate callback, insert this exact URL in channel settings at <a href="https://seguro.eupago.pt/clientes/dashboard">euPago Dashboard</a> "Receive url notification" field: %1$s', 'eupago-for-woocommerce'), '<br/><code><b>'.$this->notify_url.'</b></code><br/>'); ?></li>
  </ul>
  <hr/>
  <table class="form-table">
    <?php
    if (trim(get_woocommerce_currency())=='EUR') {
      $this->generate_settings_html();
    } else {
      ?>
      <p><strong><?php _e('ERROR!', 'eupago-for-woocommerce'); ?> <?php printf( __('Set WooCommerce currency to <strong>Euros (&euro;)</strong> %1$s', 'eupago-for-woocommerce'), '<a href="admin.php?page=wc-settings&tab=general">'.__('here', 'eupago-for-woocommerce').'</a>.'); ?></strong></p>
      <?php
    }
    ?>
  </table>
</div>
</div>
<div class="clear"></div>
<style type="text/css">
@media (min-width: 961px) {
  #wc_eupago { height: auto; overflow: hidden; }
  #wc_eupago_settings { width: auto; overflow: hidden; }
}
.wc_eupago_list { list-style-type: disc; list-style-position: inside; }
.wc_eupago_list li { margin-left: 1.5em; }
</style>
