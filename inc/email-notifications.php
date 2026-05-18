<?php
/**
 * Email Notification System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_send_email( string $to, string $subject, string $template, array $vars = [] ): bool {
    $body = jobportal_get_email_template( $template, $vars );
    if ( ! $body ) return false;

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
    ];

    return wp_mail( $to, $subject, jobportal_email_wrap( $body, $subject ), $headers );
}

function jobportal_get_email_template( string $template, array $vars = [] ): string {
    $file = JOBPORTAL_DIR . '/email-templates/' . $template . '.php';
    if ( ! file_exists( $file ) ) return '';
    extract( $vars, EXTR_SKIP );
    ob_start();
    include $file;
    return ob_get_clean();
}

function jobportal_email_wrap( string $content, string $title ): string {
    $site_name = get_bloginfo( 'name' );
    $primary   = get_theme_mod( 'jobportal_primary_color', '#6366f1' );
    return '
    <div style="font-family:Inter,Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff">
        <div style="background:' . esc_attr( $primary ) . ';padding:24px 32px">
            <h1 style="color:#fff;margin:0;font-size:1.5rem">' . esc_html( $site_name ) . '</h1>
        </div>
        <div style="padding:32px">' . $content . '</div>
        <div style="background:#f9fafb;padding:20px 32px;text-align:center;font-size:0.8rem;color:#6b7280">
            &copy; ' . gmdate( 'Y' ) . ' ' . esc_html( $site_name ) . '. All rights reserved.
        </div>
    </div>';
}

// Hook: application submitted
add_action( 'jobportal_application_submitted', function( $app_id, $job_id, $candidate_id ) {
    $candidate = get_userdata( $candidate_id );
    $company_id = (int) get_post_meta( $job_id, '_company_id', true );
    $employer_id = $company_id ? (int) get_post_field( 'post_author', $company_id ) : (int) get_post_field( 'post_author', $job_id );
    $employer = get_userdata( $employer_id );

    // Email to candidate
    jobportal_send_email( $candidate->user_email,
        sprintf( __( 'Application submitted: %s', 'jobportal' ), get_the_title( $job_id ) ),
        'application-submitted',
        [ 'candidate_name' => $candidate->display_name, 'job_title' => get_the_title( $job_id ), 'company_name' => get_the_title( $company_id ), 'dashboard_url' => jobportal_get_dashboard_url( $candidate_id ) ]
    );

    // Email to employer
    if ( $employer ) {
        jobportal_send_email( $employer->user_email,
            sprintf( __( 'New application: %s', 'jobportal' ), get_the_title( $job_id ) ),
            'new-application',
            [ 'employer_name' => $employer->display_name, 'candidate_name' => $candidate->display_name, 'job_title' => get_the_title( $job_id ), 'applications_url' => add_query_arg( 'section', 'applications', jobportal_get_dashboard_url( $employer_id ) ) ]
        );
    }
}, 10, 3 );

// Hook: meeting scheduled
add_action( 'jobportal_meeting_scheduled', function( $meeting_id, $data ) {
    $candidate = get_userdata( $data['candidate_id'] );
    if ( ! $candidate ) return;
    $employer  = get_userdata( $data['employer_id'] );
    jobportal_send_email( $candidate->user_email,
        __( 'Meeting scheduled', 'jobportal' ),
        'meeting-scheduled',
        [
            'recipient_name'   => $candidate->display_name,
            'other_party_name' => $employer ? $employer->display_name : '',
            'title'            => $data['title'],
            'date_formatted'   => gmdate( 'l, F j, Y', strtotime( $data['date_time'] ) ),
            'time_formatted'   => gmdate( 'g:i A', strtotime( $data['date_time'] ) ),
            'location'         => $data['location'] ?? '',
            'meeting_url'      => $data['meeting_url'] ?? '',
            'dashboard_url'    => jobportal_get_dashboard_url( $data['candidate_id'] ),
        ]
    );
}, 10, 2 );
