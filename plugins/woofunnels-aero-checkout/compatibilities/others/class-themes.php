<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WFACP_Plugins_Compatibility {

	public function __construct() {
		add_action( 'wfacp_loaded', array( $this, 'register_fake_kirki' ) );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'override_theme_customizer_functionality' ), 999 );

		add_action( 'wfacp_footer_before_print_scripts', [ $this, 'remove_flatsome_hooks' ] );

		/**
		 * Add compatibility For davinci Theme
		 */
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'remove_checkout_hooks' ] );

		/**
		 * Customizer compatibility for buzzstorepro theme
		 */
		add_action( 'after_setup_theme', function () {

			if ( class_exists( 'WFACP_Common' ) && WFACP_Common::is_customizer() ) {
				remove_action( 'customize_register', 'buzzstorepro_customize_register' );

			}
		} );

		/**
		 * Customizer compatibility for Easy Google Fonts plugin
		 */
		add_action( 'plugins_loaded', function () {

			if ( class_exists( 'WFACP_Common' ) && WFACP_Common::is_customizer() ) {

				if ( class_exists( 'EGF_Customize_Manager' ) ) {
					remove_action( 'customize_register', array( EGF_Customize_Manager::get_instance(), 'register_font_control_type' ) );

				}
			}

		}, 9999 );

		add_action( 'init', function () {
			if ( class_exists( 'WFACP_Common' ) && WFACP_Common::is_customizer() ) {
				remove_action( 'customize_register', 'et_divi_customize_register' );
			}

			if ( class_exists( 'Kirki_Init' ) && WFACP_Common::is_customizer() ) {
				global $wp_filter;
				foreach ( $wp_filter['wp_loaded']->callbacks as $key => $val ) {

					if ( 1 !== $key ) {
						continue;
					}

					foreach ( $val as $innerkey => $innerval ) {
						if ( isset( $innerval['function'] ) && is_array( $innerval['function'] ) ) {
							if ( is_a( $innerval['function']['0'], 'Kirki_Init' ) ) {
								$ki_customizer = $innerval['function']['0'];
								remove_action( 'wp_loaded', array( $ki_customizer, 'add_to_customizer' ), 1 );
								break;
							}
						}
					}
				}
			}

		}, 9999 );

		add_action( 'wfacp_checkout_page_not_found', [ $this, 'our_not_checkout_pages_actions' ] );
		add_action( 'wfacp_checkout_page_found', [ $this, 'our_checkout_actions' ] );
		add_action( 'woocommerce_review_order_after_shipping', [ $this, 'remove_shoptimizer_checkout_custom_field' ] );

		add_action( 'wfacp_remove_panel_section', function () {
			global $wp_customize;
			$wp_customize->remove_panel( 'shoptimizer_panel_layout' );
			$wp_customize->remove_panel( 'header' );
			$wp_customize->remove_panel( 'style' );
			$wp_customize->remove_panel( 'blog' );
			$wp_customize->remove_panel( 'woocommerce' );
			$wp_customize->remove_section( 'footer' );
		} );

	}

	public function our_not_checkout_pages_actions() {

		if ( function_exists( 'et_divi_add_customizer_css' ) ) {
			et_divi_add_customizer_css();
		}


	}

	public function flatsome_hooks() {
		remove_action( 'wp_head', 'flatsome_custom_header_js' );
		remove_action( 'wp_head', 'flatsome_google_fonts_lazy' );
	}

	public function our_checkout_actions() {
		remove_action( 'woocommerce_before_checkout_form', 'shoptimizer_cart_progress', 5 );

		add_filter( 'wp_get_custom_css', function ( $css ) {
			if ( WFACP_Common::get_id() > 0 ) {
				return '';
			}

			return $css;
		} );
		$this->flatsome_hooks();
	}

	public function remove_shoptimizer_checkout_custom_field() {
		remove_action( 'woocommerce_review_order_after_submit', 'shoptimizer_checkout_custom_field', 15 );

	}

	public function register_fake_kirki() {
		$status = apply_filters( 'wfacp_customizer_i10_error', false );
		if ( false == $status ) {
			return;
		}
		$is_wfacp_customizer = WFACP_Common::is_customizer();
		if ( false === $is_wfacp_customizer ) {
			return;
		}
		include_once __DIR__ . '/class-kirki.php';
		add_action( 'customize_controls_init', array( $this, 'remove_actions_filters' ) );

	}

	public function remove_actions_filters() {
		$is_wfacp_customizer = WFACP_Common::is_customizer();
		if ( false === $is_wfacp_customizer ) {
			return;
		}
		remove_action( 'customize_controls_print_styles', 'flatsome_enqueue_customizer_stylesheet' );
	}

	public function remove_flatsome_hooks() {
		if ( WFACP_Common::get_id() > 0 ) {
			remove_action( 'wp_footer', 'flatsome_account_login_lightbox', 10 );
			remove_action( 'wp_footer', 'flatsome_mobile_menu', 7 );
			remove_action( 'wp_footer', 'flatsome_lazy_add_icons_css', 10 );
			remove_action( 'wp_footer', 'flatsome_footer_scripts' );
			remove_action( 'wp_footer', 'ux_block_frontend' );
		}
	}

	public function override_theme_customizer_functionality() {
		$is_wfacp_customizer = WFACP_Common::is_customizer();
		if ( false === $is_wfacp_customizer ) {
			return;
		}
		/** Astra */
		if ( defined( 'ASTRA_THEME_VERSION' ) ) {
			wp_dequeue_script( 'astra-color-alpha' );
			wp_dequeue_script( 'astra-responsive-slider' );
			wp_dequeue_style( 'astra-responsive-slider' );
			wp_dequeue_style( 'astra-responsive-css' );
		}
	}

	public function remove_checkout_hooks() {

		if ( defined( 'ADSW_THEME_VERSION' ) ) {
			remove_action( 'woocommerce_before_checkout_form', 'adswth_before_login_checkout_form', 8 );
			remove_action( 'woocommerce_before_checkout_form', 'adswth_after_login_checkout_form', 15 );
			remove_filter( 'woocommerce_form_field_args', 'adswth_form_field_args', 10, 3 );
		}


		if ( function_exists( 'ccfw_criticalcss' ) ) {
			remove_action( 'wp_head', 'ccfw_criticalcss', 5 );
		}
		if ( function_exists( 'ccfw_filter_style_loader_tag' ) ) {
			remove_filter( 'style_loader_tag', 'ccfw_filter_style_loader_tag', 10, 4 );
		}

		if ( function_exists( 'buddyboss_scripts_styles' ) ) {

			add_action( 'wp_enqueue_scripts', function () {
				wp_deregister_script( 'selectboxes' );
				wp_dequeue_script( 'selectboxes' );
			} );


		}
	}

}

new WFACP_Plugins_Compatibility();
