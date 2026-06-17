( function () {

	if (
		typeof ALG_CPP_BLOCK_DATA === 'undefined' ||
		ALG_CPP_BLOCK_DATA.doRun !== 'yes' ||
		!ALG_CPP_BLOCK_DATA.items
	) {
		return;
	}
	const DATA = ALG_CPP_BLOCK_DATA.items;
	const renderOriginalPrices = () => {
		const keys = Object.keys(DATA);
		if (!keys.length) {
			return;
		}
		/* ==========================
		   CART PAGE – BLOCK CART
		========================== */
		const cartRows = document.querySelectorAll('.wc-block-cart-items__row');
		cartRows.forEach((row, index) => {
			if (!keys[index] || !DATA[keys[index]] || !DATA[keys[index]].price) {
				return;
			}
			const itemData = DATA[keys[index]];
			/* --- Product price (left) --- */
			const priceWrap = row.querySelector('.wc-block-cart-item__prices');
			if (
				priceWrap &&
				!priceWrap.querySelector('.alg-original-price')
			) {
				priceWrap.insertAdjacentHTML(
					'beforeend',
					`<div class="alg-original-price alg-cpp-cart-price">
						${itemData.price}
					</div>`
				);
			}
			/* --- Subtotal (right) --- */
			const subtotalWrap = row.querySelector(
				'.wc-block-cart-item__total .wc-block-components-product-price'
			);
			if (
				subtotalWrap &&
				!subtotalWrap.querySelector('.alg-original-price')
			) {
				subtotalWrap.insertAdjacentHTML(
					'beforeend',
					`<div class="alg-original-price alg-cpp-cart-subtotal">
						${itemData.price}
					</div>`
				);
			}
		});
		/* ==========================
		   CHECKOUT PAGE – BLOCKS
		========================== */
		const checkoutItems = document.querySelectorAll(
			'.wc-block-components-order-summary-item'
		);
		checkoutItems.forEach((item, index) => {
			if (!keys[index] || !DATA[keys[index]] || !DATA[keys[index]].price) {
				return;
			}
			const itemData = DATA[keys[index]];
			const priceWrap =
				item.querySelector('.wc-block-components-product-price') ||
				item.querySelector('.wc-block-components-order-summary-item__price');
			if (!priceWrap || priceWrap.querySelector('.alg-original-price')) {
				return;
			}
			priceWrap.insertAdjacentHTML(
				'beforeend',
				`<div class="alg-original-price alg-cpp-checkout-price">
					${itemData.price}
				</div>`
			);
		});
	};
	// Initial render
	renderOriginalPrices();
	// Re-render on Blocks updates (qty change, remove, coupon, refresh)
	const observer = new MutationObserver(renderOriginalPrices);
	observer.observe(document.body, {
		childList: true,
		subtree: true,
	});
})();