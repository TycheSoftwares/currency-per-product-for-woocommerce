<?php
/**
 * Currency per Product for WooCommerce - Migration Class
 *
 * Migrates legacy individual wp_options (alg_wc_cpp_*) to the new consolidated
 * option arrays used by the REST API layer:
 *   - cpp_general_settings
 *   - cpp_behavior_settings
 *   - cpp_currencies_settings
 *   - cpp_exchange_rates
 *   - cpp_advanced_settings
 *
 * @version 2.0.0
 * @since   2.0.0
 * @package Currency per Product for WooCommerce Pro/includes
 */

namespace Tyche\CPP;

defined( 'ABSPATH' ) || exit;

class Migration {


	/**
	 * Current plugin version used as the migration target version.
	 *
	 * @var string
	 */
	const CURRENT_VERSION = CPP_VERSION;

	/**
	 * Constructor. Registers activation hook and hooks maybe_migrate onto admin_init.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_migrate' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_reindex_to_zero' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_convert_terms_to_objects' ) );
	}

	/**
	 * Plugin activation handler.
	 *
	 * Saves default settings for a fresh install only (i.e. when neither the new
	 * consolidated options nor the legacy alg_wc_cpp_* options exist).
	 * Also stamps alg_wc_cpp_version so maybe_migrate() is skipped on first load.
	 *
	 * @since 2.0.0
	 */
	public static function activate() {
		$is_fresh_install = ! get_option( 'cpp_general_settings', false )
			&& ! get_option( 'alg_wc_cpp_enabled', false );

		if ( ! $is_fresh_install ) {
			return;
		}

		$currency_from = get_woocommerce_currency();

		update_option(
			'cpp_general_settings',
			array(
				'enabled'                         => true,
				'currency_reports_enabled'        => true,
				'custom_currency_symbol_enabled'  => false,
				'custom_currency_symbol_template' => '%currency_code%%currency_symbol%',
				'round_off_decimal_points'        => false,
			)
		);

		update_option(
			'cpp_behavior_settings',
			array(
				'shop_behaviour'                           => 'show_in_different',
				'original_price_in_shop_enabled'           => false,
				'original_price_in_shop_template'          => '<br>%price%',
				'cart_checkout'                            => 'convert_shop_default',
				'original_price_in_cart_checkout_enabled'  => false,
				'original_price_in_cart_checkout_template' => '<br>%price%',
				'cart_checkout_leave_one_product'          => __( 'Only one product can be added to the cart. Clear the cart or finish the order, before adding another product to the cart.', 'currency-per-product-for-woocommerce' ),
				'cart_checkout_leave_same_currency'        => __( 'Only products with same currency can be added to the cart. Clear the cart or finish the order, before adding products with another currency to the cart.', 'currency-per-product-for-woocommerce' ),
				'currency_by_location'                     => false,
			)
		);

		update_option(
			'cpp_currencies_settings',
			array(
				'total_number'            => 1,
				'by_users_enabled'        => false,
				'by_user_roles_enabled'   => false,
				'by_product_cats_enabled' => false,
				'by_product_tags_enabled' => false,
				'by_product_pg_enabled'   => false,
				'currencies'              => array(
					0 => array(
						'currency'     => $currency_from,
						'users'        => array(),
						'user_roles'   => array(),
						'product_cats' => array(),
						'product_tags' => array(),
						'product_pg'   => array(),
					),
				),
			)
		);

		update_option(
			'cpp_exchange_rates',
			array(
				'exchange_rate_update'                             => 'manual',
				'exchange_rate_update_rate'                        => 'daily',
				'currency_exchange_rates_server'                   => 'ecb',
				'free_currency_converter_api_key'                  => '',
				'coinmarketcap_api_key'                            => '',
				'exchange_fees_types'                              => 'markupflat',
				'apply_discount_automatic_additional_exchange_fee' => 0,
				'round_exchange_enabled'                           => false,
				'rates'                                            => array(
					0 => array(
						'rate'      => 1,
						'is_manual' => false,
					),
				),
			)
		);

		update_option(
			'cpp_advanced_settings',
			array(
				'fix_mini_cart'             => false,
				'sort_by_converted_price'   => false,
				'filter_by_converted_price' => false,
				'save_products_prices'      => false,
			)
		);

		// Stamp the version so maybe_migrate() is skipped on the first admin load.
		update_option( 'alg_wc_cpp_version', self::CURRENT_VERSION );
	}

