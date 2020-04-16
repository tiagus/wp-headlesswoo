<?php

/**
 * Contains the logic for deactivation popups
 * @since 1.0.0
 * @author woofunnels
 * @package WooFunnels
 */
class WooFunnels_Connector_Deactivate {

	public static $deactivation_str;

	/**
	 * Initialization of hooks where we prepare the functionality to ask use for survey
	 */
	public static function init() {

		self::load_all_str();

		add_action( 'admin_footer', array( __CLASS__, 'maybe_load_deactivate_options' ) );

		add_action( 'wp_ajax_woofunnels_connector_submit_uninstall_reason', array( __CLASS__, '_submit_uninstall_reason_action' ) );
	}

	/**
	 * Localizes all the string used
	 */
	public static function load_all_str() {

		self::$deactivation_str = array(
			'deactivation-share-reason'            => __( 'Type of deactivation', 'woofunnels' ),
			'deactivation-modal-button-cancel'     => _x( 'Cancel', 'the text of the cancel button of the plugin deactivation dialog box.', 'woofunnels' ),
			'deactivation-modal-button-submit'     => __( 'Submit & Deactivate', 'woofunnels' ),
			'deactivation-modal-button-confirm'    => __( 'Yes - Deactivate', 'woofunnels' ),
			'deactivation-modal-button-deactivate' => __( 'Deactivate', 'woofunnels' ),
			'temporary'                            => __( 'Temporary deactivate plugin', 'woofunnels' ),
			'permanent'                            => __( 'Permanently deactivate plugin', 'woofunnels' ),
		);
	}

	/**
	 * Checking current page and pushing html, js and css for this task
	 * @global string $pagenow current admin page
	 * @global array $VARS global vars to pass to view file
	 */
	public static function maybe_load_deactivate_options() {

		global $pagenow;

		if ( $pagenow == 'plugins.php' ) {
			global $VARS;

			$VARS = array(
				'slug'    => '',
				'reasons' => self::deactivate_options(),
			);
			include_once dirname( dirname( __FILE__ ) ) . '/views/woofunnels-connector-deactivate-modal.phtml';
		}
	}

	/**
	 * deactivation reasons in array format
	 * @return array reasons array
	 * @since 1.0.0
	 */
	public static function deactivate_options() {

		$connector_deactivation_options = array(
			array(
				'id'                => 1,
				'text'              => self::load_str( 'temporary' ),
				'input_type'        => '',
				'input_placeholder' => '',
			),
			array(
				'id'                => 2,
				'text'              => self::load_str( 'permanent' ),
				'input_type'        => '',
				'input_placeholder' => '',
			),
		);

		$uninstall_reasons['default'] = $connector_deactivation_options;

		$uninstall_reasons = apply_filters( 'woofunnels_connector_uninstall_reasons', $uninstall_reasons );

		return $uninstall_reasons;
	}

	/**
	 * get exact str against the slug
	 *
	 * @param type $slug
	 *
	 * @return type
	 */
	public static function load_str( $slug ) {
		return self::$deactivation_str[ $slug ];
	}

	/**
	 * Called after the user has submitted his reason for deactivating the plugin.
	 *
	 * @since  1.1.2
	 */
	public static function _submit_uninstall_reason_action() {

		if ( ! isset( $_POST['reason_id'] ) ) {
			exit;
		}

		$reason_info = isset( $_REQUEST['reason_info'] ) ? trim( stripslashes( $_REQUEST['reason_info'] ) ) : '';

		$plugin_connector_slug = $_POST['plugin_connector_slug'];

		$reason = array(
			'id'   => $_POST['reason_id'],
			'info' => substr( $reason_info, 0, 128 ),
		);

		$licenses = WooFunnels_addons::get_installed_plugins();

		$version = 'NA';
		if ( $licenses && count( $licenses ) > 0 ) {
			foreach ( $licenses as $key => $license ) {

				if ( $key == $_POST['plugin_basename'] ) {
					$version = $license['Version'];
				}
			}
		}

		$deactivations = array(
			$_POST['plugin_basename'] . '(' . $version . ')' => $reason,
		);

		$license_info = isset( $_REQUEST['licenses'] ) ? json_decode( stripslashes( $_REQUEST['licenses'] ) ) : '';

		$licenses_info_pass = array();

		if ( $license_info && is_object( $license_info ) ) {

			if ( property_exists( $license_info, sha1( $_POST['plugin_basename'] ) ) ) {
				$basename           = sha1( $_POST['plugin_basename'] );
				$licenses_info_pass = $license_info->$basename;
			} elseif ( property_exists( $license_info, ( $_POST['plugin_basename'] ) ) ) {
				$basename           = $_POST['plugin_basename'];
				$licenses_info_pass = $license_info->$basename;
			}
		}

		if ( 2 == intval( $_POST['reason_id'] ) ) { // user opted for permanently deactivation of the connector plugin
			// Delete all connector things like tasks, logs, actions in automation
			do_action( 'connector_disconnected', $plugin_connector_slug, true );
		}

		//      WooFunnels_API::post_deactivation_data( $deactivations, $licenses_info_pass );
		// Print '1' for successful operation.
		echo 1;
		exit;
	}

}

//initialization
WooFunnels_Connector_Deactivate::init();
