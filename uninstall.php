<?php
/**
 * Currency Per Product Pro Uninstall
 *
 * Deletes all the settings for the plugin from the database when plugin is uninstalled.
 *
 * @author      Tyche Softwares
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( file_exists( WP_PLUGIN_DIR . '/currency-per-product-for-woocommerce-pro/currency-per-product-for-woocommerce-pro.php' ) ) {
	return;
}

// General Settings.
delete_option( 'alg_wc_cpp_enabled' );
delete_option( 'alg_wc_cpp_currency_reports_enabled ' );
delete_option( 'alg_wc_cpp_custom_currency_symbol_enabled' );
delete_option( 'alg_wc_cpp_custom_currency_symbol_template' );
delete_option( 'alg_wc_cpp__reset' );

// Behaviour.
delete_option( 'alg_wc_cpp_shop_behaviour' );
delete_option( 'alg_wc_cpp_original_price_in_shop_enabled' );
delete_option( 'alg_wc_cpp_original_price_in_shop_template' );

delete_option( 'alg_wc_cpp_cart_checkout' );
delete_option( 'alg_wc_cpp_cart_checkout_leave_one_product' );
delete_option( 'alg_wc_cpp_cart_checkout_leave_same_currency' );
delete_option( 'alg_wc_cpp_behaviour_reset' );

// Currencies.
$total_currencies = get_option( 'alg_wc_cpp_total_number' );
for ( $i = 0; $i <= $total_currencies; $i++ ) {
	delete_option( 'alg_wc_cpp_currency_' . $i );
	delete_option( 'alg_wc_cpp_product_cats_' . $i );
	delete_option( 'alg_wc_cpp_product_tags_' . $i );
	delete_option( 'alg_wc_cpp_user_roles_' . $i );
	delete_option( 'alg_wc_cpp_users_' . $i );
}

delete_option( 'alg_wc_cpp_total_number' );
delete_option( 'alg_wc_cpp_by_users_enabled' );
delete_option( 'alg_wc_cpp_by_user_roles_enabled' );
delete_option( 'alg_wc_cpp_by_product_cats_enabled' );
delete_option( 'alg_wc_cpp_by_product_tags_enabled' );
delete_option( 'alg_wc_cpp_currencies_reset' );

// Exchange rates.
delete_option( 'alg_wc_cpp_exchange_rate_update' );
delete_option( 'alg_wc_cpp_exchange_rate_update_rate' );
delete_option( 'alg_wc_cpp_currency_exchange_rates_server' );
delete_option( 'alg_wc_cpp_free_currency_converter_api_key' );

for ( $i = 0; $i <= $total_currencies; $i++ ) {
	delete_option( 'alg_wc_cpp_exchange_rate_' . $i );
}

delete_option( 'alg_wc_cpp_exchange_rates_reset' );
delete_option( 'alg_wc_cpp_calculate_all_products_prices_cron_time' );
delete_option( 'alg_wc_cpp_exchange_rate_cron_time' );

// Advanced.
delete_option( 'alg_wc_cpp_fix_mini_cart' );
delete_option( 'alg_wc_cpp_sort_by_converted_price' );
delete_option( 'alg_wc_cpp_filter_by_converted_price' );
delete_option( 'alg_wc_cpp_save_products_prices' );
delete_option( 'alg_wc_cpp_advanced_reset' );

// License.
delete_option( 'edd_license_key_cpp' );
delete_option( 'edd_license_cpp_hidden_button' );
delete_option( 'edd_license_key_cpp_status' );

// Extra.
delete_option( 'alg_wc_cpp_version' );
