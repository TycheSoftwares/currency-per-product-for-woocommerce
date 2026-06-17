<?php
/**
 * CON Hooks Class
 *
 * Handles the hooks for the CON plugin.
 *
 * @author  Tyche Softwares
 * @package CON/Hooks
 */

namespace Tyche\CPP;

defined( 'ABSPATH' ) || exit;

class Hooks {

	public static function init() {
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CPP_FILE, true );
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'orders_cache', CPP_FILE, true );
				}
			},
			999
		);
	}


}