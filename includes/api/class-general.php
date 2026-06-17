<?php
/**
 * CPP Admin API Settings Class
 * Handles admin settings-related API functionalities for the CPP plugin.
 * 
 * @package CPP/Admin/API/Settings/General
 */


namespace Tyche\CPP\API;

defined( 'ABSPATH' ) || exit;

class General extends Admin_API {

    /**
	 * Construct
	 *
	 * @since 1.2
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

    /**
	 * Function for registering the API endpoints.
	 *
	 * @since 1.2
	 */
	public static function register_endpoints() {

		// Fetch Settings.
		register_rest_route(
			self::$base_endpoint,
			'settings/general',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'fetch_data' ),
					'permission_callback' => array( __CLASS__, 'permissions' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( __CLASS__, 'save_data' ),
					'permission_callback' => array( __CLASS__, 'permissions' ),
				),
			)
		);

	}

    /**
	 * Returns General Settings Data.
	 *
	 * @param array $return_raw Whether to return the Raw response.
	 *
	 * @since 1.2
	 */
	public static function fetch_data( $request ) {
		$settings = get_option( 'cpp_general_settings', array() );

		return self::return_response( $settings );
	}

	/**
	 * Saves the settings data.
	 */
	public static function save_data( $request ) {
		$data = json_decode( $request->get_body(), true );

		if ( ! is_array( $data ) ) {
			return self::error();
		}

		$settings = array(
			'enabled'                         => (bool) ( $data['enabled'] ?? false ),
			'currency_reports_enabled'        => (bool) ( $data['currency_reports_enabled'] ?? false ),
			'custom_currency_symbol_enabled'  => (bool) ( $data['custom_currency_symbol_enabled'] ?? false ),
			'custom_currency_symbol_template' => sanitize_text_field( $data['custom_currency_symbol_template'] ?? '%currency_code%%currency_symbol%' ),
			'round_off_decimal_points'        => (bool) ( $data['round_off_decimal_points'] ?? false ),
		);

		update_option( 'cpp_general_settings', $settings );

		return self::return_response( $settings );
	}
}

return new General();
