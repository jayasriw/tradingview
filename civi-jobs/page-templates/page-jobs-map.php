<?php
/**
 * Template Name: Jobs Map
 *
 * @package CiviJobs
 */

get_header();

$search   = sanitize_text_field( $_GET['search'] ?? '' );
$category = sanitize_text_field( $_GET['category'] ?? '' );
$job_type = sanitize_text_field( $_GET['job_type'] ?? '' );

$tax_query = [];
if ( $category ) {
    $tax_query[] = [ 'taxonomy' => 'job_category', 'field' => 'slug', 'terms' => $category ];
}
if ( $job_type ) {
    $tax_query[] = [ 'taxonomy' => 'job_type', 'field' => 'slug', 'terms' => $job_type ];
}

$args = [
    'post_type'      => 'civi_job',
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'tax_query'      => $tax_query,
    'meta_query'     => [
        [ 'key' => '_job_lat', 'compare' => 'EXISTS' ],
        [ 'key' => '_job_lat', 'value' => '', 'compare' => '!=' ],
    ],
];

if ( $search ) {
    $args['s'] = $search;
}

$query      = new WP_Query( $args );
$categories = get_terms( [ 'taxonomy' => 'job_category', 'hide_empty' => true ] );
$job_types  = get_terms( [ 'taxonomy' => 'job_type', 'hide' => true ] );
?>