	/**
	 * Converts a legacy 'yes'/'no' string value to a boolean.
	 *
	 * @param string $value   Option value retrieved from the database.
	 * @param bool   $default Default boolean to use when the option was not set.
	 *
	 * @since 2.0.0
	 */
	private static function to_bool( $value, bool $default = false ): bool {
		if ( 'yes' === $value ) {
			return true;
		}
		if ( 'no' === $value ) {
			return false;
		}
		return $default;
	}

	/**
	 * Run migration when the stored alg_wc_cpp_version differs from the current version.
	 *
	 * @since 2.0.0
	 */
	public static function maybe_migrate() {
		$stored_version = get_option( 'alg_wc_cpp_version', '' );

		if ( version_compare( $stored_version, self::CURRENT_VERSION, '>=' ) ) {
			return;
		}

		// No legacy options exist — nothing to migrate (e.g. after a full data reset).
		// Stamp the version and bail so default settings are not re-created from PHP fallbacks.
		if ( ! get_option( 'alg_wc_cpp_enabled', false ) ) {
			update_option( 'alg_wc_cpp_version', self::CURRENT_VERSION );
			return;
		}

		self::migrate_general();
		self::migrate_behaviour();
		self::migrate_currencies();
		self::migrate_exchange_rates();
		self::migrate_advanced();

		update_option( 'alg_wc_cpp_version', self::CURRENT_VERSION );
	}

	// -------------------------------------------------------------------------
	// General
	// -------------------------------------------------------------------------

	/**
	 * Migrate General settings.
	 *
	 * Old options                              → key in cpp_general_settings
	 * alg_wc_cpp_enabled                       → enabled
	 * alg_wc_cpp_currency_reports_enabled      → currency_reports_enabled
	 * alg_wc_cpp_custom_currency_symbol_enabled→ custom_currency_symbol_enabled
	 * alg_wc_cpp_custom_currency_symbol_template→custom_currency_symbol_template
	 * alg_wc_cpp_round_off_decimal_points      → round_off_decimal_points
	 *
	 * @since 2.0.0
	 */
	public static function migrate_general() {
		if ( get_option( 'cpp_general_settings', false ) ) {
			return;
		}

		$settings = array(
			'enabled'                         => self::to_bool( get_option( 'alg_wc_cpp_enabled', 'yes' ), true ),
			'currency_reports_enabled'        => self::to_bool( get_option( 'alg_wc_cpp_currency_reports_enabled', 'yes' ), true ),
			'custom_currency_symbol_enabled'  => self::to_bool( get_option( 'alg_wc_cpp_custom_currency_symbol_enabled', 'no' ) ),
			'custom_currency_symbol_template' => get_option( 'alg_wc_cpp_custom_currency_symbol_template', '%currency_code%%currency_symbol%' ),
			'round_off_decimal_points'        => self::to_bool( get_option( 'alg_wc_cpp_round_off_decimal_points', 'no' ) ),
		);

		update_option( 'cpp_general_settings', $settings );
	}

	// -------------------------------------------------------------------------
	// Behaviour
	// -------------------------------------------------------------------------

	/**
	 * Migrate Behaviour settings.
	 *
	 * Old options                                          → key in cpp_behavior_settings
	 * alg_wc_cpp_shop_behaviour                            → shop_behaviour
	 * alg_wc_cpp_original_price_in_shop_enabled            → original_price_in_shop_enabled
	 * alg_wc_cpp_original_price_in_shop_template           → original_price_in_shop_template
	 * alg_wc_cpp_cart_checkout                             → cart_checkout
	 * alg_wc_cpp_original_price_in_cart_checkout_enabled   → original_price_in_cart_checkout_enabled
	 * alg_wc_cpp_original_price_in_cart_checkout_template  → original_price_in_cart_checkout_template
	 * alg_wc_cpp_cart_checkout_leave_one_product           → cart_checkout_leave_one_product
	 * alg_wc_cpp_cart_checkout_leave_same_currency         → cart_checkout_leave_same_currency
	 * alg_wc_cpp_currency_by_location                      → currency_by_location
	 *
	 * @since 2.0.0
	 */
	public static function migrate_behaviour() {
		if ( get_option( 'cpp_behavior_settings', false ) ) {
			return;
		}

		$settings = array(
			'shop_behaviour'                           => get_option( 'alg_wc_cpp_shop_behaviour', 'show_in_different' ),
			'original_price_in_shop_enabled'           => self::to_bool( get_option( 'alg_wc_cpp_original_price_in_shop_enabled', 'no' ) ),
			'original_price_in_shop_template'          => get_option( 'alg_wc_cpp_original_price_in_shop_template', '<br>%price%' ),
			'cart_checkout'                            => get_option( 'alg_wc_cpp_cart_checkout', 'convert_shop_default' ),
			'original_price_in_cart_checkout_enabled'  => self::to_bool( get_option( 'alg_wc_cpp_original_price_in_cart_checkout_enabled', 'no' ) ),
			'original_price_in_cart_checkout_template' => get_option( 'alg_wc_cpp_original_price_in_cart_checkout_template', '<br>%price%' ),
			'cart_checkout_leave_one_product'          => get_option(
				'alg_wc_cpp_cart_checkout_leave_one_product',
				__( 'Only one product can be added to the cart. Clear the cart or finish the order, before adding another product to the cart.', 'currency-per-product-for-woocommerce' )
			),
			'cart_checkout_leave_same_currency'        => get_option(
				'alg_wc_cpp_cart_checkout_leave_same_currency',
				__( 'Only products with same currency can be added to the cart. Clear the cart or finish the order, before adding products with another currency to the cart.', 'currency-per-product-for-woocommerce' )
			),
			'currency_by_location'                     => self::to_bool( get_option( 'alg_wc_cpp_currency_by_location', 'no' ) ),
		);

		update_option( 'cpp_behavior_settings', $settings );
	}

