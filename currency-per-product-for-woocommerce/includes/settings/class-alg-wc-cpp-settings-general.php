<?php
/**
 * Currency per Product for WooCommerce - General Section Settings
 *
 * @version 1.4.1
 * @since   1.0.0
 * @author  Tyche Softwares
 *
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_CPP_Settings_General' ) ) :

	/**
	 * Main class Alg_WC_CPP_Settings_General
	 *
	 * @class Alg_WC_CPP_Settings_General
	 */
	class Alg_WC_CPP_Settings_General extends Alg_WC_CPP_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->id   = '';
			$this->desc = __( 'General', 'currency-per-product-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * Get General settings.
		 *
		 * @version 1.4.1
		 * @since   1.0.0
		 */
		public function get_settings() {

			$main_settings = array(
				array(
					'title' => __( 'Currency per Product Options', 'currency-per-product-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_options',
				),
				array(
					'title'    => __( 'Currency per Product for WooCommerce', 'currency-per-product-for-woocommerce' ),
					'desc'     => '<strong>' . __( 'Enable plugin', 'currency-per-product-for-woocommerce' ) . '</strong>',
					'desc_tip' => __( 'Set and display prices for WooCommerce products in different currencies.', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_enabled',
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpp_options',
				),
			);

			$general_settings = array(
				array(
					'title' => __( 'General Options', 'currency-per-product-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_general_options',
				),
				array(
					'title'    => __( 'Currency reports', 'currency-per-product-for-woocommerce' ),
					'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
					'desc_tip' => __( 'This will add currency selection to admin bar in reports.', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_currency_reports_enabled',
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'title'    => __( 'Custom currency symbol', 'currency-per-product-for-woocommerce' ),
					'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
					'desc_tip' => __( 'This will change currency symbol (frontend & backend) according to the template below.', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_custom_currency_symbol_enabled',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'desc'           => __( 'Currency symbol template.', 'currency-per-product-for-woocommerce' ) . ' ' .
						/* translators: %s: %currency_code%, %currency_symbol% */
						sprintf( __( 'Replaced values: %s.', 'currency-per-product-for-woocommerce' ), '<code>%currency_code%</code>, <code>%currency_symbol%</code>' ),
					'id'             => 'alg_wc_cpp_custom_currency_symbol_template',
					'default'        => '%currency_code%%currency_symbol%',
					'type'           => 'textarea',
					'css'            => 'width:100%',
					'alg_wc_cpp_raw' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpp_general_options',
				),
			);

			return array_merge( $main_settings, $general_settings );
		}

	}

endif;

return new Alg_WC_CPP_Settings_General();
