/**
 * General settings API.
 *
 * Endpoint: /wp-json/cpp/v1/settings/general
 *
 * Shape:
 * {
 *   enabled:                         boolean,
 *   currency_reports_enabled:        boolean,
 *   custom_currency_symbol_enabled:  boolean,
 *   custom_currency_symbol_template: string,
 *   round_off_decimal_points:        boolean,
 * }
 */

import { get, post } from './client';

const PATH = 'general';

/**
 * Fetch general settings.
 *
 * @return {Promise<Object>} General settings object.
 */
export const fetchGeneralSettings = () => get( PATH );

/**
 * Save general settings.
 *
 * @param {Object}  settings
 * @param {boolean} settings.enabled
 * @param {boolean} settings.currency_reports_enabled
 * @param {boolean} settings.custom_currency_symbol_enabled
 * @param {string}  settings.custom_currency_symbol_template
 * @param {boolean} settings.round_off_decimal_points
 * @return {Promise<Object>} Saved settings object.
 */
export const saveGeneralSettings = ( settings ) => post( PATH, settings );
