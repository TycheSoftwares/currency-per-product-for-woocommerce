<?php
/**
 * Currency per Product for WooCommerce - Functions
 *
 * @version 1.4.0
 * @since   1.0.0
 * @author  Tyche Softwares
 *
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'alg_wc_cpp_get_product_id' ) ) {
	/**
	 * Get Product Id.
	 *
	 * @version 1.4.0
	 * @since   1.3.0
	 *
	 * @param object $_product Product object.
	 */
	function alg_wc_cpp_get_product_id( $_product ) {
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		if ( ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0 ) {
			return ( isset( $_product->variation_id ) ) ? $_product->variation_id : $_product->id;
		} else {
			return $_product->get_id();
		}
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_product_id_or_variation_parent_id' ) ) {
	/**
	 * Get product or Variation Id.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @param object $_product Product object.
	 */
	function alg_wc_cpp_get_product_id_or_variation_parent_id( $_product ) {
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		if ( ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0 ) {
			return $_product->id;
		} else {
			return ( $_product->is_type( 'variation' ) ) ? $_product->get_parent_id() : $_product->get_id();
		}
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_product_display_price' ) ) {
	/**
	 * Get product display price.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @param object $_product Product object.
	 * @param string $price    Price.
	 * @param int    $qty      Product quantity.
	 */
	function alg_wc_cpp_get_product_display_price( $_product, $price = '', $qty = 1 ) {
		if ( ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0 ) {
			return $_product->get_display_price( $price, $qty );
		} else {
			return wc_get_price_to_display(
				$_product,
				array(
					'price' => $price,
					'qty'   => $qty,
				)
			);
		}
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_terms' ) ) {
	/**
	 * Get terms.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @param array $args Arguments array.
	 */
	function alg_wc_cpp_get_terms( $args ) {
		if ( ! is_array( $args ) ) {
			$_taxonomy = $args;
			$args      = array(
				'taxonomy'   => $_taxonomy,
				'orderby'    => 'name',
				'hide_empty' => false,
			);
		}
		global $wp_version;
		if ( version_compare( $wp_version, '4.5.0', '>=' ) ) {
			$_terms = get_terms( $args );
		} else {
			$_taxonomy = $args['taxonomy'];
			unset( $args['taxonomy'] );
			$_terms = get_terms( $_taxonomy, $args );
		}
		$_terms_options = array();
		if ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ) {
			foreach ( $_terms as $_term ) {
				$_terms_options[ $_term->term_id ] = $_term->name;
			}
		}
		return $_terms_options;
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_product_terms' ) ) {
	/**
	 * alg_wc_cpp_get_product_terms.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_product_terms( $product_id, $taxonomy ) {
		if ( ! $product_id ) {
			return;
		}

		$_terms = get_the_terms( $product_id, $taxonomy );

		$_terms_id = array();
		if ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ){
			foreach ( $_terms as $_term ) {
				array_push( $_terms_id, $_term->term_id );
			}
		}
		return $_terms_id;
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_user_roles_options' ) ) {
	/**
	 * Get user role options.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_user_roles_options() {
		global $wp_roles;
		$all_roles         = ( isset( $wp_roles ) && is_object( $wp_roles ) ) ? $wp_roles->roles : array();
		$all_roles         = apply_filters( 'editable_roles', $all_roles );
		$all_roles         = array_merge(
			array(
				'guest' => array(
					'name'         => __( 'Guest', 'currency-per-product-for-woocommerce' ),
					'capabilities' => array(),
				),
			),
			$all_roles
		);
		$all_roles_options = array();
		foreach ( $all_roles as $_role_key => $_role ) {
			$all_roles_options[ $_role_key ] = $_role['name'];
		}
		return $all_roles_options;
	}
}

if ( ! function_exists( 'alg_wc_cpp_is_user_role' ) ) {
	/**
	 * Check user roles.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 * @return  bool
	 *
	 * @param array $roles_to_check User roles array.
	 * @param int   $user_id        User Id.
	 */
	function alg_wc_cpp_is_user_role( $roles_to_check, $user_id = 0 ) {

		$_user = ( 0 === $user_id ? wp_get_current_user() : get_user_by( 'id', $user_id ) );
		if ( empty( $_user->roles ) ) {
			$_user->roles = array( 'guest' );
		}
		if ( ! is_array( $_user->roles ) ) {
			$_user->roles = (array) $_user->roles;
		}

		if ( ! is_array( $roles_to_check ) ) {
			$roles_to_check = (array) $roles_to_check;
		}
		if ( in_array( 'administrator', $roles_to_check, true ) ) {
			$roles_to_check[] = 'super_admin';
		}

		$_intersect = array_intersect( $roles_to_check, $_user->roles );
		return ( ! empty( $_intersect ) );
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_users_as_options' ) ) {
	/**
	 * Get display names of users.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_users_as_options() {
		$users = array();
		foreach ( get_users( 'orderby=display_name' ) as $user ) {
			$users[ $user->ID ] = $user->display_name . ' (#' . $user->ID . ')';
		}
		return $users;
	}
}

if ( ! function_exists( 'alg_wc_cpp_calculate_and_update_product_price' ) ) {
	/**
	 * Calculate & Update product prices.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @param int    $product_id    Product Id.
	 * @param string $shop_currency Shop currency.
	 */
	function alg_wc_cpp_calculate_and_update_product_price( $product_id, $shop_currency ) {
		$original_price   = get_post_meta( $product_id, '_price', true );
		$product_currency = get_post_meta( $product_id, '_alg_wc_cpp_currency', true );
		$converted_price  = ( '' !== $original_price && '' !== $product_currency && $shop_currency !== $product_currency ?
			$original_price * alg_wc_cpp_get_currency_exchange_rate( $product_currency ) : $original_price );
		update_post_meta( $product_id, '_alg_wc_cpp_converted_price', $converted_price );
	}
}

if ( ! function_exists( 'alg_wc_cpp_calculate_all_products_prices' ) ) {
	/**
	 * Calculate all product prices.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 * @todo    [dev] maybe also automatically run this when some settings section (e.g.: exchange rates; advanced ...) is saved
	 */
	function alg_wc_cpp_calculate_all_products_prices() {
		$shop_currency  = get_woocommerce_currency();
		$block_size     = 1024;
		$offset         = 0;
		$total_products = 0;
		while ( true ) {
			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => $block_size,
				'offset'         => $offset,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'fields'         => 'ids',
			);
			$loop = new WP_Query( $args );
			if ( ! $loop->have_posts() ) {
				break;
			}
			foreach ( $loop->posts as $product_id ) {
				alg_wc_cpp_calculate_and_update_product_price( $product_id, $shop_currency );
				$total_products++;
			}
			$offset += $block_size;
		}
		return $total_products;
	}
}
