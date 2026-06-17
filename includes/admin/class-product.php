<?php
/**
 * Currency per Product for WooCommerce - Admin Product Class
 * 
 * @since   2.0.0
 * @author  Tyche Softwares
 * @package CPP/Admin/Product
 */

namespace Tyche\CPP\Admin;

use Tyche\CPP\Functions\Functions;

defined( 'ABSPATH' ) || exit;

class Product {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_cpp_metabox' ) );
		add_action( 'save_post_product', array( $this, 'save_cpp_meta_box' ), PHP_INT_MAX, 2 );

		if ( Functions::get_advanced_setting( 'sort_by_converted_price', false ) || Functions::get_advanced_setting( 'filter_by_converted_price', false ) ) {
			add_action( 'save_post_product', array( $this, 'calculate_product_price_on_product_saved' ), PHP_INT_MAX, 1 );
			add_action( 'woocommerce_ajax_save_product_variations', array( $this, 'calculate_product_price_on_product_saved_ajax' ), PHP_INT_MAX, 1 );
		}
	}

	/**
	 * Add Currency per Product Metabox.
	 */
	public function add_cpp_metabox() {
		add_meta_box(
			'currency-per-product',
			__( 'Currency per Product', 'currency-per-product-for-woocommerce' ),
			array( $this, 'display_cpp_metabox' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * Display Currency per Product Metabox.
	 *
	 * @since 2.0.0
	 * return void
	 */
	public function display_cpp_metabox() {
		$current_post_id = get_the_ID();
		$html            = '';
		$html           .= '<table class="widefat striped">';
		foreach ( $this->get_meta_box_options() as $option ) {
			$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
			if ( $is_enabled ) {
				if ( 'title' === $option['type'] ) {
					$html .= '<tr>';
					$html .= '<th colspan="3" style="text-align:left;font-weight:bold;">' . esc_html( $option['title'] ) . '</th>';
					$html .= '</tr>';
				} else {
					$custom_attributes = '';
					$the_post_id       = ( isset( $option['product_id'] ) ) ? $option['product_id'] : $current_post_id;
					$the_meta_name     = ( isset( $option['meta_name'] ) ) ? $option['meta_name'] : '_' . $option['name'];
					if ( get_post_meta( $the_post_id, $the_meta_name ) ) {
						$option_value = get_post_meta( $the_post_id, $the_meta_name, true );
					} else {
						$option_value = ( isset( $option['default'] ) ) ? $option['default'] : '';
					}
					$css          = ( isset( $option['css'] ) ) ? $option['css'] : '';
					$input_ending = '';
					if ( 'select' === $option['type'] ) {
						if ( isset( $option['multiple'] ) ) {
							$custom_attributes = ' multiple';
							$option_name       = $option['name'] . '[]';
						} else {
							$option_name = $option['name'];
						}
						if ( isset( $option['custom_attributes'] ) ) {
							$custom_attributes .= ' ' . $option['custom_attributes'];
						}
						$options = '';
						foreach ( $option['options'] as $select_option_key => $select_option_value ) {
							$selected = '';
							if ( is_array( $option_value ) ) {
								foreach ( $option_value as $single_option_value ) {
									if ( '' != ( $selected = selected( $single_option_value, $select_option_key, false ) ) ) {  //phpcs:ignore
										break;
									}
								}
							} else {
								$selected = selected( $option_value, $select_option_key, false );
							}
							$options .= '<option value="' . esc_attr( $select_option_key ) . '" ' . $selected . '>' . esc_html( $select_option_value ) . '</option>';
						}
					} elseif ( 'textarea' === $option['type'] ) {
						if ( '' === $css ) {
							$css = 'min-width:300px;';
						}
					} else {
						$input_ending = ' id="' . esc_attr( $option['name'] ) . '" name="' . esc_attr( $option['name'] ) . '" value="' . esc_attr( $option_value ) . '">';
						if ( isset( $option['custom_attributes'] ) ) {
							$input_ending = ' ' . $option['custom_attributes'] . $input_ending;
						}
						if ( isset( $option['placeholder'] ) ) {
							$input_ending = ' placeholder="' . esc_attr( $option['placeholder'] ) . '"' . $input_ending;
						}
					}
					switch ( $option['type'] ) {
						case 'price':
							$field_html = '<input style="' . esc_attr( $css ) . '" class="short wc_input_price" type="number" step="0.0001"' . $input_ending;
							break;
						case 'date':
							$field_html = '<input style="' . esc_attr( $css ) . '" class="input-text" display="date" type="text"' . $input_ending;
							break;
						case 'textarea':
							$field_html = '<textarea style="' . esc_attr( $css ) . '" id="' . esc_attr( $option['name'] ) . '" name="' . esc_attr( $option['name'] ) . '">' .
								esc_textarea( $option_value ) . '</textarea>';
							break;
						case 'select':
							$field_html = '<select' . $custom_attributes . ' style="' . esc_attr( $css ) . '" id="' . esc_attr( $option['name'] ) . '" name="' .
								esc_attr( $option_name ) . '">' . $options . '</select>';
							break;
						default:
							$field_html = '<input style="' . esc_attr( $css ) . '" class="short" type="' . esc_attr( $option['type'] ) . '"' . $input_ending;
							break;
					}
					$html         .= '<tr>';
					$maybe_tooltip = ( isset( $option['tooltip'] ) && '' != $option['tooltip'] ) ? wc_help_tip( $option['tooltip'], true ) : ''; //phpcs:ignore
					$html         .= '<th style="text-align:left;width:25%;">' . esc_html( $option['title'] ) . $maybe_tooltip . '</th>';
					if ( isset( $option['desc'] ) && '' != $option['desc'] ) { //phpcs:ignore
						$html .= '<td style="font-style:italic;width:25%;">' . esc_html( $option['desc'] ) . '</td>';
					}
					$html .= '<td>' . $field_html . '</td>';
					$html .= '</tr>';
				}
			}
		}
		$html .= '</table>';
		$html .= '<input type="hidden" name="alg_wc_cpp_save_post" value="alg_wc_cpp_save_post">';
		echo $html; //phpcs:ignore
		wp_nonce_field( 'cpp_save_product_meta', 'cpp_product_meta_nonce' );
	}

	/**
	 * Save meta box data for custom post meta fields.
	 *
	 * This function handles the saving of meta box data for custom post types by checking the submitted form fields
	 * and updating the corresponding post meta values based on the provided options.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @param   int     $post_id The ID of the post being saved.
	 * @param   WP_Post $post The post object being saved.
	 * @return  void
	 */
	public function save_cpp_meta_box( $post_id, $post ) { //phpcs:ignore
		// Check that we are saving with current metabox displayed.
		if ( ! isset( $_POST['alg_wc_cpp_save_post'] ) ) { //phpcs:ignore
			return;
		}
		// Verify nonce to prevent CSRF.
		if ( ! isset( $_POST['cpp_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpp_product_meta_nonce'] ) ), 'cpp_save_product_meta' ) ) {
			return;
		}
		// Save options.
		foreach ( $this->get_meta_box_options() as $option ) {
			if ( 'title' === $option['type'] ) {
				continue;
			}
			$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
			if ( $is_enabled ) {
				$option_value = ( isset( $_POST[ $option['name'] ] ) ? $_POST[ $option['name'] ] : $option['default'] ); //phpcs:ignore
				$_post_id     = ( isset( $option['product_id'] ) ? $option['product_id'] : $post_id );
				$_meta_name   = ( isset( $option['meta_name'] ) ? $option['meta_name'] : '_' . $option['name'] );
				update_post_meta( $_post_id, $_meta_name, $option_value );
			}
		}
	}

	/**
	 * Get_meta_box_options.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	public function get_meta_box_options() {
		$currency_codes                               = array();
		$base_currency                                = get_option( 'woocommerce_currency' );
		$currency_codes[ $base_currency ]             = $base_currency;
		$currency_codes[ get_woocommerce_currency() ] = get_woocommerce_currency();
		foreach ( \Tyche\CPP\Functions\Functions::get_currencies_setting( 'currencies', array() ) as $currency_entry ) {
			$code                    = $currency_entry['currency'] ?? $base_currency;
			$currency_codes[ $code ] = $code;
		}
		$options = array(
			array(
				'title'   => __( 'Product currency', 'currency-per-product-for-woocommerce' ),
				'tooltip' => __( 'Update product after you change this field\'s value.', 'currency-per-product-for-woocommerce' ),
				'name'    => 'alg_wc_cpp_currency',
				'default' => apply_filters( 'alg_wc_cpp_default_currency', get_woocommerce_currency(), $currency_codes ),
				'type'    => 'select',
				'options' => $currency_codes,
			),
		);
		return $options;
	}

	/**
	 * Recalculates the converted price for a single product when it is saved.
	 *
	 * @param int $product_id The ID of the saved product.
	 */
	public function calculate_product_price_on_product_saved( $product_id ) {
		Functions::calculate_and_update_product_price( $product_id, get_woocommerce_currency() );
	}

	/**
	 * Recalculates the converted price for a variable product after its variations
	 * are saved via AJAX.
	 *
	 * @param int $product_id The ID of the saved product.
	 */
	public function calculate_product_price_on_product_saved_ajax( $product_id ) {
		\WC_Product_Variable::sync( $product_id );
		Functions::calculate_and_update_product_price( $product_id, get_woocommerce_currency() );
	}
}

return new Product();