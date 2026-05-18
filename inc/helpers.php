<?php
/**
 * Helper / Utility Functions
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

/**
 * Format salary range.
 */
function jobportal_format_salary( float $min, float $max, string $type = 'annual' ): string {
    if ( ! $min && ! $max ) return __( 'Negotiable', 'jobportal' );

    $type_labels = [
        'annual'  => '/yr',
        'monthly' => '/mo',
        'hourly'  => '/hr',
        'daily'   => '/day',
    ];
    $suffix = $type_labels[ $type ] ?? '/yr';
    $fmt    = fn( float $n ) => '$' . number_format( $n );

    if ( $min && $max ) return $fmt( $min ) . ' - ' . $fmt( $max ) . $suffix;
    if ( $min )         return $fmt( $min ) . '+' . $suffix;
    return 'Up to ' . $fmt( $max ) . $suffix;
}

/**
 * Human-readable time ago.
 */
function jobportal_time_ago( string $datetime ): string {
    $time = time() - strtotime( $datetime );
    if ( $time < 60 )     return __( 'Just now', 'jobportal' );
    if ( $time < 3600 )   return sprintf( _n( '%d minute ago', '%d minutes ago', (int)($time/60), 'jobportal' ), (int)($time/60) );
    if ( $time < 86400 )  return sprintf( _n( '%d hour ago', '%d hours ago', (int)($time/3600), 'jobportal' ), (int)($time/3600) );
    if ( $time < 604800 ) return sprintf( _n( '%d day ago', '%d days ago', (int)($time/86400), 'jobportal' ), (int)($time/86400) );
    return date_i18n( get_option( 'date_format' ), strtotime( $datetime ) );
}

/**
 * Status badge HTML.
 */
function jobportal_get_status_badge( string $status ): string {
    $map = [
        'publish'     => [ 'Active',         'badge-green' ],
        'pending'     => [ 'Pending',         'badge-orange' ],
        'draft'       => [ 'Draft',           'badge-gray' ],
        'reviewing'   => [ 'Reviewing',       'badge-blue' ],
        'shortlisted' => [ 'Shortlisted',     'badge-purple' ],
        'hired'       => [ 'Hired',           'badge-green' ],
        'rejected'    => [ 'Rejected',        'badge-red' ],
        'withdrawn'   => [ 'Withdrawn',       'badge-gray' ],
        'cancelled'   => [ 'Cancelled',       'badge-red' ],
        'confirmed'   => [ 'Confirmed',       'badge-green' ],
        'cj_active'   => [ 'Active',          'badge-green' ],
        'cj_expired'  => [ 'Expired',         'badge-gray' ],
        'cj_filled'   => [ 'Filled',          'badge-blue' ],
    ];
    [ $label, $class ] = $map[ $status ] ?? [ ucfirst( $status ), 'badge-gray' ];
    return '<span class="badge ' . esc_attr( $class ) . '">' . esc_html__( $label, 'jobportal' ) . '</span>';
}

/**
 * Application count for a job.
 */
function jobportal_get_application_count( int $job_id ): int {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications WHERE job_id = %d",
        $job_id
    ) );
}

/**
 * Check if user has applied.
 */
function jobportal_has_applied( int $job_id, int $user_id ): bool {
    global $wpdb;
    return (bool) $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}civi_applications WHERE job_id = %d AND candidate_id = %d",
        $job_id, $user_id
    ) );
}

/**
 * Check if user has saved a job.
 */
function jobportal_has_saved( int $job_id, int $user_id ): bool {
    global $wpdb;
    return (bool) $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}civi_saved_jobs WHERE job_id = %d AND user_id = %d",
        $job_id, $user_id
    ) );
}

/**
 * Unread messages count.
 */
function jobportal_get_unread_messages( int $user_id ): int {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}jp_messages WHERE receiver_id = %d AND is_read = 0",
        $user_id
    ) );
}

/**
 * Unread notifications count.
 */
function jobportal_get_unread_notifications( int $user_id ): int {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}civi_notifications WHERE user_id = %d AND is_read = 0",
        $user_id
    ) );
}

/**
 * Get employer's company post.
 */
function jobportal_get_employer_company( int $user_id ): ?WP_Post {
    $posts = get_posts( [
        'post_type'   => 'jp_company',
        'author'      => $user_id,
        'numberposts' => 1,
        'post_status' => 'any',
    ] );
    return $posts[0] ?? null;
}

/**
 * Get active package for user.
 */
function jobportal_get_user_package( int $user_id ): ?object {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}jp_package_orders WHERE user_id = %d AND status = 'active' AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY activated_at DESC LIMIT 1",
        $user_id
    ) );
}

/**
 * Check if user can post a job.
 */
function jobportal_can_post_job( int $user_id ): bool {
    if ( current_user_can( 'manage_options' ) ) return true;
    $package = jobportal_get_user_package( $user_id );
    if ( ! $package ) return false;
    return $package->jobs_remaining === -1 || $package->jobs_remaining > 0;
}

/**
 * Get dashboard URL for user.
 */
function jobportal_get_dashboard_url( int $user_id ): string {
    $role = jobportal_get_user_role( $user_id );
    if ( $role === 'jp_employer' ) {
        $page = get_page_by_path( 'employer-dashboard' )
             ?? get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/page-dashboard-employer.php', 'number' => 1 ] )[0]
             ?? null;
    } else {
        $page = get_page_by_path( 'candidate-dashboard' )
             ?? get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/page-dashboard-candidate.php', 'number' => 1 ] )[0]
             ?? null;
    }
    return $page ? get_permalink( $page->ID ) : admin_url();
}

