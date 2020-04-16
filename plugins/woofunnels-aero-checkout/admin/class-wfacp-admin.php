<?php
defined( 'ABSPATH' ) || exit;

final class WFACP_admin {

	private static $ins = null;
	public $wfacp_id = 0;
	public $current_page = 'products';
	public $current_section;
	protected $localize_data = [];
	protected $have_variable = false;
	public $default_checkout_status = false;
	private $address_fields = [
		'billing'  => [],
		'shipping' => [],
	];

	protected function __construct() {
		$this->current_section = __DIR__ . '/views/sections/product.php';
		$this->wfacp_id        = WFACP_Common::get_id();
		add_action( 'admin_menu', [ $this, 'redirect_to_our_url' ], 9 );
		add_action( 'admin_init', [ $this, 'delete_checkout_pages' ], 10 );
		add_action( 'admin_init', [ $this, 'duplicate_checkout_pages' ], 11 );
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 90 );

		add_filter( 'plugin_action_links_' . WFACP_PLUGIN_BASENAME, array( $this, 'plugin_actions' ) );
		add_filter( 'woofunnels_uninstall_reasons', array( $this, 'plugin_uninstall_reasons' ), 20 );


		/* Show Widzard */
		add_action( 'admin_init', array( $this, 'maybe_show_wizard' ) );
		add_action( 'wfacp_license_activated', [ $this, 'creating_aero_default_pages' ] );


		add_action( 'admin_head', [ $this, 'open_admin_bar' ], 90 );
		add_action( 'admin_footer', [ $this, 'admin_footer' ], 90 );

		add_action( 'wfacp_loaded', array( $this, 'include_notification' ) );

		/**
		 * Admin enqueue scripts
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_assets' ], 99 );
		/**
		 * Admin customizer enqueue scripts
		 */
		add_action( 'customize_controls_print_styles', [ $this, 'admin_customizer_enqueue_assets' ], 10 );

		add_filter( 'woocommerce_billing_fields', [ $this, 'add_css_ready_classes' ] );
		add_filter( 'woocommerce_shipping_fields', [ $this, 'add_css_ready_classes' ] );
		add_action( 'admin_menu', [ $this, 'set_section' ] );

