<?php
/**
 * Archive: Companies (fallback)
 *
 * @package CiviJobs
 */

$companies_page = get_posts( [
    'post_type'      => 'page',
    'posts_per_page' => 1,
    'meta_key'       => '_wp_page_template',
    'meta_value'     => 'page-templates/page-companies.php',
] );

if ( $companies_page ) {
    wp_safe_redirect( get_permalink( $companies_page[0]->ID ) . ( $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
    exit;
}

get_header();
?>
<div class="container" style="padding:40px 20px">
    <?php if ( have_posts() ) : ?>
        <div class="companies-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/company/card-grid' ); ?>
            <?php endwhile; ?>
        </div>
        <?php get_template_part( 'template-parts/global/pagination' ); ?>
    <?php else : ?>
        <div class="empty-state">
            <div class="empty-state-icon">🏢</div>
            <div class="empty-state-title"><?php esc_html_e( 'No companies found', 'civijobs' ); ?></div>
        </div>
    <?php endif; ?>
</div>
<?php get_footer(); ?>
