<?php
/**
 * Currency Per Product Pro Uninstall
 *
 * Deletes all the settings for the plugin from the database when plugin is uninstalled.
 *
 * @package Currency per Product for WooCommerce Pro/uninstall
 * @author      Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit; // Exit if accessed directly.
}

if ( file_exists( WP_PLUGIN_DIR . '/currency-per-product-for-woocommerce-pro/currency-per-product-for-woocommerce-pro.php' ) ) {
	return;
}

// General Settings.
delete_option( 'alg_wc_cpp_enabled' );
delete_option( 'alg_wc_cpp_currency_reports_enabled' );
delete_option( 'alg_wc_cpp_custom_currency_symbol_enabled' );
delete_option( 'alg_wc_cpp_custom_currency_symbol_template' );
delete_option( 'alg_wc_cpp_round_off_decimal_points' );
delete_option( 'alg_wc_cpp__reset' );
delete_option( 'cpp_general_settings' );

// Behaviour.
delete_option( 'alg_wc_cpp_shop_behaviour' );
delete_option( 'alg_wc_cpp_original_price_in_shop_enabled' );
delete_option( 'alg_wc_cpp_original_price_in_shop_template' );
delete_option( 'alg_wc_cpp_original_price_in_cart_checkout_enabled' );
delete_option( 'alg_wc_cpp_original_price_in_cart_checkout_template' );
delete_option( 'alg_wc_cpp_cart_checkout' );
delete_option( 'alg_wc_cpp_cart_checkout_leave_one_product' );
delete_option( 'alg_wc_cpp_cart_checkout_leave_same_currency' );
delete_option( 'alg_wc_cpp_currency_by_location' );
delete_option( 'alg_wc_cpp_behaviour_reset' );
delete_option( 'cpp_behavior_settings' );

// Currencies.
$total_currencies = get_option( 'alg_wc_cpp_total_number' );
for ( $i = 0; $i <= $total_currencies; $i++ ) {
	delete_option( 'alg_wc_cpp_currency_' . $i );
	delete_option( 'alg_wc_cpp_product_cats_' . $i );
	delete_option( 'alg_wc_cpp_product_tags_' . $i );
	delete_option( 'alg_wc_cpp_user_roles_' . $i );
	delete_option( 'alg_wc_cpp_users_' . $i );
	delete_option( 'alg_wc_cpp_product_pg_' . $i );
}

delete_option( 'alg_wc_cpp_total_number' );
delete_option( 'alg_wc_cpp_by_users_enabled' );
delete_option( 'alg_wc_cpp_by_user_roles_enabled' );
delete_option( 'alg_wc_cpp_by_product_cats_enabled' );
delete_option( 'alg_wc_cpp_by_product_tags_enabled' );
delete_option( 'alg_wc_cpp_by_product_pg_enabled' );
delete_option( 'alg_wc_cpp_currencies_reset' );
delete_option( 'cpp_pro_allow_tracking' );
delete_option( 'cpp_currencies_settings' );

// Exchange rates.
delete_option( 'alg_wc_cpp_exchange_rate_update' );
delete_option( 'alg_wc_cpp_exchange_rate_update_rate' );
delete_option( 'alg_wc_cpp_currency_exchange_rates_server' );
delete_option( 'alg_wc_cpp_free_currency_converter_api_key' );
delete_option( 'alg_cpp_coinmarketcap_api_key' );
delete_option( 'alg_wc_cpp_exchange_fees_types' );
delete_option( 'alg_wc_cpp_apply_discount_automatic_additional_exchange_fee' );
delete_option( 'alg_wc_cpp_round_exchange_enabled' );
delete_option( 'alg_wc_cpp_currency_exchange_rates_calculate_by_invert' );

for ( $i = 0; $i <= $total_currencies; $i++ ) {
	delete_option( 'alg_wc_cpp_exchange_rate_' . $i );
	delete_option( 'alg_wc_cpp_exchange_rate_is_manual_' . $i );
}

delete_option( 'alg_wc_cpp_exchange_rates_reset' );
delete_option( 'alg_wc_cpp_calculate_all_products_prices_cron_time' );
delete_option( 'alg_wc_cpp_exchange_rate_cron_time' );
delete_option( 'cpp_exchange_rates' );

// Advanced.
delete_option( 'alg_wc_cpp_fix_mini_cart' );
delete_option( 'alg_wc_cpp_sort_by_converted_price' );
delete_option( 'alg_wc_cpp_filter_by_converted_price' );
delete_option( 'alg_wc_cpp_save_products_prices' );
delete_option( 'alg_wc_cpp_advanced_reset' );
delete_option( 'cpp_advanced_settings' );

// License.
delete_option( 'alg_wc_cpp_license' );
delete_option( 'edd_license_key_cpp' );
delete_option( 'edd_license_cpp_hidden_button' );
delete_option( 'edd_license_key_cpp_status' );
delete_option( 'edd_license_key_cpp_expires' );

// Reports / order data.
delete_option( 'alg_wc_cpp_order_ids_converted_prices' );
delete_option( 'alg_wc_cpp_order_ids_skipped' );
delete_option( 'alg_wc_cpp_original_order_stats_data' );

// Migration flags.
delete_option( 'cpp_reindexed_to_zero' );
delete_option( 'cpp_terms_converted_to_objects' );

// Extra.
delete_option( 'alg_wc_cron_notice_dismissed' );
delete_option( 'alg_wc_cpp_version' );
