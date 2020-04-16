<?php

defined('ABSPATH') or exit;

class StripeWebhook extends WC_MULTIBANCO_Gateway {
	
	/**
	* Receives and proccesses the webhook received by Stripe
	*
	* @since 2.0
	*/
	function ReceiveWebhook() {
		global $woocommerce;
		$url = $this->api_url . "charges";
		
		$webhook_key_stored = $this->get_option('stripe-webhook-key');
		
		if(isset($_GET['stripe_webhook']) && isset($_GET['key'])) {
			$webhook = sanitize_text_field($_GET['stripe_webhook']);
			$webhook_key = sanitize_text_field($_GET['key']);
		}
		else {
			$webhook = "no";
			$webhook_key = "";
		}
		
		if($webhook == "yes" && $webhook_key_stored == $webhook_key) {
			
			// Stripe webhook received
			$input = json_decode(file_get_contents("php://input"),true);
			$data = $input['data']['object'];
			
			$woo_multibanco_gateway_payment = $data['metadata']['woo-multibanco-gateway'];
			
			
			if($woo_multibanco_gateway_payment == true) {
				// If payment is made via WooCommerce MULTIBANCO Gateway
				
				if($data['type'] == "multibanco") {
					// Source is using MULTIBANCO
					
					if($data['status'] == "chargeable") {
						// Source is chargeable
						
						$order_id_meta = intval($data['metadata']['order_id']);
						
						$order = new WC_Order($order_id_meta);
						
						$amount = $order->get_total();
						$amount = (int) (((string) ($amount*100)));
						
						$source = get_post_meta($order_id_meta, 'woo-multibanco-gateway-stripe-source', true);
						$source_stripe = $data['id'];
						
						$payment_description = $this->get_option('payment-description');
						$payment_description = str_replace("{order_number}", $order_id_meta, $payment_description);

						if($source == $source_stripe) {
							
							if($this->get_option('test-mode') == 'yes') {
								$StripeAPIKey = $this->get_option('stripe-api-test');
							}
							else {
								$StripeAPIKey = $this->get_option('stripe-api-live');
							}
							
							$stripe_data = array(
								'amount' => $amount,
								'currency' => 'eur',
								'source' => $source_stripe,
								'description' => $payment_description
								
							);
							
							$response = wp_remote_post($url, array(
								'method' => 'POST',
								'timeout' => 45,
								'redirection' => 5,
								'httpversion' => '1.0',
								'blocking' => true,
								'headers' => array(
									"Content-Type" => "application/x-www-form-urlencoded",
									"Authorization" => "Bearer " . $StripeAPIKey
								),
								'body' => $stripe_data,
								'cookies' => array()
								)
							);
							
							if (is_wp_error($response)) {
							   $error_message = $response->get_error_message();
							   
							   $data = array(
									'error' => true,
									'message' => $error_message
								);
								
								exit(json_encode($data));
								
							} else {
								$response = json_decode($response['body'],true);
								
								if($response['paid'] == true) {
									$charge_id = $response['id'];
									$source_id = $response['source']['id'];
									$entidade = $response['source']['multibanco']['reference'];
									$referencia = $response['source']['multibanco']['entity'];
									//if($response['source']['owner']['verified_name'] != null) $verified_name = '<br>' . __('Name:', 'woo-multibanco-gateway') . ' ' . $response['source']['owner']['verified_name'];
									//else $verified_name = '';
									
									//Set order on payment complete
									update_post_meta($order_id_meta, 'woo-multibanco-gateway-stripe-charge-id', $charge_id);
									$order->add_order_note(__('Multibanco Payment succeeded', 'woo-multibanco-gateway') . '<br>' . __('Entity ', 'woo-multibanco-gateway') . $entidade . ' (Referencia:' . $referencia . ')' . $verified_name . '<br>');									$order->payment_complete($source_id);
									
									// Reduce stock levels
									//wc_reduce_stock_levels($order_id_meta);
									
									exit(json_encode($order));
									
									$output[] = array(
										'source_id' => $source_id,
										'charge_id' => $charge_id,
										'message' => __("Order status has been changed to processing", 'woo-multibanco-gateway'),
										'error' => false
									);
									
									exit(json_encode($output));
								}
								else {
									$order->update_status('failed', __('Multibanco payment failed - Error #0003!', 'woo-multibanco-gateway')); // order note is optional, if you want to  add a note to order

									$output[] = array(
										'source_id' => $source_stripe,
										'message' => __('Source is not successfully charged', 'woo-multibanco-gateway'),
										'error' => true
									);
									
									exit(json_encode($output));
								}
							}
							
						}
						else {
							$order->update_status('failed', __('Multibanco payment failed - Error #0001!', 'woo-multibanco-gateway')); // order note is optional, if you want to  add a note to order

							$output[] = array(
									'error' => true,
									'message' => __('Stripe source and WooCommerce Order Source are not the same!', 'woo-multibanco-gateway')
								);
							exit(json_encode($output));
						}
						
					}
					else {
						$order->update_status('failed', __('Multibanco payment failed - Error: #0002', 'woo-multibanco-gateway')); // order note is optional, if you want to  add a note to order

						// Source is not chargeable
						$output[] = array(
							'error' => true,
							'message' => __('Source is not in a chargeable state', 'woo-multibanco-gateway')
						);
						exit(json_encode($output));
						
					}
		
				}
				$output[] = array(
					'error' => true,
					'message' => __('Source is not using the Multibanco payment method!', 'woo-multibanco-gateway')
				);
				exit(json_encode($output));
				
			}
			$output[] = array(
				'error' => true,
				'message' => __('Source is not using the Multibanco payment method!', 'woo-multibanco-gateway')
			);
			exit(json_encode($output));

		}
		
	}

}