/**
 * Increment job view count.
 */
function jobportal_increment_job_views( int $job_id ): void {
    $views = (int) get_post_meta( $job_id, '_job_views', true );
    update_post_meta( $job_id, '_job_views', $views + 1 );
}

/**
 * Get company rating.
 */
function jobportal_get_company_rating( int $company_id ): array {
    global $wpdb;
    $result = $wpdb->get_row( $wpdb->prepare(
        "SELECT AVG(rating) as average, COUNT(*) as count FROM {$wpdb->prefix}jp_company_reviews WHERE company_id = %d AND status = 'approved'",
        $company_id
    ) );
    return [
        'average' => $result ? round( (float) $result->average, 1 ) : 0,
        'count'   => $result ? (int) $result->count : 0,
    ];
}

/**
 * Render star rating.
 */
function jobportal_render_stars( float $rating ): string {
    $html = '';
    for ( $i = 1; $i <= 5; $i++ ) {
        $html .= $i <= $rating ? '<span class="star filled">&#9733;</span>' : '<span class="star">&#9734;</span>';
    }
    return $html;
}

/**
 * Get theme setting.
 */
function jobportal_get_setting( string $key, mixed $default = '' ): mixed {
    $settings = get_option( 'jobportal_settings', [] );
    return $settings[ $key ] ?? $default;
}

/**
 * Get candidate profile data.
 */
function jobportal_get_candidate_data( int $user_id ): array {
    return [
        'headline'    => get_user_meta( $user_id, 'civi_headline',    true ),
        'location'    => get_user_meta( $user_id, 'civi_location',    true ),
        'phone'       => get_user_meta( $user_id, 'civi_phone',       true ),
        'bio'         => get_user_meta( $user_id, 'civi_bio',         true ),
        'website'     => get_user_meta( $user_id, 'civi_website',     true ),
        'linkedin'    => get_user_meta( $user_id, 'civi_linkedin',    true ),
        'github'      => get_user_meta( $user_id, 'civi_github',      true ),
        'hourly_rate' => get_user_meta( $user_id, 'civi_hourly_rate', true ),
        'skills'      => get_user_meta( $user_id, 'civi_skills',      true ) ?: [],
        'cv_file'     => get_user_meta( $user_id, 'civi_cv_file',     true ),
        'available'   => (bool) get_user_meta( $user_id, 'civi_available_for_hire', true ),
        'visibility'  => get_user_meta( $user_id, 'civi_visibility',  true ) ?: 'public',
    ];
}

/**
 * Get company data.
 */
function jobportal_get_company_data( int $company_id ): array {
    return [
        'location'  => get_post_meta( $company_id, '_company_location', true ),
        'website'   => get_post_meta( $company_id, '_company_website',  true ),
        'email'     => get_post_meta( $company_id, '_company_email',    true ),
        'phone'     => get_post_meta( $company_id, '_company_phone',    true ),
        'linkedin'  => get_post_meta( $company_id, '_company_linkedin', true ),
        'twitter'   => get_post_meta( $company_id, '_company_twitter',  true ),
        'founded'   => get_post_meta( $company_id, '_company_founded',  true ),
        'tagline'   => get_post_meta( $company_id, '_company_tagline',  true ),
        'lat'       => get_post_meta( $company_id, '_lat',              true ),
        'lng'       => get_post_meta( $company_id, '_lng',              true ),
    ];
}

/**
 * Get map markers JSON.
 */
function jobportal_get_map_markers( array $args = [] ): array {
    $query = new WP_Query( array_merge( [
        'post_type'      => 'jp_job',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
    ], $args ) );

    $markers = [];
    foreach ( $query->posts as $post ) {
        $company_id = (int) get_post_meta( $post->ID, '_company_id', true );
        $lat = (float) get_post_meta( $post->ID, '_lat', true );
        $lng = (float) get_post_meta( $post->ID, '_lng', true );
        if ( ! $lat || ! $lng ) {
            if ( $company_id ) {
                $lat = (float) get_post_meta( $company_id, '_lat', true );
                $lng = (float) get_post_meta( $company_id, '_lng', true );
            }
        }
        if ( ! $lat || ! $lng ) continue;
        $markers[] = [
            'id'       => $post->ID,
            'title'    => get_the_title( $post->ID ),
            'company'  => $company_id ? get_the_title( $company_id ) : '',
            'url'      => get_permalink( $post->ID ),
            'lat'      => $lat,
            'lng'      => $lng,
            'salary'   => jobportal_format_salary(
                (float) get_post_meta( $post->ID, '_salary_min', true ),
                (float) get_post_meta( $post->ID, '_salary_max', true )
            ),
            'location' => get_post_meta( $post->ID, '_job_location', true ),
        ];
    }
    return $markers;
}

/**
 * Get message threads for a user.
 */
function jobportal_get_message_threads( int $user_id ): array {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT m.*, MAX(m.created_at) as last_message
         FROM {$wpdb->prefix}jp_messages m
         WHERE m.sender_id = %d OR m.receiver_id = %d
         GROUP BY m.thread_id
         ORDER BY last_message DESC",
        $user_id, $user_id
    ) ) ?: [];
}
