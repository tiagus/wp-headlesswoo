<?php

defined( 'ABSPATH' ) || exit;

final class WFACP_Customizer {

	private static $ins = null;
	/**
	 * @var WFACP_Template_Common
	 */
	private $template_ins = null;
	private $template_path = '';
	private $template = null;
	private $wfacp_id = 0;

	protected function __construct() {
		$this->wfacp_id        = WFACP_Common::get_id();
		$this->template_path   = WFACP_PLUGIN_DIR . '/templates';
		$this->template_assets = WFACP_PLUGIN_URL . '/assets';
		$this->maybe_load_customizer();
	}

	public function maybe_load_customizer() {

		if ( WFACP_Common::is_customizer() ) {
			$this->wfacp_id = absint( $_REQUEST['wfacp_id'] );
			//set checkout page id when customizer is open
			WFACP_Common::set_id( $this->wfacp_id );
			add_filter( 'customize_loaded_components', 'WFACP_Common::remove_menu_support', 99 );
			add_filter( 'customize_register', [ $this, 'remove_sections' ], 110 );
			add_action( 'customize_controls_print_styles', [ $this, 'print_customizer_styles' ] );
			add_filter( 'customize_control_active', [ $this, 'control_filter' ], 10, 2 );
			add_action( 'customize_controls_enqueue_scripts', [ $this, 'enqueue_scripts' ], 9999 );
			add_action( 'customize_controls_enqueue_scripts', [ $this, 'maybe_remove_script_customizer' ], 10000 );
			add_filter( 'customize_register', [ $this, 'add_sections' ], 101 );
			add_action( 'customize_save_validation_before', [ $this, 'add_sections' ], 101 );
			add_action( 'wfacp_footer_before_print_scripts', [ $this, 'add_loader_to_custmizer' ] );
			add_action( 'admin_enqueue_scripts', array( $this, 'dequeue_unnecessary_customizer_scripts' ), 999 );
		}
	}

