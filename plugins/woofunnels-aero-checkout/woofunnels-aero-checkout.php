<?php
/**
 * Plugin Name: Aero: Custom WooCommerce Checkout Pages
 * Plugin URI: https://buildwoofunnels.com
 * Description: AeroCheckout lets you build highly optimized checkout page. Choose from list of growing templates to create dedicated order pages or swap your native checkout with conversion friendly checkout template.
 * Version: 1.9.3
 * Author: WooFunnels
 * Author URI: https://buildwoofunnels.com
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woofunnels-aero-checkout
 *
 * Requires at least: 4.9
 * Tested up to: 5.2.3
 * WC requires at least: 3.3
 * WC tested up to: 3.7
 * WooFunnels: true
 *
 * Aero: Custom WooCommerce Checkout Pages is free software.
 * You can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Aero: Custom WooCommerce Checkout Pages is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Aero: Custom WooCommerce Checkout Pages. If not, see <http://www.gnu.org/licenses/>.
 */


defined( 'ABSPATH' ) || exit;


final class WFACP_Core {

	private static $ins = null;
	private static $_registered_entity = array(
		'active'   => array(),
		'inactive' => array(),
	);
	public $is_dependency_exists = true;

	/**
	 * @var WFACP_Template_loader
	 */
	public $template_loader;

	/**
	 * @var WFACP_public
	 */
	public $public;

	/**
	 * @var WFACP_Customizer
	 */
	public $customizer;

	/**
	 * @var WFACP_WooFunnels_Support
	 */
	public $support;

	/**
	 * Using protected method no one create new instance this class
	 * WFACP_Core constructor.
	 */
	protected function __construct() {

		$this->definition();
		$this->do_dependency_check();
		/**
		 * Initiates and loads WooFunnels start file
		 */
		if ( true === $this->is_dependency_exists ) {
			$this->load_core_classes();

			/**
			 * Loads common file
			 */
			$this->load_commons();
		}
	}

	private function definition() {
		define( 'WFACP_VERSION', '1.9.3' );
		define( 'WFACP_BWF_VERSION', '1.8.5' );
		define( 'WFACP_MIN_WP_VERSION', '4.9' );
		define( 'WFACP_MIN_WC_VERSION', '3.3' );
		define( 'WFACP_SLUG', 'wfacp' );
		define( 'WFACP_TEXTDOMAIN', 'woofunnels-aero-checkout' );
		define( 'WFACP_FULL_NAME', 'Aero: Custom WooCommerce Checkout Pages' );
		define( 'WFACP_PLUGIN_FILE', __FILE__ );
		define( 'WFACP_PLUGIN_DIR', __DIR__ );
		define( 'WFACP_WEB_FONT_PATH', __DIR__ . '/assets/google-web-fonts' );

		define( 'WFACP_TEMPLATE_COMMON', plugin_dir_path( WFACP_PLUGIN_FILE ) . '/public/template-common' );
		define( 'WFACP_TEMPLATE_DIR', plugin_dir_path( WFACP_PLUGIN_FILE ) . '/public/templates' );

		define( 'WFACP_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFACP_PLUGIN_FILE ) ) );

		define( 'WFACP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

		( defined( 'WFACP_IS_DEV' ) && true === WFACP_IS_DEV ) ? define( 'WFACP_VERSION_DEV', time() ) : define( 'WFACP_VERSION_DEV', WFACP_VERSION );
	}

	private function do_dependency_check() {
		include_once WFACP_PLUGIN_DIR . '/woo-includes/woo-functions.php';
		if ( ! wfacp_is_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'wc_not_installed_notice' ) );
			$this->is_dependency_exists = false;
		}
	}

	private function load_core_classes() {
		/** Setting Up WooFunnels Core */
		require_once( 'start.php' );
	}

	private function load_commons() {

		require WFACP_PLUGIN_DIR . '/includes/class-wfacp-common-helper.php';
		require WFACP_PLUGIN_DIR . '/includes/class-wfacp-common.php';
		require WFACP_PLUGIN_DIR . '/includes/class-wfacp-xl-support.php';
		require WFACP_PLUGIN_DIR . '/includes/class-compatibilities.php';
		require WFACP_PLUGIN_DIR . '/includes/class-wfacp-ajax-controller.php';
		WFACP_Common::init();
		$this->load_hooks();
	}

	private function load_hooks() {
		/**
		 * Initialize Localization
		 */
		add_action( 'init', array( $this, 'localization' ) );
		add_action( 'plugins_loaded', array( $this, 'load_classes' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'register_classes' ), 2 );
		add_action( 'activated_plugin', array( $this, 'redirect_on_activation' ) );

	}

