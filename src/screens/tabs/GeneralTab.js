/**
 * WordPress dependencies.
 */
import {
	__experimentalVStack as VStack,
	__experimentalInputControl as InputControl,
	TextareaControl,
	CheckboxControl,
	Button,
	ExternalLink,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';

/**
 * Internal dependencies.
 */
import { SettingsCardSection, FormActions } from '../../components';
import { saveGeneralSettings, resetTracking as resetTrackingApi } from '../../data/api';

const UPGRADE_URL = 'https://www.tychesoftwares.com/products/woocommerce-currency-per-product-plugin/?utm_source=cppupgradetopro&utm_medium=unlockall&utm_campaign=CurrencePerProductLite';


const toBoolean = ( v ) => v === 'yes' || v === true;

const DEFAULTS = {
	enabled: false,
	currency_reports_enabled: false,
	custom_currency_symbol_enabled: false,
	custom_currency_symbol_template: '%currency_code%%currency_symbol%',
	round_off_decimal_points: false,
};

function GeneralTab( { settings, onSaved } ) {
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice,   setNotice   ] = useState( null );
    const isWcVariant = !! window?.cppAdminData?.isWcVariant;

	const showNotice = ( message, status = 'success' ) => {
		setNotice( { message, status } );
		if ( status === 'success' ) {
			setTimeout( () => setNotice( null ), 3000 );
		}
	};

	const { control, handleSubmit, reset, watch } = useForm( {
		defaultValues: settings ?? DEFAULTS,
	} );

	useEffect( () => {
		if ( settings )
			reset( { ...DEFAULTS, ...settings } );
		},
	[ settings, reset ] );

	const onSubmit = async ( data ) => {
		setIsSaving( true );
		try {
			await saveGeneralSettings( data );
			showNotice( __( 'Settings saved.', 'currency-per-product-for-woocommerce' ) );
			onSaved?.();
		} catch {
			showNotice( __( 'Error saving settings.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	const resetTracking = async () => {
        setIsSaving(true);
        try {
            await resetTrackingApi();
            showNotice( __( 'Tracking has been successfully reset.', 'currency-per-product-for-woocommerce' ) );
        } catch ( error ) {
			showNotice( __( 'Error resetting tracking.', 'currency-per-product-for-woocommerce' ), 'error' );
        } finally {
            setIsSaving(false);
        }
    };

	const resetSettings = async () => {
		setIsSaving( true );
		try {
			await saveGeneralSettings( DEFAULTS );
			const latest = await onSaved?.();
			reset( latest ?? DEFAULTS );
			showNotice( __( 'Settings have been successfully reset to default values.', 'currency-per-product-for-woocommerce' ) );
		} catch {
			showNotice( __( 'Error resetting settings.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	return (
		<form onSubmit={ handleSubmit( onSubmit ) }>
			<VStack className={'cpp_setting_section'} spacing={10}>
				<SettingsCardSection
					heading={ __( 'Currency per Product Options', 'currency-per-product-for-woocommerce' ) }
					subHeading={ __( 'Enable or disable the plugin functionality', 'currency-per-product-for-woocommerce' ) }
					control={ control }
					fields={ [
						{
							name: 'enabled',
							defaultValue: false,
							label: __( 'Enable Currency per Product', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable Plugin', 'currency-per-product-for-woocommerce' ) }
									help={ __( 'Displays product prices in your configured currencies across your store.', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
								/>
							),
						},
					] }
				/>

				<SettingsCardSection
					heading={ __( 'General Options', 'currency-per-product-for-woocommerce' ) }
					subHeading={ __( 'Configure general plugin options and preferences', 'currency-per-product-for-woocommerce' ) }
					control={ control }
					fields={ [
						{
							name: 'currency_reports_enabled',
							defaultValue: false,
							label: __( 'Currency Reports', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ __( 'Adds a currency filter to WooCommerce Reports in the admin toolbar.', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
								/>
							),
						},
						{
							name: 'custom_currency_symbol_enabled',
							defaultValue: false,
							label: __( 'Custom Currency Symbol', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ __( 'Use a custom currency symbol on the shop and in the admin. Configure the format using the template field below.', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
								/>
							),
						},
						{
							name: 'custom_currency_symbol_template',
							defaultValue: '%currency_code%%currency_symbol%',
							showWhen: toBoolean( watch( 'custom_currency_symbol_enabled' ) ),
							label: __( 'Currency Symbol Template', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<TextareaControl
									value={ field.value }
									onChange={ field.onChange }
									help={ __( 'Replaced values: %currency_code%, %currency_symbol%.', 'currency-per-product-for-woocommerce' ) }
								/>
							),
						},
						{
							name: 'round_off_decimal_points',
							defaultValue: false,
							label: __( 'Round Off Product Prices', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ <>{ __( 'Rounds converted prices to the nearest whole number (e.g. €9.87 → €10). ', 'currency-per-product-for-woocommerce' ) }<ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink></> }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									disabled={true}
								/>
							),
						},
					] }
				/>

				{ ! isWcVariant && (
                    <SettingsCardSection
						heading={ __( 'Usage Data', 'product-input-fields-for-woocommerce' ) }
						control={ control }
						fields={ [
							{
								name: 'ts_reset_tracking',
								defaultValue: false,
								render: ( field ) => (
									<Button
										variant="secondary"
										onClick={resetTracking}
										help={ __( 'This will reset your usage tracking settings, causing it to show the opt-in banner again and not sending any data.', 'custom-order-numbers-for-woocommerce' ) }
									> { __( 'Reset Usage Tracking', 'custom-order-numbers-for-woocommerce' ) }</Button>
								),
							},
						] }
					/>
                ) }

				<FormActions isSaving={ isSaving } notice={ notice } onNoticeRemove={ () => setNotice( null ) } onReset={ resetSettings } />
			</VStack>
		</form>
	);
}

export default GeneralTab;
