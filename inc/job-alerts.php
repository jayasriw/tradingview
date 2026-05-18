<?php
/**
 * Job Alerts System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_subscribe_alert( int $user_id, array $data ): int|false {
    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'jp_job_alerts',
        [
            'user_id'    => $user_id,
            'keyword'    => sanitize_text_field( $data['keyword']  ?? '' ),
            'location'   => sanitize_text_field( $data['location'] ?? '' ),
            'category'   => sanitize_key( $data['category']  ?? '' ),
            'job_type'   => sanitize_key( $data['job_type']   ?? '' ),
            'frequency'  => in_array( $data['frequency'] ?? '', [ 'daily', 'weekly' ], true ) ? $data['frequency'] : 'daily',
            'created_at' => current_time( 'mysql' ),
        ],
        [ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );
    return $result ? $wpdb->insert_id : false;
}

function jobportal_unsubscribe_alert( int $alert_id, int $user_id ): bool {
    global $wpdb;
    return (bool) $wpdb->delete(
        $wpdb->prefix . 'jp_job_alerts',
        [ 'id' => $alert_id, 'user_id' => $user_id ],
        [ '%d', '%d' ]
    );
}

function jobportal_get_user_alerts( int $user_id ): array {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}jp_job_alerts WHERE user_id = %d ORDER BY created_at DESC",
        $user_id
    ) ) ?: [];
}

function jobportal_match_jobs_for_alert( object $alert ): array {
    $args = [
        'post_type'      => 'jp_job',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'date_query'     => [ [ 'after' => $alert->last_sent ?? '1 week ago' ] ],
    ];
    if ( $alert->keyword )  $args['s'] = $alert->keyword;
    if ( $alert->category ) $args['tax_query'][] = [ 'taxonomy' => 'job_category', 'field' => 'slug', 'terms' => $alert->category ];
    if ( $alert->job_type ) $args['tax_query'][] = [ 'taxonomy' => 'job_type', 'field' => 'slug', 'terms' => $alert->job_type ];

    $query = new WP_Query( $args );
    return $query->posts;
}

// Cron
add_action( 'after_switch_theme', function() {
    if ( ! wp_next_scheduled( 'jobportal_process_daily_alerts' ) ) {
        wp_schedule_event( time(), 'daily', 'jobportal_process_daily_alerts' );
    }
    if ( ! wp_next_scheduled( 'jobportal_process_weekly_alerts' ) ) {
        wp_schedule_event( time(), 'weekly', 'jobportal_process_weekly_alerts' );
    }
} );

add_action( 'jobportal_process_daily_alerts',  fn() => jobportal_process_alerts( 'daily' ) );
add_action( 'jobportal_process_weekly_alerts', fn() => jobportal_process_alerts( 'weekly' ) );

function jobportal_process_alerts( string $frequency ): void {
    global $wpdb;
    $alerts = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}jp_job_alerts WHERE frequency = %s",
        $frequency
    ) );

    foreach ( $alerts as $alert ) {
        $jobs = jobportal_match_jobs_for_alert( $alert );
        if ( empty( $jobs ) ) continue;

        $user = get_userdata( $alert->user_id );
        if ( ! $user ) continue;

        do_action( 'jobportal_send_job_alert_email', $user, $alert, $jobs );

        $wpdb->update(
            $wpdb->prefix . 'jp_job_alerts',
            [ 'last_sent' => current_time( 'mysql' ) ],
            [ 'id' => $alert->id ],
            [ '%s' ],
            [ '%d' ]
        );
    }
}

// AJAX
add_action( 'wp_ajax_jobportal_subscribe_alert',   'jobportal_ajax_subscribe_alert' );
add_action( 'wp_ajax_jobportal_unsubscribe_alert', 'jobportal_ajax_unsubscribe_alert' );

function jobportal_ajax_subscribe_alert(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) wp_send_json_error( __( 'Please log in.', 'jobportal' ) );

    $result = jobportal_subscribe_alert( $user_id, $_POST );
    if ( $result ) {
        wp_send_json_success( __( 'Alert created!', 'jobportal' ) );
    } else {
        wp_send_json_error( __( 'Could not create alert.', 'jobportal' ) );
    }
}

function jobportal_ajax_unsubscribe_alert(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id  = get_current_user_id();
    $alert_id = (int) ( $_POST['alert_id'] ?? 0 );
    if ( jobportal_unsubscribe_alert( $alert_id, $user_id ) ) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
