<?php
/**
 * Enqueue scripts and styles
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue front-end assets.
 */
function civijobs_enqueue_assets(): void {
    $ver = CIVIJOBS_VERSION;
    $css = CIVIJOBS_URL . '/assets/css';
    $js  = CIVIJOBS_URL . '/assets/js';

    // ---- Styles ----
    wp_enqueue_style(
        'civijobs-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        [],
        null
    );

    wp_enqueue_style( 'civijobs-main',       "$css/main.css",       [ 'civijobs-fonts' ], $ver );
    wp_enqueue_style( 'civijobs-jobs',       "$css/jobs.css",       [ 'civijobs-main' ],  $ver );
    wp_enqueue_style( 'civijobs-companies',  "$css/companies.css",  [ 'civijobs-main' ],  $ver );
    wp_enqueue_style( 'civijobs-candidates', "$css/candidates.css", [ 'civijobs-main' ],  $ver );
    wp_enqueue_style( 'civijobs-responsive', "$css/responsive.css", [ 'civijobs-main' ],  $ver );

    // Dashboard styles
    if ( civijobs_is_dashboard_page() ) {
        wp_enqueue_style( 'civijobs-dashboard', "$css/dashboard.css", [ 'civijobs-main' ], $ver );
    }

    // Map styles (Leaflet)
    if ( civijobs_is_map_page() ) {
        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            [],
            '1.9.4'
        );
        wp_enqueue_style(
            'leaflet-markercluster',
            'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css',
            [ 'leaflet' ],
            '1.5.3'
        );
        wp_enqueue_style(
            'leaflet-markercluster-default',
            'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css',
            [ 'leaflet-markercluster' ],
            '1.5.3'
        );
    }

    // ---- Scripts ----
    wp_enqueue_script( 'jquery' );

    wp_enqueue_script(
        'civijobs-main',
        "$js/main.js",
        [ 'jquery' ],
        $ver,
        true
    );

    wp_enqueue_script(
        'civijobs-search',
        "$js/search.js",
        [ 'jquery', 'civijobs-main' ],
        $ver,
        true
    );

    wp_enqueue_script(
        'civijobs-notifications',
        "$js/notifications.js",
        [ 'jquery', 'civijobs-main' ],
        $ver,
        true
    );

    if ( civijobs_is_dashboard_page() ) {
        // Chart.js for analytics
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
            [],
            '4.4.0',
            true
        );
        wp_enqueue_script(
            'civijobs-dashboard',
            "$js/dashboard.js",
            [ 'jquery', 'civijobs-main', 'chartjs' ],
            $ver,
            true
        );
        wp_enqueue_script(
            'civijobs-messaging',
            "$js/messaging.js",
            [ 'jquery', 'civijobs-main' ],
            $ver,
            true
        );
    }

    if ( civijobs_is_map_page() ) {
        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            [],
            '1.9.4',
            true
        );
        wp_enqueue_script(
            'leaflet-markercluster',
            'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js',
            [ 'leaflet' ],
            '1.5.3',
            true
        );
        wp_enqueue_script(
            'civijobs-map',
            "$js/map.js",
            [ 'jquery', 'leaflet', 'leaflet-markercluster' ],
            $ver,
            true
        );
    }

    // Localize script data
    $user_id = get_current_user_id();
    wp_localize_script( 'civijobs-main', 'civijobs_ajax', [
        'ajaxurl'        => admin_url( 'admin-ajax.php' ),
        'nonce'          => wp_create_nonce( 'civijobs_nonce' ),
        'is_logged_in'   => is_user_logged_in(),
        'current_user_id'=> $user_id,
        'user_role'      => $user_id ? civijobs_get_user_role( $user_id ) : '',
        'theme_url'      => CIVIJOBS_URL,
        'dashboard_url'  => $user_id ? civijobs_get_dashboard_url() : '',
        'login_url'      => wp_login_url( get_permalink() ),
        'i18n'           => [
            'saved'        => __( 'Job saved!', 'civijobs' ),
            'unsaved'      => __( 'Job removed from saved list.', 'civijobs' ),
            'applied'      => __( 'Application submitted!', 'civijobs' ),
            'error'        => __( 'An error occurred. Please try again.', 'civijobs' ),
            'confirm_del'  => __( 'Are you sure you want to delete this?', 'civijobs' ),
            'login_req'    => __( 'Please log in to continue.', 'civijobs' ),
            'sending'      => __( 'Sending…', 'civijobs' ),
            'sent'         => __( 'Message sent!', 'civijobs' ),
        ],
    ] );
}
add_action( 'wp_enqueue_scripts', 'civijobs_enqueue_assets' );

/**
 * Enqueue admin assets.
 */
function civijobs_admin_enqueue_assets( string $hook ): void {
    if ( ! str_contains( $hook, 'civijobs' ) && ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        return;
    }
    wp_enqueue_style(
        'civijobs-admin',
        CIVIJOBS_URL . '/assets/css/admin.css',
        [],
        CIVIJOBS_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'civijobs_admin_enqueue_assets' );

/**
 * Whether the current page is a dashboard page.
 */
function civijobs_is_dashboard_page(): bool {
    return is_page_template( [
        'page-templates/page-dashboard-employer.php',
        'page-templates/page-dashboard-candidate.php',
    ] );
}

/**
 * Whether the current page uses the map layout.
 */
function civijobs_is_map_page(): bool {
    return is_page_template( 'page-templates/page-jobs-map.php' );
}