	// -------------------------------------------------------------------------
	// Currencies
	// -------------------------------------------------------------------------

	/**
	 * Migrate Currencies settings.
	 *
	 * Scalar options                    → key in cpp_currencies_settings
	 * alg_wc_cpp_total_number           → total_number
	 * alg_wc_cpp_by_users_enabled       → by_users_enabled
	 * alg_wc_cpp_by_user_roles_enabled  → by_user_roles_enabled
	 * alg_wc_cpp_by_product_cats_enabled→ by_product_cats_enabled
	 * alg_wc_cpp_by_product_tags_enabled→ by_product_tags_enabled
	 * alg_wc_cpp_by_product_pg_enabled  → by_product_pg_enabled
	 *
	 * Per-currency options (i = 1 … total_number):
	 * alg_wc_cpp_currency_{i}           → currencies[i][currency]
	 * alg_wc_cpp_users_{i}              → currencies[i][users]
	 * alg_wc_cpp_user_roles_{i}         → currencies[i][user_roles]
	 * alg_wc_cpp_product_cats_{i}       → currencies[i][product_cats]
	 * alg_wc_cpp_product_tags_{i}       → currencies[i][product_tags]
	 * alg_wc_cpp_product_pg_{i}         → currencies[i][product_pg]
	 *
	 * @since 2.0.0
	 */
	public static function migrate_currencies() {
		if ( get_option( 'cpp_currencies_settings', false ) ) {
			return;
		}

		$total_number = (int) get_option( 'alg_wc_cpp_total_number', 1 );
		$currency_from = get_woocommerce_currency();

		$settings = array(
			'total_number'            => $total_number,
			'by_users_enabled'        => self::to_bool( get_option( 'alg_wc_cpp_by_users_enabled', 'no' ) ),
			'by_user_roles_enabled'   => self::to_bool( get_option( 'alg_wc_cpp_by_user_roles_enabled', 'no' ) ),
			'by_product_cats_enabled' => self::to_bool( get_option( 'alg_wc_cpp_by_product_cats_enabled', 'no' ) ),
			'by_product_tags_enabled' => self::to_bool( get_option( 'alg_wc_cpp_by_product_tags_enabled', 'no' ) ),
			'by_product_pg_enabled'   => self::to_bool( get_option( 'alg_wc_cpp_by_product_pg_enabled', 'no' ) ),
			'currencies'              => array(),
		);

		for ( $i = 1; $i <= $total_number; $i++ ) {
			$settings['currencies'][ $i - 1 ] = array(
				'currency'     => get_option( 'alg_wc_cpp_currency_' . $i, $currency_from ),
				'users'        => get_option( 'alg_wc_cpp_users_' . $i, array() ),
				'user_roles'   => get_option( 'alg_wc_cpp_user_roles_' . $i, array() ),
				'product_cats' => self::ids_to_term_objects( get_option( 'alg_wc_cpp_product_cats_' . $i, array() ), 'product_cat' ),
				'product_tags' => self::ids_to_term_objects( get_option( 'alg_wc_cpp_product_tags_' . $i, array() ), 'product_tag' ),
				'product_pg'   => get_option( 'alg_wc_cpp_product_pg_' . $i, array() ),
			);
		}

		update_option( 'cpp_currencies_settings', $settings );
	}

