<?php
/**
 * Taxonomies Registration
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'jobportal_register_taxonomies' );

function jobportal_register_taxonomies(): void {

    $taxonomies = [
        'job_category'     => [ 'post_type' => 'jp_job',     'hierarchical' => true,  'label' => 'Job Categories', 'slug' => 'job-category' ],
        'job_type'         => [ 'post_type' => 'jp_job',     'hierarchical' => false, 'label' => 'Job Types',      'slug' => 'job-type' ],
        'job_location'     => [ 'post_type' => 'jp_job',     'hierarchical' => false, 'label' => 'Locations',      'slug' => 'job-location' ],
        'job_experience'   => [ 'post_type' => 'jp_job',     'hierarchical' => false, 'label' => 'Experience',     'slug' => 'job-experience' ],
        'job_salary'       => [ 'post_type' => 'jp_job',     'hierarchical' => false, 'label' => 'Salary Ranges',  'slug' => 'job-salary' ],
        'company_industry' => [ 'post_type' => 'jp_company', 'hierarchical' => false, 'label' => 'Industries',     'slug' => 'company-industry' ],
        'company_size'     => [ 'post_type' => 'jp_company', 'hierarchical' => false, 'label' => 'Company Sizes',  'slug' => 'company-size' ],
        'service_category' => [ 'post_type' => 'jp_service', 'hierarchical' => true,  'label' => 'Service Categories', 'slug' => 'service-category' ],
        'resume_skills'    => [ 'post_type' => 'jp_resume',  'hierarchical' => false, 'label' => 'Skills',         'slug' => 'resume-skill' ],
    ];

    foreach ( $taxonomies as $taxonomy => $args ) {
        register_taxonomy( $taxonomy, $args['post_type'], [
            'hierarchical'      => $args['hierarchical'],
            'label'             => __( $args['label'], 'jobportal' ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => [ 'slug' => $args['slug'] ],
        ] );
    }
}

// Seed default terms on theme activation
add_action( 'after_switch_theme', 'jobportal_seed_terms' );

function jobportal_seed_terms(): void {
    $job_types = [ 'Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship', 'Temporary', 'Remote', 'Hybrid' ];
    foreach ( $job_types as $type ) {
        if ( ! term_exists( $type, 'job_type' ) ) {
            wp_insert_term( $type, 'job_type' );
        }
    }

    $experience = [ 'Entry Level', 'Junior', 'Mid Level', 'Senior', 'Lead', 'Manager', 'Director', 'Executive', 'No Experience Required' ];
    foreach ( $experience as $exp ) {
        if ( ! term_exists( $exp, 'job_experience' ) ) {
            wp_insert_term( $exp, 'job_experience' );
        }
    }

    $salaries = [ '$0 - $25k', '$25k - $50k', '$50k - $75k', '$75k - $100k', '$100k - $150k', '$150k+' ];
    foreach ( $salaries as $range ) {
        if ( ! term_exists( $range, 'job_salary' ) ) {
            wp_insert_term( $range, 'job_salary' );
        }
    }

    $industries = [ 'Technology', 'Healthcare', 'Finance', 'Education', 'Marketing', 'Design', 'Engineering', 'Sales', 'Legal', 'Real Estate' ];
    foreach ( $industries as $industry ) {
        if ( ! term_exists( $industry, 'company_industry' ) ) {
            wp_insert_term( $industry, 'company_industry' );
        }
    }

    $sizes = [ '1-10 employees', '11-50 employees', '51-200 employees', '201-500 employees', '501-1000 employees', '1000+ employees' ];
    foreach ( $sizes as $size ) {
        if ( ! term_exists( $size, 'company_size' ) ) {
            wp_insert_term( $size, 'company_size' );
        }
    }
}
