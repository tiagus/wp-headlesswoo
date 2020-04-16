<?php

abstract class BWF_CO {

	public static $GET = 1;
	public static $POST = 2;
	public static $DELETE = 3;
	public static $PUT = 4;
	public static $PATCH = 5;

	/** @var string Connector folder directory */
	public $dir = __DIR__;

	/** @var string AutoBot integration class name */
	public $autobot_int_slug = '';

	/** @var null Nice name */
	public $nice_name = null;

	/** @var bool Connector has settings */
	public $is_setting = true;

	/** @var string Public directory URL */
	protected $connector_url = '';

	/** @var array Connector keys which are tracked during syncing and update */
	protected $keys_to_track = [];
	protected $sync = false;
	protected $is_oauth = false;

	/**
	 * Loads all calls of current connector
	 */
	public function load_calls() {
		$resource_dir = $this->dir . '/calls';
		if ( @file_exists( $resource_dir ) ) {
			foreach ( glob( $resource_dir . '/class-*.php' ) as $filename ) {
				$call_class = require_once( $filename );
				if ( method_exists( $call_class, 'get_instance' ) ) {
					$call_obj = $call_class::get_instance();
					$call_obj->set_connector_slug( $this->get_slug() );
					WFCO_Load_Connectors::register_calls( $call_obj );
				}
			}
		}

		do_action( 'wfab_' . $this->get_slug() . '_actions_loaded' );
	}

	public function get_slug() {
		return sanitize_title( get_class( $this ) );
	}

	/**
	 * Handles the settings form submission
	 *
	 * @param $data
	 * @param string $type
	 *
	 * @return int
	 */
	public function handle_settings_form( $data, $type = 'save' ) {
		$old_data = [];
		$new_data = [];
		$is_valid = $this->validate_settings_fields( $data, $type );
		if ( false == $is_valid ) {
			$new_data = __( 'Validation Failed', 'woofunnels-core' );

			return $new_data;

		}

		switch ( $type ) {
			case 'save':
				$new_data = $this->get_api_data( $data );
				if ( ! is_array( $new_data ) || count( $new_data ) == 0 ) {
					return $new_data;
				}

				$id = WFCO_Common::save_connector_data( $new_data, $this->get_slug(), 1 );

				return $id;
				break;
			case 'update':
				$saved_data = WFCO_Common::$connectors_saved_data;
				$old_data   = $saved_data[ $this->get_slug() ];
				$new_data   = $this->get_api_data( $data );
				break;
			case 'sync':
				$saved_data = WFCO_Common::$connectors_saved_data;
				$old_data   = $saved_data[ $this->get_slug() ];
				$new_data   = $this->get_api_data( $old_data );
				break;
		}

		if ( ! is_array( $new_data ) || count( $new_data ) == 0 ) {
			return $new_data;
		}

		WFCO_Common::update_connector_data( $new_data, $data['id'] );
		$data['data_changed'] = 0;
		$is_data_changed      = $this->track_sync_changes( $new_data, $old_data );
		if ( $is_data_changed ) {
			do_action( 'change_in_connector_data', $this->get_slug() );
			$data['data_changed'] = 1;
		}

		return $data;
	}

	/**
	 * @param $data
	 * @param string $type
	 *
	 * @return boolean
	 */
	protected function validate_settings_fields( $data, $type = 'save' ) {
		return true;
	}

	protected function get_api_data( $data ) {

		return $data;
	}

	protected function track_sync_changes( $new_data, $old_data ) {
		$has_changes = false;

		if ( empty( $this->keys_to_track ) || empty( $new_data ) || empty( $old_data ) ) {
			return $has_changes;
		}

		foreach ( $this->keys_to_track as $key ) {
			$str1 = isset( $new_data[ $key ] ) ? $new_data[ $key ] : '';
			$str2 = isset( $old_data[ $key ] ) ? $old_data[ $key ] : '';
			$str1 = is_array( $str1 ) ? json_encode( $str1 ) : $str1;
			$str2 = is_array( $str2 ) ? json_encode( $str2 ) : $str2;

			$diff = strcmp( $str1, $str2 );
			if ( 0 === $diff ) {
				continue;
			}
			$has_changes = true;
			break;
		}

		return $has_changes;
	}

	public function get_settings_view() {
		$file_path = $this->dir . '/views/settings.php';
		if ( file_exists( $file_path ) ) {
			include $file_path;
		}

	}

	public function get_image() {
		return $this->connector_url . '/views/logo.png';
	}


	public function has_settings() {

		return $this->is_setting;
	}

	public function is_syncable() {
		return $this->sync;

	}

	public function is_oauth() {
		return $this->is_oauth;
	}


	public function setting_view() {
		?>
        <script type="text/html" id="tmpl-connector-<?php echo $this->get_slug(); ?>">
			<?php $this->get_settings_view(); ?>
        </script>
		<?php
	}
}