	// -------------------------------------------------------------------------
	// Exchange Rates
	// -------------------------------------------------------------------------

	/**
	 * Migrate Exchange Rates settings.
	 *
	 * Scalar options                                                 → key in cpp_exchange_rates
	 * alg_wc_cpp_exchange_rate_update                               → exchange_rate_update
	 * alg_wc_cpp_exchange_rate_update_rate                          → exchange_rate_update_rate
	 * alg_wc_cpp_currency_exchange_rates_server                     → currency_exchange_rates_server
	 * alg_wc_cpp_free_currency_converter_api_key                    → free_currency_converter_api_key
	 * alg_cpp_coinmarketcap_api_key                                 → coinmarketcap_api_key
	 * alg_wc_cpp_exchange_fees_types                                → exchange_fees_types
	 * alg_wc_cpp_apply_discount_automatic_additional_exchange_fee   → apply_discount_automatic_additional_exchange_fee
	 * alg_wc_cpp_round_exchange_enabled                             → round_exchange_enabled
	 *
	 * Per-currency rate options (i = 1 … total_number):
	 * alg_wc_cpp_exchange_rate_{i}           → rates[i][rate]
	 * alg_wc_cpp_exchange_rate_is_manual_{i} → rates[i][is_manual]
	 *
	 * @since 2.0.0
	 */
	public static function migrate_exchange_rates() {
		if ( get_option( 'cpp_exchange_rates', false ) ) {
			return;
		}

		$total_number = (int) get_option( 'alg_wc_cpp_total_number', 1 );

		$settings = array(
			'exchange_rate_update'                             => get_option( 'alg_wc_cpp_exchange_rate_update', 'manual' ),
			'exchange_rate_update_rate'                        => get_option( 'alg_wc_cpp_exchange_rate_update_rate', 'daily' ),
			'currency_exchange_rates_server'                   => get_option( 'alg_wc_cpp_currency_exchange_rates_server', 'ecb' ),
			'free_currency_converter_api_key'                  => get_option( 'alg_wc_cpp_free_currency_converter_api_key', '' ),
			'coinmarketcap_api_key'                            => get_option( 'alg_cpp_coinmarketcap_api_key', '' ),
			'exchange_fees_types'                              => get_option( 'alg_wc_cpp_exchange_fees_types', 'markupflat' ),
			'apply_discount_automatic_additional_exchange_fee' => get_option( 'alg_wc_cpp_apply_discount_automatic_additional_exchange_fee', 0 ),
			'round_exchange_enabled'                           => self::to_bool( get_option( 'alg_wc_cpp_round_exchange_enabled', 'no' ) ),
			'rates'                                            => array(),
		);

		for ( $i = 1; $i <= $total_number; $i++ ) {
			$settings['rates'][ $i - 1 ] = array(
				'rate'      => get_option( 'alg_wc_cpp_exchange_rate_' . $i, 1 ),
				'is_manual' => self::to_bool( get_option( 'alg_wc_cpp_exchange_rate_is_manual_' . $i, 'no' ) ),
			);
		}

		update_option( 'cpp_exchange_rates', $settings );
	}

	// -------------------------------------------------------------------------
	// Advanced
	// -------------------------------------------------------------------------

	/**
	 * Migrate Advanced settings.
	 *
	 * Old options                            → key in cpp_advanced_settings
	 * alg_wc_cpp_fix_mini_cart               → fix_mini_cart
	 * alg_wc_cpp_sort_by_converted_price     → sort_by_converted_price
	 * alg_wc_cpp_filter_by_converted_price   → filter_by_converted_price
	 * alg_wc_cpp_save_products_prices        → save_products_prices
	 *
	 * @since 2.0.0
	 */
	public static function migrate_advanced() {
		if ( get_option( 'cpp_advanced_settings', false ) ) {
			return;
		}

		$settings = array(
			'fix_mini_cart'             => self::to_bool( get_option( 'alg_wc_cpp_fix_mini_cart', 'no' ) ),
			'sort_by_converted_price'   => self::to_bool( get_option( 'alg_wc_cpp_sort_by_converted_price', 'no' ) ),
			'filter_by_converted_price' => self::to_bool( get_option( 'alg_wc_cpp_filter_by_converted_price', 'no' ) ),
			'save_products_prices'      => self::to_bool( get_option( 'alg_wc_cpp_save_products_prices', 'no' ) ),
		);

		update_option( 'cpp_advanced_settings', $settings );
	}

