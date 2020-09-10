<?php
/**
 * Currency per Product for WooCommerce - Exchange Rates Section Settings
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

if ( ! class_exists( 'Alg_WC_CPP_Settings_Exchange_Rates' ) ) :

	/**
	 * Main class Alg_WC_CPP_Settings_Exchange_Rates.
	 *
	 * @class Alg_WC_CPP_Settings_Exchange_Rates
	 */
	class Alg_WC_CPP_Settings_Exchange_Rates extends Alg_WC_CPP_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function __construct() {
			$this->id   = 'exchange_rates';
			$this->desc = __( 'Exchange Rates', 'currency-per-product-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * Get exchange rate settings.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 * @todo    [dev] maybe add exchange rate server name directly to button label
		 * @todo    [feature] exchange rates offsets
		 * @todo    [feature] exchange rates rounding
		 * @todo    [feature] final price rounding
		 * @todo    [feature] `alg_wc_cpp_currency_exchange_rates_calculate_by_invert`
		 * @todo    [feature] add JS "grab exchange rate" button
		 */
		public function get_settings() {

			$_time                  = get_option( 'alg_wc_cpp_exchange_rate_cron_time', 0 );
			$update_rates_button    = ( 'auto' === apply_filters( 'alg_wc_cpp', 'manual', 'value_exchange_rate_update' ) ?
			'<a class="button" title="' . alg_wc_cpp_get_currency_exchange_rate_server_name() . '" href="' .
				add_query_arg( 'alg_wc_cpp_update_exchange_rates', '1' ) . '">' . __( 'Update rates now', 'currency-per-product-for-woocommerce' ) . '</a>' : '' );
			$update_rates_cron_time = ( 'auto' === apply_filters( 'alg_wc_cpp', 'manual', 'value_exchange_rate_update' ) && 0 !== $_time ?
				sprintf(
					'<p>' .
					/* translators: %s: time difference, like "1 hour" "50 minutes" "2 days" & so on. */
					__( '%s until next update', 'currency-per-product-for-woocommerce' ) .
					'.</p>',
					human_time_diff( $_time )
				) : ''
			);
			$exchange_rate_update_settings = array(
				array(
					'title' => __( 'Exchange Rates Options', 'currency-per-product-for-woocommerce' ),
					'desc'  => sprintf(
						/* translators: %s: description text */
						__( 'Exchange rates for currencies <strong>will be ignored unless</strong>: %s', 'currency-per-product-for-woocommerce' ),
						'<ol>' .
						'<li>' .
							sprintf(
								/* translators: %s: Behaviour section link */
								__( 'at least one of the behaviour options in %s is set to one of "Convert to ..." options, or', 'currency-per-product-for-woocommerce' ),
								'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cpp&section=behaviour' ) . '">' .
								__( 'WooCommerce > Settings > Currency per Product > Behaviour', 'currency-per-product-for-woocommerce' ) . '</a>'
							) .
						'</li>' .
						'<li>' .
							sprintf(
								/* translators: %s: Advanced section link */
								__( '"Sorting by converted price" or "Filtering by converted price" option is enabled in %s.', 'currency-per-product-for-woocommerce' ),
								'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cpp&section=advanced' ) . '">' .
								__( 'WooCommerce > Settings > Currency per Product > Advanced', 'currency-per-product-for-woocommerce' ) . '</a>'
							) .
						'</li>' .
						'</ol>'
					),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_exchange_rate_update_options',
				),
				array(
					'title'             => __( 'Exchange rates updates', 'currency-per-product-for-woocommerce' ),
					'desc_tip'          => __( 'Possible values: Enter rates manually; Update rates automatically.', 'currency-per-product-for-woocommerce' ),
					'id'                => 'alg_wc_cpp_exchange_rate_update',
					'default'           => 'manual',
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'options'           => array(
						'manual' => __( 'Enter rates manually', 'currency-per-product-for-woocommerce' ),
						'auto'   => __( 'Update rates automatically', 'currency-per-product-for-woocommerce' ),
					),
					'desc'              => $update_rates_button . apply_filters(
						'alg_wc_cpp',
						'<p>' . sprintf(
							/* translators: %s: pro plugin link */
							__(
								'To enable automatic exchange rates, please get <a target="_blank" href="%s">Currency per Product for WooCommerce Pro</a> plugin.',
								'currency-per-product-for-woocommerce'
							),
							'https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=cppupgradetopro&utm_medium=link&utm_campaign=CurrencePerProductLite'
						) . '</p>',
						'settings'
					) . $update_rates_cron_time,
					'custom_attributes' => apply_filters( 'alg_wc_cpp', array( 'disabled' => 'disabled' ), 'settings' ),
				),
				array(
					'title'   => __( 'Update rate', 'currency-per-product-for-woocommerce' ),
					'id'      => 'alg_wc_cpp_exchange_rate_update_rate',
					'default' => 'daily',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => array(
						'hourly'     => __( 'Update Hourly', 'currency-per-product-for-woocommerce' ),
						'twicedaily' => __( 'Update Twice Daily', 'currency-per-product-for-woocommerce' ),
						'daily'      => __( 'Update Daily', 'currency-per-product-for-woocommerce' ),
					),
					'desc'              => apply_filters(
						'alg_wc_cpp',
						'<p>' . sprintf(
							/* translators: %s: pro plugin link */
							__(
								'To enable Update rates, please get <a target="_blank" href="%s">Currency per Product for WooCommerce Pro</a> plugin.',
								'currency-per-product-for-woocommerce'
							),
							'https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=cppupgradetopro&utm_medium=link&utm_campaign=CurrencePerProductLite'
						) . '</p>',
						'settings'
					),
					'custom_attributes' => apply_filters( 'alg_wc_cpp', array( 'disabled' => 'disabled' ), 'settings' ),
				),
				array(
					'title'   => __( 'Update server', 'currency-per-product-for-woocommerce' ),
					'id'      => 'alg_wc_cpp_currency_exchange_rates_server',
					'default' => 'ecb',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => alg_wc_cpp_get_currency_exchange_rate_servers(),
					'desc'              => apply_filters(
						'alg_wc_cpp',
						'<p>' . sprintf(
							/* translators: %s: pro plugin link */
							__(
								'To enable Update server, please get <a target="_blank" href="%s">Currency per Product for WooCommerce Pro</a> plugin.',
								'currency-per-product-for-woocommerce'
							),
							'https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=cppupgradetopro&utm_medium=link&utm_campaign=CurrencePerProductLite'
						) . '</p>',
						'settings'
					),
					'custom_attributes' => apply_filters( 'alg_wc_cpp', array( 'disabled' => 'disabled' ), 'settings' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cpp_exchange_rate_update_options',
				),
			);

			$currency_from          = get_woocommerce_currency();
			$all_currencies         = get_woocommerce_currencies();
			$exchange_rate_settings = array(
				array(
					'title' => __( 'Exchange Rates', 'currency-per-product-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cpp_exchange_rate_options',
				),
				array(
					// phpcs:ignore
					'title'             => __( 'Currency', 'currency-per-product-for-woocommerce' ) . ' #' . '0' . ' [' . $currency_from . '/' . $currency_from . ']',
					'desc'              => ( isset( $all_currencies[ $currency_from ] ) ? $all_currencies[ $currency_from ] : $currency_from ),
					'id'                => 'alg_wc_cpp_exchange_rate_0',
					'default'           => 1,
					'type'              => 'number',
					'custom_attributes' => array( 'readonly' => 'readonly' ),
				),
			);
			$total_number           = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
			for ( $i = 1; $i <= $total_number; $i++ ) {
				$currency_to            = get_option( 'alg_wc_cpp_currency_' . $i, $currency_from );
				$exchange_rate_settings = array_merge(
					$exchange_rate_settings,
					array(
						array(
							'title'             => __( 'Currency', 'currency-per-product-for-woocommerce' ) . ' #' . $i . ' [' . $currency_from . '/' . $currency_to . ']',
							'desc'              => ( isset( $all_currencies[ $currency_to ] ) ? $all_currencies[ $currency_to ] : $currency_to ),
							'id'                => 'alg_wc_cpp_exchange_rate_' . $i,
							'default'           => 1,
							'type'              => 'number',
							'custom_attributes' => array(
								'step' => '0.000000000001',
								'min'  => '0',
							),
						),
					)
				);
			}
			$exchange_rate_settings = array_merge(
				$exchange_rate_settings,
				array(
					array(
						'type' => 'sectionend',
						'id'   => 'alg_wc_cpp_exchange_rate_options',
					),
				)
			);

			return array_merge( $exchange_rate_update_settings, $exchange_rate_settings );
		}

	}

endif;

return new Alg_WC_CPP_Settings_Exchange_Rates();
