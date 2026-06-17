/**
 * WordPress dependencies.
 */
import {
	__experimentalVStack as VStack,
	__experimentalHeading as Heading,
	__experimentalText as Text,
	__experimentalConfirmDialog as ConfirmDialog,
	Card,
	CardHeader,
	CardBody,
	CheckboxControl,
	Button,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';

/**
 * Internal dependencies.
 */
import { SettingsCardSection, FormActions } from '../../components';
import { saveAdvancedSettings, recalculatePrices, deletePluginData } from '../../data/api';

const toBoolean = ( v ) => v === 'yes' || v === true;

const DEFAULTS = {
	fix_mini_cart: false,
	sort_by_converted_price: false,
	filter_by_converted_price: false,
	save_products_prices: false,
};

function AdvancedTab( { settings, onSaved } ) {
	const [ isSaving, setIsSaving ]                 = useState( false );
	const [ notice,   setNotice   ]                 = useState( null );
	const [ isRecalculating, setIsRecalculating ]   = useState( false );

	const [ isDeletingData,      setIsDeletingData      ] = useState( false );
	const [ isConfirmDialogOpen, setIsConfirmDialogOpen ] = useState( false );

	const showNotice = ( message, status = 'success' ) => {
		setNotice( { message, status } );
		if ( status === 'success' ) {
			setTimeout( () => setNotice( null ), 3000 );
		}
	};

	const { control, handleSubmit, reset } = useForm( {
		defaultValues: settings ?? DEFAULTS,
	} );

	useEffect( () => { if ( settings ) reset( { ...DEFAULTS, ...settings } ); }, [ settings, reset ] );

	const resetSettings = async () => {
		setIsSaving( true );
		try {
			await saveAdvancedSettings( DEFAULTS );
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
			await saveAdvancedSettings( data );
			showNotice( __( 'Settings saved.', 'currency-per-product-for-woocommerce' ) );
			onSaved?.();
		} catch {
			showNotice( __( 'Error saving settings.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	const handleRecalculatePrices = async () => {
		setIsRecalculating( true );
		try {
			await recalculatePrices();
			showNotice( __( 'Prices re-calculated successfully.', 'currency-per-product-for-woocommerce' ) );
		} catch {
			showNotice( __( 'Error re-calculating prices.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsRecalculating( false );
		}
	};

	const handleDeletePluginData = async () => {
		setIsDeletingData( true );
		try {
			await deletePluginData();
			await onSaved?.();
			showNotice( __( 'All plugin data deleted.', 'currency-per-product-for-woocommerce' ) );
		} catch {
			showNotice( __( 'Error deleting plugin data.', 'currency-per-product-for-woocommerce' ), 'error' );
		} finally {
			setIsDeletingData( false );
		}
	};

	return (
		<VStack className={ 'cpp_setting_section' } spacing={ 10 }>
			<form onSubmit={ handleSubmit( onSubmit ) } style={ { display: 'contents' } }>
				<SettingsCardSection
					heading={ __( 'Advanced Options', 'currency-per-product-for-woocommerce' ) }
					control={ control }
					fields={ [
						{
							name: 'fix_mini_cart',
							defaultValue: false,
							label: __( 'Mini Cart Currency Fix', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ __( 'Enable if you have issues with the currency symbol in mini cart. It will recalculate cart totals on each page load.', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									__nextHasNoMarginBottom
								/>
							),
						},
						{
							name: 'sort_by_converted_price',
							defaultValue: false,
							label: __( 'Sorting by Converted Price', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ __( 'Applies converted prices to WooCommerce\'s \'Sort by price\' feature on the shop page.', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									__nextHasNoMarginBottom
								/>
							),
						},
						{
							name: 'filter_by_converted_price',
							defaultValue: false,
							label: __( 'Filtering by Converted Price', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ __( 'Use converted prices in WooCommerce "Filter Products by Price" widget.', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									__nextHasNoMarginBottom
								/>
							),
						},
						{
							name: 'save_products_prices',
							defaultValue: false,
							label: __( 'Save Product Prices', 'currency-per-product-for-woocommerce' ),
							render: ( field ) => (
								<CheckboxControl
									label={ __( 'Enable', 'currency-per-product-for-woocommerce' ) }
									help={ __( 'Saves converted prices to the database to prevent conflicts with other plugins. Each price is converted once and stored, rather than recalculated on every page load.', 'currency-per-product-for-woocommerce' ) }
									checked={ toBoolean( field.value ) }
									onChange={ field.onChange }
									__nextHasNoMarginBottom
								/>
							),
						},
					] }
				/>

			{ /* Tools */ }
			<Card>
				<CardHeader>
					<Heading level={ 4 }>{ __( 'Tools', 'currency-per-product-for-woocommerce' ) }</Heading>
				</CardHeader>
				<CardBody>
					<div className="cpp-settings-row">
						<div className="cpp-settings-row__label">
							<Text className="cpp-settings-label">{ __( 'Re-calculate prices', 'currency-per-product-for-woocommerce' ) }</Text>
						</div>
						<div className="cpp-settings-row__control">
							<Button
								variant="secondary"
								type="button"
								onClick={ handleRecalculatePrices }
								isBusy={ isRecalculating }
								disabled={ isRecalculating }
							>
								{ __( 'Recalculate Prices', 'currency-per-product-for-woocommerce' ) }
							</Button>
							<Text className="cpp-tool-help">
								{ __( 'Saves converted prices for all products, used by the sort and filter features. This cannot be undone — run only when needed.', 'currency-per-product-for-woocommerce' ) }
							</Text>
						</div>
					</div>
				</CardBody>
			</Card>

			{ /* Danger Zone */ }
			<Card>
				<CardHeader>
					<Heading level={ 4 }>{ __( 'Danger Zone', 'currency-per-product-for-woocommerce' ) }</Heading>
				</CardHeader>
				<CardBody>
					<div className="cpp-settings-row">
						<div className="cpp-settings-row__label">
							<Text className="cpp-settings-label">{ __( 'Delete all plugin data', 'currency-per-product-for-woocommerce' ) }</Text>
						</div>
						<div className="cpp-settings-row__control">
							<Button
								variant="primary"
								type="button"
								isDestructive
								onClick={ () => setIsConfirmDialogOpen( true ) }
								isBusy={ isDeletingData }
								disabled={ isDeletingData }
							>
								{ __( "Delete all plugin's data", 'currency-per-product-for-woocommerce' ) }
							</Button>
							<ConfirmDialog
								isOpen={ isConfirmDialogOpen }
								cancelButtonText={ __( 'Cancel', 'currency-per-product-for-woocommerce' ) }
								confirmButtonText={ __( 'Delete', 'currency-per-product-for-woocommerce' ) }
								onCancel={ () => setIsConfirmDialogOpen( false ) }
								onConfirm={ () => { setIsConfirmDialogOpen( false ); handleDeletePluginData(); } }
							>
								{ __( 'Are you sure? This will delete ALL plugin data, options and product meta. There is no undo.', 'currency-per-product-for-woocommerce' ) }
							</ConfirmDialog>
							<Text className="cpp-tool-help">
								{ __( 'Permanently deletes all plugin settings, currencies, and product price overrides. This cannot be undone.', 'currency-per-product-for-woocommerce' ) }
							</Text>
						</div>
					</div>
				</CardBody>
			</Card>

			<FormActions isSaving={ isSaving } notice={ notice } onNoticeRemove={ () => setNotice( null ) } onReset={ resetSettings } />
		</form>
		</VStack>
	);
}

export default AdvancedTab;
