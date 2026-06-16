<?php
/**
 * Custom Order Numbers for WooCommerce.
 *
 * Admin Base Class.
 *
 * @author      Tyche Softwares
 * @package     CON/Admin
 * @category    Classes
 * @since       2.0
 */

namespace Tyche\CPP\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Base Class.
 *
 * @since 2.0
 */
class Admin {

	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	public function __construct() {
	}

	/**
	 * Checks if the user is on the Admin Section of the Plugin.
	 *
	 * @since 1.0
	 */
	public static function is_on_cpp_page() {
		global $pagenow;
		return 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset ( $_GET['tab'] ) &&  'currency-per-product-for-woocommerce' === $_GET['tab']; // phpcs:ignore
	}

	/**
	 * Checks if the user is on theWP Plugin Page.
	 *
	 * @since 1.0
	 */
	public static function is_on_wp_plugin_page() {
		global $pagenow;
		return 'plugins.php' === $pagenow; // phpcs:ignore
	}
}