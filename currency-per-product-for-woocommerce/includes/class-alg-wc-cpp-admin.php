<?php
/**
 * Currency per Product for WooCommerce - Admin Class
 *
 * @version 1.4.0
 * @since   1.4.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_CPP_Admin' ) ) :

class Alg_WC_CPP_Admin {

	/**
	 * Constructor.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function __construct() {

		// Admin notices
		add_action( 'admin_notices',                                       array( $this, 'admin_notices' ), PHP_INT_MAX );

		// Manual exchange rates update
		add_action( 'admin_init',                                          array( $this, 'update_exchange_rates_now' ) );

		// Delete plugin data
		add_action( 'admin_init',                                          array( $this, 'delete_all_plugin_data' ), PHP_INT_MAX );

		// Manual converted prices re-calculation ("Sort by price" sorting and "Filter Products by Price" widget)
		add_action( 'admin_init',                                          array( $this, 'calculate_all_products_prices' ), PHP_INT_MAX );

		// Automatic converted prices re-calculation ("Sort by price" sorting and "Filter Products by Price" widget)
		if ( 'yes' === get_option( 'alg_wc_cpp_sort_by_converted_price', 'no' ) || 'yes' === get_option( 'alg_wc_cpp_filter_by_converted_price', 'no' ) ) {
			add_action( 'save_post_product',                               array( $this, 'calculate_product_price_on_product_saved' ), PHP_INT_MAX, 1 );
			add_action( 'woocommerce_ajax_save_product_variations',        array( $this, 'calculate_product_price_on_product_saved_ajax' ), PHP_INT_MAX, 1 );
		}

	}

	/*
	 * admin_notices.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 * @todo    [dev] maybe use `WC_Admin_Settings::add_message()` instead
	 */
	function admin_notices() {
		if ( isset( $_GET['alg_wc_cpp_calculate_all_products_prices_finished'] ) ) {
			echo '<div class="notice notice-info"><p>' .
				sprintf( __( 'Prices successfully re-calculated for %s products.', 'currency-per-product-for-woocommerce' ),
					'<strong>' . $_GET['alg_wc_cpp_calculate_all_products_prices_finished'] . '</strong>' ) .
			'</p></div>';
		}
		if ( isset( $_GET['alg_wc_cpp_delete_plugin_data_finished'] ) ) {
			$totals = explode( ',', $_GET['alg_wc_cpp_delete_plugin_data_finished'] );
			echo '<div class="notice notice-info"><p>' .
				sprintf( __( 'Plugin data successfully deleted (%s options and %s product metas).', 'currency-per-product-for-woocommerce' ),
					'<strong>' . ( isset( $totals[1] ) ? $totals[1] : 0 ) . '</strong>', '<strong>' . $totals[0] . '</strong>' ) .
			'</p></div>';
		}
	}

	/*
	 * delete_all_plugin_data.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 * @todo    [feature] maybe separate buttons for options and meta
	 */
	function delete_all_plugin_data() {
		if ( isset( $_GET['alg_wc_cpp_delete_plugin_data'] ) ) {
			if (
				current_user_can( 'manage_woocommerce' ) &&
				isset( $_GET['alg_wc_cpp_delete_plugin_data_nonce'] ) &&
				wp_verify_nonce( $_GET['alg_wc_cpp_delete_plugin_data_nonce'], 'alg_wc_cpp_delete_plugin_data' )
			) {
				global $wpdb;
				$delete_counter_meta    = 0;
				$plugin_meta            = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE '_alg_wc_cpp_%'" );
				foreach( $plugin_meta as $meta ) {
					delete_post_meta( $meta->post_id, $meta->meta_key );
					$delete_counter_meta++;
				}
				$delete_counter_options = 0;
				$plugin_options         = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'alg_wc_cpp_%'" );
				foreach( $plugin_options as $option ) {
					if ( 'alg_wc_cpp_version' != $option->option_name ) {
						delete_option( $option->option_name );
						delete_site_option( $option->option_name );
						$delete_counter_options++;
					}
				}
				wp_safe_redirect( add_query_arg( 'alg_wc_cpp_delete_plugin_data_finished', $delete_counter_meta . ',' . $delete_counter_options,
					remove_query_arg( array( 'alg_wc_cpp_delete_plugin_data', 'alg_wc_cpp_delete_plugin_data_nonce' ) ) ) );
				exit;
			} else {
				wp_die( __( 'User role or nonce is not valid!', 'currency-per-product-for-woocommerce' ) );
			}
		}
	}

	/*
	 * calculate_product_price_on_product_saved.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function calculate_product_price_on_product_saved( $product_id ) {
		alg_wc_cpp_calculate_and_update_product_price( $product_id, get_woocommerce_currency() );
	}

	/*
	 * calculate_product_price_on_product_saved_ajax.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function calculate_product_price_on_product_saved_ajax( $product_id ) {
		WC_Product_Variable::sync( $product_id );
		alg_wc_cpp_calculate_and_update_product_price( $product_id, get_woocommerce_currency() );
	}

	/*
	 * calculate_all_products_prices.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function calculate_all_products_prices() {
		if ( isset( $_GET['alg_wc_cpp_calculate_all_products_prices'] ) ) {
			if (
				current_user_can( 'manage_woocommerce' ) &&
				isset( $_GET['alg_wc_cpp_calculate_all_products_prices_nonce'] ) &&
				wp_verify_nonce( $_GET['alg_wc_cpp_calculate_all_products_prices_nonce'], 'alg_wc_cpp_calculate_all_products_prices' )
			) {
				$total_products = alg_wc_cpp_calculate_all_products_prices();
				wp_safe_redirect( add_query_arg( 'alg_wc_cpp_calculate_all_products_prices_finished', $total_products,
					remove_query_arg( array( 'alg_wc_cpp_calculate_all_products_prices', 'alg_wc_cpp_calculate_all_products_prices_nonce' ) ) ) );
				exit;
			} else {
				wp_die( __( 'User role or nonce is not valid!', 'currency-per-product-for-woocommerce' ) );
			}
		}
	}

	/**
	 * update_exchange_rates_now.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function update_exchange_rates_now() {
		if ( isset( $_GET['alg_wc_cpp_update_exchange_rates'] ) ) {
			do_action( 'alg_wc_cpp_update_exchange_rates' );
			wp_safe_redirect( remove_query_arg( 'alg_wc_cpp_update_exchange_rates' ) );
			exit;
		}
	}

}

endif;

return new Alg_WC_CPP_Admin();
