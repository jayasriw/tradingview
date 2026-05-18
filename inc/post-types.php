<?php
/**
 * Custom Post Types Registration
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'jobportal_register_post_types' );

function jobportal_register_post_types(): void {

    // Job
    register_post_type( 'jp_job', [
        'labels'        => [
            'name'          => __( 'Jobs', 'jobportal' ),
            'singular_name' => __( 'Job', 'jobportal' ),
            'add_new_item'  => __( 'Add New Job', 'jobportal' ),
            'edit_item'     => __( 'Edit Job', 'jobportal' ),
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => [ 'slug' => 'job' ],
        'supports'      => [ 'title', 'editor', 'author', 'thumbnail' ],
        'menu_icon'     => 'dashicons-businessman',
        'show_in_rest'  => true,
    ] );

    // Company
    register_post_type( 'jp_company', [
        'labels'        => [
            'name'          => __( 'Companies', 'jobportal' ),
            'singular_name' => __( 'Company', 'jobportal' ),
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => [ 'slug' => 'company' ],
        'supports'      => [ 'title', 'editor', 'author', 'thumbnail' ],
        'menu_icon'     => 'dashicons-building',
        'show_in_rest'  => true,
    ] );

    // Resume / Candidate Profile
    register_post_type( 'jp_resume', [
        'labels'        => [
            'name'          => __( 'Resumes', 'jobportal' ),
            'singular_name' => __( 'Resume', 'jobportal' ),
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => [ 'slug' => 'candidate' ],
        'supports'      => [ 'title', 'editor', 'author', 'thumbnail' ],
        'menu_icon'     => 'dashicons-id',
        'show_in_rest'  => true,
    ] );

    // Service (Freelancer marketplace)
    register_post_type( 'jp_service', [
        'labels'        => [
            'name'          => __( 'Services', 'jobportal' ),
            'singular_name' => __( 'Service', 'jobportal' ),
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => [ 'slug' => 'service' ],
        'supports'      => [ 'title', 'editor', 'author', 'thumbnail' ],
        'menu_icon'     => 'dashicons-hammer',
        'show_in_rest'  => true,
    ] );

    // Package
    register_post_type( 'jp_package', [
        'labels'        => [
            'name'          => __( 'Packages', 'jobportal' ),
            'singular_name' => __( 'Package', 'jobportal' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'supports'      => [ 'title', 'editor' ],
        'menu_icon'     => 'dashicons-products',
        'show_in_rest'  => true,
    ] );
}

// Custom post statuses for jobs
add_action( 'init', function() {
    register_post_status( 'cj_active',         [ 'label' => __( 'Active', 'jobportal' ),         'public' => true,  'post_type' => [ 'jp_job' ] ] );
    register_post_status( 'cj_expired',        [ 'label' => __( 'Expired', 'jobportal' ),        'public' => false, 'post_type' => [ 'jp_job' ] ] );
    register_post_status( 'cj_filled',         [ 'label' => __( 'Filled', 'jobportal' ),         'public' => false, 'post_type' => [ 'jp_job' ] ] );
    register_post_status( 'cj_pending_review', [ 'label' => __( 'Pending Review', 'jobportal' ), 'public' => false, 'post_type' => [ 'jp_job' ] ] );
} );
