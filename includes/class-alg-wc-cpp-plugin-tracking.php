<?php // phpcs:ignore
/**
 *  Currency per Product for WooCommerce - Data Tracking Functions
 *
 * @since   1.5.0
 * @package  Currency per Product/Data Tracking
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_Wc_Cpp_Plugin_Tracking' ) ) :

	/**
	 *  Currency per Product Data Tracking Functions.
	 */
	class Alg_Wc_Cpp_Plugin_Tracking {
		/**
		 * Construct.
		 */
		public function __construct() {
			add_filter( 'ts_tracker_data', array( __CLASS__, 'cpp_lite_ts_add_plugin_tracking_data' ), 10, 1 );

			add_action( 'admin_footer', array( __CLASS__, 'ts_admin_notices_scripts' ) );
			add_action( 'cpp_lite_init_tracker_completed', array( __CLASS__, 'init_tracker_completed' ), 10 );
			add_filter( 'cpp_lite_ts_tracker_display_notice', array( __CLASS__, 'cpp_ts_tracker_display_notice' ), 10, 1 );
		}

		/**
		 * Send the plugin data when the user has opted in
		 *
		 * @hook ts_tracker_data
		 * @param array $data All data to send to server.
		 *
		 * @return array $plugin_data All data to send to server.
		 */
		public static function cpp_lite_ts_add_plugin_tracking_data( $data ) {
			$plugin_short_name = 'cpp_lite';
			if ( ! isset( $_GET[ $plugin_short_name . '_tracker_nonce' ] ) ) {
				return $data;
			}

			$tracker_option = isset( $_GET[ $plugin_short_name . '_tracker_optin' ] ) ? $plugin_short_name . '_tracker_optin' : ( isset( $_GET[ $plugin_short_name . '_tracker_optout' ] ) ? $plugin_short_name . '_tracker_optout' : '' ); // phpcs:ignore
			if ( '' === $tracker_option || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET[ $plugin_short_name . '_tracker_nonce' ] ) ), $tracker_option ) ) {
				return $data;
			}

			$data = self::cpp_lite_plugin_tracking_data( $data );
			return $data;
		}

		/**
		 * Add admin notice script.
		 */
		public static function ts_admin_notices_scripts() {
			wp_enqueue_script(
				'cpp_ts_dismiss_notice',
				plugins_url() . '/currency-per-product-for-woocommerce/assets/js/tyche-dismiss-tracking-notice.js',
				'',
				get_option( 'alg_wc_cpp_version', '' ),
				false
			);

			wp_localize_script(
				'cpp_ts_dismiss_notice',
				'cpp_ts_dismiss_notice',
				array(
					'ts_prefix_of_plugin' => 'cpp_lite',
					'ts_admin_url'        => admin_url( 'admin-ajax.php' ),
				)
			);
		}

		/**
		 * Add tracker completed.
		 */
		public static function init_tracker_completed() {
			$redirect_url = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cpp' );
			header( 'Location: ' . $redirect_url );
			exit;
		}

		/**
		 * Display admin notice on specific page.
		 *
		 * @param array $is_flag Is Flag defailt value true.
		 */
		public static function cpp_ts_tracker_display_notice( $is_flag ) {
			global $current_section;

			if ( isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] ) { // phpcs:ignore
				$is_flag = false;
				if ( isset( $_GET['tab'] ) && 'alg_wc_cpp' === $_GET['tab'] && empty( $current_section ) ) { // phpcs:ignore
					$is_flag = true;
				}
			}

			return $is_flag;
		}

		/**
		 * Returns plugin data for tracking.
		 *
		 * @param array $data - Generic data related to WP, WC, Theme, Server and so on.
		 * @return array $data - Plugin data included in the original data received.
		 */
		public static function cpp_lite_plugin_tracking_data( $data ) {
			$currencies                                   = array();
			$exchange_rates                               = array();
			$total_number                                 = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
			$currencies['alg_wc_cpp_currency_0']          = get_option( 'alg_wc_cpp_currency_0' );
			$exchange_rates['alg_wc_cpp_exchange_rate_0'] = get_option( 'alg_wc_cpp_exchange_rate_0' );
			for ( $i = 1; $i <= $total_number; $i++ ) {
				$currencies[ 'alg_wc_cpp_currency_' . $i ]          = get_option( 'alg_wc_cpp_currency_' . $i );
				$exchange_rates[ 'alg_wc_cpp_exchange_rate_' . $i ] = get_option( 'alg_wc_cpp_exchange_rate_' . $i );
			}

			$plugin_data         = array(
				'ts_meta_data_table_name'              => 'ts_tracking_cpp_lite_meta_data',
				'ts_plugin_name'                       => 'Currency per Product for WooCommerce',
				'plugin_version'                       => get_option( 'alg_wc_cpp_version', '' ),
				'alg_wc_cpp_enabled'                   => get_option( 'alg_wc_cpp_enabled' ), // General Settings.
				'alg_wc_cpp_currency_reports_enabled'  => get_option( 'alg_wc_cpp_currency_reports_enabled' ),
				'alg_wc_cpp_custom_currency_symbol_enabled' => get_option( 'alg_wc_cpp_custom_currency_symbol_enabled' ),
				'alg_wc_cpp_custom_currency_symbol_template' => get_option( 'alg_wc_cpp_custom_currency_symbol_template' ),
				'alg_wc_cpp_shop_behaviour'            => get_option( 'alg_wc_cpp_shop_behaviour' ), // Behavior Settings.
				'alg_wc_cpp_original_price_in_shop_enabled' => get_option( 'alg_wc_cpp_original_price_in_shop_enabled' ),
				'alg_wc_cpp_original_price_in_shop_template' => get_option( 'alg_wc_cpp_original_price_in_shop_template' ),
				'alg_wc_cpp_cart_checkout'             => get_option( 'alg_wc_cpp_cart_checkout' ),
				'alg_wc_cpp_cart_checkout_leave_one_product' => get_option( 'alg_wc_cpp_cart_checkout_leave_one_product' ),
				'alg_wc_cpp_cart_checkout_leave_same_currency' => get_option( 'alg_wc_cpp_cart_checkout_leave_same_currency' ),
				'alg_wc_cpp_total_number'              => get_option( 'alg_wc_cpp_total_number' ), // Currency Settings.
				'alg_wc_cpp_by_users_enabled'          => get_option( 'alg_wc_cpp_by_users_enabled' ),
				'alg_wc_cpp_by_user_roles_enabled'     => get_option( 'alg_wc_cpp_by_user_roles_enabled' ),
				'alg_wc_cpp_by_product_cats_enabled'   => get_option( 'alg_wc_cpp_by_product_cats_enabled' ),
				'alg_wc_cpp_by_product_tags_enabled'   => get_option( 'alg_wc_cpp_by_product_tags_enabled' ),
				'currency_count'                       => count( $currencies ),
				'currency_list'                        => wp_json_encode( $currencies ),
				'alg_wc_cpp_exchange_rate_update'      => get_option( 'alg_wc_cpp_exchange_rate_update' ), // Exchange Rate Settings.
				'alg_wc_cpp_exchange_rate_update_rate' => get_option( 'alg_wc_cpp_exchange_rate_update_rate' ),
				'alg_wc_cpp_currency_exchange_rates_server' => get_option( 'alg_wc_cpp_currency_exchange_rates_server' ),
				'exchange_rates'                       => wp_json_encode( $exchange_rates ),
				'alg_wc_cpp_fix_mini_cart'             => get_option( 'alg_wc_cpp_fix_mini_cart' ), // Advanced Settings.
				'alg_wc_cpp_sort_by_converted_price'   => get_option( 'alg_wc_cpp_sort_by_converted_price' ),
				'alg_wc_cpp_filter_by_converted_price' => get_option( 'alg_wc_cpp_filter_by_converted_price' ),
				'alg_wc_cpp_save_products_prices'      => get_option( 'alg_wc_cpp_save_products_prices' ),
				'product_count'                        => self::cpp_get_each_currency_count(), // Each currency count product.
				'order_count'                          => self::cpp_get_each_currency_count( 'order' ), // Each currency count order.
			);
			$data['plugin_data'] = $plugin_data;

			return $data;
		}

		/**
		 * Send each currency product or order counts for tracking.
		 *
		 * @param array $type - Type default value product.
		 */
		public static function cpp_get_each_currency_count( $type = 'product' ) {
			global $wpdb;

			if ( 'order' === $type || 'product' === $type ) {
				$total_count           = array();
				$total_number          = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
				$currency_key          = ( 'order' === $type ) ? '_order_currency' : '_alg_wc_cpp_currency';
				$f_key                 = get_option( 'alg_wc_cpp_currency_0' );
				$total_count[ $f_key ] = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT(post_id)) FROM `wp_postmeta` WHERE `meta_key` LIKE %s AND `meta_value` = %s', '%' . $wpdb->esc_like( $currency_key ) . '%', $f_key ) ); // phpcs:ignore
				for ( $i = 1; $i <= $total_number; $i++ ) {
					$i_key                 = get_option( 'alg_wc_cpp_currency_' . $i );
					$total_count[ $i_key ] = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT(post_id)) FROM `wp_postmeta` WHERE `meta_key` LIKE %s AND `meta_value` = %s', '%' . $wpdb->esc_like( $currency_key ) . '%', $i_key ) ); // phpcs:ignore
				}

				return wp_json_encode( $total_count );
			}
		}
	}

endif;

$alg_wc_cpp_plugin_tracking = new Alg_Wc_Cpp_Plugin_Tracking();
