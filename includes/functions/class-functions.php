<?php
/**
 * Functions for Currency per Product for WooCommerce
 */

namespace Tyche\CPP\Functions;

use Automattic\WooCommerce\Utilities\OrderUtil;

defined( 'ABSPATH' ) || exit;

class Functions {

	// -------------------------------------------------------------------------
	// Settings getters
	// -------------------------------------------------------------------------

	/**
	 * Returns the full cpp_general_settings array, or a single key from it.
	 *
	 * @param string $key     Optional key to retrieve. If omitted, the entire array is returned.
	 * @param mixed  $default Default value when the key is not found.
	 * @return mixed
	 */
	public static function get_general_setting( $key = '', $default = null ) {
		$settings = get_option( 'cpp_general_settings', array() );
		if ( '' === $key ) {
			return $settings;
		}
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Returns the full cpp_behavior_settings array, or a single key from it.
	 *
	 * @param string $key     Optional key to retrieve. If omitted, the entire array is returned.
	 * @param mixed  $default Default value when the key is not found.
	 * @return mixed
	 */
	public static function get_behavior_setting( $key = '', $default = null ) {
		$settings = get_option( 'cpp_behavior_settings', array() );
		if ( '' === $key ) {
			return $settings;
		}
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Returns the full cpp_currencies_settings array, or a single key from it.
	 *
	 * @param string $key     Optional key to retrieve. If omitted, the entire array is returned.
	 * @param mixed  $default Default value when the key is not found.
	 * @return mixed
	 */
	public static function get_currencies_setting( $key = '', $default = null ) {
		$settings = get_option( 'cpp_currencies_settings', array() );
		if ( '' === $key ) {
			return $settings;
		}
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Returns the full cpp_exchange_rates array, or a single key from it.
	 *
	 * @param string $key     Optional key to retrieve. If omitted, the entire array is returned.
	 * @param mixed  $default Default value when the key is not found.
	 * @return mixed
	 */
	public static function get_exchange_rate_setting( $key = '', $default = null ) {
		$settings = get_option( 'cpp_exchange_rates', array() );
		if ( '' === $key ) {
			return $settings;
		}
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Returns the full cpp_advanced_settings array, or a single key from it.
	 *
	 * @param string $key     Optional key to retrieve. If omitted, the entire array is returned.
	 * @param mixed  $default Default value when the key is not found.
	 * @return mixed
	 */
	public static function get_advanced_setting( $key = '', $default = null ) {
		$settings = get_option( 'cpp_advanced_settings', array() );
		if ( '' === $key ) {
			return $settings;
		}
		return $settings[ $key ] ?? $default;
	}

	// -------------------------------------------------------------------------

    /**
     * This function calculates the deposit amount and returns the price
     *
     * @param int   $product_id Product ID.
     * @param float $value Value.
     * @param bool  $is_formatted default false.
     *
     * @since 2.13
     * @return mixed value
     */
    public static function get_converted_value( $product_id, $value, $is_formatted = false ) {
        $wc_price_args    = bkap_common::get_currency_args();
        $product_currency = get_post_meta( $product_id, '_alg_wc_cpp_currency', true );
        $shop_currency    = ( ! empty( $wc_price_args ) && isset( $wc_price_args['currency'] ) && ! empty( $wc_price_args['currency'] ) ) ? $wc_price_args['currency'] : get_woocommerce_currency();

        $value = ( '' != $value && '' != $product_currency && $shop_currency != $product_currency ? //phpcs:ignore
            $value * Exchange_Rate_Functions::get_currency_exchange_rate( $product_currency ) : $value );

        $formatted_value = wc_price( $value, $wc_price_args );

        return ( $is_formatted ) ? $formatted_value : $value;
    }

    /**
	 * Fetches the product ID.
	 *
	 * @version 1.4.0
	 * @since   1.3.0
	 * @param mixed $_product Product Object.
	 */
	public static function get_product_id( $_product ) {
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		return $_product->get_id();
	}

    /**
	 * Fetches the product ID or variation parent ID if the product is a variation.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 * @param mixed $_product Product Object.
	 */
	public static function get_product_id_or_variation_parent_id( $_product ) {
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		
        return ( $_product->is_type( 'variation' ) ? $_product->get_parent_id() : $_product->get_id() );
	}

    /**
	 * Hook: alg_wc_cpp_get_product_display_price.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 * @param mixed  $_product Product Object.
	 * @param string $price Price Object.
	 * @param int    $qty Quantity Object.
	 */
	public static function get_product_display_price( $_product, $price = '', $qty = 1 ) {
		return wc_get_price_to_display(
            $_product,
            array(
                'price' => $price,
                'qty'   => $qty,
            )
        );
	}

    /**
	 * Function to get the term ids from product id.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $taxonomy Product taxonomies.
	 * @version 1.4.9
	 * @since   1.4.9
	 *
	 * @return array $_terms_id
	 */
	public static function get_product_terms( $product_id, $taxonomy ) {

		if ( ! $product_id ) {
			return;
		}

		$_terms = get_the_terms( $product_id, $taxonomy );

		$_terms_id = array();
		if ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ) {
			foreach ( $_terms as $_term ) {
				array_push( $_terms_id, $_term->term_id );
			}
		}
		return $_terms_id;
	}

    /**
	 * Checks if the user has the specified role(s).
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 * @param array $roles_to_check Roles to check.
	 * @param int   $user_id User ID.
	 * @return  bool
	 */
	public static function has_user_role( $roles_to_check, $user_id = 0 ) {

		$_user = ( 0 == $user_id ? wp_get_current_user() : get_user_by( 'id', $user_id ) ); //phpcs:ignore
		if ( empty( $_user->roles ) ) {
			$_user->roles = array( 'guest' );
		}
		if ( ! is_array( $_user->roles ) ) {
			$_user->roles = (array) $_user->roles;
		}

		if ( ! is_array( $roles_to_check ) ) {
			$roles_to_check = (array) $roles_to_check;
		}
		if ( in_array( 'administrator', $roles_to_check ) ) { //phpcs:ignore
			$roles_to_check[] = 'super_admin';
		}

		$_intersect = array_intersect( $roles_to_check, $_user->roles );
		return ( ! empty( $_intersect ) );
	}

    /**
	 * Calculates and updates the product price based on the exchange rate if the product currency is different from the shop currency.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 * @param int    $product_id Product ID.
	 * @param string $shop_currency Shop Currency.
	 */
	public static function calculate_and_update_product_price( $product_id, $shop_currency ) {
		$original_price   = get_post_meta( $product_id, '_price', true );
		$product_currency = get_post_meta( $product_id, '_alg_wc_cpp_currency', true );
		$converted_price  = ( '' != $original_price && '' != $product_currency && $shop_currency != $product_currency ? //phpcs:ignore
		$original_price * Exchange_Rate_Functions::get_currency_exchange_rate( $product_currency ) : $original_price );

        update_post_meta( $product_id, '_alg_wc_cpp_converted_price', $converted_price );
	}

    /**
	 * Save the product prices correctly in Quick Edit
	 * when the plugin is set to convert prices in Shop.
	 *
	 * @version 1.4.6
	 * @since 1.4.6
	 * @param mixed $product Product Object.
	 */
	public static function calculate_product_price_quick_edit( $product ) {

		$product_id = $product->get_id();

		// If product currency is different than shop currency.
		$product_currency = get_post_meta( $product_id, '_alg_wc_cpp_currency', true );
		$shop_currency    = get_woocommerce_currency();

		if ( '' !== $product_currency && $shop_currency !== $product_currency ) {

			$base_price = get_post_meta( $product_id, '_price', true );
			$prd_price  = round( $base_price / Exchange_Rate_Functions::get_currency_exchange_rate( $product_currency ), 2 );
			$product->set_price( $prd_price );

			update_post_meta( $product_id, '_alg_wc_cpp_converted_price', $base_price );

			$regular_price     = get_post_meta( $product_id, '_regular_price', true );
			$prd_regular_price = round( $regular_price / Exchange_Rate_Functions::get_currency_exchange_rate( $product_currency ), 2 );
			$product->set_regular_price( $prd_regular_price );

			$sale_price = get_post_meta( $product_id, '_sale_price', true );
			if ( $sale_price > 0 ) {
				$prd_sale_price = round( $sale_price / Exchange_Rate_Functions::get_currency_exchange_rate( $product_currency ), 2 );
				$product->sale_price( $prd_sale_price );
			}
			$product->save();
		}
	}

    /**
	 * Hook: alg_wc_cpp_calculate_all_products_prices.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 * @todo    [dev] maybe also automatically run this when some settings section (e.g.: exchange rates; advanced ...) is saved
	 */
	public static function calculate_all_products_prices() {
		$shop_currency  = get_woocommerce_currency();
		$block_size     = 1024;
		$offset         = 0;
		$total_products = 0;
		while ( true ) {
			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => $block_size,
				'offset'         => $offset,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'fields'         => 'ids',
			);
			$loop = new \WP_Query( $args );
			if ( ! $loop->have_posts() ) {
				break;
			}
			foreach ( $loop->posts as $product_id ) {
				self::calculate_and_update_product_price( $product_id, $shop_currency );
				++$total_products;
			}
			$offset += $block_size;
		}
		return $total_products;
	}

    /**
	 * Checks if HPOS is active.
	 *
	 * @version 1.4.9
	 * @since   1.4.9
	 *
	 * @return array $active_gateways
	 */
	public static function is_hpos_enabled() {

		if ( version_compare( WOOCOMMERCE_VERSION, '7.1.0' ) < 0 ) {
            return false;
        }

        if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
            return true;
        }

        return false;
	}

    /**
	 * Function to get all exchange rates.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @return array $rates
	 */
	public static function get_all_exchange_rates_variation() {
		$rates             = array();
		$base_currency     = get_option( 'woocommerce_currency' );
		$currencies        = self::get_currencies_setting( 'currencies', array() );
		$exchange_settings = self::get_exchange_rate_setting();
		$rates_data        = $exchange_settings['rates'] ?? array();
		$round_enabled     = $exchange_settings['round_exchange_enabled'] ?? false;

		foreach ( $currencies as $idx => $currency_entry ) {
			$currency_code = $currency_entry['currency'] ?? '';
			$exchange_rate = (float) ( $rates_data[ $idx ]['rate'] ?? 1 );

			if ( $currency_code && $exchange_rate > 0 ) {
				$rate = $exchange_rate;
				if ( $round_enabled ) {
					$rate = round( $rate );
				}

				$rates[ $currency_code ] = 0 != $rate ? 1 / $rate : 0;
			}
		}
		$rates[ $base_currency ] = 1.0;
		return $rates;
	}

    /**
	 * Country to currency mapping.
	 *
	 * @return array Country and their corresponding currency codes.
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	public static function get_country_currency_map() {
		return array(
			'AF' => 'AFN', // Afghanistan.
			'AL' => 'ALL', // Albania.
			'DZ' => 'DZD', // Algeria.
			'AS' => 'USD', // American Samoa.
			'AD' => 'EUR', // Andorra.
			'AO' => 'AOA', // Angola.
			'AI' => 'XCD', // Anguilla.
			'AG' => 'XCD', // Antigua and Barbuda.
			'AR' => 'ARS', // Argentina.
			'AM' => 'AMD', // Armenia.
			'AW' => 'AWG', // Aruba.
			'AU' => 'AUD', // Australia.
			'AT' => 'EUR', // Austria.
			'AZ' => 'AZN', // Azerbaijan.
			'BS' => 'BSD', // Bahamas.
			'BH' => 'BHD', // Bahrain.
			'BD' => 'BDT', // Bangladesh.
			'BB' => 'BBD', // Barbados.
			'BY' => 'BYN', // Belarus.
			'BE' => 'EUR', // Belgium.
			'BZ' => 'BZD', // Belize.
			'BJ' => 'XOF', // Benin.
			'BM' => 'BMD', // Bermuda.
			'BT' => 'BTN', // Bhutan.
			'BO' => 'BOB', // Bolivia.
			'BA' => 'BAM', // Bosnia and Herzegovina.
			'BW' => 'BWP', // Botswana.
			'BR' => 'BRL', // Brazil.
			'BN' => 'BND', // Brunei.
			'BG' => 'BGN', // Bulgaria.
			'BF' => 'XOF', // Burkina Faso.
			'BI' => 'BIF', // Burundi.
			'KH' => 'KHR', // Cambodia.
			'CM' => 'XAF', // Cameroon.
			'CA' => 'CAD', // Canada.
			'CV' => 'CVE', // Cape Verde.
			'KY' => 'KYD', // Cayman Islands.
			'CF' => 'XAF', // Central African Republic.
			'TD' => 'XAF', // Chad.
			'CL' => 'CLP', // Chile.
			'CN' => 'CNY', // China.
			'CO' => 'COP', // Colombia.
			'KM' => 'KMF', // Comoros.
			'CG' => 'XAF', // Congo.
			'CD' => 'CDF', // Congo (DRC).
			'CR' => 'CRC', // Costa Rica.
			'HR' => 'HRK', // Croatia.
			'CU' => 'CUP', // Cuba.
			'CY' => 'EUR', // Cyprus.
			'CZ' => 'CZK', // Czech Republic.
			'DK' => 'DKK', // Denmark.
			'DJ' => 'DJF', // Djibouti.
			'DM' => 'XCD', // Dominica.
			'DO' => 'DOP', // Dominican Republic.
			'EC' => 'USD', // Ecuador.
			'EG' => 'EGP', // Egypt.
			'SV' => 'USD', // El Salvador.
			'GQ' => 'XAF', // Equatorial Guinea.
			'ER' => 'ERN', // Eritrea.
			'EE' => 'EUR', // Estonia.
			'SZ' => 'SZL', // Eswatini.
			'ET' => 'ETB', // Ethiopia.
			'FJ' => 'FJD', // Fiji.
			'FI' => 'EUR', // Finland.
			'FR' => 'EUR', // France.
			'GA' => 'XAF', // Gabon.
			'GM' => 'GMD', // Gambia.
			'GE' => 'GEL', // Georgia.
			'DE' => 'EUR', // Germany.
			'GH' => 'GHS', // Ghana.
			'GR' => 'EUR', // Greece.
			'GD' => 'XCD', // Grenada.
			'GT' => 'GTQ', // Guatemala.
			'GN' => 'GNF', // Guinea.
			'GW' => 'XOF', // Guinea-Bissau.
			'GY' => 'GYD', // Guyana.
			'HT' => 'HTG', // Haiti.
			'HN' => 'HNL', // Honduras.
			'HK' => 'HKD', // Hong Kong.
			'HU' => 'HUF', // Hungary.
			'IS' => 'ISK', // Iceland.
			'IN' => 'INR', // India.
			'ID' => 'IDR', // Indonesia.
			'IR' => 'IRR', // Iran.
			'IQ' => 'IQD', // Iraq.
			'IE' => 'EUR', // Ireland.
			'IL' => 'ILS', // Israel.
			'IT' => 'EUR', // Italy.
			'JM' => 'JMD', // Jamaica.
			'JP' => 'JPY', // Japan.
			'JO' => 'JOD', // Jordan.
			'KZ' => 'KZT', // Kazakhstan.
			'KE' => 'KES', // Kenya.
			'KI' => 'AUD', // Kiribati.
			'KR' => 'KRW', // South Korea.
			'KW' => 'KWD', // Kuwait.
			'KG' => 'KGS', // Kyrgyzstan.
			'LA' => 'LAK', // Laos.
			'LV' => 'EUR', // Latvia.
			'LB' => 'LBP', // Lebanon.
			'LS' => 'LSL', // Lesotho.
			'LR' => 'LRD', // Liberia.
			'LY' => 'LYD', // Libya.
			'LI' => 'CHF', // Liechtenstein.
			'LT' => 'EUR', // Lithuania.
			'LU' => 'EUR', // Luxembourg.
			'MG' => 'MGA', // Madagascar.
			'MW' => 'MWK', // Malawi.
			'MY' => 'MYR', // Malaysia.
			'MV' => 'MVR', // Maldives.
			'ML' => 'XOF', // Mali.
			'MT' => 'EUR', // Malta.
			'MH' => 'USD', // Marshall Islands.
			'MR' => 'MRU', // Mauritania.
			'MU' => 'MUR', // Mauritius.
			'MX' => 'MXN', // Mexico.
			'FM' => 'USD', // Micronesia.
			'MD' => 'MDL', // Moldova.
			'MC' => 'EUR', // Monaco.
			'MN' => 'MNT', // Mongolia.
			'ME' => 'EUR', // Montenegro.
			'MA' => 'MAD', // Morocco.
			'MZ' => 'MZN', // Mozambique.
			'MM' => 'MMK', // Myanmar.
			'NA' => 'NAD', // Namibia.
			'NR' => 'AUD', // Nauru.
			'NP' => 'NPR', // Nepal.
			'NL' => 'EUR', // Netherlands.
			'NZ' => 'NZD', // New Zealand.
			'NI' => 'NIO', // Nicaragua.
			'NE' => 'XOF', // Niger.
			'NG' => 'NGN', // Nigeria.
			'NO' => 'NOK', // Norway.
			'OM' => 'OMR', // Oman.
			'PK' => 'PKR', // Pakistan.
			'PW' => 'USD', // Palau.
			'PA' => 'PAB', // Panama.
			'PG' => 'PGK', // Papua New Guinea.
			'PY' => 'PYG', // Paraguay.
			'PE' => 'PEN', // Peru.
			'PH' => 'PHP', // Philippines.
			'PL' => 'PLN', // Poland.
			'PT' => 'EUR', // Portugal.
			'QA' => 'QAR', // Qatar.
			'RO' => 'RON', // Romania.
			'RU' => 'RUB', // Russia.
			'RW' => 'RWF', // Rwanda.
			'KN' => 'XCD', // Saint Kitts and Nevis.
			'LC' => 'XCD', // Saint Lucia.
			'VC' => 'XCD', // Saint Vincent and the Grenadines.
			'WS' => 'WST', // Samoa.
			'SM' => 'EUR', // San Marino.
			'ST' => 'STN', // São Tomé and Príncipe.
			'SA' => 'SAR', // Saudi Arabia.
			'SN' => 'XOF', // Senegal.
			'RS' => 'RSD', // Serbia.
			'SC' => 'SCR', // Seychelles.
			'SL' => 'SLL', // Sierra Leone.
			'SG' => 'SGD', // Singapore.
			'SK' => 'EUR', // Slovakia.
			'SI' => 'EUR', // Slovenia.
			'SB' => 'SBD', // Solomon Islands.
			'SO' => 'SOS', // Somalia.
			'ZA' => 'ZAR', // South Africa.
			'ES' => 'EUR', // Spain.
			'LK' => 'LKR', // Sri Lanka.
			'SD' => 'SDG', // Sudan.
			'SR' => 'SRD', // Suriname.
			'SE' => 'SEK', // Sweden.
			'CH' => 'CHF', // Switzerland.
			'SY' => 'SYP', // Syria.
			'TW' => 'TWD', // Taiwan.
			'TJ' => 'TJS', // Tajikistan.
			'TZ' => 'TZS', // Tanzania.
			'TH' => 'THB', // Thailand.
			'TL' => 'USD', // Timor-Leste.
			'TG' => 'XOF', // Togo.
			'TO' => 'TOP', // Tonga.
			'TT' => 'TTD', // Trinidad and Tobago.
			'TN' => 'TND', // Tunisia.
			'TR' => 'TRY', // Türkiye.
			'TM' => 'TMT', // Turkmenistan.
			'TV' => 'AUD', // Tuvalu.
			'UG' => 'UGX', // Uganda.
			'UA' => 'UAH', // Ukraine.
			'AE' => 'AED', // United Arab Emirates.
			'GB' => 'GBP', // United Kingdom.
			'US' => 'USD', // United States.
			'UY' => 'UYU', // Uruguay.
			'UZ' => 'UZS', // Uzbekistan.
			'VU' => 'VUV', // Vanuatu.
			'VE' => 'VES', // Venezuela.
			'VN' => 'VND', // Vietnam.
			'YE' => 'YER', // Yemen.
			'ZM' => 'ZMW', // Zambia.
			'ZW' => 'ZWL', // Zimbabwe.
		);
	}
}
class_alias( Functions::class, 'Functions' );
