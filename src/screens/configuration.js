/**
 * WordPress dependencies.
 */
import {
	__experimentalHeading as Heading,
	__experimentalText as Text,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Tab components.
 */
import GeneralTab       from './tabs/GeneralTab';
import BehaviourTab     from './tabs/BehaviourTab';
import CurrenciesTab    from './tabs/CurrenciesTab';
import ExchangeRatesTab from './tabs/ExchangeRatesTab';
import AdvancedTab      from './tabs/AdvancedTab';

const TABS = [
	{ name: 'general',        title: __( 'General',        'currency-per-product-for-woocommerce' ) },
	{ name: 'behaviour',      title: __( 'Behaviour',      'currency-per-product-for-woocommerce' ) },
	{ name: 'currencies',     title: __( 'Currencies',     'currency-per-product-for-woocommerce' ) },
	{ name: 'exchange-rates', title: __( 'Exchange Rates', 'currency-per-product-for-woocommerce' ) },
	{ name: 'advanced',       title: __( 'Advanced',       'currency-per-product-for-woocommerce' ) },
];

function Configuration( {
	generalSettings,
	behaviourSettings,
	currenciesSettings,
	exchangeRatesSettings,
	advancedSettings,
	onSettingsSaved,
} ) {
	const [ activeTab, setActiveTab ] = useState( TABS[ 0 ].name );

	return (
		<div className="cpp-configuration">

			<div className='cpp-heading-block'>
				{/* Page heading */}
				<Heading level={ 3 } className="cpp-configuration__heading">
					{ __( 'Settings', 'currency-per-product-for-woocommerce' ) }
				</Heading>
				<Text className="cpp-configuration__subheading">
					{ __( 'Configure plugin options and preferences', 'currency-per-product-for-woocommerce' ) }
				</Text>
			</div>

			{/* Tab bar */}
			<div className="cpp-config-tabs">
				{ TABS.map( ( tab, index ) => (
					<>
						<button
							key={ tab.name }
							type="button"
							className={ `cpp-config-tab${ activeTab === tab.name ? ' is-active' : '' }` }
							onClick={ () => setActiveTab( tab.name ) }
						>
							{ tab.title }
						</button>
						{ index < TABS.length - 1 && (
							<span className="cpp-config-tab-divider" aria-hidden="true">|</span>
						) }
					</>
				) ) }
			</div>

			{/* Tab content */}
			<div className="cpp-config-content">
				{ activeTab === 'general'        && <GeneralTab       settings={ generalSettings }       onSaved={ onSettingsSaved } /> }
				{ activeTab === 'behaviour'      && <BehaviourTab     settings={ behaviourSettings }     onSaved={ onSettingsSaved } /> }
				{ activeTab === 'currencies'     && <CurrenciesTab    settings={ currenciesSettings }    onSaved={ onSettingsSaved } /> }
				{ activeTab === 'exchange-rates' && <ExchangeRatesTab settings={ exchangeRatesSettings } currenciesSettings={ currenciesSettings } onSaved={ onSettingsSaved } /> }
				{ activeTab === 'advanced'       && <AdvancedTab      settings={ advancedSettings }      onSaved={ onSettingsSaved } /> }
			</div>

			<style>{ `
				tr:not(:last-child) td { padding-bottom: 24px; }
				td:nth-child(2) { padding-left: 30px; }
			` }</style>

		</div>
	);
}

export default Configuration;
