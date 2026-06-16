/**
 * WordPress dependencies.
 */
import {
	Card,
	CardHeader,
	CardBody,
	CheckboxControl,
	SelectControl,
	FormTokenField,
	Button,
	Icon,
	ExternalLink,
	__experimentalVStack as VStack,
	__experimentalHStack as HStack,
	__experimentalHeading as Heading,
	__experimentalText as Text,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useForm, useWatch, Controller } from 'react-hook-form';
import { trash, lock, info } from '@wordpress/icons';

/**
 * Internal dependencies.
 */
import { saveCurrenciesSettings } from '../../data/api';
import { FormActions, TokenField } from '../../components';

const UPGRADE_URL = 'https://www.tychesoftwares.com/products/woocommerce-currency-per-product-plugin/?utm_source=cppupgradetopro&utm_medium=unlockall&utm_campaign=CurrencePerProductLite';

const toBoolean = ( v ) => v === 'yes' || v === true;

const DEFAULTS = {
	total_number:            1,
	by_users_enabled:        false,
	by_user_roles_enabled:   false,
	by_product_cats_enabled: false,
	by_product_tags_enabled: false,
	by_product_pg_enabled:   false,
	currencies:              { 0: { currency: '', product_pg: [] } },
};

const MODULE_OPTIONS = [
	{
		name:        'by_product_cats_enabled',
		title:       __( 'Currency by Product Category', 'currency-per-product-for-woocommerce' ),
		description: __( "Apply a specific currency based on the product's category", 'currency-per-product-for-woocommerce' ),
	},
	{
		name:        'by_product_tags_enabled',
		title:       __( 'Currency by Product Tag', 'currency-per-product-for-woocommerce' ),
		description: __( 'Assign currency based on product tags', 'currency-per-product-for-woocommerce' ),
	},
	{
		name:        'by_users_enabled',
		title:       __( 'Currency by Product Author', 'currency-per-product-for-woocommerce' ),
		description: __( 'Assign a default currency to each product author', 'currency-per-product-for-woocommerce' ),
	},
	{
		name:        'by_user_roles_enabled',
		title:       __( 'Currency by Author\'s User Role', 'currency-per-product-for-woocommerce' ),
		description: __( 'Assigns currency based on the WordPress user role of the product\'s author', 'currency-per-product-for-woocommerce' ),
	},
	{
		name:        'by_product_pg_enabled',
		title:       __( 'Currency by Payment Gateway', 'currency-per-product-for-woocommerce' ),
		description: __( 'Use a specific currency for each payment gateway', 'currency-per-product-for-woocommerce' ),
		disabled:    true,
	},
];

