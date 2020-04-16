<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
add_thickbox();
/** @var WC_Order_Export_Admin $WC_Order_Export */
$settings                 = WC_Order_Export_Manage::get( $mode, $id );
$settings                 = apply_filters('woe_settings_page_prepare', $settings );
$order_custom_meta_fields = WC_Order_Export_Data_Extractor_UI::get_all_order_custom_meta_fields();
$readonly_php = WC_Order_Export_Admin::user_can_add_custom_php() ? '' : 'readonly';
?>

<script>
	var mode = '<?php echo $mode ?>';
	var job_id = '<?php echo $id ?>';
	var output_format = '<?php echo $settings[ 'format' ] ?>';
	var order_fields = <?php echo json_encode( $settings[ 'order_fields' ] ) ?>;
	var order_products_fields = <?php echo json_encode( $settings[ 'order_product_fields' ] ) ?>;
	var order_coupons_fields = <?php echo json_encode( $settings[ 'order_coupon_fields' ] ) ?>;
	var order_custom_meta_fields = <?php echo json_encode( $order_custom_meta_fields ) ?>;
	var order_products_custom_meta_fields = <?php echo json_encode( WC_Order_Export_Data_Extractor_UI::get_product_custom_fields() ) ?>;
	var order_order_item_custom_meta_fields = <?php echo json_encode( WC_Order_Export_Data_Extractor_UI::get_product_itemmeta() ) ?>;
	var order_coupons_custom_meta_fields = <?php echo json_encode( WC_Order_Export_Data_Extractor_UI::get_all_coupon_custom_meta_fields() ) ?>;
	var flat_formats   = ['XLS', 'CSV', 'TSV'];
	var object_formats = ['XML', 'JSON'];
	var xml_formats    = ['XML'];
	var day_names    = <?php echo json_encode( WC_Order_Export_Manage::get_days() ) ?>;
	var summary_mode = <?php echo $settings['summary_report_by_products'] ?>;
</script>


