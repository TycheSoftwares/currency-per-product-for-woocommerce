=== Currency per Product for WooCommerce ===
Contributors: tychesoftwares
Tags: woocommerce, woo commerce, currency per product, currency, multicurrency
Requires at least: 4.4
Tested up to: 5.1.1
Stable tag: 1.4.5
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Set and display prices for WooCommerce products in different currencies.

== Description ==

**Currency per Product for WooCommerce** plugin lets you set and display prices for WooCommerce products in different currencies.

There is a number of scenarios that can be implemented with this plugin:

* Set WooCommerce product prices in different currencies and display it in shop in **different currencies**.
* Set WooCommerce product prices in different currencies, but display it in shop in **one currency, converted with the exchange rates** (i.e. "multicurrency product base price").
* Set WooCommerce product prices in different currencies, display it in shop in **different currencies**, but convert it to **one currency in cart and checkout**.

= Shop Behaviour Options =

* Show prices in **different currencies** (and set cart and checkout behaviour separately).
* Convert to **shop default currency** (including cart and checkout).
* Add **original (i.e. not converted) price** display to shop pages.

= Cart and Checkout Behaviour Options =

* **Convert** to **shop default** currency.
* **Leave** product currency (allow only **one product** to be added to cart).
* **Leave** product currency (allow only **same currency products** to be added to cart).
* **Convert** to currency of **last product** in cart.
* **Convert** to currency of **first product** in cart.

= Currencies Options =

* Add **two** currencies in free version and **unlimited** number of currencies in [Pro version](https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=wprepo&utm_medium=link&utm_campaign=CurrencyProductLite/).
* Optional currency per **product authors**.
* Optional currency per **product authors user roles**.
* Optional currency per **product categories**.
* Optional currency per **product tags**.

= Currency Exchange Rates Options =

* **Manual** currency exchange rates in free version and **automatic** currency exchange rates in [Pro version](https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=wprepo&utm_medium=link&utm_campaign=CurrencyProductLite/) (including customizable update **rate** and currency exchange rates **server**).

= More Options =

* Add currency selection to admin bar in WooCommerce **admin reports**.
* Enable use of converted prices in WooCommerce **"Sort by price" sorting**.
* Enable use of converted prices in WooCommerce **"Filter Products by Price" widget**.

= Feedback =
* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* Please visit the [plugin page](https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=wprepo&utm_medium=link&utm_campaign=CurrencyProductLite/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Currency per Product".

== Changelog ==

= 1.4.5 - 25/03/2019 =
* Fix - Prices for the Bookable products from WooCommerce Bookings plugin were not converted to the default currency when 'Convert to Shop default currency (and set cart and checkout behavior separately)' option is selected for Shop Behaviour setting. This is fixed now. 
* Fix - The prices for the selected currency for the bookable products were shown as 0 on the Shop page when 'Add original price in shop' option is checked. This is fixed now. 
* Fix - A fatal error was shown on the Shop page for Variable products when visited as Wholesale customer added from Wholesale Customers For Woo plugin. This is fixed now. 

= 1.4.4 - 16/11/2018 =
* Version Changed. 

= 1.4.3 - 31/10/2018 =
* Compatibility with WooCommerce 3.5.0 tested.

= 1.4.2 - 23/09/2018 =
* Fix - Behaviour - Add original price in shop - Variable products fixed.

= 1.4.1 - 19/09/2018 =
* Feature - General - "Custom currency symbol" options added.
* Fix - Variations currency in backend fixed (in case if "Convert to shop default currency (including cart and checkout)" is selected).
* Fix - Exchange Rates - "ECB" and "TCMB" fixed on servers with `allow_url_fopen` option disabled.
* Dev - Minor code refactoring.

= 1.4.0 - 13/08/2018 =
* Feature - Behaviour - Shop Behaviour Options - "Add original price in shop" options added.
* Feature - Advanced - "Sorting by converted price" option added ("Sort by price" sorting).
* Feature - Advanced - "Filtering by converted price" option added ("Filter Products by Price" widget).
* Dev - Exchange Rates - "Yahoo" server removed (as it's discontinued).
* Dev - Exchange Rates - Step decreased (to 12 decimals) in admin settings.
* Dev - Raw input is now allowed in some plugin options.
* Dev - Admin settings divided in separate sections, restyled and descriptions updated.
* Dev - Major code refactoring; plugin folders structure changed; `version_updated()` function added.

= 1.3.0 - 09/08/2018 =
* Fix - Additional checks added to `get_currency_exchange_rate()` function to prevent division by zero and "non-numeric value encountered" notices.
* Dev - Advanced Options - "Save products prices" option added.
* Dev - Exchange rates updates - "Free Currency Converter API (free.currencyconverterapi.com)" server added.
* Dev - Exchange rates updates - "European Central Bank (ECB)" server set as the default option.
* Dev - Admin settings restyled.
* Dev - Code refactoring.

= 1.2.1 - 19/06/2018 =
* Dev - Core - `change_currency_code()` function rewritten.
* Dev - "WC tested up to" added to plugin header.
* Dev - Plugin URI updated to wpfactory.com.
* Dev - Admin settings descriptions updated.

= 1.2.0 - 06/12/2017 =
* Dev - WooCommerce v3.2.0 compatibility - Admin settings `select` type options fixed.
* Dev - Yahoo exchange rates server URL updated.
* Dev - Admin settings - Minor restyling.
* Dev - `includes()` - `settings` are saved as object property now.
* Dev - POT file added.

= 1.1.0 - 27/09/2017 =
* Dev - "Shop Behaviour" option added.
* Dev - Advanced Options - "Fix mini cart" option added.
* Dev - Advanced Options - "Currency Reports" option added.

= 1.0.0 - 20/08/2017 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
