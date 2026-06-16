<?php
/**
 * Custom Order Numbers for WooCommerce.
 *
 * Main Class.
 *
 * @author      Tyche Softwares
 * @package     CON/Main
 * @category    Classes
 * @since       2.0
 */

namespace Tyche\CPP;

defined( 'ABSPATH' ) || exit;

use Tyche\CPP\Functions\Functions;

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Main Class.
 */
final class Currency_Per_Product {

    /**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	protected static $plugin_version = '2.0.0';

	/**
	 * Minimum version of WordPress required.
	 *
	 * @var string
	 */
	private static $wordpress_version = '5.2';

	/**
	 * Minimum version of PHP required.
	 *
	 * @var string
	 */
	private static $php_version = '7.4';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected static $plugin_slug = 'currency-per-product-for-woocommerce';

	/**
	 * Plugin Name.
	 *
	 * @var string
	 */
	protected static $plugin_name = 'Currency Per Product for WooCommerce';

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	protected static $plugin_url = 'https://www.tychesoftwares.com/products/woocommerce-currency-per-product-plugin/';

    /**
     * The single instance of the class.
     *
     * @var Custom_Order_Numbers
     * @since 2.0
     */
    protected static $instance = null;

    /**
     * Main Custom Order Numbers Instance.
     *
     * Ensures only one instance of Custom Order Numbers is loaded or can be loaded.
     *
     * @return Custom_Order_Numbers - Main instance.
     * @since 2.0
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->setup();
        }
        return self::$instance;
    }

	/**
	 * Register the textdomain and schedule plugin initialisation on plugins_loaded.
	 *
	 * Called once from the main plugin file. Keeps bootstrap logic out of
	 * the global scope.
	 *
	 * @since 2.0
	 */
	public static function bootstrap() {
		// Must be registered before plugins_loaded so it fires before WooCommerce
		// boots and triggers before_woocommerce_init.
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					$plugin = plugin_basename( CPP_FILE );
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $plugin, true );
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'orders_cache', $plugin, true );
				}
			}
		);

		add_action(
			'init',
			function () {
				$domain = 'currency-per-product-for-woocommerce';
				$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
				if ( ! load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '-' . $locale . '.mo' ) ) {
					load_plugin_textdomain( $domain, false, dirname( plugin_basename( CPP_FILE ) ) . '/languages/' );
				}
			},
			1
		);
		add_action( 'plugins_loaded', __NAMESPACE__ . '\\CPP' );
	}

    /**
	 * A dummy constructor to prevent CON from being loaded more than once.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

    /**
	 * A dummy magic method to prevent CON from being cloned.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Not allowed.', 'currency-per-product-for-woocommerce' ), '1.0' );
	}

	/**
	 * A dummy magic method to prevent CON from being unserialized.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Not allowed.', 'currency-per-product-for-woocommerce' ), '1.0' );
	}

    private function setup() {

		/**
		 * Define Constants.
		 */
		self::define_constants();

		if ( ! self::check_requirements() ) {
			return;
		}

		self::init();

		/**
		 * Include Files.
		 */
		self::maybe_include_files();

		/**
		 * Hooks.
		 */
		self::init_hooks();
    }

	public function init() {
		// Deactivate Lite.
		add_filter( 'plugin_action_links_' . CPP_PLUGIN_BASENAME, array( $this, 'action_links' ) );
		add_action( 'init', array( $this, 'maybe_start_wc_session' ), 1 );

		add_filter(
			'woocommerce_settings_tabs_array',
			function ( $tabs ) {
				$tabs['currency-per-product-for-woocommerce'] = __( 'Currency Per Product', 'custom-order-numbers-for-woocommerce' );
				return $tabs;
			},
			50
		);

		add_action(
			'woocommerce_settings_tabs_currency-per-product-for-woocommerce',
			function () {
				echo '<div id="currency-per-product-for-woocommerce"></div>';
			}
		);
	}

	/**
	 * Function for definining constants.
	 *
	 * @param string $variable Constant which is to be defined.
	 * @param string $value Valueof the Constant.
	 *
	 * @since 1.0
	 */
	public static function define( $variable, $value ) {
		if ( ! defined( $variable ) ) {
			define( $variable, $value );
		}
	}

	/**
	 * Include File.
	 *
	 * @param string $file File to be included.
	 * @param bool   $is_plugin_include_file If it's a plugin file, then we can add the path.
	 * @since 1.0
	 */
	public static function include_file( $file, $is_plugin_include_file = true ) {
		$file = $is_plugin_include_file ? CPP_PLUGIN_DIR_PATH . '/includes/' . $file : $file;

		$real        = realpath( $file );
		$plugin_base = realpath( CPP_PLUGIN_DIR_PATH );

		if ( false === $real || false === $plugin_base || 0 !== strpos( $real, $plugin_base ) ) {
			return;
		}

		include_once $real; // nosemgrep: audit.php.lang.security.file.inclusion-arg -- all callers pass hardcoded string literals; path is prefixed with CON_PRO_PLUGIN_DIR_PATH.
	}

	/**
	 * Define constants to be used accross the plugin.
	 *
	 * @since 2.0
	 */
	public static function define_constants() {
		self::define( 'CPP_URL', self::$plugin_url );
		self::define( 'CPP_ITEM_NAME', 'Currency Per Product for WooCommerce' );
		self::define( 'CPP_VERSION', self::$plugin_version );
		self::define( 'CPP_PLUGIN_BASENAME', plugin_basename( CPP_FILE ) );
		self::define( 'CPP_PLUGIN_DIR_PATH', plugin_dir_path( CPP_FILE ) );
		self::define( 'CPP_PLUGIN_PATH', untrailingslashit( plugin_dir_path( CPP_FILE ) ) );
		self::define( 'CPP_PLUGIN_URL', plugins_url( '/', CPP_FILE ) );
        self::define( 'CPP_STORE_URL', 'https://www.tychesoftwares.com/' );
		self::define( 'CPP_AJAX_URL', get_admin_url() . 'admin-ajax.php' );
	}

	/**
	 * Checks that all requirements are met.
	 *
	 * @return bool
	 */
	public static function check_requirements() {

		$messages = array();

		// Check WordPress version.
		if ( version_compare( get_bloginfo( 'version' ), self::$wordpress_version, '<' ) ) {
			/* translators: 1. Plugin Name, 2. WordPress Version */
			$messages[] = sprintf( esc_html__( 'You are using an outdated version of WordPress. %1$s requires WP version %2$s or higher.', 'currency-per-product-for-woocommerce' ), self::$plugin_name, self::$wordpress_version );
		}

		// Check PHP version.
		if ( version_compare( phpversion(), self::$php_version, '<' ) ) {
			/* translators: 1. Plugin Name, 2. PHP Version */
			$messages[] = sprintf( esc_html__( '%1$s requires PHP version %2$s or above. Please update PHP to run this plugin.', 'currency-per-product-for-woocommerce' ), self::$plugin_name, self::$php_version );
		}

		// Check WooCommerce.
		if ( ! self::is_woocommerce_active() ) {
			/* translators: Plugin Name */
			$messages[] = sprintf( esc_html__( 'WooCommerce not found. %s requires a minimum of WooCommerce v3.3.0.', 'currency-per-product-for-woocommerce' ), self::$plugin_name );
		}

		if ( empty( $messages ) ) {
			return true;
		}

		add_action( 'admin_init', array( __CLASS__, 'deactivate' ) );

		return false;
	}

	/**
	 * Auto-deactivate plugin if requirements are not met.
	 */
	public static function deactivate() {
		if ( is_plugin_active( plugin_basename( CPP_FILE ) ) ) {
			deactivate_plugins( plugin_basename( CPP_FILE ) );
		}

		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore
			unset( $_GET['activate'] ); // phpcs:ignore
		}
	}

	/**
	 * Checks if WooCommerce is installed and active.
	 *
	 * @since 1.0
	 */
	public static function is_woocommerce_active() {

		// WooCommerce is required.
		$woocommerce_path = 'woocommerce/woocommerce.php';
		$active_plugins   = (array) get_option( 'active_plugins', array() );
		$active           = false;

		if ( is_multisite() ) {
			$plugins = get_site_option( 'active_sitewide_plugins' );
			$active  = isset( $plugins[ $woocommerce_path ] );
		}

		return in_array( $woocommerce_path, $active_plugins, true ) || array_key_exists( $woocommerce_path, $active_plugins ) || $active;
	}

	/**
	 * Checks whether to inlcude the plugin files.
	 *
	 * @since 1.0
	 */
	public static function maybe_include_files() {
		self::include_file( 'core/class-files.php' );
		Files::include_files();
	}

	/**
	 * Action Hooks.
	 *
	 * @since 1.0
	 */
	private static function init_hooks() {
		register_activation_hook( CPP_FILE, array( 'CPP_Migration', 'activate' ) );

		// CON Hooks.
		self::include_file( 'core/class-hooks.php' );
		Hooks::init();
	}

	/**
	 * Show action links on the plugin screen
	 *
	 * @param   mixed $links Action Links.
	 * @version 1.2.0
	 * @since   1.0.0
	 * @return  array
	 */
	public function action_links( $links ) {
		$custom_links   = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=currency-per-product-for-woocommerce' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';

		$custom_links[] = '<a href="tychesoftwares.com/store/premium-plugins/currency-per-product-for-woocommerce/?utm_source=cppupgradetopro&utm_medium=unlockall&utm_campaign=CurrencePerProductLite">' . __( 'Unlock All', 'currency-per-product-for-woocommerce' ) . '</a>';

		return array_merge( $custom_links, $links );
	}

	/**
	 * Ensure WooCommerce session cookie is created for non-logged users too.
	 */
	public function maybe_start_wc_session() {
		if ( function_exists( 'WC' ) && WC()->session ) {
			if ( ! WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}
		}
	}

}

