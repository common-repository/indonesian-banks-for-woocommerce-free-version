<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/* @wordpress-plugin
 * Plugin Name:       Indonesian Banks for WooCommerce
 * Plugin URI:        https://www.seniberpikir.com/indonesian-banks-plugin-woocommerce-toko-online-indonesia
 * Description:       A collection of Indonesian banks for offline payment gateway on WooCommerce-enabled eCommerce. This plugin allows eCommerce owner to set offline payment gateway using Indonesian banks via bank transfer to their customers.
 * Version:           0.1.2
 * Author:            Walter Pinem
 * Author URI:        https://walterpinem.me/
 * Developer: 		  Walter Pinem | Seni Berpikir
 * Developer URI: 	  https://www.seniberpikir.com/
 * Text Domain:       wltrpnm
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * 
 * Requires at least: 4.1
 * Tested up to: 5.0.1
 * 
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.2
 *
 * Copyright: © 2018 Walter Pinem.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Make sure we don't expose any info if called directly
add_action( 'plugins_loaded', 'indonesian_banks_init', 0 );

function indonesian_banks_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	DEFINE ('IB_PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
	DEFINE ('IB_PLUGIN_VERSION', get_file_data(__FILE__, array('Version' => 'Version'), false)['Version'] );

	require_once dirname( __FILE__ ) . '/banks/mandiri.php';
	require_once dirname( __FILE__ ) . '/banks/bri.php';
	require_once dirname( __FILE__ ) . '/banks/bni.php';
	require_once dirname( __FILE__ ) . '/banks/settings.php';

	add_filter( 'woocommerce_payment_gateways', 'add_indonesian_banks_gateway' );
}

function add_indonesian_banks_gateway( $methods ) {
	$methods[] = 'WC_Gateway_Mandiri';
	$methods[] = 'WC_Gateway_bni';
	$methods[] = 'WC_Gateway_bri';
	return $methods;
}

