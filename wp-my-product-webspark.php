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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_plugin_styles' ) );
	}

	/**
	 * Enqueue plugin styles.
	 */
	public function enqueue_plugin_styles() {
		// Load custom plugin styles
		wp_enqueue_style( 'wp-my-product-webspark-styles', plugin_dir_url( __FILE__ ) . 'styles.css' );
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

		if ( isset( $_POST['wp_my_product_add'] ) && check_admin_referer( 'wp_my_product_add_nonce', 'wp_my_product_add_nonce_field' ) ) {
			// Get data from the form
			if ( ! empty( $_POST['product_name'] ) && ! empty( $_POST['product_price'] ) ) { // Check only for required fields
				$product_name        = sanitize_text_field( $_POST['product_name'] );
				$product_price       = sanitize_text_field( $_POST['product_price'] );
				$product_quantity    = isset( $_POST['product_quantity'] ) ? intval( $_POST['product_quantity'] ) : 0; // Quantity can be empty
				$product_description = isset( $_POST['product_description'] ) ? wp_kses_post( $_POST['product_description'] ) : ''; // Description can be empty

				// Create a new product via WooCommerce
				$product = new WC_Product_Simple();  // For a simple product
				$product->set_name( $product_name );
				$product->set_regular_price( $product_price );
				$product->set_manage_stock( true );
				$product->set_stock_quantity( $product_quantity );  // Set quantity (optional)
				$product->set_description( $product_description ); // Set description (optional)
				$product->set_status( 'pending' );

				// Save the product
				$product->save();
				echo '<p>' . esc_html__( 'Product added successfully and is awaiting review.', 'wp-my-product-webspark' ) . '</p>';
			} else {
				echo '<p>' . esc_html__( 'Please fill in the required fields: Product Name and Product Price.', 'wp-my-product-webspark' ) . '</p>';
			}
		}

		// Form for adding a product
		?>
        <form method="POST" class="wp-my-product-form">
			<?php wp_nonce_field( 'wp_my_product_add_nonce', 'wp_my_product_add_nonce_field' ); ?>

            <div class="wp-my-product-field">
                <input type="text" id="product_name" name="product_name"
                       placeholder="<?php esc_html_e( 'Product Name', 'wp-my-product-webspark' ); ?>" required/>
            </div>

            <div class="wp-my-product-field">
                <input type="number" id="product_price" name="product_price" step="0.01"
                       placeholder="<?php esc_html_e( 'Product Price', 'wp-my-product-webspark' ); ?>" required/>
            </div>

            <div class="wp-my-product-field">
                <input type="number" id="product_quantity" name="product_quantity" step="1" min="1"
                       placeholder="<?php esc_html_e( 'Product Quantity', 'wp-my-product-webspark' ); ?>"/>
            </div>

            <div class="wp-my-product-field">
				<?php
				// Output WYSIWYG editor for product description
				$content = isset( $_POST['product_description'] ) ? $_POST['product_description'] : '';
				wp_editor( $content, 'product_description', array(
					'textarea_name' => 'product_description',
					'editor_class'  => 'wp-editor-area',
					'textarea_rows' => 5,  // Editor height can be customized
					'editor_height' => 200,  // Editor height can be customized
				) );
				?>
            </div>

            <div class="wp-my-product-field">
                <input type="submit" name="wp_my_product_add"
                       value="<?php esc_html_e( 'Add Product', 'wp-my-product-webspark' ); ?>"/>
            </div>
        </form>
		<?php
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
