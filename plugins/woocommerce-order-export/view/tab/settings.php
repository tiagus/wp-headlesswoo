<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$url = admin_url('admin-ajax.php?action=order_exporter_run&method=run_cron_jobs&key=' . $settings['cron_key']);
$sample_link = '<b>curl "http://site.com/...&key=xyz"</b>';
$step_input = '<input type="text" name="ajax_orders_per_step" size="3" value="' . $settings['ajax_orders_per_step'] . '">';
?>
<div class="clearfix"></div>
<form id="settings-form">
    <input type="hidden" name="action" value="order_exporter">
    <input type="hidden" name="method" value="save_settings_tab">
    <table class="form-table">
        <tbody>
            <tr>
                <td>
                    <label>
                    <?php echo sprintf( __( 'AJAX progressbar exports %s orders per step', 'woocommerce-order-export'), $step_input)  ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    <label>
                        <input type="hidden" name="show_export_status_column"  value="0" >
                        <input type="checkbox" name="show_export_status_column" value="1" <?php checked($settings['show_export_status_column']) ?>>
                        <?php _e('Show column "Export Status" in order list', 'woocommerce-order-export') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    <label>
                        <input type="hidden" name="show_export_actions_in_bulk"  value="0" >
                        <input type="checkbox" name="show_export_actions_in_bulk" value="1" <?php checked($settings['show_export_actions_in_bulk']) ?>>
                        <?php _e('Add "Mark/unmark exported" to bulk actions in order list', 'woocommerce-order-export') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    <label>
                        <input type="hidden" name="show_export_in_status_change_job"  value="0" >
                        <input type="checkbox" name="show_export_in_status_change_job" value="1" <?php checked($settings['show_export_in_status_change_job']) ?>>
                        <?php _e('Allow mass export for "Status Change" jobs', 'woocommerce-order-export') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    <label>
                        <input type="hidden" name="cron_tasks_active"  value="0" >
                        <input type="checkbox" name="cron_tasks_active" value="1" <?php checked($settings['cron_tasks_active']) ?>>
                        <?php _e('Activate scheduled jobs', 'woocommerce-order-export') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    <label>
                        <?php _e('Button "Test" sends', 'woocommerce-order-export') ?>
                        <select style="width: auto;" name="limit_button_test">
                            <option value="1" <?php selected($settings['limit_button_test'], '1') ?>><?php _e('First suitable order', 'woocommerce-order-export') ?></option>
                            <option value="0" <?php selected($settings['limit_button_test'], '0') ?>><?php _e('All suitable orders', 'woocommerce-order-export') ?></option>
                        </select>
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    <label>
                        <?php _e('Cron url', 'woocommerce-order-export') ?>
                        <a id="cron-url" href="<?php echo $url;?>" data-value="<?php echo admin_url('admin-ajax.php?action=order_exporter_run&method=run_cron_jobs&key=');?>" target=_blank><?php echo $url;?></a>
                        <input type="hidden" name="cron_key" readonly size="4" id="cron-key" value="<?php echo $settings['cron_key'] ?>">
                        <br>
                        
                        <i><?php echo sprintf( __('Schedule it as %s only if you have problem with WP cron!', 'woocommerce-order-export'), $sample_link) ?></i>
                        <br>
                        <button class="button-secondary" id="generate-new-key"><?php _e('Generate new key', 'woocommerce-order-export') ?></button>
                    </label>
                    </td>
            </tr>
            <tr>
                <td>
                    <label>
                        <?php _e('String to identify IPN call', 'woocommerce-order-export') ?>
                        <input type="text" name="ipn_url" value="<?php echo $settings['ipn_url'] ?>">
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit">
        <button type="submit" id="save-btn" class="button-primary"><?php _e('Save settings', 'woocommerce-order-export') ?></button>
    </p>
</form>

<script>
jQuery(function($) {
    $('#generate-new-key').click(function (e) {
        e.preventDefault();
        var key = Math.random().toString(36).substring(2, 6);
        $('#cron-key').val(key);
        $('#cron-url').text( $('#cron-url').data('value') + key );
        $('#cron-url').attr('href', $('#cron-url').data('value') + key );
    });

    $( "#settings-form" ).submit( function(e) {
        e.preventDefault();
        var data = $( '#settings-form' ).serialize();
        $.post( ajaxurl, data, function( response ) {
            document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=settings&save=y' ) ?>';
        });
        return false;
    } );
});
</script>
