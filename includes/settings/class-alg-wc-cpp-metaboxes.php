<?php
/**
 * Currency per Product for WooCommerce - Metaboxes
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

if ( ! class_exists( 'Alg_WC_CPP_Metaboxes' ) ) :

	/**
	 * Main Alg_WC_CPP_Metaboxes Class
	 *
	 * @class   Alg_WC_CPP_Metaboxes
	 */
	class Alg_WC_CPP_Metaboxes {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			add_action( 'add_meta_boxes', array( $this, 'add_cpp_metabox' ) );
			add_action( 'save_post_product', array( $this, 'save_cpp_meta_box' ), PHP_INT_MAX, 2 );
		}

		/**
		 * Add Currency per product metabox.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_cpp_metabox() {
			add_meta_box(
				'alg-wc-currency-per-product',
				__( 'Currency per Product', 'currency-per-product-for-woocommerce' ),
				array( $this, 'display_cpp_metabox' ),
				'product',
				'normal',
				'high'
			);
		}

		/**
		 * Display Currency per product metabox.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
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
						$html .= '<th colspan="3" style="text-align:left;font-weight:bold;">' . $option['title'] . '</th>';
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
										$selected = selected( $single_option_value, $select_option_key, false );
										if ( '' !== $selected ) {
											break;
										}
									}
								} else {
									$selected = selected( $option_value, $select_option_key, false );
								}
								$options .= '<option value="' . $select_option_key . '" ' . $selected . '>' . $select_option_value . '</option>';
							}
						} elseif ( 'textarea' === $option['type'] ) {
							if ( '' === $css ) {
								$css = 'min-width:300px;';
							}
						} else {
							$input_ending = ' id="' . $option['name'] . '" name="' . $option['name'] . '" value="' . $option_value . '">';
							if ( isset( $option['custom_attributes'] ) ) {
								$input_ending = ' ' . $option['custom_attributes'] . $input_ending;
							}
							if ( isset( $option['placeholder'] ) ) {
								$input_ending = ' placeholder="' . $option['placeholder'] . '"' . $input_ending;
							}
						}
						switch ( $option['type'] ) {
							case 'price':
								$field_html = '<input style="' . $css . '" class="short wc_input_price" type="number" step="0.0001"' . $input_ending;
								break;
							case 'date':
								$field_html = '<input style="' . $css . '" class="input-text" display="date" type="text"' . $input_ending;
								break;
							case 'textarea':
								$field_html = '<textarea style="' . $css . '" id="' . $option['name'] . '" name="' . $option['name'] . '">' .
								$option_value . '</textarea>';
								break;
							case 'select':
								$field_html = '<select' . $custom_attributes . ' style="' . $css . '" id="' . $option['name'] . '" name="' .
								$option_name . '">' . $options . '</select>';
								break;
							default:
								$field_html = '<input style="' . $css . '" class="short" type="' . $option['type'] . '"' . $input_ending;
								break;
						}
						$html         .= '<tr>';
						$maybe_tooltip = ( isset( $option['tooltip'] ) && '' !== $option['tooltip'] ) ? wc_help_tip( $option['tooltip'], true ) : '';
						$html         .= '<th style="text-align:left;width:25%;">' . $option['title'] . $maybe_tooltip . '</th>';
						if ( isset( $option['desc'] ) && '' !== $option['desc'] ) {
							$html .= '<td style="font-style:italic;width:25%;">' . $option['desc'] . '</td>';
						}
						$html .= '<td>' . $field_html . '</td>';
						$html .= '</tr>';
					}
				}
			}
			$html .= '</table>';
			$html .= '<input type="hidden" name="alg_wc_cpp_save_post" value="alg_wc_cpp_save_post">';
			echo $html;
		}

		/**
		 * Save Currency per product metabox.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param int    $post_id Post Id.
		 * @param object $post Post object.
		 */
		public function save_cpp_meta_box( $post_id, $post ) {
			// Check that we are saving with current metabox displayed.
			if ( ! isset( $_POST['alg_wc_cpp_save_post'] ) ) {
				return;
			}
			// Save options.
			foreach ( $this->get_meta_box_options() as $option ) {
				if ( 'title' === $option['type'] ) {
					continue;
				}
				$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
				if ( $is_enabled ) {
					$option_value = ( isset( $_POST[ $option['name'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $option['name'] ] ) ) : $option['default'] );
					$_post_id     = ( isset( $option['product_id'] ) ? $option['product_id'] : $post_id );
					$_meta_name   = ( isset( $option['meta_name'] ) ? $option['meta_name'] : '_' . $option['name'] );
					update_post_meta( $_post_id, $_meta_name, $option_value );
				}
			}
		}

		/**
		 * Get meta box options.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 */
		public function get_meta_box_options() {
			$currency_codes                               = array();
			$base_currency                                = get_option( 'woocommerce_currency' );
			$currency_codes[ $base_currency ]             = $base_currency;
			$currency_codes[ get_woocommerce_currency() ] = get_woocommerce_currency();
			$total_number                                 = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
			for ( $i = 1; $i <= $total_number; $i++ ) {
				$currency_codes[ get_option( 'alg_wc_cpp_currency_' . $i, $base_currency ) ] = get_option( 'alg_wc_cpp_currency_' . $i, $base_currency );
			}
			$options = array(
				array(
					'title'   => __( 'Product currency', 'currency-per-product-for-woocommerce' ),
					'tooltip' => __( 'Update product after you change this field\'s value.', 'currency-per-product-for-woocommerce' ),
					'name'    => 'alg_wc_cpp_currency',
					'default' => get_woocommerce_currency(),
					'type'    => 'select',
					'options' => $currency_codes,
				),
			);
			return apply_filters( 'alg_wc_cpp', $options, 'meta_box_options' );
		}

	}

endif;

return new Alg_WC_CPP_Metaboxes();
