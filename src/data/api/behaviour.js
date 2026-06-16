/**
 * Behaviour settings API.
 *
 * Endpoint: /wp-json/cpp/v1/settings/behaviour
 *
 * Shape:
 * {
 *   shop_behaviour:                            string,
 *   original_price_in_shop_enabled:            boolean,
 *   original_price_in_shop_template:           string,
 *   cart_checkout:                             string,
 *   original_price_in_cart_checkout_enabled:   boolean,
 *   original_price_in_cart_checkout_template:  string,
 *   cart_checkout_leave_one_product:           string,
 *   cart_checkout_leave_same_currency:         string,
 *   currency_by_location:                      boolean,
 * }
 */

import { get, post } from './client';

const PATH = 'behaviour';

/**
 * Fetch behaviour settings.
 *
 * @return {Promise<Object>} Behaviour settings object.
 */
export const fetchBehaviourSettings = () => get( PATH );

/**
 * Save behaviour settings.
 *
 * @param {Object}  settings
 * @param {string}  settings.shop_behaviour
 * @param {boolean} settings.original_price_in_shop_enabled
 * @param {string}  settings.original_price_in_shop_template
 * @param {string}  settings.cart_checkout
 * @param {boolean} settings.original_price_in_cart_checkout_enabled
 * @param {string}  settings.original_price_in_cart_checkout_template
 * @param {string}  settings.cart_checkout_leave_one_product
 * @param {string}  settings.cart_checkout_leave_same_currency
 * @param {boolean} settings.currency_by_location
 * @return {Promise<Object>} Saved settings object.
 */
export const saveBehaviourSettings = ( settings ) => post( PATH, settings );
