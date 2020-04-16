<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WFCO_Admin {

	private static $ins = null;
	public $admin_path;
	public $admin_url;
	public $section_page = '';
	public $should_show_shortcodes = null;

	public function __construct() {

		$should_include = apply_filters( 'wfco_include_connector', false );
		if ( false === $should_include ) {
			return;
		}
		define( 'WFCO_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_PLUGIN_FILE ) ) );
		$this->admin_path = WFCO_PLUGIN_DIR;
		$this->admin_url  = WFCO_PLUGIN_URL;

		include_once( $this->admin_path . '/class-wfco-connector.php' );
		include_once( $this->admin_path . '/class-wfco-call.php' );
		include_once( $this->admin_path . '/class-wfco-load-connectors.php' );
		include_once( $this->admin_path . '/class-wfco-common.php' );
		include_once( $this->admin_path . '/class-wfco-ajax-controller.php' );
		include_once( $this->admin_path . '/class-wfco-model.php' );
		include_once( $this->admin_path . '/class-wfco-db.php' );
		include_once( $this->admin_path . '/class-wfco-connector-api.php' );

		WFCO_Common::init();

		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 90 );

		/**
		 * Admin enqueue scripts
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 99 );

		/**
		 * Admin footer text
		 */
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 9999, 1 );
		add_filter( 'update_footer', array( $this, 'update_footer' ), 9999, 1 );
		add_action( 'in_admin_header', array( $this, 'maybe_remove_all_notices_on_page' ) );
		//      add_action( 'admin_init', array( $this, 'check_db_version' ) );

	}

	public static function get_instance() {
		if ( null == self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function get_admin_url() {
		return plugin_dir_url( WFCO_PLUGIN_FILE ) . 'admin';
	}

	public function register_admin_menu() {

		add_submenu_page( 'woofunnels', __( 'Connector', 'woofunnels' ), __( 'Connector', 'woofunnels' ), 'manage_woocommerce', 'connector', array(
			$this,
			'connector_page',
		) );
	}

	public function admin_enqueue_assets() {
		/**
		 * Including izimodal assets
		 */
		if ( WFCO_Common::is_load_admin_assets( 'all' ) ) {

			if ( $this->is_connector_page() ) {
				wp_enqueue_style( 'wfco-sweetalert2-style', $this->admin_url . '/assets/css/sweetalert2.css', array(), WooFunnel_Loader::$version );
				wp_enqueue_style( 'wfco-izimodal', $this->admin_url . '/assets/css/iziModal/iziModal.css', array(), WooFunnel_Loader::$version );
				wp_enqueue_style( 'wfco-toast-style', $this->admin_url . '/assets/css/toast.min.css', array(), WooFunnel_Loader::$version );

				wp_enqueue_script( 'wfco-sweetalert2-script', $this->admin_url . '/assets/js/sweetalert2.js', array( 'jquery' ), WooFunnel_Loader::$version, true );
				wp_enqueue_script( 'wfco-izimodal', $this->admin_url . '/assets/js/iziModal/iziModal.js', array(), WooFunnel_Loader::$version );
				wp_enqueue_script( 'wfco-toast-script', $this->admin_url . '/assets/js/toast.min.js', array( 'jquery' ), WooFunnel_Loader::$version, true );

				wp_enqueue_script( 'wc-backbone-modal' );
			}
		}

		/**
		 * Including Connector assets on all connector pages.
		 */
		if ( WFCO_Common::is_load_admin_assets( 'all' ) ) {
			wp_enqueue_style( 'wfco-admin', $this->admin_url . '/assets/css/wfco-admin.css', array(), WooFunnel_Loader::$version );
			wp_enqueue_script( 'wfco-admin-ajax', $this->admin_url . '/assets/js/wfco-admin-ajax.js', array(), WooFunnel_Loader::$version );
			wp_enqueue_script( 'wfco-admin', $this->admin_url . '/assets/js/wfco-admin.js', array(), WooFunnel_Loader::$version );
			wp_enqueue_script( 'wfco-admin-sub', $this->admin_url . '/assets/js/wfco-admin-sub.js', array(), WooFunnel_Loader::$version );

			$data = array(
				'ajax_nonce'            => wp_create_nonce( 'wfcoaction-admin' ),
				'plugin_url'            => plugin_dir_url( WFCO_PLUGIN_FILE ),
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'admin_url'             => admin_url(),
				'ajax_chosen'           => wp_create_nonce( 'json-search' ),
				'search_products_nonce' => wp_create_nonce( 'search-products' ),
				'connectors_pg'         => admin_url( 'admin.php?page=connector&tab=connectors' ),
				'oauth_nonce'           => wp_create_nonce( 'wfco-connector' ),
				'oauth_connectors'      => $this->get_oauth_connector(),
				'errors'                => self::get_error_message(),
				'texts'                 => $this->js_text(),
			);
			wp_localize_script( 'wfco-admin', 'wfcoParams', $data );
		}
	}

	public function is_connector_page( $section = '' ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'single_connector' && '' == $section ) {
			return true;
		}
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'connector' && '' == $section ) {
			return true;
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'connector' && isset( $_GET['section'] ) && $_GET['section'] == $section ) {
			return true;
		}

		return false;
	}

	public function get_oauth_connector() {
		$oauth_connectors = array();
		$all_connector    = WFCO_Admin::get_available_connectors();
		if ( is_array( $all_connector ) && count( $all_connector ) > 0 ) {
			foreach ( $all_connector as $source_slug => $addons ) {
				if ( $addons->is_activated() ) {
					$connector = $source_slug::get_instance();
					if ( $connector->is_oauth() ) {
						$oauth_connectors[] = $source_slug;
					}
				}
			}
		}

		return $oauth_connectors;
	}

	private static function load_connector_screens( $response_data ) {
		foreach ( $response_data as $slug => $data ) {
			WFCO_Connector_Screen_Factory::create( $slug, $data );
		}

		return WFCO_Connector_Screen_Factory::getAll();
	}

	public static function get_available_connectors() {

		$transient = new WooFunnels_Transient();
		$data      = $transient->get_transient( 'get_available_connectors' );

		if ( ! empty( $data ) && is_array( $data ) ) {
			$data = apply_filters( 'wfco_connectors_loaded', $data );

			return self::load_connector_screens( $data );
		}

		$connector_api = new WFCO_Connector_api();
		$response_data = $connector_api->set_action( 'get_available_connectors' )->get()->get_package();
		if ( is_array( $response_data ) ) {
			$transient->set_transient( 'get_available_connectors', $response_data, 3 * HOUR_IN_SECONDS );
		}

		$response_data = apply_filters( 'wfco_connectors_loaded', $response_data );

		return self::load_connector_screens( $response_data );;
	}


	public static function get_plugins() {
		return apply_filters( 'all_plugins', get_plugins() );
	}

	public static function get_error_message() {
		$errors['WFAB_ENCODE'][100] = __( 'Connector not found' );
		$errors['WFAB_ENCODE'][101] = __( 'AutoBot license is required in order to install a connector' );
		$errors['WFAB_ENCODE'][102] = __( 'AutoBot license is invalid, kindly contact woofunnels team.' );
		$errors['WFAB_ENCODE'][103] = __( 'AutoBot license is expired, kindly renew and activate it first.' );

		return $errors;
	}

	public function js_text() {
		$data = array(
			'text_copied'             => __( 'Text Copied', 'woofunnels' ),
			'sync_title'              => __( 'Sync Connector', 'woofunnels' ),
			'sync_text'               => __( 'All the data of this Connector will be Synced.', 'woofunnels' ),
			'sync_wait'               => __( 'Please Wait...', 'woofunnels' ),
			'sync_progress'           => __( 'Sync in progress...', 'woofunnels' ),
			'sync_success_title'      => __( 'Connector Synced', 'woofunnels' ),
			'sync_success_text'       => __( 'We have detected change in the connector during syncing. Please Re-save your Automations/Campaign.', 'woofunnels' ),
			'oops_title'              => __( 'Oops', 'woofunnels' ),
			'oops_text'               => __( 'There was some error. Please try again later.', 'woofunnels' ),
			'delete_int_title'        => __( 'There was some error. Please try again later.', 'woofunnels' ),
			'delete_int_text'         => __( 'There was some error. Please try again later.', 'woofunnels' ),
			'update_int_prompt_title' => __( 'Connector Updated', 'woofunnels' ),
			'delete_int_prompt_title' => __( 'Delete Connector', 'woofunnels' ),
			'delete_int_prompt_text'  => __( 'All the action, tasks, logs of this connector will be deleted.', 'woofunnels' ),
			'delete_int_wait_title'   => __( 'Please Wait...', 'woofunnels' ),
			'delete_int_wait_text'    => __( 'Disconnecting the connector ...', 'woofunnels' ),
			'delete_int_success'      => __( 'Connector Disconnected', 'woofunnels' ),
			'update_btn'              => __( 'Update', 'woofunnels' ),
			'update_btn_process'      => __( 'Updating...', 'woofunnels' ),
			'connect_btn_process'     => __( 'Connecting...', 'woofunnels' ),
			'install_success_title'   => __( 'Connector Installed Successfully', 'woofunnels-autobot-automation' ),
			'connect_success_title'   => __( 'Connected Successfully', 'woofunnels-autobot-automation' ),
		);

		return $data;
	}

	public function connector_page() {
		if ( isset( $_GET['page'] ) && 'connector' === $_GET['page'] ) {
			include_once( $this->admin_path . '/view/connector-admin.php' );
		}

	}

	public function admin_footer_text( $footer_text ) {
		if ( WFCO_Common::is_load_admin_assets( 'all' ) ) {
			return '';
		}

		return $footer_text;
	}

	public function update_footer( $footer_text ) {
		if ( WFCO_Common::is_load_admin_assets( 'all' ) ) {
			return '';
		}

		return $footer_text;
	}

	/**
	 * Hooked over 'plugin_action_links_{PLUGIN_BASENAME}' WordPress hook to add deactivate popup support
	 *
	 * @param array $links array of existing links
	 *
	 * @return array modified array
	 */
	public function plugin_actions( $links ) {
		$links['deactivate'] .= '<i class="woofunnels-slug" data-slug="' . WFCO_PLUGIN_BASENAME . '"></i>';

		return $links;
	}

	public function tooltip( $text ) {
		?>
        <span class="wfco-help"><i class="icon"></i><div class="helpText"><?php echo $text; ?></div></span>
		<?php
	}

	/**
	 * Remove all the notices in our dashboard pages as they might break the design.
	 */
	public function maybe_remove_all_notices_on_page() {
		if ( isset( $_GET['page'] ) && 'connector' == $_GET['page'] && isset( $_GET['section'] ) ) {
			remove_all_actions( 'admin_notices' );
		}
	}


	public function check_db_version() {

		$get_db_version = get_option( '_wfco_db_version', '0.0.0' );

		if ( version_compare( WFCO_DB_VERSION, $get_db_version, '>' ) ) {

			//needs checking
			global $wpdb;
			include_once plugin_dir_path( WFCO_PLUGIN_FILE ) . 'db/tables.php';
			$tables = new WFCO_DB_Tables( $wpdb );

			$tables->add_if_needed();

			update_option( '_wfco_db_version', WFCO_DB_VERSION, true );
		}

	}

}