		add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'show_advanced_field_order' ] );

		add_action( 'admin_menu', [ $this, 'update_our_custom_field_data' ] );
		add_action( 'in_admin_header', [ $this, 'maybe_remove_all_notices_on_page' ] );

		add_action( 'in_admin_header', [ $this, 'restrict_notices_display' ] );

		add_filter( 'wfacp_builder_merge_field_arguments', [ $this, 'wfacp_builder_merge_field_arguments' ], 10, 4 );

		add_action( 'admin_print_styles', [ $this, 'remove_theme_css_and_scripts' ], 100 );

	}


	public function include_notification() {

		include_once dirname( __FILE__ ) . '/includes/notifications/class-wfacp-notifications.php';

	}

	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function set_section() {
		if ( WFACP_Common::get_id() > 0 && isset( $_GET['section'] ) ) {

			$new_section        = $_GET['section'];
			$this->current_page = $new_section;
			if ( file_exists( __DIR__ . '/views/sections/' . $new_section . '.php' ) ) {
				$this->current_section = __DIR__ . '/views/sections/' . $new_section . '.php';
			}
			$this->current_section = apply_filters( 'wfacp_builder_pages_path', $this->current_section, $new_section, $this );

		}

	}

	public function register_admin_menu() {
		add_submenu_page( 'woofunnels', 'AeroCheckout', 'AeroCheckout', 'manage_woocommerce', 'wfacp', array(
			$this,
			'admin_page',
		) );
	}

	public function admin_enqueue_assets() {
		wp_enqueue_style( 'wfacp-admin-font', $this->get_admin_url() . '/assets/css/wfacp-admin-font.css', array(), WFACP_VERSION_DEV );
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'wfacp' ) {
			wp_enqueue_script( 'jquery' );

			wp_enqueue_editor();
			wp_enqueue_style( 'wfacp-izimodal', $this->get_admin_url() . '/includes/iziModal/iziModal.css', array(), WFACP_VERSION_DEV );
			wp_enqueue_style( 'wfacp-vue-multiselect', $this->get_admin_url() . '/includes/vuejs/vue-multiselect.min.css', array(), WFACP_VERSION_DEV );
			wp_enqueue_style( 'wfacp-vfg', $this->get_admin_url() . '/includes/vuejs/vfg.min.css', array(), WFACP_VERSION_DEV );
			wp_enqueue_style( 'wfacp-admin-main', $this->get_admin_url() . '/assets/css/wfacp-admin.css', array(), WFACP_VERSION_DEV );
			wp_enqueue_style( 'wfacp-admin-app', $this->get_admin_url() . '/assets/css/wfacp-admin-app.css', array(), WFACP_VERSION_DEV );
			wp_enqueue_style( 'wfacp-sweetalert2', $this->get_admin_url() . '/assets/css/sweetalert2.css', array(), WFACP_VERSION_DEV );
			wp_enqueue_script( 'wfacp-izimodal', $this->get_admin_url() . '/includes/iziModal/iziModal.js', array(), WFACP_VERSION_DEV );
			wp_enqueue_script( 'wfacp-vuejs', $this->get_admin_url() . '/includes/vuejs/vue.min.js', array(), '2.6.10' );
			wp_enqueue_script( 'wfacp-vue-vfg', $this->get_admin_url() . '/includes/vuejs/vfg.min.js', array(), '2.3.4' );
			wp_enqueue_script( 'wfacp-vue-multiselected', $this->get_admin_url() . '/includes/vuejs/vue-multiselect.min.js', array(), '2.1.0' );
			wp_enqueue_script( 'wfacp-sweetalert2', $this->get_admin_url() . '/assets/js/wfacp-sweetalert.min.js', array(), WFACP_VERSION_DEV );

			if ( $this->wfacp_id > 0 ) {
				wp_enqueue_style( 'wfacp-admin-bg', $this->get_admin_url() . '/assets/css/wfacp-funnel-bg.css', array(), WFACP_VERSION_DEV );
				wp_enqueue_script( 'jquery-ui' );
				wp_enqueue_script( 'jquery-ui-sortable' );
			} else {
				wp_enqueue_style( 'woocommerce_admin_styles' );
				wp_enqueue_script( 'wc-backbone-modal' );
			}

			wp_dequeue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'wfacp', $this->get_admin_url() . '/assets/js/wfacp_combined.min.js', array( 'jquery', 'underscore', 'backbone' ), WFACP_VERSION_DEV );

			$this->localize_data();
		}
	}

	public function get_admin_url() {
		return plugin_dir_url( WFACP_PLUGIN_FILE ) . 'admin';
	}

	private function localize_data() {

		wp_localize_script( 'wfacp', 'wfacp_data', $this->get_localize_data() );
		wp_localize_script( 'wfacp', 'wfacp_localization', WFACP_Common::get_builder_localization() );
		wp_localize_script( 'wfacp', 'wfacp_secure', [
			'nonce' => wp_create_nonce( 'wfacp_secure_key' ),
		] );

	}

	public function get_localize_data() {

		if ( is_array( $this->localize_data ) && count( $this->localize_data ) > 0 ) {
			return $this->localize_data;
		}

		$checkout_page_slug = 'checkout';
		$checkout_id        = wc_get_page_id( 'checkout' );
		if ( $checkout_id > - 1 ) {
			$checkout_page = get_post( $checkout_id );
			if ( $checkout_page instanceof WP_Post ) {
				$checkout_page_slug = $checkout_page->post_name;
			}
		}
		$post                                            = get_post( $this->wfacp_id );
		$this->localize_data['checkout_page_slug']       = $checkout_page_slug;
		$this->localize_data['checkout_page_slug_error'] = "Sorry! You cannot use the slug '" . $checkout_page_slug . "'  Its already reserved by native WooCommerce checkout. Please use another slug.";
		$this->localize_data['id']                       = $this->wfacp_id;
		$this->localize_data['name']                     = get_the_title( $this->wfacp_id );
		$this->localize_data['post_name']                = ! is_null( $post ) ? $post->post_name : '';
		$this->localize_data['post_url']                 = get_the_permalink( $this->wfacp_id );
		$this->localize_data['base_url']                 = WFACP_Common::base_url();
		$this->localize_data['curtomize_url']            = $this->get_customize_url();
		$this->localize_data['currency']                 = get_woocommerce_currency_symbol();
		$this->localize_data['global_settings']          = WFACP_Common::global_settings( $this->wfacp_id );

		if ( ! empty( $this->wfacp_id ) ) {
			$this->localize_data['products'] = $this->get_page_product();
			if ( $this->current_page == 'fields' ) {
				$this->localize_data['product_switcher_data'] = WFACP_Common::get_product_switcher_data( $this->wfacp_id );
			}

			$this->localize_data['products_settings'] = WFACP_Common::get_page_product_settings( $this->wfacp_id );
			$this->localize_data['design']            = $this->get_page_design();
			$this->localize_data['layout']            = $this->get_page_layout();
			$this->localize_data['settings']          = WFACP_Common::get_page_settings( $this->wfacp_id );

		}

		unset( $this->localize_data['layout']['fieldsets_normalize'] );
		unset( $this->localize_data['layout']['checkout_fields'] );

		$this->localize_data['product_switcher_templates'] = WFACP_Common::get_product_switcher_templates();
		$this->localize_data['global_dependency_messages'] = $this->global_dependency_messages();

		return apply_filters( 'wfacp_admin_localize_data', $this->localize_data );

	}

	/**
	 * @return array
	 */
	public function global_dependency_messages() {
		$aero_messages = [];
		if ( wc_shipping_enabled() ) {

			$shipping_location = admin_url( 'admin.php?page=wc-settings' );
			$shipping_methods  = admin_url( 'admin.php?page=wc-settings&tab=shipping' );
			$msg               = __( sprintf( 'Your store has <a href="%s">shipping location</a> enabled. Depending upon <a href="%s">shipping method</a> configuration, checkout may need "Shipping Method" field. Please drag Shipping Method field to place in form. Note: If items in cart have no shipping  applicable, this field will be automatically hidden.', $shipping_location, $shipping_methods ), 'woofunnels-aero-checkout' );

			$aero_messages[] = [
				'message'     => $msg,
				'id'          => 'shipping_calculator',
				'show'        => 'yes',
				'dismissible' => true,
				'is_global'   => false,
				'type'        => 'wfacp_warning',
			];

		}


		$messages = apply_filters( 'wfacp_global_dependency_messages', [] );


		if ( ! empty( $messages ) && is_array( $messages ) ) {
			$aero_messages = array_merge( $aero_messages, $messages );
		}

		$final_messages = [];
		if ( empty( $aero_messages ) ) {
			$final_messages = new stdClass();
		} else {
			foreach ( $aero_messages as $msg ) {

				$mid = md5( $msg['message'] );
				if ( ! isset( $msg['is_global'] ) || false === $msg['is_global'] ) {
					$pageID = WFACP_Common::get_id();
					$mid    = md5( $msg['message'] . $pageID );
				}


				if ( isset( $msg['dismissible'] ) ) {
					$msg['dismissible'] = wc_string_to_bool( $msg['dismissible'] );
				} else {
					$msg['dismissible'] = false;
				}
				$final_messages[ $mid ] = $msg;
			}
		}

		return $this->hide_notification( $final_messages );
	}

	public function get_customize_url() {
		$url        = add_query_arg( [
			'wfacp_customize' => 'loaded',
			'wfacp_id'        => $this->wfacp_id,
		], get_the_permalink( $this->wfacp_id ) );
		$return_url = add_query_arg( [
			'page'     => 'wfacp',
			'section'  => 'design',
			'wfacp_id' => $this->wfacp_id,
		], admin_url( 'admin.php' ) );

		return add_query_arg( [
			'url'             => apply_filters( 'wfacp_customize_url', urlencode_deep( $url ), $this ),
			'wfacp_customize' => 'loaded',
			'wfacp_id'        => $this->wfacp_id,
			'return'          => urlencode( $return_url ),
		], admin_url( 'customize.php' ) );
	}

	private function get_page_product() {
		$output   = [];
		$products = WFACP_Common::get_page_product( $this->wfacp_id );

		if ( is_array( $products ) && count( $products ) > 0 ) {
			foreach ( $products as $unique_id => $pdata ) {
				$product = wc_get_product( $pdata['id'] );

				if ( $product instanceof WC_Product ) {
					$image_id     = $product->get_image_id();
					$default      = WFACP_Common::get_default_product_config();
					$default      = array_merge( $default, $pdata );
					$product_type = $product->get_type();
					if ( '' == $default['title'] ) {
						$default['title'] = $product->get_title();
					}

					$product_image_url = '';
					$images            = wp_get_attachment_image_src( $image_id );
					if ( is_array( $images ) && count( $images ) > 0 ) {
						$product_image_url = wp_get_attachment_image_src( $image_id )[0];
					}
					$default['image'] = apply_filters( 'wfacp_product_image', $product_image_url, $product );
					if ( '' == $default['image'] ) {
						$default['image'] = WFACP_PLUGIN_URL . '/admin/assets/img/product_default_icon.jpg';
					}

					$default['type'] = $product_type;
					/**
					 * @var $product WC_Product_Variable;
					 */
					if ( in_array( $product_type, WFACP_COmmon::get_variable_product_type() ) ) {
						$this->have_variable = true;
						$default['variable'] = 'yes';
						$default['price']    = $product->get_price_html();
					} else {
						if ( in_array( $product_type, WFACP_Common::get_variation_product_type() ) ) {
							$default['title'] = $product->get_name();
						}
						$row_data                 = $product->get_data();
						$sale_price               = $row_data['sale_price'];
						$default['price']         = wc_price( $row_data['price'] );
						$default['regular_price'] = wc_price( $row_data['regular_price'] );
						if ( '' != $sale_price ) {
							$default['sale_price'] = wc_price( $sale_price );
						}
					}
					$default['stock']                = $product->is_in_stock();
					$default['is_sold_individually'] = $product->is_sold_individually();
					$resp['product'][ $unique_id ]   = $default;
					$output[ $unique_id ]            = $default;
				};
			}
			if ( count( $output ) > 0 ) {
				return $output;
			}
		} else {
			return new stdClass();
		}
	}

	private function get_page_design() {

		$templates   = WFACP_Core()->template_loader->get_templates();
		$settings    = WFACP_Common::get_page_design( $this->wfacp_id );
		$design_type = WFACP_Core()->template_loader->get_template_type();
		$out         = array_merge( [
			'designs'      => $templates,
			'design_types' => $design_type,
		], $settings );

		return $out;
	}

	private function get_page_layout() {

		/**
		 * remove selected field(step field) from main checkout fields [billing,shipping];
		 */

		$data                  = $this->manage_input_fields();
		$data['default_steps'] = WFACP_Common::get_default_steps_fields();

		return $data;
	}

	/**
	 * Remove Selected field from available checkout fields
	 *
	 * @param $input_fields
	 * @param array $selected_fields
	 *
	 * @return mixed
	 */
	private function manage_input_fields() {
		$page_data        = WFACP_Common::get_page_layout( $this->wfacp_id );
		$input_fields     = $this->get_checkout_field();
		$input_fields     = $this->merge_custom_fields( $input_fields );
		$available_fields = $input_fields;
		$selected_fields  = $page_data['fieldsets'];

		if ( empty( $selected_fields ) || ! is_array( $selected_fields ) ) {
			return $input_fields;
		}
		foreach ( $selected_fields as $step => $step_data ) {
			if ( ! is_array( $step_data ) ) {
				continue;
			}

			foreach ( $step_data as $index => $section ) {
				if ( empty( $section['fields'] ) ) {
					continue;
				}

				$fields = $section['fields'];
				foreach ( $fields as $f_index => $field ) {
					$id   = $field['id'];
					$type = $field['field_type'];
					if ( ! isset( $field['cssready'] ) ) {
						$input_fields[ $type ][ $id ]['cssready'] = [];
					}
					if ( $id == 'address' || $id == 'shipping-address' ) {
						if ( isset( $this->address_fields[ $type ] ) ) {
							$this->address_fields[ $type ][ $id ] = true;
						}
					}

					$temp_page_field = $page_data['fieldsets'][ $step ][ $index ]['fields'][ $f_index ];

					$page_data['fieldsets'][ $step ][ $index ]['fields'][ $f_index ] = apply_filters( 'wfacp_builder_merge_field_arguments', $temp_page_field, $id, $type, $available_fields );

					if ( isset( $input_fields[ $type ][ $id ] ) ) {
						unset( $input_fields[ $type ][ $id ] );
					}
				}
			}
		}

		$input_fields     = $this->add_address_field( $input_fields );
		$available_fields = $this->add_address_field( $available_fields, true );
		foreach ( $input_fields as $key => $field_data ) {
			if ( is_array( $field_data ) && count( $field_data ) == 0 ) {
				$input_fields[ $key ] = new stdClass();
			}
		}
		$input_fields = [
			'input_fields'     => $input_fields,
			'available_fields' => $available_fields,
		];
		$data         = array_merge( $page_data, $input_fields );

		return $data;
	}

	private function get_checkout_field() {
		$billing = WFACP_Common::get_address_fields( 'billing_' );
		$output  = [
			'billing' => $billing,
		];

		$products_fields = WFACP_Common::get_product_field();
		if ( count( $products_fields ) > 0 ) {
			$output['product'] = $products_fields;
		}
		$advanced_fields = WFACP_Common::get_advanced_fields();
		if ( get_option( 'woocommerce_enable_order_comments', 'yes' ) !== 'yes' ) {
			unset( $advanced_fields['order_comments'] );
		}

		$output['advanced'] = $advanced_fields;

		return $output;
	}

	/**
	 * Merge Custom createad field with real fields;
	 *
	 * @param $wfacp_id
	 * @param $input_fields
	 *
	 * @return mixed
	 */
	private function merge_custom_fields( $input_fields ) {

		$custom_fields = WFACP_Common::get_page_custom_fields( $this->wfacp_id );
		if ( ! is_array( $custom_fields ) ) {
			return $input_fields;
		}
		foreach ( $custom_fields as $section => $fields ) {
			foreach ( $fields as $key => $field ) {
				$input_fields[ $section ][ $key ] = $field;
			}
		}

		return $input_fields;
	}


	private function add_address_field( $input_fields, $force = false ) {

		foreach ( [ 'billing' ] as $type ) {
			if ( isset( $input_fields[ $type ] ) && ! isset( $this->address_fields[ $type ]['address'] ) || true == $force ) {

				$input_fields[ $type ]['address'] = WFACP_Common::get_single_address_fields( $type );

			}
			if ( isset( $input_fields[ $type ] ) && ! isset( $this->address_fields[ $type ]['shipping-address'] ) || true == $force ) {

				$input_fields[ $type ]['shipping-address'] = WFACP_Common::get_single_address_fields( 'shipping' );
			}
		}

		return $input_fields;
	}

	public function admin_customizer_enqueue_assets() {
		if ( WFACP_Common::is_customizer() ) {
			wp_enqueue_style( 'wfacp-customizer', $this->get_admin_url() . '/assets/css/wfacp-customizer.css', array(), WFACP_VERSION_DEV );
			wp_enqueue_style( 'wfacp-modal-css', $this->get_admin_url() . '/assets/css/wfacp-modal.css', array(), WFACP_VERSION_DEV );
			wp_enqueue_script( 'wfacp-modal-js', $this->get_admin_url() . '/assets/js/wfacp-modal.js', array(), WFACP_VERSION_DEV );
		}
	}

	public function open_admin_bar() {

		if ( defined( 'WFACP_IS_DEV' ) && true == WFACP_IS_DEV ) {

			echo "<style>html{margin-top:32px}div#wpadminbar {display: block !important;} .wfacp_fixed_sidebar{top:92px}.wfacp_fixed_header {margin-top: 32px;}#order_data .order_data_column .form-field input[type='checkbox'], #order_data .order_data_column .form-field input[type='radio']{width: auto;}#order_data .order_data_column .form-field label.radio {width: calc(100% - 22px); display: inline-block;}</style>";
		}

		echo "<style>
#order_data #wfacp_admin_advanced_field input[type='radio']{width: auto;float: left;margin: 0 5px 5px 0;}
#order_data #wfacp_admin_advanced_field span.wfacp_radio_options_group{display: block;}
#order_data #wfacp_admin_advanced_field span.wfacp_radio_options_group:before, #order_data #wfacp_admin_advanced_field span.wfacp_radio_options_group:after{content: ''; display: block;}
#order_data #wfacp_admin_advanced_field span.wfacp_radio_options_group:after{    clear: both;} </style>";
	}

	public function admin_page() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wfacp' ) {
			if ( $this->wfacp_id > 0 ) {
				include __DIR__ . '/views/view.php';
			} else {
				/**
				 * No need here to save it in transient as we are not removing the transients at appropriate places
				 */
				WFACP_Common::save_publish_checkout_pages_in_transient();
				$path = __DIR__ . '/views/admin.php';
				if ( isset( $_GET['tab'] ) && '' != $_GET['tab'] ) {
					$tab  = trim( $_GET['tab'] );
					$path = __DIR__ . "/views/{$tab}.php";
				}
				if ( file_exists( $path ) ) {
					include_once $path;
				}
			}
		}
	}

	public function add_css_ready_classes( $address ) {

		if ( is_array( $address ) && count( $address ) > 0 ) {
			foreach ( $address as $key => $field ) {
				$address[ $key ]['cssready'] = [];
			}
		}

		return $address;
	}

	public function delete_checkout_pages() {
		if ( isset( $_GET['wfacp_delete'] ) && isset( $_GET['wfacp_id'] ) && $_GET['wfacp_id'] > 0 ) {
			$wfacp_id = absint( $_GET['wfacp_id'] );
			wp_delete_post( $wfacp_id, true );
			wp_redirect( admin_url( 'admin.php?page=wfacp' ) );
			exit;

		}
	}

	public function duplicate_checkout_pages() {
		if ( isset( $_GET['wfacp_duplicate'] ) && isset( $_GET['wfacp_id'] ) && $_GET['wfacp_id'] > 0 ) {
			$wfacp_id = absint( $_GET['wfacp_id'] );
			WFACP_Common::make_duplicate( $wfacp_id );
			wp_redirect( admin_url( 'admin.php?page=wfacp' ) );
			exit;
		}
	}

	/**
	 * this function use for display advanced field in order backend in General Tab
	 *
	 * @param $order WC_Order
	 */
	public function show_advanced_field_order( $order ) {

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$wfacp_id = get_post_meta( $order->get_id(), '_wfacp_post_id', true );

		if ( empty( $wfacp_id ) ) {
			return;
		}
		$title      = get_the_title( $wfacp_id );
		$title      .= " (#{$wfacp_id})";
		$title_link = add_query_arg( [
			'page'     => 'wfacp',
			'wfacp_id' => $wfacp_id,
			'section'  => 'product',
		], admin_url( 'admin.php' ) );

		$permalink = get_post_meta( $order->get_id(), '_wfacp_source', true );
		if ( empty( $permalink ) ) {
			$permalink = get_the_permalink( $wfacp_id );
		}
		$display_text = str_replace( home_url(), '', $permalink );
		?>
        <div style="clear: both;">
            <style>
                #wfacp_admin_advanced_field .optional {
                    display: none;
                }
            </style>
        </div>
        <div style="margin-top:15px" class="wfacp_order_backend_field_container">
            <h3 style="display: inline">Aero Checkout</h3>
            <p><b><?php _e( 'Template', 'woofunnel-aero-checkout' ); ?>:</b> <a href="<?php echo $title_link; ?>" target="_blank"><?php echo $title; ?></a></p>
            <p><b><?php _e( 'Source', 'woofunnel-aero-checkout' ); ?>:</b> <a href="<?php echo $permalink; ?>" target="_blank"><?php echo $display_text; ?></a></p>
        </div>
		<?php
		$wfacp_id = absint( $wfacp_id );
		$cfields  = WFACP_Common::get_page_custom_fields( $wfacp_id );
		if ( ! isset( $cfields['advanced'] ) ) {
			return;
		}
		$advancedFields = $cfields['advanced'];
		if ( ! is_array( $advancedFields ) || count( $advancedFields ) == 0 ) {
			return;
		}

		$heading_print = false;

		foreach ( $advancedFields as $field_key => $field ) {

			$has_data = get_post_meta( $order->get_id(), $field_key, true );

			if ( '' != $has_data ) {
				if ( false == $heading_print ) {
					printf( '<div style="clear: both;"></div><div style="margin-top:15px" class="wfacp_order_backend_field_container"><h3 style="display: inline">%s</h3> <span class="dashicons dashicons-edit" onclick="wfacp_show_admin_advanced_field(this)" style="cursor: pointer"></span><fieldset id="wfacp_admin_advanced_field" disabled>', __( 'Custom Fields', 'woofunnels-aero-checkout' ) );
					$heading_print = true;
				}
				if ( isset( $field['required'] ) ) {
					unset( $field['required'] );
				}
				if ( $field['type'] == 'hidden' ) {
					$field['type'] = 'text';
				}

				if ( isset( $field['class'] ) ) {
					$field['class'] = [ 'form-field', ' form-field-wide' ];
				}

				woocommerce_form_field( $field_key, $field, $has_data );
			}
		}
		if ( true == $heading_print ) {
			echo '</fieldset></div>';
		}
	}

	public function admin_footer() {
		?>

        <style>
            .wfacp_order_backend_field_container p.form-field {
                float: none;
            }
        </style>
        <script>
            function wfacp_show_admin_advanced_field(el) {
                el.style.visibility = 'hidden';
                document.getElementById("wfacp_admin_advanced_field").removeAttribute('disabled');
            }
        </script>
		<?php
	}

	public function update_our_custom_field_data() {
		if ( ! empty( $_POST ) && count( $_POST ) > 0 ) {
			if ( ! isset( $_POST['post_ID'] ) ) {
				return;
			}
			$post_data = $_POST;
			$post_id   = absint( $_POST['post_ID'] );
			foreach ( $post_data as $key => $value ) {
				if ( false !== strpos( $key, 'wfacp_' ) ) {
					update_post_meta( $post_id, $key, $post_data[ $key ] );
				}
			}
		}
	}

	public function maybe_remove_all_notices_on_page() {

		if ( isset( $_GET['page'] ) && 'wfacp' == $_GET['page'] ) {
			global $wp_filter;
			if ( isset( $wp_filter['admin_notices'] ) ) {
				foreach ( $wp_filter['admin_notices']->callbacks as $f_key => $f ) {
					foreach ( $f as $c_name => $clback ) {

						if ( false !== strpos( $c_name, 'XL_' ) ) {
							continue;
						}
						unset( $wp_filter['admin_notices']->callbacks[ $f_key ][ $c_name ] );

					}
				}
			}
		}

		if ( isset( $_GET['page'] ) && 'wfacp' == $_GET['page'] && isset( $_GET['wfacp_id'] ) && $_GET['wfacp_id'] > 0 ) {

			remove_all_actions( 'admin_notices' );
		}
	}

	public function redirect_to_our_url() {
		if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == WFACP_Common::get_post_type_slug() && isset( $_REQUEST['post'] ) && $_REQUEST['post'] > 0 ) {
			$wfob_id = absint( $_REQUEST['post'] );
			if ( $wfob_id > 0 ) {
				$redirect_url = add_query_arg( [
					'section'  => 'product',
					'wfacp_id' => $wfob_id,
				], admin_url( 'admin.php?page=wfacp' ) );
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}


	public function restrict_notices_display() {
		/** Inside AeroCheckout page */
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'wfacp' ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	public function wfacp_builder_merge_field_arguments( $field, $id, $type, $available_fields ) {

		if ( $id == 'shipping_calculator' ) {
			$default = $available_fields[ $type ][ $id ];
			$field   = wp_parse_args( $field, $default );
		} elseif ( $id == 'product_switching' ) {

			$default = $available_fields[ $type ][ $id ];
			$field   = wp_parse_args( $field, $default );
		} elseif ( $id == 'vat_number' ) {
			$default = $available_fields[ $type ][ $id ];

			$field = wp_parse_args( $field, $default );
			if ( isset( $default['depend_dency_message'] ) ) {
				$field['depend_dency_message'] = $default['depend_dency_message'];
			}


		}

		return $field;
	}


	/**
	 * Find removal folder path exist in enqueue js and css url
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	private final function find_js_css_handle( $url ) {
		if ( ! WFACP_Common::is_builder() ) {
			return;
		}
		$paths   = [ '/themes/', '/cache/' ];
		$plugins = [
			'revslider',
			'elementor/',
		];
		$paths   = array_merge( $paths, $plugins );

		$paths = apply_filters( 'wfacp_admin_css_js_removal_paths', $paths, $this );
		if ( empty( $paths ) ) {
			return false;
		}
		foreach ( $paths as $path ) {
			if ( false !== strpos( $url, $path ) ) {
				return true;
				break;
			}
		}

		return false;

	}

	public function remove_theme_css_and_scripts() {

		global $wp_scripts, $wp_styles;
		$registered_script = $wp_scripts->registered;
		if ( ! empty( $registered_script ) ) {
			foreach ( $registered_script as $handle => $data ) {
				if ( $this->find_js_css_handle( $data->src ) ) {
					unset( $wp_scripts->registered[ $handle ] );
					wp_dequeue_script( $handle );
				}
			}
		}

		$registered_style = $wp_styles->registered;
		if ( ! empty( $registered_style ) ) {
			foreach ( $registered_style as $handle => $data ) {
				if ( $this->find_js_css_handle( $data->src ) ) {
					unset( $wp_styles->registered[ $handle ] );
					wp_dequeue_script( $handle );
				}
			}
		}

	}

	private function hide_notification( $messages ) {
		if ( empty( $messages ) ) {
			return $messages;
		}
		$hide_messages = get_option( 'wfacp_global_notifications', [] );

		$post_message = get_post_meta( WFACP_Common::get_id(), 'notifications', true );
		if ( is_array( $post_message ) ) {
			$hide_messages = array_merge( $hide_messages, $post_message );
		}
		if ( empty( $hide_messages ) ) {
			return $messages;
		}


		foreach ( $messages as $mid => $message ) {
			if ( array_key_exists( $mid, $hide_messages ) ) {
				unset( $messages[ $mid ] );
			}
		}

		return $messages;

	}

	public function maybe_show_wizard() {
		if ( empty( $_GET['page'] ) || 'wfacp' !== $_GET['page'] ) {
			return;
		}

		if ( isset( $_GET['tab'] ) && strpos( $_GET['tab'], 'wizard' ) !== false ) {
			return;
		}

		if ( WFACP_Core()->support->is_license_present() === false ) {
			wp_redirect( admin_url( 'admin.php?page=wfacp&tab=' . WFACP_SLUG . '-wizard' ) );
		}
	}


	public function creating_aero_default_pages() {

		/* Check Variable is update or not */
		if ( $this->default_checkout_status == true ) {
			return;
		}

		/* Check Option key update or not For default create checkout */
		$checkout_option_status = get_option( 'wfacp_default_checkout' );
		if ( isset( $checkout_option_status ) && $checkout_option_status == 'active' ) {
			return;
		}

		/* Check AeroCheckout pages count */
		$checkoutPublished = (int) wp_count_posts( 'wfacp_checkout' )->publish;
		$checkoutDrafted   = (int) wp_count_posts( 'wfacp_checkout' )->draft;
		if ( $checkoutPublished > 0 || $checkoutDrafted > 0 ) {
			return;
		}


		/* Implement the templates code  */
		$templates = [
			[ 'slug' => 'salesletter', 'name' => 'SalesLetter' ],
			[ 'slug' => 'marketer', 'name' => 'Marketer' ],
			[ 'slug' => 'classic', 'name' => 'Classic' ],
			[ 'slug' => 'shopcheckout', 'name' => 'ShopCheckout' ],
			[ 'slug' => 'shopcheckout', 'name' => 'ShopCheckout MultiStep', 'is_multi' => true ],
		];

		$pagesIds = [];


		foreach ( $templates as $templ_key => $templ_name ) {

			$is_multiStep = false;

			if ( isset( $templ_name['slug'] ) ) {
				$template_key = $templ_name['slug'];
			}

			if ( isset( $templ_name['name'] ) ) {
				$template_name = $templ_name['name'];
			}

			if ( isset( $templ_name['is_multi'] ) && true === $templ_name['is_multi'] ) {
				$is_multiStep = $templ_name['is_multi'];
			}


			$insert_obj = new WFACP_Insert_Page();
			$insert_obj->setTitle( $template_name );
			$insert_obj->setPageName( $template_key );
			$insert_obj->setProducts();
			$insert_obj->setFormLayout( $is_multiStep );
			$insert_obj->setTemplate( $template_key );

			$CustomizerDefaultOption = [];
			if ( $template_key == "shopcheckout" ) {

				$CustomizerDefaultOption = [
					'wfacp_header_section_layout_9_header_layout'              => 'outside_header',
					'wfacp_layout_section_layout_9_mobile_sections_page_order' => [
						'wfacp_form',
						'wfacp_benefits_0',
						'wfacp_testimonials_0',
						'wfacp_promises_0',
						'wfacp_assurance_0',
						'wfacp_customer_0',
					],
				];

				if ( true === $is_multiStep ) {
					$CustomizerDefaultOption['wfacp_form_section_back_btn_text']                               = '« Return to {step_name}';
					$CustomizerDefaultOption['wfacp_form_section_layout_9_btn_order-place_talign']             = 'right';
					$CustomizerDefaultOption['wfacp_form_section_layout_9_btn_order-place_top_bottom_padding'] = 20;
					$CustomizerDefaultOption['wfacp_form_section_layout_9_btn_order-place_left_right_padding'] = 40;
					$CustomizerDefaultOption['wfacp_form_section_layout_9_btn_next_btn_text']                  = "CONTINUE TO {step_name} →";
					$CustomizerDefaultOption['wfacp_form_section_layout_9_btn_back_btn_text']                  = "CONTINUE TO {step_name} →";
					$CustomizerDefaultOption['wfacp_form_section_layout_9_btn_order-place_fs']                 = [
						'desktop'      => 16,
						'tablet'       => 14,
						'mobile'       => 16,
						'desktop-unit' => "px",
						'mobile-unit'  => "px",
						'tablet-unit'  => "px",
					];
				}

			}

			if ( true === $insert_obj->is_multi_step_form_type() ) {
				$num_of_steps = $insert_obj->get_form_step_count();
				if ( $num_of_steps > 1 ) {
					$defaultStepsname = [ 'Information', 'Shipping', 'Payment' ];
					for ( $i = 0; $i < $num_of_steps; $i ++ ) {
						$CustomizerDefaultOption[ 'wfacp_form_section_breadcrumb_' . $i . '_step_text' ] = $defaultStepsname[ $i ];
					}

				}
			}

			if ( is_array( $CustomizerDefaultOption ) && count( $CustomizerDefaultOption ) > 0 ) {
				$insert_obj->setCustomizer( $CustomizerDefaultOption );
			}

			$insert_obj->save();

			/* update option for default create aero checkout pages */
			$wfacpId = $insert_obj->get_wfacp_id();
			if ( $wfacpId != 0 && $wfacpId != '' ) {
				$pagesIds[] = $wfacpId;
				update_option( 'wfacp_default_checkout', 'active' );
				update_option( 'wfacp_default_checkout_pages', $pagesIds );
			}
			$this->default_checkout_status = true;
		}
	}

	public function plugin_actions( $links ) {

		$link = '<i class="woofunnels-slug" data-slug="' . WFACP_PLUGIN_BASENAME . '"></i>';
		if ( isset( $links['deactivate'] ) ) {
			$links['deactivate'] .= $link;
		}


		return $links;
	}

	public function plugin_uninstall_reasons( $uninstall_reasons ) {

		if ( ! isset( $uninstall_reasons['default'] ) ) {
			return $uninstall_reasons;
		}

		$sorted        = [ 0, 1, 2, 6, 3, 4, 5, 7 ];
		$final_reasons = [];

		array_push( $uninstall_reasons['default'], [
			'id'                => 35,
			'text'              => __( 'Doing testing', 'woofunnels-aero-checkout' ),
			'input_type'        => '',
			'input_placeholder' => '',
		] );
		array_push( $uninstall_reasons['default'], [
			'id'                => 42,
			'text'              => __( 'My checkout is not looking good', 'woofunnels-aero-checkout' ),
			'input_type'        => '',
			'input_placeholder' => '',
		] );
		array_push( $uninstall_reasons['default'], [
			'id'                => 41,
			'text'              => __( 'Troubleshooting conflicts with other plugins', 'woofunnels-aero-checkout' ),
			'input_type'        => '',
			'input_placeholder' => '',
		] );

		foreach ( $sorted as $key => $value ) {
			if ( $value === 2 ) {
				$uninstall_reasons['default'][ $value ]['text'] = 'I only need the plugin for shorter period';
			}
			$final_reasons['default'][] = $uninstall_reasons['default'][ $value ];
		}


		return $final_reasons;
	}

	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'WFACP_Core can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {

		throw new ErrorException( 'WFACP_Core can`t converted to string' );
	}

	/**
	 * To avoid cloning of current template class
	 */
	protected function __clone() {
	}


}

WFACP_admin::get_instance();
