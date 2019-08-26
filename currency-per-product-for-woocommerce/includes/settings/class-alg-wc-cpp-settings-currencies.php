<?php
/**
 * Currency per Product for WooCommerce - Currencies Section Settings
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

if ( ! class_exists( 'Alg_WC_CPP_Settings_Currencies' ) ) :

	/**
	 * Main class Alg_WC_CPP_Settings_Currencies
	 *
	 * @class Alg_WC_CPP_Settings_Currencies
	 */
	class Alg_WC_CPP_Settings_Currencies extends Alg_WC_CPP_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function __construct() {
			$this->id   = 'currencies';
			$this->desc = __( 'Currencies', 'currency-per-product-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * Get currency settings.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function get_settings() {

			$currencies_general_settings = array(
				array(
					'title' => __( 'Currencies Options', 'currency-per-product-for-woocommerce' ),
					'desc'  => __( 'Click "Save changes" button after changing options in this subsection to see new settings fields.', 'currency-per-product-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_currencies_general_options',
				),
				array(
					'title'             => __( 'Total currencies', 'currency-per-product-for-woocommerce' ),
					'id'                => 'alg_wc_cpp_total_number',
					'default'           => 1,
					'type'              => 'number',
					'desc'              => apply_filters(
						'alg_wc_cpp',
						'<p>' . sprintf(
							'To add more than one <strong>additional</strong> currency, please get <a target="_blank" href="%s">Currency per Product for WooCommerce Pro</a> plugin.',
							'https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=cppupgradetopro&utm_medium=link&utm_campaign=CurrencePerProductLite'
						) . '</p>',
						'settings_button'
					),
					'custom_attributes' => apply_filters( 'alg_wc_cpp', array( 'readonly' => 'readonly' ), 'settings_array' ),
				),
				array(
					'title'         => __( 'Additional options', 'currency-per-product-for-woocommerce' ),
					'desc'          => sprintf(
						/* translators: %s: set currency per product author */
						__( 'Currency per %s', 'currency-per-product-for-woocommerce' ),
						'<strong>' . __( 'product authors', 'currency-per-product-for-woocommerce' ) . '</strong>'
					),
					'id'            => 'alg_wc_cpp_by_users_enabled',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
				),
				array(
					'desc'          => sprintf(
						/* translators: %s: set currency per author user role */
						__( 'Currency per %s', 'currency-per-product-for-woocommerce' ),
						'<strong>' . __( 'product authors user roles', 'currency-per-product-for-woocommerce' ) . '</strong>'
					),
					'id'            => 'alg_wc_cpp_by_user_roles_enabled',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => '',
				),
				array(
					'desc'          => sprintf(
						/* translators: %s: set currency per product category */
						__( 'Currency per %s', 'currency-per-product-for-woocommerce' ),
						'<strong>' . __( 'product categories', 'currency-per-product-for-woocommerce' ) . '</strong>'
					),
					'id'            => 'alg_wc_cpp_by_product_cats_enabled',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => '',
				),
				array(
					'desc'          => sprintf(
						/* translators: %s: set currency per product tags */
						__( 'Currency per %s', 'currency-per-product-for-woocommerce' ),
						'<strong>' . __( 'product tags', 'currency-per-product-for-woocommerce' ) . '</strong>'
					),
					'id'            => 'alg_wc_cpp_by_product_tags_enabled',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'end',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpp_currencies_general_options',
				),
			);

			$currency_from  = get_woocommerce_currency();
			$all_currencies = get_woocommerce_currencies();
			if ( get_option( 'alg_wc_cpp_currency_0', $currency_from ) !== $currency_from ) {
				update_option( 'alg_wc_cpp_currency_0', $currency_from );
			}
			$currencies_settings     = array(
				array(
					'title' => __( 'Currencies', 'currency-per-product-for-woocommerce' ),
					'desc'  => sprintf(
						/* translators: %s: currency code */
						__( 'Your shop base currency %s will be automatically added to the currencies list on product edit page, so you <strong>don\'t need</strong> to add it to the list below.', 'currency-per-product-for-woocommerce' ),
						'<code>' . $currency_from . '</code>'
					) . '<br>' .
						sprintf(
							/* translators: %s: All Currencies for WooCommerce plugin */
							__( 'If you are missing some currencies (or cryptocurrencies) in the currencies list below - we suggest checking %s plugin.', 'currency-per-product-for-woocommerce' ),
							'<a href="https://wordpress.org/plugins/woocommerce-all-currencies/" target="_blank">' .
							'<strong>' . __( 'free', 'currency-per-product-for-woocommerce' ) . '</strong> ' . __( 'All Currencies for WooCommerce', 'currency-per-product-for-woocommerce' ) . '</a>'
						),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_currencies_options',
				),
				array(
					// phpcs:ignore
					'title'             => __( 'Currency', 'currency-per-product-for-woocommerce' ) . ' #' . '0' . ' [' . $currency_from . ']',
					'id'                => 'alg_wc_cpp_currency_0',
					'default'           => $currency_from,
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'options'           => $all_currencies,
					'custom_attributes' => array( 'disabled' => 'disabled' ),
				),
			);
			$users_as_options        = ( 'yes' === get_option( 'alg_wc_cpp_by_users_enabled', 'no' ) ? alg_wc_cpp_get_users_as_options() : false );
			$user_roles_as_options   = ( 'yes' === get_option( 'alg_wc_cpp_by_user_roles_enabled', 'no' ) ? alg_wc_cpp_get_user_roles_options() : false );
			$product_cats_as_options = ( 'yes' === get_option( 'alg_wc_cpp_by_product_cats_enabled', 'no' ) ? alg_wc_cpp_get_terms( 'product_cat' ) : false );
			$product_tags_as_options = ( 'yes' === get_option( 'alg_wc_cpp_by_product_tags_enabled', 'no' ) ? alg_wc_cpp_get_terms( 'product_tag' ) : false );
			$total_number            = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
			for ( $i = 1; $i <= $total_number; $i++ ) {
				$currency_to         = get_option( 'alg_wc_cpp_currency_' . $i, $currency_from );
				$currencies_settings = array_merge(
					$currencies_settings,
					array(
						array(
							'title'   => __( 'Currency', 'currency-per-product-for-woocommerce' ) . ' #' . $i . ' [' . $currency_to . ']',
							'id'      => 'alg_wc_cpp_currency_' . $i,
							'default' => $currency_from,
							'type'    => 'select',
							'class'   => 'wc-enhanced-select',
							'options' => $all_currencies,
						),
					)
				);
				if ( 'yes' === get_option( 'alg_wc_cpp_by_users_enabled', 'no' ) ) {
					$currencies_settings = array_merge(
						$currencies_settings,
						array(
							array(
								'desc'    => __( 'Product authors', 'currency-per-product-for-woocommerce' ),
								'id'      => 'alg_wc_cpp_users_' . $i,
								'default' => '',
								'type'    => 'multiselect',
								'options' => $users_as_options,
								'class'   => 'chosen_select',
							),
						)
					);
				}
				if ( 'yes' === get_option( 'alg_wc_cpp_by_user_roles_enabled', 'no' ) ) {
					$currencies_settings = array_merge(
						$currencies_settings,
						array(
							array(
								'desc'    => __( 'Product authors user roles', 'currency-per-product-for-woocommerce' ),
								'id'      => 'alg_wc_cpp_user_roles_' . $i,
								'default' => '',
								'type'    => 'multiselect',
								'options' => $user_roles_as_options,
								'class'   => 'chosen_select',
							),
						)
					);
				}
				if ( 'yes' === get_option( 'alg_wc_cpp_by_product_cats_enabled', 'no' ) ) {
					$currencies_settings = array_merge(
						$currencies_settings,
						array(
							array(
								'desc'    => __( 'Product categories', 'currency-per-product-for-woocommerce' ),
								'id'      => 'alg_wc_cpp_product_cats_' . $i,
								'default' => '',
								'type'    => 'multiselect',
								'options' => $product_cats_as_options,
								'class'   => 'chosen_select',
							),
						)
					);
				}
				if ( 'yes' === get_option( 'alg_wc_cpp_by_product_tags_enabled', 'no' ) ) {
					$currencies_settings = array_merge(
						$currencies_settings,
						array(
							array(
								'desc'    => __( 'Product tags', 'currency-per-product-for-woocommerce' ),
								'id'      => 'alg_wc_cpp_product_tags_' . $i,
								'default' => '',
								'type'    => 'multiselect',
								'options' => $product_tags_as_options,
								'class'   => 'chosen_select',
							),
						)
					);
				}
			}
			$currencies_settings = array_merge(
				$currencies_settings,
				array(
					array(
						'type' => 'sectionend',
						'id'   => 'alg_wc_cpp_currencies_options',
					),
				)
			);

			return array_merge( $currencies_general_settings, $currencies_settings );
		}
	}

endif;

return new Alg_WC_CPP_Settings_Currencies();
