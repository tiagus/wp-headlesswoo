<?php

defined( 'ABSPATH' ) || exit;

/**
 * Aero checkout Common Class
 *
 */
abstract class WFACP_Common extends WFACP_Common_Helper {

	public static $customizer_key_prefix = '';
	public static $customizer_key_data = [];
	public static $post_data = [];
	public static $customizer_fields_default = array();
	private static $wfacp_id = 0;
	private static $wfacp_section = '';
	private static $product_switcher_setting = [];
	private static $product_data = [];

	public static function init() {
		/**
		 * Loading WooFunnels core
		 */
		if ( apply_filters( 'wfacp_skip_common_loading', false ) ) {
			return;
		}
		add_action( 'plugins_loaded', [ __CLASS__, 'plugins_loaded' ], - 1 );
		add_action( 'init', [ __CLASS__, 'register_post_type' ], 100 );

		add_action( 'woocommerce_checkout_update_order_meta', [ __CLASS__, 'update_checkout_custom_field' ], 10, 2 );
		add_action( 'wc_ajax_get_refreshed_fragments', [ __CLASS__, 'wc_ajax_get_refreshed_fragments' ], - 1 );
		add_action( 'woocommerce_checkout_update_order_review', [ __CLASS__, 'woocommerce_checkout_update_order_review' ], - 1 );
		add_action( 'woocommerce_before_checkout_process', [ __CLASS__, 'woocommerce_before_checkout_process' ] );
		add_action( 'woocommerce_after_register_post_type', array( __CLASS__, 'maybe_flush_rewrite_rules' ) );
		add_filter( 'woocommerce_form_field_hidden', [ __CLASS__, 'woocommerce_form_field_hidden' ], 10, 4 );
		add_filter( 'woocommerce_form_field_wfacp_radio', [ __CLASS__, 'woocommerce_form_field_wfacp_radio' ], 10, 4 );
		add_filter( 'woocommerce_form_field_wfacp_start_divider', [ __CLASS__, 'woocommerce_form_field_wfacp_start_divider' ], 10, 4 );
		add_filter( 'woocommerce_form_field_wfacp_end_divider', [ __CLASS__, 'woocommerce_form_field_wfacp_end_start_divider' ], 10, 4 );
		add_filter( 'woocommerce_form_field_product', [ __CLASS__, 'woocommerce_form_field_wfacp_product' ], 10, 4 );
		add_action( 'woocommerce_form_field_wfacp_html', [ __CLASS__, 'process_wfacp_html' ], 10, 4 );
		add_filter( 'wcct_get_restricted_action', [ __CLASS__, 'wcct_get_restricted_action' ] );
		add_shortcode( 'wfacp_order_custom_field', [ __CLASS__, 'wfacp_order_custom_field' ] );
		add_action( 'wfacp_get_fragments', [ __CLASS__, 'initializeTemplate' ] );
		add_action( 'wfob_before_remove_bump_from_cart', [ __CLASS__, 'wfob_order_bump_fragments' ] );
		add_action( 'wfob_before_add_to_cart', [ __CLASS__, 'wfob_order_bump_fragments' ] );
		add_filter( 'wfacp_product_switcher_product', [ __CLASS__, 'wfacp_product_switcher_product' ], 10, 2 );
		add_filter( 'wfacp_get_product_switcher_data', [ __CLASS__, 'wfacp_get_product_switcher_data' ] );
		add_action( 'woofunnels_loaded', [ __CLASS__, 'include_notification_class' ] );

		add_action( 'woocommerce_form_field_wfacp_wysiwyg', [ __CLASS__, 'process_wfacp_wysiwyg' ], 10, 4 );

		add_action( 'woocommerce_locate_template', [ __CLASS__, 'woocommerce_locate_template' ] );

		add_action( 'wfacp_get_product_switcher_data', [ __CLASS__, 'merge_page_product_settings' ] );
		add_filter( 'wfacp_billing_field', [ __CLASS__, 'check_wc_validations_billing' ], 10, 2 );
		add_filter( 'wfacp_shipping_field', [ __CLASS__, 'check_wc_validations_shipping' ], 10, 2 );


		$default_printing_hook_email = apply_filters( 'wfacp_default_custom_field_print_hook_for_email', 'woocommerce_email_order_meta' );
		if ( '' !== $default_printing_hook_email ) {
			add_action( $default_printing_hook_email, [ __CLASS__, 'print_custom_field_at_email' ], 999 );
		}

		add_filter( 'woocommerce_add_cart_item_data', [ __CLASS__, 're_apply_aero_checkout_settings' ] );

		add_action( 'wp_head', function () {
			$default_printing_hook_thankyou = apply_filters( 'wfacp_default_custom_field_print_hook_for_thankyou', 'woocommerce_order_details_after_customer_details' );
			if ( '' !== $default_printing_hook_thankyou ) {
				add_action( $default_printing_hook_thankyou, [ __CLASS__, 'print_custom_field_at_thankyou' ], 999 );
			}
		} );

		//try to resolve cache
		add_filter( 'woocommerce_shipping_chosen_method', [ __CLASS__, 'assign_minimum_value_sipping_method' ], 99, 3 );

	}

	public static function plugins_loaded() {

		/**
		 * @since 1.6.0
		 * Detect heartbeat call from our customizer page
		 * Remove some unwanted warnings and error
		 */
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'heartbeat' && isset( $_REQUEST['data'] ) ) {
			$data = $_REQUEST['data'];
			if ( isset( $data['wfacp_customize'] ) ) {
				add_filter( 'customize_loaded_components', array( __CLASS__, 'remove_menu_support' ), 99 );
			}
		}

		if ( isset( $_REQUEST['wfacp_id'] ) && $_REQUEST['wfacp_id'] > 0 ) {
			self::set_id( absint( $_REQUEST['wfacp_id'] ) );
		}

