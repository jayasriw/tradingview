<?php
/**
 * Helper / utility functions
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get all meta data for a job post.
 */
function civijobs_get_job_data( int $job_id ): array {
    $post = get_post( $job_id );
    if ( ! $post || $post->post_type !== 'civi_job' ) {
        return [];
    }

    $meta = get_post_meta( $job_id );
    $scalar = static fn( $key, $default = '' ) => $meta[ $key ][0] ?? $default;

    return [
        'id'          => $job_id,
        'title'       => get_the_title( $job_id ),
        'excerpt'     => get_the_excerpt( $post ),
        'content'     => apply_filters( 'the_content', $post->post_content ),
        'url'         => get_permalink( $job_id ),
        'status'      => $post->post_status,
        'date'        => $post->post_date,
        'company_id'  => (int) $scalar( '_company_id' ),
        'salary_min'  => (float) $scalar( '_salary_min' ),
        'salary_max'  => (float) $scalar( '_salary_max' ),
        'salary_type' => $scalar( '_salary_type', 'annual' ),
        'location'    => $scalar( '_job_location' ),
        'latitude'    => (float) $scalar( '_latitude' ),
        'longitude'   => (float) $scalar( '_longitude' ),
        'remote'      => (bool) $scalar( '_remote_ok' ),
        'deadline'    => $scalar( '_application_deadline' ),
        'is_featured' => (bool) $scalar( '_is_featured' ),
        'video_url'   => $scalar( '_video_url' ),
        'experience'  => $scalar( '_experience_required' ),
        'vacancies'   => (int) $scalar( '_vacancies', 1 ),
        'skills'      => maybe_unserialize( $scalar( '_required_skills', 'a:0:{}' ) ),
        'benefits'    => maybe_unserialize( $scalar( '_benefits', 'a:0:{}' ) ),
        'categories'  => wp_get_post_terms( $job_id, 'job_category', [ 'fields' => 'names' ] ),
        'types'       => wp_get_post_terms( $job_id, 'job_type', [ 'fields' => 'names' ] ),
        'thumbnail'   => get_the_post_thumbnail_url( $job_id, 'job-thumb' ),
        'views'       => (int) $scalar( '_job_views' ),
        'apply_count' => civijobs_get_application_count( $job_id ),
    ];
}

/**
 * Get all meta data for a company post.
 */
function civijobs_get_company_data( int $company_id ): array {
    $post = get_post( $company_id );
    if ( ! $post || $post->post_type !== 'civi_company' ) {
        return [];
    }

    $meta   = get_post_meta( $company_id );
    $scalar = static fn( $key, $default = '' ) => $meta[ $key ][0] ?? $default;

    $rating = civijobs_get_company_rating( $company_id );

    return [
        'id'          => $company_id,
        'name'        => get_the_title( $company_id ),
        'description' => apply_filters( 'the_content', $post->post_content ),
        'url'         => get_permalink( $company_id ),
        'website'     => $scalar( '_company_website' ),
        'email'       => $scalar( '_company_email' ),
        'phone'       => $scalar( '_company_phone' ),
        'founded'     => $scalar( '_founded_year' ),
        'employees'   => $scalar( '_company_size' ),
        'location'    => $scalar( '_company_location' ),
        'latitude'    => (float) $scalar( '_latitude' ),
        'longitude'   => (float) $scalar( '_longitude' ),
        'owner_id'    => (int) $scalar( '_owner_id' ),
        'logo'        => get_the_post_thumbnail_url( $company_id, 'company-logo' ),
        'cover'       => $scalar( '_cover_image' ),
        'twitter'     => $scalar( '_twitter' ),
        'linkedin'    => $scalar( '_linkedin' ),
        'facebook'    => $scalar( '_facebook' ),
        'industry'    => wp_get_post_terms( $company_id, 'company_industry', [ 'fields' => 'names' ] ),
        'size'        => wp_get_post_terms( $company_id, 'company_size', [ 'fields' => 'names' ] ),
        'avg_rating'  => $rating['average'],
        'review_count'=> $rating['count'],
        'open_jobs'   => (int) $scalar( '_open_jobs_count' ),
        'is_verified' => (bool) $scalar( '_is_verified' ),
    ];
}

/**
 * Get candidate profile data for a user.
 */
