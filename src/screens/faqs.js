/**
 * WordPress dependencies.
 */
import {
	Card,
	CardHeader,
	CardBody,
	Panel,
	PanelBody,
	__experimentalVStack as VStack,
	__experimentalHeading as Heading,
	__experimentalText as Text,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const faqs = [
	{
		question: __( 'How do I add a currency to a specific product?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'Open the product edit page in WooCommerce. In the Product Data section you will find a Currency per Product panel where you can choose a currency and enter prices. Save the product to apply the change.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'Can customers add products in different currencies to the same cart?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'This depends on the "Cart and checkout behaviour" setting. You can allow only products sharing the same currency, convert everything to the shop default currency, or convert to the currency of the last (or first) product added. Configure this under Configuration → Behaviour.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'I enabled "Show Original Price in Shop," but I do not see it on the frontend. Why?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'The Show Original Price in Shop setting is ignored when prices are displayed in their assigned currencies. Review your Shop Behaviour setting to verify how prices are configured to appear.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'Why are my converted prices different from live exchange rates?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'The plugin uses the exchange rates configured in the Exchange Rates settings. In the Lite version, these rates are entered manually and do not update automatically. As a result, displayed prices may differ from current market exchange rates if the configured values are outdated. Automatic exchange rate updates are available in the Pro version.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'When should I use the "Recalculate Prices" tool?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'Use Recalculate Prices after updating exchange rates when Save Product Prices is enabled. This regenerates the saved converted prices for all products.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'I changed a currency symbol, but the old symbol still appears in the mini cart. What should I do?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'Enable Mini Cart Currency Fix to recalculate cart totals on each page load and help resolve currency display issues in the mini cart.', 'currency-per-product-for-woocommerce' ),
	},
];

function Faqs() {
	return (
		<div className="cpp-configuration">
			<div className="cpp-heading-block">
				<Heading level={ 3 } className="cpp-dashboard__heading">
					{ __( 'FAQs', 'currency-per-product-for-woocommerce' ) }
				</Heading>
				<Text className="cpp-dashboard__subheading">
					{ __( 'Frequently asked questions about Currency per Product for WooCommerce', 'currency-per-product-for-woocommerce' ) }
				</Text>
			</div>

			<Card>
				<CardHeader>
					<VStack spacing={ 2 }>
						<Heading level={ 4 }>
							{ __( 'Frequently Asked Questions', 'currency-per-product-for-woocommerce' ) }
						</Heading>
						<Text className="components-text">
							{ __( 'Find answers to the most common questions about Currency per Product for WooCommerce.', 'currency-per-product-for-woocommerce' ) }
						</Text>
					</VStack>
				</CardHeader>

				<CardBody style={ { padding: 0 } }>
					<Panel>
						{ faqs.map( ( faq, index ) => (
							<PanelBody
								key={ index }
								title={ faq.question }
								initialOpen={ false }
							>
								<Text>{ faq.answer }</Text>
							</PanelBody>
						) ) }
					</Panel>
				</CardBody>
			</Card>
		</div>
	);
}

export default Faqs;
