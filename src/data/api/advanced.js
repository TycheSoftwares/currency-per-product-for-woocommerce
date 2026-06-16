/**
 * Advanced settings API.
 *
 * Endpoint: /wp-json/cpp/v1/settings/advanced
 *
 * Shape:
 * {
 *   fix_mini_cart:             boolean,
 *   sort_by_converted_price:   boolean,
 *   filter_by_converted_price: boolean,
 *   save_products_prices:      boolean,
 * }
 */

import apiFetch from '@wordpress/api-fetch';
import { get, post } from './client';

const PATH = 'advanced';

/**
 * Fetch advanced settings.
 *
 * @return {Promise<Object>} Advanced settings object.
 */
export const fetchAdvancedSettings = () => get( PATH );

/**
 * Save advanced settings.
 *
 * @param {Object}  settings
 * @param {boolean} settings.fix_mini_cart
 * @param {boolean} settings.sort_by_converted_price
 * @param {boolean} settings.filter_by_converted_price
 * @param {boolean} settings.save_products_prices
 * @return {Promise<Object>} Saved settings object.
 */
export const saveAdvancedSettings = ( settings ) => post( PATH, settings );

/**
 * Re-calculate and save all product prices.
 *
 * @return {Promise<Object>} Result object.
 */
export const recalculatePrices = () =>
	apiFetch( {
		path: '/cpp/v1/tools/recalculate-prices',
		method: 'POST',
	} );

/**
 * Delete all plugin data, options and product meta.
 *
 * @return {Promise<Object>} Result object.
 */
export const deletePluginData = () =>
	apiFetch( {
		path: '/cpp/v1/tools/delete-plugin-data',
		method: 'POST',
	} );
