<?php
/**
 * Currency per Product for WooCommerce - Crons Class
 *
 * @version 1.4.0
 * @since   1.0.0
 * @author  Tyche Softwares
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_CPP_Crons' ) ) :

	/**
	 * Main Alg_WC_CPP_Crons Class
	 *
	 * @class   Alg_WC_CPP_Crons
	 */
	class Alg_WC_CPP_Crons {

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 * @todo    [dev] maybe schedule on plugin activation only
		 */
		public function __construct() {
			// Exchange rates.
			if ( 'auto' === apply_filters( 'alg_wc_cpp', 'manual', 'value_exchange_rate_update' ) ) {
				add_action( 'init', array( $this, 'schedule_update_exchange_rates' ) );
				add_action( 'admin_init', array( $this, 'schedule_update_exchange_rates' ) );
				add_action( 'alg_wc_cpp_update_exchange_rates', array( $this, 'update_exchange_rates' ) );
			}
			// "Sort by price" sorting and "Filter Products by Price" widget.
			if ( 'yes' === get_option( 'alg_wc_cpp_sort_by_converted_price', 'no' ) || 'yes' === get_option( 'alg_wc_cpp_filter_by_converted_price', 'no' ) ) {
				add_action( 'init', array( $this, 'schedule_calculate_all_products_prices' ) );
				add_action( 'admin_init', array( $this, 'schedule_calculate_all_products_prices' ) );
				add_action( 'alg_wc_cpp_calculate_all_products_prices', 'alg_wc_cpp_calculate_all_products_prices' );
			}
		}

		/**
		 * Schedule event.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 *
		 * @param string $event_hook        Name of event hook.
		 * @param string $selected_interval Interval at which to run the cron.
		 * @param string $cron_time_option  Cron time.
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
		 * Schedule calculation of all product prices.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 * @todo    [feature] maybe add option to change `$selected_interval`
		 */
		public function schedule_calculate_all_products_prices() {
			$this->schedule_event( 'alg_wc_cpp_calculate_all_products_prices', 'hourly', 'alg_wc_cpp_calculate_all_products_prices_cron_time' );
		}

		/**
		 * Schedule updation of exchange rates.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 */
		public function schedule_update_exchange_rates() {
			$this->schedule_event( 'alg_wc_cpp_update_exchange_rates', get_option( 'alg_wc_cpp_exchange_rate_update_rate', 'daily' ), 'alg_wc_cpp_exchange_rate_cron_time' );
		}

		/**
		 * Update exchange rates.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 *
		 * @param string $interval Interval.
		 */
		public function update_exchange_rates( $interval ) {
			alg_wc_cpp_update_exchange_rates();
			if ( 'yes' === get_option( 'alg_wc_cpp_sort_by_converted_price', 'no' ) || 'yes' === get_option( 'alg_wc_cpp_filter_by_converted_price', 'no' ) ) {
				// "Sort by price" sorting and "Filter Products by Price" widget.
				alg_wc_cpp_calculate_all_products_prices();
			}
		}

	}

endif;

return new Alg_WC_CPP_Crons();
