<?php //phpcs:ignore
/**
 * Plugin Name: Currency per Product for WooCommerce
 * Plugin URI: https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce
 * Description: Set and display prices for WooCommerce products in different currencies.
 * Version: 2.0.0
 * Author: Tyche Softwares
 * Author URI: https://www.tychesoftwares.com
 * Text Domain: currency-per-product-for-woocommerce
 * Domain Path: /languages
 * Copyright: � 2022 Tyche Softwares
 * WC tested up to: 10.8.1
 * Requires PHP: 7.4
 * Tested up to: 7.0.0
 * WC requires at least: 5.0
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins: woocommerce
 *
 * @package Currency per Product for WooCommerce Pro
 */

namespace Tyche\CPP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'CPP_FILE' ) ) {
	define( 'CPP_FILE', __FILE__ );
}

// Include the Product Input Fields class.
if ( ! class_exists( 'Currency_Per_Product', false ) ) {
	include_once dirname( CPP_FILE ) . '/includes/class-currency-per-product.php';
}

/**
 * Returns the instance of CPP.
 *
 * @since  1.0
 */
function CPP() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return Currency_Per_Product::instance();
}

Currency_Per_Product::bootstrap();
