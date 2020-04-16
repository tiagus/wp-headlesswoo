<?php

class WFCO_Model_ConnectorMeta extends WFCO_Model {
	static $primary_key = 'ID';

	public static function get_rows( $only_query = false, $connector_ids = array() ) {
		global $wpdb;

		$table_name = self::_table();

		if ( $only_query ) {
			// For Fetching the meta of connectors
			$connector_count        = count( $connector_ids );
			$string_placeholders    = array_fill( 0, $connector_count, '%s' );
			$placeholders_connector = implode( ', ', $string_placeholders );
			$sql_query              = "SELECT connector_id, meta_key, meta_value FROM $table_name WHERE connector_id IN ($placeholders_connector)";
			$sql_query              = $wpdb->prepare( $sql_query, $connector_ids );
		}

		$result = $wpdb->get_results( $sql_query, ARRAY_A );

		return $result;
	}

	private static function _table() {
		global $wpdb;
		$table_name = strtolower( get_called_class() );

		$table_name = str_replace( 'wfco_model_', 'wfco_', $table_name );

		return $wpdb->prefix . $table_name;
	}

	public static function get_connector_meta( $task_id ) {
		if ( 0 == $task_id ) {
			return [];
		}

		global $wpdb;
		$table = self::_table();

		$sql_query = "SELECT * FROM $table WHERE ID =%d";
		$sql_query = $wpdb->prepare( $sql_query, $task_id );
		$result    = $wpdb->get_results( $sql_query, ARRAY_A );
		$meta      = [];
		if ( is_array( $result ) && count( $result ) > 0 ) {
			foreach ( $result as $meta_values ) {
				$key          = $meta_values['meta_key'];
				$meta[ $key ] = maybe_unserialize( $meta_values['meta_value'] );
			}
		}

		return $meta;
	}


}