		if ( ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'wfacp' ) ) {
			self::$wfacp_section = ! isset( $_REQUEST['section'] ) ? 'product' : $_REQUEST['section'];
		}
		WooFunnel_Loader::include_core();
	}

	/**
	 * Get current Page id
	 * @return int
	 */
	public static function set_id( $wfacp_id = 0 ) {
		if ( is_numeric( $wfacp_id ) ) {
			self::$wfacp_id              = absint( $wfacp_id );
			self::$customizer_key_prefix = WFACP_SLUG . '_c_' . self::get_id();
		}
	}

	/** Get current Page id
	 * @return int
	 */
	public static function get_id() {
		if ( self::is_disabled() ) {
			return 0;
		}

		if ( self::$wfacp_id == 0 && ! is_admin() && ! self::is_disabled() && function_exists( 'WC' ) && ! is_null( WC()->session ) ) {
			$wfacp_id = WC()->session->get( 'wfacp_id', 0 );
			if ( $wfacp_id > 0 ) {
				self::$wfacp_id = absint( $wfacp_id );
			}
		}

		return self::$wfacp_id;
	}

	public static function is_disabled() {
		if ( isset( $_REQUEST['wfacp_disabled'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Setup checkout page when get_refreshed_fragments ajax called
	 */
	public static function wc_ajax_get_refreshed_fragments() {
		if ( isset( $_REQUEST['wfacp_id'] ) && 0 < absint( $_REQUEST['wfacp_id'] ) ) {
			$wfacp_id = absint( $_REQUEST['wfacp_id'] );
			self::initTemplateLoader( $wfacp_id );
		}
	}

	/**
	 * Initialize template when woocommerce ajax running is running
	 *
	 * @param $wfacp_id
	 */
	private static function initTemplateLoader( $wfacp_id ) {
		self::set_id( $wfacp_id );
		$get_customizer_instance = WFACP_Core()->customizer;
		$instances               = $get_customizer_instance->load_template( $wfacp_id );
		if ( ! is_null( $instances ) ) {
			do_action( 'wfacp_before_process_checkout_template_loader', $wfacp_id, $instances );
			WFACP_Core()->template_loader->current_template = $instances;
			WFACP_Core()->template_loader->current_template->get_customizer_data();


			WFACP_Common::pc( '(initTemplateLoader) May be setup page Layout class is found -> ' . WFACP_Core()->template_loader->current_template->get_slug() );
			self::disable_wcct_pricing();
		} else {
			WFACP_Common::pc( '(initTemplateLoader) May be setup page Layout class is not found ' );
		}

	}

	public static function pc( $data ) {
		if ( class_exists( 'PC' ) && method_exists( 'PC', 'debug' ) && ( true == apply_filters( 'wfacp_show_debug_logs', false ) ) ) {
			PC::debug( $data );
		}
	}


	/**
	 * Setup checkout page when update_order_review ajax called
	 */
	public static function woocommerce_checkout_update_order_review( $postdata ) {

		$post_data = [];
		parse_str( $postdata, $post_data );
		if ( isset( $post_data['_wfacp_post_id'] ) ) {

			self::$post_data = $post_data;
			self::handling_post_data( $post_data );
			$wfacp_id = absint( $post_data['_wfacp_post_id'] );
			self::initTemplateLoader( $wfacp_id );

		}
	}

	/**
	 * Setup checkout page when before_checkout_process hooks executed
	 */
	public static function woocommerce_before_checkout_process() {
		if ( isset( $_REQUEST['_wfacp_post_id'] ) ) {
			$wfacp_id = absint( $_REQUEST['_wfacp_post_id'] );
			self::initTemplateLoader( $wfacp_id );
		}
	}


	public static function set_data() {

		self::$customizer_key_prefix = WFACP_SLUG . '_c_' . WFACP_Common::get_id();
		/** wfacpkirki */
		if ( class_exists( 'wfacpkirki' ) ) {
			wfacpkirki::add_config( WFACP_SLUG, array(
				'option_type' => 'option',
				'option_name' => WFACP_Common::$customizer_key_prefix,
			) );
		}
	}

	/**
	 * GEt Current open step
	 * @return string
	 */
	public static function get_current_step() {
		return self::$wfacp_section;
	}

	/**
	 * Get title of checkout page
	 * @return string
	 */

	public static function get_page_name() {
		return get_the_title( self::$wfacp_id );
	}

	public static function register_post_type() {
		/**
		 * Funnel Post Type
		 */
		register_post_type( self::get_post_type_slug(), apply_filters( 'wfacp_post_type_args', array(
			'labels'              => array(
				'name'          => __( 'Checkout', 'woofunnels-aero-checkout' ),
				'singular_name' => __( 'Checkout', 'woofunnels-aero-checkout' ),
				'add_new'       => __( 'Add Checkout page', 'woofunnels-aero-checkout' ),
				'add_new_item'  => __( 'Add New Checkout page', 'woofunnels-aero-checkout' ),
			),
			'public'              => true,
			'show_ui'             => false,
			'capability_type'     => 'product',
			'map_meta_cap'        => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'rewrite'             => apply_filters( 'wfacp_rewrite_slug', [ 'slug' => self::get_url_rewrite_slug() ] ),
			'query_var'           => true,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
		) ) );
	}

	/**
	 * Get Post_type slug
	 * @return string
	 */
	public static function get_post_type_slug() {
		return 'wfacp_checkout';
	}

	public static function get_url_rewrite_slug() {
		$g_setting = get_option( '_wfacp_global_settings', [] );

		return isset( $g_setting['rewrite_slug'] ) ? $g_setting['rewrite_slug'] : 'checkouts';
	}

	public static function maybe_flush_rewrite_rules() {
		$g_setting = get_option( '_wfacp_global_settings', [] );
		if ( isset( $g_setting['update_rewrite_slug'] ) && 'yes' == $g_setting['update_rewrite_slug'] ) {
			flush_rewrite_rules();
			unset( $g_setting['update_rewrite_slug'] );
			update_option( '_wfacp_global_settings', $g_setting, true );

		}
	}


	public static function get_formatted_product_name( $product ) {
		$formatted_variation_list = self::get_variation_attribute( $product );

		$arguments = array();
		if ( ! empty( $formatted_variation_list ) && count( $formatted_variation_list ) > 0 ) {
			foreach ( $formatted_variation_list as $att => $att_val ) {
				if ( $att_val == '' ) {
					$att_val = __( 'any' );
				}
				$att         = strtolower( $att );
				$att_val     = strtolower( $att_val );
				$arguments[] = "$att: $att_val";
			}
		}

		return sprintf( '%s (#%d) %s', $product->get_title(), $product->get_id(), ( count( $arguments ) > 0 ) ? '(' . implode( ',', $arguments ) . ')' : '' );
	}

	public static function get_variation_attribute( $variation ) {
		if ( is_a( $variation, 'WC_Product_Variation' ) ) {
			$variation_attributes = $variation_attributes_basic = $variation->get_attributes();
		} else {

			$variation_attributes = array();
			if ( is_array( $variation ) ) {
				foreach ( $variation as $key => $value ) {
					$variation_attributes[ str_replace( 'attribute_', '', $key ) ] = $value;
				}
			}
		}

		return ( $variation_attributes );
	}

	public static function search_products( $term, $include_variations = false ) {
		global $wpdb;
		$like_term     = '%' . $wpdb->esc_like( $term ) . '%';
		$post_types    = array( 'product', 'product_variation' );
		$post_statuses = current_user_can( 'edit_private_products' ) ? array( 'private', 'publish' ) : array( 'publish' );
		$type_join     = '';
		$type_where    = '';

		$product_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT posts.ID FROM {$wpdb->posts} posts
				LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
				$type_join
				WHERE (
					posts.post_title LIKE %s
					OR (
						postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
					)
				)
				AND posts.post_type IN ('" . implode( "','", $post_types ) . "')
				AND posts.post_status IN ('" . implode( "','", $post_statuses ) . "')
				$type_where
				ORDER BY posts.post_parent ASC, posts.post_title ASC", $like_term, $like_term ) );

		if ( is_numeric( $term ) ) {
			$post_id       = absint( $term );
			$product_ids[] = $post_id;
		}

		return wp_parse_id_list( $product_ids );
	}

	public static function array_flatten( $array ) {
		if ( ! is_array( $array ) ) {
			return false;
		}
		$result = iterator_to_array( new RecursiveIteratorIterator( new RecursiveArrayIterator( $array ) ), false );

		return $result;
	}

	public static function get_default_product_config() {
		return [
			'title'           => '',
			'discount_type'   => 'percent_discount_sale',
			'discount_amount' => 0,
			'discount_price'  => 0,
			'quantity'        => 1,
		];

	}

	public static function get_admin_menu() {
		$sections = [
			[
				'slug' => 'product',
				'name' => __( 'Products', 'woofunnels-aero-checkout' ),
				'icon' => WFACP_PLUGIN_URL . '/assets/img/product.svg',
			],
			[
				'slug' => 'fields',
				'name' => __( 'Form', 'woofunnels-aero-checkout' ),
				'icon' => WFACP_PLUGIN_URL . '/assets/img/fields.svg',
			],
			[
				'slug' => 'design',
				'name' => __( 'Design', 'woofunnels-aero-checkout' ),
				'icon' => WFACP_PLUGIN_URL . '/assets/img/design.svg',
			],
			[
				'slug' => 'settings',
				'name' => __( 'Settings', 'woofunnels-aero-checkout' ),
				'icon' => WFACP_PLUGIN_URL . '/assets/img/settings.svg',
			],
		];

		$pages = apply_filters( 'wfacp_builder_section_pages', $sections );
		if ( empty( $pages ) ) {
			$pages = $sections;
		}

		return $pages;
	}

	public static function get_discount_type_keys() {

		$discounted = [
			'fixed_discount_reg'    => __( 'Fixed Amount $ on Regular Price', 'woofunnels-aero-checkout' ),
			'fixed_discount_sale'   => __( 'Fixed Amolunt $ on Sale Price', 'woofunnels-aero-checkout' ),
			'percent_discount_reg'  => __( 'Percentage % on Regular Price', 'woofunnels-aero-checkout' ),
			'percent_discount_sale' => __( 'Percentage % on Sale Price', 'woofunnels-aero-checkout' ),
		];

		return $discounted;

	}

	/**
	 * Get all product of checkout page
	 *
	 * @param $wfacp_id
	 *
	 * @return array|mixed
	 */

	public static function get_page_product( $wfacp_id ) {

		$wfacp_id = absint( $wfacp_id );
		$product  = self::get_post_meta_data( $wfacp_id, '_wfacp_selected_products' );

		if ( ! is_array( $product ) ) {
			return [];
		}

		return apply_filters( 'wfacp_save_products', $product );

	}


	/**
	 * Get Default products of checkout page
	 *
	 * @param $wfacp_id
	 *
	 * @return array|mixed
	 */


	public static function update_product_switcher_setting( $wfacp_id, $data ) {
		$new_data                                = [
			'products'         => $data['products'],
			'default_products' => isset( $data['default_products'] ) ? $data['default_products'] : '',
			'settings'         => $data['product_settings'],
		];
		$new_data['settings']['setting_migrate'] = WFACP_VERSION;
		update_post_meta( $wfacp_id, '_wfacp_product_switcher_setting', $new_data );
	}

	private static function get_product_switcher_setting( $wfacp_id ) {
		$switcher_setting = self::get_post_meta_data( $wfacp_id, '_wfacp_product_switcher_setting' );
		$settings         = self::get_page_settings( $wfacp_id );
		if ( ! is_array( $switcher_setting ) || empty( $switcher_setting ) ) {
			$switcher_setting = [];
			$products         = self::get_page_product( $wfacp_id );
			foreach ( $products as $key => $product ) {
				$products[ $key ] = self::handle_product_data_array( $product, $key );
			}
			$switcher_setting['products']         = $products;
			$switcher_setting['default_products'] = '';
			if ( ! isset( $settings['is_hide_additional_information'] ) ) {
				$settings['is_hide_additional_information'] = false;
			}
			if ( ! isset( $settings['additional_information_title'] ) ) {
				$settings['additional_information_title'] = self::get_default_additional_information_title();
			}
			$switcher_setting['settings'] = $settings;
		}
		if ( isset( $settings['coupons'] ) ) {
			$switcher_setting['settings']['coupons'] = $settings['coupons'];
		}
		if ( isset( $settings['enable_coupon'] ) ) {
			$switcher_setting['settings']['enable_coupon'] = $settings['enable_coupon'];
		}
		if ( isset( $settings['disable_coupon'] ) ) {
			$switcher_setting['settings']['disable_coupon'] = $settings['disable_coupon'];
		}

		return $switcher_setting;
	}

	/**
	 * Remove extra keys and add you save key for only product switcher field
	 *
	 * @param $product_data []
	 * @param $key String
	 * @param $product WC_Product
	 */
	private static function handle_product_data_array( $product_data, $key ) {
		$title         = $product_data['title'];
		$you_save_text = '';
		if ( ! isset( $product_data['you_save_text'] ) ) {

			$version = self::get_checkout_page_version();
			if ( version_compare( $version, WFACP_VERSION, '<=' ) ) {
				// check for older version
				$fields = self::get_checkout_fields( self::get_id() );
				if ( isset( $fields['product'] ) && isset( $fields['product']['product_switching'] ) ) {
					$you_save_text = $fields['product']['product_switching']['default'];
				}
			} else {
				$you_save_text = self::get_default_you_save_text();
			}
		} else {
			$you_save_text = $product_data['you_save_text'];
		}

		$product_data = [
			'title'          => $title,
			'you_save_text'  => $you_save_text,
			'whats_included' => '',
			'enable_delete'  => false,
		];

		return $product_data;
	}

	public static function get_product_switcher_data( $wfacp_id ) {
		if ( absint( $wfacp_id ) === 0 ) {
			return self::$product_switcher_setting;
		}
		if ( ! empty( self::$product_switcher_setting ) ) {
			return self::$product_switcher_setting;
		}

		$final_products            = [];
		$settings                  = self::get_page_product_settings( $wfacp_id );
		$products                  = self::get_page_product( $wfacp_id );
		$switcher_product_settings = self::get_product_switcher_setting( $wfacp_id );
		$switcher_product          = $switcher_product_settings['products'];
		$let_first_key             = '';


		if ( count( $products ) > 0 ) {

			foreach ( $products as $product_key => $product ) {
				if ( '' == $let_first_key ) {
					$let_first_key = $product_key;
				}
				$product_data = [];
				if ( isset( $switcher_product[ $product_key ] ) ) {
					$product_data = $switcher_product[ $product_key ];
				}
				$product_data_array = self::handle_product_data_array( $product, $product_key );
				foreach ( $product_data_array as $def_key => $def_val ) {
					if ( ! isset( $product_data[ $def_key ] ) ) {
						$product_data[ $def_key ] = $product_data_array[ $def_key ];
					}
				}
				$product_data['old_title']             = $product['title'];
				$product_data['product_id']            = $product['id'];
				$product_data['whats_include_heading'] = $product['title'];
				$final_products[ $product_key ]        = apply_filters( 'wfacp_product_switcher_product', $product_data, $product_key, $switcher_product_settings );
			}
		}

		$switcher_product_settings['products'] = $final_products;
		$default_products                      = isset( $switcher_product_settings['default_products'] ) ? $switcher_product_settings['default_products'] : '';
		if ( $settings['add_to_cart_setting'] == '2' ) {
			if ( is_array( $default_products ) || '' == $default_products ) {
				unset( $default_products );
				$default_products = $let_first_key;
			}
		} elseif ( $settings['add_to_cart_setting'] === '3' ) {
			if ( is_string( $default_products ) || empty( $default_products ) ) {
				$default_products = [ $let_first_key ];
			}
		}

		$switcher_product_settings['product_settings'] = $settings;
		$switcher_product_settings['default_products'] = apply_filters( 'wfacp_default_product', $default_products, $products, $settings );


		$switcher_product_settings = apply_filters( 'wfacp_get_product_switcher_data', $switcher_product_settings );

		$no_need_settings = [
			'close_after_x_purchase',
			'total_purchased_allowed',
			'close_checkout_after_date',
			'close_checkout_on',
			'close_checkout_redirect_url',
			'total_purchased_redirect_url',
			'setting_migrate',
		];
		foreach ( $no_need_settings as $setting_key ) {
			unset( $switcher_product_settings['settings'][ $setting_key ] );

		}

		self::$product_switcher_setting = $switcher_product_settings;

		return $switcher_product_settings;
	}

	public static function get_post_meta_data( $item_id, $meta_key = '', $force = false ) {

		$wfacp_cache_obj     = WooFunnels_Cache::get_instance();
		$wfacp_transient_obj = WooFunnels_Transient::get_instance();

		$cache_key = 'wfacp_post_meta' . $item_id;

		/** When force enabled */

		if ( true === $force ) {
			$post_meta = get_post_meta( $item_id );
			$post_meta = self::parsed_query_results( $post_meta );
			$wfacp_transient_obj->set_transient( $cache_key, $post_meta, DAY_IN_SECONDS, WFACP_SLUG );
			$wfacp_cache_obj->set_cache( $cache_key, $post_meta, WFACP_SLUG );
		} else {
			/**
			 * Setting xl cache and transient for Free gift meta
			 */
			$cache_data = $wfacp_cache_obj->get_cache( $cache_key, WFACP_SLUG );
			if ( false !== $cache_data ) {
				$post_meta = $cache_data;
			} else {
				$transient_data = $wfacp_transient_obj->get_transient( $cache_key, WFACP_SLUG );
				if ( false !== $transient_data ) {
					$post_meta = $transient_data;
				} else {
					$post_meta = get_post_meta( $item_id );
					$post_meta = self::parsed_query_results( $post_meta );
					$wfacp_transient_obj->set_transient( $cache_key, $post_meta, DAY_IN_SECONDS, WFACP_SLUG );
				}
				$wfacp_cache_obj->set_cache( $cache_key, $post_meta, WFACP_SLUG );
			}
		}

		$fields = array();
		if ( $post_meta && is_array( $post_meta ) && count( $post_meta ) > 0 ) {
			foreach ( $post_meta as $key => $val ) {
				$newKey            = $key;
				$fields[ $newKey ] = $val;
			}
		}

		if ( '' != $meta_key ) {

			return isset( $fields[ $meta_key ] ) ? $fields[ $meta_key ] : '';
		}

		return $fields;
	}

	public static function parsed_query_results( $results ) {
		$parsed_results = array();
		if ( is_array( $results ) && count( $results ) > 0 ) {
			foreach ( $results as $key => $result ) {
				$parsed_results[ $key ] = maybe_unserialize( $result['0'] );
			}
		}

		return $parsed_results;
	}

	/**
	 * Get all product of checkout page Setting
	 *
	 * @param $wfacp_id
	 *
	 * @return array|mixed
	 */

	public static function get_page_product_settings( $wfacp_id ) {
		$wfacp_id = absint( $wfacp_id );
		$settings = self::get_post_meta_data( $wfacp_id, '_wfacp_selected_products_settings' );

		if ( ! is_array( $settings ) ) {
			return [
				'add_to_cart_setting' => '2',
			];
		}

		$settings = apply_filters( 'wfacp_page_product_settings', $settings );

		return $settings;

	}

	/**
	 * save product against checkout page id
	 *
	 * @param $wfacp_id
	 * @param $product
	 */
	public static function update_page_product( $wfacp_id, $product ) {
		if ( $wfacp_id == 0 ) {
			return;
		}

		if ( empty( $product ) ) {
			$product = [];
		}
		update_post_meta( $wfacp_id, '_wfacp_selected_products', $product );
	}

	/**Update product settings
	 *
	 * @param $wfacp_id
	 * @param $settings
	 */
	public static function update_page_product_setting( $wfacp_id, $settings ) {
		if ( $wfacp_id == 0 ) {
			return;
		}
		if ( empty( $settings ) ) {
			$settings = [];
		}
		update_post_meta( $wfacp_id, '_wfacp_selected_products_settings', $settings );
	}

	public static function update_page_design( $page_id, $data ) {
		if ( $page_id == 0 ) {
			return $data;
		}
		if ( ! is_array( $data ) ) {
			$data = self::default_design_data();
		}
		update_post_meta( $page_id, '_wfacp_selected_design', $data );

		return $data;
	}

	public static function default_design_data() {

		return [
			'selected'      => 'shopcheckout',
			'selected_type' => 'pre_built',
		];
	}

	public static function update_page_settings( $page_id, $data ) {
		if ( $page_id == 0 ) {
			return $data;
		}

		if ( ! is_array( $data ) ) {
			$data = [];
		}


		$data['update_time'] = time();
		$data['user_id']     = get_current_user_id();
		update_post_meta( $page_id, '_wfacp_page_settings', $data );

		return $data;
	}

	public static function get_checkout_fields( $page_id ) {
		$data = self::get_post_meta_data( $page_id, '_wfacp_checkout_fields' );
		if ( empty( $data ) ) {
			$layout_data  = self::get_page_layout( $page_id );
			$prepare_data = self::prepare_fieldset( $layout_data );
			$data         = $prepare_data['checkout_fields'];
		}

		return $data;
	}

	public static function get_page_layout_multistep() {
		$product_field  = self::get_product_field();
		$advanced_field = self::get_advanced_fields();
		$data           = array(
			'steps'                       => self::get_default_steps_fields( true ),
			'fieldsets'                   => array(
				'single_step' => array(
					array(
						'name'        => __( 'Customer Information', 'woofunnels-aero-checkout' ),
						'class'       => '',
						'sub_heading' => '',
						'fields'      => array(
							array(
								'label'        => __( 'Email', 'woocommerce' ),
								'required'     => 'true',
								'type'         => 'email',
								'class'        => array(
									0 => 'form-row-wide',
								),
								'validate'     => array(
									0 => 'email',
								),
								'autocomplete' => 'email username',
								'priority'     => '110',
								'id'           => 'billing_email',
								'field_type'   => 'billing',
								'placeholder'  => __( 'abc@example.com', 'woofunnels-aero-checkout' ),
							)
						),

					),
					[
						'name'        => __( 'Shipping Information', 'woofunnels-aero-checkout' ),
						'class'       => 'wfacp_contact_information',
						'is_default'  => 'yes',
						'sub_heading' => 'Fields marked with * are mandatory',
						'fields'      => array(
							array(
								'label'        => __( 'First name', 'woocommerce' ),
								'required'     => 'true',
								'class'        => [
									0 => 'form-row-first',
								],
								'autocomplete' => 'given-name',
								'priority'     => '10',
								'type'         => 'text',
								'id'           => 'billing_first_name',
								'field_type'   => 'billing',
								'placeholder'  => __( 'John', 'woofunnels-aero-checkout' ),

							),
							array(
								'label'        => __( 'Last name', 'woocommerce' ),
								'required'     => 'true',
								'class'        => array(
									0 => 'form-row-last',
								),
								'autocomplete' => 'family-name',
								'priority'     => '20',
								'type'         => 'text',
								'id'           => 'billing_last_name',
								'field_type'   => 'billing',
								'placeholder'  => __( 'Doe', 'woofunnels-aero-checkout' ),
							),

							self::get_single_address_fields( 'shipping' ),
							self::get_single_address_fields(),

						),
					],
				),
				'two_step'    => [
					[
						'name'        => __( 'Your Products', 'woofunnels-aero-checkout' ),
						'class'       => '',
						'sub_heading' => '',
						'fields'      => [
							$product_field['product_switching']
						],
					]
				],
				'third_step'  => [

					[
						'name'        => __( 'Order Summary', 'woofunnels-aero-checkout' ),
						'class'       => 'wfacp_order_summary_box',
						'sub_heading' => '',
						'fields'      => [
							$advanced_field['order_coupon'],
							$advanced_field['order_summary'],
						],
					],
				],
			),
			'enabled_product_switching'   => "yes",
			'have_billing_address'        => true,
			'have_shipping_address'       => true,
			'have_billing_address_index'  => 4,
			'have_shipping_address_index' => 3,
			'have_coupon_field'           => false,
			'have_shipping_method'        => true,
			'current_step'                => 'third_step',
		);

		$advanced_field = self::get_advanced_fields();

		if ( isset( $advanced_field['shipping_calculator'] ) ) {
			$data['fieldsets']['two_step'][] = array(
				'name'        => __( 'Shipping Method', 'woocommerce' ),
				'class'       => 'wfacp_shipping_method',
				'sub_heading' => '',
				'fields'      => array(
					$advanced_field['shipping_calculator'],
				),
			);
		}


		return $data;
	}

	/**
	 * Get page layout data
	 *
	 * @param $page_id
	 *
	 * @return array|mixed
	 */
	public static function get_page_layout( $page_id ) {

		$data = self::get_post_meta_data( $page_id, '_wfacp_page_layout' );

		if ( empty( $data ) ) {

			$data = array(
				'steps'     => self::get_default_steps_fields(),
				'fieldsets' => array(
					'single_step' => [],
				),

				'current_step'                => 'single_step',
				'have_billing_address'        => 'true',
				'have_shipping_address'       => 'true',
				'have_billing_address_index'  => 5,
				'have_shipping_address_index' => 4,
				'have_coupon_field'           => true,
				'have_shipping_method'        => true,
			);

			$data['fieldsets']['single_step'][] = array(
				'name'        => __( 'Shipping Information', 'woofunnels-aero-checkout' ),
				'class'       => 'wfacp_contact_information',
				'is_default'  => 'yes',
				'sub_heading' => __( 'Fields marked with * are mandatory', 'woofunnels-aero-checkout' ),
				'fields'      => array(
					array(
						'label'        => __( 'Email', 'woocommerce' ),
						'required'     => 'true',
						'type'         => 'email',
						'class'        => array(
							0 => 'form-row-wide',
						),
						'validate'     => array(
							0 => 'email',
						),
						'autocomplete' => 'email username',
						'priority'     => '110',
						'id'           => 'billing_email',
						'field_type'   => 'billing',
						'placeholder'  => __( 'abc@example.com', 'woofunnels-aero-checkout' ),
					),
					array(
						'label'        => __( 'First name', 'woocommerce' ),
						'required'     => 'true',
						'class'        => array(
							0 => 'form-row-first',
						),
						'autocomplete' => 'given-name',
						'priority'     => '10',
						'type'         => 'text',
						'id'           => 'billing_first_name',
						'field_type'   => 'billing',
						'placeholder'  => __( 'John', 'woofunnels-aero-checkout' ),

					),
					array(
						'label'        => __( 'Last name', 'woocommerce' ),
						'required'     => 'true',
						'class'        => array(
							0 => 'form-row-last',
						),
						'autocomplete' => 'family-name',
						'priority'     => '20',
						'type'         => 'text',
						'id'           => 'billing_last_name',
						'field_type'   => 'billing',
						'placeholder'  => __( 'Doe', 'woofunnels-aero-checkout' ),
					),
					self::get_single_address_fields( 'shipping' ),
					self::get_single_address_fields(),
					array(
						'label'        => __( 'Phone', 'woocommerce' ),
						'type'         => 'tel',
						'class'        => array( 'form-row-wide' ),
						'id'           => 'billing_phone',
						'field_type'   => 'billing',
						'validate'     => array( 'phone' ),
						'placeholder'  => '999-999-9999',
						'autocomplete' => 'tel',
						'priority'     => 100,
					),

				),
			);

			$advanced_field = self::get_advanced_fields();

			if ( isset( $advanced_field['shipping_calculator'] ) ) {
				$data['fieldsets']['single_step'][] = array(

					'name'        => __( 'Shipping Method', 'woocommerce' ),
					'class'       => 'wfacp_shipping_method',
					'sub_heading' => '',
					'fields'      => array(
						$advanced_field['shipping_calculator'],
					),
				);
			}
			$product_field                      = self::get_product_field();
			$data['fieldsets']['single_step'][] = array(
				'name'        => __( 'Your Products', 'woofunnels-aero-checkout' ),
				'class'       => 'wfacp_product_switcher',
				'sub_heading' => '',
				'fields'      => array(
					$product_field['product_switching'],
				),
			);
			$data['fieldsets']['single_step'][] = array(
				'name'        => __( 'Order Summary', 'woofunnels-aero-checkout' ),
				'class'       => 'wfacp_order_summary_box',
				'sub_heading' => '',
				'fields'      => array(
					$advanced_field['order_coupon'],
					$advanced_field['order_summary'],
				),
			);
			$data                               = apply_filters( 'wfacp_default_form_fieldset', $data );
		}

		return $data;
	}

	public static function get_builder_localization() {
		$data                                                 = [];
		$data['global']                                       = [
			'form_has_changes'                      => [
				'title'             => __( 'Changes have been made!', 'woofunnels-aero-checkout' ),
				'text'              => __( 'You need to save changes before generating preview.', 'woofunnels-aero-checkout' ),
				'confirmButtonText' => __( 'Yes, Save it!', 'woofunnels-aero-checkout' ),
				'cancelText'        => __( 'Cancel', 'woofunnels-aero-checkout' ),
			],
			'no_products'                           => __( 'No product associated with this checkout. You need to add minimum one product to generate preview', 'woofunnels-aero-checkout' ),
			'remove_product'                        => [
				'title'             => __( 'Want to Remove this product from checkout?', 'woofunnels-aero-checkout' ),
				'text'              => __( "You won't be able to revert this!", 'woofunnels-aero-checkout' ),
				'confirmButtonText' => __( 'Yes, Remove it!', 'woofunnels-aero-checkout' ),
			],
			'active'                                => __( 'Active', 'woofunnels-aero-checkout' ),
			'inactive'                              => __( 'Inactive', 'woofunnels-aero-checkout' ),
			'add_checkout'                          => [
				'heading'           => __( 'Page Title', 'woofunnels-aero-checkout' ),
				'checkout_url_slug' => __( 'Checkout URL', 'woofunnels-aero-checkout' ),
			],
			'confirm_button_text'                   => __( 'Ok', 'woofunnels-aero-checkout' ),
			'billing_email_present_only_first_step' => __( 'Billing Email field must be on step 1 for the form', 'woofunnels-aero-checkout' ),
			'cncel_button_text'                     => __( 'Cancel', 'woofunnels-aero-checkout' ),
			'delete_checkout_page'                  => __( 'Are you sure, you want to delete this permanently? This can`t be undone', 'woofunnels-aero-checkout' ),
			'add_checkout_page'                     => __( 'Add New Checkout Page', 'woofunnels-aero-checkout' ),
			'edit_checkout_page'                    => __( 'Edit Checkout Page', 'woofunnels-aero-checkout' ),
			'add_checkout_btn'                      => __( 'Create', 'woofunnels-aero-checkout' ),
			'edit_checkout_btn'                     => __( 'Update', 'woofunnels-aero-checkout' ),
			'data_saving'                           => __( 'Data Saving...', 'woofunnels-aero-checkout' ),
		];
		$data['fields']                                       = [
			'field_id_slug'     => __( 'Field ID', 'woofunnels-aero-checkout' ),
			'inputs'            => [
				'active'   => __( 'Active', 'woofunnels-aero-checkout' ),
				'inactive' => __( 'Inactive', 'woofunnels-aero-checkout' ),
			],
			'section'           => [
				'default_sub_heading' => __( 'Example: Fields marked with * are mandatory', 'woofunnels-aero-checkout' ),
				'default_classes'     => '',
				'add_heading'         => __( 'Add Section', 'woofunnels-aero-checkout' ),
				'update_heading'      => __( 'Update Section', 'woofunnels-aero-checkout' ),
				'delete'              => __( 'Want to delete {{section_name}} Section', 'woofunnels-aero-checkout' ),
				'fields'              => [
					'heading'     => __( 'Section Name', 'woofunnels-aero-checkout' ),
					'sub_heading' => __( 'Sub Heading', 'woofunnels-aero-checkout' ),
					'classes'     => __( 'Classes', 'woofunnels-aero-checkout' ),
				],
			],
			'steps_error_msgs'  => [
				'single_step' => __( 'Step 1', 'woofunnels-aero-checkout' ),
				'two_step'    => __( 'Step 2', 'woofunnels-aero-checkout' ),
				'third_step'  => __( 'Step 3', 'woofunnels-aero-checkout' ),
			],
			'empty_step_error'  => __( 'Can`t be empty before save the Fields', 'woofunnels-aero-checkout' ),
			'input_field_error' => [
				'billing_email' => __( 'Billing Email is required for processing payment', 'woofunnels-aero-checkout' ),
			],

			'same_as_billing'            => __( 'Enable checkbox to show above fields', 'woofunnels-aero-checkout' ),
			'same_as_billing_label_hint' => __( 'This will make shipping address an optional checkbox when billing address is present in the form', 'woofunnels-aero-checkout' ),

			'same_as_shipping'            => __( 'Different from shipping address', 'woofunnels-aero-checkout' ),
			'same_as_shipping_label_hint' => __( 'This will make shipping address an optional checkbox when billing address is present in the form', 'woofunnels-aero-checkout' ),

			'add_new_btn'                     => __( 'Add Section', 'woofunnels-aero-checkout' ),
			'update_btn'                      => __( 'Update Section', 'woofunnels-aero-checkout' ),
			'show_field_label1'               => __( 'Status', 'woofunnels-aero-checkout' ),
			'show_field_label2'               => __( 'Label', 'woofunnels-aero-checkout' ),
			'show_field_label3'               => __( 'Placeholder', 'woofunnels-aero-checkout' ),
			'product_you_save_merge_tags'     => __( 'Merge Tags: {{quantity}},{{saving_value}} or {{saving_percentage}}', 'woofunnels-aero-checkout' ),
			'field_types_label'               => __( 'Field Type', 'woofunnels-aero-checkout' ),
			'field_types'                     => [
				[
					'id'   => 'text',
					'name' => __( 'Single Line Text', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'password',
					'name' => __( 'Password', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'email',
					'name' => __( 'Email', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'textarea',
					'name' => __( 'Paragraph Text', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'checkbox',
					'name' => __( 'Checkbox', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'number',
					'name' => __( 'Number', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'select',
					'name' => __( 'Dropdown', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'wfacp_radio',
					'name' => __( 'Radio', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'wfacp_wysiwyg',
					'name' => __( 'HTML', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'tel',
					'name' => __( 'Phone Number', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => 'hidden',
					'name' => __( 'Hidden', 'woofunnels-aero-checkout' ),
				],
			],
			'name_field_label'                => __( 'Field ID (Order Meta Key)', 'woofunnels-aero-checkout' ),
			'name_field_label_hint'           => __( "Field ID (Order Meta Key) where value of this field gets stored. Use '_' to seperate in case of multiple words. Example: date_of_birth", 'woofunnels-aero-checkout' ),
			'label_field_label'               => __( 'Label', 'woofunnels-aero-checkout' ),
			'options_field_label'             => __( 'Options (|) separated', 'woofunnels-aero-checkout' ),
			'default_field_label'             => __( 'Default', 'woofunnels-aero-checkout' ),
			'shipping_field_placeholder'      => __( 'Placeholder', 'woofunnels-aero-checkout' ),
			'shipping_field_placeholder_hint' => __( 'Enter the default text for shipping method', 'woofunnels-aero-checkout' ),
			'default_field_placeholder'       => __( 'Default Value', 'woofunnels-aero-checkout' ),
			'order_total_breakup_label'       => __( 'Enable Detailed Summary', 'woofunnels-aero-checkout' ),
			'order_total_breakup_hint'        => __( 'Subtotal, Coupon, Fee, Shipping, taxes will hide accordingly', 'woofunnels-aero-checkout' ),
			'default_field_checkbox_options'  => [
				[
					'id'   => '1',
					'name' => __( 'True', 'woofunnels-aero-checkout' ),
				],
				[
					'id'   => '0',
					'name' => __( 'False', 'woofunnels-aero-checkout' ),
				],
			],
			'placeholder_field_label'         => __( 'Placeholder', 'woofunnels-aero-checkout' ),
			'required_field_label'            => __( 'Required', 'woofunnels-aero-checkout' ),
			'address'                         => [
				'billing_address_first_name_hint' => __( 'Please keep this field turned OFF, if you are using First name separate field in the form', 'woofunnels-aero-checkout' ),
				'billing_address_last_name_hint'  => __( 'Please keep this field turned OFF, if you are using First name separate field in the form', 'woofunnels-aero-checkout' ),
				'first_name'                      => __( 'First Name', 'woocommerce' ),
				'last_name'                       => __( 'Last Name', 'woocommerce' ),
				'label'                           => __( 'Label', 'woocommerce' ),
				'placeholder'                     => __( 'Placeholder', 'woocommerce' ),
				'street_address1'                 => __( 'Street Address', 'woocommerce' ),
				'street_address2'                 => __( 'Street Address 2', 'woocommerce' ),
				'company'                         => __( 'Company', 'woocommerce' ),
				'city'                            => __( 'City', 'woocommerce' ),
				'state'                           => __( 'State/County', 'woocommerce' ),
				'zip'                             => __( 'Zip/Postcode', 'woocommerce' ),
				'country'                         => __( 'Country', 'woocommerce' ),
			],
			'add_field'                       => __( 'Add Field', 'woofunnels-aero-checkout' ),
			'edit_field'                      => __( 'Edit Field', 'woofunnels-aero-checkout' ),
			'shipping_address_message'        => self::default_shipping_placeholder_text(),

			'show_on_thankyou' => __( 'Show on thank you page', WFACP_TEXTDOMAIN ),
			'show_in_email'    => __( 'Show in email', WFACP_TEXTDOMAIN ),

		];
		$data['design']['section']                            = [];
		$data['design']['settings']                           = [];
		$data['settings']['radio_fields']                     = [
			[
				'value' => 'true',
				'name'  => __( 'Yes', 'woofunnels-aero-checkout' ),
			],
			[
				'value' => 'false',
				'name'  => __( 'No', 'woofunnels-aero-checkout' ),
			],
		];
		$data['settings']['preview_section_heading']          = __( 'Heading (optional)', 'woofunnels-aero-checkout' );
		$data['settings']['preview_section_subheading']       = __( 'Subheading (optional)', 'woofunnels-aero-checkout' );
		$data['settings']['preview_field_admin_heading']      = __( 'Fields Preview', 'woofunnels-aero-checkout' );
		$data['settings']['preview_field_admin_heading_hint'] = __( 'Check the fields to generate the preview at next steps.', 'woofunnels-aero-checkout' );
		$data['settings']['preview_field_admin_note']         = __( 'This Feature is available only for multistep form', 'woofunnels-aero-checkout' );
		$data['settings']['scripts']                          = [
			'heading'                   => __( 'Embed Script', 'woofunnels-aero-checkout' ),
			'sub_heading'               => __( 'Add scripts to run when user reaches to checkout page', 'woofunnels-aero-checkout' ),
			'header_heading'            => __( 'Header', 'woofunnels-aero-checkout' ),
			'header_script_placeholder' => __( 'Paste your code here', 'woofunnels-aero-checkout' ),
			'footer_heading'            => __( 'Footer', 'woofunnels-aero-checkout' ),
			'footer_script_placeholder' => __( 'Paste your code here', 'woofunnels-aero-checkout' ),
		];
		$data['settings']['coupons']                          = [
			'heading'                 => __( 'Coupons', 'woofunnels-aero-checkout' ),
			'sub_heading'             => __( 'Enable Coupon Field for checkout page and add a coupon auto apply when visitor start checkout', 'woofunnels-aero-checkout' ),
			'auto_add_coupon_heading' => __( 'Automatically Apply Coupon', 'woofunnels-aero-checkout' ),
			'coupon_heading'          => __( 'Coupon Code', 'woofunnels-aero-checkout' ),
			'search_placeholder'      => __( 'Enter coupon code here', 'woofunnels-aero-checkout' ),
			'select_coupon'           => __( 'Choose Coupon', 'woofunnels-aero-checkout' ),
			'disable_coupon'          => __( 'Disable Coupon Field', 'woofunnels-aero-checkout' ),
			'active'                  => __( 'Active', 'woofunnels-aero-checkout' ),
			'inactive'                => __( 'Inactive', 'woofunnels-aero-checkout' ),
		];
		$data['settings']['product_switching']                = [
			'heading'                => __( 'Product Selection', 'woofunnels-aero-checkout' ),
			'sub_heading'            => __( 'You can manage the quantity increment, quick view provision from here', 'woofunnels-aero-checkout' ),
			'you_save_text'          => __( 'You Save text', 'woofunnels-aero-checkout' ),
			'hide_quantity_switcher' => __( 'Hide Quantity Incrementor', 'woofunnels-aero-checkout' ),
			'hide_quick_view'        => __( 'Hide Quick View', 'woofunnels-aero-checkout' ),
			'hide_product_image'     => __( 'Hide Product Image', 'woofunnels-aero-checkout' ),
		];
		$data['settings']['coupon']                           = [
			'success_message_heading'      => __( 'Success message', 'woofunnels-aero-checkout' ),
			'success_message_heading_hint' => __( '{{coupon_code}},{{coupon_value}}, Leave empty if you are not using', 'woofunnels-aero-checkout' ),
			'remove_message_heading'       => __( 'Failure message', 'woofunnels-aero-checkout' ),
			'style_heading'                => __( 'Collapsible', 'woofunnels-aero-checkout' ),
			'style_options'                => [
				[
					'value' => 'true',
					'name'  => __( 'yes', 'woofunnels-aero-checkout' ),
				],
				[
					'value' => 'false',
					'name'  => __( 'no', 'woofunnels-aero-checkout' ),
				],
			],
			'sub_heading'                  => __( 'You can manage the quantity increment, quick view provision from here', 'woofunnels-aero-checkout' ),
		];

		$data['settings']['advanced'] = [
			'heading'                           => __( 'Advanced Settings', 'woofunnels-aero-checkout' ),
			'sub_heading'                       => __( 'You can set expiry of this checkout page after certain  sales or particular date to manage sales, offers and campaigns', 'woofunnels-aero-checkout' ),
			'close_after'                       => __( 'Close This checkout Page After # of Orders', 'woofunnels-aero-checkout' ),
			'close_checkout_after_date'         => __( 'Close Checkout After Date', 'woofunnels-aero-checkout' ),
			'total_purchased_allowed'           => __( 'Total Orders Allowed', 'woofunnels-aero-checkout' ),
			'total_purchased_allowed_hint'      => __( 'After given number of order made, disable this checkout page and redirect buyer to a specified URL', 'woofunnels-aero-checkout' ),
			'total_purchased_redirect_url'      => __( 'Redirect URL', 'woofunnels-aero-checkout' ),
			'total_purchased_redirect_url_hint' => __( 'Buyer will be redirect to given URL here', 'woofunnels-aero-checkout' ),
			'close_checkout_on'                 => __( 'Close Checkout On', 'woofunnels-aero-checkout' ),
			'close_checkout_on_hint'            => __( 'Set the date to close this checkout page', 'woofunnels-aero-checkout' ),
			'close_checkout_redirect_url'       => __( 'Closed Checkout Redirect URL', 'woofunnels-aero-checkout' ),
			'close_checkout_redirect_url_hint'  => __( 'Buyer will be redirect to given URL here', 'woofunnels-aero-checkout' ),
			'note_for_global_checkout'          => __( 'Note: These settings are only applicable for dedicated checkout page', 'woofunnels-aero-checkout' ),

		];
		$shipping_address_options     = self::get_single_address_fields( 'shipping' );
		$address_options              = self::get_single_address_fields();
		$data['shipping-address']     = $shipping_address_options['fields_options'];
		$data['address']              = $address_options['fields_options'];

		$shipping_address_options = self::get_single_address_fields( 'shipping' );
		$address_options          = self::get_single_address_fields();
		$data['shipping-address'] = $shipping_address_options['fields_options'];
		$data['address']          = $address_options['fields_options'];
		$data                     = apply_filters( 'wfacp_builder_default_localization', $data );

		return $data;
	}

	public static function get_single_address_fields( $type = 'billing' ) {

		$address_field = array(
			'required'   => '1',
			'class'      => [ 'wfacp-col-half' ],
			'cssready'   => [ 'wfacp-col-half' ],
			'id'         => 'address',
			'field_type' => 'billing',
		);

		if ( 'billing' == $type ) {
			$address_field['label'] = __( 'Billing Address', 'woocommerce' );
		} else {
			$address_field['label'] = __( 'Shipping Address', 'woocommerce' );
			unset( $address_field['required'] );
		}

		if ( 'shipping' === $type ) {
			$address_field['id']                                = 'shipping-address';
			$address_field['fields_options']['same_as_billing'] = array(
				'same_as_billing'       => 'false',
				'same_as_billing_label' => __( 'Use a different shipping address', 'woofunnels-aero-checkout' ),

			);
		} else {
			$address_field['fields_options']['same_as_shipping'] = array(
				'same_as_shipping'       => 'true',
				'same_as_shipping_label' => __( 'Use a different billing address', 'woofunnels-aero-checkout' ),

			);
		}

		$address_field['fields_options']['first_name'] = array(
			'first_name'             => 'false',
			'first_name_label'       => __( 'First Name', 'woocommerce' ),
			'first_name_placeholder' => __( 'John', 'woofunnels-aero-checkout' ),
			'hint'                   => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_first_name',
		);
		$address_field['fields_options']['last_name']  = array(
			'last_name'             => 'false',
			'last_name_label'       => __( 'Last Name', 'woocommerce' ),
			'last_name_placeholder' => __( 'Doe', 'woofunnels-aero-checkout' ),
			'hint'                  => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_last_name',
		);

		$address_field['fields_options']['company']   = array(
			'company'             => 'false',
			'company_label'       => __( 'Company', 'woocommerce' ),
			'company_placeholder' => '',
			'hint'                => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_company',
		);
		$address_field['fields_options']['address_1'] = array(
			'street_address1'              => 'true',
			'street_address_1_label'       => __( 'Street address', 'woocommerce' ),
			'street_address_1_placeholder' => __( 'House Number and Street Name', 'woocommerce' ),
			'hint'                         => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_address_1',
		);
		$address_field['fields_options']['address_2'] = array(
			'street_address2'              => 'false',
			'street_address_2_label'       => __( 'Apartment, suite, unit etc', 'woocommerce' ),
			'street_address_2_placeholder' => __( 'Apartment, suite, unit etc. (optional)', 'woocommerce' ),
			'hint'                         => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_address_2',
		);
		$address_field['fields_options']['city']      = array(
			'address_city'             => 'true',
			'address_city_label'       => __( 'Town / City', 'woocommerce' ),
			'address_city_placeholder' => 'Albany',
			'hint'                     => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_city',
		);
		$address_field['fields_options']['postcode']  = array(
			'address_postcode'             => 'true',
			'address_postcode_label'       => __( 'Postcode', 'woocommerce' ),
			'address_postcode_placeholder' => 12084,
			'hint'                         => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_postcode',
		);
		$address_field['fields_options']['country']   = array(
			'address_country'             => 'true',
			'address_country_label'       => __( 'Country', 'woocommerce' ),
			'address_country_placeholder' => 'United States',
			'hint'                        => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_country',
		);
		$address_field['fields_options']['state']     = array(
			'address_state'             => 'true',
			'address_state_label'       => __( 'State / County', 'woocommerce' ),
			'address_state_placeholder' => 'New York',
			'hint'                      => __( 'Field ID: ', 'woofunnels-aero-checkout' ) . $type . '_state',
		);

		return $address_field;
	}

	public static function get_default_steps_fields( $active_steps = false ) {

		return array(
			'single_step' => array(
				'name'          => __( 'Step 1', 'woofunnels-aero-checkout' ),
				'slug'          => 'single_step',
				'friendly_name' => __( 'Single Step Checkout Form', 'woofunnels-aero-checkout' ),
				'active'        => 'yes',
			),
			'two_step'    => array(
				'name'          => __( 'Step 2', 'woofunnels-aero-checkout' ),
				'slug'          => 'two_step',
				'friendly_name' => __( 'Two Step Checkout Form', 'woofunnels-aero-checkout' ),
				'active'        => true === $active_steps ? 'yes' : 'no',
			),
			'third_step'  => array(
				'name'          => __( 'Step 3', 'woofunnels-aero-checkout' ),
				'slug'          => 'third_step',
				'friendly_name' => __( 'Three Step Checkout Form', 'woofunnels-aero-checkout' ),
				'active'        => true === $active_steps ? 'yes' : 'no',
			),
		);
	}

	public static function get_advanced_fields() {
		$field = array(
			'order_comments' => [
				'type'  => 'textarea',
				'class' => [ 'notes' ],
				'id'    => 'order_comments',
				'label' => __( 'Order notes', 'woocommerce' ),
			],
		);

		if ( wc_shipping_enabled() ) {
			$field['shipping_calculator'] = [
				'type'       => 'wfacp_html',
				'field_type' => 'advanced',
				'id'         => 'shipping_calculator',
				'default'    => self::default_shipping_placeholder_text(),
				'class'      => [ 'wfacp_shipping_calculator' ],
				'label'      => __( 'Shipping method', 'woocommerce' ),
			];
		}

		$field['order_summary'] = [
			'type'       => 'wfacp_html',
			'field_type' => 'advanced',
			'class'      => [ 'wfacp_order_summary' ],
			'id'         => 'order_summary',
			'label'      => __( 'Order Summary', 'woocommerce' ),
		];

		$field['order_total'] = [
			'type'       => 'wfacp_html',
			'field_type' => 'advanced',
			'class'      => [ 'wfacp_order_total' ],
			'default'    => false,
			'id'         => 'order_summary',
			'label'      => __( 'Order Total', 'woofunnels-aero-checkout' )
		];

		$field['order_coupon'] = [
			'type'                           => 'wfacp_html',
			'field_type'                     => 'advanced',
			'class'                          => [ 'wfacp_order_coupon' ],
			'id'                             => 'order_coupon',
			'coupon_style'                   => 'true',
			'coupon_success_message_heading' => __( 'Congrats! Coupon code {{coupon_code}} ({{coupon_value}}) applied successfully.', 'woocommerce' ),
			'coupon_remove_message_heading'  => __( 'Coupon code removed successfully.', 'woocommerce' ),
			'label'                          => __( 'Coupon', 'woocommerce' ),
		];

		return apply_filters( 'wfacp_advanced_fields', $field );
	}

	public static function get_product_field() {
		$output = [];

		$output['product_switching'] = [
			'type'                           => 'product',
			'class'                          => [ 'wfacp_product_switcher' ],
			'cssready'                       => [ 'wfacp-col-full' ],
			'id'                             => 'product_switching',
			'label'                          => __( 'Products', 'woocommerce' ),
			'is_hide_additional_information' => false,
			'additional_information_title'   => self::get_default_additional_information_title(),
			'hide_quantity_switcher'         => false,
			'hide_you_save'                  => true,
			'default'                        => self::get_default_you_save_text(),
			'field_type'                     => 'product',
			'placeholder'                    => '',
		];

		$output = apply_filters( 'wfacp_products_field', $output );

		return $output;
	}

	/**
	 * Prepare fieldset using this prepration we display section wise field on frontend
	 *
	 * @param $data
	 *
	 * @return array
	 */
	private static function prepare_fieldset( $data ) {

		$fieldsets             = $data['fieldsets'];
		$checkout_fields       = [];
		$have_billing_address  = wc_string_to_bool( $data['have_billing_address'] );
		$have_shipping_address = wc_string_to_bool( $data['have_shipping_address'] );

		$hide_apply_cls_type = '';
		if ( $have_shipping_address && $have_billing_address ) {
			$have_billing_address_index  = absint( $data['have_billing_address_index'] );
			$have_shipping_address_index = absint( $data['have_shipping_address_index'] );

			if ( $have_billing_address_index < $have_shipping_address_index ) {
				$hide_apply_cls_type = 'shipping';
			} else {
				$hide_apply_cls_type = 'billing';
			}
		}

		if ( ! is_array( $fieldsets ) ) {
			return [
				'fieldset' => [],
				'fields'   => [],
			];
		}

		foreach ( $fieldsets as $step => $sections ) {
			if ( is_array( $sections ) && count( $sections ) > 0 ) {
				foreach ( $sections as $section_index => $section ) {
					if ( ! isset( $section['fields'] ) || count( $section['fields'] ) == 0 ) {
						continue;
					}
					$fields       = $section['fields'];
					$newFields    = [];
					$custom_index = 0;
					foreach ( $fields as $field_index => $field ) {
						$field_id   = isset( $field['id'] ) ? $field['id'] : '';
						$field_type = isset( $field['field_type'] ) ? $field['field_type'] : '';
						if ( ( $field_id == 'address' || $field_id == 'shipping-address' ) && in_array( $field_type, [ 'billing', 'shipping' ] ) ) {
							$field_type = 'billing';
							if ( $field_id == 'shipping-address' ) {
								$field_type = 'shipping';
							}
							// Merge address field into separate fields
							$add_fields = self::get_address_fields( $field_type . '_', true );
							if ( is_array( $add_fields ) && count( $add_fields ) > 0 ) {

								$newFields[ 'wfacp_start_divider_' . $field_type ] = self::get_start_divider_field( $field_type );

								$addRessData    = $fields[ $field_index ];
								$fields_options = $addRessData['fields_options'];

								$fields_options = apply_filters( 'wfacp_address_fields_' . $field_type, $fields_options );

								foreach ( $fields_options as $field_key => $field_value ) {
									if ( is_null( $field_value ) ) {
										continue;
									}
									$temp_key   = $field_type . '_' . $field_key;
									$temp_value = array_values( $field_value );
									if ( ! isset( $add_fields[ $temp_key ] ) ) {
										continue;
									}
									if ( ( false == $have_billing_address && 'shipping_same_as_billing' == $temp_key ) ) {
										continue;
									}
									if ( false == $have_shipping_address && 'billing_same_as_shipping' == $temp_key ) {
										continue;
									}
									$val = $add_fields[ $temp_key ];
									if ( 'true' === $temp_value[0] ) {

										if ( 'shipping_same_as_billing' == $temp_key && 'billing' == $hide_apply_cls_type ) {
											continue;
										}
										if ( 'billing_same_as_shipping' == $temp_key && 'shipping' == $hide_apply_cls_type ) {
											continue;
										}
										if ( isset( $temp_value[1] ) && '' !== $temp_value[1] ) {
											$val['label'] = $temp_value[1];
										}
										if ( isset( $temp_value[2] ) && '' !== $temp_value[2] ) {
											$val['placeholder'] = $temp_value[2];
										}
										$val['id'] = $temp_key;
										if ( 'shipping' == $hide_apply_cls_type && 'shipping' == $field_type && 'shipping_same_as_billing' != $temp_key ) {
											if ( wc_string_to_bool( $fields_options['same_as_billing']['same_as_billing'] ) === true ) {
												$val['class'][] = 'wfacp_' . $field_type . '_fields';
												$val['class'][] = 'wfacp_' . $field_type . '_field_hide';

												//unset( $val['required'] );
											}
										}
										if ( 'billing' == $hide_apply_cls_type && 'billing' == $field_type && 'billing_same_as_shipping' != $temp_key ) {
											if ( wc_string_to_bool( $fields_options['same_as_shipping']['same_as_shipping'] ) === true ) {
												$val['class'][] = 'wfacp_' . $field_type . '_fields';
												$val['class'][] = 'wfacp_' . $field_type . '_field_hide';

												//unset( $val['required'] );
											}
										}

										if ( isset( $val['required'] ) && 'false' === $val['required'] ) {
											unset( $val['required'] );
										}
										$val['address_group']                        = true;
										$checkout_fields[ $field_type ][ $temp_key ] = $val;
										$newFields[ $custom_index ]                  = $val;
										$custom_index ++;
									} else {

										if ( $val['type'] == 'country' ) {
											//                                          $val['type']          = 'hidden';
											$val['id']            = $temp_key;
											$val['class'][]       = 'wfacp_country_field_hide';
											$default_customer_add = get_option( 'woocommerce_default_customer_address', '' );

											if ( '' == $default_customer_add ) {
												$wc_default = wc_get_base_location();
												if ( isset( $wc_default['country'] ) && '' !== $wc_default['country'] ) {
													$default_country = trim( $wc_default['country'] );
												} elseif ( class_exists( 'WC_Geolocation' ) ) {
													$ip_data = WC_Geolocation::geolocate_ip();

													if ( is_array( $ip_data ) && isset( $ip_data['country'] ) ) {
														$default_country = $ip_data['country'];
													}
												}
											} else {
												$wc_default = wc_get_base_location();
												if ( isset( $wc_default['country'] ) && '' !== $wc_default['country'] ) {
													$default_country = trim( $wc_default['country'] );
												}
											}


											$val['default'] = $default_country;
											if ( isset( $val['required'] ) ) {
												unset( $val['required'] );
											}
											$checkout_fields[ $field_type ][ $temp_key ] = $val;
											$newFields[ $custom_index ]                  = $val;
											$custom_index ++;
										}
									}
									unset( $temp_key, $temp_value, $field_value );
								}
								$newFields[ 'wfacp_end_divider_' . $field_type ] = self::get_end_divider_field();
								unset( $fields[ $field_index ], $fields_options, $addRessData, $add_fields );
							}
						} else {
							if ( isset( $field['required'] ) && 'false' === $field['required'] ) {
								unset( $field['required'] );
							}

							$checkout_fields[ $field_type ][ $field_id ] = $field;
							$newFields[ $custom_index ]                  = $field;
							$custom_index ++;
						}
					}
					$fieldsets[ $step ][ $section_index ]['fields'] = $newFields;
				}
			}
		}

		unset( $data, $newFields, $custom_index );

		return [
			'fieldsets'       => $fieldsets,
			'checkout_fields' => $checkout_fields,
		];
	}

	public static function get_address_fields( $type = 'billing_', $unset = false ) {

		$unset_address_fields = [
			'billing_'  => [ 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_same_as_shipping' ],
			'shipping_' => [ 'shipping_company', 'shipping_country', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_same_as_billing' ],
		];
		$countries            = new WC_Countries();
		$country              = $countries->get_base_country();

		if ( is_admin() ) {
			do_action( 'wfacp_before_get_address_field_admin' );
			remove_all_filters( 'woocommerce_default_address_fields' );
		}
		$fields = $countries->get_default_address_fields();


		$locale = $countries->get_country_locale();

		if ( isset( $locale[ $country ] ) ) {
			$fields = wc_array_overlay( $fields, $locale[ $country ] );
		}

		$address_fields = array();

		foreach ( $fields as $key => $value ) {
			if ( 'state' === $key ) {
				$value['country_field'] = $type . 'country';
			}

			if ( ! isset( $value['type'] ) || '' == $value['type'] ) {
				$value['type'] = 'text';
			}
			if ( ! isset( $value['cssready'] ) || '' == $value['cssready'] ) {
				$value['cssready'] = [];
			}
			$field_key                                   = $type . $key;
			$address_fields[ $field_key ]                = $value;
			$address_fields[ $field_key ]['placeholder'] = isset( $value['label'] ) ? $value['label'] : '';
			if ( $field_key == 'shipping_state' || $field_key == 'billing_state' ) {
				$address_fields[ $field_key ]['class'][] = 'update_totals_on_change';
			}

			if ( false == $unset && in_array( $field_key, $unset_address_fields[ $type ] ) ) {

				unset( $address_fields[ $field_key ] );
			}
		}
		if ( false != $unset ) {
			if ( 'shipping_' === $type ) {

				$address_fields['shipping_same_as_billing'] = [
					'label'          => __( 'Use a different shipping address', 'woofunnels-aero-checkout' ),
					'type'           => 'checkbox',
					'value'          => 'off',
					'is_wfacp_field' => true,
					'class'          => [],
					'priority'       => 100,
				];
			} else {
				$address_fields['billing_same_as_shipping'] = [
					'label'          => __( 'Use a different billing address', 'woofunnels-aero-checkout' ),
					'type'           => 'checkbox',
					'value'          => 'off',
					'is_wfacp_field' => true,
					'class'          => [],
					'priority'       => 100,
				];
			}
		}

		if ( 'billing_' === $type ) {
			if ( 'hidden' !== get_option( 'woocommerce_checkout_phone_field', 'required' ) ) {
				$address_fields['billing_phone'] = array(
					'label'        => __( 'Phone', 'woocommerce' ),
					'type'         => 'tel',
					'class'        => array( 'form-row-wide' ),
					'validate'     => array( 'phone' ),
					'placeholder'  => '999-999-9999',
					'autocomplete' => 'tel',
					'priority'     => 100,
				);
			}
			$address_fields['billing_email'] = array(
				'label'        => __( 'Email', 'woocommerce' ),
				'required'     => true,
				'type'         => 'email',
				'class'        => array( 'form-row-wide' ),
				'validate'     => array( 'email' ),
				'autocomplete' => 'no' === get_option( 'woocommerce_registration_generate_username' ) ? 'email' : 'email username',
				'priority'     => 110,
			);
		}


		return apply_filters( 'wfacp_' . $type . 'field', $address_fields, $type );
	}

	public static function get_start_divider_field( $unique_key = '' ) {

		if ( '' == $unique_key ) {
			$unique_key = uniqid( 'wfacp_field_' );
		}

		return [
			'type'        => 'wfacp_start_divider',
			'label_class' => [ 'wfacp_divider_field', 'wfacp_divider_' . $unique_key ],
			'id'          => 'wfacp_divider_' . $unique_key,
		];
	}

	public static function get_end_divider_field() {

		return [
			'type' => 'wfacp_end_divider',
		];
	}

	public static function get_fieldset_data( $page_id ) {
		$data = self::get_post_meta_data( $page_id, '_wfacp_fieldsets_data' );

		if ( empty( $data ) ) {
			$data         = [];
			$layout_data  = self::get_page_layout( $page_id );
			$prepare_data = self::prepare_fieldset( $layout_data );

			$data['current_step']                = $layout_data['current_step'];
			$data['have_billing_address']        = wc_string_to_bool( $layout_data['have_billing_address'] );
			$data['have_shipping_address']       = wc_string_to_bool( $layout_data['have_shipping_address'] );
			$data['have_billing_address_index']  = $layout_data['have_billing_address_index'];
			$data['have_shipping_address_index'] = $layout_data['have_shipping_address_index'];
			$data['enabled_product_switching']   = isset( $layout_data['enabled_product_switching'] ) ? $layout_data['enabled_product_switching'] : 'no';
			$data['have_coupon_field']           = $layout_data['have_coupon_field'];
			$data['fieldsets']                   = $prepare_data['fieldsets'];
		}

		return $data;
	}

	public static function update_page_layout( $page_id, $data, $update_switcher = true ) {
		if ( $page_id == 0 ) {
			return $data;
		}
		$prepare_data = self::prepare_fieldset( $data );
		unset( $data['wfacp_id'], $data['action'], $data['wfacp_nonce'] );

		$fieldset_data = [
			'have_billing_address'        => $data['have_billing_address'],
			'have_shipping_address'       => $data['have_shipping_address'],
			'have_billing_address_index'  => $data['have_billing_address_index'],
			'have_shipping_address_index' => $data['have_shipping_address_index'],
			'enabled_product_switching'   => $data['enabled_product_switching'],
			'have_coupon_field'           => $data['have_coupon_field'],
			'have_shipping_method'        => $data['have_shipping_method'],
			'current_step'                => $data['current_step'],
			'fieldsets'                   => $prepare_data['fieldsets'],
		];

		//this meta use form generate form at form builder
		update_post_meta( $page_id, '_wfacp_page_layout', $data );
		//this meta use for printing the Form
		update_post_meta( $page_id, '_wfacp_fieldsets_data', $fieldset_data );
		//this meta use for woocommerce_checkout_field filter hooks
		update_post_meta( $page_id, '_wfacp_checkout_fields', $prepare_data['checkout_fields'] );

		if ( true === $update_switcher ) {
			self::update_product_switcher_setting( $page_id, $data );
		}

		do_action( 'wfacp_update_page_layout', $page_id, $data );
		unset( $prepare_data, $fieldset_data );
	}

	public static function update_page_custom_fields( $wfacp_id, $data = [] ) {
		if ( $wfacp_id == 0 ) {
			return;
		}
		update_post_meta( $wfacp_id, '_wfacp_page_custom_field', $data );
	}

	/**
	 * remove unnecessay keys from single product array
	 */
	public static function remove_product_keys( $product ) {
		unset( $product['image'] );
		unset( $product['price'] );
		unset( $product['regular_price'] );
		unset( $product['sale_price'] );

		return $product;
	}


	public static function get_page_data( $wfacp_id ) {
		return [];
	}

	public static function set_customizer_fields_default_vals( $data ) {


		if ( ! is_array( $data ) || count( $data ) == 0 ) {
			return;
		}


		$default_values = array();
		foreach ( $data as $panel_single ) {
			if ( empty( $panel_single ) ) {
				continue;
			}
			/** Panel */
			foreach ( $panel_single as $panel_key => $panel_arr ) {
				/** Section */
				if ( is_array( $panel_arr['sections'] ) && count( $panel_arr['sections'] ) > 0 ) {
					foreach ( $panel_arr['sections'] as $section_key => $section_arr ) {
						$section_key_final = $panel_key . '_' . $section_key;
						/** Fields */
						if ( is_array( $section_arr['fields'] ) && count( $section_arr['fields'] ) > 0 ) {
							foreach ( $section_arr['fields'] as $field_key => $field_data ) {
								$field_key_final = $section_key_final . '_' . $field_key;

								if ( isset( $field_data['default'] ) ) {
									$default_values[ $field_key_final ] = $field_data['default'];
								}
							}
						}
					}
				}
			}
		}
		self::$customizer_fields_default = $default_values;

	}

	public static function get_date_format() {
		return get_option( 'date_format', '' ) . ' ' . get_option( 'time_format', '' );
	}

	public static function posts_per_page() {
		return apply_filters( 'wfacp_post_per_page', 10 );
	}

	public static function pr( $arr ) {
		echo '<br /><pre>';
		print_r( $arr );
		echo '</pre><br />';
	}

	public static function dump( $arr ) {
		echo '<pre>';
		var_dump( $arr );
		echo '</pre>';
	}

	public static function export( $arr ) {
		echo '<pre>';
		var_export( $arr );
		echo '</pre>';
	}

	/**
	 * Check our customizer page is open or not
	 * @return bool
	 */
	public static function is_customizer() {
		if ( isset( $_REQUEST['wfacp_customize'] ) && $_REQUEST['wfacp_customize'] == 'loaded' && isset( $_REQUEST['wfacp_id'] ) && $_REQUEST['wfacp_id'] > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Checkout Placeorder button pressed and checout process started
	 * @return bool
	 */
	public static function is_checkout_process() {
		if ( isset( $_REQUEST['_wfacp_post_id'] ) && $_REQUEST['_wfacp_post_id'] > 0 ) {
			return true;
		}

		return false;
	}

	public static function unset_blank_keys_old( $data_array ) {

		foreach ( $data_array as $key => $value ) {
			if ( $value == '' ) {
				unset( $data_array[ $key ] );
			}
		}

		return $data_array;
	}

	public static function unset_blank_keys( $array_for_check ) {
		if ( is_array( $array_for_check ) && count( $array_for_check ) > 0 ) {
			foreach ( $array_for_check as $key => $value ) {
				if ( is_array( $value ) && count( $value ) > 0 ) {
					continue;
				}
				if ( $value == '' ) {
					unset( $array_for_check[ $key ] );
				}
			}
		}

		return $array_for_check;

	}

	/**
	 * UPdate all custom field into post meta when placeorder button presed
	 *
	 * @param $order_id
	 * @param $data
	 */
	public static function update_checkout_custom_field( $order_id, $data ) {
		if ( empty( $data ) ) {
			return;
		}
		if ( ! isset( $_REQUEST['_wfacp_post_id'] ) ) {
			return;
		}
		$wfacp_id = absint( $_REQUEST['_wfacp_post_id'] );
		if ( $wfacp_id > 0 ) {
			update_post_meta( $order_id, '_wfacp_post_id', $wfacp_id );

			$cfields = WFACP_Common::get_page_custom_fields( $wfacp_id );
			if ( ! isset( $cfields['advanced'] ) ) {
				return;
			}
			$advancedFields = $cfields['advanced'];
			if ( ! is_array( $advancedFields ) || count( $advancedFields ) == 0 ) {
				return;
			}

			foreach ( $advancedFields as $field_key => $field ) {
				if ( isset( $_REQUEST[ $field_key ] ) ) {
					update_post_meta( $order_id, $field_key, wc_clean( $_REQUEST[ $field_key ] ) );
				}
			}
		}

	}

	public static function get_page_custom_fields( $wfacp_id ) {

		$fields = self::get_post_meta_data( $wfacp_id, '_wfacp_page_custom_field' );


		if ( ! is_array( $fields ) || empty( $fields ) ) {
			$fields = [ 'advanced' => [] ];
		}

		$advanced_fields = self::get_advanced_fields();
		if ( count( $advanced_fields ) > 0 ) {
			foreach ( $advanced_fields as $key => $field ) {
				$fields['advanced'][ $key ] = $field;
			}
		}

		return apply_filters( 'wfacp_custom_fields', $fields );
	}

	/**
	 * Return Schema and model data for global setting in admin page
	 *
	 * @param bool $only_model
	 *
	 * @return array
	 */
	public static function global_settings( $only_model = false ) {

		$output      = self::get_default_global_settings();
		$save_models = get_option( '_wfacp_global_settings', [] );
		$models      = [];
		foreach ( $output as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $k => $group_data ) {
					if ( ! isset( $group_data['fields'] ) && count( $group_data['fields'] ) == 0 ) {
						continue;
					}
					foreach ( $group_data['fields'] as $index => $field ) {
						if ( ! isset( $field['model'] ) ) {
							continue;
						}
						$model   = trim( $field['model'] );
						$default = isset( $field['default'] ) ? $field['default'] : '';
						if ( ! empty( $save_models[ $model ] ) ) {
							$default = $save_models[ $model ];
						}
						$models[ $model ] = $default;
					}
				}
			}
		}
		if ( $only_model ) {
			$models['invalid_email_field'] = __( '%s is not a valid email address.', 'woocommerce' );
			$models['error_required_msg']  = __( '%s is a required field.', 'woocommerce' );

			return $models;
		}

		return [
			'schema' => $output,
			'model'  => $models,
		];
	}

	/**
	 * Get default global setting schema
	 * @return array
	 */
	public static function get_default_global_settings() {
		$data = [];

		$wfacp_tracking_analytics = [
			'fields' => [
				[
					'type'         => 'label',
					'styleClasses' => 'wfacp_global_label_cls',
					'label'        => __( 'Facebook Pixel Tracking', 'woofunnels-aero-checkout' ),
				],
				[
					'type'         => 'input',
					'inputType'    => 'text',
					'label'        => __( 'Facebook Pixel ID', 'woofunnels-aero-checkout' ),
					'default'      => '',
					'styleClasses' => [ 'wfacp_html_hint', 'wfacp_checkout_pixel_id' ],
					'hint'         => __( 'Log into your facebook ads account to find your Pixel ID <a target="_blank" href="https://www.facebook.com/ads/manager/pixel/facebook_pixel">Click here for more info.</a>', 'woofunnels-aero-checkout' ),
					'model'        => 'wfacp_checkout_pixel_id',
				],
				[
					'type'         => 'checkbox',
					'inputType'    => 'text',
					'label'        => __( 'Enable AddtoCart Event', 'woofunnels-aero-checkout' ),
					'default'      => '',
					'styleClasses' => [ 'wfacp_checkbox_wrap' ],
					'model'        => 'wfacp_checkout_pixel_add_to_cart_event',
					'is_bool'      => true,
				],
				[
					'type'         => 'checkbox',
					'inputType'    => 'text',
					'label'        => __( 'Enable InitiateCheckout Event', 'woofunnels-aero-checkout' ),
					'default'      => '',
					'styleClasses' => [ 'wfacp_checkbox_wrap' ],
					'model'        => 'wfacp_checkout_pixel_initiate_checkout_event',
					'is_bool'      => true,
				],

			],
			'legend' => __( 'Tracking & Analytics', 'woofunnels-aero-checkout' ),
		];

		$Woofunnel_transient_obj = WooFunnels_Transient::get_instance();

		$wfacp_miscellaneous_analytics = [
			'fields' => [
				[
					'type'         => 'label',
					'styleClasses' => 'wfacp_global_label_cls',
					'label'        => __( 'Checkout Settings', 'woofunnels-aero-checkout' ),
				],
				[
					'type'         => 'input',
					'inputType'    => 'text',
					'styleClasses' => 'group-one-class',
					'label'        => __( 'Checkout Page Slug', 'woofunnels-aero-checkout' ),
					'hint'         => __( 'Please use a unique slug which is not in use by WooCommerce or any other page in your site', 'woofunnels-aero-checkout' ),
					'default'      => self::get_url_rewrite_slug(),
					'model'        => 'rewrite_slug',
				],
				[
					'type'          => 'select',
					'styleClasses'  => 'group-one-class',
					'label'         => __( 'Override Default Checkout Page', 'woofunnels-aero-checkout' ),
					'hint'          => __( 'Set Aero Checkout page as  default page for your all products', 'woofunnels-aero-checkout' ),
					'default'       => '0',
					'values'        => $Woofunnel_transient_obj->get_transient( 'wfacp_publish_posts', WFACP_SLUG ),
					'model'         => 'override_checkout_page_id',
					'selectOptions' => [
						'hideNoneSelectedText' => true,
					],
				],
				[
					'type'         => 'checkbox',
					'inputType'    => 'text',
					'label'        => __( 'Set Shipping Method Prices in Ascending Order', 'woofunnels-aero-checkout' ),
					'default'      => '',
					'styleClasses' => 'group-one-class wfacp_set_shipping_method_wrap wfacp_checkbox_wrap',
					'model'        => 'wfacp_set_shipping_method',
					'is_bool'      => false,
				],

			],
			'legend' => __( 'Miscellaneous', 'woofunnels-aero-checkout' ),
		];

		$wfacp_appearance = [
			'fields' => [

				[
					'type'         => 'textArea',
					'inputType'    => 'text',
					'label'        => __( 'Custom CSS Tweaks', 'woofunnels-aero-checkout' ),
					'styleClasses' => 'wfacp_global_css_wrap_field',
					'model'        => 'wfacp_checkout_global_css',
					'hint'         => __( 'Add your custom CSS to apply on all AeroCheckout pages', 'woofunnels-aero-checkout' ),

				],

			],
			'legend' => __( 'Global Custom CSS', 'woofunnels-aero-checkout' ),
		];

		$wfacp_external_script = [
			'fields' => [

				[
					'type'         => 'textArea',
					'inputType'    => 'text',
					'label'        => __( 'External JS Scripts', 'woofunnels-aero-checkout' ),
					'styleClasses' => 'wfacp_global_external_script_field',
					'model'        => 'wfacp_global_external_script',
					'hint'         => __( 'These scripts will be globally embedded in all the AeroCheckout Pages.', 'woofunnels-aero-checkout' ),

				],

			],
			'legend' => __( 'External Scripts', 'woofunnels-aero-checkout' ),
		];

		$data['groups'][] = $wfacp_tracking_analytics;
		$data['groups'][] = $wfacp_miscellaneous_analytics;
		$data['groups'][] = $wfacp_appearance;
		$data['groups'][] = $wfacp_external_script;

		return $data;
	}

	/**
	 * Get default global setting Error Messages
	 * @return array
	 */
	public static function get_error_message() {

		$msg = [
			'required' => __( 'is required field', 'woofunnels-aero-checkout' ),
			'invalid'  => __( 'is not a valid', 'woofunnels-aero-checkout' ),

		];

		return $msg;

	}

	public static function base_url() {
		$slug = self::get_url_rewrite_slug();

		return home_url( "/{$slug}/" );
	}

	public static function product_switcher_merge_tags( $content, $price_data, $pro = false, $product_data = [], $cart_item = [], $cart_item_key = '' ) {
		return WFACP_Product_Switcher_Merge_Tags::maybe_parse_merge_tags( $content, $price_data, $pro, $product_data, $cart_item, $cart_item_key );
	}

	/**
	 * This function print our custom hidden field type `hidden`
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 *
	 * @return string
	 */
	public static function woocommerce_form_field_hidden( $field, $key, $args, $value ) {
		$args['input_class'][] = 'wfacp_hidden_field';
		$field                 = '<input type="' . esc_attr( $args['type'] ) . '" class="input-hidden ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '"  />';

		return $field;
	}

	/**
	 * This function print our custom radion field type `wfacp_radio`
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 *
	 * @return string
	 */
	public static function woocommerce_form_field_wfacp_radio( $field, $key, $args, $value ) {

		$label_id        = $args['id'];
		$args['class'][] = 'wfacp_custom_field_radio_wrap';
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
		} else {
			$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
		}
		$sort              = $args['priority'] ? $args['priority'] : '';
		$field_container   = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';
		$field             = '';
		$custom_attributes = [];

		unset( $args['input_class'][0] );
		unset( $args['label_class'][0] );
		if ( ! empty( $args['options'] ) ) {
			foreach ( $args['options'] as $option_key => $option_text ) {
				$field .= "<span class='wfacp_radio_options_group'>";
				$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
				$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . $option_text . '</label>';
				$field .= '</span>';
			}
		}

		$field_html = '';
		if ( $args['label'] && 'checkbox' !== $args['type'] ) {
			$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
		}

		$field_html .= '<span class="woocommerce-input-wrapper wfacp-form-control">' . $field;

		if ( $args['description'] ) {
			$field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
		}

		$field_html .= '</span>';

		$container_class = esc_attr( implode( ' ', $args['class'] ) );
		$container_id    = esc_attr( $args['id'] ) . '_field';
		$field           = sprintf( $field_container, $container_class, $container_id, $field_html );

		return $field;
	}

	/**
	 * This function print our custom start div tag field type `_wfacp_start_divider`
	 * this field for separet some field from other fields
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 *
	 * @return string
	 */
	public static function woocommerce_form_field_wfacp_start_divider( $field, $key, $args, $value ) {

		ob_start();
		echo '<div class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">';


		if ( 'wfacp_divider_billing' == $args['id'] ) {
			do_action( 'wfacp_divider_billing' );

		}
		if ( 'wfacp_divider_shipping' == $args['id'] ) {
			do_action( 'wfacp_divider_shipping' );
		}

		return ob_get_clean();

	}

	/**
	 * This function print our custom start div tag field type `wfacp_end_start_divider`
	 * this field for separet some field from other fields
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 *
	 * @return string
	 */
	public static function woocommerce_form_field_wfacp_end_start_divider( $field, $key, $args, $value ) {
		return '</div>';
	}

	/**
	 * This function print our custom product switcher layout
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 *
	 * @return string
	 */
	public static function woocommerce_form_field_wfacp_product( $field_html, $key, $field, $value ) {

		if ( apply_filters( 'wfacp_skip_' . $field['id'], false ) ) {
			return '';
		}
		if ( 'product_switching' == $field['id'] ) {
			ob_start();
			$instance = WFACP_Core()->customizer->get_template_instance();
			WC()->session->set( 'wfacp_product_switcher_field_' . WFACP_Common::get_id(), $field );
			if ( WFACP_Core()->public->is_checkout_override() ) {
				echo '<div class="wfacp_clear"></div>';
				self::get_product_global_quantity_bump();
				echo '<div class="wfacp_clear"></div>';
			} else {
				WC()->session->set( 'wfacp_product_best_value_' . WFACP_Common::get_id(), $instance->customizer_fields_data['wfacp_form']['form_data']['best_value'] );
				echo '<div class="wfacp_clear"></div>';
				self::get_product_switcher_table();
				echo '<div class="wfacp_clear"></div>';
			}
			$field_html = ob_get_clean();
		}

		return $field_html;
	}

	public static function get_option( $field ) {
		if ( empty( $field ) ) {
			return '';
		}

		/** If data not fetched once */
		if ( empty( self::$customizer_key_data ) ) {
			self::$customizer_key_data = get_option( self::$customizer_key_prefix );
		}

		/** Field found in customizer get option */
		if ( isset( $field ) ) {

			if ( is_array( self::$customizer_key_data ) && isset( self::$customizer_key_data[ $field ] ) ) {
				$value = self::$customizer_key_data[ $field ];
				$value = self::maybe_convert_html_tag( $value );

				return $value;
			}
		}

		/** Field found in customizer fields default */
		if ( is_array( self::$customizer_fields_default ) && isset( self::$customizer_fields_default[ $field ] ) ) {
			$value = self::$customizer_fields_default[ $field ];
			$value = self::maybe_convert_html_tag( $value );

			return $value;
		}

		return '';
	}

	public static function maybe_convert_html_tag( $val ) {
		//      new WP_Customize_Manager();
		if ( false === is_string( $val ) ) {
			return $val;
		}
		$val = str_replace( '&lt;', '<', $val );
		$val = str_replace( '&gt;', '>', $val );

		return $val;
	}

	public static function get_product_global_quantity_bump( $return = false ) {
		if ( $return ) {
			ob_start();
		}
		$switcher_settings = WFACP_Common::get_product_switcher_data( WFACP_Common::get_id() );
		$currentTemplate   = isset( $switcher_settings['settings']['product_switcher_template'] ) ? $switcher_settings['settings']['product_switcher_template'] : 'default';
		$template_path     = WFACP_TEMPLATE_COMMON . '/product-switcher/' . $currentTemplate . '/product_quantity_bump.php';
		if ( ! file_exists( $template_path ) ) {
			$template_path = WFACP_TEMPLATE_COMMON . '/product-switcher/default/product_quantity_bump.php';
		}
		include_once $template_path;
		if ( $return ) {
			return ob_get_clean();
		}
	}

	public static function get_product_switcher_table( $return = false ) {
		if ( WFACP_Core()->public->is_checkout_override() ) {
			$quantity = self::get_product_global_quantity_bump( $return );
			if ( $return ) {
				return $quantity;
			}
		}
		if ( $return ) {
			ob_start();
		}

		$switcher_settings = WFACP_Common::get_product_switcher_data( WFACP_Common::get_id() );

		$currentTemplate = isset( $switcher_settings['settings']['product_switcher_template'] ) ? $switcher_settings['settings']['product_switcher_template'] : 'default';

		$template_path = WFACP_TEMPLATE_COMMON . '/product-switcher/' . $currentTemplate . '/product-switcher.php';
		if ( ! file_exists( $template_path ) ) {
			$template_path = WFACP_TEMPLATE_COMMON . '/product-switcher/default/product-switcher.php';
		}

		include $template_path;

		if ( $return ) {
			return ob_get_clean();
		}
	}

	public static function get_product_switcher_row( $product_data, $item_key, $type, $switcher_settings, $return = false ) {
		$cart_item_key = '';
		$cart_item     = null;


		if ( isset( $product_data['is_added_cart'] ) ) {
			$cart_item_key = $product_data['is_added_cart'];
			$cart_item     = WC()->cart->get_cart_item( $cart_item_key );
			if ( empty( $cart_item ) ) {
				$cart_item_key              = '';
				$product_data['is_checked'] = '';
			}
		} else {
			$search_type = false;
			if ( 'hidden' == $type ) {
				// find cart items present in removed cart items
				$search_type = true;
			}
			$cart_data = WFACP_Common::get_cart_item_key( $item_key, $search_type );
			if ( ! is_null( $cart_data ) ) {
				$cart_item_key = $cart_data[0];
				$cart_item     = $cart_data[1];
			}
		}

		if ( ! is_null( $cart_item ) && isset( $cart_item['data'] ) ) {
			$pro = $cart_item['data'];
			$pro = WFACP_Common::set_product_price( $pro, $product_data );
		} else {
			$pro = null;
			if ( ! wp_doing_ajax() ) {
				// get instance of product when product is added to cart
				$pro = isset( WFACP_Core()->public->added_products[ $item_key ] ) ? WFACP_Core()->public->added_products[ $item_key ] : null;
			}

			if ( ! $pro instanceof WC_Product ) {
				// if product is not in cart then we create product object product product_data variable
				//To make sure all product comes up in  product switcher with add to carted product
				$pro = self::wc_get_product( $product_data['id'], $product_data['item_key'] );
			}
			if ( isset( $product_data['variable'] ) ) {

				$variation_id = absint( $product_data['default_variation'] );
				$pro          = self::wc_get_product( $variation_id, $product_data['item_key'] . '_' . $variation_id );
			}
			$pro = WFACP_Common::set_product_price( $pro, $product_data );
		}

		// at this stage we not fount any product insance then we return and not printing product in switcher UI
		if ( ! $pro instanceof WC_Product ) {
			return;
		}

		if ( ! is_null( $cart_item ) ) {
			$qty = absint( ( isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 1 ) / ( isset( $product_data['org_quantity'] ) ? $product_data['org_quantity'] : 1 ) );
		} else {
			$qty = 1;
		}

		$price_data = apply_filters( 'wfacp_product_switcher_price_data', [], $pro );
		if ( is_string( $cart_item_key ) && '' !== $cart_item_key && isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
			// calculate price data for cart item
			$price_data = WFACP_Common::get_cart_product_price_data( $pro, $cart_item, $qty );
		} else {
			if ( empty( $price_data ) ) {
				$price_data['regular_org'] = $pro->get_regular_price( 'edit' );
				if ( 0 == absint( $price_data['regular_org'] ) ) {
					$price_data['regular_org'] = $pro->get_regular_price();

				}
				$price_data['price'] = $pro->get_price( 'edit' );
			}
			// calculate price data for normal product
			$price_data = WFACP_Common::get_product_price_data( $pro, $price_data );
		}
		$price_data['quantity'] = ( $qty * $product_data['org_quantity'] );
		ob_start();
		$currentTemplate = isset( $switcher_settings['settings']['product_switcher_template'] ) ? $switcher_settings['settings']['product_switcher_template'] : 'default';
		$template_path   = WFACP_TEMPLATE_COMMON . '/product-switcher/' . $currentTemplate . '/product-switcher-row.php';
		if ( ! file_exists( $template_path ) ) {
			$template_path = WFACP_TEMPLATE_COMMON . '/product-switcher/default/product-switcher-row.php';
		}
		include $template_path;
		$row = ob_get_clean();
		if ( $return ) {
			return $row;
		}
		echo $row;

	}

	/**
	 * Find cart key using product item key
	 *
	 * @param $product_key
	 *
	 * @return array|null
	 */
	public static function get_cart_item_key( $product_key, $from_removed_cart = false ) {
		$cart = WC()->cart->get_cart_contents();
		if ( count( $cart ) == 0 ) {
			return null;
		}

		foreach ( $cart as $item_key => $item_data ) {
			if ( isset( $item_data['_wfacp_product_key'] ) && $product_key === $item_data['_wfacp_product_key'] ) {

				return [ $item_key, $item_data ];
			}
		}

		if ( $from_removed_cart ) {
			$cart = WC()->cart->removed_cart_contents;
			foreach ( $cart as $item_key => $item_data ) {
				if ( isset( $item_data['_wfacp_product_key'] ) && $product_key === $item_data['_wfacp_product_key'] ) {

					return [ $item_key, $item_data ];
				}
			}
		}

		return null;
	}

	public static function get_cart_item_from_removed_items( $product_key ) {
		$cart = WC()->cart->removed_cart_contents;

		foreach ( $cart as $item_key => $item_data ) {
			if ( isset( $item_data['_wfacp_product_key'] ) && $product_key === $item_data['_wfacp_product_key'] ) {

				return [ $item_key, $item_data ];
			}
		}
	}

	/**
	 * Set Product price like regular, sale price on basis of discount
	 *
	 * @param $pro WC_Product
	 * @param $product
	 */
	public static function set_product_price( $pro, $data ) {
		if ( ! $pro instanceof WC_Product ) {
			return null;
		}
		$qty = isset( $data['org_quantity'] ) ? absint( $data['org_quantity'] ) : 1;

		$raw_data = $pro->get_data();

		$raw_data        = apply_filters( 'wfacp_product_raw_data', $raw_data, $pro );
		$discount_type   = trim( $data['discount_type'] );
		$regular_price   = floatval( apply_filters( 'wfacp_discount_regular_price_data', $raw_data['regular_price'] ) );
		$price           = floatval( apply_filters( 'wfacp_discount_price_data', $raw_data['price'] ) );
		$discount_amount = floatval( apply_filters( 'wfacp_discount_amount_data', $data['discount_amount'], $discount_type ) );

		$discount_data = [
			'wfacp_product_rp'      => $regular_price * $qty,
			'wfacp_product_p'       => $price * $qty,
			'wfacp_discount_amount' => $discount_amount,
			'wfacp_discount_type'   => $discount_type,
		];
		if ( 'fixed_discount_sale' == $discount_type || 'fixed_discount_reg' == $discount_type ) {
			$discount_data['wfacp_discount_amount'] = $discount_amount * $qty;
		}

		WFACP_Common::pc( 'Product Switcher Discount apply started' );
		WFACP_Common::pc( $discount_data );
		$new_price = self::calculate_discount( $discount_data );
		WFACP_Common::pc( 'Calculated discount is ' . $new_price );

		if ( ! is_null( $new_price ) ) {
			$pro->set_regular_price( $regular_price * $qty );
			$pro->set_price( $new_price );
			$pro->set_sale_price( $new_price );
		}

		return $pro;
	}

	/**
	 * Calculate product discount using options meta
	 * [wfacp_options] => Array
	 * (
	 * [discount_type] => percentage
	 * [discount_amount] => 5
	 * [discount_price] => 0
	 * [quantity] => 1
	 * [id] => 121
	 * [parent_product_id] => 117
	 * [type] => variation
	 * )
	 *
	 * @param $product_price
	 * @param $options
	 *
	 * @return float;
	 */
	public static function calculate_discount( $options ) {
		if ( ! isset( $options['wfacp_product_rp'] ) ) {
			return null;
		}

		$discount_type = $options['wfacp_discount_type'];
		$reg_price     = floatval( $options['wfacp_product_rp'] );
		$price         = floatval( $options['wfacp_product_p'] );
		$value         = floatval( $options['wfacp_discount_amount'] );
		switch ( $discount_type ) {
			case 'fixed_discount_reg':
				if ( 0 == $value ) {
					$discounted_price = $reg_price;
					break;
				}
				$discounted_price = $reg_price - ( $value );
				break;
			case 'fixed_discount_sale':
				if ( 0 == $value ) {
					$discounted_price = $price;
					break;
				}
				$discounted_price = $price - ( $value );

				break;
			case 'percent_discount_reg':
				if ( 0 == $value ) {
					$discounted_price = $reg_price;
					break;
				}
				$discounted_price = ( $value > 0 ) ? $reg_price - ( ( $value / 100 ) * $reg_price ) : $reg_price;
				break;
			case 'percent_discount_sale':
				if ( 0 == $value ) {
					$discounted_price = $price;
					break;
				}
				$discounted_price = ( $value > 0 ) ? $price - ( ( $value / 100 ) * $price ) : $price;
				break;
			case 'flat_price':
				$discounted_price = ( $value > 0 ) ? ( $value ) : $price;
				break;
			default:
				$discounted_price = $price;
				break;
		}
		if ( $discounted_price < 0 ) {
			$discounted_price = 0;
		}

		return $discounted_price;
	}

	public static function wc_get_product( $product_id, $unique_key ) {

		if ( isset( self::$product_data[ $unique_key ][ $product_id ] ) ) {
			return self::$product_data[ $unique_key ][ $product_id ];
		}
		self::$product_data[ $unique_key ][ $product_id ] = wc_get_product( $product_id );

		return self::$product_data[ $unique_key ][ $product_id ];
	}

	/**
	 * get global price data after tax calculation based
	 *
	 * @param $pro
	 * @param $cart_item
	 * @param int $qty
	 *
	 * @return array
	 */
	public static function get_cart_product_price_data( $pro, $cart_item, $qty = 1 ) {
		$price_data = [];
		if ( $pro instanceof WC_Product ) {
			$display_type = get_option( 'woocommerce_tax_display_cart' );
			if ( 'incl' == $display_type ) {
				$price_data['regular_org'] = wc_get_price_including_tax( $pro, [
					'qty'   => $qty,
					'price' => $pro->get_regular_price(),
				] );
				$price_data['price']       = round( $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'], wc_get_price_decimals() );
			} else {
				$price_data['regular_org'] = wc_get_price_excluding_tax( $pro, [
					'qty'   => $qty,
					'price' => $pro->get_regular_price(),
				] );
				$price_data['price']       = round( $cart_item['line_subtotal'], wc_get_price_decimals() );
			}

			$price_data['quantity'] = $qty;
		}

		return $price_data;
	}

	/**
	 * get global price data after tax calculation based
	 *
	 * @param $pro
	 * @param $cart_item
	 * @param int $qty
	 *
	 * @return array
	 */
	public static function get_product_price_data( $pro, $price_data, $qty = 1 ) {
		if ( $pro instanceof WC_Product ) {
			$display_type = get_option( 'woocommerce_tax_display_cart' );
			if ( 'incl' == $display_type ) {

				$price_data['regular_org'] = wc_get_price_including_tax( $pro, [
					'qty'   => $qty,
					'price' => $price_data['regular_org'],
				] );
				$price_data['price']       = wc_get_price_including_tax( $pro, [
					'qty'   => $qty,
					'price' => $price_data['price'],
				] );

			} else {
				$price_data['regular_org'] = wc_get_price_excluding_tax( $pro, [
					'qty'   => $qty,
					'price' => $price_data['regular_org'],
				] );
				$price_data['price']       = wc_get_price_excluding_tax( $pro, [
					'qty'   => $qty,
					'price' => $price_data['price'],
				] );
			}

			$price_data['quantity'] = $qty;
		}

		return $price_data;
	}

	public static function get_product_switcher_row_description( $data, $product_obj, $switcher_settings, $return = false ) {
		if ( $return ) {
			ob_start();
		}
		$currentTemplate = isset( $switcher_settings['settings']['product_switcher_template'] ) ? $switcher_settings['settings']['product_switcher_template'] : 'default';
		$template_path   = WFACP_TEMPLATE_COMMON . '/product-switcher/' . $currentTemplate . '/product-switcher-description.php';
		if ( ! file_exists( $template_path ) ) {
			$template_path = WFACP_TEMPLATE_COMMON . '/product-switcher/default/product-switcher-description.php';
		}

		include $template_path;
		if ( $return ) {
			return ob_get_clean();
		}
	}

	public static function process_wfacp_html( $field, $key, $args, $value ) {
		if ( is_admin() ) {
			return;
		}
		WC()->session->set( 'wfacp_' . $key . '_field', $args );
		if ( apply_filters( 'wfacp_html_fields_' . $key, true, $field, $key, $args, $value ) ) {


			if ( 'order_summary' === $key ) {
				WC()->session->set( 'wfacp_order_summary_' . self::get_id(), $args );
				include WFACP_TEMPLATE_COMMON . '/order-summary.php';
			} elseif ( 'shipping_calculator' === $key ) {
				WC()->session->set( 'shipping_calculator_' . self::get_id(), $args );
				include_once WFACP_TEMPLATE_COMMON . '/shipping-options.php';
			} elseif ( 'order_total' === $key ) {
				WC()->session->set( 'wfacp_order_total_' . self::get_id(), $args );
				self::get_order_total_fields();
			} elseif ( 'order_coupon' === $key ) {
				WC()->session->set( 'order_coupon_' . self::get_id(), $args );
				include WFACP_TEMPLATE_COMMON . '/order-coupon.php';
			}
		} else {

			do_action( 'process_wfacp_html', $field, $key, $args, $value );
		}
	}

	public static function get_order_total_fields( $return = false ) {
		if ( $return ) {
			ob_start();
		}
		include_once WFACP_TEMPLATE_COMMON . '/order-total.php';
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Get checkout page default settings
	 *
	 * @param $page_id
	 *
	 * @return array|mixed|string
	 */
	public static function get_page_settings( $page_id ) {

		$data = self::get_post_meta_data( $page_id, '_wfacp_page_settings' );

		$default_data = [
			'coupons'                             => '',
			'enable_coupon'                       => 'false',
			'disable_coupon'                      => 'false',
			'hide_quantity_switcher'              => 'false',
			'enable_delete_item'                  => false,
			'hide_product_image'                  => true,
			'is_hide_additional_information'      => 'false',
			'additional_information_title'        => self::get_default_additional_information_title(),
			'hide_quick_view'                     => 'false',
			'hide_you_save'                       => 'true',
			'close_after_x_purchase'              => 'false',
			'total_purchased_allowed'             => '',
			'close_checkout_after_date'           => 'false',
			'close_checkout_on'                   => '',
			'close_checkout_redirect_url'         => '',
			'total_purchased_redirect_url'        => '',
			'hide_best_value'                     => false,
			'best_value_product'                  => '',
			'best_value_text'                     => 'Best Value',
			'best_value_position'                 => 'below',
			'enable_custom_name_in_order_summary' => 'false',
			'show_on_next_step'                   => [ 'single_step' => new stdClass(), 'two_step' => new stdClass(), 'third_step' => new stdClass() ]
		];

		if ( empty( $data ) ) {

			return $default_data;
		}

		if ( is_array( $data ) && count( $data ) > 0 ) {
			foreach ( $default_data as $key => $val ) {
				if ( ! isset( $data[ $key ] ) ) {
					$data[ $key ] = $val;
				}
			}
		}

		return apply_filters( 'wfacp_page_settings', $data );
	}

	/**
	 * @param $product WC_Product_Variable;
	 */
	public static function get_default_variation( $product ) {

		if ( $product instanceof WC_Product_Variable ) {
			$var_data = $product->get_data();

			if ( isset( $var_data['default_attributes'] ) && count( $var_data['default_attributes'] ) > 0 ) {
				$attributes = $var_data['default_attributes'];
				$matched_id = self::find_matching_product_variation( $product, $attributes );

				if ( ! is_null( $matched_id ) && $matched_id > 0 ) {
					return self::get_first_variation( $product, $matched_id );
				}

				return self::get_first_variation( $product );

			} else {
				return self::get_first_variation( $product );
			}
		}

		return [];
	}

	/**
	 * Find matching product variation
	 *
	 * @param WC_Product $product
	 * @param array $attributes
	 *
	 * @return int Matching variation ID or 0.
	 */
	public static function find_matching_product_variation( $product, $attributes ) {

		foreach ( $attributes as $key => $value ) {
			if ( strpos( $key, 'attribute_' ) === 0 ) {
				continue;
			}

			unset( $attributes[ $key ] );
			$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
		}

		if ( class_exists( 'WC_Data_Store' ) ) {

			$data_store = WC_Data_Store::load( 'product' );

			return $data_store->find_matching_product_variation( $product, $attributes );

		} else {

			return $product->get_matching_variation( $attributes );

		}

		return null;
	}

	/**
	 * get first available variation
	 *
	 * @param $product WC_Product_Variable
	 */
	public static function get_first_variation( $product, $vars_id = 0 ) {
		if ( $product instanceof WC_Product_Variable ) {
			$vars = $product->get_available_variations();

			if ( count( $vars ) == 0 ) {
				return [];
			}

			foreach ( $vars as $v ) {
				if ( $v['variation_id'] == $vars_id ) {
					if ( wc_string_to_bool( $v['is_in_stock'] ) && $v['is_purchasable'] ) {
						return $v;
					}
				}

			}

			return $vars[0];
		}

		return [];
	}

	/**
	 * Check stock of the product
	 *
	 * @param $product_obj
	 * @param $new_qty
	 *
	 * @return bool
	 */
	public static function check_manage_stock( $product_obj, $new_qty ) {

		if ( ! $product_obj instanceof WC_Product ) {

			return false;
		}
		if ( $new_qty < 1 ) {
			return false;
		}

		// when stock management is on in product
		if ( true == $product_obj->managing_stock() ) {

			$available_qty = $product_obj->get_stock_quantity();
			if ( $available_qty < $new_qty ) {

				if ( ! in_array( $product_obj->get_backorders(), [ 'yes', 'notify' ] ) ) {
					return false;
				}
			}
		} else {
			// for non stock managerment
			return $product_obj->is_in_stock();
		}

		return true;
	}

	/**
	 * get pixel initiated pixel checkout data
	 * @return array
	 */

	public static function pixel_InitiateCheckout() {
		$output = new stdClass();
		if ( function_exists( 'WC' ) ) {
			$subtotal = WC()->cart->get_subtotal();
			$contents = WC()->cart->get_cart_contents();
			if ( count( $contents ) > 0 ) {
				$output = [];
				foreach ( $contents as $item_key => $item ) {


					if ( $item['data'] instanceof WC_Product ) {
						$item_id                 = (string) $item['data']->get_id();
						$output['content_ids'][] = $item_id;
						$output['contents'][]    = [ 'id' => $item_id, 'item_price' => $item['line_subtotal'], 'quantity' => $item['quantity'] ];
					}
				}
				$output['currency']     = get_woocommerce_currency();
				$output['value']        = $subtotal;
				$output['content_name'] = __( 'Checkout', 'woofunnels-aero-checkout' );
				$output['num_ids']      = count( $output['content_ids'] );
				$output['num_items']    = count( $output['content_ids'] );
				$output['content_type'] = 'product';
				$output['plugin']       = 'AeroCheckout';
				$output['subtotal']     = $subtotal;
				$output['user_roles']   = self::get_current_user_role();
			}
		}


		$final['pixel'] = $output;

		return apply_filters( 'wfacp_checkout_data', $final, WC()->cart );
	}

	/**
	 * @param $product_obj WC_Product
	 * @param $cart_item []
	 */
	public static function get_pixel_item( $product_obj, $cart_item ) {

		$item_id                  = (string) $product_obj->get_id();
		$item_added_data['pixel'] = [
			'value'        => $cart_item['line_subtotal'],
			'content_name' => $product_obj->get_title(),
			'content_type' => 'product',
			'currency'     => get_woocommerce_currency(),
			'content_ids'  => [ $item_id ],
			'plugin'       => 'AeroCheckout',
			'contents'     => [ [ 'id' => $item_id, 'item_price' => $cart_item['line_subtotal'], 'quantity' => $cart_item['quantity'] ] ],
			'user_roles'   => self::get_current_user_role()
		];


		return apply_filters( 'wfacp_item_added_to_cart', $item_added_data, $product_obj, $cart_item );
	}

	public static function pixel_add_to_cart_product() {
		$cart_data = [];
		if ( function_exists( 'WC' ) ) {
			$contents = WC()->cart->get_cart_contents();
			$args     = array(
				'ex_tax_label'       => false,
				'currency'           => '',
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals'           => wc_get_price_decimals(),
				'price_format'       => get_woocommerce_price_format(),
			);
			if ( count( $contents ) > 0 ) {
				foreach ( $contents as $item_key => $item ) {
					$temp                   = self::get_pixel_item( $item['data'], $item );
					$cart_data[ $item_key ] = $temp['pixel'];
				}
			}
		}

		return $cart_data;
	}

	public static function get_current_user_role() {
		if ( is_user_logged_in() ) {
			if ( is_super_admin() ) {
				return 'administrator';
			} else {
				return 'customer';
			}

		}

		return 'guest';
	}

	/**
	 * Return WFACP Post id if user overide default checkout from global settings
	 */
	public static function get_checkout_page_id() {
		$checkout_page_id = 0;
		$global_settings  = get_option( '_wfacp_global_settings', [] );

		if ( isset( $global_settings['override_checkout_page_id'] ) ) {
			$checkout_page_id = absint( $global_settings['override_checkout_page_id'] );
		}

		return apply_filters( 'wfacp_global_checkout_page_id', $checkout_page_id );
	}

	/**
	 * Save all publish checkout pages into transient
	 */
	public static function save_publish_checkout_pages_in_transient( $force = true, $count = '-1' ) {
		$output = [];
		$data   = self::get_saved_pages();

		if ( is_array( $data ) && count( $data ) > 0 ) {

			$output[] = [
				'id'   => 0,
				'name' => __( 'Default WooCommerce Checkout Page', 'woofunnels-aero-checkout' ),
				'type' => 'default',
			];
			foreach ( $data as $v ) {
				$design_type = self::get_page_design( $v['ID'] );
				if ( 'pre_built' == $design_type['selected_type'] ) {
					$output[] = [
						'id'   => $v['ID'],
						'name' => $v['post_title'],
						'type' => 'wfacp',
					];
				}
			}
		}

		$output = apply_filters( 'wfacp_checkout_post_list', $output );
		if ( count( $output ) == 0 ) {
			return [];
		}

		/**
		 * @var $Woofunnel_cache_obj WooFunnels_Cache
		 */
		$Woofunnel_cache_obj     = WooFunnels_Cache::get_instance();
		$Woofunnel_transient_obj = WooFunnels_Transient::get_instance();

		$cache_key = 'wfacp_publish_posts';
		/** $force = true */
		if ( true === $force ) {
			$Woofunnel_transient_obj->set_transient( $cache_key, $output, DAY_IN_SECONDS, WFACP_SLUG );
			$Woofunnel_cache_obj->set_cache( $cache_key, $output, 'free-gift' );

			return $output;
		}

		$cache_data = $Woofunnel_cache_obj->get_cache( $cache_key, WFACP_SLUG );
		if ( false !== $cache_data ) {
			$wfacp_publish_posts = $cache_data;
		} else {
			$transient_data = $Woofunnel_transient_obj->get_transient( $cache_key, WFACP_SLUG );

			if ( false !== $transient_data ) {
				$wfacp_publish_posts = $transient_data;
			} else {

				$Woofunnel_transient_obj->set_transient( $cache_key, $output, DAY_IN_SECONDS, WFACP_SLUG );
			}
			$Woofunnel_cache_obj->set_cache( $cache_key, $output, WFACP_SLUG );
		}

		return $wfacp_publish_posts;
	}

	public static function get_page_design( $page_id ) {
		$design_data = self::get_post_meta_data( $page_id, '_wfacp_selected_design' );

		if ( '' == $design_data ) {
			$design_data = self::default_design_data();
		}

		return $design_data;
	}

	public static function get_post_table_data( $post_status = 'any', $post_count = 10 ) {

		$args = [
			'post_type'   => self::get_post_type_slug(),
			'post_status' => $post_status,
			'orderby'     => "ID",
		];

		if ( isset( $_REQUEST['paged'] ) ) {
			$args['paged'] = absint( $_REQUEST['paged'] );
		}

		if ( $post_status == 'any' ) {
			if ( isset( $_REQUEST['s'] ) ) {
				$searchText = $_REQUEST['s'];
				if ( is_numeric( $searchText ) ) {
					$args['p'] = $searchText;
				} else {
					$args['s'] = $searchText;
				}

			}
			if ( isset( $_REQUEST['status'] ) ) {

				if ( $_REQUEST['status'] == 'active' ) {
					$args['post_status'] = 'publish';
				}
				if ( $_REQUEST['status'] == 'inactive' ) {
					$args['post_status'] = 'draft';
				}
			}
		}
		if ( ! empty( $post_count ) ) {
			$args['posts_per_page'] = $post_count;
		}

		$data  = [
			'items'       => [],
			'found_posts' => 0,
		];
		$query = new WP_Query( $args );


		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				global $post;

				$temp_data = (array) $post;

				$permalink       = get_the_permalink( $post->ID );
				$delete_url      = add_query_arg( [
					'wfacp_delete' => 'true',
					'wfacp_id'     => $temp_data['ID'],
				], admin_url( 'admin.php?page=wfacp' ) );
				$wfacp_duplicate = add_query_arg( [
					'wfacp_duplicate' => 'true',
					'wfacp_id'        => $temp_data['ID'],
				], admin_url( 'admin.php?page=wfacp' ) );

				$temp_data['row_actions'] = [
					'view'      => [
						'action' => 'view',
						'class'  => '',
						'attrs'  => 'target="_blank"',
						'text'   => __( 'View', 'woofunnels-aero-checkout' ),
						'link'   => $permalink,

					],
					'duplicate' => [
						'action' => 'wfacp_duplicate',
						'attrs'  => '',
						'class'  => 'wfacp_duplicate_checkout_page',
						'text'   => __( 'Duplicate', 'woofunnels-aero-checkout' ),
						'link'   => $wfacp_duplicate,
					],
					'delete'    => [
						'action' => 'delete',
						'attrs'  => '',
						'class'  => 'wfacp_delete_checkout_page',
						'text'   => __( 'Delete Permanently', 'woofunnels-aero-checkout' ),
						'link'   => $delete_url,
					],

				];

				$data['items'][] = $temp_data;
			}
		}
		$data['found_posts'] = $query->found_posts;


		return $data;
	}

	public static function get_variable_product_type() {
		return [ 'variable', 'variable-subscription' ];
	}

	public static function get_variation_product_type() {
		return [ 'variation', 'subscription_variation' ];
	}

	public static function get_subscription_product_type() {

		if ( ! class_exists( 'WC_Subscriptions_Product' ) ) {
			return [];
		}

		return [ 'variable-subscription', 'subscription', 'subscription_variation' ];
	}

	/**
	 * Copy data from old checkout page to new checkout page
	 *
	 * @param $post_id
	 *
	 * @return int|null|WP_Error
	 */
	public static function make_duplicate( $post_id ) {
		if ( $post_id > 0 ) {
			$post = get_post( $post_id );
			if ( ! is_null( $post ) && $post->post_type === self::get_post_type_slug() ) {

				$args        = [
					'post_title'   => $post->post_title . ' - ' . __( 'Copy', 'woofunnels-aero-checkout' ),
					'post_content' => $post->post_content,
					'post_name'    => sanitize_title( $post->post_title . ' - ' . __( 'Copy', 'woofunnels-aero-checkout' ) ),
					'post_type'    => self::get_post_type_slug(),
					'post_status'  => 'draft',
				];
				$new_post_id = wp_insert_post( $args );
				if ( ! is_wp_error( $new_post_id ) ) {
					self::get_duplicate_data( $new_post_id, $post_id );
					update_post_meta( $new_post_id, '_wfacp_version', WFACP_VERSION );

					return $new_post_id;
				}
			}
		}

		return null;
	}

	public static function get_duplicate_data( $new_post_id, $post_id ) {

		update_post_meta( $new_post_id, '_wfacp_selected_products', get_post_meta( $post_id, '_wfacp_selected_products', true ) );
		update_post_meta( $new_post_id, '_wfacp_selected_products_settings', get_post_meta( $post_id, '_wfacp_selected_products_settings', true ) );
		update_post_meta( $new_post_id, '_wfacp_selected_design', get_post_meta( $post_id, '_wfacp_selected_design', true ) );
		update_post_meta( $new_post_id, '_wfacp_page_layout', get_post_meta( $post_id, '_wfacp_page_layout', true ) );
		update_post_meta( $new_post_id, '_wfacp_page_settings', get_post_meta( $post_id, '_wfacp_page_settings', true ) );
		update_post_meta( $new_post_id, '_wfacp_page_custom_field', get_post_meta( $post_id, '_wfacp_page_custom_field', true ) );
		update_post_meta( $new_post_id, '_wfacp_fieldsets_data', get_post_meta( $post_id, '_wfacp_fieldsets_data', true ) );
		update_post_meta( $new_post_id, '_wfacp_checkout_fields', get_post_meta( $post_id, '_wfacp_checkout_fields', true ) );
		update_post_meta( $new_post_id, '_wfacp_product_switcher_setting', get_post_meta( $post_id, '_wfacp_product_switcher_setting', true ) );

		//copy customizer setting
		update_option( WFACP_SLUG . '_c_' . $new_post_id, get_option( WFACP_SLUG . '_c_' . $post_id, [] ) );
		do_action( 'wfacp_duplicate_pages', $new_post_id, $post_id );
	}

	public static function clean_ascii_characters( $content ) {
		if ( '' == $content ) {
			return $content;
		}

		$content = str_replace( '%', '_', $content );
		$content = str_replace( '!', '_', $content );
		$content = str_replace( '\"', '_', $content );
		$content = str_replace( '#', '_', $content );
		$content = str_replace( '$', '_', $content );
		$content = str_replace( '&', '_', $content );
		$content = str_replace( '(', '_', $content );
		$content = str_replace( ')', '_', $content );
		$content = str_replace( '(', '_', $content );
		$content = str_replace( '*', '_', $content );
		$content = str_replace( ',', '_', $content );
		$content = str_replace( '', '_', $content );
		$content = str_replace( '.', '_', $content );
		$content = str_replace( '/', '_', $content );

		return $content;
	}

	public static function wc_dropdown_variation_attribute_options( $args = array() ) {
		$args = wp_parse_args( apply_filters( 'woocommerce_wfacp_dropdown_variation_attribute_options_args', $args ), array(
			'options'          => false,
			'attribute'        => false,
			'product'          => false,
			'selected'         => false,
			'name'             => '',
			'id'               => '',
			'class'            => '',
			'show_option_none' => __( 'Choose an option', 'woocommerce' ),
		) );

		// Get selected value.
		if ( false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product ) {
			$selected_key     = 'attribute_' . sanitize_title( $args['attribute'] );
			$args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( urldecode( wp_unslash( $_REQUEST[ $selected_key ] ) ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
		}

		$options               = $args['options'];
		$product               = $args['product'];
		$attribute             = $args['attribute'];
		$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
		$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
		$class                 = $args['class'];
		$show_option_none      = (bool) $args['show_option_none'];
		$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		$html = '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
		$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

		if ( ! empty( $options ) ) {
			if ( $product && taxonomy_exists( $attribute ) ) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms( $product->get_id(), $attribute, array(
					'fields' => 'all',
				) );

				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $options, true ) ) {
						$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
					}
				}
			} else {
				foreach ( $options as $option ) {
					// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
					$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
					$html     .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
				}
			}
		}

		$html .= '</select>';

		echo apply_filters( 'woocommerce_wfacp_dropdown_variation_attribute_options_html', $html, $args ); // WPCS: XSS ok.
	}

	public static function string2hex( $string ) {
		$hex = '';
		for ( $i = 0; $i < strlen( $string ); $i ++ ) {
			$hex .= dechex( ord( $string[ $i ] ) );
		}

		return $hex;
	}

	public static function wfacp_order_custom_field( $atts ) {

		$atts = shortcode_atts( array(
			'order_id' => 0,
			'field_id' => '',
			'type'     => 'value',
		), $atts );

		$field = $atts['field_id'];
		if ( '' == $field ) {
			return '';
		}
		$order_id = $atts['field_id'];
		if ( $order_id == 0 ) {
			if ( isset( $_REQUEST['order_id'] ) && $_REQUEST['order_id'] > 0 ) {
				$order_id = absint( $_REQUEST['order_id'] );
			}
		}
		$order_id = apply_filters( 'wfacp_custom_field_order_id', $order_id );
		if ( $order_id == 0 ) {
			return '';
		}

		$meta_keys = [
			'billing_email',
			'billing_first_name',
			'billing_last_name',
			'billing_phone',
			'billing_country',
			'billing_city',
			'billing_address_1',
			'billing_address_2',
			'billing_postcode',
			'billing_company',
			'billing_state',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_phone',
			'shipping_country',
			'shipping_city',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_postcode',
			'shipping_state',

		];

		if ( $atts['type'] == 'value' ) {
			if ( in_array( $field, $meta_keys ) ) {
				$field = '_' . $field;
			}
			$metadata = get_post_meta( $order_id, $field, true );
			if ( is_string( $metadata ) ) {
				return $metadata;
			}
		} else {
			$fpos = strpos( $field, '_' );
			if ( 0 === $fpos ) {
				$field = substr( $field, 1, strlen( $field ) );

			}

			$wfacp_id = get_post_meta( $order_id, '_wfacp_post_id', true );
			if ( empty( $wfacp_id ) ) {
				return '';
			}

			$wfacp_id = absint( $wfacp_id );

			$checkout_fields = get_post_meta( $wfacp_id, '_wfacp_checkout_fields', true );
			if ( ! is_array( $checkout_fields ) || count( $checkout_fields ) == 0 ) {
				return '';
			}
			foreach ( $checkout_fields as $field_typ => $fieldset ) {
				foreach ( $fieldset as $field_key => $field_vl ) {
					$pos = strpos( $field_key, '_' );
					if ( 0 === $pos ) {
						$field_key = substr( $field_key, 1, strlen( $field_key ) );
					}
					if ( $field_key === $field ) {
						return $field_vl['label'];
					}
				}
			}
		}

		return '';
	}

	public static function get_fragments( $wfacp_id ) {

		if ( isset( $_REQUEST['post_data'] ) ) {
			$post_data = [];
			parse_str( $_REQUEST['post_data'], $post_data );
			WFACP_Common::$post_data = $post_data;
		}

		do_action( 'wfacp_get_fragments', $wfacp_id, $_REQUEST );

		// Get order review fragment
		ob_start();
		woocommerce_order_review();
		$woocommerce_order_review = ob_get_clean();

		return apply_filters( 'woocommerce_update_order_review_fragments', array(
			'.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
		) );
	}

	public static function wfob_order_bump_fragments() {

		if ( isset( $_REQUEST['wfacp_id'] ) ) {
			$wfacp_id = absint( $_REQUEST['wfacp_id'] );
			self::initializeTemplate( $wfacp_id );
		}
	}

	public static function include_notification_class( $get_global_path ) {

		require_once $get_global_path . 'includes/class-woofunnels-notifications.php';
	}

	public static function initializeTemplate( $wfacp_id ) {
		self::initTemplateLoader( $wfacp_id );
		do_action( 'wfacp_intialize_template_by_ajax', $wfacp_id );
	}


	/**
	 * Get the product row subtotal.
	 *
	 * Gets the tax etc to avoid rounding issues.
	 *
	 * When on the checkout (review order), this will get the subtotal based on the customer's tax rate rather than the base rate.
	 *
	 * @param WC_Product $product Product object.
	 * @param int $quantity Quantity being purchased.
	 *
	 * @return string formatted price
	 */
	public static function get_product_subtotal( $product, $cart_item ) {
		if ( $product->is_taxable() ) {

			if ( WC()->cart->display_prices_including_tax() ) {
				$row_price        = round( $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'], wc_get_price_decimals() );
				$product_subtotal = wc_price( $row_price );
				if ( ! wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
				$row_price        = round( $cart_item['line_subtotal'], wc_get_price_decimals() );
				$product_subtotal = wc_price( $row_price );
				if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
		} else {
			$row_price        = $cart_item['line_subtotal'];
			$product_subtotal = wc_price( $row_price );
		}

		return apply_filters( 'woocommerce_cart_product_subtotal', $product_subtotal, $product, $cart_item['quantity'], WC()->cart );

	}

	public static function date_i18n( $timestamp = '' ) {
		if ( '' == $timestamp ) {
			$timestamp = time();
		}

		return date_i18n( apply_filters( 'wfacp_date_i18n_format', get_option( 'date_format', 'M jS, Y' ) ), $timestamp );
	}

	public static function remove_menu_support( $component ) {

		$i = array_search( 'nav_menus', $component );
		if ( is_numeric( $i ) ) {
			unset( $component[ $i ] );
		}

		return $component;
	}

	public static function get_base_country() {
		$allowed_countries = WC()->countries->get_allowed_countries();
		if ( is_array( $allowed_countries ) && count( $allowed_countries ) == 1 ) {
			$country = array_keys( $allowed_countries );

			return $country[0];

		}

		if ( class_exists( 'WC_Geolocation' ) ) {
			$ip_data = WC_Geolocation::geolocate_ip();
			if ( is_array( $ip_data ) && isset( $ip_data['country'] ) && '' !== $ip_data['country'] ) {
				return trim( $ip_data['country'] );
			}
		}

		$wc_default = wc_get_base_location();
		if ( isset( $wc_default['country'] ) && '' !== $wc_default['country'] ) {
			return trim( $wc_default['country'] );
		}

		return 'US';
	}


	public static function default_shipping_placeholder_text() {
		return __( 'Enter your address to view shipping options.', 'woocommerce' );
	}

	/**
	 *
	 * @param $pro WC_Subscriptions_Product
	 * @param $price_data []
	 */
	public static function get_subscription_price( $pro, $price_data ) {

		$trial_length = WC_Subscriptions_Product::get_trial_length( $pro );
		$signup_fee   = WC_Subscriptions_Product::get_sign_up_fee( $pro );
		// Product now in free trial and with signup fee

		if ( $trial_length > 0 && $signup_fee > 0 ) {
			return $signup_fee * $price_data['quantity'];
		}
		if ( $trial_length > 0 && $signup_fee == 0 ) {
			return 0;
		} elseif ( $trial_length == 0 && $signup_fee > 0 ) {
			return $price_data['price'] + ( $signup_fee * $price_data['quantity'] );
		}

		return $price_data['price'];
	}

	/**
	 * Display proper subscription price
	 *
	 * @param $_product WC_Product
	 * @param $cart_item WC_Cart
	 * @param $cart_item_key
	 *
	 * @return string
	 */

	public static function display_subscription_price( $_product, $cart_item, $cart_item_key ) {
		if ( ! wp_doing_ajax() && $cart_item['quantity'] > 1 ) {
			$price = $_product->get_price();
			$price = $price / $cart_item['quantity'];
			if ( $price > 0 ) {
				$_product->set_price( $price );
			}
		}

		return apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
	}

	public static function get_signup_fee( $price ) {
		global $wfacp_product_switcher_quantity;
		if ( ! is_null( $wfacp_product_switcher_quantity ) && $wfacp_product_switcher_quantity > 0 ) {
			$price *= $wfacp_product_switcher_quantity;
		}

		return $price;
	}

	/**
	 * @param $pro WC_Product_Subscription
	 * @param $product_data
	 * @param $cart_item
	 * @param $cart_item_key
	 *
	 * @return string
	 */

	public static function subscription_product_string( $pro, $product_data, $cart_item, $cart_item_key ) {
		$temp_price = $pro->get_price();
		$temp_price *= ( isset( $product_data['quantity'] ) && $product_data['quantity'] > 0 ) ? absint( $product_data['quantity'] ) : 1;
		$temp_data  = [
			'price' => wc_price( $temp_price ),
		];
		global $wfacp_product_switcher_quantity;
		if ( '' !== $cart_item_key && ! isset( WC()->cart->removed_cart_contents[ $cart_item_key ] ) ) {
			$wfacp_product_switcher_quantity = $cart_item['quantity'];
		} else {
			$wfacp_product_switcher_quantity = $product_data['quantity'] * $product_data['org_quantity'];

		}
		add_filter( 'woocommerce_subscriptions_product_sign_up_fee', 'WFACP_Common::get_signup_fee' );
		$final_price = WC_Subscriptions_Product::get_price_string( $pro, $temp_data );
		remove_filter( 'woocommerce_subscriptions_product_sign_up_fee', 'WFACP_Common::get_signup_fee' );
		unset( $wfacp_product_switcher_quantity );

		return $final_price;
	}

	/**
	 * Check cart all product is boolean
	 * @return bool
	 */
	public static function is_cart_is_virtual() {
		$cart_items      = WC()->cart->get_cart_contents();
		$virtual_product = 0;
		if ( ! empty( $cart_items ) ) {
			foreach ( $cart_items as $key => $cart_item ) {
				$pro = $cart_item['data'];
				if ( $pro instanceof WC_Product && $pro->is_virtual() ) {
					$virtual_product ++;
				}
			}
		}
		if ( count( $cart_items ) == $virtual_product ) {
			return true;
		}

		return false;
	}

	/**
	 * Get coupon display total.
	 *
	 * @param string|WC_Coupon $coupon Coupon data or code.
	 */
	public static function wc_cart_totals_coupon_total( $coupon ) {
		if ( is_string( $coupon ) ) {
			$coupon = new WC_Coupon( $coupon );
		}
		$amount               = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );
		$discount_amount_html = wc_price( $amount );

		if ( $coupon->get_free_shipping() && empty( $amount ) ) {
			$discount_amount_html = __( 'Free shipping coupon', 'woocommerce' );
		}

		return $discount_amount_html;
	}


	/**
	 * Get a coupon label.
	 *
	 * @param string|WC_Coupon $coupon Coupon data or code.
	 * @param bool $echo Echo or return.
	 *
	 * @return string
	 */
	public static function wc_cart_totals_coupon_label( $coupon, $echo = false ) {
		if ( is_string( $coupon ) ) {
			$coupon = new WC_Coupon( $coupon );
		}
		$label = $coupon->get_code();
		if ( $echo ) {
			echo $label; // WPCS: XSS ok.
		} else {
			return $label;
		}
	}


	public static function get_saved_pages() {
		global $wpdb;

		$slug = self::get_post_type_slug();
		$data = $wpdb->get_results( "SELECT `ID`, `post_title`, `post_type` FROM `{$wpdb->prefix}posts` WHERE `post_type` = '{$slug}' AND `post_title` != '' AND `post_status` = 'publish' ORDER BY `post_title` ASC", ARRAY_A );

		return $data;
	}


	public static function get_default_you_save_text() {
		return apply_filters( 'wfacp_default_you_text', __( 'Buy {{quantity}} and Save {{saving_value}} ({{saving_percentage}})', 'woofunnels-aero-checkout' ) );
	}


	public static function get_default_additional_information_title() {
		return __( "WHAT'S INCLUDED IN YOUR PLAN?", 'woofunnels-aero-checkout' );
	}


	public static function wfacp_product_switcher_product( $temp_data, $product_key ) {

		$version = self::get_checkout_page_version();
		if ( version_compare( $version, WFACP_VERSION, '<=' ) ) {

			if ( '' == $temp_data['whats_included'] ) {
				$page_design       = self::get_page_design( WFACP_Common::get_id() );
				$selected_template = $page_design['selected'];
				$template_slug     = '';
				if ( $selected_template != 'embed_forms_1' ) {
					$selected_template_type = $page_design['selected_type'];
					$template               = WFACP_Core()->template_loader->get_single_template( $selected_template, $selected_template_type );
					if ( isset( $template['slug'] ) ) {
						$template_slug = $template['slug'];
					}
				} else {
					// legacy issue
					$template_slug = 'embed_forms_2';
				}

				if ( $template_slug != '' ) {
					$heading_key          = "wfacp_form_product_switcher_section_{$template_slug}_{$product_key}_heading";
					$description_key      = "wfacp_form_product_switcher_section_{$template_slug}_{$product_key}_description";
					$text                 = self::get_option( $description_key );
					$customizer_save_data = self::$customizer_key_data;
					if ( isset( $customizer_save_data[ $heading_key ] ) ) {
						$temp_data['title'] = $customizer_save_data[ $heading_key ];
					}

					if ( isset( $customizer_save_data[ $description_key ] ) ) {
						$temp_data['whats_included'] = $text;
					}
				}
				$temp_data['whats_included'] = isset( $temp_data['whats_included'] ) ? trim( $temp_data['whats_included'] ) : '';
			}
		}

		return $temp_data;
	}

	public static function wfacp_get_product_switcher_data( $data ) {

		$version = self::get_checkout_page_version();
		if ( self::get_id() == 0 ) {
			return $data;
		}
		if ( version_compare( $version, WFACP_VERSION, '>=' ) ) {

			return $data;
		}
		if ( isset( $data['settings']['setting_migrate'] ) ) {

			return $data;
		}
		$wfacp_id = self::get_id();
		//$logger   = wc_get_logger();
		//$context  = [ 'source' => 'woofunnels-aero-checkout' ];
		//$logger->debug( "-----Page id is =>" . $wfacp_id . " --------\n", $context );
		//$logger->debug( "-----update page setting in " . __FUNCTION__ . "--------\n", $context );
		//$logger->debug( "-----Before data------ \n", $context );
		//$logger->debug( print_r( $data, true ), $context );

		$page_design       = self::get_page_design( $wfacp_id );
		$selected_template = $page_design['selected'];
		if ( $selected_template != 'embed_forms_1' ) {
			$selected_template_type = $page_design['selected_type'];
			$template               = WFACP_Core()->template_loader->get_single_template( $selected_template, $selected_template_type );
			$template_slug          = '';
			if ( isset( $template['slug'] ) ) {
				$template_slug = $template['slug'];
			}
		} else {
			// legacy issue
			$template_slug = 'embed_forms_2';
		}

		$best_value_product   = self::get_option( 'wfacp_form_section_best_value_product' );
		$best_value_text      = self::get_option( 'wfacp_form_section_best_value_product' );
		$customizer_save_data = self::$customizer_key_data;

		if ( $template_slug != '' ) {
			$hide_section                                       = "wfacp_form_product_switcher_section_{$template_slug}_hide_section";
			$hide_section_vl                                    = self::get_option( $hide_section );
			$data['settings']['is_hide_additional_information'] = wc_string_to_bool( $hide_section_vl );
			if ( isset( $customizer_save_data['wfacp_form_product_switcher_section_section_heading'] ) ) {
				$data['settings']['additional_information_title'] = $customizer_save_data['wfacp_form_product_switcher_section_section_heading'];
			} else {
				$data['settings']['additional_information_title'] = self::get_default_additional_information_title();
			}
		}

		if ( false == wc_string_to_bool( $data['settings']['hide_best_value'] ) ) {
			if ( 'selected' == $best_value_product ) {
				$data['settings']['hide_best_value'] = false;
			}
		}
		if ( empty( $data['settings']['best_value_product'] ) && $best_value_product !== 'selected' ) {
			$data['settings']['best_value_product'] = $best_value_product;
		}
		if ( empty( $data['settings']['best_value_text'] ) ) {
			$data['settings']['best_value_text'] = $best_value_text;
		}

		$data['settings']['setting_migrate'] = WFACP_VERSION;
		$settings                            = self::get_page_settings( $wfacp_id );
		$merge                               = wp_parse_args( $data['settings'], $settings );

		//$logger->debug( "-----After data------\n", $context );
		//$logger->debug( print_r( $merge, true ), $context );

		self::update_page_settings( $wfacp_id, $merge );


		update_post_meta( $wfacp_id, '_wfacp_product_switcher_setting', $data );
		update_post_meta( $wfacp_id, '_wfacp_version', WFACP_VERSION );

		$wfacp_transient_obj = WooFunnels_Transient::get_instance();
		$meta_key            = 'wfacp_post_meta' . absint( $wfacp_id );
		$wfacp_transient_obj->delete_transient( $meta_key, WFACP_SLUG );

		//$logger->debug( "----- update page setting in " . __FUNCTION__ . "-------- Ended----\n", $context );

		return $data;
	}

	public static function get_checkout_page_version() {
		$version = self::get_post_meta_data( self::get_id(), '_wfacp_version', true );
		if ( '' == $version ) {
			$version = '1.0.0';
		}

		return $version;
	}

	public static function process_wfacp_wysiwyg( $field, $key, $args, $value ) {
		if ( '' == $args['default'] ) {
			return $field;
		}
		$args['class'][] = 'wfacp_custom_field_wfacp_wysiwyg';
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<div class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</div>';
		$container_class = esc_attr( implode( ' ', $args['class'] ) );
		$container_id    = esc_attr( $args['id'] ) . '_field';
		$field           = sprintf( $field_container, $container_class, $container_id, apply_filters( 'wfacp_the_content', $args['default'] ) );

		if ( false !== strpos( $field, '<form' ) ) {
			if ( is_super_admin() ) {
				return sprintf( '<p class="form-row form-row-wide wfacp-form-control-wrapper wfacp_error" style="color:red">%s</p>', __( 'Unable to execute a shortcode as it contains a form inside.', 'woofunnels-aero-checkout' ) );
			} else {
				return '';
			}
		}

		return $field;
	}

	public static function get_class_path( $class = 'WFACP_Core' ) {
		$reflector = new ReflectionClass( $class );
		$fn        = $reflector->getFileName();

		return dirname( $fn );
	}


	public static function woocommerce_locate_template( $template ) {
		$wfacp_dir = strpos( $template, 'wfacp/checkout/cart-shipping.php' );
		if ( false !== $wfacp_dir ) {
			return WFACP_TEMPLATE_COMMON . '/checkout/cart-shipping.php';
		}

		$wfacp_dir = strpos( $template, 'wfacp/checkout/cart-recurring-shipping.php' );
		if ( false !== $wfacp_dir ) {
			return WFACP_TEMPLATE_COMMON . '/checkout/cart-recurring-shipping.php';
		}

		$wfacp_dir = strpos( $template, 'wfacp/checkout/cart-recurring-shipping-calculate.php' );
		if ( false !== $wfacp_dir ) {
			return WFACP_TEMPLATE_COMMON . '/checkout/cart-recurring-shipping-calculate.php';
		}

		return $template;

	}


	public static function delete_option_enable_in_product_switcher() {
		return apply_filters( 'wfacp_enable_product_switcher_deletion_item', false );
	}

	public static function get_product_switcher_templates() {
		$templates = [
			'default' => array(
				'slug'      => 'default',
				'path'      => WFACP_TEMPLATE_COMMON . '/layout_9/template.php',
				'name'      => __( 'Default', 'woofunnels-aero-checkout' ),
				'thumbnail' => WFACP_PLUGIN_URL . '/public/template-common/images/defualt_product_switcher.jpg',
			)
		];

		return $templates;
	}


	/**
	 * Detect builder page is open
	 * @return bool
	 */

	public static function is_builder() {
		if ( is_admin() && isset( $_GET['page'] ) && 'wfacp' == $_GET['page'] ) {
			return true;
		}

		return false;

	}


}
