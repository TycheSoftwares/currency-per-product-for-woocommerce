<?php
/**
 * Currency per Product for WooCommerce.
 *
 * Currency Reports Class.
 *
 * @author      Tyche Softwares
 * @package     CPP/Admin
 * @category    Classes
 * @since       1.1.0
 */

namespace Tyche\CPP\Admin;

use Tyche\CPP\Functions\Functions;
use Tyche\CPP\Functions\Exchange_Rate_Functions;

defined( 'ABSPATH' ) || exit;

/**
 * Currency_Reports
 *
 * Hooks into WooCommerce reports to filter order data by currency and
 * convert multi-currency order totals for the analytics stats table.
 */
class Currency_Reports {

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		if ( is_admin() && Functions::get_general_setting( 'currency_reports_enabled', false ) ) {
			add_filter( 'woocommerce_reports_get_order_report_data_args', array( $this, 'filter_reports' ), PHP_INT_MAX, 1 );
			add_filter( 'woocommerce_currency', array( $this, 'change_reports_currency_code' ), PHP_INT_MAX, 1 );
			add_action( 'admin_bar_menu', array( $this, 'add_reports_currency_to_admin_bar' ), PHP_INT_MAX );
			add_filter( 'wc_admin_reports_path', array( $this, 'filter_wc_admin_reports_path' ), PHP_INT_MAX, 3 );
			add_filter( 'alg_wc_cpp_report_sales_by_date_order', array( $this, 'filter_cpp_report_sales_by_date_order' ), PHP_INT_MAX, 2 );
		}

