/**
 * WordPress dependencies.
 */
import {
	Card,
	CardHeader,
	CardBody,
	Spinner,
	__experimentalHeading as Heading,
	__experimentalText as Text,
	__experimentalVStack as VStack,
	Icon,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { check, currencyDollar, update, tag, info } from '@wordpress/icons';

/**
 * External dependencies.
 */
import { NavLink } from 'react-router-dom';

/* ── SVG icons not in @wordpress/icons ──────────────────────────────── */

const SignalIcon = () => (
	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
		<circle cx="12" cy="12" r="3" stroke="currentColor" strokeWidth="1.5" />
		<path d="M6.34 6.34a8 8 0 0 0 0 11.32" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
		<path d="M17.66 6.34a8 8 0 0 1 0 11.32" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
		<path d="M3.52 3.52a13 13 0 0 0 0 16.96" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
		<path d="M20.48 3.52a13 13 0 0 1 0 16.96" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
	</svg>
);

const CheckCircle = ( { color = '#1a8d34' } ) => (
	<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
		<circle cx="10" cy="10" r="9" stroke={ color } strokeWidth="1.5" />
		<path d="M6 10l3 3 5-5" stroke={ color } strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

const EmptyCircle = () => (
	<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
		<circle cx="10" cy="10" r="9" stroke="#c3c4c7" strokeWidth="1.5" />
	</svg>
);

/* ── Helpers ─────────────────────────────────────────────────────────── */

function buildSteps( generalSettings, currenciesSettings, exchangeRatesSettings ) {
	const currencyCount      = currenciesSettings?.total_number ?? 0;
	const hasExchangeRate    = ( exchangeRatesSettings?.rates ?? {} )[ 1 ]?.rate !== 1
		|| exchangeRatesSettings?.exchange_rate_update === 'auto';
	const hasProductCurrency = window.cppAdminData?.hasProductCurrency ?? false;
    const isWcVariant = !! window?.cppAdminData?.isWcVariant;

	return [
		{
			label: __( 'Add currencies', 'currency-per-product-for-woocommerce' ),
			done: currencyCount >= 1,
		},
		{
			label: __( 'Configure exchange rates', 'currency-per-product-for-woocommerce' ),
			done: hasExchangeRate,
		},
		{
			label: __( 'Assign currencies to products', 'currency-per-product-for-woocommerce' ),
			done: hasProductCurrency,
		},
	].filter( ( { hidden } ) => ! hidden );
}

/* ── Component ───────────────────────────────────────────────────────── */

function Dashboard( { generalSettings, currenciesSettings, exchangeRatesSettings, isLoading } ) {
	if ( isLoading ) {
		return (
			<div className="cpp-dashboard" style={ { display: 'flex', justifyContent: 'center', padding: '60px 0' } }>
				<Spinner style={ { width: 32, height: 32 } } />
			</div>
		);
	}

	const steps        = buildSteps( generalSettings, currenciesSettings, exchangeRatesSettings );
	const doneCount    = steps.filter( ( s ) => s.done ).length;
	const pct          = Math.round( ( doneCount / steps.length ) * 100 );

	const isEnabled       = generalSettings?.enabled ?? false;
	const currencyCount   = currenciesSettings?.total_number ?? 0;
	const ratesUpToDate   = exchangeRatesSettings?.exchange_rate_update === 'auto';
	const hasProductCurrency = window.cppAdminData?.hasProductCurrency ?? false;

	return (
		<div className="cpp-dashboard">
			{/* Page heading */}
			<div className='cpp-heading-block'>
				<Heading level={ 3 } className="cpp-dashboard__heading">
					{ __( 'Dashboard', 'currency-per-product-for-woocommerce' ) }
				</Heading>
				<Text className="cpp-dashboard__subheading">
					{ __( 'Overview of your Currency per Product plugin', 'currency-per-product-for-woocommerce' ) }
				</Text>
			</div>

			{/* Two-column cards */}
			<div className="cpp-dashboard__cards">

				{/* Getting Started */}
				<Card className="cpp-getting-started-card">
					<CardHeader>
						<Heading level={ 4 }>
							{ __( 'Getting Started', 'currency-per-product-for-woocommerce' ) }
						</Heading>
					</CardHeader>
					<CardBody>
						{/* Progress */}
						<div className="cpp-getting-started__progress-row">
							<span>
								{ doneCount } { __( 'of', 'currency-per-product-for-woocommerce' ) } { steps.length } { __( 'completed', 'currency-per-product-for-woocommerce' ) }
							</span>
							<span className="cpp-getting-started__progress-pct">{ pct }%</span>
						</div>
						<div className="cpp-getting-started__progress-bar-track">
							<div
								className="cpp-getting-started__progress-bar-fill"
								style={ { width: `${ pct }%` } }
							/>
						</div>

						{/* Steps */}
						<div className="cpp-getting-started__steps">
							{ steps.map( ( step, i ) => (
								<div
									key={ i }
									className={ `cpp-getting-started__step${ step.done ? ' cpp-getting-started__step--done' : '' }` }
								>
									<span className="cpp-step-icon">
										{ step.done
											? <CheckCircle color="#2271b1" />
											: <EmptyCircle />
										}
									</span>
									{ step.label }
								</div>
							) ) }
						</div>
					</CardBody>
				</Card>

				{/* Plugin Summary */}
				<Card className="cpp-summary-card">
					<CardHeader>
						<Heading level={ 4 }>
							{ __( 'Plugin Summary', 'currency-per-product-for-woocommerce' ) }
						</Heading>
					</CardHeader>
					<CardBody>
						<div className="cpp-summary-card__grid">
							{/* Plugin Status */}
							<div className="cpp-summary-stat">
								<div className="cpp-summary-stat__icon">
									<SignalIcon />
								</div>
								<Text className="cpp-summary-stat__label">
									{ __( 'Plugin Status', 'currency-per-product-for-woocommerce' ) }
								</Text>
								<Text className={ `cpp-summary-stat__value${ isEnabled ? ' cpp-summary-stat__value--enabled' : ' cpp-summary-stat__value--inactive' }` }>
									{ isEnabled
										? __( 'Enabled', 'currency-per-product-for-woocommerce' )
										: __( 'Disabled', 'currency-per-product-for-woocommerce' ) }
								</Text>
							</div>

							{/* Active Currencies */}
							<div className="cpp-summary-stat">
								<div className="cpp-summary-stat__icon">
									<Icon icon={ currencyDollar } size={ 24 } />
								</div>
								<Text className="cpp-summary-stat__label">
									{ __( 'Active Currencies', 'currency-per-product-for-woocommerce' ) }
								</Text>
								<Text className="cpp-summary-stat__value">
									{ currencyCount > 0
										? `${ currencyCount } ${ __( 'configured', 'currency-per-product-for-woocommerce' ) }`
										: __( 'None', 'currency-per-product-for-woocommerce' ) }
								</Text>
							</div>

							{/* Exchange Rates */}
							<div className="cpp-summary-stat">
								<div className="cpp-summary-stat__icon">
									<Icon icon={ update } size={ 24 } style={{color: '#2271b1'}}/>
								</div>
								<Text className="cpp-summary-stat__label">
									{ __( 'Exchange Rates', 'currency-per-product-for-woocommerce' ) }
								</Text>
								<Text className={ `cpp-summary-stat__value${ ratesUpToDate ? ' cpp-summary-stat__value--uptodate' : ' cpp-summary-stat__value--inactive' }` }>
									{ ratesUpToDate
										? __( 'Up to date', 'currency-per-product-for-woocommerce' )
										: __( 'Manual', 'currency-per-product-for-woocommerce' ) }
								</Text>
							</div>

							{/* Per-Product Overrides */}
							<div className="cpp-summary-stat">
								<div className="cpp-summary-stat__icon">
									<Icon icon={ tag } size={ 24 } style={{color: '#2271b1'}} />
								</div>
								<Text className="cpp-summary-stat__label">
									{ __( 'Per-Product Overrides', 'currency-per-product-for-woocommerce' ) }
								</Text>
								<Text className={ hasProductCurrency ? "cpp-summary-stat__value cpp-summary-stat__value--active" : "cpp-summary-stat__value cpp-summary-stat__value--inactive"}>
									{ hasProductCurrency ? __( 'Active', 'currency-per-product-for-woocommerce' ) : __( 'Inactive', 'currency-per-product-for-woocommerce' ) }
								</Text>
							</div>
						</div>
					</CardBody>
				</Card>

			</div>

			{/* Quick Tip */}
			<div className="cpp-quick-tip">
				<span className="cpp-quick-tip__icon">
					<Icon icon={ info } size={ 18 } />
				</span>
				<div>
					<span className="cpp-quick-tip__title">
						{ __( 'Quick Tip', 'currency-per-product-for-woocommerce' ) }
					</span>
					{ __( 'You can assign a different currency to individual products, product categories, or tags. ', 'currency-per-product-for-woocommerce' ) }
					<NavLink to="/configuration">
						{ __( 'Visit the Configuration tab', 'currency-per-product-for-woocommerce' ) }
					</NavLink>
					{ __( ' to set up currencies and exchange rates, then assign them directly on each product\'s edit page.', 'currency-per-product-for-woocommerce' ) }
				</div>
			</div>
		</div>
	);
}

export default Dashboard;
