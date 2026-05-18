<?php
/**
 * Company reviews and ratings
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Submit a company review.
 */
function civijobs_submit_review( int $company_id, int $reviewer_id, int $rating, string $title, string $review_text ): int|false {
    global $wpdb;

    if ( civijobs_has_reviewed( $company_id, $reviewer_id ) ) {
        return false;
    }

    $rating = max( 1, min( 5, $rating ) );

    $inserted = $wpdb->insert(
        "{$wpdb->prefix}civi_company_reviews",
        [
            'company_id'  => $company_id,
            'reviewer_id' => $reviewer_id,
            'rating'      => $rating,
            'title'       => sanitize_text_field( $title ),
            'review'      => sanitize_textarea_field( $review_text ),
        ],
        [ '%d', '%d', '%d', '%s', '%s' ]
    );

    if ( $inserted ) {
        civijobs_update_company_rating_meta( $company_id );
        return $wpdb->insert_id;
    }
    return false;
}

/**
 * Get reviews for a company.
 */
function civijobs_get_reviews( int $company_id, array $args = [] ): array {
    global $wpdb;

    $defaults = [ 'limit' => 10, 'offset' => 0, 'order' => 'DESC' ];
    $args     = wp_parse_args( $args, $defaults );
    $order    = $args['order'] === 'ASC' ? 'ASC' : 'DESC';

    // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT r.*, u.display_name as reviewer_name, u.user_email as reviewer_email
             FROM {$wpdb->prefix}civi_company_reviews r
             LEFT JOIN {$wpdb->users} u ON u.ID = r.reviewer_id
             WHERE r.company_id = %d
             ORDER BY r.created_at $order
             LIMIT %d OFFSET %d",
            $company_id,
            (int) $args['limit'],
            (int) $args['offset']
        )
    );
}

/**
 * Get average rating and count for a company.
 */
function civijobs_get_company_rating( int $company_id ): array {
    global $wpdb;
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT AVG(rating) as average, COUNT(*) as count
             FROM {$wpdb->prefix}civi_company_reviews
             WHERE company_id = %d",
            $company_id
        )
    );
    return [
        'average' => round( (float) ( $row->average ?? 0 ), 1 ),
        'count'   => (int) ( $row->count ?? 0 ),
    ];
}

/**
 * Check if a user has already reviewed a company.
 */
function civijobs_has_reviewed( int $company_id, int $user_id ): bool {
    global $wpdb;
    return (bool) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}civi_company_reviews
             WHERE company_id = %d AND reviewer_id = %d",
            $company_id,
            $user_id
        )
    );
}

/**
 * Delete a review (own review or admin).
 */
function civijobs_delete_review( int $review_id, int $user_id ): bool {
    global $wpdb;

    $review = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}civi_company_reviews WHERE id = %d",
            $review_id
        )
    );

    if ( ! $review ) {
        return false;
    }

    if ( $review->reviewer_id !== $user_id && ! current_user_can( 'administrator' ) ) {
        return false;
    }

    $wpdb->delete( "{$wpdb->prefix}civi_company_reviews", [ 'id' => $review_id ], [ '%d' ] );
    civijobs_update_company_rating_meta( (int) $review->company_id );
    return true;
}

/**
 * Recalculate and save average rating to post meta.
 */
function civijobs_update_company_rating_meta( int $company_id ): void {
    $rating = civijobs_get_company_rating( $company_id );
    update_post_meta( $company_id, '_avg_rating', $rating['average'] );
    update_post_meta( $company_id, '_review_count', $rating['count'] );
}

// ---- AJAX ----

add_action( 'wp_ajax_civijobs_submit_review', 'civijobs_ajax_submit_review' );
function civijobs_ajax_submit_review(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( [ 'message' => __( 'Please log in to leave a review.', 'civijobs' ) ] );
    }

    $company_id  = (int) ( $_POST['company_id'] ?? 0 );
    $rating      = (int) ( $_POST['rating'] ?? 0 );
    $title       = sanitize_text_field( $_POST['title'] ?? '' );
    $review_text = sanitize_textarea_field( $_POST['review'] ?? '' );

    if ( ! $company_id || ! $rating || ! $review_text ) {
        wp_send_json_error( [ 'message' => __( 'All fields are required.', 'civijobs' ) ] );
    }

    $result = civijobs_submit_review( $company_id, $user_id, $rating, $title, $review_text );

    if ( $result === false ) {
        wp_send_json_error( [ 'message' => __( 'You have already reviewed this company.', 'civijobs' ) ] );
    }

    wp_send_json_success( [ 'message' => __( 'Review submitted successfully!', 'civijobs' ) ] );
}

add_action( 'wp_ajax_civijobs_delete_review', 'civijobs_ajax_delete_review' );
function civijobs_ajax_delete_review(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    $review_id = (int) ( $_POST['review_id'] ?? 0 );
    $user_id   = get_current_user_id();

    if ( civijobs_delete_review( $review_id, $user_id ) ) {
        wp_send_json_success( [ 'message' => __( 'Review deleted.', 'civijobs' ) ] );
    } else {
        wp_send_json_error( [ 'message' => __( 'Could not delete review.', 'civijobs' ) ] );
    }
}
