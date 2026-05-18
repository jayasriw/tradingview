<?php
/**
 * AJAX Handlers
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

// Search
add_action( 'wp_ajax_jobportal_search',        'jobportal_ajax_search' );
add_action( 'wp_ajax_nopriv_jobportal_search', 'jobportal_ajax_search' );

function jobportal_ajax_search(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );

    $keyword  = sanitize_text_field( $_POST['keyword']  ?? '' );
    $location = sanitize_text_field( $_POST['location'] ?? '' );
    $category = sanitize_text_field( $_POST['category'] ?? '' );
    $types    = array_map( 'sanitize_key', (array) ( $_POST['job_type'] ?? [] ) );
    $paged    = max( 1, (int) ( $_POST['paged'] ?? 1 ) );
    $remote   = (bool) ( $_POST['remote_only'] ?? false );
    $sort     = sanitize_key( $_POST['sort'] ?? 'date' );

    $args = [
        'post_type'      => 'jp_job',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'orderby'        => $sort === 'salary' ? 'meta_value_num' : 'date',
        'order'          => 'DESC',
    ];

    if ( $keyword ) $args['s'] = $keyword;
    if ( $sort === 'salary' ) $args['meta_key'] = '_salary_max';

    $tax_query = [];
    if ( $category ) {
        $tax_query[] = [ 'taxonomy' => 'job_category', 'field' => 'slug', 'terms' => $category ];
    }
    if ( $types ) {
        $tax_query[] = [ 'taxonomy' => 'job_type', 'field' => 'slug', 'terms' => $types ];
    }
    if ( $tax_query ) {
        $args['tax_query'] = array_merge( [ 'relation' => 'AND' ], $tax_query );
    }

    $meta_query = [];
    if ( $location ) {
        $meta_query[] = [ 'key' => '_job_location', 'value' => $location, 'compare' => 'LIKE' ];
    }
    if ( $remote ) {
        $meta_query[] = [ 'key' => '_remote_ok', 'value' => '1' ];
    }
    if ( $meta_query ) {
        $args['meta_query'] = array_merge( [ 'relation' => 'AND' ], $meta_query );
    }

    $query = new WP_Query( $args );
    $jobs  = [];
    foreach ( $query->posts as $job ) {
        $jobs[] = jobportal_get_job_card_data( $job->ID );
    }

    wp_send_json_success( [
        'jobs'        => $jobs,
        'total'       => $query->found_posts,
        'total_pages' => $query->max_num_pages,
        'paged'       => $paged,
    ] );
}

// Toggle saved job
add_action( 'wp_ajax_jobportal_toggle_saved_job', 'jobportal_ajax_toggle_saved_job' );

function jobportal_ajax_toggle_saved_job(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) wp_send_json_error( __( 'Please log in.', 'jobportal' ) );

    global $wpdb;
    $job_id = (int) ( $_POST['job_id'] ?? 0 );
    $table  = $wpdb->prefix . 'civi_saved_jobs';

    $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE user_id=%d AND job_id=%d", $user_id, $job_id ) );
    if ( $exists ) {
        $wpdb->delete( $table, [ 'user_id' => $user_id, 'job_id' => $job_id ] );
        wp_send_json_success( [ 'saved' => false ] );
    } else {
        $wpdb->insert( $table, [ 'user_id' => $user_id, 'job_id' => $job_id, 'created_at' => current_time( 'mysql' ) ] );
        wp_send_json_success( [ 'saved' => true ] );
    }
}

// Update profile
add_action( 'wp_ajax_jobportal_update_profile', 'jobportal_ajax_update_profile' );

function jobportal_ajax_update_profile(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) wp_send_json_error( __( 'Not authenticated.', 'jobportal' ) );

    $fields = [ 'civi_headline', 'civi_location', 'civi_phone', 'civi_bio', 'civi_website', 'civi_linkedin', 'civi_github', 'civi_hourly_rate', 'civi_visibility' ];
    foreach ( $fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_user_meta( $user_id, $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }

    if ( isset( $_POST['display_name'] ) ) {
        wp_update_user( [ 'ID' => $user_id, 'display_name' => sanitize_text_field( $_POST['display_name'] ) ] );
    }

    if ( isset( $_POST['civi_available_for_hire'] ) ) {
        update_user_meta( $user_id, 'civi_available_for_hire', '1' );
    } else {
        update_user_meta( $user_id, 'civi_available_for_hire', '0' );
    }

    if ( isset( $_POST['civi_skills'] ) && is_array( $_POST['civi_skills'] ) ) {
        update_user_meta( $user_id, 'civi_skills', array_map( 'sanitize_text_field', $_POST['civi_skills'] ) );
    }

    wp_send_json_success( __( 'Profile updated successfully.', 'jobportal' ) );
}

// Helper: get job card data
function jobportal_get_job_card_data( int $job_id ): array {
    $company_id = (int) get_post_meta( $job_id, '_company_id', true );
    return [
        'id'           => $job_id,
        'title'        => get_the_title( $job_id ),
        'url'          => get_permalink( $job_id ),
        'company_name' => $company_id ? get_the_title( $company_id ) : '',
        'company_url'  => $company_id ? get_permalink( $company_id ) : '',
        'company_logo' => $company_id ? get_the_post_thumbnail_url( $company_id, 'company-logo' ) : '',
        'location'     => get_post_meta( $job_id, '_job_location', true ),
        'remote'       => (bool) get_post_meta( $job_id, '_remote_ok', true ),
        'salary'       => jobportal_format_salary(
            (float) get_post_meta( $job_id, '_salary_min', true ),
            (float) get_post_meta( $job_id, '_salary_max', true )
        ),
        'types'        => wp_get_post_terms( $job_id, 'job_type', [ 'fields' => 'names' ] ),
        'featured'     => (bool) get_post_meta( $job_id, '_is_featured', true ),
        'posted_date'  => get_the_date( 'Y-m-d H:i:s', $job_id ),
        'deadline'     => get_post_meta( $job_id, '_application_deadline', true ),
    ];
}
