<?php
/*
 * Plugin Name: WooCommerce Multibanco Gateway
 * Plugin URI: https://wordpress.org/plugins/woo-multibanco-gateway/
 * Description: Payment gateway for WooCommerce that allows MULTIBANCO via Stripe.
 * Author: Jose Vieira
 * Author URI: https://convertodigital.com
 * Version: 4.0
 * Text Domain: woo-multibanco-gateway
 *
 * Copyright: (c) 2017 Jose Vieira
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package		WC_MULTIBANCO_Gateway
 * @author		Jose Vieira
 * @category	E-Commerce
 * @copyright	Copyright (c) 2017 Jose Vieira
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */

defined('ABSPATH') or exit;


// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	return;
}

/**
 * Load plugin textdomain
 * This fixes the translation problems
 *
 * @since 2.3.1
 */
function woo_multibanco_gateway_load_plugin_textdomain() {
	load_plugin_textdomain('woo-multibanco-gateway', FALSE, basename(dirname(__FILE__)) . '/i18n/languages/');
}
add_action( 'plugins_loaded', 'woo_multibanco_gateway_load_plugin_textdomain' );


/**
 * Add the gateway to WC Available Gateways
 *
 * @since 1.0.0
 */
function wc_multibanco_add_to_gateways($gateways) {
	$gateways[] = 'WC_MULTIBANCO_Gateway';
	return $gateways;
}
add_filter('woocommerce_payment_gateways', 'wc_multibanco_add_to_gateways');

/**
 * Adds plugin page links
 *
 * @since 1.0.0
 */
function woo_multibanco_gateway_plugin_links($links) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=multibanco_gateway' ) . '">' . __( 'Settings', 'woo-multibanco-gateway' ) . '</a>',
		'<a href="mailto:jose@convertodigital.com">' . __( 'Support', 'woo-multibanco-gateway' ) . '</a>'
	);
	return array_merge( $plugin_links, $links );
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'woo_multibanco_gateway_plugin_links');


/**
 * Initializes all classes required
 *
 * @since 2.3
 */
function IncludeClasses() {
	include(__DIR__ . '/includes/woo-multibanco-gateway-class-fee.php');
	include(__DIR__ . '/includes/woo-multibanco-gateway-class-notification.php');
	include(__DIR__ . '/includes/woo-multibanco-gateway-class-stripe-webhook.php');
	
	$Notification = new Notification(true);
	$Notification->woo_multibanco_gateway_check_api();
	
	$Fee = new Fee;
	$Fee->woo_multibanco_gateway_fee_init();
	
	$StripeWebhook = new StripeWebhook;
	$StripeWebhook->ReceiveWebhook();
}

add_action('init', 'IncludeClasses');
add_action('plugins_loaded', 'woo_multibanco_gateway_init', 11);


