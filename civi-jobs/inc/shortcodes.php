<?php
/**
 * Theme Shortcodes
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// [civijobs_jobs] - Job listing grid
add_shortcode( 'civijobs_jobs', function( $atts ) {
    $atts = shortcode_atts( [
        'count'    => 6,
        'category' => '',
        'type'     => '',
        'featured' => '',
        'columns'  => 2,
        'view'     => 'grid',
    ], $atts, 'civijobs_jobs' );

    $args = [
        'post_type'      => 'civi_job',
        'post_status'    => 'publish',
        'posts_per_page' => (int) $atts['count'],
    ];

    $tax_query = [];
    if ( $atts['category'] ) {
        $tax_query[] = [ 'taxonomy' => 'job_category', 'field' => 'slug', 'terms' => explode( ',', $atts['category'] ) ];
    }
    if ( $atts['type'] ) {
        $tax_query[] = [ 'taxonomy' => 'job_type', 'field' => 'slug', 'terms' => explode( ',', $atts['type'] ) ];
    }
    if ( $tax_query ) $args['tax_query'] = $tax_query;
    if ( $atts['featured'] ) {
        $args['meta_query'] = [ [ 'key' => '_is_featured', 'value' => '1' ] ];
    }

    $query = new WP_Query( $args );

    ob_start();
    if ( $query->have_posts() ) :
        $grid_class = 'jobs-grid jobs-grid--cols-' . (int) $atts['columns'];
        echo '<div class="' . esc_attr( $grid_class ) . '">';
        while ( $query->have_posts() ) : $query->the_post();
            get_template_part( 'template-parts/job/card-' . ( $atts['view'] === 'list' ? 'list' : 'grid' ) );
        endwhile;
        wp_reset_postdata();
        echo '</div>';
    endif;
    return ob_get_clean();
} );

// [civijobs_companies] - Company grid
add_shortcode( 'civijobs_companies', function( $atts ) {
    $atts = shortcode_atts( [
        'count'    => 6,
        'industry' => '',
        'featured' => '',
    ], $atts, 'civijobs_companies' );

    $args = [
        'post_type'      => 'civi_company',
        'post_status'    => 'publish',
        'posts_per_page' => (int) $atts['count'],
    ];

    if ( $atts['industry'] ) {
        $args['tax_query'] = [ [ 'taxonomy' => 'company_industry', 'field' => 'slug', 'terms' => explode( ',', $atts['industry'] ) ] ];
    }
    if ( $atts['featured'] ) {
        $args['meta_query'] = [ [ 'key' => '_is_featured', 'value' => '1' ] ];
    }

    $query = new WP_Query( $args );

    ob_start();
    if ( $query->have_posts() ) :
        echo '<div class="companies-grid">';
        while ( $query->have_posts() ) : $query->the_post();
            get_template_part( 'template-parts/company/card-grid' );
        endwhile;
        wp_reset_postdata();
        echo '</div>';
    endif;
    return ob_get_clean();
} );

// [civijobs_search] - Job search bar
add_shortcode( 'civijobs_search', function( $atts ) {
    ob_start();
    get_template_part( 'template-parts/job/search-bar' );
    return ob_get_clean();
} );

// [civijobs_job_categories] - Category grid with icons
add_shortcode( 'civijobs_job_categories', function( $atts ) {
    $atts = shortcode_atts( [
        'count'   => 8,
        'columns' => 4,
    ], $atts, 'civijobs_job_categories' );

    $categories = get_terms( [
        'taxonomy'   => 'job_category',
        'hide_empty' => true,
        'number'     => (int) $atts['count'],
    ] );

    if ( is_wp_error( $categories ) || ! $categories ) return '';

    $jobs_page = get_posts( [ 'post_type' => 'page', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/page-jobs.php' ] );
    $base_url  = $jobs_page ? get_permalink( $jobs_page[0]->ID ) : home_url( '/jobs' );

    ob_start();
    echo '<div class="categories-grid categories-grid--cols-' . (int) $atts['columns'] . '">';
    foreach ( $categories as $cat ) :
        $icon = get_term_meta( $cat->term_id, 'icon', true ) ?: '💼';
        ?>
        <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, $base_url ) ); ?>" class="category-card">
            <div class="category-card-icon"><?php echo $icon; // phpcs:ignore ?></div>
            <div class="category-card-name"><?php echo esc_html( $cat->name ); ?></div>
            <div class="category-card-count"><?php printf( _n( '%d job', '%d jobs', $cat->count, 'civijobs' ), $cat->count ); // phpcs:ignore ?></div>
        </a>
        <?php
    endforeach;
    echo '</div>';
    return ob_get_clean();
} );

// [civijobs_stats] - Site statistics
add_shortcode( 'civijobs_stats', function( $atts ) {
    $jobs      = wp_count_posts( 'civi_job' )->publish;
    $companies = wp_count_posts( 'civi_company' )->publish;
    $candidates = count_users()['avail_roles']['civi_candidate'] ?? 0;

    global $wpdb;
    $applications = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications" );

    ob_start();
    ?>
    <div class="stats-row">
        <?php foreach ( [
            [ '💼', $jobs,         __( 'Jobs Posted', 'civijobs' ) ],
            [ '🏢', $companies,    __( 'Companies', 'civijobs' ) ],
            [ '👤', $candidates,   __( 'Candidates', 'civijobs' ) ],
            [ '📋', $applications, __( 'Applications', 'civijobs' ) ],
        ] as [ $icon, $val, $label ] ) : ?>
            <div class="stat-item">
                <div class="stat-icon"><?php echo $icon; // phpcs:ignore ?></div>
                <div class="stat-number"><?php echo esc_html( number_format( $val ) ); ?>+</div>
                <div class="stat-label"><?php echo esc_html( $label ); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <style>
    .stats-row { display: flex; gap: 32px; flex-wrap: wrap; justify-content: center; }
    .stat-item { text-align: center; }
    .stat-icon { font-size: 2rem; margin-bottom: 8px; }
    .stat-number { font-size: 2.25rem; font-weight: 800; color: var(--color-primary); }
    .stat-label { font-size: 0.875rem; color: var(--color-gray-600); }
    </style>
    <?php
    return ob_get_clean();
} );

// [civijobs_login_form] - Frontend login form
add_shortcode( 'civijobs_login_form', function( $atts ) {
    if ( is_user_logged_in() ) {
        return '<p>' . esc_html__( 'You are already logged in.', 'civijobs' ) . '</p>';
    }
    ob_start();
    include get_template_directory() . '/page-templates/page-login.php';
    return ob_get_clean();
} );

// [civijobs_register_form] - Frontend registration form
add_shortcode( 'civijobs_register_form', function( $atts ) {
    if ( is_user_logged_in() ) {
        return '<p>' . esc_html__( 'You are already registered and logged in.', 'civijobs' ) . '</p>';
    }
    ob_start();
    include get_template_directory() . '/page-templates/page-register.php';
    return ob_get_clean();
} );
