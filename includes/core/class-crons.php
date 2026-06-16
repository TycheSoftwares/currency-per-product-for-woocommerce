<?php
/**
 * Currency per Product for WooCommerce.
 *
 * Crons Class — schedules and handles recurring WP-Cron events.
 *
 * @author      Tyche Softwares
 * @package     CPP/Core
 * @category    Classes
 * @since       1.0.0
 */

namespace Tyche\CPP\Core;

use Tyche\CPP\Functions\Functions;
use Tyche\CPP\Functions\Exchange_Rate_Functions;

defined( 'ABSPATH' ) || exit;

/**
 * Crons
 *
 * Registers WP-Cron schedules for automatic exchange-rate updates and
 * converted-price recalculations.
 */
class Crons {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// "Sort by price" sorting and "Filter Products by Price" widget.
		if ( Functions::get_advanced_setting( 'sort_by_converted_price', false ) || Functions::get_advanced_setting( 'filter_by_converted_price', false ) ) {
			add_action( 'init', array( $this, 'schedule_calculate_all_products_prices' ) );
			add_action( 'admin_init', array( $this, 'schedule_calculate_all_products_prices' ) );
			add_action( 'alg_wc_cpp_calculate_all_products_prices', array( Functions::class, 'calculate_all_products_prices' ) );
		}
	}

	/**
	 * Schedules a recurring WP-Cron event for the given hook and interval,
	 * and clears any previously scheduled events using a different interval.
	 *
	 * @param string $event_hook        The hook name for the event to schedule.
	 * @param string $selected_interval The desired WP-Cron interval (e.g. 'hourly', 'daily').
	 * @param string $cron_time_option  The option name used to store the next event timestamp.
	 *
	 * @since 1.4.0
	 */
	public function schedule_event( $event_hook, $selected_interval, $cron_time_option ) {
		$update_intervals = array( 'hourly', 'twicedaily', 'daily' );

		foreach ( $update_intervals as $interval ) {
			$event_timestamp = wp_next_scheduled( $event_hook, array( $interval ) );

			if ( $selected_interval === $interval ) {
				update_option( $cron_time_option, $event_timestamp );
			}

			if ( ! $event_timestamp && $selected_interval === $interval ) {
				wp_schedule_event( time(), $selected_interval, $event_hook, array( $selected_interval ) );
			} elseif ( $event_timestamp && $selected_interval !== $interval ) {
				wp_unschedule_event( $event_timestamp, $event_hook, array( $interval ) );
			}
		}
	}

	/**
	 * Schedules the hourly cron for recalculating all product prices.
	 *
	 * @since 1.4.0
	 */
	public function schedule_calculate_all_products_prices() {
		$this->schedule_event( 'alg_wc_cpp_calculate_all_products_prices', 'hourly', 'alg_wc_cpp_calculate_all_products_prices_cron_time' );
	}
}

return new Crons();
