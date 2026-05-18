<?php
/**
 * Meeting Scheduling System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_schedule_meeting( array $data ): int|false {
    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'jp_meetings',
        [
            'employer_id'   => (int) $data['employer_id'],
            'candidate_id'  => (int) $data['candidate_id'],
            'job_id'        => (int) ( $data['job_id'] ?? 0 ) ?: null,
            'title'         => sanitize_text_field( $data['title'] ),
            'description'   => sanitize_textarea_field( $data['description'] ?? '' ),
            'date_time'     => sanitize_text_field( $data['date_time'] ),
            'location'      => sanitize_text_field( $data['location'] ?? '' ),
            'meeting_url'   => esc_url_raw( $data['meeting_url'] ?? '' ),
            'status'        => 'pending',
            'created_at'    => current_time( 'mysql' ),
        ],
        [ '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );
    if ( $result ) {
        do_action( 'jobportal_meeting_scheduled', $wpdb->insert_id, $data );
        return $wpdb->insert_id;
    }
    return false;
}

function jobportal_get_meetings( int $user_id, string $role = 'candidate' ): array {
    global $wpdb;
    $col = $role === 'employer' ? 'employer_id' : 'candidate_id';
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}jp_meetings WHERE {$col} = %d ORDER BY date_time DESC",
        $user_id
    ) ) ?: [];
}

function jobportal_update_meeting_status( int $meeting_id, string $status, int $user_id ): bool {
    global $wpdb;
    $allowed = [ 'confirmed', 'cancelled', 'completed' ];
    if ( ! in_array( $status, $allowed, true ) ) return false;
    return (bool) $wpdb->update(
        $wpdb->prefix . 'jp_meetings',
        [ 'status' => $status ],
        [ 'id'     => $meeting_id ],
        [ '%s' ],
        [ '%d' ]
    );
}

function jobportal_get_meeting( int $meeting_id ): ?object {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}jp_meetings WHERE id = %d",
        $meeting_id
    ) );
}

// AJAX
add_action( 'wp_ajax_jobportal_schedule_meeting',      'jobportal_ajax_schedule_meeting' );
add_action( 'wp_ajax_jobportal_update_meeting_status', 'jobportal_ajax_update_meeting_status' );

function jobportal_ajax_schedule_meeting(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! jobportal_is_employer( $user_id ) ) wp_send_json_error( __( 'Only employers can schedule meetings.', 'jobportal' ) );

    $data = [
        'employer_id'  => $user_id,
        'candidate_id' => (int) ( $_POST['candidate_id'] ?? 0 ),
        'job_id'       => (int) ( $_POST['job_id'] ?? 0 ),
        'title'        => sanitize_text_field( $_POST['title'] ?? '' ),
        'description'  => sanitize_textarea_field( $_POST['description'] ?? '' ),
        'date_time'    => sanitize_text_field( $_POST['date_time'] ?? '' ),
        'location'     => sanitize_text_field( $_POST['location'] ?? '' ),
        'meeting_url'  => esc_url_raw( $_POST['meeting_url'] ?? '' ),
    ];

    if ( ! $data['candidate_id'] || ! $data['title'] || ! $data['date_time'] ) {
        wp_send_json_error( __( 'Required fields missing.', 'jobportal' ) );
    }

    $result = jobportal_schedule_meeting( $data );
    if ( $result ) {
        wp_send_json_success( __( 'Meeting scheduled!', 'jobportal' ) );
    } else {
        wp_send_json_error( __( 'Could not schedule meeting.', 'jobportal' ) );
    }
}

function jobportal_ajax_update_meeting_status(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id    = get_current_user_id();
    $meeting_id = (int) ( $_POST['meeting_id'] ?? 0 );
    $status     = sanitize_key( $_POST['status'] ?? '' );
    if ( jobportal_update_meeting_status( $meeting_id, $status, $user_id ) ) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
