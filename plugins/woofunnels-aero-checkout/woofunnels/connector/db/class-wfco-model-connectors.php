<?php

class WFCO_Model_Connectors extends WFCO_Model {
	static $primary_key = 'ID';

	public static function count_rows( $dependency = null ) {
		global $wpdb;
		$table_name = self::_table();
		$sql        = 'SELECT COUNT(*) FROM ' . $table_name;

		if ( isset( $_GET['status'] ) && 'all' !== $_GET['status'] ) {
			$status = $_GET['status'];
			$status = ( 'active' == $status ) ? 1 : 2;
			$sql    = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE status = %d", $status );
		}

		return $wpdb->get_var( $sql );
	}

	private static function _table() {
		global $wpdb;
		$table_name = strtolower( get_called_class() );
		$table_name = str_replace( 'wfco_model_', 'wfco_', $table_name );

		return $wpdb->prefix . $table_name;
	}

	/**
	 * Return Task detail with its meta details
	 *
	 * @param $task_id
	 */
	public static function get_connector_with_data( $task_id ) {

		$task = self::get( $task_id );
		if ( ! is_array( $task ) || empty( $task ) ) {
			return [];
		}
		$task['meta'] = WFCO_Model_ConnectorMeta::get_connector_meta( $task_id );

		return $task;

	}
}