function civijobs_get_candidate_data( int $user_id ): array {
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return [];
    }

    $meta   = get_user_meta( $user_id );
    $scalar = static fn( $key, $default = '' ) => $meta[ $key ][0] ?? $default;

    return [
        'id'            => $user_id,
        'name'          => $user->display_name,
        'email'         => $user->user_email,
        'headline'      => $scalar( 'civi_headline' ),
        'bio'           => $scalar( 'civi_bio' ),
        'location'      => $scalar( 'civi_location' ),
        'phone'         => $scalar( 'civi_phone' ),
        'website'       => $scalar( 'civi_website' ),
        'linkedin'      => $scalar( 'civi_linkedin' ),
        'github'        => $scalar( 'civi_github' ),
        'avatar'        => $scalar( 'civi_avatar' ),
        'cv_file'       => $scalar( 'civi_cv_file' ),
        'experience'    => maybe_unserialize( $scalar( 'civi_experience', 'a:0:{}' ) ),
        'education'     => maybe_unserialize( $scalar( 'civi_education', 'a:0:{}' ) ),
        'skills'        => maybe_unserialize( $scalar( 'civi_skills', 'a:0:{}' ) ),
        'languages'     => maybe_unserialize( $scalar( 'civi_languages', 'a:0:{}' ) ),
        'resume_id'     => (int) $scalar( 'civi_resume_id' ),
        'visibility'    => $scalar( 'civi_visibility', 'public' ),
        'available'     => (bool) $scalar( 'civi_available_for_hire', true ),
        'hourly_rate'   => (float) $scalar( 'civi_hourly_rate' ),
        'min_salary'    => (float) $scalar( 'civi_min_salary' ),
        'job_type_pref' => maybe_unserialize( $scalar( 'civi_job_type_pref', 'a:0:{}' ) ),
    ];
}

/**
 * Format salary range for display.
 */
function civijobs_format_salary( float $min, float $max, string $type = 'annual' ): string {
    $period = match ( $type ) {
        'hourly'  => __( '/hr', 'civijobs' ),
        'monthly' => __( '/mo', 'civijobs' ),
        'daily'   => __( '/day', 'civijobs' ),
        default   => __( '/yr', 'civijobs' ),
    };

    $fmt = static fn( float $n ): string => '$' . ( $n >= 1000 ? number_format( $n / 1000, 0 ) . 'K' : number_format( $n, 0 ) );

    if ( $min > 0 && $max > 0 ) {
        return $fmt( $min ) . ' – ' . $fmt( $max ) . $period;
    }
    if ( $min > 0 ) {
        return __( 'From ', 'civijobs' ) . $fmt( $min ) . $period;
    }
    if ( $max > 0 ) {
        return __( 'Up to ', 'civijobs' ) . $fmt( $max ) . $period;
    }
    return __( 'Negotiable', 'civijobs' );
}

/**
 * Return human-readable time difference ("3 hours ago").
 */
function civijobs_time_ago( string $datetime ): string {
    $timestamp = strtotime( $datetime );
    if ( ! $timestamp ) {
        return '';
    }
    return human_time_diff( $timestamp, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'civijobs' );
}

/**
 * Return an HTML badge for a job or application status.
 */
function civijobs_get_status_badge( string $status ): string {
    $map = [
        'active'      => [ 'success', __( 'Active', 'civijobs' ) ],
        'expired'     => [ 'danger',  __( 'Expired', 'civijobs' ) ],
        'pending'     => [ 'warning', __( 'Pending', 'civijobs' ) ],
        'reviewing'   => [ 'primary', __( 'Reviewing', 'civijobs' ) ],
        'shortlisted' => [ 'info',    __( 'Shortlisted', 'civijobs' ) ],
        'rejected'    => [ 'danger',  __( 'Rejected', 'civijobs' ) ],
        'hired'       => [ 'success', __( 'Hired', 'civijobs' ) ],
        'draft'       => [ 'gray',    __( 'Draft', 'civijobs' ) ],
        'publish'     => [ 'success', __( 'Published', 'civijobs' ) ],
        'closed'      => [ 'gray',    __( 'Closed', 'civijobs' ) ],
    ];

    [ $type, $label ] = $map[ $status ] ?? [ 'gray', ucfirst( $status ) ];
    return '<span class="badge badge-' . esc_attr( $type ) . '">' . esc_html( $label ) . '</span>';
}

/**
 * Count total applications for a job.
 */
function civijobs_get_application_count( int $job_id ): int {
    global $wpdb;
    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications WHERE job_id = %d",
            $job_id
        )
    );
}

/**
 * Check if a candidate has applied for a job.
 */
function civijobs_has_applied( int $job_id, int $user_id = 0 ): bool {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return false;
    }
    global $wpdb;
    return (bool) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}civi_applications WHERE job_id = %d AND candidate_id = %d",
            $job_id,
            $user_id
        )
    );
}

/**
 * Check if a user has saved a job.
 */
function civijobs_has_saved( int $job_id, int $user_id = 0 ): bool {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return false;
    }
    global $wpdb;
    return (bool) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}civi_saved_jobs WHERE job_id = %d AND user_id = %d",
            $job_id,
            $user_id
        )
    );
}

/**
 * Count unread messages for a user.
 */
function civijobs_get_unread_messages( int $user_id = 0 ): int {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return 0;
    }
    global $wpdb;
    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}civi_messages
             WHERE receiver_id = %d AND is_read = 0 AND receiver_deleted = 0",
            $user_id
        )
    );
}

/**
 * Count unread notifications for a user.
 */
function civijobs_get_unread_notifications( int $user_id = 0 ): int {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    if ( ! $user_id ) {
        return 0;
    }
    global $wpdb;
    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}civi_notifications
             WHERE user_id = %d AND is_read = 0",
            $user_id
        )
    );
}

/**
 * Get the company post owned by an employer.
 */