		add_filter( 'woocommerce_analytics_update_order_stats_data', array( $this, 'update_single_order_stats_data' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'update_order_stats_data' ), 10 );
	}

	/**
	 * Gets the meta title for an admin bar node based on the provided currency code.
	 *
	 * @param string $code Currency code, or 'merge' for the merged-all option.
	 * @return string
	 */
	public function get_node_meta_title( $code ) {
		return ( 'merge' === $code
			? __( 'Merge all currencies', 'currency-per-product-for-woocommerce' )
			// Translators: %s is the currency code or the text 'All currencies'.
			: sprintf( __( 'Show reports only in %s', 'currency-per-product-for-woocommerce' ), $code )
		);
	}

	/**
	 * Gets the title for an admin bar node based on the provided currency code.
	 *
	 * @param string $code Currency code, or 'merge' for the merged-all option.
	 * @return string
	 */
	public function get_node_title( $code ) {
		$name = ( 'merge' === $code ? __( 'All currencies', 'currency-per-product-for-woocommerce' ) : $code );
		// Translators: %s is the currency code or the text 'All currencies'.
		return sprintf( __( 'Reports currency: %s', 'currency-per-product-for-woocommerce' ), $name );
	}

	/**
	 * Adds the reports currency options to the WordPress admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
	 */
	public function add_reports_currency_to_admin_bar( $wp_admin_bar ) {
		if ( isset( $_GET['page'] ) && 'wc-reports' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$base_currency = get_option( 'woocommerce_currency' );
			$_current_code = isset( $_GET['currency'] ) ? sanitize_text_field( wp_unslash( $_GET['currency'] ) ) : $base_currency; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$parent        = 'alg_wc_cpp_reports_currency_select';

			$wp_admin_bar->add_node(
				array(
					'parent' => false,
					'id'     => $parent,
					'title'  => $this->get_node_title( $_current_code ),
					'href'   => false,
					'meta'   => array( 'title' => $this->get_node_meta_title( $_current_code ) ),
				)
			);

			// Build list of all known currencies.
			$currencies                   = array();
			$currencies[ $_current_code ] = $_current_code;
			$currencies[ $base_currency ] = $base_currency;
			$wc_currency                  = get_woocommerce_currency();
			if ( '' !== $wc_currency ) {
				$currencies[ $wc_currency ] = $wc_currency;
			}

			foreach ( Functions::get_currencies_setting( 'currencies', array() ) as $currency_entry ) {
				$_code                = $currency_entry['currency'] ?? $base_currency;
				$currencies[ $_code ] = $_code;
			}

			asort( $currencies );
			$currencies['merge'] = __( 'Merge all', 'currency-per-product-for-woocommerce' );

			foreach ( $currencies as $code => $name ) {
				$wp_admin_bar->add_node(
					array(
						'parent' => $parent,
						'id'     => $parent . '_' . $code,
						'title'  => $name,
						'href'   => add_query_arg( 'currency', $code ),
						'meta'   => array( 'title' => $this->get_node_meta_title( $code ) ),
					)
				);
			}
		}
	}

	/**
	 * Changes the WooCommerce currency code when viewing reports filtered by currency.
	 *
	 * @param string $currency The current currency code.
	 * @return string
	 */
	public function change_reports_currency_code( $currency ) {
		if ( isset( $_GET['page'] ) && 'wc-reports' === $_GET['page'] && isset( $_GET['currency'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return ( 'merge' === $_GET['currency'] ? '' : sanitize_text_field( wp_unslash( $_GET['currency'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return $currency;
	}

	/**
	 * Filters the report queries to show only orders in the selected currency.
	 *
	 * @param array $args Query arguments for the WooCommerce report.
	 * @return array
	 */
	public function filter_reports( $args ) {
		if ( isset( $_GET['currency'] ) && 'merge' === $_GET['currency'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $args;
		}
		$args['where_meta'] = array(
			array(
				'meta_key'   => '_order_currency', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => ( isset( $_GET['currency'] ) ? sanitize_text_field( wp_unslash( $_GET['currency'] ) ) : get_option( 'woocommerce_currency' ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'operator'   => '=',
			),
		);
		return $args;
	}

	/**
	 * Redirects WooCommerce's sales-by-date report to our overriding class file.
	 *
	 * @param string $path Current file path for the report.
	 * @param string $name Report name slug.
	 * @return string
	 */
	public function filter_wc_admin_reports_path( $path, $name ) {
		if ( 'sales-by-date' === $name ) {
			$path = plugin_dir_path( __FILE__ ) . 'class-report-sales-by-date.php';
		}
		return $path;
	}

	/**
	 * Returns merged report totals across all currencies when the "All currencies" option is selected.
	 *
	 * @param array  $data     Order report data rows as returned by WooCommerce.
	 * @param object $instance Instance of WC_Report_Sales_By_Date.
	 * @return array
	 */
	public function filter_cpp_report_sales_by_date_order( $data, $instance ) {

		if ( ! ( isset( $_GET['currency'] ) && 'merge' === $_GET['currency'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $data;
		}

		// Query all price items without the SUM function so we can convert each row individually.
		// Caching is disabled to prevent WordPress serving stale data.
		$args = array(
			'data'         => array(
				'_order_total'        => array(
					'type'     => 'meta',
					'function' => '',
					'name'     => 'sales',
				),
				'_order_shipping'     => array(
					'type'     => 'meta',
					'function' => '',
					'name'     => 'shipping',
				),
				'_order_tax'          => array(
					'type'     => 'meta',
					'function' => '',
					'name'     => 'tax',
				),
				'_order_shipping_tax' => array(
					'type'     => 'meta',
					'function' => '',
					'name'     => 'shipping_tax',
				),
				'post_date'           => array(
					'type'     => 'post_data',
					'function' => '',
					'name'     => 'post_date',
				),
				'_order_currency'     => array(
					'type'     => 'meta',
					'function' => '',
					'name'     => 'currency',
				),
			),
			'group_by'     => '',
			'order_by'     => 'post_date ASC',
			'query_type'   => 'get_results',
			'filter_range' => true,
			'nocache'      => true,
			'order_types'  => wc_get_order_types( 'sales-reports' ),
			'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
		);

		$price                    = (array) $instance->get_order_report_data( $args );
		$shop_currency            = get_option( 'woocommerce_currency' );
		$round_off_decimal_points = Functions::get_general_setting( 'round_off_decimal_points', false );
		$currencies_list          = Functions::get_currencies_setting( 'currencies', array() );
		$rates_list               = Functions::get_exchange_rate_setting( 'rates', array() );

		// Convert each non-base-currency order row into the shop currency.

		foreach ( $price as $item ) {
			if ( $item->currency === $shop_currency ) {
				continue;
			}

			$rate = 0;
			foreach ( $currencies_list as $idx => $currency_entry ) {
				if ( ( $currency_entry['currency'] ?? '' ) === $item->currency ) {
					$rate = (int) ( $rates_list[ $idx ]['rate'] ?? 0 );
					break;
				}
			}

			if ( $rate > 0 ) {
				// Base currency rate is 1, so 1 base unit = $rate foreign units → exchange_rate = 1 / $rate.
				$exchange_rate      = 1 / $rate;
				$item->sales        = $round_off_decimal_points ? round( $item->sales * $exchange_rate, 1 ) : $item->sales * $exchange_rate;
				$item->shipping     = $round_off_decimal_points ? round( $item->shipping * $exchange_rate, 1 ) : $item->shipping * $exchange_rate;
				$item->tax          = $round_off_decimal_points ? round( $item->tax * $exchange_rate, 1 ) : $item->tax * $exchange_rate;
				$item->shipping_tax = $round_off_decimal_points ? round( $item->shipping_tax * $exchange_rate, 1 ) : $item->shipping_tax * $exchange_rate;
			}
		}

		// Simulate GROUP BY on the date range used by the chart.
		$range = strpos( $instance->group_by_query, 'DAY' ) !== false ? 3 : 2;

		$_data       = $this->rewrite_post_date_as_index( $price, $range );
		$return_data = array();

		foreach ( $_data as $__data ) {
			foreach ( $__data as $key => $item ) {
				if ( isset( $return_data[ $key ] ) ) {
					$return_data[ $key ]->total_sales        += $item->sales;
					$return_data[ $key ]->total_shipping     += $item->shipping;
					$return_data[ $key ]->total_tax          += $item->tax;
					$return_data[ $key ]->total_shipping_tax += $item->shipping_tax;
				} else {
					$return_data[ $key ]                     = new \stdClass();
					$return_data[ $key ]->total_sales        = $item->sales;
					$return_data[ $key ]->total_shipping     = $item->shipping;
					$return_data[ $key ]->total_tax          = $item->tax;
					$return_data[ $key ]->total_shipping_tax = $item->shipping_tax;
					$return_data[ $key ]->post_date          = $item->post_date;
				}
			}
		}

		// Re-index to sequential integers, matching the format WooCommerce expects.
		return array_values( $return_data );
	}

	/**
	 * Rewrites the input array so that a date string is used as each element's key.
	 *
	 * @param array $array Array of order-data objects, each with a `post_date` property.
	 * @param int   $range Date precision: 1 = year, 2 = year-month, 3 = year-month-day.
	 * @return array
	 */
	public function rewrite_post_date_as_index( $array, $range ) {
		$format_map = array(
			1 => 'Y',
			2 => 'Y-m',
			3 => 'Y-m-d',
		);
		$date_format = $format_map[ $range ] ?? 'Y-m-d';

		$new_array = array();
		foreach ( $array as $item ) {
			$new_key     = gmdate( $date_format, strtotime( $item->post_date ) );
			$new_array[] = array( $new_key => $item );
		}
		return $new_array;
	}

	/**
	 * Converts order totals using the exchange rate before they are written to the analytics stats table.
	 *
	 * @param array    $data      Order data array.
	 * @param \WC_Order $order_obj WooCommerce order object.
	 * @return array
	 */
	public function update_single_order_stats_data( $data, $order_obj ) {
		$order_id       = (int) $data['order_id'];
		$order_currency = $order_obj->get_currency();
		$shop_currency  = get_option( 'woocommerce_currency' );

		if ( $order_currency !== $shop_currency ) {
			$this->backup_original_order_stats_data( $order_id, array( $shop_currency, $order_currency ), $data );
			$data = $this->update_order_data_using_exchange_rate( $data, $order_currency );

			$ids   = get_option( 'alg_wc_cpp_order_ids_converted_prices', array() );
			$ids[] = $order_id;
			update_option( 'alg_wc_cpp_order_ids_converted_prices', array_unique( $ids ) );
		}

		return $data;
	}

	/**
	 * Back-fills unconverted order rows already in the analytics stats table.
	 *
	 * Enabled only when the `alg_wc_cpp_update_past_order_prices_using_exchange_rate` filter returns true.
	 *
	 * @since 1.13
	 */
	public function update_order_stats_data() {
		global $wpdb;

		if ( ! apply_filters( 'alg_wc_cpp_update_past_order_prices_using_exchange_rate', false ) ) {
			return;
		}

		$shop_currency = get_option( 'woocommerce_currency' );
		$_ids          = get_option( 'alg_wc_cpp_order_ids_converted_prices', array( '-1' ) );
		$skipped_ids   = get_option( 'alg_wc_cpp_order_ids_skipped', array( '-1' ) );
		$cache_key     = 'alg_wc_cpp_order_stats_cache_key_' . md5( wp_json_encode( $_ids ) );

		$_ids_int        = array_map( 'intval', (array) $_ids );
		$skipped_ids_int = array_map( 'intval', (array) $skipped_ids );
		$args            = array_merge( $_ids_int, $skipped_ids_int );

		$transient_data = get_transient( $cache_key );
		$ids            = $transient_data ?: $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				'SELECT ' . $wpdb->prefix . 'wc_order_stats.order_id FROM ' . $wpdb->prefix . 'wc_order_stats INNER JOIN ' . $wpdb->posts . ' AS tb1 ON ' . $wpdb->prefix . 'wc_order_stats.order_id = tb1.ID WHERE ' . $wpdb->prefix . 'wc_order_stats.order_id NOT IN ( ' . implode( ', ', array_fill( 0, count( $_ids_int ), '%d' ) ) . ' ) AND ' . $wpdb->prefix . 'wc_order_stats.order_id NOT IN ( ' . implode( ', ', array_fill( 0, count( $skipped_ids_int ), '%d' ) ) . ' )', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$args
			)
		);

		if ( ! $transient_data ) {
			set_transient( $cache_key, $ids, 7 * DAY_IN_SECONDS );
		}

		if ( Functions::is_hpos_enabled() ) {
			$transient_data = get_transient( $cache_key . '_hpos' );
			$hpos_ids       = $transient_data ?: $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					'SELECT ' . $wpdb->prefix . 'wc_order_stats.order_id FROM ' . $wpdb->prefix . 'wc_order_stats INNER JOIN ' . $wpdb->wcorders . ' AS tb2 ON ' . $wpdb->prefix . 'wc_order_stats.order_id = tb2.id WHERE ' . $wpdb->prefix . 'wc_order_stats.order_id NOT IN ( ' . implode( ', ', array_fill( 0, count( $_ids_int ), '%d' ) ) . ' ) AND ' . $wpdb->prefix . 'wc_order_stats.order_id NOT IN ( ' . implode( ', ', array_fill( 0, count( $skipped_ids_int ), '%d' ) ) . ' )', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$args
				)
			);
			$ids = array_merge( $ids, $hpos_ids );

			if ( ! $transient_data ) {
				set_transient( $cache_key . '_hpos', $hpos_ids, 7 * DAY_IN_SECONDS );
			}
		}

		if ( empty( $ids ) ) {
			return;
		}

		$ids         = array_unique( $ids );
		$did_convert = false;
		$skipped_ids = array();

		foreach ( $ids as $order_id ) {
			$order_id       = (int) $order_id;
			$order          = wc_get_order( $order_id );
			$order_currency = $order->get_currency();
			$exchange_rate  = Exchange_Rate_Functions::get_currency_exchange_rate( $order_currency );
			$data           = array(
				'total_sales'    => $order->get_total(),
				'tax_total'      => $order->get_total_tax(),
				'shipping_total' => $order->get_shipping_total(),
				'net_total'      => (float) $order->get_total() - (float) $order->get_total_tax() - (float) $order->get_shipping_total(),
			);

			if ( $order_currency !== $shop_currency && 1 !== $exchange_rate ) {
				$this->backup_original_order_stats_data( $order_id, array( $shop_currency, $order_currency ), $data );
				$order_arr = $this->update_order_data_using_exchange_rate( $data, $order_currency, $exchange_rate );
				$wpdb->update( "{$wpdb->prefix}wc_order_stats", $order_arr, array( 'order_id' => $order_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$_ids[]      = $order_id;
				$did_convert = true;
			} else {
				$skipped_ids[] = $order_id;
			}
		}

		$_skipped_ids = get_option( 'alg_wc_cpp_order_ids_skipped', array( '-1' ) );
		update_option( 'alg_wc_cpp_order_ids_skipped', array_unique( array_merge( $_skipped_ids, $skipped_ids ) ) );

		if ( $did_convert ) {
			update_option( 'alg_wc_cpp_order_ids_converted_prices', array_unique( $_ids ) );
		}
	}

	/**
	 * Updates order totals using the given exchange rate.
	 *
	 * @param array  $data          Order totals array (total_sales, tax_total, shipping_total, net_total).
	 * @param string $currency      The order's currency code.
	 * @param float  $exchange_rate Exchange rate to apply; resolved automatically when 0.
	 * @return array
	 */
	public function update_order_data_using_exchange_rate( $data, $currency, $exchange_rate = 0 ) {
		if ( 0 === $exchange_rate ) {
			$exchange_rate = Exchange_Rate_Functions::get_currency_exchange_rate( $currency );
		}

		$data['total_sales']    = round( $data['total_sales'] * $exchange_rate, 2 );
		$data['tax_total']      = 0 !== $data['tax_total'] ? round( $data['tax_total'] * $exchange_rate, 2 ) : $data['tax_total'];
		$data['shipping_total'] = 0 !== $data['shipping_total'] ? round( $data['shipping_total'] * $exchange_rate, 2 ) : $data['shipping_total'];
		$data['net_total']      = round( $data['net_total'] * $exchange_rate, 2 );

		return $data;
	}

	/**
	 * Saves the original order stat values before they are converted, so they can be restored if needed.
	 *
	 * @param int    $order_id   Order ID.
	 * @param array  $currency   Two-element array: [ shop_currency, order_currency ].
	 * @param array  $order_data Order totals array.
	 */
	public function backup_original_order_stats_data( $order_id, $currency, $order_data ) {
		$data = get_option( 'alg_wc_cpp_original_order_stats_data', array() );

		if ( ! array_search( $order_id, array_column( $data, 'order_id' ), true ) ) {
			$data[] = array(
				'order_id'       => $order_id,
				'shop_currency'  => $currency[0],
				'order_currency' => $currency[1],
				'data'           => $order_data,
			);

			update_option( 'alg_wc_cpp_original_order_stats_data', $data );
		}
	}
}

return new Currency_Reports();
