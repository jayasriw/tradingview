<?php
/**
 * Archive for jp_resume
 *
 * @package JobPortal
 */

$candidates_page = get_page_by_path( 'candidates' );
if ( $candidates_page ) {
    wp_redirect( get_permalink( $candidates_page->ID ) );
    exit;
}

get_header();
get_template_part( 'index' );
get_footer();
