<?php
/**
 * Membership Packages System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_get_packages( string $for = 'employer' ): array {
    return get_posts( [
        'post_type'      => 'jp_package',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'meta_query'     => [
            [ 'key' => '_package_for', 'value' => $for ],
        ],
    ] );
}

function jobportal_get_user_active_package( int $user_id ): ?object {
    return jobportal_get_user_package( $user_id );
}

function jobportal_activate_package( int $user_id, int $package_id, int $order_id = 0 ): bool {
    global $wpdb;

    $jobs_limit    = (int) get_post_meta( $package_id, '_jobs_limit',    true );
    $duration_days = (int) get_post_meta( $package_id, '_duration_days', true );
    $expires_at    = $duration_days ? gmdate( 'Y-m-d H:i:s', strtotime( "+{$duration_days} days" ) ) : null;

    // Deactivate existing
    $wpdb->update(
        $wpdb->prefix . 'jp_package_orders',
        [ 'status' => 'inactive' ],
        [ 'user_id' => $user_id, 'status' => 'active' ],
        [ '%s' ],
        [ '%d', '%s' ]
    );

    $result = $wpdb->insert(
        $wpdb->prefix . 'jp_package_orders',
        [
            'user_id'        => $user_id,
            'package_id'     => $package_id,
            'package_name'   => get_the_title( $package_id ),
            'order_id'       => $order_id,
            'jobs_limit'     => $jobs_limit ?: -1,
            'jobs_remaining' => $jobs_limit ?: -1,
            'status'         => 'active',
            'activated_at'   => current_time( 'mysql' ),
            'expires_at'     => $expires_at,
        ],
        [ '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s', '%s' ]
    );

    if ( $result ) {
        do_action( 'jobportal_package_activated', $user_id, $package_id );
    }

    return (bool) $result;
}

function jobportal_decrement_package_jobs( int $user_id ): void {
    global $wpdb;
    $package = jobportal_get_user_package( $user_id );
    if ( $package && $package->jobs_remaining > 0 ) {
        $wpdb->update(
            $wpdb->prefix . 'jp_package_orders',
            [ 'jobs_remaining' => $package->jobs_remaining - 1 ],
            [ 'id' => $package->id ],
            [ '%d' ],
            [ '%d' ]
        );
    }
}

function jobportal_get_package_features( int $package_id ): array {
    $features = get_post_meta( $package_id, '_features', true );
    return is_array( $features ) ? $features : [];
}

// WooCommerce order hook
add_action( 'woocommerce_order_status_completed', 'jobportal_woocommerce_order_completed', 10, 1 );

function jobportal_woocommerce_order_completed( int $order_id ): void {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;
    $user_id = $order->get_customer_id();
    foreach ( $order->get_items() as $item ) {
        $product_id = $item->get_product_id();
        // Find matching package by WC product ID
        $packages = get_posts( [
            'post_type'      => 'jp_package',
            'meta_key'       => '_wc_product_id',
            'meta_value'     => $product_id,
            'posts_per_page' => 1,
        ] );
        if ( ! empty( $packages ) ) {
            jobportal_activate_package( $user_id, $packages[0]->ID, $order_id );
        }
    }
}

// Seed default packages on activation
add_action( 'after_switch_theme', 'jobportal_seed_packages' );

function jobportal_seed_packages(): void {
    $existing = get_posts( [ 'post_type' => 'jp_package', 'numberposts' => 1 ] );
    if ( ! empty( $existing ) ) return;

    $packages = [
        [ 'title' => 'Basic',      'price' => 29,  'jobs' => 5,   'days' => 30,  'for' => 'employer', 'features' => [ '5 Job Postings', '30 Days Active', 'Basic Support' ] ],
        [ 'title' => 'Pro',        'price' => 79,  'jobs' => 20,  'days' => 30,  'for' => 'employer', 'features' => [ '20 Job Postings', '30 Days Active', 'Featured Jobs', 'Priority Support' ], 'featured' => true ],
        [ 'title' => 'Enterprise', 'price' => 199, 'jobs' => -1,  'days' => 30,  'for' => 'employer', 'features' => [ 'Unlimited Job Postings', '30 Days Active', 'Featured Jobs', 'Dedicated Support' ] ],
    ];

    foreach ( $packages as $pkg ) {
        $post_id = wp_insert_post( [
            'post_type'   => 'jp_package',
            'post_title'  => $pkg['title'],
            'post_status' => 'publish',
        ] );
        if ( $post_id ) {
            update_post_meta( $post_id, '_price',          $pkg['price'] );
            update_post_meta( $post_id, '_jobs_limit',     $pkg['jobs'] );
            update_post_meta( $post_id, '_duration_days',  $pkg['days'] );
            update_post_meta( $post_id, '_package_for',    $pkg['for'] );
            update_post_meta( $post_id, '_features',       $pkg['features'] );
            update_post_meta( $post_id, '_is_featured',    $pkg['featured'] ?? false );
        }
    }
}
