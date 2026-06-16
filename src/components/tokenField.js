/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';
import { useDebounce } from '@wordpress/compose';
import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies.
 */
import AsyncSelect from 'react-select/async';

/**
 * Async token field that searches CPP store endpoints.
 *
 * Props:
 *   type     {string}   'categories' | 'tags'
 *   value    {Array}    Array of {value, label} objects (react-select format)
 *   onChange {Function} Called with the new array of {value, label} objects
 *   placeholder {string}
 */
function TokenField( { type, value, onChange, placeholder } ) {

	const loadOptions = useCallback(
		useDebounce( ( inputValue, callback ) => {
			apiFetch( { path: `/cpp/v1/store/${ type }?search=${ encodeURIComponent( inputValue ) }` } )
				.then( ( data ) => {
					callback( data.map( ( item ) => ( { value: item.id, label: item.title } ) ) );
				} )
				.catch( () => callback( [] ) );
		}, 300 ),
		[ type ]
	);

	return (
		<AsyncSelect
			isMulti
			value={ value }
			onChange={ onChange }
			loadOptions={ loadOptions }
			placeholder={ placeholder ?? __( 'Search…', 'currency-per-product-for-woocommerce' ) }
			noOptionsMessage={ ( { inputValue } ) =>
				inputValue.length < 2
					? __( 'Please enter at least 2 characters', 'currency-per-product-for-woocommerce' )
					: __( 'No results found', 'currency-per-product-for-woocommerce' )
			}
			classNamePrefix="cpp-token-field"
			styles={ {
				control: ( base ) => ( { ...base, minHeight: '36px', borderColor: '#949494', borderRadius: '2px', boxShadow: 'none' } ),
				menu:    ( base ) => ( { ...base, zIndex: 9999 } ),
			} }
		/>
	);
}

export default TokenField;
