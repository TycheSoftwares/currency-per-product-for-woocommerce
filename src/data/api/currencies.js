/**
 * Currencies settings API.
 *
 * Endpoint: /wp-json/cpp/v1/settings/currencies
 *
 * Shape:
 * {
 *   total_number:            number,
 *   by_users_enabled:        boolean,
 *   by_user_roles_enabled:   boolean,
 *   by_product_cats_enabled: boolean,
 *   by_product_tags_enabled: boolean,
 *   by_product_pg_enabled:   boolean,
 *   currencies: {
 *     [index]: {
 *       currency:     string,
 *       users:        number[],
 *       user_roles:   string[],
 *       product_cats: number[],
 *       product_tags: number[],
 *       product_pg:   string[],
 *     }
 *   }
 * }
 */

import { get, post } from './client';

const PATH = 'currencies';

/**
 * Fetch currencies settings.
 *
 * @return {Promise<Object>} Currencies settings object.
 */
export const fetchCurrenciesSettings = () => get( PATH );

/**
 * Save currencies settings.
 *
 * @param {Object}  settings
 * @param {number}  settings.total_number
 * @param {boolean} settings.by_users_enabled
 * @param {boolean} settings.by_user_roles_enabled
 * @param {boolean} settings.by_product_cats_enabled
 * @param {boolean} settings.by_product_tags_enabled
 * @param {boolean} settings.by_product_pg_enabled
 * @param {Object}  settings.currencies
 * @return {Promise<Object>} Saved settings object.
 */
export const saveCurrenciesSettings = ( settings ) => post( PATH, settings );
