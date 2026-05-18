<?php
/**
 * Template Name: Jobs Listing
 *
 * @package CiviJobs
 */

get_header();

// Build query from URL params
$per_page  = (int) civijobs_get_setting( 'jobs_per_page', 10 );
$paged     = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$keyword   = sanitize_text_field( $_GET['keyword'] ?? '' );
$location  = sanitize_text_field( $_GET['location'] ?? '' );
$sort      = sanitize_key( $_GET['sort'] ?? 'newest' );
$view_mode = sanitize_key( $_GET['view'] ?? get_user_meta( get_current_user_id(), 'civi_jobs_view', true ) ?: 'grid' );

$args = [
    'post_type'      => 'civi_job',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'orderby'        => $sort === 'oldest' ? 'date' : 'date',
    'order'          => $sort === 'oldest' ? 'ASC' : 'DESC',
];

if ( $keyword ) $args['s'] = $keyword;

$tax_query  = [];
$meta_query = [];

foreach ( [ 'job_type', 'job_category', 'job_experience', 'job_salary' ] as $tax ) {
    $terms = array_map( 'sanitize_key', (array) ( $_GET[ $tax ] ?? [] ) );
    if ( $terms ) {
        $tax_query[] = [ 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $terms ];
    }
}

if ( $tax_query ) $args['tax_query'] = array_merge( [ 'relation' => 'AND' ], $tax_query );

if ( $location ) {
    $meta_query[] = [ 'key' => '_job_location', 'value' => $location, 'compare' => 'LIKE' ];
}
if ( ! empty( $_GET['remote_only'] ) ) {
    $meta_query[] = [ 'key' => '_remote_ok', 'value' => '1', 'compare' => '=' ];
}
if ( $meta_query ) $args['meta_query'] = $meta_query;

if ( $sort === 'featured' ) {
    $args['meta_key'] = '_is_featured';
    $args['orderby']  = [ 'meta_value' => 'DESC', 'date' => 'DESC' ];
}

