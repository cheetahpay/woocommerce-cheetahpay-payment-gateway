<?php
/**
 * @package CheetahPay
 * @version 1.0.0
 */
/*
Plugin Name: Cheetahpay WooCommerce payment gateway
Plugin URI: http://cheetahpay.com/
Description: A woocommerce paymernt gateway to help you receive airtime as payment
Author: Incofab Ikenna
Version: 1.0.0
Author URI: http://cheetahpay.com/
*/

define("DEV", true);

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'cheetahpay_init', 0 );

function cheetahpay_init() 
{
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	
	// If we made it this far, then include our Gateway Class
	include_once( 'wc_cheetahpay.php' );

	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'add_cheetahpay_payment_gateway' );

	function add_cheetahpay_payment_gateway( $methods ) {
		$methods[] = 'WC_Cheetahpay';
		return $methods;
	}
}

// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cheetahpay_action_links' );

function cheetahpay_action_links( $links ) 
{
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'spyr-authorizenet-aim' ) . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge( $plugin_links, $links );	
}

