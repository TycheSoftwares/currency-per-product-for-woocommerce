<?php
/**
 * CPP Admin API Tools Class
 * Handles tool-action REST endpoints (re-calculate prices, delete plugin data).
 *
 * @package CPP/Admin/API/Tools
 */

namespace Tyche\CPP\API;

use Tyche\CPP\Functions\Functions;

defined( 'ABSPATH' ) || exit;

class Tools extends Admin_API {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

	/**
	 * Register REST endpoints.
	 */
	public static function register_endpoints() {

		register_rest_route(
			self::$base_endpoint,
			'tools/recalculate-prices',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'recalculate_prices' ),
				'permission_callback' => array( __CLASS__, 'permissions' ),
			)
		);

		register_rest_route(
			self::$base_endpoint,
			'tools/delete-plugin-data',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'delete_plugin_data' ),
				'permission_callback' => array( __CLASS__, 'permissions' ),
			)
		);
	}

	/**
	 * Re-calculate and save converted prices for all products.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function recalculate_prices( $request ) {
		$total = Functions::calculate_all_products_prices();

		return self::return_response(
			array(
				'success' => true,
				'updated' => $total,
			)
		);
	}

	/**
	 * Delete all plugin options and product meta.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function delete_plugin_data( $request ) {
		global $wpdb;

		// Delete named options.
		$options = array(
			'cpp_general_settings',
			'cpp_behavior_settings',
			'cpp_currencies_settings',
			'cpp_exchange_rates',
			'cpp_advanced_settings',
			'cpp_reindexed_to_zero',
			'cpp_terms_converted_to_objects',
			'alg_wc_cpp_version',
			'edd_license_key_cpp',
			'edd_license_key_cpp_status',
			'edd_license_key_cpp_expires',
			'cpp_allow_tracking',
		);

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		// Delete wildcard legacy options (e.g. alg_wc_cpp_exchange_rate_1, alg_wc_cpp_user_roles_2 …).
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE 'alg_wc_cpp_%'"
		);

		// Delete product post meta added by this plugin.
		$wpdb->query(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ('_alg_wc_cpp_currency', '_alg_wc_cpp_converted_price')"
		);

		// Clean up object cache so stale option values are not served.
		wp_cache_flush();

		return self::return_response( array( 'success' => true ) );
	}
}

return new Tools();
