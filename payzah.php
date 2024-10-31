<?php
/**
 * Plugin Name: Payzah Payment Gateway for WooCommerce
 * Plugin URI: http://payzah.com/
 * Description: Payzah is a third-party payment gateway that supports KNET & Credit Cards Transactions (VISA & MasterCard).
 * Version: 1.5
 * Author: Payzah
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: payzah
 * Domain Path: /languages/
 */

// Prevent direct access to this file
defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

// Define plugin constants
define('PAYZAH_PLUGIN_DIR', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));
define('PAYZAH_IMAGE_DIR', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/assets/image/');

// Define API URLs as constants

// define('PAYZAH_API_URL_TEST', 'https://development.payzah.net/ws/paymentgateway/index');
// define('PAYZAH_REFUND_API_URL_TEST', 'https://development.payzah.net/ws/paymentgateway/refund');

define('PAYZAH_API_URL_TEST', 'https://staging.payzah.net/production770/ws/paymentgateway/index');
define('PAYZAH_REFUND_API_URL_TEST', 'https://staging.payzah.net/production770/ws/paymentgateway/refund');

define('PAYZAH_API_URL_PROD', 'https://payzah.net/production770/ws/paymentgateway/index');
define('PAYZAH_REFUND_API_URL_PROD', 'https://payzah.net/production770/ws/paymentgateway/refund');



// Plugin activation hook
register_activation_hook(__FILE__, function(){
	/* Silence is Golden */
});

// Plugin deactivation hook
register_deactivation_hook(__FILE__, function(){
	/* Silence is Golden */
});

// Include required files
require plugin_dir_path( __FILE__ ) . 'includes/hooks.php';
require plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-payzah-woocommerce-payment-.php';

// Initialize the Payzah plugin
add_action('plugins_loaded', 'payzah_plugin_init'); 
function payzah_plugin_init() {
	load_plugin_textdomain( 'payzah', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
