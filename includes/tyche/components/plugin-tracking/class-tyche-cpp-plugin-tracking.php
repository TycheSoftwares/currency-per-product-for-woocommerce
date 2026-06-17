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
			add_filter( 'cpp_ts_tracker_data', array( __CLASS__, 'cpp_ts_add_plugin_tracking_data' ), 10, 1 );
			add_action( 'admin_footer', array( __CLASS__, 'ts_admin_notices_scripts' ) );
			add_action( 'cpp_init_tracker_completed', array( __CLASS__, 'init_tracker_completed' ), 10 );
			add_filter( 'cpp_ts_tracker_display_notice', array( __CLASS__, 'cpp_ts_tracker_display_notice' ), 10, 1 );
			add_filter( 'woocommerce_reset_settings_alg_wc_cpp', array( $this, 'ts_tracking_reset_option' ), 10, 2 );
		}

		/**
		 * Add reset tracking option on general settings.
		 *
		 * @param array  $settings Settings.
		 * @param string $current_section Current section.
		 *
		 * @return array
		 */
		public function ts_tracking_reset_option( $settings, $current_section ) {

			$reset_usage_tracking = array(
				'title'   => __( 'Reset Usage Tracking', 'currency-per-product-for-woocommerce' ),
				'desc'    => __( 'This will reset your usage tracking settings, causing it to show the opt-in banner again and not sending any data.', 'currency-per-product-for-woocommerce' ),
				'id'      => $current_section . '_ts_reset_tracking',
				'default' => 'no',
				'type'    => 'checkbox',
			);
			array_splice( $settings, 2, 0, array( $reset_usage_tracking ) );

			return $settings;
		}

		/**
		 * Send the plugin data when the user has opted in
		 *
		 * @hook ts_tracker_data
		 * @param array $data All data to send to server.
		 *
		 * @return array $plugin_data All data to send to server.
		 */
		public static function cpp_ts_add_plugin_tracking_data( $data ) {
			$plugin_short_name = 'cpp';
			if ( ! isset( $_GET[ $plugin_short_name . '_tracker_nonce' ] ) ) {
				return $data;
			}

			$tracker_option = isset( $_GET[ $plugin_short_name . '_tracker_optin' ] ) ? $plugin_short_name . '_tracker_optin' : ( isset( $_GET[ $plugin_short_name . '_tracker_optout' ] ) ? $plugin_short_name . '_tracker_optout' : '' ); // phpcs:ignore
			if ( '' === $tracker_option || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET[ $plugin_short_name . '_tracker_nonce' ] ) ), $tracker_option ) ) {
				return $data;
			}

			$data = self::cpp_plugin_tracking_data( $data );
			return $data;
		}

		/**
		 * Add admin notice script.
		 */
		public static function ts_admin_notices_scripts() {
			$nonce = wp_create_nonce( 'tracking_notice' );
			wp_enqueue_script(
				'cpp_ts_dismiss_notice',
				plugins_url() . '/currency-per-product-for-woocommerce/includes/tyche/assets/js/tyche-dismiss-tracking-notice.js',
				'',
				get_option( 'alg_wc_cpp_version', '' ),
				false
			);

			wp_localize_script(
				'cpp_ts_dismiss_notice',
				'cpp_ts_dismiss_notice',
				array(
					'ts_prefix_of_plugin' => 'cpp',
					'ts_admin_url'        => admin_url( 'admin-ajax.php' ),
					'tracking_notice'     => $nonce,
				)
			);
		}

		/**
		 * Add tracker completed.
		 */
		public static function init_tracker_completed() {
			$redirect_url = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : admin_url( 'admin.php?page=wc-settings&tab=currency-per-product-for-woocommerce' );
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
				if ( isset( $_GET['tab'] ) && 'currency-per-product-for-woocommerce' === $_GET['tab'] && empty( $current_section ) ) { // phpcs:ignore
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
		public static function cpp_plugin_tracking_data( $data ) {
			$currencies      = array();
			$exchange_rates  = array();
			$currencies_list = Functions::get_currencies_setting( 'currencies', array() );
			$rates_list      = Functions::get_exchange_rate_setting( 'rates', array() );
			foreach ( $currencies_list as $idx => $currency_entry ) {
				$currencies[ 'currency_' . $idx ]      = $currency_entry['currency'] ?? '';
				$exchange_rates[ 'exchange_rate_' . $idx ] = $rates_list[ $idx ]['rate'] ?? 1;
			}

			$plugin_data         = array(
				'ts_meta_data_table_name'              => 'ts_tracking_cpp_meta_data',
				'ts_plugin_name'                       => 'Currency per Product for WooCommerce',
				'plugin_version'                       => get_option( 'alg_wc_cpp_version', '' ),
				'enabled'                              => Functions::get_general_setting( 'enabled' ), // General Settings.
				'currency_reports_enabled'             => Functions::get_general_setting( 'currency_reports_enabled' ),
				'custom_currency_symbol_enabled'       => Functions::get_general_setting( 'custom_currency_symbol_enabled' ),
				'custom_currency_symbol_template'      => Functions::get_general_setting( 'custom_currency_symbol_template' ),
				'shop_behaviour'                       => Functions::get_behavior_setting( 'shop_behaviour' ), // Behavior Settings.
				'original_price_in_shop_enabled'       => Functions::get_behavior_setting( 'original_price_in_shop_enabled' ),
				'original_price_in_shop_template'      => Functions::get_behavior_setting( 'original_price_in_shop_template' ),
				'cart_checkout'                        => Functions::get_behavior_setting( 'cart_checkout' ),
				'cart_checkout_leave_one_product'      => Functions::get_behavior_setting( 'cart_checkout_leave_one_product' ),
				'cart_checkout_leave_same_currency'    => Functions::get_behavior_setting( 'cart_checkout_leave_same_currency' ),
				'total_number'                         => Functions::get_currencies_setting( 'total_number' ), // Currency Settings.
				'by_users_enabled'                     => Functions::get_currencies_setting( 'by_users_enabled' ),
				'by_user_roles_enabled'                => Functions::get_currencies_setting( 'by_user_roles_enabled' ),
				'by_product_cats_enabled'              => Functions::get_currencies_setting( 'by_product_cats_enabled' ),
				'by_product_tags_enabled'              => Functions::get_currencies_setting( 'by_product_tags_enabled' ),
				'currency_count'                       => count( $currencies ),
				'currency_list'                        => wp_json_encode( $currencies ),
				'exchange_rate_update'                 => Functions::get_exchange_rate_setting( 'exchange_rate_update' ), // Exchange Rate Settings.
				'exchange_rates'                       => wp_json_encode( $exchange_rates ),
				'fix_mini_cart'                        => Functions::get_advanced_setting( 'fix_mini_cart' ), // Advanced Settings.
				'sort_by_converted_price'              => Functions::get_advanced_setting( 'sort_by_converted_price' ),
				'filter_by_converted_price'            => Functions::get_advanced_setting( 'filter_by_converted_price' ),
				'save_products_prices'                 => Functions::get_advanced_setting( 'save_products_prices' ),
				'product_count'                        => self::cpp_get_each_currency_count(), // Each currency count product.
				'order_count'                          => self::cpp_get_each_currency_count( 'order' ), // Each currency count order.
			);
			$data['plugin_data'] = $plugin_data;

			return $data;
		}

		/**
		 * Send each currency product or order counts for tracking.
		 *
		 * @param string $type - Type default value product.
		 */
		public static function cpp_get_each_currency_count( $type = 'product' ) {
			global $wpdb;

			if ( 'order' === $type || 'product' === $type ) {
				$total_count     = array();
				$currency_key    = ( 'order' === $type ) ? '_order_currency' : '_alg_wc_cpp_currency';
				$currencies_list = Functions::get_currencies_setting( 'currencies', array() );
				foreach ( $currencies_list as $currency_entry ) {
					$i_key = $currency_entry['currency'] ?? '';
					if ( $i_key ) {
						$total_count[ $i_key ] = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT(post_id)) FROM `wp_postmeta` WHERE `meta_key` LIKE %s AND `meta_value` = %s', '%' . $wpdb->esc_like( $currency_key ) . '%', $i_key ) ); //phpcs:ignore
					}
				}

				return wp_json_encode( $total_count );
			}
		}
	}

endif;

$alg_wc_cpp_plugin_tracking = new Alg_Wc_Cpp_Plugin_Tracking();