<?php include 'modal-controls.php'; ?>
<form method="post" id="export_job_settings">
	<?php if ( $mode !== WC_Order_Export_Manage::EXPORT_NOW ): ?>
		<div style="width: 100%;">&nbsp;</div>
	<?php endif; ?>

	<div id="my-left" style="float: left; width: 49%; max-width: 500px;">
		<?php 
			if ( $mode === WC_Order_Export_Manage::EXPORT_PROFILE ): 
				include 'pro-version/top-profile.php';
			elseif ( $mode === WC_Order_Export_Manage::EXPORT_ORDER_ACTION ): 
				include 'pro-version/top-order-actions.php';
			elseif ( $mode === WC_Order_Export_Manage::EXPORT_SCHEDULE ): 
				include 'pro-version/top-scheduled-jobs.php';
			endif; 
		?>

		<?php if ( $show[ 'date_filter' ] ) : ?>
			<div id="my-export-date-field" class="my-block">
				<div class="wc-oe-header">
					<?php _e( 'Filter orders by', 'woocommerce-order-export' ) ?>:
				</div>
				<label>
					<input type="radio" name="settings[export_rule_field]" class="width-100" <?php echo (!isset( $settings[ 'export_rule_field' ] ) || ($settings[ 'export_rule_field' ] == 'date')) ? 'checked' : '' ?> value="date" >
					<?php _e( 'Order Date', 'woocommerce-order-export' ) ?>
				</label>
				&#09;&#09;
				<label>
					<input type="radio" name="settings[export_rule_field]" class="width-100" <?php echo (isset( $settings[ 'export_rule_field' ] ) && ($settings[ 'export_rule_field' ] == 'modified')) ? 'checked' : '' ?> value="modified" >
					<?php _e( 'Modification Date', 'woocommerce-order-export' ) ?>
				</label>
				&#09;&#09;
				<label>
					<input type="radio" name="settings[export_rule_field]" class="width-100" <?php echo (isset( $settings[ 'export_rule_field' ] ) && ($settings[ 'export_rule_field' ] == 'date_paid')) ? 'checked' : '' ?> value="date_paid" >
					<?php _e( 'Paid Date', 'woocommerce-order-export' ) ?>
				</label>
				&#09;&#09;
				<label>
					<input type="radio" name="settings[export_rule_field]" class="width-100" <?php echo (isset( $settings[ 'export_rule_field' ] ) && ($settings[ 'export_rule_field' ] == 'date_completed')) ? 'checked' : '' ?> value="date_completed" >
					<?php _e( 'Completed Date', 'woocommerce-order-export' ) ?>
				</label>
			</div>
			<br>
			<div id="my-date-filter" class="my-block" title = "<?php _e( 'This date range should not be saved in the scheduled task', 'woocommerce-order-export' ) ?>">
				<div style="display: inline;">
					<span class="wc-oe-header"><?php _e( 'Date range', 'woocommerce-order-export' ) ?></span>
					<input type=text class='date' name="settings[from_date]" id="from_date" value='<?php echo $settings[ 'from_date' ] ?>'>
					<?php _e( 'to', 'woocommerce-order-export' ) ?>
					<input type=text class='date' name="settings[to_date]" id="to_date" value='<?php echo $settings[ 'to_date' ] ?>'>
				</div>

				<button id="my-quick-export-btn" class="button-primary"><?php _e( 'Express export', 'woocommerce-order-export' ) ?></button>
				<div id="summary_report_by_products" style="display:inline-block"><input type="hidden" name="settings[summary_report_by_products]" value="0"/><label><input type="checkbox" id=summary_report_by_products_checkbox name="settings[summary_report_by_products]" value="1" <?php checked($settings[ 'summary_report_by_products' ]) ?> /> <?php _e( "Summary Report By Products", 'woocommerce-order-export' ) ?></label>
					&nbsp;&nbsp;<label id="summary_setup_fields"><a href="#TB_inline?width=600&height=550&inlineId=modal-manage-products" class="thickbox " id="link_modal_manage_products_summary"><?php _e( 'Set up fields', 'woocommerce-order-export' ) ?></a></label>
				</div>
			</div>
			<br>
		<?php endif; ?>

			<div id="my-export-file" class="my-block">
				<div class="wc-oe-header">
					<?php _e( 'Export filename', 'woocommerce-order-export' ) ?>:
				</div>
				<label id="export_filename" class="width-100">
					<input type="text" name="settings[export_filename]" class="width-100" value="<?php echo isset( $settings[ 'export_filename' ] ) ? $settings[ 'export_filename' ] : 'orders-%y-%m-%d-%h-%i-%s.xlsx' ?>" >
				</label>
			</div>
			<br>


		<div id="my-format" class="my-block">
			<span class="wc-oe-header"><?php _e( 'Format', 'woocommerce-order-export' ) ?></span><br>
			<p>
				<?php foreach ( WC_Order_Export_Admin::$formats as $format ) { ?>
					<label class="button-secondary">
						<input type=radio name="settings[format]" class="output_format" value="<?php echo $format ?>"
							   <?php if ( $format == $settings[ 'format' ] ) echo 'checked'; ?> ><?php echo $format ?>
						<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
					</label>
				<?php } ?>
			</p>

			<div id='XLS_options' style='display:none'><strong><?php _e( 'XLS options', 'woocommerce-order-export' ) ?></strong><br>
				<input type=hidden name="settings[format_xls_use_xls_format]" value=0>
				<input type=hidden name="settings[format_xls_display_column_names]" value=0>
				<input type=hidden name="settings[format_xls_auto_width]" value=0>
				<input type=hidden name="settings[format_xls_populate_other_columns_product_rows]" value=0>
				<input type=hidden name="settings[format_xls_direction_rtl]" value=0>
				<input type=checkbox name="settings[format_xls_use_xls_format]" value=1 <?php if ( @$settings[ 'format_xls_use_xls_format' ] ) echo 'checked'; ?>  id="format_xls_use_xls_format">  <?php _e( 'Export as .xls (Binary File Format)', 'woocommerce-order-export' ) ?><br>
				<input type=checkbox checked disabled><?php _e( 'Use sheet name', 'woocommerce-order-export' ) ?></b><input type=text name="settings[format_xls_sheet_name]" value='<?php echo $settings[ 'format_xls_sheet_name' ] ?>' size=10><br>
				<input type=checkbox name="settings[format_xls_display_column_names]" value=1 <?php if ( @$settings[ 'format_xls_display_column_names' ] ) echo 'checked'; ?>  >  <?php _e( 'Output column titles as first line', 'woocommerce-order-export' ) ?><br>
				<input type=checkbox name="settings[format_xls_auto_width]" value=1 <?php if ( @$settings[ 'format_xls_auto_width' ] ) echo 'checked'; ?>  >  <?php _e( 'Auto column width', 'woocommerce-order-export' ) ?><br>
				<input type=checkbox name="settings[format_xls_populate_other_columns_product_rows]" value=1 <?php if ( @$settings[ 'format_xls_populate_other_columns_product_rows' ] ) echo 'checked'; ?>  >  <?php _e( 'Populate other columns if products exported as rows', 'woocommerce-order-export' ) ?><br>
				<input type=checkbox name="settings[format_xls_direction_rtl]" value=1 <?php if ( @$settings[ 'format_xls_direction_rtl' ] ) echo 'checked'; ?>  >  <?php _e( 'Right-to-Left direction', 'woocommerce-order-export' ) ?><br>
			</div>
			<div id='CSV_options' style='display:none'><strong><?php _e( 'CSV options', 'woocommerce-order-export' ) ?></strong><br>
				<input type=hidden name="settings[format_csv_add_utf8_bom]" value=0>
				<input type=hidden name="settings[format_csv_display_column_names]" value=0>
				<input type=hidden name="settings[format_csv_populate_other_columns_product_rows]" value=0>
				<input type=hidden name="settings[format_csv_delete_linebreaks]" value=0>
				<input type=hidden name="settings[format_csv_item_rows_start_from_new_line]" value=0>
				<input type=checkbox name="settings[format_csv_add_utf8_bom]" value=1 <?php if ( @$settings[ 'format_csv_add_utf8_bom' ] ) echo 'checked'; ?>  > <?php _e( 'Output UTF-8 BOM', 'woocommerce-order-export' ) ?><br>
				<input type=checkbox name="settings[format_csv_display_column_names]" value=1 <?php if ( @$settings[ 'format_csv_display_column_names' ] ) echo 'checked'; ?>  >  <?php _e( 'Output column titles as first line', 'woocommerce-order-export' ) ?><br>
				<input type=checkbox name="settings[format_csv_populate_other_columns_product_rows]" value=1 <?php if ( @$settings[ 'format_csv_populate_other_columns_product_rows' ] ) echo 'checked'; ?>  >  <?php _e( 'Populate other columns if products exported as rows', 'woocommerce-order-export' ) ?><br>
				<input type=checkbox name="settings[format_csv_delete_linebreaks]" value=1 <?php if ( @$settings[ 'format_csv_delete_linebreaks' ] ) echo 'checked'; ?>  >  <?php _e( 'Convert line breaks to literals', 'woocommerce-order-export' ) ?><br>
				<input type=checkbox name="settings[format_csv_item_rows_start_from_new_line]" value=1 <?php if ( @$settings[ 'format_csv_item_rows_start_from_new_line' ] ) echo 'checked'; ?>  >  <?php _e( 'Item rows start from new line', 'woocommerce-order-export' ) ?><br>
				<?php _e( 'Enclosure', 'woocommerce-order-export' ) ?> <input type=text name="settings[format_csv_enclosure]" value='<?php echo $settings[ 'format_csv_enclosure' ] ?>' size=1>
				<?php _e( 'Field Delimiter', 'woocommerce-order-export' ) ?> <input type=text name="settings[format_csv_delimiter]" value='<?php echo $settings[ 'format_csv_delimiter' ] ?>' size=1>
				<?php _e( 'Line Break', 'woocommerce-order-export' ) ?><input type=text name="settings[format_csv_linebreak]" value='<?php echo $settings[ 'format_csv_linebreak' ] ?>' size=4><br>
				<?php if ( function_exists( 'iconv' ) ): ?>
					<?php _e( 'Character encoding', 'woocommerce-order-export' ) ?><input type=text name="settings[format_csv_encoding]" value="<?php echo $settings[ 'format_csv_encoding' ] ?>"><br>
				<?php endif ?>
			</div>
			<div id='XML_options' style='display:none'><strong><?php _e( 'XML options', 'woocommerce-order-export' ) ?></strong><br>
				<input type=hidden name="settings[format_xml_self_closing_tags]" value=0>
				<span class="xml-title"><?php _e( 'Prepend XML', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_prepend_raw_xml]" value='<?php echo $settings[ 'format_xml_prepend_raw_xml' ] ?>'><br>
				<span class="xml-title"><?php _e( 'Root tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_root_tag]" value='<?php echo $settings[ 'format_xml_root_tag' ] ?>'><br>
				<span class="xml-title"><?php _e( 'Order tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_order_tag]" value='<?php echo $settings[ 'format_xml_order_tag' ] ?>'><br>
				<span class="xml-title"><?php _e( 'Product tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_product_tag]" value='<?php echo $settings[ 'format_xml_product_tag' ] ?>'><br>
				<span class="xml-title"><?php _e( 'Coupon tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_coupon_tag]" value='<?php echo $settings[ 'format_xml_coupon_tag' ] ?>'><br>
				<span class="xml-title"><?php _e( 'Append XML', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_append_raw_xml]" value='<?php echo $settings[ 'format_xml_append_raw_xml' ] ?>'><br>
				<span class="xml-title"><?php _e( 'Self closing tags', 'woocommerce-order-export' ) ?></span><input type=checkbox name="settings[format_xml_self_closing_tags]" value=1 <?php if ( @$settings[ 'format_xml_self_closing_tags' ] ) echo 'checked'; ?>  ><br>
			</div>
			<div id='JSON_options' style='display:none'><strong><?php _e( 'JSON options', 'woocommerce-order-export' ) ?></strong><br>
				<span class="xml-title"><?php _e( 'Start tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_json_start_tag]" value='<?php echo @$settings[ 'format_json_start_tag' ] ?>'><br>
				<span class="xml-title"><?php _e( 'End tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_json_end_tag]" value='<?php echo @$settings[ 'format_json_end_tag' ] ?>'>
			</div>
            <div id='TSV_options' style='display:none'><strong><?php _e( 'TSV options', 'woocommerce-order-export' ) ?></strong><br>
                <input type=hidden name="settings[format_tsv_add_utf8_bom]" value=0>
                <input type=hidden name="settings[format_tsv_display_column_names]" value=0>
                <input type=hidden name="settings[format_tsv_populate_other_columns_product_rows]" value=0>
                <input type=checkbox name="settings[format_tsv_add_utf8_bom]" value=1 <?php if ( @$settings[ 'format_tsv_add_utf8_bom' ] ) echo 'checked'; ?>  > <?php _e( 'Output UTF-8 BOM', 'woocommerce-order-export' ) ?><br>
                <input type=checkbox name="settings[format_tsv_display_column_names]" value=1 <?php if ( @$settings[ 'format_tsv_display_column_names' ] ) echo 'checked'; ?>  >  <?php _e( 'Output column titles as first line', 'woocommerce-order-export' ) ?><br>
                <input type=checkbox name="settings[format_tsv_populate_other_columns_product_rows]" value=1 <?php if ( @$settings[ 'format_tsv_populate_other_columns_product_rows' ] ) echo 'checked'; ?>  >  <?php _e( 'Populate other columns if products exported as rows', 'woocommerce-order-export' ) ?><br>
				<?php _e( 'Line Break', 'woocommerce-order-export' ) ?><input type=text name="settings[format_tsv_linebreak]" value='<?php echo $settings[ 'format_tsv_linebreak' ] ?>' size=4><br>
				<?php if ( function_exists( 'iconv' ) ): ?>
					<?php _e( 'Character encoding', 'woocommerce-order-export' ) ?><input type=text name="settings[format_tsv_encoding]" value="<?php echo $settings[ 'format_tsv_encoding' ] ?>"><br>
				<?php endif ?>
            </div>

			<br>
			<div id="my-date-time-format" class="">
				<div id="date_format_block">
					<span class="wc-oe-header"><?php _e( 'Date', 'woocommerce-order-export' ) ?></span>
					<?php
					$date_format = array(
							'',
							'F j, Y',
							'Y-m-d',
							'm/d/Y',
							'd/m/Y',
					);
					$date_format = apply_filters( 'woe_date_format', $date_format );
					?>
					<select>
						<?php foreach( $date_format as $format ):  ?>
							<option value="<?php echo $format ?>" <?php echo selected( @$settings[ 'date_format' ], $format ) ?> ><?php echo !empty( $format ) ? current_time( $format ) : __( '-', 'woocommerce-order-export' ) ?></option>
						<?php endforeach; ?>
						<option value="custom" <?php echo selected( in_array( @$settings[ 'date_format' ], $date_format ), false) ?> ><?php echo __( 'custom', 'woocommerce-order-export' ) ?></option>
					</select>
					<div id="custom_date_format_block" style="<?php echo in_array( @$settings[ 'date_format' ], $date_format ) ? 'display: none' : '' ?>">
						<input type="text" name="settings[date_format]" value="<?php echo $settings[ 'date_format' ] ?>">
					</div>
				</div>

				<div id="time_format_block">
					<span class="wc-oe-header"><?php _e( 'Time', 'woocommerce-order-export' ) ?></span>
					<?php
					$time_format = array(
							'',
							'g:i a',
							'g:i A',
							'H:i',
					);
					$time_format = apply_filters( 'woe_time_format', $time_format );
					?>
					<select>
						<?php foreach( $time_format as $format ):  ?>
							<option value="<?php echo $format ?>" <?php echo selected( @$settings[ 'time_format' ], $format ) ?> ><?php echo !empty( $format ) ? current_time( $format ) : __( '-', 'woocommerce-order-export' ) ?></option>
						<?php endforeach; ?>
						<option value="custom" <?php echo selected( in_array( @$settings[ 'time_format' ], $time_format ), false) ?> ><?php echo __( 'custom', 'woocommerce-order-export' ) ?></option>
					</select>
					<div id="custom_time_format_block" style="<?php echo in_array( @$settings[ 'time_format' ], $time_format ) ? 'display: none' : '' ?>">
						<input type="text" name="settings[time_format]" value="<?php echo $settings[ 'time_format' ] ?>">
					</div>
				</div>		
			</div>
		</div>
		<br/>
		<div id="my-sort" class="my-block">
			<?php
			$sort = array(
				'order_id'      => __( 'Order ID', 'woocommerce-order-export' ),
				'post_date'     => __( 'Order Date', 'woocommerce-order-export' ),
				'post_modified' => __( 'Modification Date', 'woocommerce-order-export' ),
			);
			ob_start();
			?>
            <select name="settings[sort]">
				<?php foreach( $sort as $value => $text ):  ?>
                	<option value='<?php echo $value ?>' <?php echo selected( @$settings[ 'sort' ], $value ) ?> ><?php echo  $text; ?></option>
				<?php endforeach; ?>
            </select>
            <?php
            $sort_html = ob_get_clean();

			ob_start();
			?>
            <select name="settings[sort_direction]">
                <option value='DESC' <?php echo selected( @$settings[ 'sort_direction' ], 'DESC') ?> ><?php _e( 'Descending', 'woocommerce-order-export' ) ?></option>
                <option value='ASC'  <?php echo selected( @$settings[ 'sort_direction' ], 'ASC') ?> ><?php _e( 'Ascending', 'woocommerce-order-export' ) ?></option>
            </select>
            <?php
            $sort_direction_html = ob_get_clean();

            echo sprintf( __( 'Sort orders by %s in %s order', 'woocommerce-order-export' ), $sort_html, $sort_direction_html );
            ?>

			<?php if ( $mode === WC_Order_Export_Manage::EXPORT_SCHEDULE ): ?>
                <div>
                    <label for="change_order_status_to"><?php _e( 'Change order status to', 'woocommerce-order-export' ) ?></label>
                    <select id="change_order_status_to" name="settings[change_order_status_to]">
                        <option value="" <?php if ( empty( $settings[ 'change_order_status_to' ] ) ) echo 'selected'; ?>><?php _e( "- don't modify -", 'woocommerce-order-export' ) ?></option>
		                <?php foreach ( wc_get_order_statuses() as $i => $status ) { ?>
                            <option value="<?php echo $i ?>" <?php if ( $i === $settings[ 'change_order_status_to' ] ) echo 'selected'; ?>><?php echo $status ?></option>
		                <?php } ?>
                    </select>
                </div>
			<?php endif; ?>
		</div>
        <br>
        <div class="my-block">
			<span class="my-hide-next "><?php _e( 'Misc settings', 'woocommerce-order-export' ) ?>
                <span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
            <div id="my-misc" hidden="hidden">
                <div>
                    <input type="hidden" name="settings[format_number_fields]" value="0"/>
                    <label><input type="checkbox" name="settings[format_number_fields]" value="1" <?php checked($settings['format_number_fields']) ?>/><?php _e( 'Format numbers (use WC decimal separator)', 'woocommerce-order-export' ) ?></label>
                </div>
                <div>
                    <input type="hidden" name="settings[export_all_comments]" value="0"/>
                    <label><input type="checkbox" name="settings[export_all_comments]" value="1" <?php checked($settings['export_all_comments']) ?>/><?php _e( 'Export all order notes', 'woocommerce-order-export' ) ?></label>
                </div>
                <div>
                    <input type="hidden" name="settings[export_refund_notes]" value="0"/>
                    <label><input type="checkbox" name="settings[export_refund_notes]" value="1" <?php checked($settings['export_refund_notes']) ?>/><?php _e( 'Export refund notes as Customer Note', 'woocommerce-order-export' ) ?></label>
                </div>
                <div>
                    <input type="hidden" name="settings[strip_tags_product_fields]" value="0"/>
                    <label><input type="checkbox" name="settings[strip_tags_product_fields]" value="1" <?php checked($settings['strip_tags_product_fields']) ?>/><?php _e( 'Strip tags from Product Description/Variation', 'woocommerce-order-export' ) ?></label>
                </div>
                <div>
                    <input type="hidden" name="settings[cleanup_phone]" value="0"/>
                    <label><input type="checkbox" name="settings[cleanup_phone]" value="1" <?php checked($settings['cleanup_phone']) ?>/><?php _e( 'Cleanup phone (export only digits)', 'woocommerce-order-export' ) ?></label>
                </div>
                <div>
                    <input type="hidden" name="settings[enable_debug]" value="0"/>
                    <label><input type="checkbox" name="settings[enable_debug]" value="1" <?php checked($settings['enable_debug']) ?>/><?php _e( 'Enable debug output', 'woocommerce-order-export' ) ?></label>
                </div>
				<div>
                    <input type="hidden" name="settings[custom_php]" value="0"/>
                    <label><input type="checkbox" name="settings[custom_php]" value="1" <?php checked($settings['custom_php']) ?>/><?php _e( 'Custom PHP code to modify output', 'woocommerce-order-export' ) ?></label>
					<textarea  placeholder="<?php _e( 'Use only unnamed functions!', 'woocommerce-order-export' ) ?>" name="settings[custom_php_code]" <?php echo $readonly_php?> class="width-100" rows="10" <?php echo $settings['custom_php'] ? '' : 'style="display: none"' ?>><?php echo $settings['custom_php_code'] ?></textarea>
				</div>
            </div>
        </div>
	</div>

	<div id="my-right" style="float: left; width: 48%; margin: 0px 10px; max-width: 500px;">
		<?php 
		if ( in_array( $mode, array( WC_Order_Export_Manage::EXPORT_SCHEDULE, WC_Order_Export_Manage::EXPORT_ORDER_ACTION ) ) ):
			include "pro-version/destinations.php";
		endif; ?>

		<div class="my-block">
			<span class="my-hide-next "><?php _e( 'Filter by order', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
			<div id="my-order" hidden="hidden">
				<div><input type="hidden" name="settings[skip_suborders]" value="0"/><label><input type="checkbox" name="settings[skip_suborders]" value="1" <?php checked($settings[ 'skip_suborders' ]) ?> /> <?php _e( "Don't export child orders", 'woocommerce-order-export' ) ?></label></div>
				<div><input type="hidden" name="settings[export_refunds]" value="0"/><label><input type="checkbox" name="settings[export_refunds]" value="1" <?php checked($settings[ 'export_refunds' ]) ?> /> <?php _e( "Export refunds", 'woocommerce-order-export' ) ?></label></div>
				<div><input type="hidden" name="settings[mark_exported_orders]" value="0"/><label><input type="checkbox" name="settings[mark_exported_orders]" value="1" <?php checked($settings[ 'mark_exported_orders' ]) ?> /> <?php _e( "Mark exported orders", 'woocommerce-order-export' ) ?></label></div>
				<div><input type="hidden" name="settings[export_unmarked_orders]" value="0"/><label><input type="checkbox" name="settings[export_unmarked_orders]" value="1" <?php checked($settings[ 'export_unmarked_orders' ]) ?> /> <?php _e( "Export unmarked orders only", 'woocommerce-order-export' ) ?></label></div>
				<span class="wc-oe-header"><?php _e( 'Order statuses', 'woocommerce-order-export' ) ?></span>
				<select id="statuses" name="settings[statuses][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php foreach ( apply_filters('woe_settings_order_statuses', wc_get_order_statuses() ) as $i => $status ) { ?>
						<option value="<?php echo $i ?>" <?php if ( in_array( $i, $settings[ 'statuses' ] ) ) echo 'selected'; ?>><?php echo $status ?></option>
					<?php } ?>
				</select>

				<span class="wc-oe-header"><?php _e( 'Custom fields', 'woocommerce-order-export' ) ?></span>
				<br>
				<select id="custom_fields" style="width: auto;">
					<?php foreach ( WC_Order_Export_Data_Extractor_UI::get_order_custom_fields() as $cf_name ) { ?>
						<option><?php echo $cf_name; ?></option>
					<?php } ?>
				</select>

				<select id="custom_fields_compare" class="select_compare">
					<option>=</option>
					<option>&lt;&gt;</option>
					<option>LIKE</option>
					<option>NOT SET</option>
					<option>IS SET</option>
				</select>

				<input type="text" id="text_custom_fields" disabled class="like-input" style="display: none;">

				<button id="add_custom_fields" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
				<br>
				<select id="custom_fields_check" multiple name="settings[order_custom_fields][]" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'order_custom_fields' ] )
						foreach ( $settings[ 'order_custom_fields' ] as $prod ) {
							?>
							<option selected value="<?php echo $prod; ?>"> <?php echo $prod; ?></option>
						<?php } ?>
				</select>

			</div>
		</div>

		<br>

		<div class="my-block">
			<div id=select2_warning style='display:none;color:red;font-size: 120%;'><?php _e( "The filters won't work correctly.<br>Another plugin(or theme) has loaded outdated Select2.js", 'woocommerce-order-export' ) ?></div>
			<span class="my-hide-next "><?php _e( 'Filter by product', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
			<div id="my-products" hidden="hidden">
				<div><input type="hidden" name="settings[all_products_from_order]" value="0"/><label><input type="checkbox" name="settings[all_products_from_order]" value="1" <?php checked($settings[ 'all_products_from_order' ]) ?> /> <?php _e( 'Export all products from a order', 'woocommerce-order-export' ) ?></label></div>
				<div><input type="hidden" name="settings[skip_refunded_items]" value="0"/><label><input type="checkbox" name="settings[skip_refunded_items]" value="1" <?php checked($settings[ 'skip_refunded_items' ]) ?> /> <?php _e( 'Skip fully refunded items', 'woocommerce-order-export' ) ?></label></div>
				<span class="wc-oe-header"><?php _e( 'Product categories', 'woocommerce-order-export' ) ?></span>
				<select id="product_categories" name="settings[product_categories][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'product_categories' ] )
						foreach ( $settings[ 'product_categories' ] as $cat ) {
							$cat_term = get_term( $cat, 'product_cat' );
							?>
							<option selected value="<?php echo $cat_term->term_id ?>"> <?php echo $cat_term->name; ?></option>
						<?php } ?>
				</select>
				<span class="wc-oe-header"><?php _e( 'Vendor/creator', 'woocommerce-order-export' ) ?></span>
				<select id="product_vendors" name="settings[product_vendors][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'product_vendors' ] )
						foreach ( $settings[ 'product_vendors' ] as $user_id ) {
							$user = get_user_by( 'id', $user_id );
							?>
							<option selected value="<?php echo $user_id ?>"> <?php echo $user->display_name; ?></option>
						<?php } ?>
				</select>

                <?php do_action("woe_settings_filter_by_product_after_vendors", $settings); ?>

				<span class="wc-oe-header"><?php _e( 'Product', 'woocommerce-order-export' ) ?></span>

				<select id="products" name="settings[products][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'products' ] )
						foreach ( $settings[ 'products' ] as $prod ) {
							$p = get_the_title( $prod );
							?>
							<option selected value="<?php echo $prod ?>"> <?php echo $p; ?></option>
						<?php } ?>
				</select>

				<span class="wc-oe-header"><?php _e( 'Product taxonomies', 'woocommerce-order-export' ) ?></span>
				<br>
				<select id="taxonomies" style="width: auto;">
					<?php foreach ( WC_Order_Export_Data_Extractor_UI::get_product_taxonomies() as $attr_id => $attr_name ) { ?>
						<option><?php echo $attr_name; ?></option>
					<?php } ?>
				</select>

                <select id="taxonomies_compare" class="select_compare">
                    <option>=</option>
                    <option>&lt;&gt;</option>
                </select>

                <input type="text" id="text_taxonomies" disabled style="display: none;">

                <button id="add_taxonomies" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
				<br>
				<select id="taxonomies_check" multiple name="settings[product_taxonomies][]" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'product_taxonomies' ] )
						foreach ( $settings[ 'product_taxonomies' ] as $prod ) {
							?>
							<option selected value="<?php echo $prod; ?>"> <?php echo $prod; ?></option>
						<?php } ?>
				</select>

				<span class="wc-oe-header"><?php _e( 'Product custom fields', 'woocommerce-order-export' ) ?></span>
				<br>
				<select id="product_custom_fields" style="width: auto;">
					<?php foreach ( WC_Order_Export_Data_Extractor_UI::get_product_custom_fields() as $cf_name ) { ?>
						<option><?php echo $cf_name; ?></option>
					<?php } ?>
				</select>

				<select id="product_custom_fields_compare" class="select_compare">
					<option>=</option>
					<option>&lt;&gt;</option>
					<option>LIKE</option>
				</select>

				<input type="text" id="text_product_custom_fields" disabled class="like-input" style="display: none;">

				<button id="add_product_custom_fields" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
				<br>
				<select id="product_custom_fields_check" multiple name="settings[product_custom_fields][]" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'product_custom_fields' ] )
						foreach ( $settings[ 'product_custom_fields' ] as $prod ) {
							?>
							<option selected value="<?php echo $prod; ?>"> <?php echo $prod; ?></option>
						<?php } ?>
				</select>

				<span class="wc-oe-header"><?php _e( 'Variable product attributes', 'woocommerce-order-export' ) ?></span>
				<br>
				<select id="attributes" style="width: auto;">
					<?php foreach ( WC_Order_Export_Data_Extractor_UI::get_product_attributes() as $attr_id => $attr_name ) { ?>
						<option><?php echo $attr_name; ?></option>
					<?php } ?>
				</select>

				<select id="attributes_compare" class="select_compare">
					<option>=</option>
					<option>&lt;&gt;</option>
					<option>LIKE</option>
				</select>

				<input type="text" id="text_attributes" disabled class="like-input" style="display: none;">

				<button id="add_attributes" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
				<br>
				<select id="attributes_check" multiple name="settings[product_attributes][]" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'product_attributes' ] )
						foreach ( $settings[ 'product_attributes' ] as $prod ) {
							?>
							<option selected value="<?php echo $prod; ?>"> <?php echo $prod; ?></option>
						<?php } ?>
				</select>

                <span class="wc-oe-header"><?php _e( 'Item meta data', 'woocommerce-order-export' ) ?></span>
				<br>
				<select id="itemmeta" style="width: auto;">
					<?php foreach ( WC_Order_Export_Data_Extractor_UI::get_product_itemmeta() as $attr_name ) { ?>
						<option data-base64="<?php echo base64_encode($attr_name); ?>"  ><?php echo $attr_name; ?></option>
					<?php } ?>
				</select>

				<select id="itemmeta_compare" class="select_compare">
					<option>=</option>
					<option>&lt;&gt;</option>
					<option>LIKE</option>
				</select>

				<input type="text" id="text_itemmeta" disabled class="like-input" style="display: none;">

				<button id="add_itemmeta" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
				<br>
				<select id="itemmeta_check" multiple name="settings[product_itemmeta][]" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'product_itemmeta' ] )
						foreach ( $settings[ 'product_itemmeta' ] as $prod ) {
							?>
							<option selected value="<?php echo $prod; ?>"> <?php echo $prod; ?></option>
						<?php } ?>
				</select>

			</div>
		</div>

		<br>

		<div class="my-block">
			<span class="my-hide-next "><?php _e( 'Filter by customers', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
			<div id="my-users" hidden="hidden">
				<span class="wc-oe-header"><?php _e( 'User roles', 'woocommerce-order-export' ) ?></span>
				<select id="user_roles" name="settings[user_roles][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php
					global $wp_roles;
					foreach ( $wp_roles->role_names as $k => $v ) { ?>
						<option value="<?php echo $k ?>" <?php echo ( in_array($k, $settings[ 'user_roles' ] ) ? selected(true) : '') ?>> <?php echo $v ?></option>
					<?php } ?>
				</select>

				<span class="wc-oe-header"><?php _e( 'Usernames', 'woocommerce-order-export' ) ?></span>
				<select id="user_names" name="settings[user_names][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'user_names' ] )
						foreach ( $settings[ 'user_names' ] as $user_id ) {
							$user = get_user_by( 'id', $user_id );
							?>
							<option selected value="<?php echo $user_id ?>"> <?php echo $user->display_name; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<br>

		<div class="my-block">
			<span class="my-hide-next "><?php _e( 'Filter by coupons', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
			<div id="my-coupons" hidden="hidden">
                <div>
                    <input type="hidden" name="settings[any_coupon_used]" value="0"/>
                    <label><input type="checkbox" name="settings[any_coupon_used]" value="1" <?php checked($settings['any_coupon_used']) ?>/><?php _e( 'Any coupon used', 'woocommerce-order-export' ) ?></label>
                </div>
				<span class="wc-oe-header"><?php _e( 'Coupons', 'woocommerce-order-export' ) ?></span>
				<select id="coupons" name="settings[coupons][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings['coupons'] )
						foreach ( $settings['coupons'] as $coupon ) {
							?>
							<option selected value="<?php echo $coupon; ?>"> <?php echo $coupon; ?></option>
						<?php } ?>
				</select>
			</div>
		</div>

		<br>

		<div class="my-block">
			<span class="my-hide-next "><?php _e( 'Filter by billing', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
			<div id="my-billing" hidden="hidden">
                <span class="wc-oe-header"><?php _e( 'Billing locations', 'woocommerce-order-export' ) ?></span>
                <br>
                <select id="billing_locations">
                    <option>City</option>
                    <option>State</option>
                    <option>Postcode</option>
                    <option>Country</option>
                </select>
                <select id="billing_compare" class="select_compare">
                    <option>=</option>
                    <option>&lt;&gt;</option>
                </select>

                <button id="add_billing_locations" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
                <br>
                <select id="billing_locations_check" multiple name="settings[billing_locations][]" style="width: 100%; max-width: 25%;">
                    <?php
                    if ( $settings[ 'billing_locations' ] )
                        foreach ( $settings[ 'billing_locations' ] as $location ) {
                            ?>
                            <option selected value="<?php echo $location; ?>"> <?php echo $location; ?></option>
                        <?php } ?>
                </select>

				<span class="wc-oe-header"><?php _e( 'Payment methods', 'woocommerce-order-export' ) ?></span>
				<select id="payment_methods" name="settings[payment_methods][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php foreach ( WC()->payment_gateways->payment_gateways() as $gateway ) { ?>
						<option value="<?php echo $gateway->id ?>" <?php if ( in_array( $gateway->id, $settings[ 'payment_methods' ] ) ) echo 'selected'; ?>><?php echo $gateway->get_title() ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<br>

		<div class="my-block">
			<span class="my-hide-next "><?php _e( 'Filter by shipping', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
			<div id="my-shipping" hidden="hidden">
				<span class="wc-oe-header"><?php _e( 'Shipping locations', 'woocommerce-order-export' ) ?></span>
				<br>
				<select id="shipping_locations">
					<option>City</option>
					<option>State</option>
					<option>Postcode</option>
					<option>Country</option>
				</select>
				<select id="shipping_compare" class="select_compare">
					<option>=</option>
					<option>&lt;&gt;</option>
				</select>

				<button id="add_shipping_locations" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
				<br>
				<select id="shipping_locations_check" multiple name="settings[shipping_locations][]" style="width: 100%; max-width: 25%;">
					<?php
					if ( $settings[ 'shipping_locations' ] )
						foreach ( $settings[ 'shipping_locations' ] as $location ) {
							?>
							<option selected value="<?php echo $location; ?>"> <?php echo $location; ?></option>
						<?php } ?>
				</select>

				<span class="wc-oe-header"><?php _e( 'Shipping methods', 'woocommerce-order-export' ) ?></span>
				<select id="shipping_methods" name="settings[shipping_methods][]" multiple="multiple" style="width: 100%; max-width: 25%;">
					<?php foreach ( WC_Order_Export_Data_Extractor_UI::get_shipping_methods() as $i => $title ) { ?>
						<option value="<?php echo $i ?>" <?php if ( in_array( $i, $settings[ 'shipping_methods' ] ) ) echo 'selected'; ?>><?php echo $title ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

	</div>

	<div class="clearfix"></div>
	<br>
	<div class="my-block">
		<span id='adjust-fields-btn' class="my-hide-next "><?php _e( 'Set up fields to export', 'woocommerce-order-export' ) ?>
			<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
		<div id="manage_fields" style="display: none;">
			<br>
			<div id='fields_control' style='display:none'>
				<div class='div_meta' style='display:none'>
					<label style="width: 40%;"><?php _e( 'Meta key', 'woocommerce-order-export' ) ?>:
					<select id='select_custom_meta_order'>
							<?php
							foreach ( $order_custom_meta_fields as $meta_id => $meta_name ) {
								echo "<option value='$meta_name' >$meta_name</option>";
							};
							?>
						</select></label>
					<label style="width: 40%;"><?php _e( 'Column name', 'woocommerce-order-export' ) ?>:<input type='text' id='colname_custom_meta'/></label>

					<div id="custom_meta_order_mode">
						<label style="width: 40%;"><input style="width: 80%;" type='text' id='text_custom_meta_order' placeholder="<?php _e('or type meta key here', 'woocommerce-order-export') ?>"/><br></label>
						<label><input id="custom_meta_order_mode_used" type="checkbox" name="custom_meta_order_mode" value="used"> <?php _e('Hide unused fields', 'woocommerce-order-export') ?></label>
					</div>
					<div style="text-align: right;">
						<button  id='button_custom_meta' class='button-secondary'><?php _e( 'Confirm', 'woocommerce-order-export' ) ?></button>
						<button  class='button-secondary button_cancel'><?php _e( 'Cancel', 'woocommerce-order-export' ) ?></button>
					</div>
				</div>
				<div class='div_custom' style='display:none;'>
					<label style="width: 40%;"><?php _e( 'Column name', 'woocommerce-order-export' ) ?>:<input type='text' id='colname_custom_field'/></label>
					<label style="width: 40%;"><?php _e( 'Value', 'woocommerce-order-export' ) ?>:<input type='text' id='value_custom_field'/></label>
					<div style="text-align: right;">
						<button  id='button_custom_field' class='button-secondary'><?php _e( 'Confirm', 'woocommerce-order-export' ) ?></button>
						<button   class='button-secondary button_cancel'><?php _e( 'Cancel', 'woocommerce-order-export' ) ?></button>
					</div>
				</div>
				<div class='div1'><span><strong><?php _e( 'Use sections', 'woocommerce-order-export' ) ?>:</strong></span> <?php
					foreach ( WC_Order_Export_Data_Extractor_UI::get_order_segments() as $section_id => $section_name ) {
						echo "<label ><input type=checkbox value=$section_id checked class='field_section'>$section_name &nbsp;</label>";
					}
					?>
				</div>
				<div class='div2'>
					<span><strong><?php _e( 'Actions', 'woocommerce-order-export' ) ?>:</strong></span>
					<button  id='orders_add_custom_meta' class='button-secondary'><?php _e( 'Add field', 'woocommerce-order-export' ) ?></button>
					<br><br>
					<button  id='orders_add_custom_field' class='button-secondary'><?php _e( 'Add static field', 'woocommerce-order-export' ) ?></button>
                    <br><br>
                    <button id='hide_unchecked' class='button button-secondary'>
                        <div style="padding:0px;"><?php _e( 'Hide unused fields', 'woocommerce-order-export' ) ?></div>
                        <div style="padding:0px;display:none"><?php _e( 'Show unused fields', 'woocommerce-order-export' ) ?></div>
                    </button>
				</div>
			</div>
			<div id='fields' style='display:none;'>
				<br>
				<div class="mapping_col_2">
					<label style="margin-left: 3px;">
						<input type="checkbox" name="orders_all"> <?php _e( 'Select all', 'woocommerce-order-export' ) ?></label>
				</div>
				<label class="mapping_col_3" style="color: red; font-size: medium;">
					<?php _e( 'Drag rows to reorder exported fields', 'woocommerce-order-export' ) ?>
				</label>
				<br>
				<ul id="order_fields"></ul>

			</div>
			<div id="modal_content" style="display: none;"></div>
		</div>

	</div>
     <?php do_action("woe_settings_above_buttons", $settings); ?>
	<div id=JS_error_onload style='color:red;font-size: 120%;'><?php echo sprintf(__( "If you see this message after page load, user interface won't work correctly!<br>There is a JS error (<a target=blank href='%s'>read here</a> how to view it). Probably, it's a conflict with another plugin or active theme.", 'woocommerce-order-export' ) , "https://codex.wordpress.org/Using_Your_Browser_to_Diagnose_JavaScript_Errors#Step_3:_Diagnosis"); ?></div>
	<p class="submit">
		<input type="submit" id='preview-btn' class="button-secondary preview-btn"  data-limit="<?php echo ($mode === WC_Order_Export_Manage::EXPORT_ORDER_ACTION ? 1 : 5); ?>" value="<?php _e( 'Preview', 'woocommerce-order-export' ) ?>" title="<?php _e( 'Might be different from actual export!', 'woocommerce-order-export' ) ?>" />
		<input type="submit" id='save-btn' class="button-primary" value="<?php _e( 'Save settings', 'woocommerce-order-export' ) ?>" />
		<?php if ( $show[ 'export_button' ] ) { ?>
			<input type="submit" id='export-btn' class="button-secondary" value="<?php _e( 'Export', 'woocommerce-order-export' ) ?>" />
		<?php } ?>
		<?php if ( $show[ 'export_button_plain' ] ) { ?>
			<input type="submit" id='export-wo-pb-btn' class="button-secondary" value="<?php _e( 'Export [w/o progressbar]', 'woocommerce-order-export' ) ?>" title="<?php _e( 'It might not work for huge datasets!', 'woocommerce-order-export' ) ?>"/>
		<?php } ?>
		<?php if ( $mode === WC_Order_Export_Manage::EXPORT_NOW && $WC_Order_Export::is_full_version() ): ?>
            <input type="submit" id='copy-to-profiles' class="button-secondary" value="<?php _e( 'Save as a profile', 'woocommerce-order-export' ) ?>" />
		<?php endif; ?>
		<span id="preview_actions" class="hide">
			<strong id="output_preview_total"><?php echo sprintf( __( 'Export total: %s orders', 'woocommerce-order-export' ), '<span></span>') ?></strong>
			<?php _e( 'Preview size', 'woocommerce-order-export' ); ?>
			<?php foreach( array( 5, 10, 25, 50 ) as $n ): ?>
				<button class="button-secondary preview-btn" data-limit="<?php echo $n; ?>"><?php echo $n; ?></button>
			<?php endforeach ?>
		</span>
	</p>
	<?php if ( $show[ 'export_button' ] OR $show[ 'export_button_plain' ] ) { ?>
		<div id="progress_div" style="display: none;">
			<h1 class="title-cancel"><?php _e( "Press 'Esc' to cancel the export", 'woocommerce-order-export' ) ?></h1>
			<h1 class="title-download"><a target=_blank><?php _e( "Click here to download", 'woocommerce-order-export' ) ?></a></h1>
			<div id="progressBar"><div></div></div>
		</div>
		<div id="background"></div>
	<?php } ?>

</form>
<textarea rows=10 id='output_preview' style="overflow: auto;" wrap='off'></textarea>
<div id='output_preview_csv' style="overflow: auto;width:100%"></div>

<iframe id='export_new_window_frame' width=0 height=0 style='display:none'></iframe>

<form id='export_wo_pb_form' method='post' target='export_wo_pb_window'>
	<input name="action" type="hidden" value="order_exporter">
	<input name="method" type="hidden" value="plain_export">
	<input name="mode" type="hidden" value="<?php echo $mode ?>">
	<input name="id" type="hidden" value="<?php echo $id ?>">
	<input name="json" type="hidden">
</form>

<script>
function makeJsonVar( obj ) {
	return encodeURIComponent( makeJson( obj ) ) ;
}	
function makeJson( obj ) {
	return JSON.stringify( obj.serializeJSON() )  ;
}		

	jQuery( document ).ready( function( $ ) {
	
		$( '#d-schedule-3 .btn-add' ).click( function(e) {
			var times = $( 'input[name="settings[schedule][times]"]' ).val();
			var weekday = $( '#d-schedule-3 .wc_oe-select-weekday' ).val();
			var time = $( '#d-schedule-3 .wc_oe-select-time' ).val();

			if( times.indexOf( weekday + ' ' + time) != -1 ) {
				return;
			}

			var data = [];
			if( times != '' ) {
				data = times.split( ',' ).map( function( time ) {
					var arr = time.split( ' ' );
					return { weekday: arr[ 0 ], time: arr[ 1 ] };
				} );
			}

			data.push( { weekday: weekday, time: time } );

			var weekdays = {
				'Sun': 1,
				'Mon': 2,
				'Tue': 3,
				'Wed': 4,
				'Thu': 5,
				'Fri': 6,
				'Sat': 7,
			};

			data.sort( function( a, b ) {
				if( weekdays[ a.weekday ] == weekdays[ b.weekday ] ) {
					return new Date( '1970/01/01 ' + a.time ) - new Date( '1970/01/01 ' + b.time );
				} else {
					return weekdays[ a.weekday ] - weekdays[ b.weekday ];
				}
			} );

			var html = data.map( function( elem ) {
				var weekday = day_names[elem.weekday] ;
				return '<div class="time"><span class="btn-delete">×</span>'
                        + weekday + ' ' + elem.time + '</div>';
			} ).join( '' );

			times = data.map( function( elem ) {
				return elem.weekday + ' ' + elem.time;
			} ).join();

			$( '#d-schedule-3 .input-times' ).html( html );
			$( '#d-schedule-3 .btn-delete' ).click( shedule3_time_delete );

			$( 'input[name="settings[schedule][times]"]' ).val( times );
		} );

		$( '#d-schedule-3 .input-times' ).ready( function() {
			var times = $( 'input[name="settings[schedule][times]"]' ).val();
			if( !times || times == '' ) {
				return;
			}
			var data = times.split( ',' );
			var html = data.map( function( elem ) {
				var x = elem.split(' ');
				var weekday = day_names[x[0]] + ' ' + x[1];
				return '<div class="time"><span class="btn-delete">×</span>' + weekday + '</div>';
			} ).join( '' );
			$( '#d-schedule-3 .input-times' ).html( html );
			$( '#d-schedule-3 .btn-delete' ).click( shedule3_time_delete );
		} );

		function shedule3_time_delete( e ) {
			var index = $( this ).parent().index();
			var data = $( 'input[name="settings[schedule][times]"]' ).val().split( ',' );
			data.splice( index, 1 );
			$( 'input[name="settings[schedule][times]"]' ).val( data.join() );
			$( this ).parent().remove();
		}


		$( '#schedule-1,#schedule-2,#schedule-3' ).change( function() {
			if ( $( '#schedule-1' ).is( ':checked' ) && $( '#schedule-1' ).val() == 'schedule-1' ) {
				$( '#d-schedule-2 input:not(input[type=radio])' ).attr( 'disabled', true )
				$( '#d-schedule-2 select' ).attr( 'disabled', true )
				$( '#d-schedule-1 input:not(input[type=radio])' ).attr( 'disabled', false )
				$( '#d-schedule-1 select' ).attr( 'disabled', false )
				$( '#d-schedule-3 .block' ).addClass( 'disabled' );
			} else if( $( '#schedule-2' ).is( ':checked' ) && $( '#schedule-2' ).val() == 'schedule-2' ) {
				$( '#d-schedule-1 input:not(input[type=radio])' ).attr( 'disabled', true )
				$( '#d-schedule-1 select' ).attr( 'disabled', true )
				$( '#d-schedule-2 select' ).attr( 'disabled', false )
				$( '#d-schedule-2 input:not(input[type=radio]) ' ).attr( 'disabled', false )
				$( '#d-schedule-3 .block' ).addClass( 'disabled' );
			} else if( $( '#schedule-3' ).is( ':checked' ) && $( '#schedule-3' ).val() == 'schedule-3' ) {
				$( '#d-schedule-1 input:not(input[type=radio])' ).attr( 'disabled', true )
				$( '#d-schedule-1 select' ).attr( 'disabled', true )

				$( '#d-schedule-2 input:not(input[type=radio])' ).attr( 'disabled', true )
				$( '#d-schedule-2 select' ).attr( 'disabled', true )
				
				$( '#d-schedule-3 .block' ).removeClass( 'disabled' );
			}
		} );
		$( '#schedule-1' ).change()
		$( '.wc_oe-select-interval' ).change( function() {
			var interval = $( this ).val()
			if ( interval == 'custom' ) {
				$( '#custom_interval' ).show()
			} else {
				$( '#custom_interval' ).hide()
			}
		} );
		$( '.wc_oe-select-interval' ).change()

		$( '.output_destination' ).click( function() {
			var input = $( this ).find( 'input' );
			var target = input.val();
			$( '.set-destination:not(#' + target + ')' ).hide();
			$( '.my-icon-triangle' ).removeClass( 'ui-icon-triangle-1-n' );
			$( '.my-icon-triangle' ).addClass( 'ui-icon-triangle-1-s' );
			if ( !jQuery( '#' + target ).is( ':hidden' ) ) {
				jQuery( '#' + target ).hide();
			}
			else {
				if ( jQuery( '#' + target ).is( ':hidden' ) ) {
					jQuery( '#' + target ).show();
					$( '#test_reply_div' ).hide();
					$( input ).next().removeClass( 'ui-icon-triangle-1-s' );
					$( input ).next().addClass( 'ui-icon-triangle-1-n' );
				}
			}
		} );

        var is_unchecked_shown = true;
        $('#hide_unchecked').on('click', function(e) {
            e.preventDefault();
            is_unchecked_shown = !is_unchecked_shown;
            $("#order_fields li input:checkbox:not(:checked)").closest('.mapping_row').toggle(is_unchecked_shown);
            $('#hide_unchecked div').toggle();
        });

		function my_hide( item ) {
			if ( $( item ).is( ':hidden' ) ) {
				$( item ).show();
				return false;
			}
			else {
				$( item ).hide();
				return true;
			}
		}

		$( '.my-hide-parent' ).click( function() {
			my_hide( $( this ).parent() );
		} );

		$( '.my-hide-next' ).click( function() {
			var f = my_hide( $( this ).next() );
			if ( f ) {
				$( this ).find( 'span' ).removeClass( 'ui-icon-triangle-1-n' );
				$( this ).find( 'span' ).addClass( 'ui-icon-triangle-1-s' );
			}
			else {
				$( this ).find( 'span' ).removeClass( 'ui-icon-triangle-1-s' );
				$( this ).find( 'span' ).addClass( 'ui-icon-triangle-1-n' );
			}
			return false;
		} );


		$( '.wc_oe_test' ).click( function() {
			var test = $( this ).attr( 'data-test' );
			var data = 'json=' + makeJsonVar( $( '#export_job_settings' ) )
			data = data + "&action=order_exporter&method=test_destination&mode=" + mode + "&id=" + job_id + "&destination=" + test;
			$( '#test_reply_div' ).hide();
			$.post( ajaxurl, data, function( data ) {
				$( '#test_reply' ).val( data );
				$( '#test_reply_div' ).show();
			} )
		} )
	} )

	function remove_custom_field( item ) {
		jQuery( item ).parent().parent().remove();
		return false;
	}

	function create_fields( format , format_changed) {
		jQuery( '#export_job_settings' ).prepend( jQuery( "#fields_control_products" ) );
		jQuery( '#export_job_settings' ).prepend( jQuery( "#fields_control_coupons" ) );
		jQuery( "#order_fields" ).html();
		jQuery( "#modal_content" ).html( "" );

		var html = '';
		js_tpl_popup = "<?php _e( 'Add %s as %s columns %s as rows', 'woocommerce-order-export' ) ?>";
		jQuery.each( window['order_fields'], function( index, value ) {
			var checked = ( value.checked == 1 ) ? 'checked' : '';
			var colname = value.colname;

                        colname     = escapeStr(colname);
                        value.label = escapeStr(value.label);
                        index       = escapeStr(index);
                        value.value = escapeStr(value.value);

//                         console.log(index);
//                         console.log(value);

                        if(format_changed) {
				if( is_flat_format( format ) )
					colname = value.label;
				else if ( is_xml_format( format ) )
					colname = to_xml_tags( index );
				else
					colname = index;;
			}


			if ( index == 'products' || index == 'coupons' ) {
				var sel_rows = ( value.repeat == 'rows' ) ? 'checked' : '';
				var sel_cols = ( value.repeat == 'columns' ) ? 'checked' : '';
				var max_cols = ( typeof(value.max_cols) !== 'undefined' ) ? value.max_cols : "10";
				var modal = '<div id="modal-manage-' + index + '" style="display:none;"><p>';
				modal += create_modal_fields( format, index, format_changed);
				modal += '</p></div>';
				jQuery( "#modal_content" ).append( modal );
				var row = '<li class="mapping_row segment_' + value.segment + '">\
                                                        <div class="mapping_col_1">\
                                                                <input type=hidden name="orders[segment][' + index + ']"  value="' + value.segment + '">\
                                                                <input type=hidden name="orders[label][' + index + ']"  value="' + value.label + '">\
                                                                <input type=hidden name="orders[exported][' + index + ']"  value="0">\
                                                                <input type=checkbox name="orders[exported][' + index + ']"  ' + checked + ' value="1">\
                                                        </div>\
                                                        <div class="mapping_col_2">' + value.label + '</div>\
                                                        <div class="mapping_col_3">';
				if ( is_flat_format( format ) ) {

					var popup_options = js_tpl_popup;
					popup_options = popup_options.replace('%s', '<input type=radio name="orders[repeat][' + index + ']" value="columns" ' + sel_cols + ' >')
					popup_options = popup_options.replace('%s', '<input type=text size=2 name="orders[max_cols][' + index + ']" value="'+max_cols+'">')
					popup_options = popup_options.replace('%s', '<input type=radio name="orders[repeat][' + index + ']" value="rows" ' + sel_rows + ' >')
					row += 	popup_options;
				}        
				row += '<input class="mapping_fieldname" type=input name="orders[colname][' + index + ']" value="' + colname + '">\
                                                        <input type="button" class="button-primary" id="btn_modal_manage_' + index + '" value="<?php _e( 'Set up fields to export', 'woocommerce-order-export' ) ?>" /><a href="#TB_inline?width=600&height=550&inlineId=modal-manage-' + index + '" class="thickbox " id="link_modal_manage_' + index + '"> </a></div>\
                                                </li>\
                        ';
			}
			else {
				var value_part = ''
				var label_part = '';
				if ( index.indexOf( 'custom_field' ) >= 0 ) {
					value_part = '<div class="mapping_col_3"><input class="mapping_fieldname" type=input name="orders[value][' + index + ']" value="' + value.value + '"></div>';
					label_part = '<a href="#" onclick="return remove_custom_field(this);" style="float: right;"><span class="ui-icon ui-icon-trash"></span></a>';
				}
				else if ( index.charAt( 0 ) == '_'  || !value.default) {
					label_part = '<a href="#" onclick="return remove_custom_field(this);" style="float: right;"><span class="ui-icon ui-icon-trash"></span></a>';
				}

                             var row = '<li class="mapping_row segment_' + value.segment + '">\
                                                        <div class="mapping_col_1">\
                                                                <input type=hidden name="orders[segment][' + index + ']"  value="' + value.segment + '">\
                                                                <input type=hidden name="orders[label][' + index + ']"  value="' + value.label + '">\
                                                                <input type=hidden name="orders[exported][' + index + ']"  value="0">\
                                                                <input type=checkbox name="orders[exported][' + index + ']"  ' + checked + ' value="1">\
                                                        </div>\
                                                        <div class="mapping_col_2">' + value.label + label_part + '</div>\
                                                        <div class="mapping_col_3"><input class="mapping_fieldname" type=input name="orders[colname][' + index + ']" value="' + colname + '"></div> ' + value_part + '\
                                                </li>\
                        ';
			}
			html += row;
		} );

		jQuery( "#order_fields" ).html( html );
		jQuery( '#modal-manage-products' ).prepend( jQuery( "#fields_control_products" ) );
		jQuery( '#modal-manage-coupons' ).prepend( jQuery( "#fields_control_coupons" ) );
		jQuery( "#fields_control_products" ).css( 'display', 'inline-block' );
		jQuery( "#fields_control_coupons" ).css( 'display', 'inline-block' );
		add_bind_for_custom_fields( 'products', output_format, jQuery( "#sort_products" ) );
		add_bind_for_custom_fields( 'coupons', output_format, jQuery( "#sort_coupons" ) );

	}



	function create_modal_fields( format, index_p, format_changed ) {
		//console.log( 'order_' + index_p + '_fields', window['order_' + index_p + '_fields'] );

		var modal = "<div id='sort_" + index_p + "'>";
		jQuery.each( window['order_' + index_p + '_fields'], function( index, value ) {
			var checked = ( value.checked == 1 ) ? 'checked' : '';
			var colname = value.colname;

//                         console.log(index);
//                         console.log(value);


                        colname     = escapeStr(colname);
                        value.label = escapeStr(value.label);
                        index       = escapeStr(index);
                        value.value = escapeStr(value.value);

			if(format_changed) {
				if( is_flat_format( format ) )
					colname = value.label;
				else if ( is_xml_format( format ) )
					colname = to_xml_tags( index );
				else
					colname = index;;
			}

			var value_part = ''
			var label_part = '';
			if ( index.indexOf( 'custom_field' ) >= 0 ) {
				value_part = '<div class="mapping_col_3"><input class="mapping_fieldname" type=input name="' + index_p + '[value][' + index + ']" value="' + value.value + '"></div>';
				label_part = '<a href="#" onclick="return remove_custom_field(this);" style="float: right;"><span class="ui-icon ui-icon-trash"></span></a>';
			}
			else if ( index.charAt( 0 ) == '_'  || index.substr( 0,3 ) == 'pa_' || !value.default) {
				label_part = '<a href="#" onclick="return remove_custom_field(this);" style="float: right;"><span class="ui-icon ui-icon-trash"></span></a>';
			}

			var row = '<li class="mapping_row segment_modal_' + index + '">\
                                                        <div class="mapping_col_1">\
                                                                <input type=hidden name="' + index_p + '[label][' + index + ']"  value="' + value.label + '">\
                                                                <input type=hidden name="' + index_p + '[exported][' + index + ']"  value="0">\
                                                                <input type=checkbox name="' + index_p + '[exported][' + index + ']"  ' + checked + ' value="1">\
                                                        </div>\
                                                        <div class="mapping_col_2">' + value.label + label_part + '</div>\
                                                        <div class="mapping_col_3"><input class="mapping_fieldname" type=input name="' + index_p + '[colname][' + index + ']" value="' + colname + '"></div>' + value_part + '\
                                                </li>\
                        ';
			modal += row;
		} );
		modal += "</div>";
		return modal;
	}

	//for XML labels
	function to_xml_tags( str ) {
		var arr = str.split( /_/ );
		for ( var i = 0, l = arr.length; i < l; i++ ) {
			arr[i] = arr[i].substr( 0, 1 ).toUpperCase() + ( arr[i].length > 1 ? arr[i].substr( 1 ).toLowerCase() : "" );
		}
		return arr.join( "_" );
	}


	function change_filename_ext() {
		if ( jQuery( '#export_filename' ).size() ) {
			var filename = jQuery( '#export_filename input' ).val();
			var ext = output_format.toLowerCase();
			if( ext=='xls'  && !jQuery( '#format_xls_use_xls_format' ).prop('checked') ) //fix for XLSX
				ext = 'xlsx';
				
			var file = filename.replace( /^(.*)\..+$/, "$1." + ext );
			if( file.indexOf(".") == -1)  //no dots??
				file = file + "." + ext;
			jQuery( '#export_filename input' ).val( file );
			show_summary_report(output_format);
		}
	}
	
	function show_summary_report(ext) {
		if( is_flat_format(ext) ) {
			jQuery( '#summary_report_by_products' ).show();
		} else  {
			jQuery( '#summary_report_by_products' ).hide();
			jQuery( '#summary_setup_fields' ).hide();
			jQuery( '#summary_report_by_products_checkbox' ).prop('checked', false);
		}	
	}

        function modal_buttons()
        {
            jQuery('body').on('click', '#btn_modal_manage_products', function() {

                jQuery('input[name=custom_meta_products_mode]').change();
                jQuery('#link_modal_manage_products').click();

                return false;
            });

            jQuery('body').on('click', '#btn_modal_manage_coupons', function() {

                jQuery('#custom_meta_coupons_mode_all').attr('checked', 'checked');
                jQuery('#custom_meta_coupons_mode_all').change();
                jQuery('#link_modal_manage_coupons').click();

                return false;
            });

        }

	jQuery( document ).ready( function( $ ) {

		try {
			select2_inits();
		}
		catch ( err ) {
			console.log( err.message );
			jQuery( '#select2_warning' ).show();
		}

        jQuery( "#settings_title" ).focus();

		bind_events();
        jQuery( '#taxonomies' ).change();
		jQuery( '#attributes' ).change();
		if ( jQuery( '#itemmeta option' ).length>0 )
			jQuery( '#itemmeta' ).change();
		jQuery( '#custom_fields' ).change();
		jQuery( '#product_custom_fields' ).change();
		jQuery( '#shipping_locations' ).change();
        jQuery( '#billing_locations' ).change();
//		jQuery( '#' + output_format + '_options' ).show();

		//jQuery('#fields').toggle(); //debug
		create_fields( output_format, false );
		$( '#test_reply_div' ).hide();
//		jQuery( '#' + output_format + '_options' ).hide();

		jQuery( "#sort_products" ).sortable()/*.disableSelection()*/;
		jQuery( "#sort_coupons" ).sortable()/*.disableSelection()*/;
		jQuery( "#order_fields" ).sortable({ scroll: true, scrollSensitivity: 100, scrollSpeed: 100 });/*.disableSelection()*/;


        modal_buttons();

		jQuery( '.date' ).datepicker( {
			dateFormat: 'yy-mm-dd'
		} );

		jQuery( '#adjust-fields-btn' ).click( function() {
			jQuery( '#fields' ).toggle();
			jQuery( '#fields_control' ).toggle();
			return false;
		} );

		jQuery( '.field_section' ).click( function() {
			var section = jQuery( this ).val();
			var checked = jQuery( this ).is( ':checked' );

			jQuery( '.segment_' + section ).each( function( index ) {
				if ( checked ) {
					jQuery( this ).show();
					//jQuery(this).find('input:checkbox:first').attr('checked', true);
				}
				else {
					jQuery( this ).hide();
					jQuery( this ).find( 'input:checkbox:first' ).attr( 'checked', false );
				}
			} );
		} );

		jQuery( '.output_format' ).click( function() {
			var new_format = jQuery( this ).val();
			jQuery( '#my-format .my-icon-triangle' ).removeClass( 'ui-icon-triangle-1-n' );
			jQuery( '#my-format .my-icon-triangle' ).addClass( 'ui-icon-triangle-1-s' );

			if ( new_format != output_format ) {
				jQuery( this ).next().removeClass( 'ui-icon-triangle-1-s' );
				jQuery( this ).next().addClass( 'ui-icon-triangle-1-n' );
				jQuery( '#' + output_format + '_options' ).hide();
				jQuery( '#' + new_format + '_options' ).show();
				output_format = new_format;
				create_fields( output_format, true )
				jQuery( '#output_preview, #output_preview_csv' ).hide();
//				jQuery( '#fields' ).hide();
//				jQuery( '#fields_control' ).hide();
				change_filename_ext();
			}
			else {
				if ( !jQuery( '#' + new_format + '_options' ).is( ':hidden' ) ) {
					jQuery( '#' + new_format + '_options' ).hide();
				}
				else {
					if ( jQuery( '#' + new_format + '_options' ).is( ':hidden' ) ) {
						jQuery( '#' + new_format + '_options' ).show();
						jQuery( this ).next().removeClass( 'ui-icon-triangle-1-s' );
						jQuery( this ).next().addClass( 'ui-icon-triangle-1-n' );
					}
				}
			}

		} );

		$( '#date_format_block select' ).change( function() {
			var value = $( this ).val();
			if( value == 'custom' ) {
				$( '#custom_date_format_block' ).show();
			} else {
				$( '#custom_date_format_block' ).hide();
				$( 'input[name="settings[date_format]"]' ).val( value );				
			}
		} );

		$( '#time_format_block select' ).change( function() {
			var value = $( this ).val();
			if( value == 'custom' ) {
				$( '#custom_time_format_block' ).show();
			} else {
				$( '#custom_time_format_block' ).hide();
				$( 'input[name="settings[time_format]"]' ).val( value );
			}
		} );

		$( 'input[type="checkbox"][name="settings[custom_php]"]' ).change( function() {
			$( 'textarea[name="settings[custom_php_code]"]' ).toggle( $( this ).is( ':checked' ) );
		} );

		$( '#order_fields input[type=checkbox]' ).change( function() {
			if ( $( '#order_fields input[type=checkbox]:not(:checked)' ).size() ) {
				$( 'input[name=orders_all]' ).attr( 'checked', false );
			}
			else {
				$( 'input[name=orders_all]' ).attr( 'checked', true );
			}
		} );

		$( 'input[name=orders_all]' ).change( function() {
			if ( $( 'input[name=orders_all]' ).is( ':checked' ) ) {
				$( '#order_fields input[type=checkbox]' ).attr( 'checked', true );
			}
			else {
				$( '#order_fields input[type=checkbox]' ).attr( 'checked', false );
			}
		} );

		if ( $( '#order_fields input[type=checkbox]' ).size() ) {
			$( '#order_fields input[type=checkbox]:first' ).change();
		}




		$( ".preview-btn" ).click( function() {
			preview(jQuery(this).attr('data-limit'));
			return false;
		} );

		$( '#progress_div .title-download' ).click( function() {
			$( '#progress_div .title-download' ).hide();
			$( '#progress_div .title-cancel' ).show();
			$( '#progressBar' ).show();
			jQuery( '#progress_div' ).hide();
			closeWaitingDialog();
		});

		function preview(size) {
			jQuery( '#output_preview, #output_preview_csv' ).hide();
			var data = 'json=' + makeJsonVar( $( '#export_job_settings' ) );
			var estimate_data = data + "&action=order_exporter&method=estimate&mode=" + mode + "&id=" + job_id;
			$.post( ajaxurl, estimate_data, function( response ) {
						if ( response.total !== undefined ) {
							jQuery( '#output_preview_total' ).find( 'span' ).html( response.total );
							jQuery( '#preview_actions' ).removeClass( 'hide' );
						}
					}, "json"
			);

			function showPreview( response ) {
				var id = 'output_preview';
				if ( is_flat_format( output_format ) )
					id = 'output_preview_csv';
				if ( is_object_format( output_format ) ) {
					jQuery( '#' + id ).text( response );
				}
				else {
					jQuery( '#' + id ).html( response );
				}
				jQuery( '#' + id ).show();
				window.scrollTo( 0, document.body.scrollHeight );
			}
			
			data = data + "&action=order_exporter&method=preview&limit="+size+"&mode=" + mode + "&id=" + job_id;
			$.post( ajaxurl, data, showPreview, "html" ).fail( function( xhr, textStatus, errorThrown ) {
				showPreview( xhr.responseText );
			});
		}
// EXPORT FUNCTIONS
		function get_data() {
			var data = new Array();
			data.push( { name: 'json', value: makeJson( $( '#export_job_settings' ))  } );
			data.push( { name: 'action', value: 'order_exporter' } );
			data.push( { name: 'mode', value: mode } );
			data.push( { name: 'id', value: job_id } );
			return data;
		}

		function progress( percent, $element ) {

			if ( percent == 0 ) {
				$element.find( 'div' ).html( percent + "%&nbsp;" ).animate( { width: 0 }, 0 );
				waitingDialog();
				jQuery( '#progress_div' ).show();
			}
			else {
				var progressBarWidth = percent * $element.width() / 100;
				$element.find( 'div' ).html( percent + "%&nbsp;" ).animate( { width: progressBarWidth }, 200 );

				if ( percent >= 100 ) {
					if(!is_iPad_or_iPhone()) {
						jQuery( '#progress_div' ).hide();
						closeWaitingDialog();
					}
				}
			}
		}

		function get_all( start, percent, method ) {
			if (window.cancelling) {
				return;
			}

			progress( parseInt( percent, 10 ), jQuery( '#progressBar' ) );

			if ( percent < 100 ) {
				data = get_data();
				data.push( { name: 'method', value: method } );
				data.push( { name: 'start', value: start } );
				data.push( { name: 'file_id', value: window.file_id } );

				jQuery.ajax( {
					type: "post",
					data: data,
					cache: false,
					url: ajaxurl,
					dataType: "json",
					error: function( xhr, status, error ) {
						alert( xhr.responseText );
						progress( 100, jQuery( '#progressBar' ) );
					},
					success: function( response ) {
						get_all( response.start, ( response.start / window.count ) * 100, method )
					}
				} );
			}
			else {
				data = get_data();
				data.push( { name: 'method', value: 'export_finish' } );
				data.push( { name: 'file_id', value: window.file_id } );
				jQuery.ajax( {
					type: "post",
					data: data,
					cache: false,
					url: ajaxurl,
					dataType: "json",
					error: function( xhr, status, error ) {
						alert( xhr.responseText );
					},
					success: function( response ) {
						var download_format = output_format;
						if( output_format=='XLS' && !jQuery( '#format_xls_use_xls_format' ).prop('checked') )
							download_format =  'XLSX';

						if(is_iPad_or_iPhone()) {
							$( '#progress_div .title-download a' ).attr( 'href', ajaxurl + (ajaxurl.indexOf('?') === -1? '?':'&')+'action=order_exporter&method=export_download&format=' + download_format + '&file_id=' + window.file_id );
							$( '#progress_div .title-download' ).show();
							$( '#progress_div .title-cancel' ).hide();
							$( '#progressBar' ).hide();
						} else {
							$( '#export_new_window_frame' ).attr( "src", ajaxurl + (ajaxurl.indexOf('?') === -1? '?':'&')+'action=order_exporter&method=export_download&format=' + download_format + '&file_id=' + window.file_id );
						}
						
						reset_date_filter_for_cron();
					}
				} );
			}
		}

		function is_iPad_or_iPhone() {
			return navigator.platform.match(/i(Phone|Pad)/i)
		}

		function waitingDialog() {
			jQuery( "#background" ).addClass( "loading" );
			jQuery( '#wpbody-content' ).keydown(function(event) {
				if ( event.keyCode == 27 ) {
					if (!window.cancelling) {
						event.preventDefault();
						window.cancelling = true;

						jQuery.ajax( {
							type: "post",
							data: {
								action: 'order_exporter',
								method: 'cancel_export',
								file_id: window.file_id,
							},
							cache: false,
							url: ajaxurl,
							dataType: "json",
							error: function( xhr, status, error ) {
								alert( xhr.responseText );
								progress( 100, jQuery( '#progressBar' ) );
							},
							success: function( response ) {
								progress( 100, jQuery( '#progressBar' ) );
							}
						} );

						window.count = 0;
						window.file_id = '';
						jQuery( '#wpbody-content' ).off('keydown');
					}
					return false;
				}
			});
		}
		function closeWaitingDialog() {
			jQuery( "#background" ).removeClass( "loading" );
		}

		function openFilter(object_id) {
			var f = false;
			$( '#'+object_id+' ul' ).each( function( index ) {
				if ( $( this ).find( 'li:not(:first)' ).size() ) {
					f = true;
				}
			} );
			if ( f ) {
				$( '#'+object_id ).prev().click();
			}
		}

		function validateExport() {
            if ( ( mode == '<?php echo WC_Order_Export_Manage::EXPORT_PROFILE; ?>' ) && ( !$( "[name='settings[title]']" ).val() ) ) {
                alert( export_messages.empty_title );
                $( "[name='settings[title]']" ).focus();
                return false;
            }

            if ( ( $( "#from_date" ).val() ) && ( $( "#to_date" ).val() ) ) {
                var d1 = new Date( $( "#from_date" ).val() );
                var d2 = new Date( $( "#to_date" ).val() );
                if ( d1.getTime() > d2.getTime() ) {
                    alert( export_messages.wrong_date_range );
                    return false;
                }
            }
            if ( $( '#order_fields input[type=checkbox]:checked' ).size() == 0 )
            {
                alert( export_messages.no_fields );
                return false;
            }

            return true;
        }
// EXPORT FUNCTIONS END
		$( "#export-wo-pb-btn" ).click( function() {
			$( '#export_wo_pb_form' ).attr( "action", ajaxurl );
			$( '#export_wo_pb_form' ).find( '[name=json]' ).val( makeJson( $( '#export_job_settings' ) ) );
			$( '#export_wo_pb_form' ).submit();
			return false;
		} );

		$( "#export-btn, #my-quick-export-btn" ).click( function() {
			window.cancelling = false;

			data = get_data();

			data.push( { name: 'method', value: 'export_start' } );
			if ( ( $( "#from_date" ).val() ) && ( $( "#to_date" ).val() ) ) {
				var d1 = new Date( $( "#from_date" ).val() );
				var d2 = new Date( $( "#to_date" ).val() );
				if ( d1.getTime() > d2.getTime() ) {
					alert( export_messages.wrong_date_range );
					return false;
				}
			}

			if ( $( '#order_fields input[type=checkbox]:checked' ).size() == 0 )
			{
				alert( export_messages.no_fields );
				return false;
			}
			

			jQuery.ajax( {
				type: "post",
				data: data,
				cache: false,
				url: ajaxurl,
				dataType: "json",
				error: function( xhr, status, error ) {
					alert( xhr.responseText.replace(/<\/?[^>]+(>|$)/g, "") );
				},
				success: function( response ) {
					window.count = response['total'];
					window.file_id = response['file_id'];
					console.log( window.count );
					
					if ( window.count > 0 )
						get_all( 0, 0, 'export_part' );
					else {
						alert( export_messages.no_results );
						reset_date_filter_for_cron();
					}	
				}
			} );

			return false;
		} );
		$( "#save-btn" ).click( function() {
			if (!validateExport()) {
			    return false;
            }
			setFormSubmitting();

			var data = 'json=' + makeJsonVar( $( '#export_job_settings' ) )
			data = data + "&action=order_exporter&method=save_settings&mode=" + mode + "&id=" + job_id;
			$.post( ajaxurl, data, function( response ) {
				if ( mode == '<?php echo WC_Order_Export_Manage::EXPORT_SCHEDULE; ?>' ) {
					document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=schedules&save=y' ) ?>';
				} else if ( mode == '<?php echo WC_Order_Export_Manage::EXPORT_PROFILE; ?>' ) {
					document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&save=y' ) ?>';
				} else if ( mode == '<?php echo WC_Order_Export_Manage::EXPORT_ORDER_ACTION; ?>' ) {
					document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=order_actions&save=y' ) ?>';
				} else {
					document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=export&save=y' ) ?>';
				}
			}, "json" );
			return false;
		} );
        $( "#copy-to-profiles" ).click( function() {
            if (!validateExport()) {
                return false;
            }

            var data = 'json=' + makeJsonVar( $( '#export_job_settings' ) )
            data = data + "&action=order_exporter&method=save_settings&mode=<?php echo WC_Order_Export_Manage::EXPORT_PROFILE; ?>&id=";
            $.post( ajaxurl, data, function( response ) {
                document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=edit_profile&profile_id=' ) ?>' + response.id;
            }, "json" );
            return false;
        } );

		openFilter('my-order');

		openFilter('my-products');

		openFilter('my-shipping');

		openFilter('my-users');

		openFilter('my-coupons');

		openFilter('my-billing');

		if ( mode == '<?php echo WC_Order_Export_Manage::EXPORT_SCHEDULE; ?>' ) 
			setup_alert_date_filter();
		//for XLSX
		$('#format_xls_use_xls_format').click(function() {
			change_filename_ext();
		});
		
		show_summary_report( output_format );
		if( !summary_mode ) 
			jQuery('#summary_setup_fields').hide();
		//logic for setup link	
        jQuery( "#summary_report_by_products_checkbox" ).change( function() {
			if( jQuery(this).prop('checked') )
				jQuery('#summary_setup_fields').show();
			else	
				jQuery('#summary_setup_fields').hide();
        });

		// this line must be last , we don't have any errors
		jQuery('#JS_error_onload').hide();
	} );

	function is_flat_format(format) {
        return (flat_formats.indexOf(format) > -1);
    }
    function is_object_format(format) {
        return (object_formats.indexOf(format) > -1);
    }
    function is_xml_format(format) {
        return (xml_formats.indexOf(format) > -1);
    }
    function reset_date_filter_for_cron() {
		if(mode == 'cron') {
			jQuery( "#from_date" ).val("");
			jQuery( "#to_date" ).val("");
			try_color_date_filter();
		}	
    }
</script>
