			<div id="my-shedule-destination" class="my-block">
				<div class="wc-oe-header"><?php _e( 'Destination', 'woocommerce-order-export' ) ?></div>
				<?php 
				if( isset( $settings[ 'destination' ][ 'type' ] )  && !is_array( $settings[ 'destination' ][ 'type' ] ) ) {
					$settings[ 'destination' ][ 'type' ] = array( $settings[ 'destination' ][ 'type' ] );
				}
				?>
				<div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]" value="email"
					<?php if ( isset( $settings[ 'destination' ][ 'type' ] ) AND in_array( 'email', $settings[ 'destination' ][ 'type' ] ) ) echo 'checked'; ?>
													   > <?php _e( 'Email', 'woocommerce-order-export' ) ?>
					<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
				</div>

				<div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]" value="ftp"
					<?php if ( isset( $settings[ 'destination' ][ 'type' ] ) AND in_array( 'ftp', $settings[ 'destination' ][ 'type' ] ) ) echo 'checked'; ?>
													   > <?php _e( 'FTP', 'woocommerce-order-export' ) ?>
					<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
				</div>

				<div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]" value="sftp"
					<?php if ( isset( $settings[ 'destination' ][ 'type' ] ) AND in_array( 'sftp', $settings[ 'destination' ][ 'type' ] ) ) echo 'checked'; ?>
													   > <?php _e( 'SFTP', 'woocommerce-order-export' ) ?>
					<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
				</div>

				<div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]" value="http"
					<?php if ( isset( $settings[ 'destination' ][ 'type' ] ) AND in_array( 'http', $settings[ 'destination' ][ 'type' ] ) ) echo 'checked'; ?>
													   > <?php _e( 'HTTP POST', 'woocommerce-order-export' ) ?>
					<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
				</div>

				<div class="button-secondary output_destination"><input type="checkbox" name="settings[destination][type][]" value="folder"
					<?php if ( isset( $settings[ 'destination' ][ 'type' ] ) AND in_array( 'folder', $settings[ 'destination' ][ 'type' ] ) ) echo 'checked'; ?>
													   > <?php _e( 'Directory', 'woocommerce-order-export' ) ?>
					<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
				</div>

				<div class="padding-bottom-10 set-destination my-block" id="email" style="display: none;" >
					<div class="wc-oe-header"><?php _e( 'Email settings', 'woocommerce-order-export' ) ?></div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'From email', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][email_from]" class="width-100" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][email_from]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'From name', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][email_from_name]" class="width-100" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][email_from_name]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'Email subject', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][email_subject]" class="width-100" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][email_subject]" ); ?>">
							</label>
						</div>
					</div>

					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'Email body', 'woocommerce-order-export' ) ?></div>
								<a href="#TB_inline?width=400&height=400&inlineId=modal-email-body" class="thickbox"><?php _e( 'Edit email body', 'woocommerce-order-export' ) ?></a>
							    <div class=""><input name="settings[destination][email_body_append_file_contents]" type="checkbox" <?php echo $WC_Order_Export->get_value( $settings, "[destination][email_body_append_file_contents]" ) ? 'checked' : ''; ?>><?php _e( 'Append file contents to email body', 'woocommerce-order-export' ) ?></div>
							</label>
						</div>
					</div>


					<div id="modal-email-body" >
							<label><div><?php _e( 'Email body', 'woocommerce-order-export' ) ?></div>
								<textarea name="settings[destination][email_body]" class="email_body_textarea" ><?php echo $WC_Order_Export->get_value( $settings, "[destination][email_body]" ); ?></textarea>
							</label>
					</div>

					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'Recipient(s)', 'woocommerce-order-export' ) ?></div>
								<textarea name="settings[destination][email_recipients]" class="width-100"><?php echo $WC_Order_Export->get_value( $settings, "[destination][email_recipients]" ); ?></textarea>
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'Cc Recipient(s)', 'woocommerce-order-export' ) ?></div>
								<textarea name="settings[destination][email_recipients_cc]" class="width-100"><?php echo $WC_Order_Export->get_value( $settings, "[destination][email_recipients_cc]" ); ?></textarea>
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'Bcc Recipient(s)', 'woocommerce-order-export' ) ?></div>
								<textarea name="settings[destination][email_recipients_bcc]" class="width-100"><?php echo $WC_Order_Export->get_value( $settings, "[destination][email_recipients_bcc]" ); ?></textarea>
							</label>
						</div>
					</div>

					<div class="wc_oe-row">
						<div class="col-100pr">
							<label>
								<div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="email" type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" title="<?php _e( 'It sends only last order!', 'woocommerce-order-export' ) ?>"></div>
							</label>
						</div>
					</div>
				</div>

				<div class="padding-bottom set-destination my-block" id="ftp" style="display: none;">
					<div class="wc-oe-header"><?php _e( 'FTP settings', 'woocommerce-order-export' ) ?></div>
					<div class="wc_oe-row">
						<div class="col-50pr">
							<label><div><?php _e( 'Server name', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][ftp_server]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_server]" ); ?>">
							</label>
						</div>
						<div class="col-50pr">
							<label><div><?php _e( 'Port', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][ftp_port]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_port]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">

						<div class="col-50pr">
							<label><div><?php _e( 'Username', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][ftp_user]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_user]" ); ?>">
							</label>
						</div>
						<div class="col-50pr">
							<label><div><?php _e( 'Password', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][ftp_pass]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_pass]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'Initial path', 'woocommerce-order-export' ) ?></div>
								<input type="text" class="width-100" name="settings[destination][ftp_path]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_path]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label>
								<div class=""><input name="settings[destination][ftp_passive_mode]" type="checkbox" <?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_passive_mode]" ) ? 'checked' : ''; ?>><?php _e( 'Passive mode', 'woocommerce-order-export' ) ?></div>
							</label>
						</div>
						<div class="col-100pr">
							<label>
								<div class=""><input name="settings[destination][ftp_append_existing]" type="checkbox" <?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_append_existing]" ) ? 'checked' : ''; ?>><?php _e( 'Append to existing file (need custom code!)', 'woocommerce-order-export' ) ?></div>
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label>
								<div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="ftp" type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" title="<?php _e( 'It sends only last order!', 'woocommerce-order-export' ) ?>"></div>
							</label>
						</div>
					</div>
				</div>

				<div class="padding-bottom set-destination my-block" id="sftp" style="display: none;">
					<div class="wc-oe-header"><?php _e( 'SFTP settings', 'woocommerce-order-export' ) ?></div>
					<div class="wc_oe-row">
						<div class="col-50pr">
							<label><div><?php _e( 'Server name', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][sftp_server]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][sftp_server]" ); ?>">
							</label>
						</div>
						<div class="col-50pr">
							<label><div><?php _e( 'Port', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][sftp_port]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][sftp_port]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">

						<div class="col-50pr">
							<label><div><?php _e( 'Username', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][sftp_user]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][sftp_user]" ); ?>">
							</label>
						</div>
						<div class="col-50pr">
							<label><div><?php _e( 'Password', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][sftp_pass]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][sftp_pass]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label><div><?php _e( 'Initial path', 'woocommerce-order-export' ) ?></div>
								<input type="text" class="width-100" name="settings[destination][sftp_path]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][sftp_path]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label>
								<div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="sftp" type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>"></div>
							</label>
						</div>
					</div>
				</div>

				<div class="padding-bottom-10 set-destination my-block" id="http" style="display: none;" >
					<div class="wc-oe-header"><?php _e( 'HTTP POST settings', 'woocommerce-order-export' ) ?></div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label>
								<div><?php _e( 'URL', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][http_post_url]" class="width-100" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][http_post_url]" ); ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label>
								<div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="http" type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" title="<?php _e( 'It sends only last order!', 'woocommerce-order-export' ) ?>"></div>
							</label>
						</div>
					</div>
				</div>

				<div class="padding-bottom-10 set-destination my-block" id="folder" style="display: none;" >
					<div class="wc-oe-header"><?php _e( 'Directory settings', 'woocommerce-order-export' ) ?></div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label>
								<div><?php _e( 'Path', 'woocommerce-order-export' ) ?></div>
								<input type="text" name="settings[destination][path]" class="width-100" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][path]" ) ? $WC_Order_Export->get_value( $settings, "[destination][path]" ) : ABSPATH; ?>">
							</label>
						</div>
					</div>
					<div class="wc_oe-row">
						<div class="col-100pr">
							<label>
								<div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="folder" type="button" value="<?php _e( 'Test', 'woocommerce-order-export' ) ?>" title="<?php _e( 'It sends only last order!', 'woocommerce-order-export' ) ?>"></div>
							</label>
						</div>
					</div>
				</div>

				<div id='test_reply_div'>
					<b><?php _e( 'Test Results', 'woocommerce-order-export' ) ?></b><br>
					<textarea rows=5 id='test_reply' style="overflow: auto; width:100%" wrap='off'></textarea>
				</div>

				<div class="clear"></div>
				<br/>
				<div id="extend_desstination">
					<?php if ( $mode !== WC_Order_Export_Manage::EXPORT_ORDER_ACTION ): ?>
						<div>
							<label>
								<input name="settings[destination][separate_files]" type="checkbox" value="1" <?php echo $WC_Order_Export->get_value( $settings, "[destination][separate_files]" ) ? 'checked' : ''; ?>><?php _e( 'Make separate file for each order', 'woocommerce-order-export' ) ?>
							</label>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<br>
