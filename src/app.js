/**
 * WordPress dependencies.
 */
import { __experimentalVStack as VStack, __experimentalHStack as HStack, ExternalLink, __experimentalText as Text,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { home, settings, lock, info } from '@wordpress/icons';

/**
 * External dependencies.
 */
import { Navigate, Route, Routes } from 'react-router-dom';

/**
 * Internal dependencies.
 */
import { Header } from './components';
import { Dashboard, Configuration, License, Faqs } from './screens';
import { fetchGeneralSettings, fetchBehaviourSettings, fetchCurrenciesSettings, fetchExchangeRatesSettings, fetchAdvancedSettings, getLicense } from './data/api';

function App() {
	const [ generalSettings, setGeneralSettings ] = useState( null );
	const [ behaviourSettings, setBehaviourSettings ] = useState( null );
	const [ currenciesSettings, setCurrenciesSettings ] = useState( null );
	const [ exchangeRatesSettings, setExchangeRatesSettings ] = useState( null );
	const [ advancedSettings, setAdvancedSettings ] = useState( null );
	const [licenseSettings, setLicenseSettings] = useState(null);
	const [ isLoading, setIsLoading ] = useState( true );
    const isWcVariant = !! window?.cppAdminData?.isWcVariant;

	const loadSettings = async ( withLoader = false ) => {
		if ( withLoader ) setIsLoading( true );
		try {
			const [ general, behaviour, currencies, exchangeRates, advanced ] = await Promise.all( [
				fetchGeneralSettings(),
				fetchBehaviourSettings(),
				fetchCurrenciesSettings(),
				fetchExchangeRatesSettings(),
				fetchAdvancedSettings(),
			] );
			setGeneralSettings( general );
			setBehaviourSettings( behaviour );
			setCurrenciesSettings( currencies );
			setExchangeRatesSettings( exchangeRates );
			setAdvancedSettings( advanced );
		} finally {
			if ( withLoader ) setIsLoading( false );
		}
	};

	useEffect( () => {
		loadSettings( true );
	}, [] );

	const tabs = [
		{
			name: 'dashboard',
			title: __( 'Dashboard', 'currency-per-product-for-woocommerce' ),
			path: '/',
			icon: 'dashicons-admin-home',
		},
		{
			name: 'configuration',
			title: __( 'Configuration', 'currency-per-product-for-woocommerce' ),
			path: '/configuration',
			icon: 'dashicons-admin-settings',
		},
		{
			name: 'faqs',
			title: __( 'FAQs', 'currency-per-product-for-woocommerce' ),
			path: '/faqs',
			icon: 'dashicons-editor-help',
		},
	];

	return (
		<>
			<Header
				title={ __( 'Currency per Product for WooCommerce', 'currency-per-product-for-woocommerce' ) }
				description={ __( 'Set and display prices for WooCommerce products in different currencies.', 'currency-per-product-for-woocommerce' ) }
				tabs={ tabs }
			/>

			<VStack>
				<Routes>
					<Route
						path="/"
						element={
							<Dashboard
								generalSettings={ generalSettings }
								currenciesSettings={ currenciesSettings }
								exchangeRatesSettings={ exchangeRatesSettings }
								isLoading={ isLoading }
							/>
						}
					/>
					<Route
						path="/configuration"
						element={
							<Configuration
								generalSettings={ generalSettings }
								behaviourSettings={ behaviourSettings }
								currenciesSettings={ currenciesSettings }
								exchangeRatesSettings={ exchangeRatesSettings }
								advancedSettings={ advancedSettings }
								onSettingsSaved={ () => loadSettings() }
							/>
						}
					/>
					<Route path="/faqs" element={ <Faqs /> } />
					<Route path="*" element={ <Navigate to="/" replace /> } />
				</Routes>
			</VStack>

			<VStack style={{ padding: "20px 0" }}>
                <HStack justify="center" style={{ marginBottom: "22px" }}>
                    <ExternalLink href="https://support.tychesoftwares.com/help/2285384554/" className="bogo-link">
                    Need support?
                    </ExternalLink>
                    <Text style={{ fontWeight: "bold" }}>
                    We’re always happy to help you.
                    </Text>
                </HStack>
                <HStack justify="center">
                    <Text>If this plugin helped you,</Text>
                    <ExternalLink href="https://www.tychesoftwares.com/submit-review/" className="bogo-link">
                    please rate it
                    </ExternalLink>
                    <Text style={{ fontSize: "17px", color: "#FFBA00" }}>★★★★★</Text>
                </HStack>
            </VStack>
		</>
	);
}

export default App;
