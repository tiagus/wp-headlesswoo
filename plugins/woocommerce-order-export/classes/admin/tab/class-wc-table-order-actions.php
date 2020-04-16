<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once 'class-wc-order-export-table-abstract.php';

class WC_Table_Order_Actions extends WC_Order_Export_Table_Abstract {

	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'action', 'woocommerce-order-export' ),
			'plural'   => __( 'actions', 'woocommerce-order-export' ),
			'ajax'     => true,
		) );
	}

	public function display_tablenav( $which ) {
		if ( 'top' != $which ) {
			return;
		}
		?>
        <div style="margin-top: 10px;">
            <input type="button" class="button-secondary"
                   value="<?php _e( 'Add job', 'woocommerce-order-export' ); ?>" data-action="add-order-action">
        </div>
        <br/>
		<?php
		$this->bulk_actions( $which );
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = WC_Order_Export_Manage::get( WC_Order_Export_Manage::EXPORT_ORDER_ACTION );

		foreach ( $this->items as $index => $item ) {
			$this->items[ $index ]['id'] = $index;
		}
	}

	public function get_columns() {
		$columns                        = parent::get_columns();
		$columns['active']              = __( 'Active', 'woocommerce-order-export' );
		$columns['title']               = __( 'Title', 'woocommerce-order-export' );
		$columns['format']              = __( 'Format', 'woocommerce-order-export' );
		$columns['from_status']         = __( 'From status', 'woocommerce-order-export' );
		$columns['to_status']           = __( 'To status', 'woocommerce-order-export' );
		$columns['destination']         = __( 'Destination', 'woocommerce-order-export' );
		$columns['destination_details'] = __( 'Destination Details', 'woocommerce-order-export' );
		$columns['actions']             = __( 'Actions', 'woocommerce-order-export' );

		return $columns;
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'active':
				if ( ! isset( $item['active'] ) || $item['active'] ) {
					echo '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Enabled',
							'woocommerce' ) . '">' . esc_html__( 'Yes', 'woocommerce' ) . '</span>';
				} else {
					echo '<span class="status-disabled tips" data-tip="' . esc_attr__( 'Disabled',
							'woocommerce' ) . '">-</span>';
				}
				break;
			case 'title':
				return '<a href="admin.php?page=wc-order-export&tab=order_actions&wc_oe=edit_action&action_id=' . $item['id'] . '">' . $item[ $column_name ] . '</a>';
			case 'from_status':
			case 'to_status':
				$data         = array();
				$all_statuses = wc_get_order_statuses();

				$statuses = isset( $item[ $column_name ] ) ? $item[ $column_name ] : array();
				if ( empty( $statuses ) ) {
					$data[] = __( 'Any', 'woocommerce-order-export' );
				} else {
					foreach ( $statuses as $status ) {
						$data[] = $all_statuses[ $status ];
					}
				}

				return implode( ', ', $data );
			case 'destination':
				$al = array(
					'ftp'    => __( 'FTP', 'woocommerce-order-export' ),
					'sftp'   => __( 'SFTP', 'woocommerce-order-export' ),
					'http'   => __( 'HTTP POST', 'woocommerce-order-export' ),
					'email'  => __( 'Email', 'woocommerce-order-export' ),
					'folder' => __( 'Directory', 'woocommerce-order-export' ),
				);
				if ( isset( $item['destination']['type'] ) ) {
					if ( ! is_array( $item['destination']['type'] ) ) {
						$item['destination']['type'] = array( $item['destination']['type'] );
					}
					$type = array_map( function ( $type ) use ( $al ) {
						return $al[ $type ];
					}, $item['destination']['type'] );

					return implode( $type, ', ' );
				}

				return '';
			case 'destination_details':
				if ( isset( $item['destination']['type'] ) ) {
					if ( ! is_array( $item['destination']['type'] ) ) {
						$item['destination']['type'] = array( $item['destination']['type'] );
					}

					$details = array();
					foreach ( $item['destination']['type'] as $destination ) {
						if ( $destination == 'http' ) {
							$details[] = esc_html( $item['destination']['http_post_url'] );
						}
						if ( $destination == 'email' ) {
							$email_details   = array();
							$email_details[] = __( 'Subject:',
									'woocommerce-order-export' ) . ' ' . esc_html( $item['destination']['email_subject'] );
							if ( ! empty( $item['destination']['email_recipients'] ) ) {
								$email_details[] = __( 'To:',
										'woocommerce-order-export' ) . ' ' . esc_html( $item['destination']['email_recipients'] );
							}
							if ( ! empty( $item['destination']['email_recipients_cc'] ) ) {
								$email_details[] = __( 'CC:',
										'woocommerce-order-export' ) . ' ' . esc_html( $item['destination']['email_recipients_cc'] );
							}
							$details[] = join( "<br>", $email_details );
						}
						if ( $destination == 'ftp' ) {
							$details[] = esc_html( $item['destination']['ftp_user'] ) . "@" . esc_html( $item['destination']['ftp_server'] ) . $item['destination']['ftp_path'];
						}
						if ( $destination == 'sftp' ) {
							$details[] = esc_html( $item['destination']['sftp_user'] ) . "@" . esc_html( $item['destination']['sftp_server'] ) . $item['destination']['sftp_path'];
						}
						if ( $destination == 'folder' ) {
							$details[] = esc_html( $item['destination']['path'] );
						}
					}

					return implode( $details, ', ' );
				}

				return '';
			case 'actions':
				return "<div class='button-secondary' title='" . __( 'Edit',
						'woocommerce-order-export' ) . "'   data-id='{$item['id']}' data-action='edit-order-action'><span class='dashicons dashicons-edit'></span></div>&nbsp;" .
				       "<div class='button-secondary' title='" . __( 'Clone',
						'woocommerce-order-export' ) . "'   data-id='{$item['id']}' data-action='clone-order-action'><span class='dashicons dashicons-admin-page'></span></div>&nbsp;" .
				       "<div class='button-secondary' title='" . __( 'Delete',
						'woocommerce-order-export' ) . "' data-id='{$item['id']}' data-action='delete-order-action'><span class='dashicons dashicons-trash'></span></div>&nbsp;";
				break;
			default:
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
		}
	}
}
