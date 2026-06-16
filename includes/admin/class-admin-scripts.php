<?php
/**
 * Currency per Product for WooCommerce - Admin Scripts Class
 *
 * Enqueues the compiled React app (build/admin.js + build/admin.css) on the
 * plugin's WooCommerce settings tab and passes localised data to the frontend.
 *
 * @version 2.0.0
 * @since   2.0.0
 * @package Currency per Product for WooCommerce Pro/Admin
 */

namespace Tyche\CPP\Admin;

defined( 'ABSPATH' ) || exit;

class Admin_Scripts extends Admin {

	/**
	 * Constructor. Hooks enqueue callbacks onto admin_enqueue_scripts.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_admin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_tyche_script' ), 1000001 );
	}

	/**
	 * Enqueue the compiled stylesheet.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_css() {
		if ( ! self::is_on_cpp_page() ) {
			return;
		}

		wp_enqueue_style(
			'currency-per-product-for-woocommerce-admin',
			plugins_url( 'build/admin.css', CPP_FILE ),
			array(),
			CPP_VERSION
		);
	}

	/**
	 * Enqueue the compiled JS bundle and pass localised data.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_js() {
		if ( ! self::is_on_cpp_page() ) {
			return;
		}

		// Read webpack-generated asset manifest for accurate dependency list + content hash.
		$asset_file = CPP_PLUGIN_DIR_PATH . 'build/admin.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array( 'react', 'react-dom', 'wp-api-fetch', 'wp-components', 'wp-element', 'wp-i18n', 'wp-primitives' ),
				'version'      => CPP_VERSION,
			);

		wp_register_script(
			'currency-per-product-for-woocommerce-admin',
			plugins_url( 'build/admin.js', CPP_FILE ),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			'currency-per-product-for-woocommerce-admin',
			'cppAdminData',
			array(
				'nonce'              => wp_create_nonce( 'wp_rest' ),
				'pluginVersion'      => CPP_VERSION,
				'currencies'         => get_woocommerce_currencies(),
				'shopCurrency'       => get_option( 'woocommerce_currency' ),
				'payment_gateways'   => $this->get_payment_gateways(),
				'user_roles'         => $this->get_user_roles(),
				'users'              => $this->get_users(),
				'hasProductCurrency' => $this->has_product_currency(),
			)
		);

		wp_enqueue_script( 'currency-per-product-for-woocommerce-admin' );

		wp_set_script_translations(
			'currency-per-product-for-woocommerce-admin',
			'currency-per-product-for-woocommerce',
			CPP_PLUGIN_DIR_PATH . 'languages'
		);
	}

	/**
	 * Function to enqueue scripts on Edit/Add order page.
	 *
	 * @version 1.5.3
	 * @since   1.5.3
	 */
	public function enqueue_scripts_admin() {
		global $post;

		$screen = get_current_screen();

		wp_enqueue_script(
			'alg-wc-cpp-admin',
			plugins_url( 'assets/js/cpp-admin.js', CPP_FILE ),
			array( 'jquery' ),
			CPP_VERSION,
			true
		);

		if ( 'shop_order' === $screen->post_type || 'shop_subscription' === $screen->post_type ) {
			if ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] ) {// phpcs:ignore WordPress.Security.NonceVerification
				$order_id = ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : 0;// phpcs:ignore WordPress.Security.NonceVerification
			} else {
				$order_id = $post->ID;
			}

			wp_localize_script(
				'alg-wc-cpp-admin',
				'cpp_order_id_currency',
				array(
					'order_id' => $order_id,
				)
			);

			wp_localize_script(
				'alg-wc-cpp-admin',
				'cpp_currency_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);

			wp_localize_script(
				'alg-wc-cpp-admin',
				'cpp_nonce_param',
				array(
					'select_currency_nonce' => wp_create_nonce( 'select-currency' ),
				)
			);
		}
	}

	/**
	 * Returns enabled payment gateways as [{id, title}] for the frontend.
	 */
	private function get_payment_gateways() {
		if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways ) {
			return array();
		}
		$result = array();
		foreach ( WC()->payment_gateways->payment_gateways() as $id => $gw ) {
			$result[] = array( 'id' => $id, 'title' => $gw->get_title() );
		}
		return $result;
	}

	/**
	 * Returns registered user roles as [{id, name}] for the frontend.
	 */
	private function get_user_roles() {
		$result = array();
		foreach ( wp_roles()->get_names() as $slug => $label ) {
			$result[] = array( 'id' => $slug, 'name' => translate_user_role( $label ) );
		}
		return $result;
	}

	/**
	 * Returns all users as [{id, name}] for the frontend.
	 */
	private function get_users() {
		$result = array();
		foreach ( get_users( array( 'fields' => array( 'ID', 'display_name' ) ) ) as $user ) {
			$result[] = array( 'id' => (int) $user->ID, 'name' => $user->display_name );
		}
		return $result;
	}

	/**
	 * Enqueues the shared Tyche JS utility (deactivation modal etc.) on all admin pages.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_tyche_script() {
		wp_register_script(
			'tyche',
			plugins_url() . '/currency-per-product-for-woocommerce/assets/js/tyche.js',
			array( 'jquery' ),
			get_option( 'alg_wc_cpp_version' ),
			false
		);
		wp_enqueue_script( 'tyche' );
	}

	/**
	 * Returns true if at least one product has a per-product currency assigned.
	 *
	 * @since 2.0.0
	 */
	private function has_product_currency() {
		global $wpdb;
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1 FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' LIMIT 1",
				'_alg_wc_cpp_currency'
			)
		);
		return ! is_null( $exists );
	}
}

class_alias( Admin_Scripts::class, 'CPP_Admin_Scripts' );
