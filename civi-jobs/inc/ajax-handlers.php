<?php
/**
 * Central AJAX action handlers
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---- Search ----
add_action( 'wp_ajax_civijobs_search',        'civijobs_ajax_search' );
add_action( 'wp_ajax_nopriv_civijobs_search', 'civijobs_ajax_search' );

function civijobs_ajax_search(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    $type     = sanitize_key( $_POST['search_type'] ?? 'jobs' );
    $keyword  = sanitize_text_field( $_POST['keyword'] ?? '' );
    $location = sanitize_text_field( $_POST['location'] ?? '' );
    $per_page = (int) civijobs_get_setting( 'jobs_per_page', 10 );
    $page     = max( 1, (int) ( $_POST['page'] ?? 1 ) );

    $tax_query = [];
    $meta_query = [];

    if ( $type === 'jobs' ) {
        $post_type = 'civi_job';

        foreach ( [ 'job_category', 'job_type', 'job_experience', 'job_salary' ] as $tax ) {
            $terms = array_map( 'sanitize_key', (array) ( $_POST[ $tax ] ?? [] ) );
            if ( ! empty( $terms ) ) {
                $tax_query[] = [ 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $terms ];
            }
        }

        if ( $location ) {
            $meta_query[] = [ 'key' => '_job_location', 'value' => $location, 'compare' => 'LIKE' ];
        }

        $sort = sanitize_key( $_POST['sort'] ?? 'newest' );
        $orderby = match ( $sort ) {
            'salary_high' => 'meta_value_num',
            'salary_low'  => 'meta_value_num',
            'oldest'      => 'date',
            default       => 'date',
        };
        $order = $sort === 'oldest' ? 'ASC' : 'DESC';

    } elseif ( $type === 'companies' ) {
        $post_type = 'civi_company';

        foreach ( [ 'company_industry', 'company_size' ] as $tax ) {
            $terms = array_map( 'sanitize_key', (array) ( $_POST[ $tax ] ?? [] ) );
            if ( ! empty( $terms ) ) {
                $tax_query[] = [ 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $terms ];
            }
        }

        $orderby = 'title';
        $order   = 'ASC';

    } else { // candidates
        $post_type = 'civi_resume';
        $orderby   = 'date';
        $order     = 'DESC';
    }

    $args = [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => $orderby,
        'order'          => $order,
    ];

    if ( $keyword ) {
        $args['s'] = $keyword;
    }
    if ( ! empty( $tax_query ) ) {
        $args['tax_query'] = array_merge( [ 'relation' => 'AND' ], $tax_query );
    }
    if ( ! empty( $meta_query ) ) {
        $args['meta_query'] = $meta_query;
    }

    $query = new WP_Query( $args );
    $posts = $query->posts;

    $items = [];
    foreach ( $posts as $post ) {
        if ( $type === 'jobs' ) {
            $items[] = civijobs_get_job_card_data( $post->ID );
        } elseif ( $type === 'companies' ) {
            $items[] = civijobs_get_company_card_data( $post->ID );
        } else {
            $items[] = civijobs_get_candidate_card_data( $post->ID );
        }
    }

    wp_send_json_success( [
        'items'       => $items,
        'total'       => $query->found_posts,
        'total_pages' => $query->max_num_pages,
        'page'        => $page,
    ] );
}

// ---- Map data ----
add_action( 'wp_ajax_civijobs_get_job_map_data',        'civijobs_ajax_get_map_data' );
add_action( 'wp_ajax_nopriv_civijobs_get_job_map_data', 'civijobs_ajax_get_map_data' );

function civijobs_ajax_get_map_data(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );
    wp_send_json_success( [ 'markers' => civijobs_get_map_markers() ] );
}

// ---- Save / unsave job ----
add_action( 'wp_ajax_civijobs_toggle_saved_job', 'civijobs_ajax_toggle_saved_job' );

function civijobs_ajax_toggle_saved_job(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    $user_id = get_current_user_id();
    $job_id  = (int) ( $_POST['job_id'] ?? 0 );

    if ( ! $user_id || ! $job_id ) {
        wp_send_json_error();
    }

    global $wpdb;

    if ( civijobs_has_saved( $job_id, $user_id ) ) {
        $wpdb->delete( "{$wpdb->prefix}civi_saved_jobs", [ 'user_id' => $user_id, 'job_id' => $job_id ], [ '%d', '%d' ] );
        wp_send_json_success( [ 'saved' => false ] );
    } else {
        $wpdb->insert( "{$wpdb->prefix}civi_saved_jobs", [ 'user_id' => $user_id, 'job_id' => $job_id ], [ '%d', '%d' ] );
        wp_send_json_success( [ 'saved' => true ] );
    }
}

// ---- Post job ----
add_action( 'wp_ajax_civijobs_post_job', 'civijobs_ajax_post_job' );

function civijobs_ajax_post_job(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    $user_id = get_current_user_id();
    if ( ! civijobs_is_employer( $user_id ) ) {
        wp_send_json_error( [ 'message' => __( 'Employers only.', 'civijobs' ) ] );
    }
    if ( ! civijobs_can_post_job( $user_id ) ) {
        wp_send_json_error( [ 'message' => __( 'You have no remaining job posting credits. Please upgrade your package.', 'civijobs' ) ] );
    }

    $title       = sanitize_text_field( $_POST['job_title'] ?? '' );
    $description = wp_kses_post( $_POST['job_description'] ?? '' );

    if ( ! $title || ! $description ) {
        wp_send_json_error( [ 'message' => __( 'Title and description are required.', 'civijobs' ) ] );
    }

    $job_id = wp_insert_post( [
        'post_title'   => $title,
        'post_content' => $description,
        'post_type'    => 'civi_job',
        'post_status'  => civijobs_get_setting( 'auto_approve_jobs', '0' ) ? 'publish' : 'pending',
        'post_author'  => $user_id,
    ] );

    if ( is_wp_error( $job_id ) ) {
        wp_send_json_error( [ 'message' => $job_id->get_error_message() ] );
    }

    // Meta
    $meta_fields = [
        '_employer_id'          => $user_id,
        '_job_location'         => sanitize_text_field( $_POST['location'] ?? '' ),
        '_salary_min'           => (float) ( $_POST['salary_min'] ?? 0 ),
        '_salary_max'           => (float) ( $_POST['salary_max'] ?? 0 ),
        '_salary_type'          => sanitize_key( $_POST['salary_type'] ?? 'annual' ),
        '_application_deadline' => sanitize_text_field( $_POST['deadline'] ?? '' ),
        '_experience_required'  => sanitize_text_field( $_POST['experience'] ?? '' ),
        '_vacancies'            => (int) ( $_POST['vacancies'] ?? 1 ),
        '_remote_ok'            => (bool) ( $_POST['remote_ok'] ?? false ),
        '_video_url'            => esc_url_raw( $_POST['video_url'] ?? '' ),
        '_company_id'           => (int) ( $_POST['company_id'] ?? 0 ),
        '_required_skills'      => array_map( 'sanitize_text_field', (array) ( $_POST['skills'] ?? [] ) ),
        '_benefits'             => array_map( 'sanitize_text_field', (array) ( $_POST['benefits'] ?? [] ) ),
    ];

    foreach ( $meta_fields as $key => $value ) {
        update_post_meta( $job_id, $key, $value );
    }

    // Taxonomies
    if ( ! empty( $_POST['job_category'] ) ) {
        wp_set_post_terms( $job_id, array_map( 'intval', (array) $_POST['job_category'] ), 'job_category' );
    }
    if ( ! empty( $_POST['job_type'] ) ) {
        wp_set_post_terms( $job_id, array_map( 'intval', (array) $_POST['job_type'] ), 'job_type' );
    }

    civijobs_decrement_package_jobs( $user_id );

    wp_send_json_success( [
        'job_id'  => $job_id,
        'message' => civijobs_get_setting( 'auto_approve_jobs', '0' )
            ? __( 'Job posted successfully!', 'civijobs' )
            : __( 'Job submitted and pending review.', 'civijobs' ),
        'url'     => get_permalink( $job_id ),
    ] );
}

// ---- Delete job ----
add_action( 'wp_ajax_civijobs_delete_job', 'civijobs_ajax_delete_job' );

function civijobs_ajax_delete_job(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    $job_id  = (int) ( $_POST['job_id'] ?? 0 );
    $user_id = get_current_user_id();
    $post    = get_post( $job_id );

    if ( ! $post || ( (int) $post->post_author !== $user_id && ! current_user_can( 'delete_others_posts' ) ) ) {
        wp_send_json_error( [ 'message' => __( 'Permission denied.', 'civijobs' ) ] );
    }

    wp_trash_post( $job_id );
    wp_send_json_success( [ 'message' => __( 'Job deleted.', 'civijobs' ) ] );
}

// ---- Toggle featured ----
add_action( 'wp_ajax_civijobs_toggle_featured_job', 'civijobs_ajax_toggle_featured' );

function civijobs_ajax_toggle_featured(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    if ( ! current_user_can( 'administrator' ) && ! civijobs_is_employer() ) {
        wp_send_json_error();
    }

    $job_id   = (int) ( $_POST['job_id'] ?? 0 );
    $featured = (bool) get_post_meta( $job_id, '_is_featured', true );
    update_post_meta( $job_id, '_is_featured', ! $featured );
    wp_send_json_success( [ 'featured' => ! $featured ] );
}

// ---- Upload CV ----
add_action( 'wp_ajax_civijobs_upload_cv', 'civijobs_ajax_upload_cv' );

function civijobs_ajax_upload_cv(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'Login required.', 'civijobs' ) ] );
    }

    if ( empty( $_FILES['cv_file'] ) ) {
        wp_send_json_error( [ 'message' => __( 'No file uploaded.', 'civijobs' ) ] );
    }

    $allowed = [ 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ];
    $finfo   = finfo_open( FILEINFO_MIME_TYPE );
    $mime    = finfo_file( $finfo, $_FILES['cv_file']['tmp_name'] );
    finfo_close( $finfo );

    if ( ! in_array( $mime, $allowed, true ) ) {
        wp_send_json_error( [ 'message' => __( 'Only PDF and Word documents are allowed.', 'civijobs' ) ] );
    }

    if ( $_FILES['cv_file']['size'] > 5 * 1024 * 1024 ) {
        wp_send_json_error( [ 'message' => __( 'File size must be under 5MB.', 'civijobs' ) ] );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    $uploaded = wp_handle_upload( $_FILES['cv_file'], [ 'test_form' => false ] );

    if ( isset( $uploaded['error'] ) ) {
        wp_send_json_error( [ 'message' => $uploaded['error'] ] );
    }

    update_user_meta( get_current_user_id(), 'civi_cv_file', $uploaded['url'] );
    wp_send_json_success( [ 'url' => $uploaded['url'] ] );
}

// ---- Upload avatar ----
add_action( 'wp_ajax_civijobs_upload_avatar', 'civijobs_ajax_upload_avatar' );

function civijobs_ajax_upload_avatar(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    if ( ! is_user_logged_in() || empty( $_FILES['avatar'] ) ) {
        wp_send_json_error();
    }

    $allowed = [ 'image/jpeg', 'image/png', 'image/webp' ];
    $finfo   = finfo_open( FILEINFO_MIME_TYPE );
    $mime    = finfo_file( $finfo, $_FILES['avatar']['tmp_name'] );
    finfo_close( $finfo );

    if ( ! in_array( $mime, $allowed, true ) ) {
        wp_send_json_error( [ 'message' => __( 'Only JPG/PNG/WebP images allowed.', 'civijobs' ) ] );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    $uploaded = wp_handle_upload( $_FILES['avatar'], [ 'test_form' => false ] );

    if ( isset( $uploaded['error'] ) ) {
        wp_send_json_error( [ 'message' => $uploaded['error'] ] );
    }

    update_user_meta( get_current_user_id(), 'civi_avatar', $uploaded['url'] );
    wp_send_json_success( [ 'url' => $uploaded['url'] ] );
}

// ---- Update profile ----
add_action( 'wp_ajax_civijobs_update_profile', 'civijobs_ajax_update_profile' );

function civijobs_ajax_update_profile(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error();
    }

    $string_fields = [
        'civi_headline', 'civi_bio', 'civi_location', 'civi_phone',
        'civi_website', 'civi_linkedin', 'civi_github', 'civi_visibility',
    ];

    foreach ( $string_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_user_meta( $user_id, $field, sanitize_textarea_field( $_POST[ $field ] ) );
        }
    }

    // Name
    if ( ! empty( $_POST['display_name'] ) ) {
        wp_update_user( [ 'ID' => $user_id, 'display_name' => sanitize_text_field( $_POST['display_name'] ) ] );
    }

    // JSON fields (arrays)
    foreach ( [ 'civi_skills', 'civi_experience', 'civi_education', 'civi_languages', 'civi_job_type_pref' ] as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_user_meta( $user_id, $field, (array) $_POST[ $field ] );
        }
    }

    wp_send_json_success( [ 'message' => __( 'Profile updated!', 'civijobs' ) ] );
}

// ---- Helper: job card data for AJAX response ----
function civijobs_get_job_card_data( int $job_id ): array {
    return [
        'id'        => $job_id,
        'title'     => get_the_title( $job_id ),
        'url'       => get_permalink( $job_id ),
        'company'   => get_the_title( (int) get_post_meta( $job_id, '_company_id', true ) ),
        'location'  => get_post_meta( $job_id, '_job_location', true ),
        'salary'    => civijobs_format_salary(
                           (float) get_post_meta( $job_id, '_salary_min', true ),
                           (float) get_post_meta( $job_id, '_salary_max', true ),
                           get_post_meta( $job_id, '_salary_type', true ) ?: 'annual'
                       ),
        'type'      => implode( ', ', wp_get_post_terms( $job_id, 'job_type', [ 'fields' => 'names' ] ) ),
        'featured'  => (bool) get_post_meta( $job_id, '_is_featured', true ),
        'remote'    => (bool) get_post_meta( $job_id, '_remote_ok', true ),
        'logo'      => get_the_post_thumbnail_url( $job_id, 'job-thumb' ),
        'date'      => civijobs_time_ago( get_the_date( 'Y-m-d H:i:s', $job_id ) ),
        'saved'     => is_user_logged_in() ? civijobs_has_saved( $job_id ) : false,
    ];
}

function civijobs_get_company_card_data( int $company_id ): array {
    $rating = civijobs_get_company_rating( $company_id );
    return [
        'id'         => $company_id,
        'name'       => get_the_title( $company_id ),
        'url'        => get_permalink( $company_id ),
        'logo'       => get_the_post_thumbnail_url( $company_id, 'company-logo' ),
        'location'   => get_post_meta( $company_id, '_company_location', true ),
        'industry'   => implode( ', ', wp_get_post_terms( $company_id, 'company_industry', [ 'fields' => 'names' ] ) ),
        'open_jobs'  => (int) get_post_meta( $company_id, '_open_jobs_count', true ),
        'avg_rating' => $rating['average'],
        'verified'   => (bool) get_post_meta( $company_id, '_is_verified', true ),
    ];
}

function civijobs_get_candidate_card_data( int $post_id ): array {
    $author_id = (int) get_post_field( 'post_author', $post_id );
    return [
        'id'       => $post_id,
        'name'     => get_the_title( $post_id ),
        'url'      => get_permalink( $post_id ),
        'avatar'   => civijobs_get_avatar_url( $author_id ),
        'headline' => get_post_meta( $post_id, '_headline', true ),
        'location' => get_post_meta( $post_id, '_location', true ),
        'skills'   => wp_get_post_terms( $post_id, 'resume_skills', [ 'fields' => 'names' ] ),
    ];
}
