<?php
/**
 * Company Reviews System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_submit_review( int $company_id, int $user_id, array $data ): int|false {
    global $wpdb;

    if ( jobportal_has_reviewed( $company_id, $user_id ) ) return false;

    $result = $wpdb->insert(
        $wpdb->prefix . 'jp_company_reviews',
        [
            'company_id' => $company_id,
            'user_id'    => $user_id,
            'rating'     => min( 5, max( 1, (int) ( $data['rating'] ?? 3 ) ) ),
            'title'      => sanitize_text_field( $data['title'] ?? '' ),
            'review'     => sanitize_textarea_field( $data['review'] ?? '' ),
            'status'     => 'pending',
            'created_at' => current_time( 'mysql' ),
        ],
        [ '%d', '%d', '%d', '%s', '%s', '%s', '%s' ]
    );

    if ( $result ) {
        jobportal_update_company_rating_meta( $company_id );
        return $wpdb->insert_id;
    }
    return false;
}

function jobportal_get_reviews( int $company_id, string $status = 'approved' ): array {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT r.*, u.display_name FROM {$wpdb->prefix}jp_company_reviews r LEFT JOIN {$wpdb->users} u ON u.ID = r.user_id WHERE r.company_id = %d AND r.status = %s ORDER BY r.created_at DESC",
        $company_id, $status
    ) ) ?: [];
}

function jobportal_has_reviewed( int $company_id, int $user_id ): bool {
    global $wpdb;
    return (bool) $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}jp_company_reviews WHERE company_id = %d AND user_id = %d",
        $company_id, $user_id
    ) );
}

function jobportal_update_company_rating_meta( int $company_id ): void {
    $rating = jobportal_get_company_rating( $company_id );
    update_post_meta( $company_id, '_rating_average', $rating['average'] );
    update_post_meta( $company_id, '_rating_count',   $rating['count'] );
}

// AJAX
add_action( 'wp_ajax_jobportal_submit_review', 'jobportal_ajax_submit_review' );

function jobportal_ajax_submit_review(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id    = get_current_user_id();
    $company_id = (int) ( $_POST['company_id'] ?? 0 );
    if ( ! $user_id || ! $company_id ) wp_send_json_error();

    $result = jobportal_submit_review( $company_id, $user_id, $_POST );
    if ( $result ) {
        wp_send_json_success( __( 'Review submitted and pending approval.', 'jobportal' ) );
    } else {
        wp_send_json_error( __( 'You have already reviewed this company.', 'jobportal' ) );
    }
}
