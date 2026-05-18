<?php
/**
 * Archive for jp_company
 *
 * @package JobPortal
 */

$companies_page = get_page_by_path( 'companies' );
if ( $companies_page && get_page_template_slug( $companies_page->ID ) === 'page-templates/page-companies.php' ) {
    wp_redirect( get_permalink( $companies_page->ID ) );
    exit;
}

get_header();
$template = locate_template( 'page-templates/page-companies.php' );
if ( $template ) {
    include $template;
} else {
    get_template_part( 'index' );
}
get_footer();
