<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once 'class-wc-order-export-table-abstract.php';

class WC_Table_Schedules extends WC_Order_Export_Table_Abstract {

	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'job', 'woocommerce-order-export' ),
			'plural'   => __( 'jobs', 'woocommerce-order-export' ),
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
                   value="<?php _e( 'Add job', 'woocommerce-order-export' ); ?>" id="add_schedule">
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

		$this->items = (array) WC_Order_Export_Manage::get( WC_Order_Export_Manage::EXPORT_SCHEDULE );

		foreach ( $this->items as $index => $item ) {
			$this->items[ $index ]['id'] = $index;
		}
	}

	public function get_columns() {
		$columns                        = parent::get_columns();
		$columns['active']              = __( 'Active', 'woocommerce-order-export' );
		$columns['title']               = __( 'Title', 'woocommerce-order-export' );
		$columns['format']              = __( 'Format', 'woocommerce-order-export' );
		$columns['destination']         = __( 'Destination', 'woocommerce-order-export' );
		$columns['destination_details'] = __( 'Destination Details', 'woocommerce-order-export' );
		$columns['recurrence']          = __( 'Recurrence', 'woocommerce-order-export' );
		$columns['last_run']            = __( 'Last run', 'woocommerce-order-export' );
		$columns['next_event']          = __( 'Next run', 'woocommerce-order-export' );
		$columns['actions']             = __( 'Actions', 'woocommerce-order-export' );

		return $columns;
	}

	function column_default( $item, $column_name ) {
		$active = ! isset( $item['active'] ) || $item['active'];
		switch ( $column_name ) {
			case 'active':
				if ( $active ) {
					echo '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Enabled',
							'woocommerce' ) . '">' . esc_html__( 'Yes', 'woocommerce' ) . '</span>';
				} else {
					echo '<span class="status-disabled tips" data-tip="' . esc_attr__( 'Disabled',
							'woocommerce' ) . '">-</span>';
				}
				break;
			case 'title':
				return '<a href="admin.php?page=wc-order-export&tab=schedules&wc_oe=edit_schedule&schedule_id=' . $item['id'] . '">' . $item[ $column_name ] . '</a>';
				break;
			case 'recurrence':
				$r         = '';
				$day_names = WC_Order_Export_Manage::get_days();
				if ( isset( $item['schedule'] ) ) {
					if ( $item['schedule']['type'] == 'schedule-1' ) {
						$r = __( 'Run', 'woocommerce-order-export' ) . ' ';
						if ( isset( $item['schedule']['weekday'] ) ) {
							$days = array_keys( $item['schedule']['weekday'] );
							foreach ( $days as $k => $d ) {
								$days[ $k ] = $day_names[ $d ];
							}
							$r .= __( "on", 'woocommerce-order-export' ) . ' ' . implode( ', ', $days );
						}
						if ( isset( $item['schedule']['run_at'] ) ) {
							$r .= ' ' . __( 'at', 'woocommerce-order-export' ) . ' ' . $item['schedule']['run_at'];
						}
						//nothing selected 
						if ( empty( $days ) ) {
							$r = __( 'Never', 'woocommerce-order-export' );
						}
					} else if ( $item['schedule']['type'] == 'schedule-2' ) {
						if ( $item['schedule']['interval'] == 'first_day_month' ) {
							$r = __( "First Day Every Month", 'woocommerce-order-export' );
						} elseif ( $item['schedule']['interval'] == 'first_day_quarter' ) {
							$r = __( "First Day Every Quarter", 'woocommerce-order-export' );
						} elseif ( $item['schedule']['interval'] == 'custom' ) {
							$r = sprintf( __( "To run every %s minute(s)", 'woocommerce-order-export' ),
								$item['schedule']['custom_interval'] );
						} else {
							foreach ( wp_get_schedules() as $name => $schedule ) {
								if ( $item['schedule']['interval'] == $name ) {
									$r = $schedule['display'];
								}
							}
						}
					} else if ( $item['schedule']['type'] == 'schedule-3' ) {
						$times = explode( ',', $item['schedule']['times'] );
						foreach ( $times as $k => $t ) {
							$a = explode( " ", $t );
							if ( count( $a ) == 2 ) {
								$times[ $k ] = $day_names[ $a[0] ] . " " . $a[1];
							} // replace days
						}
						$r = __( 'Run on', 'woocommerce-order-export' ) . ' <br>' . implode( ',<br>', $times );
					}
				}

				return $r;
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
			case 'last_run':
				if ( ! $active ) {
					return __( 'Inactive', 'woocommerce-order-export' );
				}
				if ( isset( $item['schedule']['last_run'] ) ) {
					$last_run_local = $item['schedule']['last_run'];
					if ( $last_run_local ) {
						return gmdate( 'M j Y', $last_run_local ) . ' ' . __( 'at',
								'woocommerce-order-export' ) . ' ' . gmdate( 'G:i', $last_run_local );
					} else {
						return __( '', 'woocommerce-order-export' );
					}
				} else {
					return __( 'Not executed', 'woocommerce-order-export' );
				}
			case 'next_event':
				if ( ! $active ) {
					return __( 'Inactive', 'woocommerce-order-export' );
				}
				if ( isset( $item['schedule']['next_run'] ) ) {
					$next_run_local = $item['schedule']['next_run'];
					if ( $next_run_local ) {
						return gmdate( 'M j Y', $next_run_local ) . ' ' . __( 'at',
								'woocommerce-order-export' ) . ' ' . gmdate( 'G:i', $next_run_local );
					} else {
						return __( '', 'woocommerce-order-export' );
					}
				} else {
					return __( 'Not installed', 'woocommerce-order-export' );
				}
			case 'actions':
				return
					'<div class="btn-edit button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Edit',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-edit"></span></div>&nbsp;' .
					'<div class="btn-clone button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Clone',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-admin-page"></span></div>&nbsp;' .
					'<div class="btn-trash button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Delete',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-trash"></span></div>&nbsp;&nbsp;' .
					'<div class="btn-export button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Export',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-download"></span></div>';
				break;
			default:

				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
		}

	}
}
