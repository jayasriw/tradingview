<?php
/**
 * Theme Shortcodes
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

// [jobportal_jobs]
add_shortcode( 'jobportal_jobs', function( $atts ) {
    $atts = shortcode_atts( [ 'count' => 6, 'category' => '', 'type' => '', 'featured' => '' ], $atts );
    $args = [
        'post_type'      => 'jp_job',
        'post_status'    => 'publish',
        'posts_per_page' => (int) $atts['count'],
    ];
    if ( $atts['featured'] ) $args['meta_query'][] = [ 'key' => '_is_featured', 'value' => '1' ];
    if ( $atts['category'] ) $args['tax_query'][]  = [ 'taxonomy' => 'job_category', 'field' => 'slug', 'terms' => $atts['category'] ];
    if ( $atts['type'] )     $args['tax_query'][]  = [ 'taxonomy' => 'job_type',     'field' => 'slug', 'terms' => $atts['type'] ];

    $query = new WP_Query( $args );
    ob_start();
    if ( $query->have_posts() ) :
        echo '<div class="jobs-grid">';
        foreach ( $query->posts as $job ) :
            get_template_part( 'template-parts/job/card-grid', null, [ 'job' => $job ] );
        endforeach;
        echo '</div>';
    endif;
    return ob_get_clean();
} );

// [jobportal_companies]
add_shortcode( 'jobportal_companies', function( $atts ) {
    $atts  = shortcode_atts( [ 'count' => 6 ], $atts );
    $posts = get_posts( [ 'post_type' => 'jp_company', 'post_status' => 'publish', 'numberposts' => (int) $atts['count'] ] );
    ob_start();
    echo '<div class="companies-grid">';
    foreach ( $posts as $company ) :
        get_template_part( 'template-parts/company/card-grid', null, [ 'company' => $company ] );
    endforeach;
    echo '</div>';
    return ob_get_clean();
} );

// [jobportal_search]
add_shortcode( 'jobportal_search', function() {
    ob_start();
    get_template_part( 'template-parts/job/search-bar' );
    return ob_get_clean();
} );

// [jobportal_stats]
add_shortcode( 'jobportal_stats', function() {
    $jobs       = wp_count_posts( 'jp_job' )->publish;
    $companies  = wp_count_posts( 'jp_company' )->publish;
    $candidates = count_users()['avail_roles']['jp_candidate'] ?? 0;

    ob_start();
    ?>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-number"><?php echo esc_html( number_format( $jobs ) ); ?>+</div>
            <div class="stat-label"><?php esc_html_e( 'Active Jobs', 'jobportal' ); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo esc_html( number_format( $companies ) ); ?>+</div>
            <div class="stat-label"><?php esc_html_e( 'Companies', 'jobportal' ); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo esc_html( number_format( $candidates ) ); ?>+</div>
            <div class="stat-label"><?php esc_html_e( 'Candidates', 'jobportal' ); ?></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
} );

// [jobportal_login_form]
add_shortcode( 'jobportal_login_form', function() {
    if ( is_user_logged_in() ) return '';
    ob_start();
    wp_login_form( [ 'redirect' => get_permalink() ] );
    return ob_get_clean();
} );

// [jobportal_register_form]
add_shortcode( 'jobportal_register_form', function() {
    if ( is_user_logged_in() ) return '';
    ob_start();
    ?>
    <form method="post" class="register-form">
        <?php wp_nonce_field( 'jobportal_register', 'register_nonce' ); ?>
        <input type="text" name="user_login" placeholder="<?php esc_attr_e( 'Username', 'jobportal' ); ?>" required class="form-control">
        <input type="email" name="user_email" placeholder="<?php esc_attr_e( 'Email', 'jobportal' ); ?>" required class="form-control">
        <input type="password" name="user_pass" placeholder="<?php esc_attr_e( 'Password', 'jobportal' ); ?>" required class="form-control">
        <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Register', 'jobportal' ); ?></button>
    </form>
    <?php
    return ob_get_clean();
} );
