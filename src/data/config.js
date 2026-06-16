/**
 * Plugin-wide configuration constants.
 */

export const PLUGIN_SLUG = 'currency-per-product-for-woocommerce';

export const SHOP_BEHAVIOUR_OPTIONS = [
	{ label: 'Show prices in different currencies (set cart behaviour separately)', value: 'show_in_different' },
	{ label: 'Convert to shop default currency (including cart and checkout)',       value: 'convert_shop_default' },
	{ label: 'Convert to User-Selected Currency',                                   value: 'user_selected_currency' },
	{ label: "Convert prices based on customer's location",                          value: 'convert_customer_based' },
];

export const CART_CHECKOUT_OPTIONS = [
	{ label: 'Convert to shop default currency',                                         value: 'convert_shop_default' },
	{ label: 'Leave product currency (allow only one product to be added to cart)',       value: 'leave_one_product' },
	{ label: 'Leave product currency (allow only same currency products)',                value: 'leave_same_currency' },
	{ label: 'Convert to currency of last product in cart',                              value: 'convert_last_product' },
	{ label: 'Convert to currency of first product in cart',                             value: 'convert_first_product' },
	{ label: 'Convert to User-Selected Currency',                                        value: 'user_selected_currency' },
	{ label: "Convert prices based on customer's location",                              value: 'convert_customer_based' },
];

export const EXCHANGE_RATE_UPDATE_OPTIONS = [
	{ label: 'Enter rates manually',       value: 'manual' },
	{ label: 'Update rates automatically', value: 'auto' },
];

export const EXCHANGE_RATE_FREQUENCY_OPTIONS = [
	{ label: 'Update Hourly',      value: 'hourly' },
	{ label: 'Update Twice Daily', value: 'twicedaily' },
	{ label: 'Update Daily',       value: 'daily' },
];

export const EXCHANGE_FEES_TYPES_OPTIONS = [
	{ label: '( + ) Markup with Flat Rates',    value: 'markupflat' },
	{ label: '( - ) Discount with Flat Rates',  value: 'discountflat' },
	{ label: '( % ) Markup with Percentage',    value: 'markuppercen' },
	{ label: '( % ) Discount with Percentage',  value: 'discountpercen' },
];