$query = new WP_Query( $args );
?>
<main class="site-main" id="main">

    <!-- Search Bar Header -->
    <div style="background:linear-gradient(135deg,var(--color-primary) 0%,#0ea5e9 100%);padding:40px 0">
        <div class="container">
            <h1 style="color:#fff;font-size:1.875rem;font-weight:800;margin-bottom:20px;text-align:center"><?php esc_html_e( 'Browse Jobs', 'civijobs' ); ?></h1>
            <?php get_template_part( 'template-parts/job/search-bar' ); ?>
        </div>
    </div>

    <div class="container" style="padding-top:40px;padding-bottom:64px">
        <div class="jobs-layout" style="display:grid;grid-template-columns:280px 1fr;gap:32px;align-items:start">

            <!-- Filters -->
            <?php get_template_part( 'template-parts/job/filters' ); ?>

            <!-- Results -->
            <div class="jobs-results">

                <!-- Toolbar -->
                <div class="jobs-toolbar" style="display:flex;align-items:center;gap:16px;margin-bottom:20px;background:#fff;padding:14px 20px;border-radius:var(--radius-lg);border:1px solid var(--color-gray-200)">
                    <div class="jobs-count" style="flex:1;font-size:0.9rem;color:var(--color-gray-700)">
                        <?php printf( _n( '<strong>%d</strong> job found', '<strong>%d</strong> jobs found', $query->found_posts, 'civijobs' ), $query->found_posts ); // phpcs:ignore ?>
                    </div>

                    <!-- Sort -->
                    <select id="jobs-sort" class="form-control" style="width:auto;min-width:150px;font-size:0.875rem">
                        <?php foreach ( [
                            'newest'   => __( 'Newest First', 'civijobs' ),
                            'oldest'   => __( 'Oldest First', 'civijobs' ),
                            'featured' => __( 'Featured First', 'civijobs' ),
                        ] as $val => $label ) : ?>
                            <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $sort, $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- View Toggle -->
                    <div class="view-toggle" style="display:flex;gap:4px">
                        <button class="view-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>" data-view="grid" title="<?php esc_attr_e( 'Grid view', 'civijobs' ); ?>" aria-label="Grid view">⊞</button>
                        <button class="view-btn <?php echo $view_mode === 'list' ? 'active' : ''; ?>" data-view="list" title="<?php esc_attr_e( 'List view', 'civijobs' ); ?>" aria-label="List view">☰</button>
                    </div>

                    <!-- Map View Button -->
                    <a href="<?php echo esc_url( home_url( '/jobs-map' ) ); ?>" class="btn btn-sm btn-outline" style="white-space:nowrap">
                        🗺 <?php esc_html_e( 'Map View', 'civijobs' ); ?>
                    </a>

                    <!-- Mobile filter toggle -->
                    <button class="filter-toggle-btn btn btn-sm btn-outline" id="toggle-filters" aria-controls="filters-sidebar" style="display:none">
                        ⚙️ <?php esc_html_e( 'Filters', 'civijobs' ); ?>
                    </button>
                </div>

                <!-- Active filters chips -->
                <?php
                $active_filters = [];
                foreach ( [ 'job_type', 'job_category', 'job_experience', 'job_salary' ] as $tax ) {
                    $terms = array_map( 'sanitize_key', (array) ( $_GET[ $tax ] ?? [] ) );
                    foreach ( $terms as $t ) {
                        $term = get_term_by( 'slug', $t, $tax );
                        if ( $term ) {
                            $active_filters[] = [ 'label' => $term->name, 'key' => $tax, 'value' => $t ];
                        }
                    }
                }
                if ( $active_filters ) : ?>
                    <div class="active-filters" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
                        <?php foreach ( $active_filters as $f ) : ?>
                            <span class="active-filter-chip">
                                <?php echo esc_html( $f['label'] ); ?>
                                <a href="<?php echo esc_url( remove_query_arg( $f['key'] ) ); ?>" style="margin-left:5px;color:var(--color-gray-400)">&times;</a>
                            </span>
                        <?php endforeach; ?>
                        <a href="<?php echo esc_url( remove_query_arg( [ 'job_type', 'job_category', 'job_experience', 'job_salary', 'remote_only' ] ) ); ?>" style="font-size:0.78rem;color:var(--color-danger);align-self:center"><?php esc_html_e( 'Clear all', 'civijobs' ); ?></a>
                    </div>
                <?php endif; ?>

                <!-- Job Cards -->
                <div id="jobs-container" class="jobs-<?php echo esc_attr( $view_mode ); ?>" style="display:<?php echo $view_mode === 'grid' ? 'grid' : 'flex'; ?>;<?php echo $view_mode === 'grid' ? 'grid-template-columns:repeat(auto-fill,minmax(300px,1fr));' : 'flex-direction:column;'; ?>gap:16px">
                    <?php if ( $query->have_posts() ) :
                        while ( $query->have_posts() ) :
                            $query->the_post();
                            if ( $view_mode === 'list' ) {
                                get_template_part( 'template-parts/job/card-list', null, [ 'job' => $GLOBALS['post'] ] );
                            } else {
                                get_template_part( 'template-parts/job/card-grid', null, [ 'job' => $GLOBALS['post'] ] );
                            }
                        endwhile;
                        wp_reset_postdata();
                    else : ?>
                        <div class="empty-state" style="grid-column:1/-1;text-align:center;padding:80px 20px">
                            <div style="font-size:3rem;margin-bottom:16px">🔍</div>
                            <h3 style="color:var(--color-gray-600)"><?php esc_html_e( 'No jobs found', 'civijobs' ); ?></h3>
                            <p style="color:var(--color-gray-500)"><?php esc_html_e( 'Try adjusting your filters or search terms.', 'civijobs' ); ?></p>
                            <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-primary" style="margin-top:16px"><?php esc_html_e( 'View All Jobs', 'civijobs' ); ?></a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Load more / Pagination -->
                <?php if ( $query->max_num_pages > 1 ) : ?>
                    <div style="margin-top:32px;text-align:center">
                        <?php
                        echo paginate_links( [ // phpcs:ignore
                            'total'   => $query->max_num_pages,
                            'current' => $paged,
                            'format'  => '?paged=%#%',
                            'add_args'=> array_filter( [
                                'keyword'   => $keyword,
                                'location'  => $location,
                                'sort'      => $sort,
                            ] ),
                        ] );
                        ?>
                    </div>
                <?php endif; ?>

            </div><!-- .jobs-results -->
        </div>
    </div>

</main>

<?php get_footer();
