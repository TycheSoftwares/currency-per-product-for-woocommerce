<?php
/**
 * CPP Admin API Settings Class
 * Handles admin settings-related API functionalities for the CPP plugin.
 * 
 * @package CPP/Admin/API/Settings/General
 */


namespace Tyche\CPP\API;

defined( 'ABSPATH' ) || exit;

class Behaviour extends Admin_API {

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
			'settings/behaviour',
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
		$settings = get_option( 'cpp_behavior_settings', array() );

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
			'shop_behaviour'                           => sanitize_text_field( $data['shop_behaviour'] ?? 'show_in_different' ),
			'original_price_in_shop_enabled'           => (bool) ( $data['original_price_in_shop_enabled'] ?? false ),
			'original_price_in_shop_template'          => wp_kses_post( $data['original_price_in_shop_template'] ?? '<br>%price%' ),
			'cart_checkout'                            => sanitize_text_field( $data['cart_checkout'] ?? 'convert_shop_default' ),
			'original_price_in_cart_checkout_enabled'  => (bool) ( $data['original_price_in_cart_checkout_enabled'] ?? false ),
			'original_price_in_cart_checkout_template' => wp_kses_post( $data['original_price_in_cart_checkout_template'] ?? '<br>%price%' ),
			'cart_checkout_leave_one_product'          => sanitize_text_field( $data['cart_checkout_leave_one_product'] ?? '' ),
			'cart_checkout_leave_same_currency'        => sanitize_text_field( $data['cart_checkout_leave_same_currency'] ?? '' ),
			'currency_by_location'                     => (bool) ( $data['currency_by_location'] ?? false ),
		);

		update_option( 'cpp_behavior_settings', $settings );

		return self::return_response( $settings );
	}
}

return new Behaviour();
