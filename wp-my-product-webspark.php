<?php
/**
 * Plugin Name: WP My Product Webspark
 * Description: test task from Webspark.
 * Version:     1.0.0
 * Text Domain: wp-my-product-webspark
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WooCommerce is activated.
 */
function wp_my_product_webspark_check_woocommerce() {
	if ( ! class_exists( 'WooCommerces' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="error"><p>WooCommerce is required for this plugin to work</p></div>';
		} );

		return false;
	}

	return true;
}

/**
 * Launching the plugin.
 */
function wp_my_product_webspark_init() {
	if ( ! wp_my_product_webspark_check_woocommerce() ) {
		return;
	}
}
