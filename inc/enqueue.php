<?php
/**
 * Asset Enqueuing
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_is_dashboard_page(): bool {
    if ( ! is_page() ) return false;
    $slug = get_page_template_slug();
    return in_array( $slug, [
        'page-templates/page-dashboard-employer.php',
        'page-templates/page-dashboard-candidate.php',
    ], true );
}

function jobportal_is_map_page(): bool {
    return is_page() && get_page_template_slug() === 'page-templates/page-jobs-map.php';
}

add_action( 'wp_enqueue_scripts', function() {
    $v = JOBPORTAL_VERSION;

    // Google Fonts
    wp_enqueue_style( 'jobportal-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', [], null );

    // CSS
    wp_enqueue_style( 'jobportal-main',        JOBPORTAL_URI . '/assets/css/main.css',        [], $v );
    wp_enqueue_style( 'jobportal-jobs',        JOBPORTAL_URI . '/assets/css/jobs.css',        [], $v );
    wp_enqueue_style( 'jobportal-companies',   JOBPORTAL_URI . '/assets/css/companies.css',   [], $v );
    wp_enqueue_style( 'jobportal-candidates',  JOBPORTAL_URI . '/assets/css/candidates.css',  [], $v );
    wp_enqueue_style( 'jobportal-responsive',  JOBPORTAL_URI . '/assets/css/responsive.css',  [], $v );

    if ( jobportal_is_dashboard_page() ) {
        wp_enqueue_style( 'jobportal-dashboard', JOBPORTAL_URI . '/assets/css/dashboard.css', [], $v );
    }

    // Leaflet
    if ( jobportal_is_map_page() ) {
        wp_enqueue_style(  'leaflet',     'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4' );
        wp_enqueue_script( 'leaflet',     'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',  [], '1.9.4', true );
    }

    // jQuery (WordPress built-in)
    wp_enqueue_script( 'jquery' );

    // JS
    wp_enqueue_script( 'jobportal-main',          JOBPORTAL_URI . '/assets/js/main.js',          [ 'jquery' ], $v, true );
    wp_enqueue_script( 'jobportal-search',         JOBPORTAL_URI . '/assets/js/search.js',        [ 'jquery' ], $v, true );
    wp_enqueue_script( 'jobportal-notifications',  JOBPORTAL_URI . '/assets/js/notifications.js', [ 'jquery' ], $v, true );

    if ( is_user_logged_in() ) {
        wp_enqueue_script( 'jobportal-messaging', JOBPORTAL_URI . '/assets/js/messaging.js', [ 'jquery' ], $v, true );
    }

    if ( jobportal_is_dashboard_page() ) {
        wp_enqueue_script( 'chart-js',          'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js', [], '4', true );
        wp_enqueue_script( 'jobportal-dashboard', JOBPORTAL_URI . '/assets/js/dashboard.js', [ 'jquery', 'chart-js' ], $v, true );
    }

    if ( jobportal_is_map_page() ) {
        wp_enqueue_script( 'jobportal-map', JOBPORTAL_URI . '/assets/js/map.js', [ 'jquery', 'leaflet' ], $v, true );
    }

    // Localize
    wp_localize_script( 'jobportal-main', 'jobportal_ajax', [
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'jobportal_nonce' ),
        'user_id' => get_current_user_id(),
        'i18n'    => [
            'saving'    => __( 'Saving...', 'jobportal' ),
            'saved'     => __( 'Saved!', 'jobportal' ),
            'error'     => __( 'An error occurred. Please try again.', 'jobportal' ),
            'confirm_delete' => __( 'Are you sure? This cannot be undone.', 'jobportal' ),
        ],
    ] );
} );

// Admin CSS
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style( 'jobportal-admin', JOBPORTAL_URI . '/assets/css/admin.css', [], JOBPORTAL_VERSION );
} );
