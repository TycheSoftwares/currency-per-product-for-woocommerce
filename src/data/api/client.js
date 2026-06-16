/**
 * Shared API client.
 *
 * Wraps @wordpress/api-fetch with the plugin's base path so individual
 * modules only need to supply the path suffix and options.
 */

import apiFetch from '@wordpress/api-fetch';

const BASE = '/cpp/v1/settings/';

/**
 * GET request.
 *
 * @param {string} path Path suffix (e.g. 'general').
 * @return {Promise<*>} Parsed response data.
 */
export const get = ( path ) =>
	apiFetch( { path: BASE + path, method: 'GET' } );

/**
 * POST request.
 *
 * @param {string} path Path suffix (e.g. 'general').
 * @param {Object} data Request body.
 * @return {Promise<*>} Parsed response data.
 */
export const post = ( path, data ) =>
	apiFetch( {
		path: BASE + path,
		method: 'POST',
		data,
	} );
