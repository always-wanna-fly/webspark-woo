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
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="error"><p>' . esc_html__( 'WooCommerce is required for this plugin to work.', 'wp-my-product-webspark' ) . '</p></div>';
		} );

		return false;
	}

	return true;
}

if ( ! wp_my_product_webspark_check_woocommerce() ) {
	return;
}

/**
 * Plugin main class.
 */
class WP_My_Product_Webspark {

	/**
	 * WP_My_Product_Webspark constructor.
	 */
	public function __construct() {
		// Initialize plugin functions
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_my_account_menu_items' ), 10, 1 );
		add_action( 'init', array( $this, 'add_my_account_routes' ) );
		add_action( 'woocommerce_account_add-product_endpoint', array( $this, 'my_account_add_product' ) );
		add_action( 'woocommerce_account_my-products_endpoint', array( $this, 'my_account_my_products' ) );
	}

	/**
	 * Add new pages to "My Account" menu after "Dashboard".
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function add_my_account_menu_items( $items ) {
		$new_items = [
			'add-product' => __( 'Add product', 'wp-my-product-webspark' ),
			'my-products' => __( 'My products', 'wp-my-product-webspark' ),
		];

		// Insert after "Dashboard"
		$position = array_search( 'dashboard', array_keys( $items ) );
		$items    = array_slice( $items, 0, $position + 1, true )
		            + $new_items
		            + array_slice( $items, $position + 1, null, true );

		return $items;
	}

	/**
	 * Register new routes for the tabs.
	 */
	public function add_my_account_routes() {
		add_rewrite_endpoint( 'add-product', EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'my-products', EP_ROOT | EP_PAGES );
	}

	/**
	 * Content for the "Add Product" tab.
	 */
	public function my_account_add_product() {
		echo '<h2>' . esc_html__( 'Add product', 'wp-my-product-webspark' ) . '</h2>';
		echo '<p>' . esc_html__( 'Here will be a form for adding a product.', 'wp-my-product-webspark' ) . '</p>';
	}

	/**
	 * Content for the "My Products" tab.
	 */
	public function my_account_my_products() {
		echo '<h2>' . esc_html__( 'My products', 'wp-my-product-webspark' ) . '</h2>';
		echo '<p>' . esc_html__( 'Here will be a list of user products.', 'wp-my-product-webspark' ) . '</p>';
	}

	/**
	 * Flush rewrite rules after plugin activation.
	 */
	public function flush_rewrite_rules() {
		$this->add_my_account_routes();
		flush_rewrite_rules();
	}

	/**
	 * Flush rewrite rules after plugin deactivation.
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}
}

/**
 * Initialize the plugin.
 */
function wp_my_product_webspark_initialize() {
	$plugin = new WP_My_Product_Webspark();
}

add_action( 'plugins_loaded', 'wp_my_product_webspark_initialize' );

/**
 * Flush rewrite rules on plugin activation.
 */
register_activation_hook( __FILE__, array( 'WP_My_Product_Webspark', 'flush_rewrite_rules' ) );

/**
 * Flush rewrite rules on plugin deactivation.
 */
register_deactivation_hook( __FILE__, array( 'WP_My_Product_Webspark', 'deactivate' ) );
