<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );



/**
 * wc_shipment_tracking_add_custom_provider
 *
 * Adds custom provider to shipment tracking
 * Change the country name, the provider name, and the URL (it must include the %1$s)
 * Add one provider per line
*/

add_filter( 'wc_shipment_tracking_get_providers' , 'wc_shipment_tracking_add_custom_provider' );


function wc_shipment_tracking_add_custom_provider( $providers ) {

	$providers['France']['Porteur'] = 'https://track.aftership.com/';

	// etc...
	
	return $providers;
	
}

/**
 * Hide shipping providers not in use
*/

add_filter( 'wc_shipment_tracking_get_providers', 'custom_shipment_tracking' );

function custom_shipment_tracking( $providers ) {

    unset($providers['Australia']);
    unset($providers['Austria']);
    unset($providers['Brazil']);
    unset($providers['Belgium']);
    unset($providers['Canada']);
    unset($providers['Czech Republic']);
    unset($providers['Finland']);
    unset($providers['Germany']);
    unset($providers['Ireland']);
    unset($providers['Italy']);
    unset($providers['India']);
    unset($providers['Netherlands']);
    unset($providers['Romania']);
    unset($providers['South African']);
    unset($providers['Sweden']);
    unset($providers['New Zealand']);
    unset($providers['United Kingdom']);
	unset($providers['United States']);
    unset($providers['United States']['Fedex']);
    unset($providers['United States']['Fedex Sameday']);
    unset($providers['United States']['UPS']);
    unset($providers['United States']['USPS']);
    unset($providers['United States']['OnTrac']);
    unset($providers['United States']['DHL US']);
    unset($providers['United States']['FedEx Sameday']);

    return $providers;
}

/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function my_hide_shipping_when_free_is_available( $rates ) {
    $free = array();
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate->method_id ) {
            $free[ $rate_id ] = $rate;
            break;
        }
    }
    return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );


/**
 * @snippet       Display "Grátis" For Free Shipping Rates @ WooCommerce Cart & Checkout
 * @author        PseudoDev
 * @testedwith    WooCommerce 3.1.2
 */
 
add_filter( 'woocommerce_cart_shipping_method_full_label', 'add_gratis_to_shipping_label', 10, 2 );
   
function add_gratis_to_shipping_label( $full_label, $method ) {
 
// if shipping rate is 0, concatenate "Grátis" to the label
if ( ! ( $method->cost > 0 ) ) {
// get full label and replace its contents with "portes grátis"
$full_label = str_replace($full_label,"Portes Grátis",$full_label);
} 
 
// return original or edited shipping label
return $full_label;
 
}

