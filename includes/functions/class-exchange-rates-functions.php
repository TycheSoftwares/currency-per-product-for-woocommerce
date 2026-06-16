<?php
/**
 * Currency per Product for WooCommerce - Exchange Rate Functions
 *
 * Class-based wrapper for all exchange rate utility functions.
 *
 * @version 2.0.0
 * @since   2.0.0
 * @package Currency per Product for WooCommerce Pro/includes/functions
 */

namespace Tyche\CPP\Functions;

defined( 'ABSPATH' ) || exit;

class Exchange_Rate_Functions {

	/**
	 * Get exchange rates.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @return array
	 */
	public static function get_exchange_rates() {
		return Functions::get_exchange_rate_setting();
	}

	/**
	 * Gets the exchange rate for a given currency code.
	 *
	 * Retrieves the exchange rate for the provided currency code, applies
	 * additional fees or discounts if necessary, and optionally rounds the
	 * exchange rate based on plugin settings.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @param string $currency_code The currency code to get the exchange rate for.
	 * @return float The exchange rate for the provided currency code. Returns 1 if no valid rate is found.
	 */
	public static function get_currency_exchange_rate( $currency_code ) {
		$base_currency  = get_option( 'woocommerce_currency' );
		$currencies     = Functions::get_currencies_setting( 'currencies', array() );
		$total_number   = Functions::get_currencies_setting( 'total_number', 1 );
		$rates          = Functions::get_exchange_rate_setting( 'rates', array() );

		for ( $i = 0; $i < $total_number; $i++ ) {
			$currency = $currencies[ $i ]['currency'] ?? $base_currency;
			if ( $currency_code === $currency ) {
				$exchange_rate = $rates[ $i ]['rate'] ?? 1;
				if ( is_numeric( $exchange_rate ) && 0 != $exchange_rate ) { //phpcs:ignore
					if ( Functions::get_exchange_rate_setting( 'exchange_rate_update', 'manual' ) === 'auto' && 0 != Functions::get_exchange_rate_setting( 'apply_discount_automatic_additional_exchange_fee', 0 ) ) { //phpcs:ignore
						$exchange_fee_types = Functions::get_exchange_rate_setting( 'exchange_fees_types', '' );
						$exchange_rate_fee  = Functions::get_exchange_rate_setting( 'apply_discount_automatic_additional_exchange_fee', 0 );

						switch ( $exchange_fee_types ) {
							case 'markupflat':
								$exchange_rate = $exchange_rate + $exchange_rate_fee;
								break;
							case 'discountflat':
								$exchange_rate = $exchange_rate - $exchange_rate_fee;
								break;
							case 'markuppercen':
								$exchange_rate = $exchange_rate + ( $exchange_rate * $exchange_rate_fee ) / 100;
								break;
							case 'discountpercen':
								$exchange_rate = $exchange_rate - ( $exchange_rate * $exchange_rate_fee ) / 100;
								break;
							default:
								$exchange_rate = $exchange_rate + $exchange_rate_fee;
						}
					}
					if ( true === Functions::get_exchange_rate_setting( 'round_exchange_enabled', false ) ) {
						$exchange_rate = round( $exchange_rate );
					}
					return ( 0 != $exchange_rate ? ( 1 / $exchange_rate ) : 1 ); //phpcs:ignore
				}
			}
		}
		return 1;
	}

	/**
	 * Updates exchange rates for all configured currencies.
	 *
	 * Retrieves exchange rates for each currency configured in the plugin and
	 * updates the exchange rates accordingly if the rates are not set to manual.
	 *
	 * @version 2.0.0
	 * @since   1.4.0
	 *
	 * @return void
	 */
	public static function update_exchange_rates() {
		$currency_from   = get_woocommerce_currency();
		$currencies      = Functions::get_currencies_setting( 'currencies', array() );
		$total_number    = Functions::get_currencies_setting( 'total_number', 1 );
		$exchange_rates  = Functions::get_exchange_rate_setting();

		for ( $i = 0; $i < $total_number; $i++ ) {
			$currency_to   = $currencies[ $i ]['currency'] ?? $currency_from;
			$is_manual     = $exchange_rates['rates'][ $i ]['is_manual'] ?? false;
			$exchange_rate = self::get_exchange_rate( $currency_from, $currency_to );
			if ( false !== $exchange_rate && true !== $is_manual ) {
				$exchange_rates['rates'][ $i ]['rate'] = $exchange_rate;
			}
		}

		update_option( 'cpp_exchange_rates', $exchange_rates );
	}

