/**
 * WordPress dependencies.
 */
import {
	__experimentalVStack as VStack,
	__experimentalInputControl as InputControl,
	__experimentalNumberControl as NumberControl,
	__experimentalText as Text,
	__experimentalHeading as Heading,
	Button,
	Card,
	CardHeader,
	CardBody,
	Icon,
	CheckboxControl,
	SelectControl,
	ExternalLink,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { lock } from '@wordpress/icons';
import { useForm, Controller } from 'react-hook-form';

/**
 * Internal dependencies.
 */
import { SettingsCardSection, FormActions } from '../../components';
import { saveExchangeRatesSettings, updateExchangeRatesNow } from '../../data/api';

const UPGRADE_URL = 'https://www.tychesoftwares.com/products/woocommerce-currency-per-product-plugin/?utm_source=cppupgradetopro&utm_medium=unlockall&utm_campaign=CurrencePerProductLite';

const toBoolean = ( v ) => v === 'yes' || v === true;

const DEFAULTS = {
	exchange_rate_update:                             'manual',
	exchange_rate_update_rate:                        'daily',
	currency_exchange_rates_server:                   'ecb',
	free_currency_converter_api_key:                  '',
	coinmarketcap_api_key:                            '',
	exchange_fees_types:                              'markupflat',
	apply_discount_automatic_additional_exchange_fee: 0,
	round_exchange_enabled:                           false,
};

/**
 * Returns a human-readable "X minutes until next update" string,
 * or null if `nextScheduled` (Unix timestamp in seconds) is not available.
 */
function formatNextUpdate( nextScheduled ) {
	if ( ! nextScheduled ) return null;
	const diffMs   = nextScheduled * 1000 - Date.now();
	const diffMins = Math.round( diffMs / 60000 );
	if ( diffMins <= 0 ) return null;
	if ( diffMins === 1 ) return __( '1 minute until next update.', 'currency-per-product-for-woocommerce' );
	return sprintf(
		/* translators: %d: number of minutes */
		__( '%d minutes until next update.', 'currency-per-product-for-woocommerce' ),
		diffMins
	);
}

function ExchangeRatesTab( { settings, currenciesSettings, onSaved } ) {
	const [ isSaving, setIsSaving ]               = useState( false );
	const [ notice,   setNotice   ]               = useState( null );
	const [ isUpdatingRates, setIsUpdatingRates ] = useState( false );

	const showNotice = ( message, status = 'success' ) => {
		setNotice( { message, status } );
		if ( status === 'success' ) {
			setTimeout( () => setNotice( null ), 3000 );
		}
	};
	const [ nextScheduled, setNextScheduled ] = useState( settings?.next_scheduled ?? null );

	const { control, handleSubmit, reset, watch } = useForm( {
		defaultValues: settings ?? DEFAULTS,
	} );

	useEffect( () => {
		if ( settings ) {
			reset( { ...DEFAULTS, ...settings } );
			setNextScheduled( settings.next_scheduled ?? null );
		}
	}, [ settings, reset ] );

	const handleUpdateNow = async () => {
		setIsUpdatingRates( true );
		try {
			const updated = await updateExchangeRatesNow();
			setNextScheduled( updated?.next_scheduled ?? null );
			showNotice( __( 'Exchange rates updated.', 'currency-per-product-for-woocommerce' ) );
			onSaved?.();
		} catch {
			showNotice( __( 'Error updating exchange rates.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsUpdatingRates( false );
		}
	};

	const resetSettings = async () => {
		setIsSaving( true );
		try {
			await saveExchangeRatesSettings( DEFAULTS );
			const latest = await onSaved?.();
			reset( latest ?? DEFAULTS );
			setNextScheduled( null );
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
			await saveExchangeRatesSettings( data );
			showNotice( __( 'Settings saved.', 'currency-per-product-for-woocommerce' ) );
			onSaved?.();
		} catch {
			showNotice( __( 'Error saving settings.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	const isAuto = watch( 'exchange_rate_update' ) === 'auto';
	const server  = watch( 'currency_exchange_rates_server' );

	const shopCurrency  = window.cppAdminData?.shopCurrency || '';
	const allCurrencies = window.cppAdminData?.currencies || {};

	// Build list: base currency at index 0, then each configured currency in order.
	const configuredCurrencies = Object.values( currenciesSettings?.currencies ?? {} )
		.filter( ( c ) => c?.currency )
		.map( ( c ) => c.currency );

	const currencyList = [ shopCurrency, ...configuredCurrencies ];

	return (
		<form onSubmit={ handleSubmit( onSubmit ) }>
			<VStack className={'cpp_setting_section'} spacing={10}>
				<SettingsCardSection
					heading={ __( 'Exchange Rates Options', 'currency-per-product-for-woocommerce' ) }
					subHeading={ __( 'Configure how exchange rates are updated and calculated', 'currency-per-product-for-woocommerce' ) }
					control={ control }
					fields={ [
						{
							name: 'exchange_rate_update',
							defaultValue: 'manual',
							label: __( 'Rate Update Method', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<VStack spacing={ 2 }>
									<SelectControl
										label=""
										value={ field.value }
										options={ [
											{ label: __( 'Enter rates manually', 'currency-per-product-for-woocommerce' ), value: 'manual' },
											{ label: __( 'Update rates automatically (PRO) ', 'currency-per-product-for-woocommerce' ), value: 'auto', disabled: true },
										] }
										onChange={ field.onChange }
										__nextHasNoMarginBottom
									/>
								</VStack>
							),
						},
						{
							name: 'exchange_rate_update_rate',
							defaultValue: 'daily',
							label: __( 'Update Rate', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<SelectControl
									label=""
									value={ field.value }
									options={ [
										{ label: __( 'Update Hourly', 'currency-per-product-for-woocommerce' ), value: 'hourly' },
										{ label: __( 'Update Twice Daily', 'currency-per-product-for-woocommerce' ), value: 'twicedaily' },
										{ label: __( 'Update Daily', 'currency-per-product-for-woocommerce' ), value: 'daily' },
									] }
									onChange={ field.onChange }
									help={ <ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink> }
									__nextHasNoMarginBottom
									disabled={true}
								/>
							),
						},
						{
							name: 'currency_exchange_rates_server',
							defaultValue: 'ecb',
							label: __( 'Update Server', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<SelectControl
									label=""
									value={ field.value }
									options={ [
										{ label: __( 'European Central Bank (ECB)', 'currency-per-product-for-woocommerce' ), value: 'ecb' },
										{ label: __( 'TCMB', 'currency-per-product-for-woocommerce' ), value: 'tcmb' },
										{ label: __( 'Free Currency Converter API (free.currencyconverterapi.com)', 'currency-per-product-for-woocommerce' ), value: 'currencyconverterapi' },
										{ label: __( 'Coinbase', 'currency-per-product-for-woocommerce' ), value: 'coinbase' },
										{ label: __( 'CoinMarketCap (for Cryptocurrencies)', 'currency-per-product-for-woocommerce' ), value: 'coinmarketcap' },
									] }
									onChange={ field.onChange }
									help={ <ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink> }
									__nextHasNoMarginBottom
									disabled={true}
								/>
							),
						},
						{
							name: 'exchange_fees_types',
							defaultValue: 'markupflat',
							label: __( 'Exchange Fee Type', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<SelectControl
									label=""
									value={ field.value }
									options={ [
										{ label: __( '( + ) Markup with Flat Rates', 'currency-per-product-for-woocommerce' ), value: 'markupflat' },
										{ label: __( '( - ) Discount with Flat Rates', 'currency-per-product-for-woocommerce' ), value: 'discountflat' },
										{ label: __( '( % ) Markup with Percentage', 'currency-per-product-for-woocommerce' ), value: 'markuppercen' },
										{ label: __( '( % ) Discount with Percentage', 'currency-per-product-for-woocommerce' ), value: 'discountpercen' },
									] }
									onChange={ field.onChange }
									help={ <>{ __( "Allows Addition/Deduction to the automatically calculated Exchange Rate for currency. This option only works when the 'Update rates automatically' is used in the 'Exchange rates updates' setting. ", 'currency-per-product-for-woocommerce' ) }<ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink></> }
									__nextHasNoMarginBottom
									disabled={true}
								/>
							),
						},
						{
							name: 'apply_discount_automatic_additional_exchange_fee',
							defaultValue: 0,
							label: __( 'Exchange Fee Amount', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<>
									<NumberControl
										value={ field.value }
										onChange={ field.onChange }
										min={ 0 }
										step="any"
										disabled={true}
									/>
									<ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink>
								</>
							),
						},
						{
							name: 'round_exchange_enabled',
							defaultValue: false,
							label: __( 'Round Off Exchange Rates', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									__nextHasNoMarginBottom
									help={ <>{ __( 'Rounds fetched exchange rates to whole numbers before applying them to product prices. ', 'currency-per-product-for-woocommerce' ) }<ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink></> }
									disabled={true}
								/>
							),
						},
					] }
				/>

				{ currencyList.length > 0 && (
					<Card>
						<CardHeader>
							<Heading level={ 4 }>
								{ __( 'Configured Rates', 'currency-per-product-for-woocommerce' ) }
							</Heading>
						</CardHeader>
						<CardBody>
							<VStack spacing={ 6 }>
								{ currencyList.map( ( currCode, index ) => {
									const isBase   = index === 0;
									const currName = allCurrencies[ currCode ] || currCode;
									const label    = isBase ? `Base Currency [${ shopCurrency }]`  : `Currency #${ index } [${ shopCurrency } → ${ currCode }]`;

									return (
										<div key={ index } className="cpp-settings-row">
											<div className="cpp-settings-row__label">
												<Text className="cpp-settings-label" style={ { fontWeight: 600, display: 'flex', alignItems: 'center', gap: '4px' } }>
													{ label }
													{ isBase && <Icon icon={ lock } size={ 16 } /> }
												</Text>
												<Text style={ { color: '#757575' } }>{ currName }</Text>
											</div>
											<div className="cpp-settings-row__control">
												{ isBase ? (
													<NumberControl
														value={ 1 }
														disabled
														style={ { opacity: 0.6 } }
														help={ __( 'The base currency rate is always 1. All other rates are calculated relative to this.', 'currency-per-product-for-woocommerce' ) }
													/>
												) : (
													<VStack spacing={ 2 }>
														<Controller
															name={ `rates.${ index - 1 }.rate` }
															control={ control }
															defaultValue={ 0 }
															render={ ( { field } ) => (
																<NumberControl
																	value={ field.value ?? 0 }
																	onChange={ field.onChange }
																	min={ 0 }
																	step="any"
																/>
															) }
														/>
													</VStack>
												) }
											</div>
										</div>
									);
								} ) }
							</VStack>
						</CardBody>
					</Card>
				) }

				<FormActions isSaving={ isSaving } notice={ notice } onNoticeRemove={ () => setNotice( null ) } onReset={ resetSettings } />
			</VStack>
		</form>
	);
}

export default ExchangeRatesTab;
