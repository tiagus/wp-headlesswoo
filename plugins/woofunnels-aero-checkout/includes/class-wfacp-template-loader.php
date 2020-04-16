<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class contains the basic functions responsible for front end views.
 * Class WFACP_View
 */
final class WFACP_Template_loader {

	public static $is_checkout = false;
	private static $ins = null;
	/**
	 * @var WFACP_Template_Common
	 */
	public $current_template;
	public $customizer_key_prefix = '';
	public $page_id = null;
	public $product_data = null;
	public $offer_data = null;
	/**
	 * @var $customize_manager_ins WP_Customize_Manager
	 */
	protected $customize_manager_ins = null;
	protected $template_type = [];
	protected $templates = [];

	protected function __construct() {

		if ( WFACP_Common::is_customizer() ) {
			// intialize customizer setup checkout page data
			WFACP_Common::pc( 'Setup customizer data hooks' );
			add_action( 'init', array( $this, 'is_wfacp_checkout_page' ), 1 );
			add_action( 'init', array( $this, 'maybe_setup_page' ), 20 );
			add_action( 'init', array( $this, 'wfacp_wfacpkirki_fields' ), 30 );
			add_action( 'init', array( $this, 'setup_page_for_wfacpkirki' ), 21 );
		}

		/** Late priority in case themes also using wfacpkirki */
		add_filter( 'wfacpkirki/config', array( $this, 'wfacp_wfacpkirki_configuration' ), 9999 );
		if ( WFACP_Common::is_customizer() ) {

			/** Kirki */
			require WFACP_PLUGIN_DIR . '/admin/includes/wfacpkirki/wfacpkirki.php';

			/** wfacpkirki custom controls */
			require WFACP_PLUGIN_DIR . '/includes/class-wfacp-wfacpkirki.php';
		}

		$this->add_default_template();
		$this->public_include();
		add_filter( 'template_redirect', array( $this, 'setup_preview' ), 99 );
	}

	public function add_default_template() {

		$template = [
			'slug'  => 'pre_built',
			'title' => __( 'Pre Built Checkout Page', 'woofunnels-aero-checkout' ),
		];
		$this->register_template_type( $template );

		$designs = array(
			'pre_built' => [

				'shopcheckout' => array(
					'path'        => WFACP_TEMPLATE_DIR . '/layout_9/template.php',
					'name'        => __( 'ShopCheckout', 'woofunnels-aero-checkout' ),
					'thumbnail'   => WFACP_PLUGIN_URL . '/public/templates/layout_9/views/images/thumbnail.jpg',
					'large_img'   => 'https://storage.googleapis.com/aerocheckout/layout_9_full.jpg',
					'type'        => 'view',
					'description' => '',
					'slug'        => 'layout_9',
				),
				'classic'      => array(
					'path'        => WFACP_TEMPLATE_DIR . '/layout_1/template.php',
					'name'        => __( 'Classic', 'woofunnels-aero-checkout' ),
					'thumbnail'   => WFACP_PLUGIN_URL . '/public/templates/layout_1/views/images/thumbnail.jpg',
					'large_img'   => 'https://storage.googleapis.com/aerocheckout/layout_1_full.jpg',
					'type'        => 'view',
					'description' => '',
					'slug'        => 'layout_1',
				),
				'salesletter'  => array(
					'path'        => WFACP_TEMPLATE_DIR . '/layout_2/template.php',
					'name'        => __( 'SalesLetter', 'woofunnels-aero-checkout' ),
					'thumbnail'   => WFACP_PLUGIN_URL . '/public/templates/layout_2/views/images/thumbnail.jpg',
					'large_img'   => 'https://storage.googleapis.com/aerocheckout/layout_2_full.jpg',
					'type'        => 'view',
					'description' => '',
					'slug'        => 'layout_2',
				),
				'marketer'     => array(
					'path'        => WFACP_TEMPLATE_DIR . '/layout_4/template.php',
					'name'        => __( 'Marketer', 'woofunnels-aero-checkout' ),
					'thumbnail'   => WFACP_PLUGIN_URL . '/public/templates/layout_4/views/images/thumbnail.jpg',
					'large_img'   => 'https://storage.googleapis.com/aerocheckout/layout_4_full.jpg',
					'type'        => 'view',
					'description' => '',
					'slug'        => 'layout_4',
				),

			],
		);
		foreach ( $designs as $d_key => $templates ) {

			if ( is_array( $templates ) ) {
				foreach ( $templates as $temp_key => $temp_val ) {
					$this->register_template( $temp_key, $temp_val, $d_key );
				}
			}
		}
	}

