<?php
/**
 * WooCommerce Integration
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WooCommerce' ) ) return;

// Remove default WC wrappers so our theme controls layout
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', 'civijobs_wc_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'civijobs_wc_wrapper_end', 10 );

function civijobs_wc_wrapper_start() {
    echo '<div class="container" style="padding:40px 20px"><div class="wc-main">';
}
function civijobs_wc_wrapper_end() {
    echo '</div></div>';
}

// Theme support
add_action( 'after_setup_theme', function() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
} );

// Process package purchase
add_action( 'woocommerce_order_status_completed', 'civijobs_process_package_order' );
add_action( 'woocommerce_order_status_processing', 'civijobs_process_package_order' );

function civijobs_process_package_order( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    $customer_id = $order->get_customer_id();
    if ( ! $customer_id ) return;

    foreach ( $order->get_items() as $item ) {
        $product_id = $item->get_product_id();

        // Find package linked to this WC product
        $packages = get_posts( [
            'post_type'      => 'civi_package',
            'posts_per_page' => 1,
            'meta_query'     => [ [ 'key' => '_wc_product_id', 'value' => $product_id, 'compare' => '=' ] ],
        ] );

        if ( ! $packages ) continue;

        $pkg      = $packages[0];
        $duration = (int) get_post_meta( $pkg->ID, '_package_duration', true );

        // Cancel existing active package
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}civi_package_orders",
            [ 'status' => 'expired' ],
            [ 'user_id' => $customer_id, 'status' => 'active' ],
            [ '%s' ],
            [ '%d', '%s' ]
        );

        // Insert new package order
        $expires_at = $duration > 0 ? gmdate( 'Y-m-d H:i:s', strtotime( "+{$duration} days" ) ) : null;
        $wpdb->insert(
            "{$wpdb->prefix}civi_package_orders",
            [
                'user_id'      => $customer_id,
                'package_id'   => $pkg->ID,
                'package_name' => $pkg->post_title,
                'order_id'     => $order_id,
                'status'       => 'active',
                'expires_at'   => $expires_at,
                'created_at'   => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%s', '%d', '%s', '%s', '%s' ]
        );

        // Reset job posting counter
        delete_user_meta( $customer_id, 'civi_jobs_posted_this_cycle' );

        // Send email
        civijobs_email_package_activated( $customer_id, $pkg->post_title );

        // Add notification
        $notification_msg = sprintf( __( 'Your "%s" package has been activated.', 'civijobs' ), $pkg->post_title );
        civijobs_add_notification( $customer_id, $notification_msg, 'package', civijobs_get_dashboard_url() );
    }
}

// Redirect after checkout if it was a package purchase
add_filter( 'woocommerce_get_return_url', 'civijobs_wc_package_return_url', 10, 2 );

function civijobs_wc_package_return_url( $url, $order ) {
    if ( ! $order ) return $url;
    $customer_id = $order->get_customer_id();
    if ( ! $customer_id ) return $url;

    foreach ( $order->get_items() as $item ) {
        $product_id = $item->get_product_id();
        $packages = get_posts( [
            'post_type'      => 'civi_package',
            'posts_per_page' => 1,
            'meta_query'     => [ [ 'key' => '_wc_product_id', 'value' => $product_id, 'compare' => '=' ] ],
        ] );
        if ( $packages ) {
            $user = get_userdata( $customer_id );
            if ( $user && in_array( 'civi_employer', (array) $user->roles, true ) ) {
                return add_query_arg( 'section', 'packages', civijobs_get_dashboard_url( 'employer' ) );
            }
            return add_query_arg( 'section', 'packages', civijobs_get_dashboard_url( 'candidate' ) );
        }
    }
    return $url;
}

// Remove default WC styles for CiviJobs pages
add_filter( 'woocommerce_enqueue_styles', 'civijobs_filter_wc_styles' );

function civijobs_filter_wc_styles( $styles ) {
    // Keep WC styles on WC pages only
    if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
        return [];
    }
    return $styles;
}
