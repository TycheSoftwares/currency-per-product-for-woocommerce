<?php
/**
 * Currency per Product for WooCommerce - Admin Class
 *
 * @version 1.4.0
 * @since   1.4.0
 * @author  Tyche Softwares
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_CPP_Admin' ) ) :

	/**
	 * Main Alg_WC_CPP_Admin Class
	 *
	 * @class   Alg_WC_CPP_Admin
	 */
	class Alg_WC_CPP_Admin {

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function __construct() {

			// Admin notices.
			add_action( 'admin_notices', array( $this, 'admin_notices' ), PHP_INT_MAX );

			// Manual exchange rates update.
			add_action( 'admin_init', array( $this, 'update_exchange_rates_now' ) );

			// Delete plugin data.
			add_action( 'admin_init', array( $this, 'delete_all_plugin_data' ), PHP_INT_MAX );

			// Manual converted prices re-calculation ("Sort by price" sorting and "Filter Products by Price" widget).
			add_action( 'admin_init', array( $this, 'calculate_all_products_prices' ), PHP_INT_MAX );

			// Automatic converted prices re-calculation ("Sort by price" sorting and "Filter Products by Price" widget).
			if ( 'yes' === get_option( 'alg_wc_cpp_sort_by_converted_price', 'no' ) || 'yes' === get_option( 'alg_wc_cpp_filter_by_converted_price', 'no' ) ) {
				add_action( 'save_post_product', array( $this, 'calculate_product_price_on_product_saved' ), PHP_INT_MAX, 1 );
				add_action( 'woocommerce_ajax_save_product_variations', array( $this, 'calculate_product_price_on_product_saved_ajax' ), PHP_INT_MAX, 1 );
			}
		}

		/**
		 * Admin Notices.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 * @todo    [dev] maybe use `WC_Admin_Settings::add_message()` instead
		 */
		public function admin_notices() {
			if ( isset( $_GET['alg_wc_cpp_calculate_all_products_prices_finished'] ) ) {
				echo '<div class="notice notice-info"><p>';
				sprintf(
					/* translators: %s: number of products for which price was re-calculated */
					esc_html_e( 'Prices successfully re-calculated for %s products.', 'currency-per-product-for-woocommerce' ),
					'<strong>' . sanitize_text_field( wp_unslash( $_GET['alg_wc_cpp_calculate_all_products_prices_finished'] ) ) . '</strong>'
				);
				echo '</p></div>';
			}
			if ( isset( $_GET['alg_wc_cpp_delete_plugin_data_finished'] ) ) {
				$totals = explode( ',', sanitize_text_field( wp_unslash( $_GET['alg_wc_cpp_delete_plugin_data_finished'] ) ) );
				echo '<div class="notice notice-info"><p>';
				sprintf(
					/* translators: %1$s: number of options deleted, %2$s: number of product metas deleted */
					__( 'Plugin data successfully deleted (%1$s options and %2$s product metas).', 'currency-per-product-for-woocommerce' ),
					'<strong>' . ( isset( $totals[1] ) ? $totals[1] : 0 ) . '</strong>',
					'<strong>' . $totals[0] . '</strong>'
				);
				echo '</p></div>';
			}
		}

		/**
		 * Delete plugin data.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 * @todo    [feature] maybe separate buttons for options and meta
		 */
		public function delete_all_plugin_data() {
			if ( isset( $_GET['alg_wc_cpp_delete_plugin_data'] ) ) {
				if (
				current_user_can( 'manage_woocommerce' ) &&
				isset( $_GET['alg_wc_cpp_delete_plugin_data_nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['alg_wc_cpp_delete_plugin_data_nonce'] ) ), 'alg_wc_cpp_delete_plugin_data' )
				) {
					global $wpdb;
					$delete_counter_meta = 0;
					// phpcs:ignore
					$plugin_meta         = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE '_alg_wc_cpp_%'" );
					foreach ( $plugin_meta as $meta ) {
						delete_post_meta( $meta->post_id, $meta->meta_key );
						$delete_counter_meta++;
					}
					$delete_counter_options = 0;
					// phpcs:ignore
					$plugin_options         = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'alg_wc_cpp_%'" );
					foreach ( $plugin_options as $option ) {
						if ( 'alg_wc_cpp_version' !== $option->option_name ) {
							delete_option( $option->option_name );
							delete_site_option( $option->option_name );
							$delete_counter_options++;
						}
					}
					wp_safe_redirect(
						add_query_arg(
							'alg_wc_cpp_delete_plugin_data_finished',
							$delete_counter_meta . ',' . $delete_counter_options,
							remove_query_arg( array( 'alg_wc_cpp_delete_plugin_data', 'alg_wc_cpp_delete_plugin_data_nonce' ) )
						)
					);
					exit;
				} else {
					wp_die( esc_html_e( 'User role or nonce is not valid!', 'currency-per-product-for-woocommerce' ) );
				}
			}
		}

		/**
		 * Calculate product price.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 *
		 * @param int $product_id Product Id.
		 */
		public function calculate_product_price_on_product_saved( $product_id ) {
			alg_wc_cpp_calculate_and_update_product_price( $product_id, get_woocommerce_currency() );
		}

		/**
		 * Calculate product price via ajax.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 *
		 * @param int $product_id Product Id.
		 */
		public function calculate_product_price_on_product_saved_ajax( $product_id ) {
			WC_Product_Variable::sync( $product_id );
			alg_wc_cpp_calculate_and_update_product_price( $product_id, get_woocommerce_currency() );
		}

		/**
		 * Calculate prices of all products.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function calculate_all_products_prices() {
			if ( isset( $_GET['alg_wc_cpp_calculate_all_products_prices'] ) ) {
				if (
				current_user_can( 'manage_woocommerce' ) &&
				isset( $_GET['alg_wc_cpp_calculate_all_products_prices_nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['alg_wc_cpp_calculate_all_products_prices_nonce'] ) ), 'alg_wc_cpp_calculate_all_products_prices' )
				) {
					$total_products = alg_wc_cpp_calculate_all_products_prices();
					wp_safe_redirect(
						add_query_arg(
							'alg_wc_cpp_calculate_all_products_prices_finished',
							$total_products,
							remove_query_arg( array( 'alg_wc_cpp_calculate_all_products_prices', 'alg_wc_cpp_calculate_all_products_prices_nonce' ) )
						)
					);
					exit;
				} else {
					wp_die( esc_html_e( 'User role or nonce is not valid!', 'currency-per-product-for-woocommerce' ) );
				}
			}
		}

		/**
		 * Update exchange rates.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function update_exchange_rates_now() {
			if ( isset( $_GET['alg_wc_cpp_update_exchange_rates'] ) ) {
				do_action( 'alg_wc_cpp_update_exchange_rates' );
				wp_safe_redirect( remove_query_arg( 'alg_wc_cpp_update_exchange_rates' ) );
				exit;
			}
		}
	}

endif;

return new Alg_WC_CPP_Admin();
