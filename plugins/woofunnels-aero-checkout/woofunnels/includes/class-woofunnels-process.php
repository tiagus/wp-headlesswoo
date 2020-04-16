<?php

/**
 * Basic process class that detect request and pass to respective class
 *
 * @author woofunnels
 * @package WooFunnels
 */
class WooFunnels_Process {

	private static $ins = null;
	public $in_update_messages = array();

	/**
	 * Initiate hooks
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'parse_request_and_process' ), 14 );
		add_filter( 'admin_notices', array( $this, 'maybe_show_advanced_update_notification' ), 999 );

		add_action( 'admin_head', array( $this, 'register_in_update_plugin_message' ) );
		add_action( 'admin_notices', array( 'WooFunnels_Admin_Notifications', 'render' ) );

		add_action( 'admin_init', array( $this, 'maybe_add_license_check_schedule' ) );
		add_action( 'woofunnels_license_check', array( 'WooFunnels_License_Controller', 'license_check' ) );

	}

	public static function get_instance() {
		if ( self::$ins == null ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function parse_request_and_process() {
		//Initiating the license instance to handle submissions  (submission can redirect page two that can cause "header already sent" issue to be arised)
		// Initiating this to over come that issue
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'woofunnels' && isset( $_GET['tab'] ) && $_GET['tab'] == 'licenses' ) {
			WooFunnels_licenses::get_instance();
		}

		//Handling Optin
		if ( isset( $_GET['woofunnels-optin-choice'] ) && isset( $_GET['_woofunnels_optin_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_woofunnels_optin_nonce'], 'woofunnels_optin_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheating huh?', 'woofunnels' ) );
			}

			$optin_choice = sanitize_text_field( $_GET['woofunnels-optin-choice'] );
			if ( $optin_choice == 'yes' ) {
				WooFunnels_optIn_Manager::Allow_optin();
				if ( isset( $_GET['ref'] ) ) {
					WooFunnels_optIn_Manager::update_optIn_referer( filter_input( INPUT_GET, 'ref' ) );
				}
			} else {
				WooFunnels_optIn_Manager::block_optin();
			}

			do_action( 'woofunnels_after_optin_choice', $optin_choice );
		}

		//Initiating the license instance to handle submissions  (submission can redirect page two that can cause "header already sent" issue to be arised)
		// Initiating this to over come that issue
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'woofunnels' && isset( $_GET['tab'] ) && $_GET['tab'] == 'support' && isset( $_POST['woofunnels_submit_support'] ) ) {
			$instance_support = WooFunnels_Support::get_instance();

			if ( filter_input( INPUT_POST, 'choose_addon' ) == '' || filter_input( INPUT_POST, 'comments' ) == '' ) {
				$instance_support->validation = false;

			}
		}
	}

	public function maybe_show_advanced_update_notification() {
		$screen            = get_current_screen();
		$plugins_installed = WooFunnels_Addons::get_installed_plugins();
		if ( is_object( $screen ) && ( 'plugins.php' == $screen->parent_file || 'index.php' == $screen->parent_file ) ) {
			$plugins = get_site_transient( 'update_plugins' );
			if ( isset( $plugins->response ) && is_array( $plugins->response ) ) {
				$plugins = array_keys( $plugins->response );
				foreach ( $plugins_installed as $basename => $installed ) {

					if ( is_array( $plugins ) && count( $plugins ) > 0 && in_array( $basename, $plugins ) ) {
						?>
                        <div class="notice notice-warning is-dismissible">
                            <p>
								<?php
								_e( sprintf( 'Attention: There is an update available of <strong>%s</strong> plugin. &nbsp;<a href="%s" class="">Go to updates</a>', $installed['Name'], admin_url( 'plugins.php?s=woofunnel&plugin_status=all' ) ), 'woo-thank-you-page-nextmove-lite' );
								?>
                            </p>
                        </div>
						<?php
					}
				}
			}
		}
	}

	public function register_in_update_plugin_message() {

		$get_in_update_message_support = apply_filters( 'woofunnels_in_update_message_support', array() );

		if ( empty( $get_in_update_message_support ) ) {
			return;
		}
		$this->in_update_messages = $get_in_update_message_support;
		foreach ( $get_in_update_message_support as $basename => $changelog_file ) {
			add_action( 'in_plugin_update_message-' . $basename, array( $this, 'in_plugin_update_message' ), 10, 2 );

		}
	}

	/**
	 * Show plugin changes on the plugins screen. Code adapted from W3 Total Cache.
	 *
	 * @param array $args Unused parameter.
	 * @param stdClass $response Plugin update response.
	 */
	public function in_plugin_update_message( $args, $response ) {

		$changelog_path  = $this->in_update_messages[ $args['plugin'] ];
		$current_version = $args['Version'];
		$upgrade_notice  = $this->get_upgrade_notice( $response->new_version, $changelog_path, $current_version );

		echo apply_filters( 'woofunnels_in_plugin_update_message', $upgrade_notice ? '</br>' . wp_kses_post( $upgrade_notice ) : '', $args['plugin'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

		echo '<style>span.woofunnels_plugin_upgrade_notice::before {
    content: ' . '"\f463";
    margin-right: 6px;
    vertical-align: bottom;
    color: #f56e28;
    display: inline-block;
    font: 400 20px/1 dashicons;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    vertical-align: top;
}</style>';

	}

	/**
	 * Get the upgrade notice from WordPress.org.
	 *
	 * @param string $version WooCommerce new version.
	 *
	 * @return string
	 */
	protected function get_upgrade_notice( $version, $path, $current_version ) {

		$transient_name = 'woofunnels_upgrade_notice_' . $version . md5( $path );
		$upgrade_notice = get_transient( $transient_name );

		if ( false === $upgrade_notice ) {
			$response = wp_safe_remote_get( $path );
			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'], $version, $current_version );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		return $upgrade_notice;
	}

	/**
	 * Parse update notice from readme file.
	 *
	 * @param string $content WooCommerce readme file content.
	 * @param string $new_version WooCommerce new version.
	 *
	 * @return string
	 */
	private function parse_update_notice( $content, $new_version, $current_version ) {
		$version_parts     = explode( '.', $new_version );
		$check_for_notices = array(
			$version_parts[0] . '.0', // Major.
			$version_parts[0] . '.0.0', // Major.
			$version_parts[0] . '.' . $version_parts[1], // Minor.
		);

		$notice_regexp  = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $new_version ) . '\s*=|$)~Uis';
		$upgrade_notice = '';

		foreach ( $check_for_notices as $check_version ) {
			if ( version_compare( $current_version, $check_version, '>' ) ) {
				continue;
			}

			$matches = null;
			if ( preg_match( $notice_regexp, $content, $matches ) ) {

				$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

				if ( version_compare( trim( $matches[1] ), $check_version, '=' ) ) {
					$upgrade_notice .= '<span class="woofunnels_plugin_upgrade_notice">';

					foreach ( $notices as $index => $line ) {
						$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
					}

					$upgrade_notice .= '</span>';
				}
				break;
			}
		}

		return wp_kses_post( $upgrade_notice );
	}

	public function maybe_add_license_check_schedule() {


		if ( ! wp_next_scheduled( 'woofunnels_license_check' ) ) {
			wp_schedule_event( time(), 'daily', 'woofunnels_license_check' );
		}
	}

}

