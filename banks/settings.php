<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/**
 * @package   Indonesian Banks
 * @author    Walter Pinem
 * @category  Admin | Checkout Page
 * @copyright Copyright (c) 2018, Walter Pinem, Seni Berpikir
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Add an option on Advanced tab setting panel
 **/
add_filter( 'woocommerce_get_sections_advanced', 'indo_banks_add_section' );
function indo_banks_add_section( $sections ) {
	
	$sections['paymentcode'] = __( 'Payment Code', 'indo-banks' );
	return $sections;
	
}

/**
 * Add settings to the specific section we created before
 */
add_filter( 'woocommerce_get_settings_advanced', 'paymentcode_all_settings', 10, 2 );
function paymentcode_all_settings( $settings, $current_section ) {
	
	/**
	 * Check the current section is what we want
	 **/
	if ( $current_section == 'paymentcode' ) {
		$settings_paymentcode = array();
		// Add Title to the Settings
		$settings_paymentcode[] = array( 'name' => __( 'Payment Code Settings', 'indo-banks' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure Payment Code', 'indo-banks' ), 'id' => 'paymentcode' );
		// Add first checkbox option
		$settings_paymentcode[] = array(
			'name' => __( 'Add Unique Payment Code', 'indo-banks' ),
			'type' => 'title',
			'desc' => __( 'To easily confirm payment made by your customers, you can add a 3-digit payment code, generated automatically, on your checkout page. If enabled, a 3-digit code will increase the total payment.', 'indo-banks' ),
			'id'   => 'indo_banks_payment_code',
		);
		// Add second text field option
		$settings_paymentcode[] = array(
			'type'     => 'checkbox',
			'id'       => 'woocommerce_paymentcode_enabled',
			'name'     => __( 'Enable Setting', 'indo-banks' ),
			'desc'     => __( 'Activate Payment Code', 'indo-banks' ),
			'desc_tip'     => __( 'You can choose to enable or disable unique payment code anytime.', 'indo-banks' ),
			'default'  => 'no',
		);
		
		$settings_paymentcode[] = array( 'type' => 'sectionend', 'id' => 'indo_banks_paymentcode' );
		return $settings_paymentcode;
		
	/**
	 * If not, return the standard settings
	 **/
} else {
	return $settings;
}
}

/**
 * Register Payment Code Function
 * 
 * To easily identify customers' payments
 *
 * @return void
 */
if ( 'yes' == get_option( 'woocommerce_paymentcode_enabled' ) ) {	
	add_action( 'woocommerce_cart_calculate_fees', 'add_payment_code' );
	function add_payment_code(){
		global $woocommerce;

		$enable = 1;  
		$title = __( 'Payment Code', 'wltrpnm' );

		if ( $enable == 1 && $woocommerce->cart->subtotal != 0){
			if(! is_cart()){
				$cost = rand(100, 999);

				if($cost != 0)
					$woocommerce->cart->add_fee( __($title, 'woocommerce'), $cost);
			}
		}
	}
}