<?php
/**
 * WooCommerce Integration
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) return;

// Add WooCommerce theme support
add_action( 'after_setup_theme', function() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
} );

// Remove default WC wrappers
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );

// Add theme wrappers
add_action( 'woocommerce_before_main_content', function() {
    echo '<div class="container" style="padding-top:40px;padding-bottom:60px"><div class="wc-content">';
}, 10 );

add_action( 'woocommerce_after_main_content', function() {
    echo '</div></div>';
}, 10 );

// Redirect post-checkout to dashboard
add_filter( 'woocommerce_get_return_url', function( $return_url, $order ) {
    $user_id = $order ? $order->get_customer_id() : get_current_user_id();
    $dash    = jobportal_get_dashboard_url( $user_id );
    return $dash ?: $return_url;
}, 10, 2 );

// Limit WC styles to WC pages only
add_filter( 'woocommerce_enqueue_styles', function( $enqueue_styles ) {
    if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
        return [];
    }
    return $enqueue_styles;
} );