	/**
	 * Returns all available exchange rate server options.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @return array
	 */
	public static function get_currency_exchange_rate_servers() {
		return array(
			'ecb'                  => __( 'European Central Bank (ECB)', 'currency-per-product-for-woocommerce' ),
			'tcmb'                 => __( 'TCMB', 'currency-per-product-for-woocommerce' ),
			'currencyconverterapi' => __( 'Free Currency Converter API (free.currencyconverterapi.com)', 'currency-per-product-for-woocommerce' ),
			'coinbase'             => __( 'Coinbase', 'currency-per-product-for-woocommerce' ),
			'coinmarketcap'        => __( 'CoinMarketCap (for Cryptocurrencies)', 'currency-per-product-for-woocommerce' ),
		);
	}

	/**
	 * Retrieves the name of the currency exchange rate server based on the server ID.
	 *
	 * If no server ID is provided, retrieves the default server ID from plugin options.
	 *
	 * @version 2.0.0
	 * @since   1.4.0
	 *
	 * @param string $server_id The ID of the exchange rate server. Defaults to plugin option ('ecb').
	 * @return string The name of the exchange rate server.
	 */
	public static function get_currency_exchange_rate_server_name( $server_id = '' ) {
		if ( '' === $server_id ) {
			$server_id = Functions::get_exchange_rate_setting( 'currency_exchange_rates_server', 'ecb' );
		}
		$servers = self::get_currency_exchange_rate_servers();
		return ( isset( $servers[ $server_id ] ) ? $servers[ $server_id ] : $servers['ecb'] );
	}

	/**
	 * Retrieves the exchange rate between two currencies using a specified server.
	 *
	 * If the "calculate by invert" option is enabled, inverts the currencies before
	 * retrieving the exchange rate.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @param string $currency_from The source currency code.
	 * @param string $currency_to   The target currency code.
	 * @return float|false The exchange rate, or false if an error occurs.
	 */
	public static function get_exchange_rate( $currency_from, $currency_to ) {
		$exchange_rates_server = Functions::get_exchange_rate_setting( 'currency_exchange_rates_server', 'ecb' );
		$calculate_by_invert   = get_option( 'alg_wc_cpp_currency_exchange_rates_calculate_by_invert', 'no' );

		if ( 'yes' === $calculate_by_invert ) {
			$_currency_to  = $currency_to;
			$currency_to   = $currency_from;
			$currency_from = $_currency_to;
		}
		switch ( $exchange_rates_server ) {
			case 'currencyconverterapi':
				$return = self::currencyconverterapi_get_exchange_rate( $currency_from, $currency_to );
				break;
			case 'coinmarketcap':
				$return = self::get_exchange_rate_coinmarketcap( $currency_from, $currency_to );
				break;
			case 'tcmb':
				$return = self::tcmb_get_exchange_rate( $currency_from, $currency_to );
				break;
			case 'coinbase':
				$return = self::get_exchange_rate_coinbase( $currency_from, $currency_to );
				$return = number_format( floatval( $return ), 8, '.', '' );
				break;
			default: // 'ecb':
				$return = self::ecb_get_exchange_rate( $currency_from, $currency_to );
				break;
		}
		return ( 'yes' === $calculate_by_invert && 0 != $return ? round( ( 1 / $return ), 6 ) : $return ); //phpcs:ignore
	}

