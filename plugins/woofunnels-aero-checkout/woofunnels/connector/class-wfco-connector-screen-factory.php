<?php

abstract class WFCO_Connector_Screen_Factory {

	private static $screens = [];


	public static function create( $slug, $data ) {
		self::$screens[ $slug ] = new WFCO_Connector_Screen( $slug, $data );
	}

	public static function get( $screen ) {
		return self::$screens[ $screen ];
	}

	public static function getAll() {
		return self::$screens;
	}

	public static function print_screens() {
		$all_connector = self::getAll();
		if ( empty( $all_connector ) ) {
			WFCO_Admin::get_available_connectors();
			$all_connector = self::getAll();
		}

		if ( is_array( $all_connector ) && count( $all_connector ) > 0 ) {
			echo '<div class="wfco-col-group">';
			foreach ( $all_connector as $source_slug => $connector ) {
				$connector->print_card();
			}
			echo '</div>';
		} else {
			?>
            <label style="text-align: center;padding-top: 10px;"><?php echo __( 'No Integration to add', 'woofunnels' ); ?></label>
			<?php
		}
	}

}