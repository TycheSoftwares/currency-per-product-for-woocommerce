<?php
/**
 * Currency Per Product for WooCommerce Pro - Tracking Class
 *
 * @version 1.1.7
 * @since   1.1.3
 * @author  Tyche Softwares
 * @package Currency Per Product
 */

namespace Tyche\CPP\Tracking;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Tyche\CPP\Tracking\Tyche_Plugin_Tracking;

if ( ! class_exists( 'Tyche_CPP_Tracking' ) ) {

	/** Declaration of Class */
	class Tyche_CPP_Tracking {

		/** Constructor */
		public function __construct() {
			require_once __DIR__ . '/tyche/components/plugin-tracking/class-tyche-plugin-tracking.php';
			require_once __DIR__ . '/tyche/components/plugin-tracking/class-tyche-tracking-api.php';
			new Tyche_Plugin_Tracking(
				array(
					'plugin_name'       => 'Currency per Product for WooCommerce',
					'plugin_locale'     => 'currency-per-product-for-woocommerce',
					'plugin_short_name' => 'cpp_lite',
					'version'           => CPP_VERSION,
					'blog_link'         => 'https://www.tychesoftwares.com/docs/woocommerce-currency-per-product/currency-usage-tracking/',
				)
			);

			if ( is_admin() ) {
				require_once __DIR__ . '/tyche/components/plugin-tracking/class-tyche-cpp-plugin-tracking.php';
			}
		}
	}

	// Initialize the license class.
	new Tyche_CPP_Tracking();
}