function woo_multibanco_gateway_init() {

	class WC_MULTIBANCO_Gateway extends WC_Payment_Gateway {
		
		/**
		 * @since 0.5 
		 * Constructor for the gateway.
		 */
		public function __construct($add_actions = false) {
			$this->id				  = 'multibanco_gateway';
			$this->icon				  = apply_filters( 'woocommerce_gateway_icon', plugins_url('woo-multibanco-gateway\images\multibanco.png', dirname(__FILE__)) );
			$this->has_fields		  = false;
			$this->method_title		  = __('Multibanco', 'woo-multibanco-gateway');
			//$this->method_description = __('Adiciona Multibanco como um processador de pagamentos, a <a href="https://stripe.com/">Stripe API key</a> is required.', 'woo-multibanco-gateway' );
			$this->method_description = __('WooCommerce Multibanco Gateway will generate a Multibanco source at Stripe using their API, then receive an entity and a reference from Stripe. At this point the order is put on-hold, once the order is payed, Stripe will use a webhook to let WooCommerce know the payment succeeded. To use this payment gateway you are required to have a <a href="https://stripe.com/">Stripe API key</a>, you can read more about Stripe\'s Authentication <a href="https://stripe.com/docs/api#authentication">here</a>.', 'woo-multibanco-gateway' );
			
			$this->supports = array(
				'products',
				'refunds'
			);
			
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->description = $this->get_option('description');
                        $this->payment_description = $this->get_option('payment_description');
			$this->stripe_api_live = $this->get_option('woocommerce-stripe-api-live');
			$this->stripe_api_test = $this->get_option('woocommerce-stripe-api-test');
			$this->show_error_codes_to_customer = $this->get_option('show-error-codes-to-customer');
			$this->title = $this->get_option('title');
                        $this->details_in_mail = $this->get_option('stripe-cost-to-customer');

			
			// Stripe API URL
			$this->api_url = "https://api.stripe.com/v1/";
			
			// API Key variable used in other classes to get current API Key
			if($this->get_option('test-mode') == 'yes') {
				$this->api_key = $this->get_option('stripe-api-test');
				$this->api_key_type = __('test', 'woo-multibanco-gateway');
			}
			else {
				$this->api_key = $this->get_option('stripe-api-live');
				$this->api_key_type = __('live', 'woo-multibanco-gateway');
			}
			
			// Actions
			if ($add_actions == true) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
				//add_action('woocommerce_thankyou_' . $this->id, array($this, 'woo_multibanco_gateway_check_source'));

				//add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou'));
				add_action('woocommerce_thankyou_'.$this->id, array($this, 'thankyou'));
                                
                                if($this->details_in_mail == 'no'){ 

				add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 2);
                                }



			}
			//add_filter('woocommerce_email_classes', array( $this, 'manipulate_woocommerce_email_sending'));
			//add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 2);

		}
		
		function manipulate_woocommerce_email_sending($email_class) {
			//remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class['WC_Email_New_Order'], 'trigger' ) );
		}
		
		public function thankyou( $order_id ) {
			
		$order = new WC_Order($order_id);

      		wc_get_template( 'payment-instructions.php', array(
       		 'method' => $order->payment_method,'payment_name' => (function_exists('icl_object_id') ? icl_t($this->id, $this->id.'_title', $this->title) : $this->title),'entidade' => get_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-entity', true),'referencia' => get_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-reference', true),'order_total' => $order->order_total,), 'woocommerce/eupago/', plugin_dir_path( __FILE__ ) . 'templates/' );
	

		}

	public function email_instructions($order, $sent_to_admin, $plain_text = false) {
      		if ($sent_to_admin || $order->status !== 'on-hold' || $this->id !== $order->payment_method ) {
      	        return;
                }

      if ($plain_text) {
        wc_get_template( 'emails/plain-instructions.php', array(
          'method' => $order->payment_method,
          'payment_name' => (function_exists('icl_object_id') ? icl_t($this->id, $this->id.'_title', $this->title) : $this->title),
          'referencia' => get_post_meta($order->id, 'woo-multibanco-gateway-stripe-multibanco-reference', true),
          'entidade' => get_post_meta($order->id, 'woo-multibanco-gateway-stripe-multibanco-entity', true),
          'order_total' => $order->order_total,
        ), 'woocommerce/eupago/', plugin_dir_path( __FILE__ ) . 'templates/' );
      } else {
        wc_get_template( 'emails/html-instructions.php', array(
          'method' => $order->payment_method,
          'payment_name' => (function_exists('icl_object_id') ? icl_t($this->id, $this->id.'_title', $this->title) : $this->title),
          'referencia' => get_post_meta($order->id, 'woo-multibanco-gateway-stripe-multibanco-reference', true),
 	  'entidade' => get_post_meta($order->id, 'woo-multibanco-gateway-stripe-multibanco-entity', true),

          'order_total' => $order->order_total,
        ), 'woocommerce/eupago/', plugin_dir_path( __FILE__ ) . 'templates/' );
      }
    }


		
		/**
		* Creates a random key used for securing the webhook
		*
		* @since 2.0
		*/
		
		private function random_webhook_key($lengte, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
			$str = '';
			$max = mb_strlen($keyspace, '8bit') - 1;
			for ($i = 0; $i < $lengte; ++$i) {
				$str .= $keyspace[random_int(0, $max)];
			}
			return $str;
			// Thanks to https://github.com/rinkp
		}

		/**
		* Initialize payment gateway form fields
		*
		* @since 2.0
		*/
		public function init_form_fields() {
			$webhook_key = $this->webhook_key();
			$this->form_fields = apply_filters( 'woo__multibanco_gateway_form_fields', array(

				'enabled' => array(
					'title'   => __('Enable/Disable', 'woo-multibanco-gateway'),
					'type'    => 'checkbox',
					'label'   => __('Enable Stripe Multibanco payments', 'woo-multibanco-gateway'),
					'default' => 'yes'
				),

				'title' => array(
					'title'       => __('Title', 'woo-multibanco-gateway'),
					'type'        => 'text',
					'description' => __('Title of this payment gateway a customer sees when checking out', 'woo-multibanco-gateway' ),
					'default'     => __('Multibanco Stripe', 'woo-multibanco-gateway'),
					'desc_tip'    => false,
				),

                                 'description' => array(
		                 'title'       => __( 'Description in the checkout page', 'woo-multibanco-gateway' ),
	   			 'type'        => 'text',
				 'desc_tip'    => true,
				 'description' => __( 'This controls the description which the user sees during checkout.', 'woo-multibanco-gateway' ),
				 'default'     => __( 'Receive a Reference to pay in the ATM or Home Banking. You have 72 hours to complete the payment.', 'woo-multibanco-gateway' ),
				),

				
				'test-mode' => array(
					'title'   => __('Enable/Disable Test Mode', 'woo-multibanco-gateway'),
					'type'    => 'checkbox',
					'label'   => __('Enable test mode to see if you have correctly setup Stripe Multibanco payements', 'woo-multibanco-gateway'),
					'default' => 'yes'
				),

				'stripe-api-live' => array(
					'title'       => __('Stripe Secret API Key (Live)', 'woo-multibanco-gateway'),
					'type'        => 'text',
					'description' => __('Secret API Key from Stripe used for live payments', 'woo-multibanco-gateway' ),
					'default'     => __('sk_live_XXXXXXXXXXXXXXXXXXXXXXXX', 'woo-multibanco-gateway'),
					'desc_tip'    => true,
				),
				
				'stripe-api-test' => array(
					'title'       => __('Stripe Secret API Key (Test)', 'woo-multibanco-gateway'),
					'type'        => 'text',
					'description' => __('Secret API Key from Stripe used for test payments', 'woo-multibanco-gateway' ),
					'default'     => __('sk_test_XXXXXXXXXXXXXXXXXXXXXXXX', 'woo-multibanco-gateway'),
					'desc_tip'    => true,
				),
				
				'stripe-webhook-key' => array(
					'title'       => __('Stripe Webhook Key', 'woo-multibanco-gateway'),
					'type'        => 'text',
					'description' => __('Your webhook URL:', 'woo-multibanco-gateway') . ' <strong><a>' . esc_url(home_url('/?stripe_webhook=yes&key=')) . $webhook_key . '</a></strong>' . 
										'<br>' . __('Read <a href="https://nl.wordpress.org/plugins/woo-multibanco-gateway/#installation">here</a> how to setup this webhook', 'woo-multibanco-gateway'),
					'default'     => $this->random_webhook_key(30),
					'desc_tip'    => false,
				),

				'stripe_description' => array(
					'title'       => __('Payment reference', 'woo-multibanco-gateway'),
					'type'        => 'text',
					'payment_description' => __('Payment reference a customer sees in their Multibanco-enviroment', 'woo-multibanco-gateway' ),
					'default'     => __('Payment for #{order_number}', 'woo-multibanco-gateway'),
					'desc_tip'    => false,
				),
				
				'stripe-cost-to-customer' => array(
					'title'   => __('Details in Order Mail', 'woo-multibanco-gateway'),
					'type'    => 'checkbox',
					'label'   => __('Do not send Multibanco details in order mail', 'woo-multibanco-gateway'),
					'default' => 'no'
				),

				'show-error-codes-to-customer' => array(
					'title'   => __('Visible error codes', 'woo-multibanco-gateway'),
					'type'    => 'checkbox',
					'label'   => __('If there is an error while checking out, show the error code to the user. This might be useful for you to solve an issue a customer has.', 'woo-multibanco-gateway'),
					'default' => 'yes'
				),

			) );
		}
		
		/**
		* Includes the StripeAPI Class
		*
		* @since 2.0
		*/
		public function InitStripeAPI() {
			require_once(__DIR__ . '/includes/woo-multibanco-gateway-class-stripe.php');
		}
		
		/**
		* Checks if a Stripe Webhook Key has been filled in or else returns "PLEASE_CREATE_A_KEY"
		*
		* @since 2.0
		*/
		public function webhook_key() {
			if($this->get_option('stripe-webhook-key') != "") {
				$webhook_key_saved = $this->get_option('stripe-webhook-key');
				return $webhook_key_saved;
			}
			else {
				$webhook_key = "PLEASE_CREATE_A_KEY";
				return $webhook_key;
			}
		}
		
		/**
		* When redirect to the webshop check source if payment is failed, if so update order status.
		*
		* @since 2.4
		*/
		public function woo_multibanco_gateway_check_source($order_id) {

			$this->InitStripeAPI();
			$StripeAPI = new StripeAPI;
			
			if($this->show_error_codes_to_customer == 'yes') $show_error = true;
			else $show_error = false;
			
			global $woocommerce;
			$order = new WC_Order($order_id);
			
			$order_data = $order->get_data();
			$order_status = $order_data['status'];
			
			if($order_status != "on-hold") return false;
			
			$source = get_post_meta($order_id, 'woo-multibanco-gateway-stripe-source', true);
			$status = $StripeAPI->GetSourceStatus($source);
			
			if($status !== false) {
				if($status == 'failed') {
					$order->update_status('failed', __('Multibanco Payment canceled by customer', 'woo-multibanco-gateway'));
					header("Location: " . $_SERVER['REQUEST_URI']);
					return;
				}
			}
			
			
		}
		
		
		 
		public function process_payment($order_id) {
			$this->InitStripeAPI();
			$StripeAPI = new StripeAPI;
			
			global $woocommerce;
			$order = new WC_Order($order_id);
			$order_data = $order->get_data();
			$payment_gateway = $order_data['payment_method'];
                        $order_total1 = $order_data['total'];

			
			if($order_data['payment_method'] == "multibanco_gateway") {
				
				
				
				
				/**
				* @since 2.3
				* If enabled, user will see the error code on the checkout page,
				* this might be useful if you want to fix this issue.
				*/
				
				if($this->show_error_codes_to_customer == 'yes') $show_error = true;
				else $show_error = false;
				
				$stripe_response = $StripeAPI->CreateSource($order_data, $order_id);
				
				if($stripe_response['success'] == 'no') {
					$order->add_order_note(__('Stripe error', 'woo-multibanco-gateway') . ': ' . $stripe_response['error_message'] . ' (' . $stripe_response['error_type'] . ')'); //FAILURE NOTE
					if($show_error) wc_add_notice(__('Multibanco payment failed, please try again', 'woo-multibanco-gateway') . ' (' . __('Error', 'woo-multibanco-gateway') . ': ' . $stripe_response['error_type'] . ')', 'error');
					else wc_add_notice(__('Multibanco payment failed, please try again', 'woo-multibanco-gateway'), 'error');
					return;
				}
				elseif ($stripe_response['success'] == 'yes') {
					$stripe_source_id = $stripe_response['source_id'];
					$stripe_url = $stripe_response['redirect_url'];
                                        $stripe_entity = $stripe_response['stripe_entity'];

                                        $stripe_reference = $stripe_response['stripe_reference'];
                                        $stripe_reference = chunk_split($stripe_reference, 3, ' ');

					update_post_meta($order_id, 'woo-multibanco-gateway-stripe-source', $stripe_source_id);
                                        update_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-entity', $stripe_entity);
                                        update_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-reference', $stripe_reference);
					update_post_meta($order_id, 'woo-multibanco-gateway-stripe-multibanco-valor', $order_total1);



					
					$order->update_status('on-hold', __('New Multibanco payment initiated by customer', 'woo-multibanco-gateway'));

                                        
					
					// Empty cart
					WC()->cart->empty_cart();
					
					return array(
						'result' => 'success',
						'redirect' => $stripe_url,
                                                'entidade' => $stripe_entity,
                                                'referencia' => $stripe_reference



					);
					
				}
			
			}
			
		}
		
		/**
		* Refunds the payment via Stripe
		*
		* @since 2.0
		*/
		public function process_refund($order_id, $amount = null, $reason = null) {
			$this->InitStripeAPI();
			$StripeAPI = new StripeAPI;
			$order = new WC_Order($order_id);
			if($order->has_status('on-hold') || empty(get_post_meta($order_id, 'woo-multibanco-gateway-stripe-source', true)) || empty(get_post_meta($order_id, 'woo-multibanco-gateway-stripe-charge-id', true))) {
				// No refund possible through Multibanco
				return new WP_Error('multibanco_refund_not_possible', __('A refund through MULTIBANCO is not possible because the order has not been paid yet. Or the order was initially not using this payment gateway.', 'woo-multibanco-gateway'));
			}
			else {
				$stripe_response = $StripeAPI->RefundPayment($order_id, $amount, $reason);
				if($stripe_response['success'] == 'yes') {
					// Refund succeeded
					return true;
				}
				else {
					// Refund failed, user gets notified.
					$error_type = $stripe_response['error_type'];
					$error_message = $stripe_response['error_message'];
					
					return new WP_Error($error_type, __('Stripe error', 'woo-multibanco-gateway') . ': ' . $error_message);
				}
			}
			
		}
		
	}
  
}

//if (in_array('automatewoo/automatewoo.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	

//namespace AutomateWoo;

/**
 * Add the custom variable to the list Automatewoo
 */
//add_filter( 'automatewoo/variables', 'my_automatewoo_variables' );
/**
 * @param $variables array
 * @return array
 */
//function my_automatewoo_variables( $variables ) {
	// variable's string form is set here, it will be order.pluralize
	//$variables['order']['ifthen_mb'] = dirname(__FILE__) . '/includes/variable-order-mbhtml.php';
        //$variables['order']['ifthen_mbsms'] = dirname(__FILE__) . '/includes/variable-order-sms.php';
        //$variables['order']['mbsmsref'] = dirname(__FILE__) . '/includes/variable-order-mbsmsref.php';
	//$variables['order']['mbsmsvalor'] = dirname(__FILE__) . '/includes/variable-order-mbsmsvalor';



//	return $variables;
//}
//}