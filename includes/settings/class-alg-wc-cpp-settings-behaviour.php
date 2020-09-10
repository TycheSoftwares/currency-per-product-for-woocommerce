<?php
/**
 * Currency per Product for WooCommerce - Behaviour Section Settings
 *
 * @version 1.4.0
 * @since   1.4.0
 * @author  Tyche Softwares
 *
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_CPP_Settings_Behaviour' ) ) :

	/**
	 * Main Alg_WC_CPP_Settings_Behaviour class
	 *
	 * @class Alg_WC_CPP_Settings_Behaviour
	 */
	class Alg_WC_CPP_Settings_Behaviour extends Alg_WC_CPP_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function __construct() {
			$this->id   = 'behaviour';
			$this->desc = __( 'Behaviour', 'currency-per-product-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * Get Behaviour settings.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 * @todo    [feature] maybe add option to remove "original price in shop" from admin products list
		 * @todo    [feature] maybe add `%price_raw_formatted%` replaced value to "Original price template"
		 * @todo    [feature] maybe add "Add original price in cart and checkout" option
		 */
		public function get_settings() {

			$shop_settings = array(
				array(
					'title' => __( 'Shop Behaviour Options', 'currency-per-product-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_shop_options',
				),
				array(
					'title'   => __( 'Shop behaviour', 'currency-per-product-for-woocommerce' ),
					'id'      => 'alg_wc_cpp_shop_behaviour',
					'default' => 'show_in_different',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => array(
						'show_in_different'    => __( 'Show prices in different currencies (and set cart and checkout behaviour separately)', 'currency-per-product-for-woocommerce' ),
						'convert_shop_default' => __( 'Convert to shop default currency (including cart and checkout)', 'currency-per-product-for-woocommerce' ),
					),
				),
				array(
					'title'    => __( 'Add original price in shop', 'currency-per-product-for-woocommerce' ),
					'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
					'desc_tip' => __( 'This will add original (i.e. not converted) price display to shop pages (and admin products list), in case if product price was converted.', 'currency-per-product-for-woocommerce' ) . ' ' .
						__( 'Ignored, if "Shop behaviour" option is set to "Show prices in different currencies ...".', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_original_price_in_shop_enabled',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'desc'           => __( 'Original price template', 'currency-per-product-for-woocommerce' ) . '. ' .
						sprintf(
							/* translators: %s: %price%, %price_raw%, %currency_code% */
							__( 'Replaced values: %s.', 'currency-per-product-for-woocommerce' ),
							'<code>' . implode( '</code>, <code>', array( '%price%', '%price_raw%', '%currency_code%' ) ) . '</code>'
						),
					'id'             => 'alg_wc_cpp_original_price_in_shop_template',
					'default'        => '<br>%price%',
					'type'           => 'textarea',
					'css'            => 'width:100%',
					'alg_wc_cpp_raw' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpp_shop_options',
				),
			);

			$cart_settings = array(
				array(
					'title' => __( 'Cart and Checkout Behaviour Options', 'currency-per-product-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_cart_options',
				),
				array(
					'title'   => __( 'Cart and checkout behaviour', 'currency-per-product-for-woocommerce' ),
					'desc'    => '<p>' . __( 'This option is ignored and always set to "Convert to shop default currency", if you selected "Convert to shop default currency" as "Shop Behaviour" option.', 'currency-per-product-for-woocommerce' ) . '</p>',
					'id'      => 'alg_wc_cpp_cart_checkout',
					'default' => 'convert_shop_default',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => array(
						'convert_shop_default'  => __( 'Convert to shop default currency', 'currency-per-product-for-woocommerce' ),
						'leave_one_product'     => __( 'Leave product currency (allow only one product to be added to cart)', 'currency-per-product-for-woocommerce' ),
						'leave_same_currency'   => __( 'Leave product currency (allow only same currency products to be added to cart)', 'currency-per-product-for-woocommerce' ),
						'convert_last_product'  => __( 'Convert to currency of last product in cart', 'currency-per-product-for-woocommerce' ),
						'convert_first_product' => __( 'Convert to currency of first product in cart', 'currency-per-product-for-woocommerce' ),
					),
				),
				array(
					'title'          => __( 'Message', 'currency-per-product-for-woocommerce' ) . ': ' . __( 'Leave product currency (allow only one product to be added to cart)', 'currency-per-product-for-woocommerce' ),
					'id'             => 'alg_wc_cpp_cart_checkout_leave_one_product',
					'default'        => __( 'Only one product can be added to the cart. Clear the cart or finish the order, before adding another product to the cart.', 'currency-per-product-for-woocommerce' ),
					'type'           => 'textarea',
					'css'            => 'width:100%',
					'alg_wc_cpp_raw' => true,
				),
				array(
					'title'          => __( 'Message', 'currency-per-product-for-woocommerce' ) . ': ' . __( 'Leave product currency (allow only same currency products to be added to cart)', 'currency-per-product-for-woocommerce' ),
					'id'             => 'alg_wc_cpp_cart_checkout_leave_same_currency',
					'default'        => __( 'Only products with same currency can be added to the cart. Clear the cart or finish the order, before adding products with another currency to the cart.', 'currency-per-product-for-woocommerce' ),
					'type'           => 'textarea',
					'css'            => 'width:100%',
					'alg_wc_cpp_raw' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpp_cart_options',
				),
			);

			return array_merge( $shop_settings, $cart_settings );
		}

	}

endif;

return new Alg_WC_CPP_Settings_Behaviour();
