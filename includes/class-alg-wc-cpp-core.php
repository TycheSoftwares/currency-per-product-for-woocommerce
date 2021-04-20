<?php
/**
 * Currency per Product for WooCommerce - Core Class
 *
 * @version 1.4.2
 * @since   1.0.0
 * @author  Tyche Softwares
 * @package currency-per-product-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_CPP_Core' ) ) :

	/**
	 * Main Alg_WC_CPP_Core Class
	 *
	 * @class   Alg_WC_CPP_Core
	 */
	class Alg_WC_CPP_Core {

		/**
		 * Constructor.
		 *
		 * @version 1.4.1
		 * @since   1.0.0
		 * @todo    [dev] store options in arrays (e.g. `"alg_wc_cpp_currency[{$i}]"` instead of `'alg_wc_cpp_currency_' . $i` and `"alg_wc_cpp_exchange_rate[{$currency_code}]"` instead of `'alg_wc_cpp_exchange_rate_' . $i`)
		 * @todo    [feature] add "price formats" subsection
		 */
		public function __construct() {

			// Admin.
			if ( is_admin() ) {
				require_once 'class-alg-wc-cpp-admin.php';
			}

			if ( 'yes' === get_option( 'alg_wc_cpp_enabled', 'yes' ) ) {

				// Crons.
				require_once 'class-alg-wc-cpp-crons.php';

				// Product edit page meta box.
				if ( is_admin() ) {
					require_once 'settings/class-alg-wc-cpp-metaboxes.php';
				}

				// Behaviour options.
				$this->convert_in_shop         = ( 'convert_shop_default' === get_option( 'alg_wc_cpp_shop_behaviour', 'show_in_different' ) );
				$this->cart_checkout_behaviour = ( $this->convert_in_shop ? 'convert_shop_default' : get_option( 'alg_wc_cpp_cart_checkout', 'convert_shop_default' ) );

				// Currency code & symbol.
				add_filter( 'woocommerce_currency', array( $this, 'change_currency_code' ), PHP_INT_MAX );
				if ( 'yes' === get_option( 'alg_wc_cpp_custom_currency_symbol_enabled', 'no' ) ) {
					add_filter( 'woocommerce_currency_symbol', array( $this, 'add_currency_code' ), PHP_INT_MAX, 2 );
					$this->custom_currency_symbol_template = get_option( 'alg_wc_cpp_custom_currency_symbol_template', '%currency_code%%currency_symbol%' );
				}

				// Add to cart.
				add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), PHP_INT_MAX, 3 );
				add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), PHP_INT_MAX, 3 );
				add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_on_add_to_cart' ), PHP_INT_MAX, 2 );

				// Price.
				$price_filter = ( ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0 ? 'woocommerce_get_price' : 'woocommerce_product_get_price' );
				add_filter( $price_filter, array( $this, 'change_price' ), PHP_INT_MAX, 2 );
				add_filter( 'woocommerce_product_variation_get_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );

				add_filter( 'booking_form_calculated_booking_cost', array( $this, 'change_booking_price' ), 100, 3 );

				// Grouped.
				if ( ! $this->convert_in_shop ) {
					add_filter( 'woocommerce_grouped_price_html', array( $this, 'grouped_price_html' ), PHP_INT_MAX, 2 );
					// Admin Order.
					add_action( 'woocommerce_ajax_add_order_item_meta', array( $this, 'change_order_currency' ), PHP_INT_MAX, 3 );
				}

				// Shipping.
				add_filter( 'woocommerce_package_rates', array( $this, 'change_shipping_price' ), PHP_INT_MAX, 2 );

				// Fix mini cart.
				if ( 'yes' === get_option( 'alg_wc_cpp_fix_mini_cart', 'no' ) ) {
					add_action( 'wp_loaded', array( $this, 'fix_mini_cart' ), PHP_INT_MAX );
				}

				// Currency reports.
				if ( is_admin() && 'yes' === get_option( 'alg_wc_cpp_currency_reports_enabled', 'yes' ) ) {
					require_once 'class-alg-wc-cpp-currency-reports.php';
				}

				// Prices on site.
				if ( $this->convert_in_shop ) {
					// Regular price.
					$price_filter = ( ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0 ? 'woocommerce_get_regular_price' : 'woocommerce_product_get_regular_price' );
					add_filter( $price_filter, array( $this, 'change_price' ), PHP_INT_MAX, 2 );
					add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );
					add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'change_price' ), PHP_INT_MAX, 2 );
					// Sale price.
					$price_filter = ( ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0 ? 'woocommerce_get_sale_price' : 'woocommerce_product_get_sale_price' );
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

				// "Original price" in shop.
				if ( 'yes' === get_option( 'alg_wc_cpp_original_price_in_shop_enabled', 'no' ) && 'show_in_different' !== get_option( 'alg_wc_cpp_shop_behaviour', 'show_in_different' ) ) {
					add_filter( 'woocommerce_get_price_html', array( $this, 'add_original_price_in_shop' ), PHP_INT_MAX, 2 );
				}

				// "Sort by price" sorting and "Filter Products by Price" widget.
				if ( 'yes' === get_option( 'alg_wc_cpp_sort_by_converted_price', 'no' ) || 'yes' === get_option( 'alg_wc_cpp_filter_by_converted_price', 'no' ) ) {
					// "Sort by price".
					if ( 'yes' === get_option( 'alg_wc_cpp_sort_by_converted_price', 'no' ) ) {
						add_action( 'woocommerce_product_query', array( $this, 'remove_sorting_by_price_posts_clauses_filters' ), PHP_INT_MAX, 2 );
						add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'add_sorting_by_converted_price' ), PHP_INT_MAX );
					}
					// "Filter Products by Price".
					if ( 'yes' === get_option( 'alg_wc_cpp_filter_by_converted_price', 'no' ) ) {
						add_filter( 'woocommerce_product_query', array( $this, 'alg_wc_cpp_products_by_price_filter' ), PHP_INT_MAX, 3 );
						add_filter( 'woocommerce_product_query_meta_query', array( $this, 'price_filter_meta_query' ), PHP_INT_MAX, 2 );
						add_filter( 'woocommerce_price_filter_widget_min_amount', array( $this, 'alg_wc_cpp_min_price' ), PHP_INT_MAX );
						add_filter( 'woocommerce_price_filter_widget_max_amount', array( $this, 'alg_wc_cpp_max_price' ), PHP_INT_MAX );
						add_filter( 'woocommerce_price_filter_widget_step', array( $this, 'alg_wc_cpp_steps' ), PHP_INT_MAX );
					}
				}
			}
		}
		/**
		 * Function alg_wc_cpp_products_by_price_filter
		 *
		 * @param array $query Main Query.
		 */
		public function alg_wc_cpp_products_by_price_filter( $query ) {
			if ( $query->is_main_query() && isset( $_GET['max_price'] ) && isset( $_GET['min_price'] ) ) {
				$product_ids = wc_get_products(
					array(
						'return' => 'ids',
						'limit'  => -1,
					)
				);
				$new_ids = array();
				foreach ( $product_ids as $product_id ) {
					$product = wc_get_product( $product_id );
					$price   = $product->get_price();
					if ( $price >= $_GET['min_price'] && $price <= $_GET['max_price'] ) {
						$new_ids[] = $product_id;
					}
				}
				$query->set( 'post__in', (array) $new_ids );
			}
		}
		/**
		 * Function alg_wc_cpp_steps
		 *
		 * @param int $steps Steps.
		 */
		public function alg_wc_cpp_steps( $steps ) {
			return $steps;
		}
		/**
		 * Function alg_wc_cpp_min_price
		 *
		 * @param int $steps Steps.
		 */
		public function alg_wc_cpp_min_price( $steps ) {
			$product_ids = wc_get_products(
				array(
					'return' => 'ids',
					'limit'  => -1,
				)
			);
			$min         = array();
			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product->is_type( 'variable' ) ) {
					$price = $product->get_variation_price();
				} else {
					$price = $product->get_price();
				}
				if ( '' !== $price ) {
					$min[] = $price;
				}
			}

			$min_price = min( $min );
			$steps     = $this->alg_wc_cpp_steps( $steps );

			return ( floor( $min_price / $steps ) * $steps );
		}
		/**
		 * Function alg_wc_cpp_max_price
		 *
		 * @param int $steps Steps.
		 */
		public function alg_wc_cpp_max_price( $steps ) {
			$product_ids = wc_get_products(
				array(
					'return' => 'ids',
					'limit'  => -1,
				)
			);
			$max         = array();
			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product->is_type( 'variable' ) ) {
					$price = $product->get_variation_price( 'max' );
				} else {
					$price = $product->get_price();
				}
				if ( '' !== $price ) {
					$max[] = $price;
				}
			}

			$max_price = max( $max );
			$steps     = $this->alg_wc_cpp_steps( $steps );

			return ( ceil( $max_price / $steps ) * $steps );
		}

		/**
		 * Add Currency code.
		 *
		 * @version 1.4.1
		 * @since   1.4.1
		 *
		 * @param string $currency_symbol Currency symbol.
		 * @param string $currency Currency.
		 */
		public function add_currency_code( $currency_symbol, $currency ) {
			return str_replace( array( '%currency_code%', '%currency_symbol%' ), array( $currency, $currency_symbol ), $this->custom_currency_symbol_template );
		}

		/**
		 * Price filter for meta query.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 *
		 * @param array  $meta_query Meta query array.
		 * @param string $_wc_query WC Query.
		 */
		public function price_filter_meta_query( $meta_query, $_wc_query ) {
			if ( ! empty( $meta_query['price_filter']['price_filter'] ) ) {
				$meta_query['price_filter']['key'] = '_alg_wc_cpp_converted_price';
			}
			return $meta_query;
		}

		/**
		 * Remove sorting by price filters.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 *
		 * @param unknown $q Query.
		 * @param unknown $_wc_query Query.
		 */
		public function remove_sorting_by_price_posts_clauses_filters( $q, $_wc_query ) {
			remove_filter( 'posts_clauses', array( $_wc_query, 'order_by_price_desc_post_clauses' ) );
			remove_filter( 'posts_clauses', array( $_wc_query, 'order_by_price_asc_post_clauses' ) );
		}

		/**
		 * Add sorting by converted price.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 *
		 * @param array $args Arguments array.
		 */
		public function add_sorting_by_converted_price( $args ) {
			$wc_clean      = ( ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0 ? 'woocommerce_clean' : 'wc_clean' );
			$orderby_value = ( isset( $_GET['orderby'] ) ? $wc_clean( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) ) );
			$orderby_value = explode( '-', $orderby_value );
			$orderby       = esc_attr( $orderby_value[0] );
			$orderby       = strtolower( $orderby );
			if ( 'price' === $orderby ) {
				$args['meta_key'] = '_alg_wc_cpp_converted_price';
				$args['orderby']  = 'meta_value_num';
			}
			return $args;
		}

		/**
		 * Add original price in shop.
		 *
		 * @version 1.4.2
		 * @since   1.4.0
		 *
		 * @param string $price_html Price HTML.
		 * @param object $product Product object.
		 */
		public function add_original_price_in_shop( $price_html, $product ) {
			$product_currency = get_post_meta( alg_wc_cpp_get_product_id_or_variation_parent_id( $product ), '_alg_wc_cpp_currency', true );
			if ( '' !== $product_currency && get_woocommerce_currency() !== $product_currency ) {
				$template = get_option( 'alg_wc_cpp_original_price_in_shop_template', '<br>%price%' );
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
						/* translators: %1$s: Price range from, %2$s: Price range to */
						$price_raw = sprintf( _x( '%1$s &ndash; %2$s', 'Price range: from-to', 'woocommerce' ), $from, $to );
						$price     = sprintf(
							/* translators: %1$s: Price range from, %2$s: Price range to */
							_x( '%1$s &ndash; %2$s', 'Price range: from-to', 'woocommerce' ),
							is_numeric( $from ) ? wc_price( $from, array( 'currency' => $product_currency ) ) : $from,
							is_numeric( $to ) ? wc_price( $to, array( 'currency' => $product_currency ) ) : $to
						);
					}
				} elseif ( $product->is_type( 'booking' ) ) {
					$price_raw = get_post_meta( alg_wc_cpp_get_product_id( $product ), '_wc_booking_cost', true );
					$price     = wc_price( $price_raw, array( 'currency' => $product_currency ) );
				} else {
					$price_raw = get_post_meta( alg_wc_cpp_get_product_id( $product ), '_price', true );
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
		 * Get product display price.
		 *
		 * @version 1.4.0
		 * @since   1.1.0
		 *
		 * @param object $product Product Object.
		 * @param int    $price Product Price.
		 * @param int    $qty Product Quantity.
		 */
		public function get_product_display_price( $product, $price = '', $qty = 1 ) {
			return ( ALG_WC_CPP_IS_WC_VERSION_BELOW_3_0_0 ?
			$product->get_display_price( $price, $qty ) :
			wc_get_price_to_display(
				$product,
				array(
					'price' => $price,
					'qty'   => $qty,
				)
			)
			);
		}

		/**
		 * Change price for grouped products.
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 *
		 * @param int    $price Product Price.
		 * @param int    $qty Product Quantity.
		 * @param object $product Product Object.
		 */
		public function change_price_grouped( $price, $qty, $product ) {
			if ( $product->is_type( 'grouped' ) ) {
				foreach ( $product->get_children() as $child_id ) {
					$_product = wc_get_product( $child_id );
					$_price   = $this->get_product_display_price( $_product, get_post_meta( $child_id, '_price', true ), 1 );
					if ( $_price === $price ) {
						return $this->change_price( $price, $_product );
					}
				}
			}
			return $price;
		}

		/**
		 * Get variation prices.
		 *
		 * @version 1.4.0
		 * @since   1.1.0
		 *
		 * @param array   $price_hash Price hash array.
		 * @param object  $_product Product object.
		 * @param boolean $display Include taxes.
		 */
		public function get_variation_prices_hash( $price_hash, $_product, $display ) {
			$price_hash['alg_wc_cpp']['currency']      = $this->get_product_currency( alg_wc_cpp_get_product_id_or_variation_parent_id( $_product ) );
			$price_hash['alg_wc_cpp']['exchange_rate'] = alg_wc_cpp_get_currency_exchange_rate( $price_hash['alg_wc_cpp']['currency'] );
			return $price_hash;
		}

		/**
		 * Fix totals in mini cart.
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
		 * Change shipping price.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 *
		 * @param array $package_rates Package rates array.
		 * @param array $package Package array.
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
						$_currency     = $this->get_cart_checkout_currency();
						if ( false !== $_currency && $_currency !== $shop_currency ) {
							$currency_exchange_rate = alg_wc_cpp_get_currency_exchange_rate( $_currency );
							if ( 0 !== $currency_exchange_rate && 1 !== $currency_exchange_rate ) {
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
					default: // case 'convert_shop_default':.
						return $package_rates;
				}
			}
			return $package_rates;
		}

		/**
		 * Get product currency.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param int $product_id Product Id.
		 */
		public function get_product_currency( $product_id ) {
			// By users or user roles.
			$base_currency            = get_option( 'woocommerce_currency' );
			$do_check_by_users        = ( 'yes' === get_option( 'alg_wc_cpp_by_users_enabled', 'no' ) );
			$do_check_by_user_roles   = ( 'yes' === get_option( 'alg_wc_cpp_by_user_roles_enabled', 'no' ) );
			$do_check_by_product_cats = ( 'yes' === get_option( 'alg_wc_cpp_by_product_cats_enabled', 'no' ) );
			$do_check_by_product_tags = ( 'yes' === get_option( 'alg_wc_cpp_by_product_tags_enabled', 'no' ) );
			if ( $do_check_by_users || $do_check_by_user_roles || $do_check_by_product_cats || $do_check_by_product_tags ) {
				if ( $do_check_by_users || $do_check_by_user_roles ) {
					$product_author_id = get_post_field( 'post_author', $product_id );
				}
				if ( $do_check_by_product_cats ) {
						$_product_cats = alg_wc_cpp_get_product_terms( $product_id, 'product_cat' );
				}
				if ( $do_check_by_product_tags ) {
						$_product_tags= alg_wc_cpp_get_product_terms( $product_id, 'product_tag' );
				}
				$total_number = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
				for ( $i = 1; $i <= $total_number; $i++ ) {
					if ( $do_check_by_users ) {
						$users = get_option( 'alg_wc_cpp_users_' . $i, '' );
						if ( ! empty( $users ) && in_array( $product_author_id, $users, true ) ) {
							return get_option( 'alg_wc_cpp_currency_' . $i, $base_currency );
						}
					}
					if ( $do_check_by_user_roles ) {
						$user_roles = get_option( 'alg_wc_cpp_user_roles_' . $i, '' );
						if ( ! empty( $user_roles ) && alg_wc_cpp_is_user_role( $user_roles, $product_author_id ) ) {
							return get_option( 'alg_wc_cpp_currency_' . $i, $base_currency );
						}
					}
					if ( $do_check_by_product_cats ) {
						$product_cats = get_option( 'alg_wc_cpp_product_cats_' . $i, '' );
						if ( ! empty( $_product_cats ) && ! empty( $product_cats ) ) {
							$_intersect = array_intersect( $_product_cats, $product_cats );
							if ( ! empty( $_intersect ) ) {
								return get_option( 'alg_wc_cpp_currency_' . $i, $base_currency );
							}
						}
					}
					if ( $do_check_by_product_tags ) {
						$product_tags = get_option( 'alg_wc_cpp_product_tags_' . $i, '' );
						if ( ! empty( $_product_tags ) && ! empty( $product_tags ) ) {
							$_intersect = array_intersect( $_product_tags, $product_tags );
							if ( ! empty( $_intersect ) ) {
								return get_option( 'alg_wc_cpp_currency_' . $i, $base_currency );
							}
						}
					}
				}
			}
			// By product meta.
			return get_post_meta( $product_id, '_alg_wc_cpp_currency', true );
		}

		/**
		 * Called when Add to cart is clicked.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 * @param bool $passed True or false.
		 * @param int  $product_id Product Id.
		 */
		public function validate_on_add_to_cart( $passed, $product_id ) {
			if ( 'leave_one_product' === $this->cart_checkout_behaviour ) {
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if ( $cart_item['product_id'] !== $product_id ) {
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
				$product_currency = $this->get_product_currency( $product_id );
				if ( '' === $product_currency ) {
					$product_currency = $shop_currency;
				}
				foreach ( WC()->cart->get_cart() as $cart_item ) {
					$cart_product_currency = ( isset( $cart_item['alg_wc_cpp'] ) && '' !== $cart_item['alg_wc_cpp'] ) ?
					$cart_item['alg_wc_cpp'] : $shop_currency;
					if ( $cart_product_currency !== $product_currency ) {
						wc_add_notice(
							get_option(
								'alg_wc_cpp_cart_checkout_leave_same_currency',
								__(
									'Only products with same currency can be added to the cart. Clear the cart or finish the order, before adding products with another currency to the cart.',
									'currency-per-product-for-woocommerce'
								)
							),
							'error'
						);
						return false;
					}
				}
			}
			return $passed;
		}

		/**
		 * HTML for Grouped price.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param string $price_html Price HTML.
		 * @param object $_product Product object.
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
				if ( $min_price === $max_price && $min_cpp_currency === $max_cpp_currency ) {
					$display_price = wc_price( alg_wc_cpp_get_product_display_price( $_product, $min_price, 1 ), array( 'currency' => $min_cpp_currency ) );
				} else {
					$from = wc_price( alg_wc_cpp_get_product_display_price( $_product, $min_price, 1 ), array( 'currency' => $min_cpp_currency ) );
					$to   = wc_price( alg_wc_cpp_get_product_display_price( $_product, $max_price, 1 ), array( 'currency' => $max_cpp_currency ) );
					/* translators: %1$s: Price range from, %2$s: Price range to */
					$display_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), $from, $to );
				}
				$new_price_html = $display_price . $_product->get_price_suffix();
				return $new_price_html;
			}

			return $price_html;
		}

		/**
		 * Change the price.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 *
		 * @param int    $price Product price.
		 * @param object $_product Product object.
		 */
		public function change_price( $price, $_product ) {
			$do_save_prices = ( 'yes' === get_option( 'alg_wc_cpp_save_products_prices', 'no' ) );
			if ( $do_save_prices ) {
				$product_id = alg_wc_cpp_get_product_id( $_product );
			}
			if ( isset( $_product->alg_wc_cpp ) ) {
				switch ( $this->cart_checkout_behaviour ) {
					case 'leave_one_product':
					case 'leave_same_currency':
						return $price;
					case 'convert_first_product':
					case 'convert_last_product':
						$shop_currency = get_option( 'woocommerce_currency' );
						$_currency     = $this->get_cart_checkout_currency();
						if ( false !== $_currency && $_currency !== $shop_currency ) {
							if ( $_product->alg_wc_cpp === $_currency ) {
								return $price;
							} else {
								if ( $do_save_prices && isset( $this->saved_prices['cart_checkout'][ $product_id ] ) ) {
									return $this->saved_prices['cart_checkout'][ $product_id ];
								}
								$exchange_rate_product       = alg_wc_cpp_get_currency_exchange_rate( $_product->alg_wc_cpp );
								$exchange_rate_cart_checkout = alg_wc_cpp_get_currency_exchange_rate( $_currency );
								$exchange_rate               = $exchange_rate_product / $exchange_rate_cart_checkout;
								$return_price                = $price * $exchange_rate;
								if ( $do_save_prices ) {
									$this->saved_prices['cart_checkout'][ $product_id ] = $return_price;
								}
								return $return_price;
							}
						} elseif ( $_product->alg_wc_cpp === $shop_currency ) {
							return $price;
						} else {
							if ( $do_save_prices && isset( $this->saved_prices['cart_checkout'][ $product_id ] ) ) {
								return $this->saved_prices['cart_checkout'][ $product_id ];
							}
							$exchange_rate = alg_wc_cpp_get_currency_exchange_rate( $_product->alg_wc_cpp );
							$return_price  = $price * $exchange_rate;
							if ( $do_save_prices ) {
								$this->saved_prices['cart_checkout'][ $product_id ] = $return_price;
							}
							return $return_price;
						}
					default: // case 'convert_shop_default':.
						if ( $do_save_prices && isset( $this->saved_prices['cart_checkout'][ $product_id ] ) ) {
							return $this->saved_prices['cart_checkout'][ $product_id ];
						}
						$exchange_rate = alg_wc_cpp_get_currency_exchange_rate( $_product->alg_wc_cpp );
						$return_price  = $price * $exchange_rate;
						if ( $do_save_prices ) {
							$this->saved_prices['cart_checkout'][ $product_id ] = $return_price;
						}
						return $return_price;
				}
			} elseif ( $this->convert_in_shop ) {
				if ( '' === $price ) {
					return $price;
				}
				if ( $do_save_prices && isset( $this->saved_prices['shop'][ $product_id ] ) ) {
					return $this->saved_prices['shop'][ $product_id ];
				}
				$exchange_rate = alg_wc_cpp_get_currency_exchange_rate( $this->get_product_currency( alg_wc_cpp_get_product_id_or_variation_parent_id( $_product ) ) );

				$return_price = (float) $price * $exchange_rate;
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
						if ( false !== $_currency && ! isset( $_product->alg_wc_cpp ) && $_currency !== $shop_currency ) {

							if ( $do_save_prices && isset( $this->saved_prices['shop'][ $product_id ] ) ) {
								return $this->saved_prices['shop'][ $product_id ];
							}

							$exchange_rate          = alg_wc_cpp_get_currency_exchange_rate( $_currency );
							$currency_exchange_rate = 1 / $exchange_rate;

							$return_price = (float) $price * $currency_exchange_rate;
							if ( $do_save_prices ) {
								$this->saved_prices['shop'][ $product_id ] = $return_price;
							}
							return $return_price;
						}
						break;
				}
			}
			return $price;
		}

		/**
		 * Change the Booking cost for the number of persons displayed on the Booking product page from WooCommerce Bookings plugin.
		 *
		 * Change the booking price.
		 *
		 * @version 1.4.5
		 * @since 1.4.5
		 *
		 * @param int     $price   Product price.
		 * @param object  $product Product object.
		 * @param boolean $posted  Posted or not.
		 */
		public function change_booking_price( $price, $product, $posted ) {
			if ( $this->convert_in_shop && wp_doing_ajax() ) {
				if ( isset( $product->product ) ) {
					$exchange_rate = alg_wc_cpp_get_currency_exchange_rate( $this->get_product_currency( alg_wc_cpp_get_product_id_or_variation_parent_id( $product->product ) ) );
				} else {
					$exchange_rate = 1;
				}
				$price = $price * $exchange_rate;
			}
			return $price;
		}

		/**
		 * Get cart item from session.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array  $item   Items array.
		 * @param array  $values Values array.
		 * @param string $key    Array key to check.
		 */
		public function get_cart_item_from_session( $item, $values, $key ) {
			if ( array_key_exists( 'alg_wc_cpp', $values ) ) {
				$item['data']->alg_wc_cpp = $values['alg_wc_cpp'];
			}
			return $item;
		}

		/**
		 * Add cart item data.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $cart_item_data Cart item data.
		 * @param int   $product_id     Product Id.
		 * @param int   $variation_id   Variation Id.
		 */
		public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
			$cpp_currency = $this->get_product_currency( $product_id );
			if ( '' !== $cpp_currency ) {
				$cart_item_data['alg_wc_cpp'] = $cpp_currency;
			}
			return $cart_item_data;
		}

		/**
		 * Add cart item.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $cart_item_data Cart item data.
		 * @param int   $cart_item_key  Cart item key.
		 */
		public function add_cart_item( $cart_item_data, $cart_item_key ) {
			if ( isset( $cart_item_data['alg_wc_cpp'] ) ) {
				$cart_item_data['data']->alg_wc_cpp = $cart_item_data['alg_wc_cpp'];
			}
			return $cart_item_data;
		}

		/**
		 * Get current Product Id & it's currency.
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
				$product_id = alg_wc_cpp_get_product_id_or_variation_parent_id( $product );
			}
			// Get ID - product_id in _REQUEST.
			if ( ! $product_id && isset( $_REQUEST['product_id'] ) ) {
				$product_id = sanitize_text_field( wp_unslash( $_REQUEST['product_id'] ) );
			}
			// Get ID - WooCommerce Bookings plugin.
			if ( ! $product_id && isset( $_POST['form'] ) ) {
				$posted = array();
				parse_str( sanitize_text_field( wp_unslash( $_POST['form'] ) ), $posted );
				$product_id = isset( $posted['add-to-cart'] ) ? $posted['add-to-cart'] : 0;
			}
			// Get ID - EventON plugin.
			$eventon_wc_product_id = get_post_meta( get_the_ID(), 'tx_woocommerce_product_id', true );
			if ( ! $product_id && '' !== $eventon_wc_product_id ) {
				$product_id = $eventon_wc_product_id;
			}
			// Get ID - final fallback - get_the_ID.
			if ( ! $product_id ) {
				$product_id = get_the_ID();
			}
			// Get currency.
			if ( $product_id && 'product' === get_post_type( $product_id ) ) {
				$cpp_currency = $this->get_product_currency( $product_id );
				return ( '' !== $cpp_currency ) ? $cpp_currency : false;
			}
			return false;
		}

		/**
		 * Get cart checkout currency.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 */
		public function get_cart_checkout_currency() {
			if ( ! isset( WC()->cart ) || empty( WC()->cart->get_cart_contents() ) ) {
				return false;
			}
			if ( ! isset( WC()->cart ) || WC()->cart->is_empty() ) {
				return false;
			}
			if ( 'convert_shop_default' === $this->cart_checkout_behaviour ) {
				return false;
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
		 * Check if it's cart page or checkout page.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @todo    [dev] maybe need to check for AJAX also
		 */
		public function is_cart_or_checkout() {
			return ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) );
		}

		/**
		 * Check if admin is editing product.
		 *
		 * @version 1.4.1
		 * @since   1.4.1
		 */
		public function is_admin_product_edit() {
			if ( is_admin() ) {
				global $pagenow;
				if (
				( 'post.php' === $pagenow && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) ||
				( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] ) && 'woocommerce_load_variations' === $_REQUEST['action'] )
				) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Change currency code.
		 *
		 * @version 1.4.1
		 * @since   1.0.0
		 *
		 * @param string $currency Currency.
		 */
		public function change_currency_code( $currency ) {
			$cart_checkout_currency      = $this->get_cart_checkout_currency();
			$current_product_id_currency = $this->get_current_product_id_and_currency();
			if ( $this->convert_in_shop && ! $this->is_admin_product_edit() ) {
				// When shop behaviour is set to Convert to shop currency.
				return $currency;
			} elseif ( $this->is_cart_or_checkout() ) {
				if ( false !== $cart_checkout_currency ) {
					// Currency for cart/checkout page.
					return $cart_checkout_currency;
				}
			} elseif ( wp_doing_ajax() && false !== $current_product_id_currency && 'convert_shop_default' === $this->cart_checkout_behaviour ) {
				// When Cart/Checkout behaviour set to Convert to shop default currency.
				return $currency;
			} elseif ( wp_doing_ajax() && false === $cart_checkout_currency && 'convert_first_product' === $this->cart_checkout_behaviour ) {
				// When Cart/Checkout behaviour set to Convert to first product currency and when first product added have base currency.
				return $currency;
			} elseif ( false !== $current_product_id_currency ) {
				// Currency for the every product.
				return $current_product_id_currency;
			} elseif ( wp_doing_ajax() && false !== $cart_checkout_currency ) {
				return $cart_checkout_currency;
			}
			return $currency;
		}

		/**
		 * Change the Order currency based on the plugin settings.
		 *
		 * @param integer $item_id - Item ID.
		 * @param object  $item - WC Order Item.
		 * @param object  $order - WC Order.
		 * @return object $item - WC Order Item.
		 *
		 * @since 1.4.6
		 */
		public function change_order_currency( $item_id, $item, $order ) {

			// check the product currency.
			$product_id = $item->get_product_id();

			if ( $product_id > 0 ) {
				// get the product currency.
				$prd_currency = get_post_meta( $product_id, '_alg_wc_cpp_currency', true );

				// get the shop currency.
				$shop_currency = get_woocommerce_currency();

				// check if it's different than the Shop currency.
				if ( $prd_currency !== $shop_currency ) {
					$order->set_currency( $prd_currency );
					$order->save();
				}
			}

			return $item;
		}
	}

endif;

return new Alg_WC_CPP_Core();