	// -------------------------------------------------------------------------
	// Re-index existing data to 0-based (one-time, runs once per site)
	// -------------------------------------------------------------------------

	/**
	 * Converts any existing 1-based currencies/rates arrays in the DB to 0-based.
	 * Runs once and stores a flag so it is never repeated.
	 *
	 * @since 2.0.1
	 */
	public static function maybe_reindex_to_zero() {
		if ( get_option( 'cpp_reindexed_to_zero', false ) ) {
			return;
		}

		// Re-index currencies.
		$currencies_settings = get_option( 'cpp_currencies_settings', array() );
		if ( ! empty( $currencies_settings['currencies'] ) ) {
			$keys = array_keys( $currencies_settings['currencies'] );
			sort( $keys, SORT_NUMERIC );
			if ( isset( $keys[0] ) && (int) $keys[0] !== 0 ) {
				$reindexed = array();
				$idx       = 0;
				foreach ( $currencies_settings['currencies'] as $entry ) {
					$reindexed[ $idx++ ] = $entry;
				}
				$currencies_settings['currencies'] = $reindexed;
				update_option( 'cpp_currencies_settings', $currencies_settings );
			}
		}

		// Re-index rates.
		$exchange_rates = get_option( 'cpp_exchange_rates', array() );
		if ( ! empty( $exchange_rates['rates'] ) ) {
			$keys = array_keys( $exchange_rates['rates'] );
			sort( $keys, SORT_NUMERIC );
			if ( isset( $keys[0] ) && (int) $keys[0] !== 0 ) {
				$reindexed = array();
				$idx       = 0;
				foreach ( $exchange_rates['rates'] as $entry ) {
					$reindexed[ $idx++ ] = $entry;
				}
				$exchange_rates['rates'] = $reindexed;
				update_option( 'cpp_exchange_rates', $exchange_rates );
			}
		}

		update_option( 'cpp_reindexed_to_zero', true );
	}

	// -------------------------------------------------------------------------
	// Convert plain integer IDs to {value, label} objects (one-time)
	// -------------------------------------------------------------------------

	/**
	 * Walks existing cpp_currencies_settings and converts any product_cats /
	 * product_tags entries that are still plain integers into {value, label}
	 * objects. Runs once per site and stores a flag.
	 *
	 * @since 2.0.1
	 */
	public static function maybe_convert_terms_to_objects() {
		if ( get_option( 'cpp_terms_converted_to_objects', false ) ) {
			return;
		}

		$settings = get_option( 'cpp_currencies_settings', array() );

		if ( ! empty( $settings['currencies'] ) ) {
			$changed = false;
			foreach ( $settings['currencies'] as $idx => $currency ) {
				foreach ( array( 'product_cats' => 'product_cat', 'product_tags' => 'product_tag' ) as $field => $taxonomy ) {
					if ( ! empty( $currency[ $field ] ) ) {
						$converted                            = self::ids_to_term_objects( $currency[ $field ], $taxonomy );
						$settings['currencies'][ $idx ][ $field ] = $converted;
						$changed                              = true;
					}
				}
			}
			if ( $changed ) {
				update_option( 'cpp_currencies_settings', $settings );
			}
		}

		update_option( 'cpp_terms_converted_to_objects', true );
	}

	/**
	 * Converts an array of term IDs (or already-converted objects) to
	 * [{value: int, label: string}] format, looking up term names from the DB.
	 *
	 * @param array  $items    Raw IDs or existing objects.
	 * @param string $taxonomy WordPress taxonomy slug.
	 * @return array
	 */
	private static function ids_to_term_objects( $items, $taxonomy ) {
		$result = array();
		foreach ( (array) $items as $item ) {
			if ( is_array( $item ) && isset( $item['value'] ) ) {
				// Already in object format — keep as-is (refresh label from DB).
				$id   = intval( $item['value'] );
				$term = get_term( $id, $taxonomy );
				$result[] = array(
					'value' => $id,
					'label' => ( $term && ! is_wp_error( $term ) ) ? $term->name : ( $item['label'] ?? '' ),
				);
			} elseif ( is_numeric( $item ) ) {
				$id   = intval( $item );
				$term = get_term( $id, $taxonomy );
				$result[] = array(
					'value' => $id,
					'label' => ( $term && ! is_wp_error( $term ) ) ? $term->name : '',
				);
			}
		}
		return $result;
	}
}

class_alias( Migration::class, 'CPP_Migration' );
new Migration();
