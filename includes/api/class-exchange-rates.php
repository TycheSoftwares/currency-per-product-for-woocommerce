<?php
/**
 * CPP Admin API Settings Class
 * Handles admin settings-related API functionalities for the CPP plugin.
 * 
 * @package CPP/Admin/API/Settings/General
 */


namespace Tyche\CPP\API;

defined( 'ABSPATH' ) || exit;

use Tyche\CPP\Functions\Exchange_Rate_Functions;

class Exchange_Rates extends Admin_API {

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

		// Fetch / Save Settings.
		register_rest_route(
			self::$base_endpoint,
			'settings/exchange-rates',
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

		// Trigger an immediate rates update.
		register_rest_route(
			self::$base_endpoint,
			'settings/exchange-rates/update-now',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_now' ),
				'permission_callback' => array( __CLASS__, 'permissions' ),
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
		$settings                    = get_option( 'cpp_exchange_rates', array() );
		$settings['next_scheduled']  = self::get_next_scheduled( $settings );

		return self::return_response( $settings );
	}

	/**
	 * Returns the Unix timestamp of the next scheduled rates update, or null.
	 *
	 * @param array $settings Current exchange-rates settings.
	 * @return int|null
	 */
	private static function get_next_scheduled( $settings ) {
		$rate = $settings['exchange_rate_update_rate'] ?? 'daily';
		$ts   = wp_next_scheduled( 'alg_wc_cpp_update_exchange_rates', array( $rate ) );
		return $ts ? (int) $ts : null;
	}

	/**
	 * Immediately fetches fresh exchange rates from the configured server
	 * and returns the updated settings (including next_scheduled).
	 */
	public static function update_now( $request ) {
		Exchange_Rate_Functions::update_exchange_rates();

		$settings                   = get_option( 'cpp_exchange_rates', array() );
		$settings['next_scheduled'] = self::get_next_scheduled( $settings );

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

		$rates = array();
		$idx   = 0;
		foreach ( $data['rates'] ?? array() as $entry ) {
			$rates[ $idx++ ] = array(
				'rate'      => (float) ( $entry['rate'] ?? 1 ),
				'is_manual' => (bool) ( $entry['is_manual'] ?? false ),
			);
		}

		$settings = array(
			'exchange_rate_update'                             => sanitize_text_field( $data['exchange_rate_update'] ?? 'manual' ),
			'exchange_rate_update_rate'                        => sanitize_text_field( $data['exchange_rate_update_rate'] ?? 'daily' ),
			'currency_exchange_rates_server'                   => sanitize_text_field( $data['currency_exchange_rates_server'] ?? 'ecb' ),
			'free_currency_converter_api_key'                  => sanitize_text_field( $data['free_currency_converter_api_key'] ?? '' ),
			'coinmarketcap_api_key'                            => sanitize_text_field( $data['coinmarketcap_api_key'] ?? '' ),
			'exchange_fees_types'                              => sanitize_text_field( $data['exchange_fees_types'] ?? 'markupflat' ),
			'apply_discount_automatic_additional_exchange_fee' => (float) ( $data['apply_discount_automatic_additional_exchange_fee'] ?? 0 ),
			'round_exchange_enabled'                           => (bool) ( $data['round_exchange_enabled'] ?? false ),
			'rates'                                            => $rates,
		);

		update_option( 'cpp_exchange_rates', $settings );

		return self::return_response( $settings );
	}
}

return new Exchange_Rates();
