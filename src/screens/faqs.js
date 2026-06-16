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
		question: __( 'Will existing prices be converted automatically when I set an exchange rate?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'Yes. When a product does not have an explicit price in the selected currency, the plugin multiplies the base price by the configured exchange rate. Manual prices set on each product always take precedence over automatic conversion.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'How do I update exchange rates automatically?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'Go to Configuration → Exchange Rates and set "Exchange rates updates" to "Update rates automatically". Then choose an update server and a frequency (hourly, twice daily, or daily). The plugin will fetch fresh rates via WordPress Cron.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'Can customers add products in different currencies to the same cart?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'This depends on the "Cart and checkout behaviour" setting. You can allow only products sharing the same currency, convert everything to the shop default currency, or convert to the currency of the last (or first) product added. Configure this under Configuration → Behaviour.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'What does the "Convert prices based on customer\'s location" option do?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'When this option is selected, the plugin detects the visitor\'s IP address or geolocation and automatically applies the matching currency from your configured list. Enable "Enable by Location" under Behaviour → Additional Settings to activate this feature.', 'currency-per-product-for-woocommerce' ),
	},
	{
		question: __( 'My license shows inactive after entering the key. What should I check?', 'currency-per-product-for-woocommerce' ),
		answer: __( 'Verify that the key contains no extra spaces and has not exceeded its activation limit. Also confirm that your server allows outbound HTTP requests for license validation. If the issue persists, contact Tyche Softwares support with your purchase order number and site URL.', 'currency-per-product-for-woocommerce' ),
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