	/**
	 * Retrieves the response from a given URL.
	 *
	 * Attempts to retrieve the URL content using cURL, falling back to
	 * file_get_contents() if allowed by the server.
	 *
	 * @version 1.4.0
	 * @since   1.3.0
	 *
	 * @param string $url The URL to fetch the response from.
	 * @return string|false The response content, or false if the request fails.
	 */
	public static function get_url_response( $url ) {
		$response = false;
		if ( function_exists( 'curl_version' ) ) {
			$curl = curl_init( $url ); // phpcs:ignore
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 ); // phpcs:ignore
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true ); // phpcs:ignore
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 ); // phpcs:ignore
			$response = curl_exec( $curl ); // phpcs:ignore
			if ( curl_errno( $curl ) ) { // phpcs:ignore
				$response = false;
			}
			curl_close( $curl ); // phpcs:ignore
		} elseif ( ini_get( 'allow_url_fopen' ) ) {
			$response = file_get_contents( $url ); // phpcs:ignore
		}
		return $response;
	}

	/**
	 * Retrieves the exchange rate from CoinMarketCap API.
	 *
	 * Fetches the exchange rate between two currencies using the CoinMarketCap API.
	 * If the exchange rate is 0, attempts to reverse the currency pair and try again.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 *
	 * @param string  $currency_from The base currency to convert from.
	 * @param string  $currency_to   The target currency to convert to.
	 * @param boolean $try_reverse   Whether to attempt a reverse conversion if the initial query fails. Default true.
	 * @return string The exchange rate as a formatted string.
	 */
	public static function get_exchange_rate_coinmarketcap( $currency_from, $currency_to, $try_reverse = true ) {
		$return   = 0;
		$api_key  = Functions::get_exchange_rate_setting( 'coinmarketcap_api_key', '' );
		$response = self::get_currency_exchange_rates_url_response(
			"https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol=$currency_to&convert=$currency_from",
			array(
				'Accepts'           => 'application/json',
				'X-CMC_PRO_API_KEY' => $api_key,
			)
		);
		if ( isset( $response->data->{$currency_to}->quote->{$currency_from}->price ) ) {
			$return = $response->data->{$currency_to}->quote->{$currency_from}->price;
			$return = round( ( 1 / $return ), 12 );
		}
		if ( 0 === $return && $try_reverse ) {
			$return = self::get_exchange_rate_coinmarketcap( $currency_to, $currency_from, false );
		}
		$return = number_format( floatval( $return ), 8, '.', '' );
		return $return;
	}

	/**
	 * Retrieves the response from a given URL with optional headers and JSON decoding.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 *
	 * @param string  $url            The URL to make the HTTP request to.
	 * @param array   $headers        Optional. Array of headers to include in the request.
	 * @param boolean $do_json_decode Optional. Whether to decode the response as JSON. Default true.
	 * @return mixed The response body, or false on failure.
	 */
	public static function get_currency_exchange_rates_url_response( $url, $headers = array(), $do_json_decode = true ) {
		$response = apply_filters( 'wpw_cs_http_request', false, $url, $headers );
		if ( ! $response ) {
			$response = wp_remote_get(
				$url,
				array(
					'sslverify' => false,
					'timeout'   => 10,
					'headers'   => $headers,
				)
			);
			if ( ! is_wp_error( $response ) ) {
				$response = $response['body'];
			} else {
				$response = false;
			}
		}
		if ( false !== $response && $do_json_decode ) {
			$response = json_decode( $response );
		}
		return $response;
	}

	/**
	 * Retrieves the exchange rate from Coinbase.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 *
	 * @param string $currency_from The base currency.
	 * @param string $currency_to   The target currency.
	 * @return float The exchange rate, or 0 if not available.
	 */
	public static function get_exchange_rate_coinbase( $currency_from, $currency_to ) {
		$response = self::get_currency_exchange_rates_url_response( "https://api.coinbase.com/v2/exchange-rates?currency=$currency_from" );
		return ( isset( $response->data->rates->{$currency_to} ) ? $response->data->rates->{$currency_to} : 0 );
	}

	/**
	 * Retrieves and parses XML data from a specified URL.
	 *
	 * Prioritizes using allow_url_fopen with simplexml_load_file, falling back to cURL.
	 *
	 * @version 1.4.1
	 * @since   1.4.1
	 *
	 * @param string $url The URL to fetch XML from.
	 * @return SimpleXMLElement|false The XML response object or false on failure.
	 */
	public static function get_url_response_xml( $url ) {
		$response = false;
		if ( ini_get( 'allow_url_fopen' ) && function_exists( 'simplexml_load_file' ) ) {
			$response = simplexml_load_file( $url );
		} elseif ( function_exists( 'curl_version' ) && class_exists( 'SimpleXMLElement' ) ) {
			$curl = curl_init( $url ); // phpcs:ignore
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 ); // phpcs:ignore
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true ); // phpcs:ignore
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 ); // phpcs:ignore
			$response = curl_exec( $curl ); // phpcs:ignore
			if ( curl_errno( $curl ) ) { // phpcs:ignore
				$response = false;
			}
			curl_close( $curl ); // phpcs:ignore
			if ( false !== $response ) {
				libxml_use_internal_errors( true );
				try {
					$response = new SimpleXMLElement( $response );
				} catch ( Exception $e ) {
					return false;
				}
			}
		}
		return $response;
	}

	/**
	 * Retrieves the exchange rate from the Currency Converter API.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @param string $currency_from The base currency.
	 * @param string $currency_to   The target currency.
	 * @return float|false The exchange rate, or false if not available.
	 */
	public static function currencyconverterapi_get_exchange_rate( $currency_from, $currency_to ) {
		$pair     = $currency_from . '_' . $currency_to;
		$url      = 'https://free.currencyconverterapi.com/api/v5/convert?q=' . $pair . '&compact=y&apiKey=' . Functions::get_exchange_rate_setting( 'free_currency_converter_api_key', '' );
		$response = self::get_url_response( $url );
		if ( $response ) {
			$response = json_decode( $response );
			return ( ! empty( $response->{$pair}->val ) ? $response->{$pair}->val : false );
		}
		return false;
	}

	/**
	 * Retrieves the exchange rate for TRY (Turkish Lira) from TCMB.
	 *
	 * @version 1.4.1
	 * @since   1.0.0
	 *
	 * @param string $currency_from The base currency (should not be TRY).
	 * @return float|false The exchange rate, or false if not available.
	 */
	public static function tcmb_get_exchange_rate_try( $currency_from ) {
		if ( 'TRY' === $currency_from ) {
			return 1;
		}
		$xml = self::get_url_response_xml( 'https://www.tcmb.gov.tr/kurlar/today.xml' );
		if ( isset( $xml->Currency ) ) { //phpcs:ignore
			foreach ( $xml->Currency as $the_rate ) { //phpcs:ignore
				$attributes = $the_rate->attributes();
				if ( isset( $attributes['CurrencyCode'] ) ) {
					$currency_code = (string) $attributes['CurrencyCode'];
					if ( $currency_code === $currency_from ) {
						// Possible values: ForexSelling, ForexBuying, BanknoteSelling, BanknoteBuying.
						if ( '' != ( $property_to_check = apply_filters( 'alg_wc_cpp_currency_exchange_rates_tcmb_property_to_check', '' ) ) ) { //phpcs:ignore
							if ( isset( $the_rate->{$property_to_check} ) ) {
								$rate = (float) $the_rate->{$property_to_check};
							} else {
								continue;
							}
						} else { //phpcs:ignore
							if ( isset( $the_rate->ForexSelling ) ) { //phpcs:ignore
								$rate = (float) $the_rate->ForexSelling; //phpcs:ignore
							} elseif ( isset( $the_rate->ForexBuying ) ) { //phpcs:ignore
								$rate = (float) $the_rate->ForexBuying; //phpcs:ignore
							} elseif ( isset( $the_rate->BanknoteSelling ) ) { //phpcs:ignore
								$rate = (float) $the_rate->BanknoteSelling; //phpcs:ignore
							} elseif ( isset( $the_rate->BanknoteBuying ) ) { //phpcs:ignore
								$rate = (float) $the_rate->BanknoteBuying; //phpcs:ignore
							} else {
								continue;
							}
						}
						$unit = ( isset( $the_rate->Unit ) ) ? (float) $the_rate->Unit : 1; //phpcs:ignore
						return ( $rate / $unit );
					}
				}
			}
		}
		return false;
	}

	/**
	 * Retrieves the exchange rate between two currencies using TCMB (Turkish Central Bank).
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @param string $currency_from The base currency.
	 * @param string $currency_to   The target currency.
	 * @return float|false The exchange rate, or false if not available.
	 */
	public static function tcmb_get_exchange_rate( $currency_from, $currency_to ) {
		$currency_from_try = self::tcmb_get_exchange_rate_try( strtoupper( $currency_from ) );
		if ( false == $currency_from_try ) { //phpcs:ignore
			return false;
		}
		$currency_to_try = self::tcmb_get_exchange_rate_try( strtoupper( $currency_to ) );
		if ( false == $currency_to_try ) { //phpcs:ignore
			return false;
		}
		if ( 1 == $currency_to_try ) { //phpcs:ignore
			return round( $currency_from_try, 6 );
		}
		return round( ( $currency_from_try / $currency_to_try ), 6 );
	}

	/**
	 * Retrieves the exchange rate between two currencies using ECB (European Central Bank).
	 *
	 * @version 1.4.1
	 * @since   1.0.0
	 *
	 * @param string $currency_from The base currency.
	 * @param string $currency_to   The target currency.
	 * @return float|false The exchange rate, or false if not available.
	 */
	public static function ecb_get_exchange_rate( $currency_from, $currency_to ) {
		$final_rate = false;
		$xml        = self::get_url_response_xml( 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml' );
		if ( isset( $xml->Cube->Cube->Cube ) ) { //phpcs:ignore
			if ( 'EUR' === $currency_from ) {
				$EUR_currency_from_rate = 1; //phpcs:ignore
			}
			if ( 'EUR' === $currency_to ) {
				$EUR_currency_to_rate = 1; //phpcs:ignore
			}
			foreach ( $xml->Cube->Cube->Cube as $currency_rate ) { //phpcs:ignore
				$currency_rate = $currency_rate->attributes();
				if ( ! isset( $EUR_currency_from_rate ) && $currency_from == $currency_rate->currency ) { //phpcs:ignore
					$EUR_currency_from_rate = (float) $currency_rate->rate; //phpcs:ignore
				}
				if ( ! isset( $EUR_currency_to_rate ) && $currency_to == $currency_rate->currency ) { //phpcs:ignore
					$EUR_currency_to_rate = (float) $currency_rate->rate; //phpcs:ignore
				}
			}
			if ( isset( $EUR_currency_from_rate ) && isset( $EUR_currency_to_rate ) && 0 != $EUR_currency_from_rate ) { //phpcs:ignore
				$final_rate = round( $EUR_currency_to_rate / $EUR_currency_from_rate, 6 ); //phpcs:ignore
			} else {
				$final_rate = false;
			}
		}
		return $final_rate;
	}
}

class_alias( Exchange_Rate_Functions::class, 'Exchange_Rate_Functions' );