<!-- Map page layout: full-height split view -->
<div class="map-page-layout">
    <!-- Sidebar: filters + job list -->
    <div class="map-sidebar" id="map-sidebar">
        <div class="map-sidebar-header">
            <form method="GET" action="" id="map-filter-form">
                <div class="map-search-bar">
                    <input type="text" name="search" value="<?php echo esc_attr( $search ); ?>"
                           placeholder="<?php esc_attr_e( 'Job title or keyword…', 'civijobs' ); ?>"
                           class="form-control map-search-input">
                </div>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <select name="category" class="form-control" style="flex:1">
                        <option value=""><?php esc_html_e( 'All Categories', 'civijobs' ); ?></option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $category, $cat->slug ); ?>>
                                <?php echo esc_html( $cat->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="job_type" class="form-control" style="flex:1">
                        <option value=""><?php esc_html_e( 'All Types', 'civijobs' ); ?></option>
                        <?php foreach ( $job_types as $type ) : ?>
                            <option value="<?php echo esc_attr( $type->slug ); ?>" <?php selected( $job_type, $type->slug ); ?>>
                                <?php echo esc_html( $type->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px">
                    🔍 <?php esc_html_e( 'Search', 'civijobs' ); ?>
                </button>
            </form>
            <p style="font-size:0.8rem;color:var(--color-gray-500);margin-top:10px">
                <?php printf( _n( '%d job with location', '%d jobs with locations', $query->found_posts, 'civijobs' ), $query->found_posts ); // phpcs:ignore ?>
            </p>
        </div>

        <div class="map-job-list" id="map-job-list">
            <?php if ( $query->have_posts() ) :
                while ( $query->have_posts() ) : $query->the_post();
                    $job_id   = get_the_ID();
                    $lat      = get_post_meta( $job_id, '_job_lat', true );
                    $lng      = get_post_meta( $job_id, '_job_lng', true );
                    $company_id = (int) get_post_meta( $job_id, '_company_id', true );
                    $logo     = $company_id ? get_the_post_thumbnail_url( $company_id, 'company-logo' ) : '';
                    $location = get_post_meta( $job_id, '_job_location', true );
                    $salary   = civijobs_format_salary( get_post_meta( $job_id, '_salary_min', true ), get_post_meta( $job_id, '_salary_max', true ), get_post_meta( $job_id, '_salary_currency', true ) );
                    $types    = wp_get_post_terms( $job_id, 'job_type' );
            ?>
                <div class="map-job-card" data-lat="<?php echo esc_attr( $lat ); ?>" data-lng="<?php echo esc_attr( $lng ); ?>" data-job-id="<?php echo esc_attr( $job_id ); ?>">
                    <?php if ( $logo ) : ?>
                        <img src="<?php echo esc_url( $logo ); ?>" class="map-job-card-logo" alt="">
                    <?php else : ?>
                        <div class="map-job-card-logo map-job-card-logo--empty">🏢</div>
                    <?php endif; ?>
                    <div class="map-job-card-info">
                        <a href="<?php the_permalink(); ?>" class="map-job-card-title"><?php the_title(); ?></a>
                        <?php if ( $company_id ) : ?>
                            <div class="map-job-card-company"><?php echo esc_html( get_the_title( $company_id ) ); ?></div>
                        <?php endif; ?>
                        <div class="map-job-card-meta">
                            <?php if ( $location ) : ?>
                                <span>📍 <?php echo esc_html( $location ); ?></span>
                            <?php endif; ?>
                            <?php if ( $salary ) : ?>
                                <span>💰 <?php echo esc_html( $salary ); ?></span>
                            <?php endif; ?>
                            <?php if ( $types ) : ?>
                                <span class="badge badge-blue" style="font-size:0.68rem"><?php echo esc_html( $types[0]->name ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata();
            else : ?>
                <div class="empty-state" style="padding:40px 20px">
                    <div class="empty-state-icon">📍</div>
                    <div class="empty-state-title"><?php esc_html_e( 'No jobs with locations', 'civijobs' ); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Map -->
    <div class="map-main" id="map-container" style="flex:1;min-height:600px"></div>
</div>

<style>
.map-page-layout {
    display: flex;
    height: calc(100vh - var(--header-height, 80px));
    overflow: hidden;
}
.map-sidebar {
    width: 380px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--color-gray-200);
    background: #fff;
    overflow: hidden;
}
.map-sidebar-header {
    padding: 16px;
    border-bottom: 1px solid var(--color-gray-200);
    flex-shrink: 0;
}
.map-job-list {
    flex: 1;
    overflow-y: auto;
}
.map-job-card {
    display: flex;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--color-gray-100);
    cursor: pointer;
    transition: background .15s;
    align-items: flex-start;
}
.map-job-card:hover, .map-job-card.active { background: #f5f3ff; }
.map-job-card-logo {
    width: 44px; height: 44px; object-fit: contain;
    border-radius: 6px; border: 1px solid var(--color-gray-200);
    flex-shrink: 0; background: var(--color-gray-50);
}
.map-job-card-logo--empty {
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
}
.map-job-card-info { flex: 1; min-width: 0; }
.map-job-card-title {
    font-weight: 600; font-size: 0.875rem; color: var(--color-gray-900);
    display: block; margin-bottom: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.map-job-card-company { font-size: 0.78rem; color: var(--color-gray-500); margin-bottom: 4px; }
.map-job-card-meta { display: flex; gap: 8px; flex-wrap: wrap; font-size: 0.72rem; color: var(--color-gray-500); align-items: center; }

@media (max-width: 768px) {
    .map-page-layout { flex-direction: column; height: auto; }
    .map-sidebar { width: 100%; height: 300px; border-right: none; border-bottom: 1px solid var(--color-gray-200); }
    .map-main { height: 400px; }
}
</style>

<script>
// Pass map data to JS
window.CiviJobsMapData = <?php
    $map_markers = [];
    if ( $query->have_posts() ) {
        $query->rewind_posts();
        while ( $query->have_posts() ) {
            $query->the_post();
            $jid = get_the_ID();
            $lat = get_post_meta( $jid, '_job_lat', true );
            $lng = get_post_meta( $jid, '_job_lng', true );
            if ( $lat && $lng ) {
                $company_id = (int) get_post_meta( $jid, '_company_id', true );
                $map_markers[] = [
                    'id'       => $jid,
                    'title'    => get_the_title(),
                    'url'      => get_permalink(),
                    'lat'      => (float) $lat,
                    'lng'      => (float) $lng,
                    'company'  => $company_id ? get_the_title( $company_id ) : '',
                    'location' => get_post_meta( $jid, '_job_location', true ),
                ];
            }
        }
        wp_reset_postdata();
    }
    echo wp_json_encode( $map_markers ); // phpcs:ignore
?>;
</script>

<?php get_footer(); ?>
