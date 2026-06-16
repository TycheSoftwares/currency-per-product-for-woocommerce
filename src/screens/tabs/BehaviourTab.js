/**
 * WordPress dependencies.
 */
import {
	__experimentalVStack as VStack,
	__experimentalInputControl as InputControl,
	CheckboxControl,
	SelectControl,
	TextareaControl,
	ExternalLink,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';

/**
 * Internal dependencies.
 */
import { SettingsCardSection, FormActions } from '../../components';
import { saveBehaviourSettings } from '../../data/api';

const UPGRADE_URL = 'https://www.tychesoftwares.com/products/woocommerce-currency-per-product-plugin/?utm_source=cppupgradetopro&utm_medium=unlockall&utm_campaign=CurrencePerProductLite';

const toBoolean = ( v ) => v === 'yes' || v === true;

const DEFAULTS = {
	shop_behaviour: 'show_in_different',
	original_price_in_shop_enabled: false,
	original_price_in_shop_template: '<br>%price%',
	cart_checkout: 'convert_shop_default',
	original_price_in_cart_checkout_enabled: false,
	original_price_in_cart_checkout_template: '<br>%price%',
	cart_checkout_leave_one_product: 'Only one product can be added to the cart. Clear the cart or finish the order, before adding another product to the cart.',
	cart_checkout_leave_same_currency: 'Only products with same currency can be added to the cart. Clear the cart or finish the order, before adding products with another currency to the cart.',
	currency_by_location: false,
};

function BehaviourTab( { settings, onSaved } ) {
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice,   setNotice   ] = useState( null );

	const showNotice = ( message, status = 'success' ) => {
		setNotice( { message, status } );
		if ( status === 'success' ) {
			setTimeout( () => setNotice( null ), 3000 );
		}
	};

	const { control, handleSubmit, reset, watch } = useForm( {
		defaultValues: settings ?? DEFAULTS,
	} );

	useEffect( () => { if ( settings ) reset( { ...DEFAULTS, ...settings } ); }, [ settings, reset ] );

	const resetSettings = async () => {
		setIsSaving( true );
		try {
			await saveBehaviourSettings( DEFAULTS );
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
			await saveBehaviourSettings( data );
			showNotice( __( 'Settings saved.', 'currency-per-product-for-woocommerce' ) );
			onSaved?.();
		} catch {
			showNotice( __( 'Error saving settings.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	const shopBehaviour          = watch( 'shop_behaviour' );
	const cartCheckout           = watch( 'cart_checkout' );
	const originalPriceInShop    = toBoolean( watch( 'original_price_in_shop_enabled' ) );
	const originalPriceInCart    = toBoolean( watch( 'original_price_in_cart_checkout_enabled' ) );

	const cartBaseHelp = __( "Only applies when Shop Behaviour is not set to 'Convert to shop default currency.'", 'currency-per-product-for-woocommerce' );
	const cartCheckoutHelp = cartCheckout === 'convert_customer_based'
		? cartBaseHelp + ' ' + __( "Note: To use the Convert prices based on customer's location option, you must enable the Enable by Location option in the Additional Settings section.", 'currency-per-product-for-woocommerce' )
		: cartCheckout === 'user_selected_currency'
			? cartBaseHelp + ' ' + __( 'Note: To use the Convert to User-Selected Currency option, you must add the Currency Switcher (CPP) widget to one of your site\'s widget areas (e.g., sidebar, footer, or header). This widget allows customers to select their preferred currency.', 'currency-per-product-for-woocommerce' )
			: cartBaseHelp;

	const shopBehaviourHelp = shopBehaviour === 'convert_customer_based'
		? __( "Note: To use the Convert prices based on customer's location option, you must enable the Enable by Location option in the Additional Settings section.", 'currency-per-product-for-woocommerce' )
		: shopBehaviour === 'user_selected_currency'
			? __( 'Note: To use the Convert to User-Selected Currency option, you must add the Currency Switcher (CPP) widget to one of your site\'s widget areas (e.g., sidebar, footer, or header). This widget allows customers to select their preferred currency.', 'currency-per-product-for-woocommerce' )
			: __( 'Control how currency is displayed in shop pages', 'currency-per-product-for-woocommerce' );

	return (
		<form onSubmit={ handleSubmit( onSubmit ) }>
			<VStack className={'cpp_setting_section'} spacing={10}>

				<SettingsCardSection
					heading={ __( 'Shop Behaviour Options', 'currency-per-product-for-woocommerce' ) }
					control={ control }
					fields={ [
						{
							name: 'shop_behaviour',
							defaultValue: 'show_in_different',
							label: __( 'Shop Behaviour', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<SelectControl
									label=""
									value={ field.value }
									options={ [
										{ label: __( 'Show prices in different currencies (and set cart and checkout behaviour separately)', 'currency-per-product-for-woocommerce' ), value: 'show_in_different' },
										{ label: __( 'Convert to shop default currency (including cart and checkout)', 'currency-per-product-for-woocommerce' ), value: 'convert_shop_default' },
										{ label: __( 'Convert to User-Selected Currency (PRO)', 'currency-per-product-for-woocommerce' ), value: 'user_selected_currency', disabled: true },
										{ label: __( "Convert prices based on customer's location (PRO)", 'currency-per-product-for-woocommerce' ), value: 'convert_customer_based', disabled: true },
									] }
									onChange={ field.onChange }
									help={ shopBehaviourHelp }
									__nextHasNoMarginBottom
								/>
							),
						},
						{
							name: 'original_price_in_shop_enabled',
							defaultValue: false,
							label: __( 'Show Original Price in Shop', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ __( 'Display original (unconverted) price on shop pages and product lists. Ignored, if \'Shop behaviour\' option is set to \'Show prices in different currencies ...\'', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									__nextHasNoMarginBottom
								/>
							),
						},
						{
							name: 'original_price_in_shop_template',
							defaultValue: '<br>%price%',
							showWhen: originalPriceInShop,
							label: __( 'Original Price Template (Shop)', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<InputControl
									value={ field.value }
									onChange={ field.onChange }
									help={ __( 'Replaced values: %price%, %price_raw%, %currency_code%.', 'currency-per-product-for-woocommerce' ) }
								/>
							),
						},
					] }
				/>

				<SettingsCardSection
					heading={ __( 'Cart and Checkout Behaviour Options', 'currency-per-product-for-woocommerce' ) }
					control={ control }
					fields={ [
						{
							name: 'cart_checkout',
							defaultValue: 'convert_shop_default',
							label: __( 'Cart and Checkout Behaviour', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<SelectControl
									label=""
									value={ field.value }
									options={ [
										{ label: __( 'Convert to shop default currency', 'currency-per-product-for-woocommerce' ), value: 'convert_shop_default' },
										{ label: __( 'Leave product currency (allow only one product to be added to cart)', 'currency-per-product-for-woocommerce' ), value: 'leave_one_product' },
										{ label: __( 'Leave product currency (allow only same currency products to be added to cart)', 'currency-per-product-for-woocommerce' ), value: 'leave_same_currency' },
										{ label: __( 'Convert to currency of last product in cart', 'currency-per-product-for-woocommerce' ), value: 'convert_last_product' },
										{ label: __( 'Convert to currency of first product in cart', 'currency-per-product-for-woocommerce' ), value: 'convert_first_product' },
										{ label: __( 'Convert to User-Selected Currency (PRO)', 'currency-per-product-for-woocommerce' ), value: 'user_selected_currency', disabled: true },
										{ label: __( "Convert prices based on customer's location (PRO)", 'currency-per-product-for-woocommerce' ), value: 'convert_customer_based', disabled: true },
									] }
									onChange={ field.onChange }
									help={ cartCheckoutHelp }
									__nextHasNoMarginBottom
								/>
							),
						},
						{
							name: 'original_price_in_cart_checkout_enabled',
							defaultValue: false,
							label: __( 'Show Original Price in Cart, Checkout & Emails', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ <>{ __( "Display original price in cart, checkout pages and emails. Ignored, if 'Shop behaviour' option is set to 'Show prices in different currencies ...' ", 'currency-per-product-for-woocommerce' ) }<ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink></> }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									__nextHasNoMarginBottom
									disabled={true}
								/>
							),
						},
						{
							name: 'original_price_in_cart_checkout_template',
							defaultValue: '<br>%price%',
							showWhen: originalPriceInCart,
							label: __( 'Original Price Template (Cart)', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<InputControl
									value={ field.value }
									onChange={ field.onChange }
									help={ __( 'Replaced values: %price%, %price_raw%, %currency_code%.', 'currency-per-product-for-woocommerce' ) }
								/>
							),
						},
						{
							name: 'cart_checkout_leave_one_product',
							defaultValue: '',
							showWhen: cartCheckout === 'leave_one_product',
							label: __( 'Single-Currency Cart Message', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<InputControl
									value={ field.value }
									onChange={ field.onChange }
									help={ __( 'Shown when a customer tries to add a second product with a different currency.', 'currency-per-product-for-woocommerce' ) }
								/>
							),
						},
						{
							name: 'cart_checkout_leave_same_currency',
							defaultValue: '',
							showWhen: cartCheckout === 'leave_same_currency',
							label: __( 'Mixed-Currency Cart Restriction Message', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<InputControl
									value={ field.value }
									onChange={ field.onChange }
									help={ __( 'Shown when a customer tries to add a product with a different currency to the cart.', 'currency-per-product-for-woocommerce' ) }
								/>
							),
						},
					] }
				/>

				<SettingsCardSection
					heading={ __( 'Location-Based Currency', 'currency-per-product-for-woocommerce' ) }
					control={ control }
					fields={ [
						{
							name: 'currency_by_location',
							defaultValue: false,
							label: __( 'Auto-Detect Customer Location', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ <>{ __( "Detects the customer's location via IP or geolocation and applies the matching currency automatically. Requires Shop Behaviour to be set to 'Convert prices based on customer's location.' ", 'currency-per-product-for-woocommerce' ) }<ExternalLink href={ UPGRADE_URL } className="cpp-upgrade-link">{ __( 'Upgrade to Pro', 'currency-per-product-for-woocommerce' ) }</ExternalLink></> }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									__nextHasNoMarginBottom
									disabled={true}
								/>
							),
						},
					] }
				/>

				<FormActions isSaving={ isSaving } notice={ notice } onNoticeRemove={ () => setNotice( null ) } onReset={ resetSettings } />

			</VStack>
		</form>
	);
}

export default BehaviourTab;
