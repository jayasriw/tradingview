<?php
/**
 * Archive: Resumes/Candidates (fallback)
 *
 * @package CiviJobs
 */

$candidates_page = get_posts( [
    'post_type'      => 'page',
    'posts_per_page' => 1,
    'meta_key'       => '_wp_page_template',
    'meta_value'     => 'page-templates/page-candidates.php',
] );

if ( $candidates_page ) {
    wp_safe_redirect( get_permalink( $candidates_page[0]->ID ) . ( $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
    exit;
}

get_header();
?>
<div class="container" style="padding:40px 20px">
    <h1 class="page-title"><?php esc_html_e( 'Candidates', 'civijobs' ); ?></h1>
    <?php if ( have_posts() ) : ?>
        <div class="candidates-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php the_title( '<h2>', '</h2>' ); ?>
                <?php the_excerpt(); ?>
            <?php endwhile; ?>
        </div>
        <?php get_template_part( 'template-parts/global/pagination' ); ?>
    <?php else : ?>
        <div class="empty-state">
            <div class="empty-state-icon">👤</div>
            <div class="empty-state-title"><?php esc_html_e( 'No resumes found', 'civijobs' ); ?></div>
        </div>
    <?php endif; ?>
</div>
<?php get_footer(); ?>
