<?php
/**
 * Job alerts / email subscription system
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Subscribe a user to a job alert.
 */
function civijobs_subscribe_alert( int $user_id, string $keywords, string $location, string $job_category, string $job_type, string $frequency = 'daily' ): int|false {
    global $wpdb;

    $frequency = in_array( $frequency, [ 'daily', 'weekly' ], true ) ? $frequency : 'daily';

    $inserted = $wpdb->insert(
        "{$wpdb->prefix}civi_job_alerts",
        [
            'user_id'      => $user_id,
            'keywords'     => sanitize_text_field( $keywords ),
            'location'     => sanitize_text_field( $location ),
            'job_category' => sanitize_text_field( $job_category ),
            'job_type'     => sanitize_text_field( $job_type ),
            'frequency'    => $frequency,
        ],
        [ '%d', '%s', '%s', '%s', '%s', '%s' ]
    );

    return $inserted ? $wpdb->insert_id : false;
}

/**
 * Unsubscribe from a job alert.
 */
function civijobs_unsubscribe_alert( int $alert_id, int $user_id ): bool {
    global $wpdb;
    return (bool) $wpdb->delete(
        "{$wpdb->prefix}civi_job_alerts",
        [ 'id' => $alert_id, 'user_id' => $user_id ],
        [ '%d', '%d' ]
    );
}

/**
 * Get all job alerts for a user.
 */
function civijobs_get_user_alerts( int $user_id ): array {
    global $wpdb;
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}civi_job_alerts WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        )
    );
}

/**
 * Find jobs matching an alert, published since last_sent.
 */
function civijobs_match_jobs_for_alert( object $alert ): array {
    $since = $alert->last_sent ?? ( gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) ) );

    $args = [
        'post_type'      => 'civi_job',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'date_query'     => [
            [ 'after' => $since, 'inclusive' => false ],
        ],
    ];

    if ( $alert->keywords ) {
        $args['s'] = $alert->keywords;
    }

    $tax_query = [];

    if ( $alert->job_category ) {
        $tax_query[] = [
            'taxonomy' => 'job_category',
            'field'    => 'slug',
            'terms'    => array_map( 'trim', explode( ',', $alert->job_category ) ),
        ];
    }

    if ( $alert->job_type ) {
        $tax_query[] = [
            'taxonomy' => 'job_type',
            'field'    => 'slug',
            'terms'    => array_map( 'trim', explode( ',', $alert->job_type ) ),
        ];
    }

    if ( ! empty( $tax_query ) ) {
        $args['tax_query'] = array_merge( [ 'relation' => 'AND' ], $tax_query );
    }

    if ( $alert->location ) {
        $args['meta_query'] = [
            [
                'key'     => '_job_location',
                'value'   => $alert->location,
                'compare' => 'LIKE',
            ],
        ];
    }

    return get_posts( $args );
}

/**
 * Send daily job alerts.
 */
function civijobs_send_daily_alerts(): void {
    civijobs_process_alerts( 'daily' );
}

/**
 * Send weekly job alerts.
 */
function civijobs_send_weekly_alerts(): void {
    civijobs_process_alerts( 'weekly' );
}

/**
 * Process and send job alerts for a given frequency.
 */
function civijobs_process_alerts( string $frequency ): void {
    global $wpdb;

    $alerts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}civi_job_alerts WHERE frequency = %s",
            $frequency
        )
    );

    foreach ( $alerts as $alert ) {
        $jobs = civijobs_match_jobs_for_alert( $alert );
        if ( empty( $jobs ) ) {
            continue;
        }
        civijobs_email_job_alert( (int) $alert->user_id, $jobs );

        $wpdb->update(
            "{$wpdb->prefix}civi_job_alerts",
            [ 'last_sent' => current_time( 'mysql' ) ],
            [ 'id' => $alert->id ],
            [ '%s' ],
            [ '%d' ]
        );
    }
}

// ---- Cron registration ----

function civijobs_register_cron_events(): void {
    if ( ! wp_next_scheduled( 'civijobs_daily_alerts' ) ) {
        wp_schedule_event( time(), 'daily', 'civijobs_daily_alerts' );
    }
    if ( ! wp_next_scheduled( 'civijobs_weekly_alerts' ) ) {
        wp_schedule_event( time(), 'weekly', 'civijobs_weekly_alerts' );
    }
}
add_action( 'after_switch_theme', 'civijobs_register_cron_events' );
add_action( 'civijobs_daily_alerts',  'civijobs_send_daily_alerts' );
add_action( 'civijobs_weekly_alerts', 'civijobs_send_weekly_alerts' );

function civijobs_deregister_cron_events(): void {
    wp_clear_scheduled_hook( 'civijobs_daily_alerts' );
    wp_clear_scheduled_hook( 'civijobs_weekly_alerts' );
}
add_action( 'switch_theme', 'civijobs_deregister_cron_events' );

// ---- AJAX ----

add_action( 'wp_ajax_civijobs_subscribe_job_alert',   'civijobs_ajax_subscribe_alert' );
add_action( 'wp_ajax_civijobs_unsubscribe_job_alert', 'civijobs_ajax_unsubscribe_alert' );

function civijobs_ajax_subscribe_alert(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( [ 'message' => __( 'Login required.', 'civijobs' ) ] );
    }

    $id = civijobs_subscribe_alert(
        $user_id,
        sanitize_text_field( $_POST['keywords'] ?? '' ),
        sanitize_text_field( $_POST['location'] ?? '' ),
        sanitize_text_field( $_POST['job_category'] ?? '' ),
        sanitize_text_field( $_POST['job_type'] ?? '' ),
        sanitize_key( $_POST['frequency'] ?? 'daily' )
    );

    $id
        ? wp_send_json_success( [ 'alert_id' => $id, 'message' => __( 'Job alert created!', 'civijobs' ) ] )
        : wp_send_json_error( [ 'message' => __( 'Failed to create alert.', 'civijobs' ) ] );
}

function civijobs_ajax_unsubscribe_alert(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );
    $user_id  = get_current_user_id();
    $alert_id = (int) ( $_POST['alert_id'] ?? 0 );

    civijobs_unsubscribe_alert( $alert_id, $user_id )
        ? wp_send_json_success( [ 'message' => __( 'Alert removed.', 'civijobs' ) ] )
        : wp_send_json_error( [ 'message' => __( 'Failed to remove alert.', 'civijobs' ) ] );
}
