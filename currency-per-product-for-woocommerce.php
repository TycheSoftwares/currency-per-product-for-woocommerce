<?php
/**
 * Set diferent currencies for different WooCommerce products.
 *
 * Plugin Name: Currency per Product for WooCommerce
 * Plugin URI: https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/
 * Description: Set and display prices for WooCommerce products in different currencies.
 * Version: 1.4.7
 * Author: Tyche Softwares
 * Author URI: https://www.tychesoftwares.com/
 * Text Domain: currency-per-product-for-woocommerce
 * Domain Path: /langs
 * Copyright: © 2020 Tyche Softwares
 * WC tested up to: 4.5
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check if WooCommerce is active.
$plugin_woo = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin_woo, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) &&
	! ( is_multisite() && array_key_exists( $plugin_woo, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'currency-per-product-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return.
	$plugin_cpp = 'currency-per-product-for-woocommerce-pro/currency-per-product-for-woocommerce-pro.php';
	if (
		in_array( $plugin_cpp, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) ||
		( is_multisite() && array_key_exists( $plugin_cpp, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_WC_CPP' ) ) :

	/**
	 * Main Alg_WC_CPP Class
	 *
	 * @class   Alg_WC_CPP
	 * @version 1.4.1
	 * @since   1.0.0
	 */
	final class Alg_WC_CPP {

		/**
		 * Plugin version.
		 *
		 * @var   string
		 * @since 1.0.0
		 */
		public $version = '1.4.7';

		/**
		 * Single instance of the class.
		 *
		 * @var   Alg_WC_CPP The single instance of the class
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * Main Alg_WC_CPP Instance
		 *
		 * Ensures only one instance of Alg_WC_CPP is loaded or can be loaded.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @static
		 * @return  Alg_WC_CPP - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Alg_WC_CPP Constructor.
		 *
		 * @version 1.4.1
		 * @since   1.0.0
		 * @access  public
		 */
		public function __construct() {

			// Set up localisation.
			load_plugin_textdomain( 'currency-per-product-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

			// Constants.
			if ( ! defined( 'ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0' ) ) {
				define( 'ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0', version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' ) );
			}

			// Include required files.
			$this->includes();

			// Admin.
			if ( is_admin() ) {
				add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
				// Settings.
				require_once 'includes/settings/class-alg-wc-cpp-settings-section.php';
				$this->settings                   = array();
				$this->settings['general']        = require_once 'includes/settings/class-alg-wc-cpp-settings-general.php';
				$this->settings['behaviour']      = require_once 'includes/settings/class-alg-wc-cpp-settings-behaviour.php';
				$this->settings['currencies']     = require_once 'includes/settings/class-alg-wc-cpp-settings-currencies.php';
				$this->settings['exchange-rates'] = require_once 'includes/settings/class-alg-wc-cpp-settings-exchange-rates.php';
				$this->settings['advanced']       = require_once 'includes/settings/class-alg-wc-cpp-settings-advanced.php';
				// Version updated.
				if ( get_option( 'alg_wc_cpp_version', '' ) !== $this->version ) {
					add_action( 'admin_init', array( $this, 'version_updated' ) );
				}
			}

		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @version 1.2.1
		 * @since   1.0.0
		 * @param   mixed $links Links array.
		 * @return  array
		 */
		public function action_links( $links ) {
			$custom_links   = array();
			$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cpp' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
			if ( 'currency-per-product-for-woocommerce.php' === basename( __FILE__ ) ) {
				$custom_links[] = '<a href="https://www.tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=cppupgradetopro&utm_medium=unlockall&utm_campaign=CurrencePerProductLite">' .
				__( 'Unlock All', 'currency-per-product-for-woocommerce' ) . '</a>';
			}
			return array_merge( $custom_links, $links );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @version 1.4.1
		 * @since   1.0.0
		 */
		public function includes() {
			// Functions.
			require_once 'includes/functions/alg-wc-cpp-functions.php';
			require_once 'includes/functions/alg-wc-cpp-exchange-rates-functions.php';
			// Core.
			$this->core = require_once 'includes/class-alg-wc-cpp-core.php';
		}

		/**
		 * Runs when plugin is updated.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 */
		public function version_updated() {
			// Adding (new) options.
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
			// Version updated.
			update_option( 'alg_wc_cpp_version', $this->version );
		}

		/**
		 * Add Currency per Product settings tab to WooCommerce settings.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 *
		 * @param array $settings Settings array.
		 */
		public function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once 'includes/settings/class-alg-wc-settings-cpp.php';
			return $settings;
		}

		/**
		 * Get the plugin url.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return  string
		 */
		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return  string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

	}

endif;

if ( ! function_exists( 'alg_wc_cpp' ) ) {
	/**
	 * Returns the main instance of Alg_WC_CPP to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_CPP
	 */
	function alg_wc_cpp() {
		return Alg_WC_CPP::instance();
	}
}

alg_wc_cpp();
