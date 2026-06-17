/**
 * Exchange Rates settings API.
 *
 * Endpoint: /wp-json/cpp/v1/settings/exchange-rates
 *
 * Shape:
 * {
 *   exchange_rate_update:                             string,
 *   exchange_rate_update_rate:                        string,
 *   currency_exchange_rates_server:                   string,
 *   free_currency_converter_api_key:                  string,
 *   coinmarketcap_api_key:                            string,
 *   exchange_fees_types:                              string,
 *   apply_discount_automatic_additional_exchange_fee: number,
 *   round_exchange_enabled:                           boolean,
 *   rates: {
 *     [index]: {
 *       rate:      number,
 *       is_manual: boolean,
 *     }
 *   }
 * }
 */

import apiFetch from '@wordpress/api-fetch';
import { get, post } from './client';

const PATH = 'exchange-rates';

/**
 * Fetch exchange rates settings.
 *
 * @return {Promise<Object>} Exchange rates settings object.
 */
export const fetchExchangeRatesSettings = () => get( PATH );

/**
 * Save exchange rates settings.
 *
 * @param {Object}  settings
 * @param {string}  settings.exchange_rate_update
 * @param {string}  settings.exchange_rate_update_rate
 * @param {string}  settings.currency_exchange_rates_server
 * @param {string}  settings.free_currency_converter_api_key
 * @param {string}  settings.coinmarketcap_api_key
 * @param {string}  settings.exchange_fees_types
 * @param {number}  settings.apply_discount_automatic_additional_exchange_fee
 * @param {boolean} settings.round_exchange_enabled
 * @param {Object}  settings.rates
 * @return {Promise<Object>} Saved settings object.
 */
export const saveExchangeRatesSettings = ( settings ) => post( PATH, settings );

/**
 * Trigger an immediate exchange-rate update on the server.
 *
 * @return {Promise<Object>} Updated settings including next_scheduled timestamp.
 */
export const updateExchangeRatesNow = () =>
	apiFetch( {
		path: '/cpp/v1/settings/exchange-rates/update-now',
		method: 'POST',
	} );
