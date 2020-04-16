<?php

namespace PixelYourSite\GA\Helpers;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function adaptDynamicRemarketingParams( $params ) {
	
	if ( PixelYourSite\PYS()->getOption( 'google_retargeting_logic' ) == 'ecomm' ) {
		
		return array(
			'ecomm_prodid'     => $params['product_id'],
			'ecomm_pagetype'   => $params['page_type'],
			'ecomm_totalvalue' => $params['total_value'],
		);
		
	} else {
		
		// custom vertical has different than retail page types
		$page_types = array(
			'search' => 'searchresults',
			'product' => 'offerdetail',
			'category' => null, //not supported by custom vertical
			'cart' => 'conversionintent',
			'checkout' => 'conversionintent',
			'purchase' => 'conversion'
		);
		
		return array(
			'dynx_itemid'     => $params['product_id'],
			'dynx_pagetype'   => $page_types[ $params['page_type'] ],
			'dynx_totalvalue' => $params['total_value'],
		);
		
	}
	
}

/**
 * Render Cross Domain Domain text field
 *
 * @param int    $index
 */
function renderCrossDomainDomain( $index = 0 ) {
    
    $slug = PixelYourSite\GA()->getSlug();
    
    $attr_name = "pys[$slug][cross_domain_domains][]";
    $attr_id = 'pys_' . $slug . '_cross_domain_domains_' . $index;
    
    $values = (array) PixelYourSite\GA()->getOption( 'cross_domain_domains' );
    $attr_value = isset( $values[ $index ] ) ? $values[ $index ] : null;
    
    ?>
    
    <input type="text" name="<?php esc_attr_e( $attr_name ); ?>"
           id="<?php esc_attr_e( $attr_id ); ?>"
           value="<?php esc_attr_e( $attr_value ); ?>"
           placeholder="Enter domain"
           class="form-control">
    
    <?php
    
}