	/**
	 * @return null|WFACP_Core
	 */
	public static function get_instance() {
		if ( is_null( self::$ins ) ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function register( $short_name, $class, $overrides = null ) {
		//Ignore classes that have been marked as inactive
		if ( in_array( $class, self::$_registered_entity['inactive'] ) ) {
			return;
		}
		//Mark classes as active. Override existing active classes if they are supposed to be overridden
		$index = array_search( $overrides, self::$_registered_entity['active'] );
		if ( false !== $index ) {
			self::$_registered_entity['active'][ $index ] = $class;
		} else {
			self::$_registered_entity['active'][ $short_name ] = $class;
		}

		//Mark overridden classes as inactive.
		if ( ! empty( $overrides ) ) {
			self::$_registered_entity['inactive'][] = $overrides;
		}
	}

	public function localization() {
		load_plugin_textdomain( 'woofunnels-aero-checkout', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	public function load_classes() {

		global $woocommerce;
		global $wp_version;
		if ( ! version_compare( $wp_version, WFACP_MIN_WP_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'wp_version_check_notice' ) );

			return false;
		}
		if ( ! version_compare( $woocommerce->version, WFACP_MIN_WC_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'wc_version_check_notice' ) );

			return false;
		}

		if ( is_admin() ) {
			require WFACP_PLUGIN_DIR . '/admin/class-wfacp-admin.php';
			require WFACP_PLUGIN_DIR . '/admin/class-insert-page.php';
		}

		require WFACP_PLUGIN_DIR . '/admin/class-wfacp-wizard.php';

		require WFACP_PLUGIN_DIR . '/includes/class-dynamic-merge-tags.php';
		require WFACP_PLUGIN_DIR . '/includes/class-wfacp-customizer.php';
		require WFACP_PLUGIN_DIR . '/includes/class-wfacp-template-loader.php';
		require WFACP_PLUGIN_DIR . '/public/class-wfacp-public.php';
		require WFACP_PLUGIN_DIR . '/includes/class-mobile-detect.php';

	}

	public function register_classes() {
		$load_classes = self::get_registered_class();
		if ( is_array( $load_classes ) && count( $load_classes ) > 0 ) {
			foreach ( $load_classes as $access_key => $class ) {
				$this->$access_key = $class::get_instance();
			}

			do_action( 'wfacp_loaded' );
		}
	}

	public static function get_registered_class() {
		return self::$_registered_entity['active'];
	}

	public function redirect_on_activation( $plugin ) {
		if ( wfacp_is_woocommerce_active() && class_exists( 'WooCommerce' ) ) {
			if ( $plugin == plugin_basename( __FILE__ ) ) {
				$g_setting                        = get_option( '_wfacp_global_settings', [] );
				$g_setting['update_rewrite_slug'] = 'yes';
				update_option( '_wfacp_global_settings', $g_setting );
				wp_redirect( add_query_arg( array(
					'page' => 'wfacp',
				), admin_url( 'admin.php' ) ) );
				exit;
			}
		}
	}

	public function wc_version_check_notice() {
		?>
        <div class="error">
            <p>
				<?php
				/* translators: %1$s: Min required woocommerce version */
				printf( __( '<strong> Attention: </strong>AeroCheckout requires WooCommerce version %1$s or greater. Kindly update the WooCommerce plugin.', 'woofunnels-aero-checkout' ), WFACP_MIN_WC_VERSION );
				?>
            </p>
        </div>
		<?php
	}

	public function wp_version_check_notice() {
		?>
        <div class="error">
            <p>
				<?php
				/* translators: %1$s: Min required woocommerce version */
				printf( __( '<strong> Attention: </strong>AeroCheckout requires WordPress version %1$s or greater. Kindly update the WordPress.', 'woofunnels-aero-checkout' ), WFACP_MIN_WP_VERSION );
				?>
            </p>
        </div>
		<?php
	}


	public function wc_not_installed_notice() {
		?>
        <div class="error">
            <p>
				<?php
				echo __( '<strong> Attention: </strong>WooCommerce is not installed or activated. AeroCheckout is a WooCommerce Extension and would only work if WooCommerce is activated. Please install the WooCommerce Plugin first.', 'woofunnels-aero-checkout' );
				?>
            </p>
        </div>
		<?php
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
	 * To avoid cloning of current class
	 */
	protected function __clone() {
	}

}

function WFACP_Core() {

	return WFACP_Core::get_instance();
}

WFACP_Core();
