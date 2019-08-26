<?php
/**
 * Currency per Product for WooCommerce - Section Settings
 *
 * @version 1.4.0
 * @since   1.0.0
 * @author  Tyche Softwares
 *
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_CPP_Settings_Section' ) ) :

	/**
	 * Main class Alg_WC_CPP_Settings_Section
	 *
	 * @class Alg_WC_CPP_Settings_Section
	 */
	class Alg_WC_CPP_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 */
		public function __construct() {
			add_filter( 'woocommerce_get_sections_alg_wc_cpp', array( $this, 'settings_section' ) );
			add_filter( 'woocommerce_get_settings_alg_wc_cpp_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
		}

		/**
		 * Settings section.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $sections Sections array.
		 */
		public function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;
			return $sections;
		}

	}

endif;
