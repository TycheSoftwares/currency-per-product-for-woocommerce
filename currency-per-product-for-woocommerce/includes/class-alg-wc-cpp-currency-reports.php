<?php
/**
 * Currency per Product for WooCommerce - Currency Reports
 *
 * @version  1.1.0
 * @since    1.1.0
 * @author   Tyche Softwares
 *
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Alg_WC_CPP_Currency_Reports' ) ) :

	/**
	 * Main Alg_WC_CPP_Currency_Reports Class
	 *
	 * @class   Alg_WC_CPP_Currency_Reports
	 */
	class Alg_WC_CPP_Currency_Reports {

		/**
		 * Constructor.
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 * @todo    [feature] reports with currency conversions (i.e. merged)
		 */
		public function __construct() {
			add_filter( 'woocommerce_reports_get_order_report_data_args', array( $this, 'filter_reports' ), PHP_INT_MAX, 1 );
			add_filter( 'woocommerce_currency', array( $this, 'change_reports_currency_code' ), PHP_INT_MAX, 1 );
			add_action( 'admin_bar_menu', array( $this, 'add_reports_currency_to_admin_bar' ), PHP_INT_MAX );
		}

		/**
		 * Get node meta title.
		 *
		 * @version  1.1.0
		 * @since    1.1.0
		 *
		 * @param string $code Code.
		 */
		public function get_node_meta_title( $code ) {
			return ( 'merge' === $code ?
			__( 'Merge all currencies', 'currency-per-product-for-woocommerce' ) :
			/* translators: %s: Show reports in which form */
			sprintf( __( 'Show reports only in %s', 'currency-per-product-for-woocommerce' ), $code )
			);
		}

		/**
		 * Get node title.
		 *
		 * @version  1.1.0
		 * @since    1.1.0
		 *
		 * @param string $code Code.
		 */
		public function get_node_title( $code ) {
			$name = ( 'merge' === $code ? __( 'All currencies', 'currency-per-product-for-woocommerce' ) : $code );
			/* translators: %s: Currency */
			return sprintf( __( 'Reports currency: %s', 'currency-per-product-for-woocommerce' ), $name );
		}

		/**
		 * Add reports currency to admin bar.
		 *
		 * @version  1.1.0
		 * @since    1.1.0
		 *
		 * @param object $wp_admin_bar WP Admin bar object.
		 */
		public function add_reports_currency_to_admin_bar( $wp_admin_bar ) {
			if ( isset( $_GET['page'] ) && 'wc-reports' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
				// Parent.
				$base_currency = get_option( 'woocommerce_currency' );
				$_current_code = isset( $_GET['currency'] ) ? sanitize_text_field( wp_unslash( $_GET['currency'] ) ) : $base_currency;
				$parent        = 'alg_wc_cpp_reports_currency_select';
				$args          = array(
					'parent' => false,
					'id'     => $parent,
					'title'  => $this->get_node_title( $_current_code ),
					'href'   => false,
					'meta'   => array( 'title' => $this->get_node_meta_title( $_current_code ) ),
				);
				$wp_admin_bar->add_node( $args );
				// Children.
				$currencies                   = array();
				$currencies[ $_current_code ] = $_current_code;
				$currencies[ $base_currency ] = $base_currency;
				$wc_currency                  = get_woocommerce_currency();
				if ( '' !== $wc_currency ) {
					$currencies[ $wc_currency ] = $wc_currency;
				}
				$total_number = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
				for ( $i = 1; $i <= $total_number; $i++ ) {
					$currencies[ get_option( 'alg_wc_cpp_currency_' . $i, $base_currency ) ] = get_option( 'alg_wc_cpp_currency_' . $i, $base_currency );
				}
				asort( $currencies );
				$currencies['merge'] = __( 'Merge all', 'currency-per-product-for-woocommerce' );
				foreach ( $currencies as $code => $name ) {
					$args = array(
						'parent' => $parent,
						'id'     => $parent . '_' . $code,
						'title'  => $name,
						'href'   => add_query_arg( 'currency', $code ),
						'meta'   => array( 'title' => $this->get_node_meta_title( $code ) ),
					);
					$wp_admin_bar->add_node( $args );
				}
			}
		}

		/**
		 * Change reports currency code.
		 *
		 * @version  1.1.0
		 * @since    1.1.0
		 *
		 * @param string $currency Currency.
		 */
		public function change_reports_currency_code( $currency ) {
			if ( isset( $_GET['page'] ) && 'wc-reports' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) && isset( $_GET['currency'] ) ) {
				return ( 'merge' === $_GET['currency'] ? '' : sanitize_text_field( wp_unslash( $_GET['currency'] ) ) );
			}
			return $currency;
		}

		/**
		 * Filters for reports.
		 *
		 * @version  1.1.0
		 * @since    1.1.0
		 *
		 * @param array $args Arguments array.
		 */
		public function filter_reports( $args ) {
			if ( isset( $_GET['currency'] ) && 'merge' === $_GET['currency'] ) {
				return $args;
			}
			$args['where_meta'] = array(
				array(
					'meta_key'   => '_order_currency',
					'meta_value' => ( isset( $_GET['currency'] ) ? sanitize_text_field( wp_unslash( $_GET['currency'] ) ) : get_option( 'woocommerce_currency' ) ),
					'operator'   => '=',
				),
			);
			return $args;
		}
	}

endif;

return new Alg_WC_CPP_Currency_Reports();
