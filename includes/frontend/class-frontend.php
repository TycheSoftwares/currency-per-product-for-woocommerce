<?php
/**
 * Currency per Product for WooCommerce - Core Class
 *
 * @version 1.4.2
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 *
 * @package Currency per Product for WooCommerce Pro/include/core
 */

namespace Tyche\CPP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Tyche\CPP\Functions\Functions;
use Tyche\CPP\Functions\Exchange_Rate_Functions;
/**
 * Main Core Class
 */
#[\AllowDynamicProperties]
class Frontend {

	/**
	 * Convert_in_shop.
	 *
	 * @var   string
	 * @since 1.10.0
	 */
	public $convert_in_shop = '';

	/**
	 * Custom currency symbol template .
	 *
	 * @var   string
	 * @since 1.10.0
	 */
	public $custom_currency_symbol_template = '';

	/**
	 * Alg_wc_cpp.
	 *
	 * @var   string
	 * @since 1.10.0
	 */
	public $alg_wc_cpp = '';

	/**
	 * Cart_checkout_behaviour.
	 *
	 * @var   string
	 * @since 1.10.0
	 */
	public $cart_checkout_behaviour = '';

	/**
	 * Constructor.
	 *
	 * @version 1.4.1
	 * @since   1.0.0
	 * @todo    [dev] store options in arrays (e.g. `"alg_wc_cpp_currency[{$i}]"` instead of `'alg_wc_cpp_currency_' . $i` and `"alg_wc_cpp_exchange_rate[{$currency_code}]"` instead of `'alg_wc_cpp_exchange_rate_' . $i`)
	 * @todo    [feature] add "price formats" subsection
	 */
	public function __construct() {
		if ( Functions::get_general_setting( 'enabled', false ) ) {

			// Behaviour options.
			$shop_behaviour                        = Functions::get_behavior_setting( 'shop_behaviour', 'show_in_different' );
			$cart_checkout_behaviour               = Functions::get_behavior_setting( 'cart_checkout', 'convert_shop_default' );
			$this->convert_in_shop                 = ( 'convert_shop_default' === $shop_behaviour );
			$this->cart_checkout_behaviour         = ( $this->convert_in_shop ? 'convert_shop_default' : $cart_checkout_behaviour);

			// Currency code & symbol.
			add_filter( 'woocommerce_currency', array( $this, 'change_currency_code' ), PHP_INT_MAX );

			// Currency for the products on the custom page created using the Featured Products block.
			add_filter( 'woocommerce_get_price_html', array( $this, 'cpp_change_currency_for_featured_products_block' ), PHP_INT_MAX, 2 );
			add_filter( 'wc_price_args', array( $this, 'show_currency_when_price_is_calculated' ), PHP_INT_MAX );
			add_action( 'woocommerce_before_checkout_form', array( $this, 'unset_saved_currency_at_checkout' ), PHP_INT_MAX );
			add_action( 'wp_enqueue_scripts', array( $this, 'fix_cart_fragments' ), PHP_INT_MAX );

			if ( Functions::get_general_setting( 'custom_currency_symbol_enabled', false ) ) {
				add_filter( 'woocommerce_currency_symbol', array( $this, 'add_currency_code' ), PHP_INT_MAX, 2 );
				$this->custom_currency_symbol_template = Functions::get_general_setting( 'custom_currency_symbol_template', '%currency_code%%currency_symbol%' );
			}

			// Add to cart.
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_on_add_to_cart' ), PHP_INT_MAX, 5 );

			// Price.
			add_filter( 'woocommerce_product_get_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );

			add_filter( 'booking_form_calculated_booking_cost', array( $this, 'change_booking_price' ), 100, 3 );
			add_filter( 'woocommerce_get_price_html', array( $this, 'cpp_override_price_html_for_shop_page' ), PHP_INT_MAX, 10, 2 );
			// Grouped.
			if ( ! $this->convert_in_shop ) {
				add_filter( 'woocommerce_grouped_price_html', array( $this, 'grouped_price_html' ), PHP_INT_MAX, 2 );
			}

			// Shipping.
			add_filter( 'woocommerce_package_rates', array( $this, 'change_shipping_price' ), PHP_INT_MAX, 2 );

			// Fix mini cart.
			if ( Functions::get_advanced_setting( 'fix_mini_cart', false ) ) {
				add_action( 'wp_loaded', array( $this, 'fix_mini_cart' ), PHP_INT_MAX );
			}

			// Prices on site.
			if ( $this->convert_in_shop ) {
				// Regular price.
				$price_filter = 'woocommerce_product_get_regular_price';
				add_filter( $price_filter, array( $this, 'change_price' ), PHP_INT_MAX, 2 ); //phpcs:ignore
				add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );
				// Sale price.
				$price_filter = 'woocommerce_product_get_sale_price';
				add_filter( $price_filter, array( $this, 'change_price' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );
				// Variation price.
				add_filter( 'woocommerce_variation_prices_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );
				// Variation hash.
				add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'get_variation_prices_hash' ), PHP_INT_MAX, 3 );
				// Grouped products.
				add_filter( 'woocommerce_get_price_including_tax', array( $this, 'change_price_grouped' ), PHP_INT_MAX, 3 );
				add_filter( 'woocommerce_get_price_excluding_tax', array( $this, 'change_price_grouped' ), PHP_INT_MAX, 3 );
			}

			// "Original price" in shop
			if ( Functions::get_behavior_setting( 'original_price_in_shop_enabled', false ) && 'show_in_different' != Functions::get_behavior_setting( 'shop_behaviour', 'show_in_different' ) ) { //phpcs:ignore
				add_filter( 'woocommerce_get_price_html', array( $this, 'add_original_price_in_shop' ), PHP_INT_MAX, 2 );
			}

			// "Sort by price" sorting and "Filter Products by Price" widget
			if ( Functions::get_advanced_setting( 'sort_by_converted_price', false ) || Functions::get_advanced_setting( 'filter_by_converted_price', false ) ) {
				// "Sort by price"
				if ( Functions::get_advanced_setting( 'sort_by_converted_price', false ) ) {
					add_action( 'woocommerce_product_query', array( $this, 'remove_sorting_by_price_posts_clauses_filters' ), PHP_INT_MAX, 2 );
					add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'add_sorting_by_converted_price' ), PHP_INT_MAX );
				}
				// "Filter Products by Price"
				if ( Functions::get_advanced_setting( 'filter_by_converted_price', false ) ) {
					add_filter( 'loop_shop_post_in', array( $this, 'products_by_price_filter' ), PHP_INT_MAX, 1 );
					add_filter( 'woocommerce_product_query_meta_query', array( $this, 'price_filter_meta_query' ), PHP_INT_MAX, 2 );
					add_filter( 'woocommerce_price_filter_widget_min_amount', array( $this, 'min_price' ), PHP_INT_MAX );
					add_filter( 'woocommerce_price_filter_widget_max_amount', array( $this, 'max_price' ), PHP_INT_MAX );
				}
			}
		}
	}


	/**
	 * Function products_by_price_filter.
	 *
	 * @param array $product_ids Product ids.
	 */
	public function products_by_price_filter( $product_ids ) {
		if ( is_main_query() && isset( $_GET['min_price'] ) && isset( $_GET['max_price'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
			$new_ids        = array();
			$min_price      = ! empty( $_GET['min_price'] ) ? sanitize_text_field( wp_unslash( $_GET['min_price'] ) ) : 0;// phpcs:ignore WordPress.Security.NonceVerification
			$max_price      = ! empty( $_GET['max_price'] ) ? sanitize_text_field( wp_unslash( $_GET['max_price'] ) ) : 0;// phpcs:ignore WordPress.Security.NonceVerification
			$product_id_all = wc_get_products(
				array(
					'return' => 'ids',
					'limit'  => -1,
				)
			);
			foreach ( $product_id_all as $product_id ) {
				$product = wc_get_product( $product_id );
				$price   = $product->get_price();
				if ( $price >= $min_price && $price <= $max_price ) {
					$new_ids[] = $product_id;
				}
			}
			$product_ids = $new_ids;
			add_filter(
				'posts_clauses',
				function ( $clauses ) {
					$_GET['__min_price'] = sanitize_text_field( wp_unslash( $_GET['min_price'] ) );// phpcs:ignore
					$_GET['__max_price'] = sanitize_text_field( wp_unslash( $_GET['max_price'] ) );// phpcs:ignore
					unset( $_GET['min_price'] );// phpcs:ignore WordPress.Security.NonceVerification
					unset( $_GET['max_price'] );// phpcs:ignore WordPress.Security.NonceVerification
					return $clauses;
				},
				5
			);
			add_filter(
				'posts_clauses',
				function ( $clauses ) {
					if ( isset( $_GET['__min_price'] ) || isset( $_GET['__max_price'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
						$_GET['min_price'] = ( sanitize_text_field( wp_unslash( $_GET['__min_price'] ) ) );// phpcs:ignore WordPress.Security.NonceVerification
						$_GET['max_price'] = sanitize_text_field( wp_unslash( $_GET['__max_price'] ) );// phpcs:ignore WordPress.Security.NonceVerification
					}
					unset( $_GET['__min_price'] );// phpcs:ignore WordPress.Security.NonceVerification
					unset( $_GET['__max_price'] );// phpcs:ignore WordPress.Security.NonceVerification
					return $clauses;
				},
				55
			);
			if ( ! isset( $product_ids[0] ) || empty( $product_ids[0] ) || is_null( $product_ids[0] ) ) {
				return '';
			}
		}
		return $product_ids;
	}

	/**
	 * Function alg_wc_cpp_min_price
	 *
	 * @param int $min_price Min Price.
	 */
	public function min_price( $min_price ) {
		$product_ids = wc_get_products(
			array(
				'return' => 'ids',
				'limit'  => -1,
			)
		);
		$min         = array();
		foreach ( $product_ids as $product_id ) {
			$product        = wc_get_product( $product_id );
			$product_status = $product->get_status();
			if ( 'publish' === $product_status ) {
				if ( $product->is_type( 'variable' ) ) {
					$price = $product->get_variation_price();
				} else {
					$price = $product->get_price();
				}
				if ( '' !== $price ) {
					$min[] = $price;
				}
			}
		}

		$min_price = min( $min );
		$steps     = max( apply_filters( 'woocommerce_price_filter_widget_step', 10 ), 1 );

		return ( floor( $min_price / $steps ) * $steps );
	}
	/**
	 * Function max_price
	 *
	 * @param int $max_price Max price.
	 */
	public function max_price( $max_price ) {
		$product_ids = wc_get_products(
			array(
				'return' => 'ids',
				'limit'  => -1,
			)
		);

		$max = array();

		foreach ( $product_ids as $product_id ) {
			$product        = wc_get_product( $product_id );
			$product_status = $product->get_status();
			if ( 'publish' === $product_status ) {
				if ( $product->is_type( 'variable' ) ) {
					$price = $product->get_variation_price( 'max' );
				} else {
					$price = $product->get_price();
				}
				if ( '' !== $price ) {
					$max[] = $price;
				}
			}
		}

		$max_price = max( $max );
		$steps     = max( apply_filters( 'woocommerce_price_filter_widget_step', 10 ), 1 );

		return ( ceil( $max_price / $steps ) * $steps );
	}

	/**
	 * Add_currency_code.
	 *
	 * Replaces placeholders in the custom currency symbol template with actual currency values.
	 *
	 * @version 1.4.1
	 * @since   1.4.1
	 *
	 * @param string $currency_symbol The currency symbol.
	 * @param string $currency The currency code.
	 * @return string The updated currency symbol template.
	 */
	public function add_currency_code( $currency_symbol, $currency ) {
		return str_replace( array( '%currency_code%', '%currency_symbol%' ), array( $currency, $currency_symbol ), $this->custom_currency_symbol_template );
	}

	/**
	 * Function price_filter_meta_query.
	 *
	 * Modifies the meta query for price filtering.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @param array  $meta_query The meta query.
	 * @param object $_wc_query The WooCommerce query object.
	 * @return array Modified meta query with converted price key.
	 */
	public function price_filter_meta_query( $meta_query, $_wc_query ) {
		if ( ! empty( $meta_query['price_filter']['price_filter'] ) ) {
			$meta_query['price_filter']['key'] = '_' . 'alg_wc_cpp_converted_price'; //phpcs:ignore
		}
		return $meta_query;
	}

	/**
	 * Removes sorting by price clauses filters from the WooCommerce query.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @param object $q The WP_Query object.
	 * @param object $_wc_query The WooCommerce query object.
	 */
	public function remove_sorting_by_price_posts_clauses_filters( $q, $_wc_query ) {
		remove_filter( 'posts_clauses', array( $_wc_query, 'order_by_price_desc_post_clauses' ) );
		remove_filter( 'posts_clauses', array( $_wc_query, 'order_by_price_asc_post_clauses' ) );
	}

	/**
	 * Adds sorting by converted price to the WooCommerce query.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @param array $args Query arguments for product sorting.
	 * @return array Modified query arguments with sorting by converted price.
	 */
	public function add_sorting_by_converted_price( $args ) {
		$wc_clean      = 'wc_clean';
		$orderby_value = ( isset( $_GET['orderby'] ) ? $wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) ) ); //phpcs:ignore
		$orderby_value = explode( '-', $orderby_value );
		$orderby       = esc_attr( $orderby_value[0] );
		$orderby       = strtolower( $orderby );
		if ( 'price' == $orderby ) { //phpcs:ignore
			$args['meta_key'] = '_' . 'alg_wc_cpp_converted_price'; //phpcs:ignore
			$args['orderby']  = 'meta_value_num';
		}
		return $args;
	}

	/**
	 * Adds the original product price (in a different currency) to the shop page.
	 *
	 * @version 1.4.2
	 * @since   1.4.0
	 *
	 * @param string $price_html The current product price HTML.
	 * @param object $product The WooCommerce product object.
	 * @return string Modified price HTML with the original price.
	 */
	public function add_original_price_in_shop( $price_html, $product ) {
		$product_id       = Functions::get_product_id( $product );
		$product_currency = $this->get_product_currency( $product->get_id() );
		if ( '' != $product_currency && get_woocommerce_currency() != $product_currency ) { //phpcs:ignore
			$template = Functions::get_behavior_setting( 'original_price_in_shop_template', '<br>%price%' );
			if ( $product->is_type( 'variable' ) ) {
				$variations = $product->get_visible_children();
				if ( empty( $variations ) ) {
					return $price_html;
				}
				foreach ( $variations as $product_id ) {
					$prices_raw[] = get_post_meta( $product_id, '_price', true );
				}
				$from = min( $prices_raw );
				$to   = max( $prices_raw );
				if ( $from === $to ) {
					$price_raw = $from;
					$price     = wc_price( $from, array( 'currency' => $product_currency ) );
				} else {
					// Translators: %1$s is the starting price, %2$s is the ending price.
					$price_raw = sprintf( _x( '%1$s &ndash; %2$s', 'Price range: from-to', 'woocommerce' ), $from, $to );
					$price     = sprintf(
						// Translators: %1$s is the starting price formatted with currency, %2$s is the ending price formatted with currency.
						_x( '%1$s &ndash; %2$s', 'Price range: from-to', 'woocommerce' ),
						is_numeric( $from ) ? wc_price( $from, array( 'currency' => $product_currency ) ) : $from,
						is_numeric( $to ) ? wc_price( $to, array( 'currency' => $product_currency ) ) : $to
					);
				}
			} elseif ( $product->is_type( 'booking' ) ) {
				$price_raw = get_post_meta( $product_id, '_wc_booking_cost', true );
				$price     = wc_price( $price_raw, array( 'currency' => $product_currency ) );

			} else {
				$price_raw = get_post_meta( $product_id, '_price', true );
				$price     = wc_price( $price_raw, array( 'currency' => $product_currency ) );
			}
			$replaced_values = array(
				'%currency_code%' => $product_currency,
				'%price_raw%'     => $price_raw,
				'%price%'         => $price,
			);
			$price_html     .= str_replace( array_keys( $replaced_values ), $replaced_values, $template );
		}
		return $price_html;
	}

	/**
	 * Get the display price of a product.
	 *
	 * @param WC_Product $product The product object.
	 * @param string     $price   The price to be displayed (optional).
	 * @param int        $qty     The quantity of the product (optional).
	 *
	 * @return float The display price of the product.
	 *
	 * @version 1.4.0
	 * @since   1.1.0
	 */
	public function get_product_display_price( $product, $price = '', $qty = 1 ) {
		return wc_get_price_to_display(
			$product,
			array(
				'price' => $price,
				'qty'   => $qty,
			)
		);
	}

	/**
	 * Change the price of grouped products.
	 *
	 * @param float      $price The price to be changed.
	 * @param int        $qty   The quantity of the product.
	 * @param WC_Product $product The product object.
	 *
	 * @return float The modified price.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	public function change_price_grouped( $price, $qty, $product ) {
		if ( $product->is_type( 'grouped' ) ) {
			foreach ( $product->get_children() as $child_id ) {
				$_product = wc_get_product( $child_id );
				$_price   = $this->get_product_display_price( $_product, get_post_meta( $child_id, '_price', true ), 1 );
				if ( $_price == $price ) { //phpcs:ignore
					return $this->change_price( $price, $_product );
				}
			}
		}
		$price = $this->cpp_rounded_price( $price );
		return $price;
	}

	/**
	 * Round the price according to settings.
	 *
	 * @param float $price The price to be rounded.
	 *
	 * @return float The rounded price.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	public static function cpp_rounded_price( $price ) {
		$is_whole_price = apply_filters( 'alg_wc_cpp_rounded_whole_price', false );
		if ( Functions::get_general_setting( 'round_off_decimal_points' ) ) {
			if ( $is_whole_price ) {
				$price = ceil( (float) $price );
			} else {
				$price = round( (float) $price, 1, PHP_ROUND_HALF_UP );
			}
		}

		return $price;
	}

	/**
	 * Get the hash for variation prices.
	 *
	 * @param array      $price_hash The existing price hash.
	 * @param WC_Product $_product   The product object.
	 * @param bool       $display   Whether to display prices.
	 *
	 * @return array The updated price hash.
	 *
	 * @version 1.4.0
	 * @since   1.1.0
	 */
	public function get_variation_prices_hash( $price_hash, $_product, $display ) {
		$price_hash['alg_wc_cpp']['currency']      = $this->get_product_currency( $_product->get_id() );
		$price_hash['alg_wc_cpp']['exchange_rate'] = Exchange_Rate_Functions::get_currency_exchange_rate( $price_hash['alg_wc_cpp']['currency'] );
		return $price_hash;
	}

	/**
	 * Fix the mini cart totals calculation.
	 *
	 * @return void
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	public function fix_mini_cart() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			if ( isset( WC()->cart ) ) {
				WC()->cart->calculate_totals();
			}
		}
	}

	/**
	 * Change shipping price based on cart and checkout settings.
	 *
	 * @param array $package_rates The existing package rates.
	 * @param array $package       The shipping package.
	 *
	 * @return array The modified package rates.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	public function change_shipping_price( $package_rates, $package ) {
		if ( $this->is_cart_or_checkout() ) {
			if ( WC()->cart->is_empty() ) {
				return $package_rates;
			}
			switch ( $this->cart_checkout_behaviour ) {
				case 'leave_one_product':
				case 'leave_same_currency':
				case 'convert_first_product':
				case 'convert_last_product':
					$shop_currency = get_option( 'woocommerce_currency' );
					if ( false != ( $_currency = $this->get_cart_checkout_currency() ) && $_currency != $shop_currency ) { //phpcs:ignore
						$currency_exchange_rate = Exchange_Rate_Functions::get_currency_exchange_rate( $_currency );
						if ( 0 != $currency_exchange_rate && 1 != $currency_exchange_rate ) { //phpcs:ignore
							$currency_exchange_rate = 1 / $currency_exchange_rate;
							$modified_package_rates = array();
							foreach ( $package_rates as $id => $package_rate ) {
								if ( isset( $package_rate->cost ) ) {
									$package_rate->cost = $package_rate->cost * $currency_exchange_rate;
									if ( isset( $package_rate->taxes ) && ! empty( $package_rate->taxes ) ) {
										foreach ( $package_rate->taxes as $tax_id => $tax ) {
											$package_rate->taxes[ $tax_id ] = $package_rate->taxes[ $tax_id ] * $currency_exchange_rate;
										}
									}
								}
								$modified_package_rates[ $id ] = $package_rate;
							}
							return $modified_package_rates;
						} else {
							return $package_rates;
						}
					} else {
						return $package_rates;
					}
				default:
					return $package_rates;
			}
		}
		return $package_rates;
	}

	/**
	 * Get the currency for a product based on various criteria.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return string The currency code for the product.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function get_product_currency( $product_id ) {
		// By users or user roles.
		$base_currency            = get_option( 'woocommerce_currency' );
		$do_check_by_users        = ( Functions::get_currencies_setting( 'by_users_enabled', false ) );
		$do_check_by_user_roles   = ( Functions::get_currencies_setting( 'by_user_roles_enabled', false ) );
		$do_check_by_product_cats = ( Functions::get_currencies_setting( 'by_product_cats_enabled', false ) );
		$do_check_by_product_tags = ( Functions::get_currencies_setting( 'by_product_tags_enabled', false ) );
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$variation_currency = get_post_meta( $product_id, '_alg_wc_variation_cpp_currency', true );
			$parent_id          = wp_get_post_parent_id( $product_id );
			if ( ! empty( $variation_currency ) ) {
				return $variation_currency;
			}
			$product_id = $parent_id;
		}
		if ( $do_check_by_users || $do_check_by_user_roles || $do_check_by_product_cats || $do_check_by_product_tags ) {
			if ( $do_check_by_users || $do_check_by_user_roles ) {
				$product_author_id = get_post_field( 'post_author', $product_id );
			}
			if ( $do_check_by_product_cats ) {
				$_product_cats = Functions::get_product_terms( $product_id, 'product_cat' );
			}
			if ( $do_check_by_product_tags ) {
				$_product_tags = Functions::get_product_terms( $product_id, 'product_tag' );
			}
			$currencies = Functions::get_currencies_setting( 'currencies', array() );
			foreach ( $currencies as $entry ) {
				$currency = $entry['currency'] ?? $base_currency;
				if ( $do_check_by_users ) {
					$users = $entry['users'] ?? array();
					if ( ! empty( $users ) && in_array( (int) $product_author_id, array_map( 'intval', $users ) ) ) { //phpcs:ignore
						return $currency;
					}
				}
				if ( $do_check_by_user_roles ) {
					$user_roles = $entry['user_roles'] ?? array();
					if ( ! empty( $user_roles ) && Functions::has_user_role( $user_roles, $product_author_id ) ) {
						return $currency;
					}
				}
				if ( $do_check_by_product_cats ) {
					$product_cats = array_column( $entry['product_cats'] ?? array(), 'value' );
					if ( ! empty( $_product_cats ) && ! empty( $product_cats ) ) {
						if ( ! empty( array_intersect( $_product_cats, $product_cats ) ) ) {
							return $currency;
						}
					}
				}
				if ( $do_check_by_product_tags ) {
					$product_tags = array_column( $entry['product_tags'] ?? array(), 'value' );
					if ( ! empty( $_product_tags ) && ! empty( $product_tags ) ) {
						if ( ! empty( array_intersect( $_product_tags, $product_tags ) ) ) {
							return $currency;
						}
					}
				}
			}
		}
		$product_meta_currency = get_post_meta( $product_id, '_' . 'alg_wc_cpp_currency', true ); //phpcs:ignore
		if ( ! empty( $product_meta_currency ) ) {
			return $product_meta_currency;
		}
		return $base_currency;
	}

	/**
	 * Validate_on_add_to_cart.
	 *
	 * @param Array  $passed - passed.
	 * @param  Object $product_id - id of product.
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	public function validate_on_add_to_cart( $passed, $product_id, $quantity, $variation_id = 0, $variations = null ) {
		$actual_product_id = ( $variation_id && $variation_id > 0 ) ? $variation_id : $product_id;
		if ( 'leave_one_product' === $this->cart_checkout_behaviour ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				if ( $cart_item['product_id'] != $product_id ) { //phpcs:ignore
					wc_add_notice(
						get_option(
							'alg_wc_cpp_cart_checkout_leave_one_product',
							__(
								'Only one product can be added to the cart. Clear the cart or finish the order, before adding another product to the cart.',
								'currency-per-product-for-woocommerce'
							)
						),
						'error'
					);
					return false;
				}
			}
		} elseif ( 'leave_same_currency' === $this->cart_checkout_behaviour ) {
			$shop_currency    = get_option( 'woocommerce_currency' );
			$product_currency = $this->get_product_currency( $actual_product_id );
			if ( '' == $product_currency ) { //phpcs:ignore
				$product_currency = $shop_currency;
			}
			if ( WC()->cart->get_cart_contents_count() != 0 ) { //phpcs:ignore
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					$cart_product_currency = ( isset( $cart_item['alg_wc_cpp'] ) && '' != $cart_item['alg_wc_cpp'] ) ? //phpcs:ignore
					$cart_item['alg_wc_cpp'] : $shop_currency;
					break;
				}
				if ( '' == $cart_product_currency ) { //phpcs:ignore
					$cart_product_currency = $shop_currency;
				}
				if ( $cart_product_currency != $product_currency ) { //phpcs:ignore
					$clear_cart = apply_filters( 'alg_wc_cpp_handle_currency_mismatch', false, $cart_product_currency, $product_currency );
					if ( $clear_cart ) {
						WC()->cart->empty_cart();
						wc_add_notice(
							__( 'Your cart contained products in a different currency. They have been removed, and your new product has been added.', 'currency-per-product-for-woocommerce' ),
							'notice'
						);
						return true;
					} else {
						wc_add_notice(
							__( 'Only products with the same currency can be added to the cart. Clear the cart or complete the order before adding products in another currency.', 'currency-per-product-for-woocommerce' ),
							'error'
						);
						return false;
					}
				}
			}
		}
		return $passed;
	}

	/**
	 * Get the HTML for grouped product price range.
	 *
	 * @param string     $price_html The original price HTML.
	 * @param WC_Product $_product   The product object.
	 *
	 * @return string The HTML for the grouped product price range.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function grouped_price_html( $price_html, $_product ) {
		$child_prices = array();
		foreach ( $_product->get_children() as $child_id ) {
			$child_prices[ $child_id ] = get_post_meta( $child_id, '_price', true );
		}
		if ( ! empty( $child_prices ) ) {
			asort( $child_prices );
			$min_price    = current( $child_prices );
			$min_price_id = key( $child_prices );
			end( $child_prices );
			$max_price        = current( $child_prices );
			$max_price_id     = key( $child_prices );
			$min_cpp_currency = $this->get_product_currency( $min_price_id );
			$max_cpp_currency = $this->get_product_currency( $max_price_id );
		} else {
			$min_price = '';
			$max_price = '';
		}

		if ( $min_price ) {
			if ( $min_price == $max_price && $min_cpp_currency === $max_cpp_currency ) { //phpcs:ignore
				$display_price = wc_price( Functions::get_product_display_price( $_product, $min_price, 1 ), array( 'currency' => $min_cpp_currency ) );
			} else {
				$from = wc_price( Functions::get_product_display_price( $_product, $min_price, 1 ), array( 'currency' => $min_cpp_currency ) );
				$to   = wc_price( Functions::get_product_display_price( $_product, $max_price, 1 ), array( 'currency' => $max_cpp_currency ) );
				// Translators: %1$s is the formatted price of the lowest priced item.
				$display_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), $from, $to );
			}
			$new_price_html = $display_price . $_product->get_price_suffix();
			return $new_price_html;
		}

		return $price_html;
	}

	/**
	 * Change the price of a product in the cart.
	 *
	 * @param float      $price    The price to be changed.
	 * @param WC_Product $_product The product object.
	 *
	 * @return float The modified price.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	public function change_price( $price, $_product ) {
		if ( '' === $price ) {
			return $price;
		}
		$cart = WC()->cart;
		if ( ! is_null( $cart ) ) {
			$cart_items = $cart->get_cart();
			foreach ( $cart_items as $cart_item ) {
				if ( $_product->get_id() === $cart_item['data']->get_id() ) {
					if ( $this->is_cart_or_checkout() ) {
						$alg_wc_cpp_curr = isset( $cart_item['alg_wc_cpp_cur'] ) ? $cart_item['alg_wc_cpp_cur'] : '';
					}
					break;
				}
			}
		}

		if ( did_action( 'wp_ajax_woocommerce_do_ajax_product_export' ) ) {
			return $price;
		}

		$do_save_prices = Functions::get_advanced_setting( 'save_products_prices', false );

		if ( $do_save_prices ) {
			$product_id = Functions::get_product_id( $_product );
		}

		if ( isset( $alg_wc_cpp_curr ) ) {
			switch ( $this->cart_checkout_behaviour ) {
				case 'leave_one_product':
				case 'leave_same_currency':
					$shop_currency    = get_option( 'woocommerce_currency' );
					$cart_currency    = $this->get_cart_checkout_currency();
					$product_currency = $this->get_product_currency( $_product->get_id() );
					if ( $cart_currency != $product_currency ) { //phpcs:ignore
						if ( $do_save_prices && isset( $this->saved_prices['shop'][ $product_id ] ) ) {
							return $this->saved_prices['shop'][ $product_id ];
						}
						$exchange_rate          = Exchange_Rate_Functions::get_currency_exchange_rate( $cart_currency );
						$currency_exchange_rate = 1 / $exchange_rate;
						$return_price           = (float) $price * $currency_exchange_rate;
						$return_price           = $this->cpp_rounded_price( $return_price );
						if ( $do_save_prices ) {
							$this->saved_prices['shop'][ $product_id ] = $return_price;
						}
						return $return_price;
					}
					return $price;
				case 'convert_first_product':
				case 'convert_last_product':
					$shop_currency = get_option( 'woocommerce_currency' );
					if ( false != ( $_currency = $this->get_cart_checkout_currency() ) && $_currency != $shop_currency ) {  //phpcs:ignore
						if ( $alg_wc_cpp_curr === $_currency ) {
							return $price;
						} else {
							if ( $do_save_prices && isset( $this->saved_prices['cart_checkout'][ $product_id ] ) ) {
								return $this->saved_prices['cart_checkout'][ $product_id ];
							}
							$exchange_rate_product       = Exchange_Rate_Functions::get_currency_exchange_rate( $alg_wc_cpp_curr );
							$exchange_rate_cart_checkout = Exchange_Rate_Functions::get_currency_exchange_rate( $_currency );
							$exchange_rate               = $exchange_rate_product / $exchange_rate_cart_checkout;
							$return_price                = $price * $exchange_rate;
							$return_price                = $this->cpp_rounded_price( $return_price );
							if ( $do_save_prices ) {
								$this->saved_prices['cart_checkout'][ $product_id ] = $return_price;
							}
							return $return_price;
						}
					} elseif ( $alg_wc_cpp_curr === $shop_currency ) {
						return $price;
					} else {
						if ( $do_save_prices && isset( $this->saved_prices['cart_checkout'][ $product_id ] ) ) {
							return $this->saved_prices['cart_checkout'][ $product_id ];
						}
						$exchange_rate = Exchange_Rate_Functions::get_currency_exchange_rate( $alg_wc_cpp_curr );
						$return_price  = $price * $exchange_rate;
						$return_price  = $this->cpp_rounded_price( $return_price );
						if ( $do_save_prices ) {
							$this->saved_prices['cart_checkout'][ $product_id ] = $return_price;
						}
						return $return_price;
					}
				default: // It will come here in the case of convert_shop_default.
					if ( apply_filters( 'alg_wc_cpp_convert_shop_default', true, $this ) ) {
						if ( $do_save_prices && isset( $this->saved_prices['cart_checkout'][ $product_id ] ) ) {
							return $this->saved_prices['cart_checkout'][ $product_id ];
						}
						if ( 'show_in_different' === Functions::get_behavior_setting( 'shop_behaviour', 'show_in_different' ) ) {
							$exchange_rate = Exchange_Rate_Functions::get_currency_exchange_rate( $alg_wc_cpp_curr );
						} else {
							$cart_currency = $this->get_cart_checkout_currency();
							$exchange_rate = Exchange_Rate_Functions::get_currency_exchange_rate( $alg_wc_cpp_curr );
						}
						$return_price = (float) $price * $exchange_rate;
						$return_price = $this->cpp_rounded_price( $return_price );
						if ( $do_save_prices ) {
							$this->saved_prices['cart_checkout'][ $product_id ] = $return_price;
						}
						return $return_price;
					}
			}
		} elseif ( $this->convert_in_shop ) {
			$product_currency = $this->get_product_currency( $_product->get_id() );
			$shop_currency    = get_option( 'woocommerce_currency' );
			if ( '' === $price || $product_currency === $shop_currency ) {
				return $price;
			}
			if ( $do_save_prices && isset( $this->saved_prices['shop'][ $product_id ] ) ) {
				return $this->saved_prices['shop'][ $product_id ];
			}
			$exchange_rate = Exchange_Rate_Functions::get_currency_exchange_rate( $this->get_product_currency( $_product->get_id() ) );

			$return_price = (float) $price * $exchange_rate;
			$return_price = $this->cpp_rounded_price( $return_price );
			if ( $do_save_prices ) {
				$this->saved_prices['shop'][ $product_id ] = $return_price;
			}
			return $return_price;
		} else {
			// will come here when shop behaviour is set to show_in_different (show prices in different currencies ).
			// and when the product does not have a currency set.
			switch ( $this->cart_checkout_behaviour ) {
				case 'convert_first_product':
				case 'convert_last_product':
					$shop_currency = get_option( 'woocommerce_currency' );

					$_currency = $this->get_cart_checkout_currency();
					if ( false !== $_currency && ! isset( $alg_wc_cpp_curr ) && $_currency !== $shop_currency && $this->is_cart_or_checkout() ) {

						if ( $do_save_prices && isset( $this->saved_prices['shop'][ $product_id ] ) ) {
							return $this->saved_prices['shop'][ $product_id ];
						}

						$exchange_rate          = Exchange_Rate_Functions::get_currency_exchange_rate( $_currency );
						$currency_exchange_rate = 1 / $exchange_rate;

						$return_price = (float) $price * $currency_exchange_rate;
						$return_price = $this->cpp_rounded_price( $return_price );
						if ( $do_save_prices ) {
							$this->saved_prices['shop'][ $product_id ] = $return_price;
						}
						return $return_price;
					}
					break;
				case 'leave_same_currency':
					if ( is_checkout() || is_page( 'cart' ) ) {
						$shop_currency    = get_option( 'woocommerce_currency' );
						$cart_currency    = $this->get_cart_checkout_currency();
						$product_currency = $this->get_product_currency( $_product->get_id() );
						if ( $cart_currency != $product_currency ) { //phpcs:ignore
							if ( $do_save_prices && isset( $this->saved_prices['shop'][ $product_id ] ) ) {
								return $this->saved_prices['shop'][ $product_id ];
							}
							$exchange_rate          = Exchange_Rate_Functions::get_currency_exchange_rate( $cart_currency );
							$currency_exchange_rate = 1 / $exchange_rate;
							$return_price           = (float) $price * $currency_exchange_rate;
							$return_price           = $this->cpp_rounded_price( $return_price );
							if ( $do_save_prices ) {
								$this->saved_prices['shop'][ $product_id ] = $return_price;
							}
							return $return_price;
						}
					}
			}
		}
		$price = $this->cpp_rounded_price( $price );
		return $price;
	}

	public function cpp_override_price_html_for_shop_page( $price_html, $product ) {
		if ( is_admin() ) {
			return $price_html;
		}

		if ( is_product() || is_cart() || is_checkout() ) {
			return $price_html;
		}
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_post_type_archive( 'product' ) ) {
			return $price_html;
		}
		if ( ! is_a( $product, 'WC_Product_Variable' ) ) {
			return $price_html;
		}
		$selected_currency = WC()->session->get( 'alg_wc_cpp_selected_currency' );
		if ( ! $selected_currency ) {
			return $price_html;
		}
		$product_currency = $this->get_product_currency( $product->get_id() );
		if ( ! $product_currency ) {
			$product_currency = get_option( 'woocommerce_currency' );
		}
		if ( $selected_currency === $product_currency ) {
			return $price_html;
		}
		$exchange_rate_product = Exchange_Rate_Functions::get_currency_exchange_rate( $product_currency );
		$exchange_rate_target  = Exchange_Rate_Functions::get_currency_exchange_rate( $selected_currency );
		if ( $exchange_rate_product <= 0 || $exchange_rate_target <= 0 ) {
			return $price_html;
		}
		$exchange_rate       = $exchange_rate_product / $exchange_rate_target;
		$min_price           = $product->get_variation_price( 'min', true );
		$max_price           = $product->get_variation_price( 'max', true );
		$min_price_converted = $this->cpp_rounded_price( $min_price * $exchange_rate );
		$max_price_converted = $this->cpp_rounded_price( $max_price * $exchange_rate );
		if ( $min_price_converted === $max_price_converted ) {
			$price_html = wc_price( $min_price_converted, [ 'currency' => $selected_currency ] );
		} else {
			$price_html = wc_format_price_range(
				wc_price( $min_price_converted, [ 'currency' => $selected_currency ] ),
				wc_price( $max_price_converted, [ 'currency' => $selected_currency ] )
			);
		}
		return $price_html;
	}

	/**
	 * Change the booking cost for the number of persons displayed on the Booking product page from the WooCommerce Bookings plugin.
	 *
	 * @param float      $price   The original price of the booking.
	 * @param WC_Product $product The WooCommerce product object.
	 * @param array      $posted  The posted data related to the booking.
	 *
	 * @return float The modified price after applying the currency conversion.
	 *
	 * @version 1.4.5
	 * @since   1.4.5
	 */
	public function change_booking_price( $price, $product, $posted ) {
		if ( $this->convert_in_shop && wp_doing_ajax() ) {
			if ( isset( $product->product ) ) {
				$product_id    = Functions::get_product_id_or_variation_parent_id( $product->product );
				$exchange_rate = Exchange_Rate_Functions::get_currency_exchange_rate( $this->get_product_currency( $product_id ) );
			} else {
				$exchange_rate = 1;
			}
			$price = $price * $exchange_rate;
		}
		return $price;
	}

	/**
	 * Get_cart_item_from_session.
	 *
	 * @param object $item product object.
	 * @param array  $values values.
	 * @param string $key array key.
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function get_cart_item_from_session( $item, $values, $key ) {
		if ( array_key_exists( 'alg_wc_cpp', $values ) ) {
				$item['alg_wc_cpp_cur'] = $values['alg_wc_cpp'];
		}
		return $item;
	}

	/**
	 * Add custom data to the cart item.
	 *
	 * @param array $cart_item_data The current cart item data.
	 * @param int   $product_id     The ID of the product being added to the cart.
	 * @param int   $variation_id   The ID of the product variation (if applicable).
	 *
	 * @return array Modified cart item data with custom data added.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		$cpp_currency = $this->get_product_currency( $variation_id ? $variation_id : $product_id );
		if ( '' != $cpp_currency ) {  //phpcs:ignore
			$cart_item_data['alg_wc_cpp'] = $cpp_currency;
		}
		return $cart_item_data;
	}

	/**
	 * Add custom cart item data when the item is added to the cart.
	 *
	 * @param array  $cart_item_data The current cart item data.
	 * @param string $cart_item_key The cart item key.
	 *
	 * @return array Modified cart item data with additional currency data.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function add_cart_item( $cart_item_data, $cart_item_key ) {
		if ( isset( $cart_item_data['alg_wc_cpp'] ) ) {
			$cart_item_data['alg_wc_cpp_cur'] = $cart_item_data['alg_wc_cpp'];
		}
		return $cart_item_data;
	}

	/**
	 * Get the current product ID and its currency.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function get_current_product_id_and_currency() {
		// Get ID.
		$product_id = false;
		// Get ID - Global product.
		global $product;
		if ( is_object( $product ) ) {
			$product_id = Functions::get_product_id_or_variation_parent_id( $product );
		}
		// Get ID - product_id in _REQUEST.
		if ( ! $product_id && isset( $_REQUEST['product_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$product_id = sanitize_text_field( wp_unslash( $_REQUEST['product_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
		// Get ID - WooCommerce Bookings plugin.
		if ( ! $product_id && isset( $_POST['form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$posted = array();
			if ( is_string( sanitize_text_field( wp_unslash( $_POST['form'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				parse_str( sanitize_text_field( wp_unslash( $_POST['form'] ) ), $posted ); // phpcs:ignore WordPress.Security.NonceVerification
				$product_id = isset( $posted['add-to-cart'] ) ? $posted['add-to-cart'] : 0;
			}
		}
		// Get ID - EventON plugin.
		$eventon_wc_product_id = get_post_meta( get_the_ID(), 'tx_woocommerce_product_id', true );
		if ( ! $product_id && '' !== $eventon_wc_product_id ) {
			$product_id = $eventon_wc_product_id;
		}
		// Get ID - final fallback - get_the_ID.
		if ( ! $product_id && ! is_shop() ) {
			$product_id = get_the_ID();
		}
		// Get currency.
		if ( $product_id && 'product' === get_post_type( $product_id ) ) {
			$cpp_currency = $this->get_product_currency( $product_id );
			if ( $this->is_allowed_page() ) {
				if ( '' !== $cpp_currency ) {
					// Save to Session Variable for future use and make sure it is not set in checkout page.
					if ( ! is_admin() ) {
						if ( ! is_checkout() ) {
							WC()->session->set( 'alg_wc_cpp_product_currency', $cpp_currency );
						} else {
							// Unset Session Variable in case it has alreay been set earlier.
							WC()->session->set( 'alg_wc_cpp_product_currency', null );
						}
					}
				}
			}
			return ( '' != $cpp_currency ) ? $cpp_currency : false; //phpcs:ignore
		}
		return apply_filters( 'alg_wc_cpp_get_order_currency', false, $product_id );
	}

	/**
	 * Get the current cart checkout currency.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	public function get_cart_checkout_currency() {
		if ( is_object( WC()->cart ) && empty( WC()->cart->get_cart_contents() ) ) {
			return false;
		}

		if ( ! isset( WC()->cart ) || WC()->cart->is_empty() ) {
			return false;
		}
		if ( 'convert_shop_default' === $this->cart_checkout_behaviour ) {

			return apply_filters( 'alg_wc_cpp_get_currency_convert_shop_default', false );
		}
		$cart_items = WC()->cart->get_cart();
		if ( 'convert_last_product' === $this->cart_checkout_behaviour ) {
			$cart_items = array_reverse( $cart_items );
		}
		foreach ( $cart_items as $cart_item ) {
			return ( isset( $cart_item['alg_wc_cpp'] ) ) ? $cart_item['alg_wc_cpp'] : false;
		}
	}

	/**
	 * Get the cart or checkout.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    [dev] maybe need to check for AJAX also
	 */
	public function is_cart_or_checkout() {
		if ( wp_doing_ajax() && isset( $_REQUEST['wc-ajax'] ) ) { // phpcs:ignore
			$wc_ajax = $_REQUEST['wc-ajax']; // phpcs:ignore
			if ( in_array( $wc_ajax, array( 'add_to_cart', 'get_refreshed_fragments', 'remove_from_cart', 'restore_cart_item', 'update_order_review', 'update_shipping_method', 'nasa_quantity_mini_cart' ), true ) ) {
				return true;
			}
		}
		if ( ! $this->is_allowed_page() ) {
			return true;
		} else {
			return ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) || has_block( 'woocommerce/checkout' ) || has_block( 'woocommerce/cart' ) );
		}
	}

	/**
	 * Function change_currency_code.
	 *
	 * @param string $currency - Currency.
	 * @version 1.4.1
	 * @since   1.0.0
	 */
	public function change_currency_code( $currency ) {
		$shop_behaviour      = Functions::get_behavior_setting( 'shop_behaviour', '' );
		$cart_behaviour      = Functions::get_behavior_setting( 'cart_checkout', 'convert_shop_default' );
		$default_currency    = get_option( 'woocommerce_currency' );
		$selected_currency   = null;
		$is_cart_or_checkout = $this->is_cart_or_checkout();

		if ( $is_cart_or_checkout || ( wp_doing_ajax() && isset( $_REQUEST['wc-ajax'] ) && 'get_refreshed_fragments' === $_REQUEST['wc-ajax'] ) ) { // phpcs:ignore
			if ( 'convert_shop_default' === $cart_behaviour ) {
				return $default_currency;
			}
			$cart_checkout_currency = $this->get_cart_checkout_currency();
			return ( false !== $cart_checkout_currency ) ? $cart_checkout_currency : $default_currency;
		}

		$cart_checkout_currency          = $this->get_cart_checkout_currency();
		$current_product_id_and_currency = $this->get_current_product_id_and_currency();
		if ( $this->convert_in_shop ) {
			// When shop behaviour is set to Convert to shop currency.
			return $currency;
		} elseif ( $this->is_cart_or_checkout() ) {
			// Currency for cart/checkout page.
			if ( false !== ( $cart_checkout_currency ) ) {
				return $cart_checkout_currency;
			}
		} elseif ( wp_doing_ajax() && false !== ( $current_product_id_and_currency ) && 'convert_shop_default' === $this->cart_checkout_behaviour ) {
			// When Cart/Checkout behaviour set to Convert to shop default currency.
			return $currency;
		} elseif ( wp_doing_ajax() && false === ( $cart_checkout_currency ) && 'convert_first_product' === $this->cart_checkout_behaviour ) {
			// When Cart/Checkout behaviour set to Convert to first product currency and when first product added have base currency.
			return $currency;
		} elseif ( false !== ( $current_product_id_and_currency ) ) {
			// Currency for the every product.
			return $current_product_id_and_currency;
		} elseif ( wp_doing_ajax() && false !== ( $cart_checkout_currency ) ) {
			// When Cart/Checkout behaviour set to Convert to first/last product currency.
			return $cart_checkout_currency;
		}

		return $currency;
	}

	/**
	 * Get enabled currencies.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function get_enabled_currencies() {
		$currencies = Functions::get_currencies_setting( 'currencies', array() );
		return array_filter( array_column( $currencies, 'currency' ) );
	}

	/**
	 * For showing correct currency on custom page for the products shown using Woo blocks.
	 *
	 * @param string $price_html HTML Price.
	 * @param object $product Product object.
	 */
	public function cpp_change_currency_for_featured_products_block( $price_html, $product ) {
		if ( 'show_in_different' === Functions::get_behavior_setting( 'shop_behaviour', 'show_in_different' ) ) {
			if ( ! $this->is_allowed_page() || ( 'page' === get_post_type() && is_page() && 'job_package' === $product->get_type() ) ) {
				if ( is_user_logged_in() ) {
					$vendor_id = get_current_user_id();
					$page_id   = get_queried_object_id();
					$seller    = get_post_field( 'post_author', $page_id );
					if ( function_exists( 'dokan' ) ) {
						$vendor = dokan()->vendor->get( $seller );
						if ( ! empty( $vendor ) ) {
							return $price_html;
						}
					}
				}
				$product_id = Functions::get_product_id_or_variation_parent_id( $product );
				if ( $product_id && 'product' === get_post_type( $product_id ) ) {
					$cpp_currency = $this->get_product_currency( $product_id );
					if ( '' !== $cpp_currency ) {
						if ( 'variable' === $product->get_type() ) {
							$prices = array( $product->get_variation_price( 'min', true ), $product->get_variation_price( 'max', true ) );
							if ( ! empty( $prices ) ) {
								$min_price = isset( $prices[0] ) ? $prices[0] : 0;
								$max_price = isset( $prices[1] ) ? $prices[1] : 0;

								if ( $min_price !== $max_price ) {
									$price = wc_format_price_range( wc_price( $min_price, array( 'currency' => $cpp_currency ) ), wc_price( $max_price, array( 'currency' => $cpp_currency ) ) );
									return $price;
								} elseif ( $product->is_on_sale() && $min_price === $max_price ) {
									$price = wc_format_sale_price( wc_price( $max_price, array( 'currency' => $cpp_currency ) ), wc_price( $min_price, array( 'currency' => $cpp_currency ) ) );
									return $price;
								} else {
									$price = wc_price( $min_price, array( 'currency' => $cpp_currency ) );
									return $price;
								}
							}
						} else {
							$regular_price = $product->get_regular_price();
							$sale_price    = $product->get_sale_price();
							if ( '' !== $sale_price && $sale_price < $regular_price ) {
								$price = wc_format_sale_price( wc_price( $regular_price, array( 'currency' => $cpp_currency ) ), wc_price( $sale_price, array( 'currency' => $cpp_currency ) ) );
								return $price;
							} else {
								$price = wc_price( $regular_price, array( 'currency' => $cpp_currency ) );
								return $price;
							}
						}
					}
				}
				return $price_html;
			}
		}
		return $price_html;
	}

	/**
	 * This function will enqueue and load the JS script needed to fix the correct display of currency on the Product and Cart Page.
	 */
	public function fix_cart_fragments() {
		// Enqueue script only on the Product page, Cart Page and Checkout Page only.
		if ( $this->is_allowed_page() ) {
			$plugin_url   = plugins_url() . '/currency-per-product-for-woocommerce';
			// Remove & from the currency string because for some reason, WordPress converts the currency string to unicode and we do not want that.
			$currency_symbol = str_replace( '&', '', $this->get_currency_symbol() );

			wp_enqueue_script(
				'fix_cart_fragments',
				$plugin_url . '/assets/js/fix_cart_fragments.js',
				'',
				CPP_VERSION,
				false
			);

			wp_localize_script(
				'fix_cart_fragments',
				'fix_cart_fragments_params',
				array(
					'currency_symbol' => $currency_symbol,
					'do_run'          => ( $this->is_allowed_page( false ) ? 'yes' : 'no' ),
				)
			);

			wp_enqueue_script(
				'cpp-frontend',
				$plugin_url . '/assets/js/cpp-frontend.js',
				array( 'jquery' ),
				CPP_VERSION,
				true
			);
			global $product;
			$exchange_rates    = Functions::get_all_exchange_rates_variation();
			$product_currency  = $this->get_current_product_id_and_currency();
			$shop_behaviour    = Functions::get_behavior_setting( 'shop_behaviour', '' );
			$selected_currency = null;
			if ( WC()->session && method_exists( WC()->session, 'get' ) ) {
				$selected_currency = WC()->session->get( 'alg_wc_cpp_selected_currency' );
			}
			if ( 'user_selected_currency' === $shop_behaviour && $selected_currency ) {
				$product_currency = $selected_currency;
			}
			$currency_symbols = html_entity_decode( get_woocommerce_currency_symbol( $product_currency ), ENT_QUOTES, 'UTF-8' );

			wp_localize_script(
				'cpp-frontend',
				'CPP_Data',
				array(
					'exchangeRates'   => $exchange_rates,
					'productCurrency' => $product_currency,
					'currency_symbol' => $currency_symbols,
					'shop_behaviour'  => Functions::get_behavior_setting( 'shop_behaviour', 'show_in_different' ),
					'do_run'          => ( $this->is_allowed_page( false ) ? 'yes' : 'no' ),
				)
			);
			$enable_block_original_price = Functions::get_behavior_setting( 'original_price_in_cart_checkout_enabled', false ) && 'show_in_different' !== Functions::get_behavior_setting( 'shop_behaviour', 'show_in_different' );
			if ( $enable_block_original_price ) {
				$block_original_prices = array();
				if ( WC()->cart ) {
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$product_id = ! empty( $cart_item['variation_id'] )
							? $cart_item['variation_id']
							: $cart_item['product_id'];
						$product    = wc_get_product( $product_id );
						if ( ! $product ) {
							continue;
						}
						$product_currency = $this->get_product_currency( $product_id );
						if ( '' === $product_currency || get_woocommerce_currency() === $product_currency ) {
							continue;
						}
						$price_raw = get_post_meta(
							Functions::get_product_id( $product ),
							'_price',
							true
						);
						$block_original_prices[ $cart_item_key ] = array(
							'price_raw' => $price_raw,
							'price'     => wc_price(
								$price_raw,
								array( 'currency' => $product_currency )
							),
							'currency'  => $product_currency,
						);
					}
				}
				wp_enqueue_script(
					'cpp-block-dom',
					$plugin_url . '/assets/js/cpp-block-dom.js',
					array(),
					CPP_VERSION,
					true
				);
				wp_localize_script(
					'cpp-block-dom',
					'ALG_CPP_BLOCK_DATA',
					array(
						'items' => $block_original_prices,
						'doRun' => ( $this->is_allowed_page( false ) ? 'yes' : 'no' ),
					)
				);
			}
		}
	}

	/**
	 * This function returns the currency symbol.
	 */
	public function get_currency_symbol() {
		$currency = $this->change_currency_code( get_option( 'woocommerce_currency' ) );
		$symbols  = get_woocommerce_currency_symbols();
		return $symbols[ $currency ];
	}

	/**
	 * This function returns the currency that is attached to the price and displayed on the Product Page including the Mini Cart.
	 *
	 * @param  array $args Arguments to format a price.
	 * @return array $args Arguments.
	 */
	public function show_currency_when_price_is_calculated( $args ) {
		global $product;

		// Ensure the function is called on valid pages where Session Variables are available.
		if ( $this->is_allowed_page() ) {

			// Filter to enable this functionality to show product currency on Product and Cart Page.
			if ( apply_filters( 'alg_wc_cpp_show_converted_currency_at_cart', false ) ) {
				$product_id       = Functions::get_product_id_or_variation_parent_id( $product );
				$product_currency = get_post_meta( $product_id, '_' . 'alg_wc_cpp_currency', true ); //phpcs:ignore

				// Must not act on Shop Page to prevent messing with currency symbols in the Shop Page.
				if ( isset( $product_currency ) && '' !== $product_currency && ! is_shop() ) {
					$args['currency'] = $product_currency;
				}
			}
		}
		return $args;
	}

	/**
	 * This function unsets the currency that hd earlier been set when on the checkout page. This is to enable the correct/converted urrency and price to be displayed.
	 */
	public function unset_saved_currency_at_checkout() {
		if ( is_checkout() ) {
			// Unset Session Variable in case it has alreay been set earlier.
			WC()->session->set( 'alg_wc_cpp_product_currency', null );
		}
	}

	/**
	 * Checks if the current page is allowed for the currency change function.
	 *
	 * This function determines whether the current page is one where currency change functionality should be allowed. It considers various page types, including product pages, the cart, checkout, product categories, and specific pages. AJAX requests for refreshing fragments are also allowed.
	 *
	 * @param bool $return_on_checkout Whether to allow the checkout page. Defaults to true.
	 *
	 * @return bool True if the current page is allowed for currency changes, false otherwise.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function is_allowed_page( $return_on_checkout = true ) {
		global $wp_query;

		// Stop for all admin pages.
		if ( is_admin() ) {
			if ( get_post_type( get_the_ID() ) == 'product' ) {//phpcs:ignore
				return true;
			} else {
				return;
			}
		}

		if ( isset( $wp_query ) ) {
			if ( isset( $wp_query->query_vars['post_type'] ) ) {
				if ( 'product' === $wp_query->query_vars['post_type'] ) {
					return true;
				}
			}

			// Allow AJAX requests as we feel it won't chnage structure of the page.
			if ( isset( $wp_query->query_vars['wc-ajax'] ) ) {
				if ( 'get_refreshed_fragments' === $wp_query->query_vars['wc-ajax'] ) {
					return true;
				}
			}
		}

		if ( is_cart() ) {
			return true;
		}

		if ( $return_on_checkout ) {
			if ( is_checkout() ) {
				return true;
			}
		}
		if ( is_product_category() ) {
			return true;
		}
		if ( is_page() ) {
			return true;
		}

		return false;
	}
}

return new Frontend();
