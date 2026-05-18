<?php
/**
 * Application System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_submit_application( int $job_id, int $candidate_id, array $data = [] ): int|false {
    global $wpdb;

    if ( jobportal_has_applied( $job_id, $candidate_id ) ) return false;

    $result = $wpdb->insert(
        $wpdb->prefix . 'civi_applications',
        [
            'job_id'       => $job_id,
            'candidate_id' => $candidate_id,
            'status'       => 'pending',
            'cover_letter' => sanitize_textarea_field( $data['cover_letter'] ?? '' ),
            'cv_file'      => esc_url_raw( $data['cv_file'] ?? '' ),
            'applied_at'   => current_time( 'mysql' ),
        ],
        [ '%d', '%d', '%s', '%s', '%s', '%s' ]
    );

    if ( ! $result ) return false;

    $application_id = $wpdb->insert_id;

    // Notify employer
    $company_id   = (int) get_post_meta( $job_id, '_company_id', true );
    $employer_id  = $company_id ? (int) get_post_field( 'post_author', $company_id ) : (int) get_post_field( 'post_author', $job_id );
    $candidate    = get_userdata( $candidate_id );

    jobportal_add_notification( $employer_id, 'application',
        sprintf( __( '%s applied for %s', 'jobportal' ), $candidate->display_name, get_the_title( $job_id ) ),
        admin_url( 'admin.php?page=jobportal-applications' )
    );

    do_action( 'jobportal_application_submitted', $application_id, $job_id, $candidate_id );

    return $application_id;
}

function jobportal_get_applications( int $job_id, array $args = [] ): array {
    global $wpdb;
    $status = sanitize_key( $args['status'] ?? '' );
    $sql    = $wpdb->prepare( "SELECT a.*, u.display_name, u.user_email FROM {$wpdb->prefix}civi_applications a INNER JOIN {$wpdb->users} u ON u.ID = a.candidate_id WHERE a.job_id = %d", $job_id );
    if ( $status ) $sql .= $wpdb->prepare( ' AND a.status = %s', $status );
    $sql .= ' ORDER BY a.applied_at DESC';
    return $wpdb->get_results( $sql ) ?: [];
}

function jobportal_get_candidate_applications( int $user_id ): array {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT a.*, p.post_title as job_title FROM {$wpdb->prefix}civi_applications a INNER JOIN {$wpdb->posts} p ON p.ID = a.job_id WHERE a.candidate_id = %d ORDER BY a.applied_at DESC",
        $user_id
    ) ) ?: [];
}

function jobportal_update_application_status( int $app_id, string $status, int $employer_id ): bool {
    global $wpdb;
    $allowed = [ 'pending', 'reviewing', 'shortlisted', 'hired', 'rejected' ];
    if ( ! in_array( $status, $allowed, true ) ) return false;

    $result = $wpdb->update(
        $wpdb->prefix . 'civi_applications',
        [ 'status' => $status ],
        [ 'id'     => $app_id ],
        [ '%s' ],
        [ '%d' ]
    );

    if ( $result !== false ) {
        $app = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}civi_applications WHERE id = %d", $app_id ) );
        if ( $app ) {
            jobportal_add_notification( $app->candidate_id, 'application',
                sprintf( __( 'Your application for %s has been updated to: %s', 'jobportal' ), get_the_title( $app->job_id ), ucfirst( $status ) )
            );
            do_action( 'jobportal_application_status_changed', $app_id, $status, $app->candidate_id );
        }
    }

    return $result !== false;
}

function jobportal_already_applied( int $job_id, int $user_id ): bool {
    return jobportal_has_applied( $job_id, $user_id );
}

// AJAX: Apply for job
add_action( 'wp_ajax_jobportal_apply_job', 'jobportal_ajax_apply_job' );

function jobportal_ajax_apply_job(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) wp_send_json_error( __( 'Please log in to apply.', 'jobportal' ) );
    if ( ! jobportal_is_candidate( $user_id ) ) wp_send_json_error( __( 'Only candidates can apply for jobs.', 'jobportal' ) );

    $job_id = (int) ( $_POST['job_id'] ?? 0 );
    if ( ! $job_id ) wp_send_json_error( __( 'Invalid job.', 'jobportal' ) );

    $result = jobportal_submit_application( $job_id, $user_id, [
        'cover_letter' => $_POST['cover_letter'] ?? '',
        'cv_file'      => get_user_meta( $user_id, 'civi_cv_file', true ),
    ] );

    if ( ! $result ) wp_send_json_error( __( 'You have already applied or an error occurred.', 'jobportal' ) );
    wp_send_json_success( __( 'Application submitted successfully!', 'jobportal' ) );
}

// AJAX: Update application status
add_action( 'wp_ajax_jobportal_update_application_status', 'jobportal_ajax_update_application_status' );

function jobportal_ajax_update_application_status(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! jobportal_is_employer( $user_id ) ) wp_send_json_error();

    $app_id = (int) ( $_POST['app_id'] ?? 0 );
    $status = sanitize_key( $_POST['status'] ?? '' );

    if ( jobportal_update_application_status( $app_id, $status, $user_id ) ) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
