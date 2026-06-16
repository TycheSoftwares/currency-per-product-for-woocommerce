<?php
/**
 * CPP Admin API Settings Class
 * Handles admin settings-related API functionalities for the CPP plugin.
 * 
 * @package CPP/Admin/API/Settings/General
 */


namespace Tyche\CPP\API;

defined( 'ABSPATH' ) || exit;

class Currencies extends Admin_API {

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
			'settings/currencies',
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
		$settings = get_option( 'cpp_currencies_settings', array() );

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

		$currencies = array();
		$idx        = 0;
		foreach ( $data['currencies'] ?? array() as $entry ) {
			$currencies[ $idx++ ] = array(
				'currency'     => sanitize_text_field( $entry['currency'] ?? '' ),
				'users'        => array_map( 'intval', (array) ( $entry['users'] ?? array() ) ),
				'user_roles'   => array_map( 'sanitize_text_field', (array) ( $entry['user_roles'] ?? array() ) ),
				'product_cats' => self::sanitize_token_list( $entry['product_cats'] ?? array() ),
				'product_tags' => self::sanitize_token_list( $entry['product_tags'] ?? array() ),
				'product_pg'   => array_map( 'sanitize_text_field', (array) ( $entry['product_pg'] ?? array() ) ),
			);
		}

		$settings = array(
			'total_number'            => absint( $data['total_number'] ?? 1 ),
			'by_users_enabled'        => (bool) ( $data['by_users_enabled'] ?? false ),
			'by_user_roles_enabled'   => (bool) ( $data['by_user_roles_enabled'] ?? false ),
			'by_product_cats_enabled' => (bool) ( $data['by_product_cats_enabled'] ?? false ),
			'by_product_tags_enabled' => (bool) ( $data['by_product_tags_enabled'] ?? false ),
			'by_product_pg_enabled'   => (bool) ( $data['by_product_pg_enabled'] ?? false ),
			'currencies'              => $currencies,
		);

		update_option( 'cpp_currencies_settings', $settings );

		return self::return_response( $settings );
	}

	/**
	 * Accepts either plain integer IDs or {value, label} objects from the async
	 * TokenField component and stores them as {value, label} objects so that
	 * labels are preserved across page reloads.
	 *
	 * @param array $items Raw items from the request body.
	 * @return array Normalised [{value: int, label: string}] array.
	 */
	private static function sanitize_token_list( $items ) {
		$result = array();
		foreach ( (array) $items as $item ) {
			if ( is_array( $item ) && isset( $item['value'] ) ) {
				$result[] = array(
					'value' => intval( $item['value'] ),
					'label' => sanitize_text_field( $item['label'] ?? '' ),
				);
			} elseif ( is_numeric( $item ) ) {
				$result[] = array( 'value' => intval( $item ), 'label' => '' );
			}
		}
		return $result;
	}
}

return new Currencies();
