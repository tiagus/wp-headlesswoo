<?php

abstract class WFCO_Model {
	static $primary_key = 'id';
	static $count = 20;

	static function get( $value ) {
		global $wpdb;

		return $wpdb->get_row( self::_fetch_sql( $value ), ARRAY_A );
	}

	private static function _fetch_sql( $value ) {
		global $wpdb;
		$sql = sprintf( 'SELECT * FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );

		return $wpdb->prepare( $sql, $value );
	}

	private static function _table() {
		global $wpdb;
		$tablename = strtolower( get_called_class() );

		$tablename = str_replace( 'wfco_model_', 'wfco_', $tablename );

		return $wpdb->prefix . $tablename;
	}

	static function insert( $data ) {
		global $wpdb;
		$wpdb->insert( self::_table(), $data );
	}

	static function update( $data, $where ) {
		global $wpdb;
		$wpdb->update( self::_table(), $data, $where );
	}

	static function delete( $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );

		return $wpdb->query( $wpdb->prepare( $sql, $value ) );
	}

	static function insert_id() {
		global $wpdb;

		return $wpdb->insert_id;
	}

	static function now() {
		return self::time_to_date( time() );
	}

	static function time_to_date( $time ) {
		return gmdate( 'Y-m-d H:i:s', $time );
	}

	static function date_to_time( $date ) {
		return strtotime( $date . ' GMT' );
	}

	static function num_rows() {
		global $wpdb;

		return $wpdb->num_rows;
	}

	static function count_rows( $dependency = null ) {
		global $wpdb;

		$sql = 'SELECT COUNT(*) FROM ' . self::_table();
		if ( ! is_null( $dependency ) ) {
			$sql = 'SELECT COUNT(*) FROM ' . self::_table() . ' INNER JOIN ' . $dependency['dependency_table'] . ' on ' . self::_table() . '.' . $dependency['dependent_col'] . '=' . $dependency['dependency_table'] . '.' . $dependency['dependency_col'] . ' WHERE ' . $dependency['dependency_table'] . '.' . $dependency['col_name'] . '=' . $dependency['col_value'];
			if ( isset( $dependency['connector_id'] ) ) {
				$sql = 'SELECT COUNT(*) FROM ' . self::_table() . ' INNER JOIN ' . $dependency['dependency_table'] . ' on ' . self::_table() . '.' . $dependency['dependent_col'] . '=' . $dependency['dependency_table'] . '.' . $dependency['dependency_col'] . ' WHERE ' . $dependency['dependency_table'] . '.' . $dependency['col_name'] . '=' . $dependency['col_value'] . ' AND ' . $dependency['connector_table'] . '.' . $dependency['connector_col'] . '=' . $dependency['connector_id'];
			}
		}

		return $wpdb->get_var( $sql );
	}

	static function get_specific_rows( $where_key, $where_value ) {
		global $wpdb;
		$table_name = self::_table();
		$results    = $wpdb->get_results( "SELECT * FROM $table_name WHERE $where_key = '$where_value'", ARRAY_A );

		return $results;
	}

	static function get_rows( $only_query = false, $connector_ids = array() ) {
		global $wpdb;

		$table_name     = self::_table();
		$page_number    = 1;
		$count_per_page = self::$count;
		$next_offset    = ( $page_number - 1 ) * $count_per_page;
		$sql_query      = $wpdb->prepare( "SELECT * FROM $table_name ORDER BY c_date DESC LIMIT %d OFFSET %d", $count_per_page, $next_offset );

		if ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) {
			$page_number = $_GET['paged'];
			$next_offset = ( $page_number - 1 ) * $count_per_page;
			$sql_query   = $wpdb->prepare( "SELECT * FROM $table_name ORDER BY c_date DESC LIMIT %d OFFSET %d", $count_per_page, $next_offset );
		}

		if ( isset( $_GET['status'] ) && 'all' !== $_GET['status'] ) {
			$status    = $_GET['status'];
			$status    = ( 'active' == $status ) ? 1 : 2;
			$sql_query = $wpdb->prepare( "SELECT * FROM $table_name WHERE status = %d ORDER BY c_date DESC LIMIT %d OFFSET %d", $status, $count_per_page, $next_offset );
		}

		if ( ( isset( $_GET['paged'] ) && $_GET['paged'] > 0 ) && ( isset( $_GET['status'] ) && '' !== $_GET['status'] ) ) {
			$page_number = $_GET['paged'];
			$next_offset = ( $page_number - 1 ) * $count_per_page;
			$status      = $_GET['status'];
			$sql_query   = $wpdb->prepare( "SELECT * FROM $table_name WHERE status = %d ORDER BY c_date DESC LIMIT %d OFFSET %d", $status, $count_per_page, $next_offset );
		}

		$result = $wpdb->get_results( $sql_query, ARRAY_A );

		return $result;
	}

	static function get_results( $query ) {
		global $wpdb;
		$query   = str_replace( '{table_name}', self::_table(), $query );
		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results;
	}

	static function delete_multiple( $query ) {
		global $wpdb;
		$query = str_replace( '{table_name}', self::_table(), $query );
		$wpdb->query( $query );
	}

	static function update_multiple( $query ) {
		global $wpdb;
		$query = str_replace( '{table_name}', self::_table(), $query );
		$wpdb->query( $query );
	}
}