	public static function get_instance() {
		if ( self::$ins == null ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Locate Template using offer meta data also setup data
	 *
	 * @param $wfacp_id
	 *
	 * @return mixed|null
	 */
	public function load_template( $wfacp_id ) {
		if ( empty( $wfacp_id ) || $wfacp_id == 0 ) {
			return null;
		}
		if ( $this->template_ins instanceof WFACP_Template_Common ) {
			return $this->template_ins;
		}

		$this->wfacp_id  = $wfacp_id;
		$template        = WFACP_Common::get_page_design( $wfacp_id );
		$this->template  = $template['selected'];
		$locate_template = WFACP_Core()->template_loader->locate_template( $this->template, $template['selected_type'] );
		if ( false !== $locate_template ) {
			$template_file = $locate_template;
		}
		if ( ! file_exists( $template_file ) ) {
			return null;
		}
		do_action( 'wfacp_template_load', $this->wfacp_id );
		// include abstract wrapper class for templates
		include __DIR__ . '/class-wfacp-template-common.php';
		$this->template_ins = include $template_file;
		if ( ! method_exists( $this->template_ins, 'get_slug' ) ) {
			return null;
		}
		$this->template_ins->set_wfacp_id( $this->wfacp_id );
		$this->template_ins->set_data();

		if ( isset( $_REQUEST['customized'] ) ) {
			$change_set = json_decode( $_REQUEST['customized'], true );
			if ( ! is_null( $change_set ) ) {
				$this->template_ins->set_changeset( $change_set );
			}
		}

		return $this->template_ins;
	}


	/**
	 * Remove any unwanted default controls.
	 *
	 * @param object $wp_customize
	 *
	 * @return bool
	 */
	public function remove_sections( $wp_customize ) {
		global $wp_customize;
		/**
		 * @var $wp_customize WP_Customize_Manager
		 */

		$wp_customize->remove_panel( 'themes' );
		$wp_customize->remove_control( 'active_theme' );
		/** Mesmerize theme */
		$wp_customize->remove_section( 'mesmerize-pro' );

		do_action( 'wfacp_remove_panel_section' );

		return true;
	}

	/**
	 * Depreciated - Storefront calling settings direct
	 * Removes the core 'Widgets' or 'Menus' panel from the Customizer.
	 *
	 * @param array $components Core Customizer components list.
	 *
	 * @return array (Maybe) modified components list.
	 */
	public function remove_extra_panels( $components ) {
		/** widgets */
		$i = array_search( 'widgets', $components );
		if ( false !== $i ) {
			unset( $components[ $i ] );
		}

		/** menus */
		$i = array_search( 'nav_menus', $components );
		if ( false !== $i ) {
			unset( $components[ $i ] );
		}

		return $components;
	}

	/**
	 * Depreciated - Storefront calling settings direct
	 * Remove any unwanted default panels.
	 *
	 * @param object $wp_customize
	 *
	 * @return bool
	 */
	public function remove_panels( $wp_customize ) {
		global $wp_customize;
		$wp_customize->get_panel( 'nav_menus' )->active_callback = '__return_false';
		$wp_customize->remove_panel( 'widgets' );

		return true;
	}

	public function control_filter( $active, $control ) {
		return $this->template_ins->control_filter( $control );
	}

	public function enqueue_scripts() {

		wp_enqueue_style( 'wfacp_customizer_common_style', $this->template_assets . '/css/wfacp-customizer-style.css', array(), WFACP_VERSION_DEV );
		wp_enqueue_script( 'wfacp_customizer_common', $this->template_assets . '/js/customizer-common.js', array( 'customize-controls' ), WFACP_VERSION_DEV, true );
		$template_fields = $this->template_ins->get_customizer_fields();
		$pd              = array();

		wp_localize_script( 'wfacp_customizer_common', 'wfacp_customizer', array(
				'is_loaded'   => 'yes',
				'wfacp_id'    => $this->wfacp_id,
				'fields'      => $template_fields,
				'preview_msg' => __( 'This is a checkout preview for styling purposes. Some of the checkout functions such as showing payment methods or applying coupons  or updating of prices based on shipping methods are restricted. Click here to see the checkout. <a href="' . get_the_permalink( WFACP_Common::get_id() ) . '" target="__blank">Click here to see the checkout.</a>', 'woofunnels-aero-checkout' ),
				'pd'          => $pd,

			) );
	}

	public function maybe_remove_script_customizer() {
		global $wp_scripts, $wp_styles;
		$accepted_scripts = [
			0  => 'heartbeat',
			1  => 'customize-controls',
			2  => 'wfacpkirki_field_dependencies',
			3  => 'customize-widgets',
			4  => 'storefront-plugin-install',
			7  => 'jquery-ui-button',
			8  => 'customize-views',
			9  => 'media-editor',
			10 => 'media-audiovideo',
			11 => 'mce-view',
			12 => 'image-edit',
			13 => 'code-editor',
			14 => 'csslint',
			15 => 'wp-color-picker',
			16 => 'wp-color-picker-alpha',
			17 => 'selectWoo',
			18 => 'wfacpkirki-script',
			19 => 'wfacp-control-responsive-js',
			20 => 'updates',
			21 => 'wfacpkirki_panel_and_section_icons',
			22 => 'wfacpkirki-custom-sections',
			23 => 'wfacp_customizer_common',
			24 => 'acf-input',
			25 => 'code-editor',
		];

		$accepted_styles = [
			0  => 'customize-controls',
			1  => 'customize-widgets',
			2  => 'storefront-plugin-install',
			3  => 'woocommerce_admin_menu_styles',
			4  => 'wfacp-admin-font',
			7  => 'media-views',
			8  => 'imgareaselect',
			9  => 'code-editor',
			10 => 'wp-color-picker',
			11 => 'selectWoo',
			12 => 'wfacpkirki-selectWoo',
			13 => 'wfacpkirki-styles',
			14 => 'wfacp-control-responsive-css',
			15 => 'wfacpkirki-custom-sections',
			16 => 'code-editor',
			17 => 'editor-buttons',
		];

		$wp_scripts->queue = $accepted_scripts;
		$wp_styles->queue  = $accepted_styles;
	}

	public function print_customizer_styles() {
		echo '<style>#customize-theme-controls li#accordion-panel-nav_menus,#customize-theme-controls li#accordion-panel-widgets,#customize-theme-controls li#accordion-section-astra-pro,#customize-controls .customize-info .customize-help-toggle,.ast-control-tooltip {display: none !important;}</style>';
	}

	public function add_sections( $wp_customize ) {
		$this->template_ins->get_section( $wp_customize );
	}


	/**
	 * @return WFACP_Template_Common
	 */
	public function get_template_instance() {
		return $this->template_ins;
	}

	public function add_loader_to_custmizer() {
		?>
        <div class="wfacpkirki-customizer-loading-wrapper wfacp_customizer_loader">
            <span class="wfacpkirki-customizer-loading"></span>
        </div>
		<?php
	}

	public function dequeue_unnecessary_customizer_scripts() {

		if ( isset( $_REQUEST['wfacp_customize'] ) && $_REQUEST['wfacp_customize'] == 'loaded' && isset( $_REQUEST['wfacp_id'] ) && $_REQUEST['wfacp_id'] > 0 ) {

			/**
			 * wp-titan framework add these color pickers, that breaks our customizer page
			 */

			wp_deregister_script( 'wp-color-picker-alpha' );
			wp_dequeue_script( 'wp-color-picker-alpha' );

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
	WFACP_Core::register( 'customizer', 'WFACP_Customizer' );
}
