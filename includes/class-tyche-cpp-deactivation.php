<?php
/**
 * Currency Per Product for WooCommerce Pro - Deactivation Class
 *
 * @version 1.1.7
 * @since   1.1.3
 * @author  Tyche Softwares
 * @package Currency Per Product Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tyche_CPP_Deactivation' ) ) {

	/** Declaration of Class */
	class Tyche_CPP_Deactivation {

		/** Constructor */
		public function __construct() {
			require_once __DIR__ . '/tyche/components/plugin-deactivation/class-tyche-plugin-deactivation.php';
			new Tyche_Plugin_Deactivation(
				array(
					'plugin_name'       => 'Currency per Product for WooCommerce',
					'plugin_base'       => 'currency-per-product-for-woocommerce/currency-per-product-for-woocommerce.php',
					'script_file'       => CPP_PLUGIN_URL . '/includes/tyche/assets/js/plugin-deactivation.js',
					'plugin_short_name' => 'cpp_lite',
					'version'           => CPP_VERSION,
					'plugin_locale'     => 'currency-per-product-for-woocommerce',
				)
			);
		}
	}

	// Initialize the license class.
	new Tyche_CPP_Deactivation();
}
