jQuery(function ($) {
	const exchangeRates   = CPP_Data.exchangeRates;
	const productCurrency = CPP_Data.productCurrency;
	const symbol          = CPP_Data.currency_symbol;
	const $form = $('form.variations_form');
	if ($form.length && CPP_Data.shop_behaviour !== 'convert_shop_default' && CPP_Data.shop_behaviour !== 'convert_customer_based' ) {
		$form.on('wc_variation_form', function () {
			const variations      = $form.data('product_variations');
			const convertedPrices = [];
			variations.forEach(function (variation) {
				const price       = parseFloat(variation.display_price ?? variation.variation_price ?? 0);
				const varCurrency = variation.cpp_currency || productCurrency;

				if (!price || !varCurrency || !exchangeRates[varCurrency]) {
					return;
				}
				const converted = (price * exchangeRates[varCurrency]) / exchangeRates[productCurrency];
				convertedPrices.push(converted);
			});
			if (!convertedPrices.length) {
				return;
			}
			const min = Math.min(...convertedPrices).toFixed(2);
			const max = Math.max(...convertedPrices).toFixed(2);
			let newPriceRange = `${symbol}${min}`;
			if (min !== max) {
				newPriceRange += ` – ${symbol}${max}`;
			}
			const $priceContainer = $('.woocommerce-variation-price .price, .price');
			if ($priceContainer.length) {
				$priceContainer.first().html(newPriceRange);
			}
		});
	}
});
