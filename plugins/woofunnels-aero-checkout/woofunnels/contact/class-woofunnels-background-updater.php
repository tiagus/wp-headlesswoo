<?php
/**
 * Background Updater
 *
 * @version 1.7.4
 */

defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WC_Background_Process', false ) && function_exists( 'wc' ) && ( strpos( wc()->plugin_path(), 'wp-content/plugins/woocommerce' ) > 0 ) && is_file( wc()->plugin_path() . '/abstracts/class-wc-background-process.php' ) ) {
	include_once WC()->plugin_path() . '/includes/abstracts/class-wc-background-process.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomFunction

}

if ( ! class_exists( 'WC_Background_Process', false ) ) {
	return;
}

/**
 * WooFunnels_Background_Updater Class.
 * Based on WC_Background_Updater concept
 */
class WooFunnels_Background_Updater extends WC_Background_Process {

	const MAX_SAME_OFFSET_THRESHOLD = 5;

	/**
	 * Initiate new background process.
	 *
	 * WooFunnels_Background_Updater constructor.
	 */
	public function __construct() {
		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix = 'bwf_' . get_current_blog_id();
		$this->action = 'updater';
		parent::__construct();

	}


	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {

			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Scheduled event cleared as queue is empty.', 'woofunnels_indexing' );

			return;
		}

		/**
		 * We are saving the last 5 offset value, due to any specific reason if last 5 offsets are same then it might be the time to kill the process.
		 */
		$offsets = $this->get_last_offsets();
		if ( self::MAX_SAME_OFFSET_THRESHOLD === count( $offsets ) ) {
			$unique = array_unique( $offsets );
			if ( 1 === count( $unique ) ) {
				$this->kill_process();
				WooFunnels_Dashboard::$classes['BWF_Logger']->log( sprintf( 'Offset is stuck from last %d cron jobs, terminating the process.', self::MAX_SAME_OFFSET_THRESHOLD ), 'woofunnels_indexing' );

				return;
			}
		}

		$this->manage_last_offsets();
		WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Cron started again!!', 'woofunnels_indexing' );

		/**
		 * Everything looks good, lets roll the indexing
		 */

		$this->handle();
	}

	/**
	 * Overriding parent protected function publically to use outside this class
	 * @return bool
	 */
	public function is_process_running() {
		return parent::is_process_running();
	}

	public function get_last_offsets() {
		return get_option( '_bwf_last_offsets', array() );
	}

	/**
	 * Kill process.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_process() {
		parent::kill_process();
		WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater']->set_upgrade_state( '1' );

	}

	/**
	 * Manage last 5 offsets
	 */
	public function manage_last_offsets() {
		$offsets        = $this->get_last_offsets();
		$current_offset = get_option( '_bwf_offset', 0 );
		if ( self::MAX_SAME_OFFSET_THRESHOLD === count( $offsets ) ) {
			$offsets = array_map( function ( $key ) use ( $offsets ) {
				return isset( $offsets[ $key + 1 ] ) ? $offsets[ $key + 1 ] : 0;
			}, array_keys( $offsets ) );

			$offsets[ self::MAX_SAME_OFFSET_THRESHOLD - 1 ] = $current_offset;
		} else {
			$offsets[ count( $offsets ) ] = $current_offset;
		}

		$this->update_last_offsets( $offsets );

	}

	public function update_last_offsets( $offsets ) {
		update_option( '_bwf_last_offsets', $offsets );
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	public function kill_process_safe() {
		parent::kill_process();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.8; // 80% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		return apply_filters( $this->identifier . '_memory_exceeded', $return );
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function.
	 *
	 * @return string|bool
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	protected function task( $callback ) {

		$result = false;
		if ( is_callable( $callback ) ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Running the callback: ' . print_r( $callback, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$result = (bool) call_user_func( $callback );

			if ( $result ) {
				/**sleep( 5 );*/
				WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Result: $result Need to run again the callback: " . print_r( $callback, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			} else {
				WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Result: $result Finished running the callback: " . print_r( $callback, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}
		} else {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Result: $result Could not find the callback: " . print_r( $callback, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $result ? $callback : false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {

		update_option( '_bwf_offset', 0 );

		WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Background scanning completed for indexing order and creating updating contacts and customers.', 'woofunnels_indexing' );
		do_action( 'bwf_order_index_completed' );
		parent::complete();
	}

	public function maybe_re_dispatch_background_process() {
		if ( 3 !== absint( WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater']->get_upgrade_state() ) ) {
			return;
		}
		if ( $this->is_queue_empty() ) {
			return;
		}
		if ( $this->is_process_running() ) {
			return;
		}

		/**
		 * We are saving the last 5 offset value, due to any specific reason if last 5 offsets are same then it might be the time to kill the process.
		 */
		$offsets = $this->get_last_offsets();
		if ( self::MAX_SAME_OFFSET_THRESHOLD === count( $offsets ) ) {
			$unique = array_unique( $offsets );
			if ( 1 === count( $unique ) ) {
				$this->kill_process();
				WooFunnels_Dashboard::$classes['BWF_Logger']->log( sprintf( 'Offset is stuck from last %d attempts, terminating the process.', self::MAX_SAME_OFFSET_THRESHOLD ), 'woofunnels_indexing' );

				return;
			}
		}

		$this->manage_last_offsets();
		$this->dispatch();
	}
}
