<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class WC_Order_Export_Table_Abstract extends WP_List_Table {

	var $current_destination = '';

	private $_actions = array(
		'activate',
		'deactivate',
		'delete',
	);

	protected function get_bulk_actions() {
		$actions               = array();
		$actions['activate']   = __( 'Activate', 'woocommerce-order-export' );
		$actions['deactivate'] = __( 'Deactivate', 'woocommerce-order-export' );
		$actions['delete']     = __( 'Delete', 'woocommerce-order-export' );

		return $actions;
	}

	/**
	 * Output the report
	 */
	public function output() {
		$this->prepare_items();
		?>

        <div class="wp-wrap">
			<?php
			$this->display();
			?>
        </div>
		<?php
	}

	protected function bulk_actions( $which = '' ) {
		?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php if ( $this->has_items() ): ?>
                <div class="actions bulkactions">
					<?php parent::bulk_actions( $which ); ?>
                </div>
			<?php endif; ?>
            <br class="clear"/>
        </div>
		<?php
	}


	protected function display_tablenav( $which ) {
	}

	public function get_columns() {
		$columns       = array();
		$columns['cb'] = '<input type="checkbox" />';

		return $columns;
	}

	protected function display_active_column_default( $item ) {
		if ( isset( $item['active'] ) && $item['active'] ) {
			echo '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Enabled',
					'woocommerce' ) . '">' . esc_html__( 'Yes', 'woocommerce' ) . '</span>';
		} else {
			echo '<span class="status-disabled tips" data-tip="' . esc_attr__( 'Disabled',
					'woocommerce' ) . '">-</span>';
		}
	}

	protected function column_default( $item, $column_name ) {
	}

	/**
	 * Handles the checkbox column output.
	 *
	 */
	protected function column_cb( $item ) {
		?>
        <label class="screen-reader-text" for="cb-select-<?php echo $item['id']; ?>"></label>
        <input type="checkbox" name="profiles" id="cb-select-<?php echo $item['id']; ?>"
               value="<?php echo $item['id']; ?>"/>
		<?php
	}

}