	public function register_template_type( $data ) {

		if ( isset( $data['slug'] ) && '' != $data['slug'] && isset( $data['title'] ) && '' != $data['title'] ) {
			$slug  = sanitize_title( $data['slug'] );
			$title = esc_html( trim( $data['title'] ) );
			if ( ! isset( $this->template_type[ $slug ] ) ) {
				$this->template_type[ $slug ] = trim( $title );
			}
		}
	}

	public function register_template( $slug, $data, $type = 'pre_built' ) {
		if ( '' !== $slug && ! empty( $data ) ) {
			if ( file_exists( $data['path'] ) ) {

				$this->templates[ $type ][ $slug ] = $data;
			}
		}
	}

	/**
	 * This function use for initialize template on public end
	 * @return type
	 */
	private function public_include() {
		if ( isset( $_REQUEST['wfacp_customize'] ) || isset( $_REQUEST['wfacp_id'] ) ) {
			return;
		}
		//allow setup data for front end checkout page
		add_action( 'wp', array( $this, 'is_wfacp_checkout_page' ), 5 );
		add_action( 'wp', array( $this, 'maybe_setup_page' ), 7 );
	}

	public static function get_instance() {
		if ( null == self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function is_wfacp_checkout_page() {

		if ( is_account_page() ) {
			return false;
		}
		if ( apply_filters( 'wfacp_skip_checkout_page_detection', false ) ) {
			return false;
		}
		/* remove divi theme customizer setting */
		remove_action( 'wp', 'et_divi_add_customizer_css' );
		if ( self::$is_checkout ) {
			return true;
		}

		global $post;

		if ( WFACP_Common::is_customizer() ) {
			$temp_id = absint( $_REQUEST['wfacp_id'] );
			$post    = get_post( $temp_id );
			WFACP_Common::pc( 'Try to get wfacp_id from customizer url->' . $temp_id );

		}
		if ( WFACP_Common::is_checkout_process() ) {
			$post_id = absint( $_REQUEST['_wfacp_post_id'] );
			$post    = get_post( $post_id );
			WFACP_Common::pc( 'Try to get wfacp_id from posted data when checkout process iniatiated->' . $post_id );
		}

		if ( ! is_null( $post ) && $post->post_type == WFACP_Common::get_post_type_slug() ) {
			WFACP_Common::pc( 'checkout wfacp_page id found ->' . $post->ID . '. wfacp_checkout_page_found hooks is setup' );
			WFACP_Common::set_id( $post->ID );
			self::$is_checkout = true;
			do_action( 'wfacp_checkout_page_found' );

			return true;
		} else {

			if ( is_checkout() && ! ( is_order_received_page() || is_checkout_pay_page() ) ) {

				$overirde_checkout_page_id = WFACP_Common::get_checkout_page_id();
				// checking this is default checkout page

				if ( 0 === $overirde_checkout_page_id ) {
					do_action( 'wfacp_none_checkout_pages', $post );

					return;
				}

				// get post return $current postdata when you pass post_id=0;
				//this couse redirection issue
				$may_be_post = get_post( $overirde_checkout_page_id );

				if ( ! is_null( $may_be_post ) ) {
					if ( WFACP_Common::get_post_type_slug() !== $may_be_post->post_type ) {
						// Other pages
						// Redirect when to other pages
						wp_redirect( get_the_permalink( $overirde_checkout_page_id ) );
					} else {
						if ( $may_be_post->post_status == 'publish' ) {
							//wfacp pages
							$overirde_checkout_page_id = apply_filters( 'wfacp_wpml_checkout_page_id', $overirde_checkout_page_id );

							WFACP_Common::pc( 'Override checkout wfacp_page id found ->' . $overirde_checkout_page_id . '. wfacp_checkout_page_found hooks is setup' );
							do_action( 'wfacp_changed_default_woocommerce_page', $overirde_checkout_page_id );
							WFACP_Common::set_id( $overirde_checkout_page_id );
							self::$is_checkout = true;
							do_action( 'wfacp_checkout_page_found' );

							return true;
						}
					}
				}
			}
			if ( ! is_null( $post ) ) {

				WFACP_Common::pc( [ 'No wfacp page found , This case for embed forms settings', $post ] );
				do_action( 'wfacp_none_checkout_pages', $post );
			}
		}

		return false;
	}

	public function setup_preview() {

		add_filter( 'template_include', array( $this, 'maybe_load' ) );
	}

	/**
	 * @hooked over `template_include`
	 * This method checks for the current running funnels and controller to setup data & offer validation
	 * It also loads and echo/prints current template if the offer demands to.
	 *
	 * @param $template current template in WordPress ecosystem
	 *
	 * @return mixed
	 */
	public function maybe_load( $template ) {
		WFACP_Common::pc( 'maybe_load template function executing default template is ' . $template );
		if ( is_subclass_of( $this->current_template, 'WFACP_Template_Common' ) ) {
			WFACP_Common::pc( 'maybe_load template function executing our template is ' . $this->current_template->get_slug() );
			do_action( 'wfacp_after_checkout_page_found', WFACP_Common::get_id() );

			$this->current_template->get_view();
			exit;
		} else {

			do_action( 'wfacp_checkout_page_not_found' );
		}

		return $template;
	}

	public function get_template_type() {
		return $this->template_type;
	}

	public function locate_template( $slug, $template_type = 'pre_built', $data = false ) {
		if ( ! isset( $this->templates[ $template_type ] ) || ! isset( $this->templates[ $template_type ][ $slug ] ) ) {
			//return  shopcheckout default template
			$default_design_data = WFACP_Common::default_design_data();
			$slug                = $default_design_data['selected'];
			$template_type       = $default_design_data['selected_type'];

			if ( true === $data ) {
				return $default_design_data;
			}

			return $this->templates[ $template_type ][ $slug ]['path'];
		}
		if ( array_key_exists( $slug, $this->templates[ $template_type ] ) ) {

			if ( true === $data ) {
				return $this->templates[ $template_type ][ $slug ];
			}

			return $this->templates[ $template_type ][ $slug ]['path'];
		}

		return false;
	}

	/**
	 * @param string $is_single
	 *
	 * @return array
	 */
	public function get_templates() {
		return $this->templates;
	}

	public function get_single_template( $template = '', $type = 'pre_built' ) {
		if ( empty( $template ) ) {
			return [];
		}
		if ( isset( $this->templates[ $type ] ) && isset( $this->templates[ $type ][ $template ] ) ) {
			return $this->templates[ $type ][ $template ];
		}

		return [];
	}


	/**
	 * @param WP_Customize_Manager $customize_manager
	 */
	public function maybe_add_customize_preview_init( $customize_manager ) {
		$this->customize_manager_ins = $customize_manager;
	}

	public function wfacp_wfacpkirki_configuration( $path ) {
		if ( $this->is_valid_state_for_data_setup() ) {
			return array(
				'url_path' => WFACP_PLUGIN_URL . '/admin/includes/wfacpkirki/',
			);
		}

		return $path;
	}

	/**
	 * Finds out if its safe to initiate data setup for the current request.
	 * Checks for the environmental conditions and provide results.
	 * @return bool true on success| false otherwise
	 * @see WFACP_Template_loader::maybe_setup_page()
	 */
	public function is_valid_state_for_data_setup() {

		return self::$is_checkout;
	}

	public function wfacp_wfacpkirki_fields() {
		$temp_ins = $this->get_template_ins();
		/** if ! customizer */
		if ( apply_filters( 'wfacp_setup_field_data', false ) ) {
			if ( ! WFACP_Common::is_customizer() ) {
				return;
			}
		}

		if ( $temp_ins instanceof WFACP_Template_Common && is_array( $temp_ins->customizer_data() ) && count( $temp_ins->customizer_data() ) > 0 ) {
			foreach ( $temp_ins->customizer_data() as $panel_single ) {
				if ( count( $panel_single ) == 0 ) {
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

									$field_data = array_merge( $field_data, array(
										'settings' => $field_key_final,
										'section'  => $section_key_final,
									) );

									/** unset wfacp_partial key if present as not required for wfacpkirki */
									if ( isset( $field_data['wfacp_partial'] ) ) {
										unset( $field_data['wfacp_partial'] );
									}

									wfacpkirki::add_field( WFACP_SLUG, $field_data );

									/** Setting fields: type and element class for live preview */
									if ( isset( $field_data['wfacp_transport'] ) && is_array( $field_data['wfacp_transport'] ) ) {
										$field_key_final = $this->customizer_key_prefix . '[' . $field_key_final . ']';

										$temp_ins->customizer_fields[ $field_key_final ] = $field_data['wfacp_transport'];
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function get_template_ins() {
		return $this->current_template;
	}

	public function setup_page_for_wfacpkirki() {

		if ( true === WFACP_Common::is_customizer() ) {
			add_action( 'customize_preview_init', array( $this, 'maybe_add_customize_preview_init' ) );
		}
		$this->customizer_key_prefix = WFACP_SLUG . '_c_' . WFACP_Common::get_id();

		/** wfacpkirki */
		if ( class_exists( 'wfacpkirki' ) ) {
			wfacpkirki::add_config( WFACP_SLUG, array(
				'option_type' => 'option',
				'option_name' => $this->customizer_key_prefix,
			) );
		}
	}

	/**
	 * @hooked over `init`:15
	 * This method try and sets up the data for all the existing pages.
	 * customizer-admin | customizer-preview | front-end-funnel | front-end-ajax-request-during-funnel
	 * For the given environments we have our offer ID setup at this point. So its safe and necessary to set the data.
	 * This method does:
	 * 1. Fetches and sets up the offer data based on the offer id provided
	 * 2. Finds the loads the appropriate template.
	 * 3. loads offer data to the template instance
	 * 4. Build offer data for the current offer
	 */
	public function maybe_setup_page() {

		if ( $this->is_valid_state_for_data_setup() ) {
			$id = WFACP_Common::get_id();
			/** Set customizer key prefix in common */
			/**
			 * @var $get_customizer_instance WFACP_Customizer
			 */
			WFACP_Common::pc( 'May be setup page stated' );
			$get_customizer_instance = WFACP_Core()->customizer;
			if ( ! is_null( $get_customizer_instance ) ) {
				$instances = $get_customizer_instance->load_template( $id );
				if ( ! is_null( $instances ) ) {
					$this->current_template = $instances;
					WFACP_Common::pc( 'May be setup page Layout class is found -> ' . $this->current_template->get_slug() );

					$this->current_template->get_customizer_data();
				} else {
					WFACP_Common::pc( 'May be setup page Layout class is not found ' );
				}
			}
		}
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

if ( class_exists( 'WFACP_Core' ) && ! WFACP_Common::is_disabled() ) {
	WFACP_Core::register( 'template_loader', 'WFACP_Template_loader' );
}

