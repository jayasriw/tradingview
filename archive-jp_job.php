<?php
/**
 * Archive for jp_job
 *
 * @package JobPortal
 */

$jobs_page = get_page_by_path( 'jobs' );
if ( $jobs_page && get_page_template_slug( $jobs_page->ID ) === 'page-templates/page-jobs.php' ) {
    wp_redirect( get_permalink( $jobs_page->ID ) );
    exit;
}

get_header();
$template = locate_template( 'page-templates/page-jobs.php' );
if ( $template ) {
    include $template;
} else {
    get_template_part( 'index' );
}
get_footer();
