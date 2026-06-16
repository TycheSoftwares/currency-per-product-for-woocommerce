<?php
/**
 * CPP Store API
 * Provides searchable endpoints for product categories and tags.
 *
 * @package CPP/Admin/API/Store
 */

namespace Tyche\CPP\API;

defined( 'ABSPATH' ) || exit;

class Store extends Admin_API {

	/**
	 * Register REST endpoints.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

	public static function register_endpoints() {

		$args = array(
			'search' => array(
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'limit'  => array(
				'required'          => false,
				'sanitize_callback' => 'absint',
				'default'           => 20,
			),
		);

		register_rest_route(
			self::$base_endpoint,
			'store/categories',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_categories' ),
				'args'                => $args,
				'permission_callback' => array( __CLASS__, 'permissions' ),
			)
		);

		register_rest_route(
			self::$base_endpoint,
			'store/tags',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_tags' ),
				'args'                => $args,
				'permission_callback' => array( __CLASS__, 'permissions' ),
			)
		);
	}

	/**
	 * GET /cpp/v1/store/categories
	 */
	public static function get_categories( $request ) {
		return self::get_terms( 'product_cat', $request );
	}

	/**
	 * GET /cpp/v1/store/tags
	 */
	public static function get_tags( $request ) {
		return self::get_terms( 'product_tag', $request );
	}

	/**
	 * Shared term fetcher.
	 */
	private static function get_terms( $taxonomy, $request ) {
		$search = $request->get_param( 'search' );
		$limit  = $request->get_param( 'limit' );

		$terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'name__like' => $search,
			'hide_empty' => false,
			'number'     => $limit,
		) );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		return rest_ensure_response(
			array_map(
				function ( $term ) {
					return array( 'id' => $term->term_id, 'title' => $term->name );
				},
				$terms
			)
		);
	}
}

return new Store();