function civijobs_get_employer_company( int $employer_id ): ?WP_Post {
    $posts = get_posts( [
        'post_type'      => 'civi_company',
        'posts_per_page' => 1,
        'meta_key'       => '_owner_id',
        'meta_value'     => $employer_id,
        'post_status'    => [ 'publish', 'draft', 'pending' ],
    ] );
    return $posts[0] ?? null;
}

/**
 * Get the active package order for a user.
 */
function civijobs_get_user_package( int $user_id ): ?object {
    global $wpdb;
    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT po.*, p.post_title as package_name
             FROM {$wpdb->prefix}civi_package_orders po
             LEFT JOIN {$wpdb->posts} p ON p.ID = po.package_id
             WHERE po.user_id = %d
               AND po.status = 'active'
               AND (po.expires_at IS NULL OR po.expires_at > NOW())
             ORDER BY po.created_at DESC
             LIMIT 1",
            $user_id
        )
    );
}

/**
 * Check whether an employer can still post a job (has package credit).
 */
function civijobs_can_post_job( int $user_id ): bool {
    if ( ! civijobs_is_employer( $user_id ) ) {
        return false;
    }
    $pkg = civijobs_get_user_package( $user_id );
    if ( ! $pkg ) {
        return false;
    }
    // -1 = unlimited
    return $pkg->jobs_remaining === -1 || $pkg->jobs_remaining > 0;
}

/**
 * Get avatar URL for a user, falling back to Gravatar.
 */
function civijobs_get_avatar_url( int $user_id, int $size = 80 ): string {
    $custom = get_user_meta( $user_id, 'civi_avatar', true );
    if ( $custom ) {
        return esc_url( $custom );
    }
    return esc_url( get_avatar_url( $user_id, [ 'size' => $size ] ) );
}

/**
 * Return dashboard URL for a given role.
 */
function civijobs_get_dashboard_url( string $role = '' ): string {
    if ( ! $role ) {
        $role = civijobs_get_user_role( get_current_user_id() );
    }
    $slug = match ( $role ) {
        'employer'  => civijobs_get_setting( 'employer_dashboard_slug', 'employer-dashboard' ),
        'candidate' => civijobs_get_setting( 'candidate_dashboard_slug', 'candidate-dashboard' ),
        'admin'     => admin_url(),
        default     => home_url( '/' ),
    };
    return in_array( $role, [ 'employer', 'candidate' ], true ) ? home_url( '/' . $slug ) : $slug;
}

/**
 * Safely encode data as JSON for inline HTML output.
 */
function civijobs_json_encode( $data ): string {
    return wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
}

/**
 * Increment job view count.
 */
function civijobs_increment_job_views( int $job_id ): void {
    $views = (int) get_post_meta( $job_id, '_job_views', true );
    update_post_meta( $job_id, '_job_views', $views + 1 );
}

/**
 * Get company rating data.
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
 * Render star rating HTML.
 */
function civijobs_render_stars( float $rating, bool $interactive = false ): string {
    $out = '<span class="stars">';
    for ( $i = 1; $i <= 5; $i++ ) {
        $class = $i <= $rating ? 'star star-filled' : 'star star-empty';
        if ( $interactive ) {
            $out .= '<span class="' . esc_attr( $class ) . '" data-value="' . $i . '">&#9733;</span>';
        } else {
            $out .= '<span class="' . esc_attr( $class ) . '">&#9733;</span>';
        }
    }
    $out .= '</span>';
    return $out;
}

/**
 * Get setting value with optional default.
 */
function civijobs_get_setting( string $key, $default = '' ) {
    $settings = get_option( 'civijobs_settings', [] );
    return $settings[ $key ] ?? $default;
}

/**
 * Get all job listings for map markers.
 *
 * @return array Array of marker data (id, title, lat, lng, company, salary, url, logo)
 */
function civijobs_get_map_markers( array $query_args = [] ): array {
    $args = array_merge( [
        'post_type'      => 'civi_job',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
    ], $query_args );

    $jobs    = get_posts( $args );
    $markers = [];

    foreach ( $jobs as $job ) {
        $lat = (float) get_post_meta( $job->ID, '_latitude', true );
        $lng = (float) get_post_meta( $job->ID, '_longitude', true );
        if ( ! $lat || ! $lng ) {
            continue;
        }
        $company_id = (int) get_post_meta( $job->ID, '_company_id', true );
        $markers[]  = [
            'id'      => $job->ID,
            'title'   => get_the_title( $job->ID ),
            'lat'     => $lat,
            'lng'     => $lng,
            'company' => $company_id ? get_the_title( $company_id ) : '',
            'logo'    => $company_id ? get_the_post_thumbnail_url( $company_id, 'company-logo' ) : '',
            'salary'  => civijobs_format_salary(
                (float) get_post_meta( $job->ID, '_salary_min', true ),
                (float) get_post_meta( $job->ID, '_salary_max', true ),
                get_post_meta( $job->ID, '_salary_type', true ) ?: 'annual'
            ),
            'type'    => implode( ', ', wp_get_post_terms( $job->ID, 'job_type', [ 'fields' => 'names' ] ) ),
            'url'     => get_permalink( $job->ID ),
        ];
    }

    return $markers;
}
