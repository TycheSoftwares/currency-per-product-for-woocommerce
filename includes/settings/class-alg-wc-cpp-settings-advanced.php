<?php
/**
 * Currency per Product for WooCommerce - Advanced Section Settings
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

if ( ! class_exists( 'Alg_WC_CPP_Settings_Advanced' ) ) {

	/**
	 * Main Alg_WC_CPP_Settings_Advanced class.
	 *
	 * @class Alg_WC_CPP_Settings_Advanced
	 */
	class Alg_WC_CPP_Settings_Advanced extends Alg_WC_CPP_Settings_Section {

		/**
		 * ID.
		 *
		 * @var   string
		 * @since 1.6.0
		 */
		public $id = '';
		/**
		 * Description.
		 *
		 * @var   string
		 * @since 1.6.0
		 */
		public $desc = '';
		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function __construct() {
			$this->id = 'advanced';
			add_action( 'init', array( &$this, 'add_cpp_desc_advanced' ) );
			parent::__construct();
		}

		/**
		 * Add desc to setting page.
		 */
		public function add_cpp_desc_advanced() {
			$this->desc = __( 'Advanced', 'currency-per-product-for-woocommerce' );
		}

		/**
		 * Get the advanced settings.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 * @todo    [dev] maybe better solution for "Fix mini cart"
		 * @todo    [dev] maybe add admin notice if `DISABLE_WP_CRON` is set to `true`
		 * @todo    [dev] maybe set "Save products prices" to "yes" by default
		 */
		public function get_settings() {

			$_time = get_option( 'alg_wc_cpp_calculate_all_products_prices_cron_time', 0 );
			return array(
				array(
					'title' => esc_html_e( 'Advanced Options', 'currency-per-product-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_advanced_options',
				),
				array(
					'title'    => __( 'Fix mini cart', 'currency-per-product-for-woocommerce' ),
					'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
					'desc_tip' => __( 'Enable this option if you have issues with currency symbol in mini cart. It will recalculate cart totals on each page load.', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_fix_mini_cart',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'title'    => __( 'Sorting by converted price', 'currency-per-product-for-woocommerce' ),
					'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
					'desc_tip' => __( 'Enable this option if you want to use converted prices in WooCommerce "Sort by price" sorting.', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_sort_by_converted_price',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'title'    => __( 'Filtering by converted price', 'currency-per-product-for-woocommerce' ),
					'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
					'desc_tip' => __( 'Enable this option if you want to use converted prices in WooCommerce "Filter Products by Price" widget.', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_filter_by_converted_price',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'title'    => __( 'Save products prices', 'currency-per-product-for-woocommerce' ),
					'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
					'desc_tip' => __( 'Enable this option if you have compatibility issues with other plugins. It will ensure that prices are converted only once.', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_save_products_prices',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpp_advanced_options',
				),
				array(
					'title' => __( 'Tools', 'currency-per-product-for-woocommerce' ),
					'type'  => 'alg_wc_cpp_title',
					'id'    => 'alg_wc_cpp_tools_options',
					'desc'  => '<table class="widefat striped"><tr><th>' . __( 'Tool', 'currency-per-product-for-woocommerce' ) . '</th><th>' . __( 'Description', 'currency-per-product-for-woocommerce' ) . '</th></tr><tr>' .
						implode(
							'</tr><tr>',
							array(
								sprintf(
									'<td><a class="button" href="' . wp_nonce_url(
										add_query_arg(
											'alg_wc_cpp_calculate_all_products_prices',
											true,
											admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cpp&section=advanced' )
										),
										'alg_wc_cpp_calculate_all_products_prices',
										'alg_wc_cpp_calculate_all_products_prices_nonce'
									) . '">' .
									__( 'Re-calculate prices', 'currency-per-product-for-woocommerce' ) . '</a></td>' .
									'<td><em>' . __( 'Re-calculates (i.e. converts) and saves all products prices for use in "Sort by price" sorting and "Filter Products by Price" widget.', 'currency-per-product-for-woocommerce' ) . ' ' .
									__( 'This is also done automatically: a) when product is saved, b) on scheduled exchange rates updates, and c) periodically.', 'currency-per-product-for-woocommerce' ) . ' ' .
									__( 'However you can also do this manually by pressing the button.', 'currency-per-product-for-woocommerce' ) . '</em></td>',
									( ( 'yes' === get_option( 'alg_wc_cpp_sort_by_converted_price', 'no' ) || 'yes' === get_option( 'alg_wc_cpp_filter_by_converted_price', 'no' ) ) &&
											0 !== $_time ?
												/* translators: %s: difference from $_time until current_time, e.g "1 hour", "5 mins", "2 days" */
												sprintf( ' (' . __( '%s until next update', 'currency-per-product-for-woocommerce' ) . ')', human_time_diff( $_time ) )
												: '' )
								),
								'<td><a class="button" style="color:red;" onclick="return confirm(\'' . __( 'Are you sure?', 'currency-per-product-for-woocommerce' ) . '\')" href="' . wp_nonce_url(
									add_query_arg(
										'alg_wc_cpp_delete_plugin_data',
										true,
										admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cpp&section=advanced' )
									),
									'alg_wc_cpp_delete_plugin_data',
									'alg_wc_cpp_delete_plugin_data_nonce'
								) . '">' .
									__( 'Delete all plugin\'s data', 'currency-per-product-for-woocommerce' ) . '</a></td>' .
							'<td><em>' . __( 'This will delete all plugin\'s data, i.e. all plugin\'s options and products meta.', 'currency-per-product-for-woocommerce' ) . ' ' .
								__( 'There is no undo action, so please be careful.', 'currency-per-product-for-woocommerce' ) . '</em></td>',
							)
						) .
						'</tr></table>',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpp_tools_options',
				),
			);
		}
	}
}

return new Alg_WC_CPP_Settings_Advanced();