function CurrenciesTab( { settings, onSaved } ) {
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice,   setNotice   ] = useState( null );

	const showNotice = ( message, status = 'success' ) => {
		setNotice( { message, status } );
		if ( status === 'success' ) {
			setTimeout( () => setNotice( null ), 3000 );
		}
	};

	const wcCurrencies      = window.cppAdminData?.currencies       ?? {};
	const paymentGateways   = window.cppAdminData?.payment_gateways ?? [];
	const userRoles         = window.cppAdminData?.user_roles        ?? [];
	const users             = window.cppAdminData?.users             ?? [];
	const baseCurrencyCode  = window.cppAdminData?.shopCurrency      ?? '';
	const baseCurrencyLabel = baseCurrencyCode && wcCurrencies[ baseCurrencyCode ]
		? `${ wcCurrencies[ baseCurrencyCode ] } (${ baseCurrencyCode })`
		: baseCurrencyCode;

	const { control, handleSubmit, reset, setValue } = useForm( {
		defaultValues: settings ?? DEFAULTS,
	} );

	useEffect( () => { if ( settings ) reset( { ...DEFAULTS, ...settings } ); }, [ settings, reset ] );

	const moduleValues = useWatch( {
		control,
		name: MODULE_OPTIONS.map( ( m ) => m.name ),
	} );
	const activeCount = moduleValues.filter( toBoolean ).length;

	const pgEnabled      = useWatch( { control, name: 'by_product_pg_enabled' } );
	const rolesEnabled   = useWatch( { control, name: 'by_user_roles_enabled' } );
	const usersEnabled   = useWatch( { control, name: 'by_users_enabled' } );
	const catsEnabled    = useWatch( { control, name: 'by_product_cats_enabled' } );
	const tagsEnabled    = useWatch( { control, name: 'by_product_tags_enabled' } );
	const currencies = useWatch( { control, name: 'currencies' } ) ?? {};

	const currencyIndices = Object.keys( currencies ).map( Number ).sort( ( a, b ) => a - b );

	const currencyOptions = [
		{ label: __( 'Select currency…', 'currency-per-product-for-woocommerce' ), value: '' },
		...Object.entries( wcCurrencies ).map( ( [ code, name ] ) => ( {
			label: `${ name } (${ code })`,
			value: code,
		} ) ),
	];

	const pgOptionLabels = paymentGateways.map( ( gw ) => gw.title ?? gw.label ?? gw.id );

	const addCurrency = () => {
		const nextIndex = currencyIndices.length;
		setValue( `currencies.${ nextIndex }`, { currency: '', product_cats: [], product_tags: [], user_roles: [], product_pg: [] } );
		setValue( 'total_number', nextIndex + 1 );
	};

	const removeCurrency = ( removeIdx ) => {
		const newCurrencies = {};
		let newIdx = 0;
		currencyIndices.forEach( ( idx ) => {
			if ( idx !== removeIdx ) {
				newCurrencies[ newIdx++ ] = currencies[ idx ];
			}
		} );
		setValue( 'currencies', newCurrencies );
		setValue( 'total_number', Object.keys( newCurrencies ).length );
	};

	const resetSettings = async () => {
		setIsSaving( true );
		try {
			await saveCurrenciesSettings( DEFAULTS );
			const latest = await onSaved?.();
			reset( latest ?? DEFAULTS );
			showNotice( __( 'Settings have been successfully reset to default values.', 'currency-per-product-for-woocommerce' ) );
		} catch {
			showNotice( __( 'Error resetting settings.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	const onSubmit = async ( data ) => {
		setIsSaving( true );
		try {
			await saveCurrenciesSettings( data );
			showNotice( __( 'Settings saved.', 'currency-per-product-for-woocommerce' ) );
			onSaved?.();
		} catch {
			showNotice( __( 'Error saving settings.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	return (
		<form onSubmit={ handleSubmit( onSubmit ) }>
			<VStack className={'cpp_setting_section'} spacing={ 6 }>

				{/* ── Additional Options ─────────────────────────────────── */}
				<Card>
					<CardHeader>
						<div>
							<Heading level={ 4 } className="cpp-license-heading">
								{ __( 'Currency Mapping Rules', 'currency-per-product-for-woocommerce' ) }
							</Heading>
							<Text className="cpp-license-subtext">
								{ __( 'Enable optional modules below. Each active module adds a configuration field to every currency, letting you map categories, tags, authors, roles, or payment gateways per currency.', 'currency-per-product-for-woocommerce' ) }
							</Text>
						</div>
					</CardHeader>
					<CardBody style={ { padding: 0 } }>
						{ MODULE_OPTIONS.map( ( module ) => (
							<Controller
								key={ module.name }
								name={ module.name }
								control={ control }
								render={ ( { field } ) => {
									const isActive = toBoolean( field.value );
									return (
										<div className={ `cpp-module-option${ isActive ? ' is-active' : '' }` }>
											<CheckboxControl
												checked={ isActive }
												onChange={ module.disabled ? () => {} : field.onChange }
												disabled={ module.disabled }
												__nextHasNoMarginBottom
											/>
											<div className="cpp-module-info">
												<Text className="cpp-module-title">{ module.title }</Text>
												<Text className="cpp-module-desc">{ module.description }</Text>
											</div>
											{ module.disabled ? (
											<ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">
												{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }
											</ExternalLink>
										) : isActive && (
											<span className="cpp-module-badge">
												{ __( 'Active', 'currency-per-product-for-woocommerce' ) }
											</span>
										) }
										</div>
									);
								} }
							/>
						) ) }

						{ activeCount > 0 && (
							<div className="cpp-module-notice">
								<Icon icon={ info } size={ 16 } />
								<Text>
									{ activeCount === 1
										? __( '1 module active — configure each currency\'s settings in the Currencies section below.', 'currency-per-product-for-woocommerce' )
										: `${ activeCount } ${ __( 'modules active — configure each currency\'s settings in the Currencies section below.', 'currency-per-product-for-woocommerce' ) }`
									}
								</Text>
							</div>
						) }
					</CardBody>
				</Card>

				{/* ── Currencies ─────────────────────────────────────────── */}
				<Card>
					<CardHeader>
						<Heading level={ 4 } className="cpp-license-heading">
								{ __( 'Currencies', 'currency-per-product-for-woocommerce' ) }
						</Heading>	
					</CardHeader>
					<CardBody className="cpp-currencies-card-body">
							<Text className="cpp-license-subtext">
									{ __( `Your shop base currency ${baseCurrencyCode} will be automatically added to the currencies list on edit product page. If you are missing some currencies (or cryptocurrencies) in the currencies list below – we suggest checking free `, 'currency-per-product-for-woocommerce' ) }
									<a href="https://wordpress.org/plugins/woocommerce-all-currencies/" target="_blank" rel="noopener noreferrer">
										{ __( 'All Currencies for WooCommerce plugin', 'currency-per-product-for-woocommerce' ) }
									</a>
							</Text>
						{/* ── Base currency (always shown, locked) ── */}
						<div className="cpp-currency-block">
							
							<div className="cpp-currency-row">
								<div className="cpp-currency-row__label">
									<Text className="cpp-currency-label">
										{ __( 'Base Currency', 'currency-per-product-for-woocommerce' ) }
									</Text>
									<span className="cpp-currency-badge--base">
										{ __( 'base', 'currency-per-product-for-woocommerce' ) }
									</span>
								</div>
								<div className="cpp-currency-row__control">
									<div className="cpp-currency-select-wrap has-lock">
										<SelectControl
											value={ baseCurrencyCode }
											options={ baseCurrencyCode
												? [ { label: baseCurrencyLabel, value: baseCurrencyCode } ]
												: [ { label: __( '— shop currency not configured —', 'currency-per-product-for-woocommerce' ), value: '' } ]
											}
											onChange={ () => {} }
											disabled
											__nextHasNoMarginBottom
										/>
										<span className="cpp-currency-select-lock">
											<Icon icon={ lock } size={ 14 } />
										</span>
									</div>
								</div>
								<div className="cpp-currency-row__action" />
							</div>
						</div>

						{/* ── Additional currencies ── */}
						{ currencyIndices.map( ( idx ) => {
							const currencyVal = currencies[ idx ]?.currency ?? '';
							const pgVal       = currencies[ idx ]?.product_pg ?? [];
							const pgLabels    = paymentGateways
								.filter( ( gw ) => pgVal.includes( gw.id ) )
								.map( ( gw ) => gw.title ?? gw.label ?? gw.id );

							return (
								<div key={ idx } className="cpp-currency-block">
									<div className="cpp-currency-row">
										<div className="cpp-currency-row__label">
											<Text className="cpp-currency-label">
												{ `Currency #${ idx + 1 }` }
											</Text>
										</div>
										<div className="cpp-currency-row__control">
											<div className="cpp-currency-select-wrap">
												<SelectControl
													value={ currencyVal }
													options={ currencyOptions }
													onChange={ ( val ) => setValue( `currencies.${ idx }.currency`, val ) }
													__nextHasNoMarginBottom
												/>
											</div>
										</div>
										<div className="cpp-currency-row__action">
											<Button
												variant="tertiary"
												isDestructive
												icon={ trash }
												iconSize={ 16 }
												onClick={ () => removeCurrency( idx ) }
												label={ __( 'Remove currency', 'currency-per-product-for-woocommerce' ) }
											/>
										</div>
									</div>

									{ toBoolean( catsEnabled ) && (
										<div className="cpp-currency-row cpp-currency-row--sub">
											<div className="cpp-currency-row__label">
												<Text className="cpp-currency-label cpp-currency-label--light">
													{ __( 'Categories', 'currency-per-product-for-woocommerce' ) }
												</Text>
											</div>
											<div className="cpp-currency-row__control">
												<TokenField
													type="categories"
													value={ currencies[ idx ]?.product_cats ?? [] }
													onChange={ ( selected ) => setValue( `currencies.${ idx }.product_cats`, selected ?? [] ) }
													placeholder={ __( 'Search categories…', 'currency-per-product-for-woocommerce' ) }
												/>
											</div>
											<div className="cpp-currency-row__action" />
										</div>
									) }

									{ toBoolean( tagsEnabled ) && (
										<div className="cpp-currency-row cpp-currency-row--sub">
											<div className="cpp-currency-row__label">
												<Text className="cpp-currency-label cpp-currency-label--light">
													{ __( 'Tags', 'currency-per-product-for-woocommerce' ) }
												</Text>
											</div>
											<div className="cpp-currency-row__control">
												<TokenField
													type="tags"
													value={ currencies[ idx ]?.product_tags ?? [] }
													onChange={ ( selected ) => setValue( `currencies.${ idx }.product_tags`, selected ?? [] ) }
													placeholder={ __( 'Search tags…', 'currency-per-product-for-woocommerce' ) }
												/>
											</div>
											<div className="cpp-currency-row__action" />
										</div>
									) }

									{ toBoolean( usersEnabled ) && (
										<div className="cpp-currency-row cpp-currency-row--sub">
											<div className="cpp-currency-row__label">
												<Text className="cpp-currency-label cpp-currency-label--light">
													{ __( 'Authors', 'currency-per-product-for-woocommerce' ) }
												</Text>
											</div>
											<div className="cpp-currency-row__control">
												<FormTokenField
													label={''}
													help={''}
													value={ ( currencies[ idx ]?.users ?? [] ).map(
														( id ) => users.find( ( u ) => Number( u.id ) === Number( id ) )?.name ?? String( id )
													) }
													suggestions={ users.map( ( u ) => u.name ) }
													placeholder={ __( 'Select authors…', 'currency-per-product-for-woocommerce' ) }
													onChange={ ( tokens ) => {
														const ids = tokens.map( ( t ) => {
															const user = users.find( ( u ) => u.name === t );
															return user?.id ?? t;
														} );
														setValue( `currencies.${ idx }.users`, ids );
													} }
													__experimentalExpandOnFocus
													__nextHasNoMarginBottom
												/>
											</div>
											<div className="cpp-currency-row__action" />
										</div>
									) }

									{ toBoolean( rolesEnabled ) && (
										<div className="cpp-currency-row cpp-currency-row--sub">
											<div className="cpp-currency-row__label">
												<Text className="cpp-currency-label cpp-currency-label--light">
													{ __( 'User Roles', 'currency-per-product-for-woocommerce' ) }
												</Text>
											</div>
											<div className="cpp-currency-row__control">
												<FormTokenField
													label={''}
													help={''}
													value={ ( currencies[ idx ]?.user_roles ?? [] ).map(
														( slug ) => userRoles.find( ( r ) => r.id === slug )?.name ?? slug
													) }
													suggestions={ userRoles.map( ( r ) => r.name ) }
													placeholder={ __( 'Select roles…', 'currency-per-product-for-woocommerce' ) }
													onChange={ ( tokens ) => {
														const slugs = tokens.map( ( t ) => {
															const role = userRoles.find( ( r ) => r.name === t );
															return role?.id ?? t;
														} );
														setValue( `currencies.${ idx }.user_roles`, slugs );
													} }
													__experimentalExpandOnFocus
													__nextHasNoMarginBottom
												/>
											</div>
											<div className="cpp-currency-row__action" />
										</div>
									) }

									{ toBoolean( pgEnabled ) && (
										<div className="cpp-currency-row cpp-currency-row--sub">
											<div className="cpp-currency-row__label">
												<Text className="cpp-currency-label cpp-currency-label--light">
													{ __( 'Payment Gateways', 'currency-per-product-for-woocommerce' ) }
												</Text>
											</div>
											<div className="cpp-currency-row__control">
												<FormTokenField
													label={''}
													help={''}
													value={ pgLabels }
													suggestions={ pgOptionLabels }
													placeholder={ __( 'Select gateways…', 'currency-per-product-for-woocommerce' ) }
													onChange={ ( tokens ) => {
														const ids = tokens.map( ( t ) => {
															const gw = paymentGateways.find( ( g ) => ( g.title ?? g.label ?? g.id ) === t );
															return gw?.id ?? t;
														} );
														setValue( `currencies.${ idx }.product_pg`, ids );
													} }
													__experimentalExpandOnFocus
													__nextHasNoMarginBottom
												/>
											</div>
											<div className="cpp-currency-row__action" />
										</div>
									) }
								</div>
							);
						} ) }

						<div className="cpp-add-currency-wrap">
							<HStack justify='start'>
								<button
									type="button"
									className="cpp-add-currency"
									onClick={ addCurrency }
									disabled={ currencyIndices.length >= 1 }
								>
									+ { __( 'Add Currency', 'currency-per-product-for-woocommerce' ) }
								</button>
								{ currencyIndices.length >= 1 && (
									<ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink>
								) }
							</HStack>
						</div>
					</CardBody>
				</Card>

				{/* ── Actions ────────────────────────────────────────────── */}
				<FormActions isSaving={ isSaving } notice={ notice } onNoticeRemove={ () => setNotice( null ) } onReset={ resetSettings } />

			</VStack>
		</form>
	);
}

export default CurrenciesTab;
