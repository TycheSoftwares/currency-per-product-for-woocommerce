<?php
/**
 * CON License API Class
 *
 * @package CON/Admin/API/License
 */

namespace Tyche\CPP\API;

defined( 'ABSPATH' ) || exit;

use Tyche\CPP\Tracking\Tyche_Plugin_Tracking;


/**
 * CON Tracking API Class
 */
class Tyche_Tracking_API extends Admin_API {

	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

	/**
	 * Register license endpoints.
	 */
	public static function register_endpoints() {
		register_rest_route(
			self::$base_endpoint,
			'reset-tracking',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'reset_tracking' ),
					'permission_callback' => array( __CLASS__, 'permissions' ),
				),
			)
		);
	}

    /**
	 * Resets usage tracking by deleting the tracking options.
	 */
	public static function reset_tracking( $request ) {
		Tyche_Plugin_Tracking::reset_tracker_setting( 'cpp_lite' );
		return self::return_response( array( 'message' => 'Tracking has been successfully reset.' ) );
	}


}

return new Tyche_Tracking_API();
