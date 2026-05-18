<?php
/**
 * Archive: Jobs (fallback — redirects to page-jobs template)
 *
 * @package CiviJobs
 */

// Redirect to the page using the "Jobs Listing" template if it exists
$jobs_page = get_posts( [
    'post_type'      => 'page',
    'posts_per_page' => 1,
    'meta_key'       => '_wp_page_template',
    'meta_value'     => 'page-templates/page-jobs.php',
] );

if ( $jobs_page ) {
    wp_safe_redirect( get_permalink( $jobs_page[0]->ID ) . ( $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
    exit;
}

// Fallback: render like the jobs page
get_header();

$paged = max( 1, get_query_var( 'paged' ) );
?>
<div class="container" style="padding:40px 20px">
    <?php get_template_part( 'template-parts/job/search-bar' ); ?>
    <?php if ( have_posts() ) : ?>
        <div class="jobs-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/job/card-grid' ); ?>
            <?php endwhile; ?>
        </div>
        <?php get_template_part( 'template-parts/global/pagination' ); ?>
    <?php else : ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <div class="empty-state-title"><?php esc_html_e( 'No jobs found', 'civijobs' ); ?></div>
        </div>
    <?php endif; ?>
</div>
<?php get_footer(); ?>
