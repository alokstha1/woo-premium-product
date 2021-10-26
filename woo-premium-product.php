<?php
/**
 * Plugin Name: Woo Premium Product
 * Description: This is an WooCommerce extension which allows to adds premium price percentage for a chosen product. The price gets added up while in cart and checkout pages.
 * Version: 1.0.0
 * Author: Alok Shrestha
 * Author URI: https://alokshrestha.com.np
 * Text Domain: woo-premium-product
 * License: GPLv3 or later
 */

// Exit if acccessed directly
defined( 'ABSPATH' ) || exit;

// Bail early if woocommerce plugin is not active/exists.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

// include premium product price admin class
require_once( __DIR__ . '/classes/class-woo-premium-price-admin.php' );
// include premium product price class
require_once( __DIR__ . '/classes/class-woo-premium-price.php' );

// creates Woo_Premium_Price_Admin instance.
Woo_Premium_Price_Admin::instance();

// Create instance of Woo_Premium_Price
Woo_Premium_Price::instance();

