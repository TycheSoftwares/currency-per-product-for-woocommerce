<?php
/**
 * Currency per Product for WooCommerce - Functions - Exchange Rates
 *
 * @version 1.4.1
 * @since   1.0.0
 * @author  Tyche Softwares
 *
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'alg_wc_cpp_get_currency_exchange_rate' ) ) {
	/**
	 * Get currency exchange rate by currency code.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @param string $currency_code Currency code for which to get exchange rate.
	 */
	function alg_wc_cpp_get_currency_exchange_rate( $currency_code ) {
		$base_currency = get_option( 'woocommerce_currency' );
		$total_number  = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( get_option( 'alg_wc_cpp_currency_' . $i, $base_currency ) === $currency_code ) {
				$exchange_rate = get_option( 'alg_wc_cpp_exchange_rate_' . $i, 1 );
				if ( is_numeric( $exchange_rate ) && 0 !== $exchange_rate ) {
					return ( 1 / $exchange_rate );
				}
			}
		}
		return 1;
	}
}

if ( ! function_exists( 'alg_wc_cpp_update_exchange_rates' ) ) {
	/**
	 * Update exchange rates.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function alg_wc_cpp_update_exchange_rates() {
		$currency_from = get_woocommerce_currency();
		$total_number  = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			$currency_to = get_option( 'alg_wc_cpp_currency_' . $i, $currency_from );
			update_option( 'alg_wc_cpp_exchange_rate_' . $i, alg_wc_cpp_get_exchange_rate( $currency_from, $currency_to ) );
		}
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_currency_exchange_rate_servers' ) ) {
	/**
	 * Get servers for fetching currency exchange rates.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_currency_exchange_rate_servers() {
		return array(
			'ecb'                  => __( 'European Central Bank (ECB)', 'currency-per-product-for-woocommerce' ),
			'tcmb'                 => __( 'TCMB', 'currency-per-product-for-woocommerce' ),
			'currencyconverterapi' => __( 'Free Currency Converter API (free.currencyconverterapi.com)', 'currency-per-product-for-woocommerce' ),
		);
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_currency_exchange_rate_server_name' ) ) {
	/**
	 * Get exchange rate server by optional server id or fetch all exchange rate servers.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @param string $server_id Server Id.
	 */
	function alg_wc_cpp_get_currency_exchange_rate_server_name( $server_id = '' ) {
		if ( '' === $server_id ) {
			$server_id = get_option( 'alg_wc_cpp_currency_exchange_rates_server', 'ecb' );
		}
		$servers = alg_wc_cpp_get_currency_exchange_rate_servers();
		return ( isset( $servers[ $server_id ] ) ? $servers[ $server_id ] : $servers['ecb'] );
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_exchange_rate' ) ) {
	/**
	 * Get exchange rate.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @param string $currency_from Currency to convert from.
	 * @param string $currency_to   Currency to convert to.
	 */
	function alg_wc_cpp_get_exchange_rate( $currency_from, $currency_to ) {
		$calculate_by_invert = get_option( 'alg_wc_cpp_currency_exchange_rates_calculate_by_invert', 'no' );
		if ( 'yes' === $calculate_by_invert ) {
			$_currency_to  = $currency_to;
			$currency_to   = $currency_from;
			$currency_from = $_currency_to;
		}
		$exchange_rates_server = get_option( 'alg_wc_cpp_currency_exchange_rates_server', 'ecb' );
		switch ( $exchange_rates_server ) {
			case 'currencyconverterapi':
				$return = alg_wc_cpp_currencyconverterapi_get_exchange_rate( $currency_from, $currency_to );
				break;
			case 'tcmb':
				$return = alg_wc_cpp_tcmb_get_exchange_rate( $currency_from, $currency_to );
				break;
			default: // 'ecb':
				$return = alg_wc_cpp_ecb_get_exchange_rate( $currency_from, $currency_to );
				break;
		}
		return ( 'yes' === $calculate_by_invert && 0 !== $return ? round( ( 1 / $return ), 6 ) : $return );
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_url_response' ) ) {
	/**
	 * Get URL Response when converting currencies.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 * @todo    [dev] maybe use `download_url()` function
	 *
	 * @param string $url URL.
	 */
	function alg_wc_cpp_get_url_response( $url ) {
		$response = false;
		if ( function_exists( 'curl_version' ) ) {
			$curl = curl_init( $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $curl );
			curl_close( $curl );
		} elseif ( ini_get( 'allow_url_fopen' ) ) {
			$response = file_get_contents( $url );
		}
		return $response;
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_url_response_xml' ) ) {
	/**
	 * Get XML response when converting currencies.
	 *
	 * @version 1.4.1
	 * @since   1.4.1
	 * @todo    [dev] maybe try CURL first (as in `alg_wc_cpp_get_url_response()`)?
	 *
	 * @param string $url URL.
	 */
	function alg_wc_cpp_get_url_response_xml( $url ) {
		$response = false;
		if ( ini_get( 'allow_url_fopen' ) && function_exists( 'simplexml_load_file' ) ) {
			$response = simplexml_load_file( $url );
		} elseif ( function_exists( 'curl_version' ) && class_exists( 'SimpleXMLElement' ) ) {
			$curl = curl_init( $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $curl );
			curl_close( $curl );
			libxml_use_internal_errors( true );
			try {
				$response = new SimpleXMLElement( $response );
			} catch ( Exception $e ) {
				return false;
			}
		}
		return $response;
	}
}

if ( ! function_exists( 'alg_wc_cpp_currencyconverterapi_get_exchange_rate' ) ) {
	/**
	 * Get exchange rate between 2 currencies.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @param string $currency_from Currency to convert from.
	 * @param string $currency_to   Currency to convert to.
	 */
	function alg_wc_cpp_currencyconverterapi_get_exchange_rate( $currency_from, $currency_to ) {
		$pair     = $currency_from . '_' . $currency_to;
		$url      = 'https://free.currencyconverterapi.com/api/v5/convert?q=' . $pair . '&compact=y';
		$response = alg_wc_cpp_get_url_response( $url );
		if ( $response ) {
			$response = json_decode( $response );
			return ( ! empty( $response->{$pair}->val ) ? $response->{$pair}->val : false );
		}
		return false;
	}
}

if ( ! function_exists( 'alg_wc_cpp_tcmb_get_exchange_rate_TRY' ) ) {
	/**
	 * Get various exchange rate for TRY (Turkish) currency.
	 *
	 * @version 1.4.1
	 * @since   1.0.0
	 *
	 * @param string $currency_from Currency from.
	 */
	// phpcs:ignore
	function alg_wc_cpp_tcmb_get_exchange_rate_TRY( $currency_from ) {
		if ( 'TRY' === $currency_from ) {
			return 1;
		}
		$xml = alg_wc_cpp_get_url_response_xml( 'http://www.tcmb.gov.tr/kurlar/today.xml' );
		// phpcs:disable
		// disabling phpcs as the variable names are coming from response from remote server
		if ( isset( $xml->Currency ) ) {
			foreach ( $xml->Currency as $the_rate ) {
				$attributes = $the_rate->attributes();
				if ( isset( $attributes['CurrencyCode'] ) ) {
					$currency_code = (string) $attributes['CurrencyCode'];
					if ( $currency_code === $currency_from ) {
						// Possible values: ForexSelling, ForexBuying, BanknoteSelling, BanknoteBuying. Not used: CrossRateUSD, CrossRateOther.
						$property_to_check = apply_filters( 'alg_wc_cpp_currency_exchange_rates_tcmb_property_to_check', '' );
						if ( '' !== $property_to_check ) {
							if ( isset( $the_rate->{$property_to_check} ) ) {
								$rate = (float) $the_rate->{$property_to_check};
							} else {
								continue;
							}
						} else {
							if ( isset( $the_rate->ForexSelling ) ) {
								$rate = (float) $the_rate->ForexSelling;
							} elseif ( isset( $the_rate->ForexBuying ) ) {
								$rate = (float) $the_rate->ForexBuying;
							} elseif ( isset( $the_rate->BanknoteSelling ) ) {
								$rate = (float) $the_rate->BanknoteSelling;
							} elseif ( isset( $the_rate->BanknoteBuying ) ) {
								$rate = (float) $the_rate->BanknoteBuying;
							} else {
								continue;
							}
						}
						$unit = ( isset( $the_rate->Unit ) ) ? (float) $the_rate->Unit : 1;
						return ( $rate / $unit );
					}
				}
			}
		}
		// phpcs:enable
		return false;
	}
}

if ( ! function_exists( 'alg_wc_cpp_tcmb_get_exchange_rate' ) ) {
	/**
	 * Get exchange rate.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @param string $currency_from Currency to convert from.
	 * @param string $currency_to   Currency to convert to.
	 */
	function alg_wc_cpp_tcmb_get_exchange_rate( $currency_from, $currency_to ) {
		// phpcs:disable
		// disabling phpcs as the functions & variable names have currency code in it, hence they are not in snake case
		$currency_from_TRY = alg_wc_cpp_tcmb_get_exchange_rate_TRY( strtoupper( $currency_from ) );
		if ( false === $currency_from_TRY ) {
			return false;
		}
		$currency_to_TRY = alg_wc_cpp_tcmb_get_exchange_rate_TRY( strtoupper( $currency_to ) );
		if ( false === $currency_to_TRY ) {
			return false;
		}
		if ( 1 === $currency_to_TRY ) {
			return round( $currency_from_TRY, 6 );
		}
		return round( ( $currency_from_TRY / $currency_to_TRY ), 6 );
		// phpcs:enable
	}
}

if ( ! function_exists( 'alg_wc_cpp_ecb_get_exchange_rate' ) ) {
	/**
	 * Get exchange rates from European Central bank.
	 *
	 * @version 1.4.1
	 * @since   1.0.0
	 * @param string $currency_from Currency to convert from.
	 * @param string $currency_to   Currency to convert to.
	 */
	function alg_wc_cpp_ecb_get_exchange_rate( $currency_from, $currency_to ) {
		$final_rate = false;
		$xml        = alg_wc_cpp_get_url_response_xml( 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml' );
		// phpcs:disable
		// disabling phpcs as the functions & variable names have currency code in it, hence they are not in snake case
		if ( isset( $xml->Cube->Cube->Cube ) ) {
			if ( 'EUR' === $currency_from ) {
				$EUR_currency_from_rate = 1;
			}
			if ( 'EUR' === $currency_to ) {
				$EUR_currency_to_rate = 1;
			}
			foreach ( $xml->Cube->Cube->Cube as $currency_rate ) {
				$currency_rate = $currency_rate->attributes();
				if ( ! isset( $EUR_currency_from_rate ) && $currency_from == $currency_rate->currency ) {
					$EUR_currency_from_rate = (float) $currency_rate->rate;
				}
				if ( ! isset( $EUR_currency_to_rate ) && $currency_to == $currency_rate->currency ) {
					$EUR_currency_to_rate = (float) $currency_rate->rate;
				}
			}
			if ( isset( $EUR_currency_from_rate ) && isset( $EUR_currency_to_rate ) && 0 != $EUR_currency_from_rate ) {
				$final_rate = round( $EUR_currency_to_rate / $EUR_currency_from_rate, 6 );
			} else {
				$final_rate = false;
			}
		}
		// phpcs:enable
		return $final_rate;
	}
}
