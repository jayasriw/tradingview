<?php
/**
 * CiviJobs Custom Post Types
 *
 * Registers all custom post types used by the theme.
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register all custom post types.
 */
function civijobs_register_post_types(): void {
    civijobs_register_cpt_job();
    civijobs_register_cpt_company();
    civijobs_register_cpt_resume();
    civijobs_register_cpt_service();
    civijobs_register_cpt_package();
    civijobs_register_cpt_meeting();
    civijobs_register_cpt_message();
}
add_action( 'init', 'civijobs_register_post_types' );

// ============================================================
// civi_job — Job Listings
// ============================================================

/**
 * Register the civi_job post type.
 */
function civijobs_register_cpt_job(): void {
    $labels = [
        'name'                  => _x( 'Jobs',                   'post type general name', 'civijobs' ),
        'singular_name'         => _x( 'Job',                    'post type singular name', 'civijobs' ),
        'menu_name'             => _x( 'Jobs',                   'admin menu',             'civijobs' ),
        'name_admin_bar'        => _x( 'Job',                    'add new on admin bar',   'civijobs' ),
        'add_new'               => __( 'Add New',                                           'civijobs' ),
        'add_new_item'          => __( 'Add New Job',                                       'civijobs' ),
        'new_item'              => __( 'New Job',                                           'civijobs' ),
        'edit_item'             => __( 'Edit Job',                                          'civijobs' ),
        'view_item'             => __( 'View Job',                                          'civijobs' ),
        'all_items'             => __( 'All Jobs',                                          'civijobs' ),
        'search_items'          => __( 'Search Jobs',                                       'civijobs' ),
        'parent_item_colon'     => __( 'Parent Jobs:',                                      'civijobs' ),
        'not_found'             => __( 'No jobs found.',                                    'civijobs' ),
        'not_found_in_trash'    => __( 'No jobs found in Trash.',                           'civijobs' ),
        'featured_image'        => __( 'Job Featured Image',                                'civijobs' ),
        'set_featured_image'    => __( 'Set job image',                                     'civijobs' ),
        'remove_featured_image' => __( 'Remove job image',                                  'civijobs' ),
        'use_featured_image'    => __( 'Use as job image',                                  'civijobs' ),
        'archives'              => __( 'Job Archives',                                      'civijobs' ),
        'insert_into_item'      => __( 'Insert into job',                                   'civijobs' ),
        'uploaded_to_this_item' => __( 'Uploaded to this job',                              'civijobs' ),
        'items_list'            => __( 'Jobs list',                                         'civijobs' ),
        'items_list_navigation' => __( 'Jobs list navigation',                              'civijobs' ),
        'filter_items_list'     => __( 'Filter jobs list',                                  'civijobs' ),
    ];

    $args = [
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'show_in_rest'        => true,
        'query_var'           => true,
        'rewrite'             => [
            'slug'       => apply_filters( 'civijobs_job_slug', 'jobs' ),
            'with_front' => false,
            'feeds'      => true,
            'pages'      => true,
        ],
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'has_archive'         => apply_filters( 'civijobs_job_archive_slug', 'jobs' ),
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-portfolio',
        'supports'            => [
            'title',
            'editor',
            'thumbnail',
            'custom-fields',
            'revisions',
            'excerpt',
        ],
        'taxonomies'          => [
            'job_category',
            'job_type',
            'job_location',
            'job_experience',
            'job_salary',
        ],
        'delete_with_user'    => false,
        'can_export'          => true,
    ];

    register_post_type( 'civi_job', apply_filters( 'civijobs_cpt_job_args', $args ) );
}

// ============================================================
// civi_company — Company Profiles
// ============================================================

/**
 * Register the civi_company post type.
 */
function civijobs_register_cpt_company(): void {
    $labels = [
        'name'                  => _x( 'Companies',               'post type general name', 'civijobs' ),
        'singular_name'         => _x( 'Company',                 'post type singular name', 'civijobs' ),
        'menu_name'             => _x( 'Companies',               'admin menu',             'civijobs' ),
        'name_admin_bar'        => _x( 'Company',                 'add new on admin bar',   'civijobs' ),
        'add_new'               => __( 'Add New',                                            'civijobs' ),
        'add_new_item'          => __( 'Add New Company',                                    'civijobs' ),
        'new_item'              => __( 'New Company',                                        'civijobs' ),
        'edit_item'             => __( 'Edit Company',                                       'civijobs' ),
        'view_item'             => __( 'View Company',                                       'civijobs' ),
        'all_items'             => __( 'All Companies',                                      'civijobs' ),
        'search_items'          => __( 'Search Companies',                                   'civijobs' ),
        'not_found'             => __( 'No companies found.',                                'civijobs' ),
        'not_found_in_trash'    => __( 'No companies found in Trash.',                       'civijobs' ),
        'featured_image'        => __( 'Company Logo',                                       'civijobs' ),
        'set_featured_image'    => __( 'Set company logo',                                   'civijobs' ),
        'remove_featured_image' => __( 'Remove company logo',                                'civijobs' ),
        'use_featured_image'    => __( 'Use as company logo',                                'civijobs' ),
        'archives'              => __( 'Company Archives',                                   'civijobs' ),
        'items_list'            => __( 'Companies list',                                     'civijobs' ),
        'items_list_navigation' => __( 'Companies list navigation',                          'civijobs' ),
        'filter_items_list'     => __( 'Filter companies list',                              'civijobs' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => true,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => [
            'slug'       => apply_filters( 'civijobs_company_slug', 'companies' ),
            'with_front' => false,
            'feeds'      => true,
        ],
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'has_archive'        => apply_filters( 'civijobs_company_archive_slug', 'companies' ),
        'hierarchical'       => false,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-building',
        'supports'           => [
            'title',
            'editor',
            'thumbnail',
            'custom-fields',
            'revisions',
            'excerpt',
        ],
        'taxonomies'         => [
            'company_industry',
            'company_size',
        ],
        'delete_with_user'   => false,
        'can_export'         => true,
    ];

    register_post_type( 'civi_company', apply_filters( 'civijobs_cpt_company_args', $args ) );
}

// ============================================================
// civi_resume — Candidate Resumes / Profiles
// ============================================================

/**
 * Register the civi_resume post type.
 */
function civijobs_register_cpt_resume(): void {
    $labels = [
        'name'                  => _x( 'Resumes',                 'post type general name', 'civijobs' ),
        'singular_name'         => _x( 'Resume',                  'post type singular name', 'civijobs' ),
        'menu_name'             => _x( 'Resumes',                 'admin menu',             'civijobs' ),
        'name_admin_bar'        => _x( 'Resume',                  'add new on admin bar',   'civijobs' ),
        'add_new'               => __( 'Add New',                                            'civijobs' ),
        'add_new_item'          => __( 'Add New Resume',                                     'civijobs' ),
        'new_item'              => __( 'New Resume',                                         'civijobs' ),
        'edit_item'             => __( 'Edit Resume',                                        'civijobs' ),
        'view_item'             => __( 'View Resume',                                        'civijobs' ),
        'all_items'             => __( 'All Resumes',                                        'civijobs' ),
        'search_items'          => __( 'Search Resumes',                                     'civijobs' ),
        'not_found'             => __( 'No resumes found.',                                  'civijobs' ),
        'not_found_in_trash'    => __( 'No resumes found in Trash.',                         'civijobs' ),
        'featured_image'        => __( 'Candidate Photo',                                    'civijobs' ),
        'set_featured_image'    => __( 'Set candidate photo',                                'civijobs' ),
        'remove_featured_image' => __( 'Remove candidate photo',                             'civijobs' ),
        'use_featured_image'    => __( 'Use as candidate photo',                             'civijobs' ),
        'archives'              => __( 'Resume Archives',                                    'civijobs' ),
        'items_list'            => __( 'Resumes list',                                       'civijobs' ),
        'items_list_navigation' => __( 'Resumes list navigation',                            'civijobs' ),
        'filter_items_list'     => __( 'Filter resumes list',                                'civijobs' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => true,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => [
            'slug'       => apply_filters( 'civijobs_resume_slug', 'resumes' ),
            'with_front' => false,
            'feeds'      => true,
        ],
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'has_archive'        => apply_filters( 'civijobs_resume_archive_slug', 'candidates' ),
        'hierarchical'       => false,
        'menu_position'      => 7,
        'menu_icon'          => 'dashicons-id',
        'supports'           => [
            'title',
            'editor',
            'thumbnail',
            'custom-fields',
            'revisions',
            'excerpt',
        ],
        'taxonomies'         => [
            'resume_skills',
            'job_category',
            'job_experience',
        ],
        'delete_with_user'   => true,
        'can_export'         => true,
    ];

    register_post_type( 'civi_resume', apply_filters( 'civijobs_cpt_resume_args', $args ) );
}

// ============================================================
// civi_service — Freelancer Services (gig-style)
// ============================================================

/**
 * Register the civi_service post type.
 */
function civijobs_register_cpt_service(): void {
    $labels = [
        'name'                  => _x( 'Services',                'post type general name', 'civijobs' ),
        'singular_name'         => _x( 'Service',                 'post type singular name', 'civijobs' ),
        'menu_name'             => _x( 'Services',                'admin menu',             'civijobs' ),
        'name_admin_bar'        => _x( 'Service',                 'add new on admin bar',   'civijobs' ),
        'add_new'               => __( 'Add New',                                            'civijobs' ),
        'add_new_item'          => __( 'Add New Service',                                    'civijobs' ),
        'new_item'              => __( 'New Service',                                        'civijobs' ),
        'edit_item'             => __( 'Edit Service',                                       'civijobs' ),
        'view_item'             => __( 'View Service',                                       'civijobs' ),
        'all_items'             => __( 'All Services',                                       'civijobs' ),
        'search_items'          => __( 'Search Services',                                    'civijobs' ),
        'not_found'             => __( 'No services found.',                                 'civijobs' ),
        'not_found_in_trash'    => __( 'No services found in Trash.',                        'civijobs' ),
        'featured_image'        => __( 'Service Image',                                      'civijobs' ),
        'set_featured_image'    => __( 'Set service image',                                  'civijobs' ),
        'archives'              => __( 'Service Archives',                                   'civijobs' ),
        'items_list'            => __( 'Services list',                                      'civijobs' ),
        'items_list_navigation' => __( 'Services list navigation',                           'civijobs' ),
        'filter_items_list'     => __( 'Filter services list',                               'civijobs' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => true,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => [
            'slug'       => apply_filters( 'civijobs_service_slug', 'services' ),
            'with_front' => false,
            'feeds'      => true,
        ],
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'has_archive'        => apply_filters( 'civijobs_service_archive_slug', 'services' ),
        'hierarchical'       => false,
        'menu_position'      => 8,
        'menu_icon'          => 'dashicons-cart',
        'supports'           => [
            'title',
            'editor',
            'thumbnail',
            'custom-fields',
            'revisions',
            'excerpt',
        ],
        'taxonomies'         => [
            'service_category',
        ],
        'delete_with_user'   => true,
        'can_export'         => true,
    ];

    register_post_type( 'civi_service', apply_filters( 'civijobs_cpt_service_args', $args ) );
}

// ============================================================
// civi_package — Membership / Pricing Packages
// ============================================================

/**
 * Register the civi_package post type.
 */
function civijobs_register_cpt_package(): void {
    $labels = [
        'name'                  => _x( 'Packages',                'post type general name', 'civijobs' ),
        'singular_name'         => _x( 'Package',                 'post type singular name', 'civijobs' ),
        'menu_name'             => _x( 'Packages',                'admin menu',             'civijobs' ),
        'name_admin_bar'        => _x( 'Package',                 'add new on admin bar',   'civijobs' ),
        'add_new'               => __( 'Add New',                                            'civijobs' ),
        'add_new_item'          => __( 'Add New Package',                                    'civijobs' ),
        'new_item'              => __( 'New Package',                                        'civijobs' ),
        'edit_item'             => __( 'Edit Package',                                       'civijobs' ),
        'view_item'             => __( 'View Package',                                       'civijobs' ),
        'all_items'             => __( 'All Packages',                                       'civijobs' ),
        'search_items'          => __( 'Search Packages',                                    'civijobs' ),
        'not_found'             => __( 'No packages found.',                                 'civijobs' ),
        'not_found_in_trash'    => __( 'No packages found in Trash.',                        'civijobs' ),
        'items_list'            => __( 'Packages list',                                      'civijobs' ),
        'items_list_navigation' => __( 'Packages list navigation',                           'civijobs' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => false,
        'show_in_admin_bar'  => true,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => [
            'slug'       => apply_filters( 'civijobs_package_slug', 'packages' ),
            'with_front' => false,
        ],
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 9,
        'menu_icon'          => 'dashicons-awards',
        'supports'           => [
            'title',
            'editor',
            'custom-fields',
            'thumbnail',
        ],
        'delete_with_user'   => false,
        'can_export'         => true,
    ];

    register_post_type( 'civi_package', apply_filters( 'civijobs_cpt_package_args', $args ) );
}

// ============================================================
// civi_meeting — Scheduled Meetings
// ============================================================

/**
 * Register the civi_meeting post type.
 * Non-public — managed only via dashboard and custom tables.
 */
function civijobs_register_cpt_meeting(): void {
    $labels = [
        'name'                  => _x( 'Meetings',                'post type general name', 'civijobs' ),
        'singular_name'         => _x( 'Meeting',                 'post type singular name', 'civijobs' ),
        'menu_name'             => _x( 'Meetings',                'admin menu',             'civijobs' ),
        'name_admin_bar'        => _x( 'Meeting',                 'add new on admin bar',   'civijobs' ),
        'add_new'               => __( 'Add New',                                            'civijobs' ),
        'add_new_item'          => __( 'Schedule Meeting',                                   'civijobs' ),
        'new_item'              => __( 'New Meeting',                                        'civijobs' ),
        'edit_item'             => __( 'Edit Meeting',                                       'civijobs' ),
        'view_item'             => __( 'View Meeting',                                       'civijobs' ),
        'all_items'             => __( 'All Meetings',                                       'civijobs' ),
        'search_items'          => __( 'Search Meetings',                                    'civijobs' ),
        'not_found'             => __( 'No meetings found.',                                 'civijobs' ),
        'not_found_in_trash'    => __( 'No meetings found in Trash.',                        'civijobs' ),
        'items_list'            => __( 'Meetings list',                                      'civijobs' ),
        'items_list_navigation' => __( 'Meetings list navigation',                           'civijobs' ),
    ];

    $args = [
        'labels'              => $labels,
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => false,
        'show_in_rest'        => false,
        'query_var'           => false,
        'rewrite'             => false,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 10,
        'menu_icon'           => 'dashicons-calendar-alt',
        'supports'            => [
            'title',
            'custom-fields',
        ],
        'delete_with_user'    => false,
        'can_export'          => false,
        'exclude_from_search' => true,
    ];

    register_post_type( 'civi_meeting', apply_filters( 'civijobs_cpt_meeting_args', $args ) );
}

// ============================================================
// civi_message — Private Messages
// ============================================================

/**
 * Register the civi_message post type.
 * Non-public — all messaging goes through custom tables; this CPT is a fallback.
 */
function civijobs_register_cpt_message(): void {
    $labels = [
        'name'                  => _x( 'Messages',                'post type general name', 'civijobs' ),
        'singular_name'         => _x( 'Message',                 'post type singular name', 'civijobs' ),
        'menu_name'             => _x( 'Messages',                'admin menu',             'civijobs' ),
        'name_admin_bar'        => _x( 'Message',                 'add new on admin bar',   'civijobs' ),
        'add_new'               => __( 'New Message',                                        'civijobs' ),
        'add_new_item'          => __( 'Compose Message',                                    'civijobs' ),
        'new_item'              => __( 'New Message',                                        'civijobs' ),
        'edit_item'             => __( 'View Message',                                       'civijobs' ),
        'view_item'             => __( 'View Message',                                       'civijobs' ),
        'all_items'             => __( 'All Messages',                                       'civijobs' ),
        'search_items'          => __( 'Search Messages',                                    'civijobs' ),
        'not_found'             => __( 'No messages found.',                                 'civijobs' ),
        'not_found_in_trash'    => __( 'No messages found in Trash.',                        'civijobs' ),
        'items_list'            => __( 'Messages list',                                      'civijobs' ),
        'items_list_navigation' => __( 'Messages list navigation',                           'civijobs' ),
    ];

    $args = [
        'labels'              => $labels,
        'public'              => false,
        'publicly_queryable'  => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => false,
        'show_in_rest'        => false,
        'query_var'           => false,
        'rewrite'             => false,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 11,
        'menu_icon'           => 'dashicons-email',
        'supports'            => [
            'title',
            'editor',
        ],
        'delete_with_user'    => true,
        'can_export'          => false,
        'exclude_from_search' => true,
    ];

    register_post_type( 'civi_message', apply_filters( 'civijobs_cpt_message_args', $args ) );
}

// ============================================================
// Custom Rewrite Rules
// ============================================================

/**
 * Add custom rewrite rules for dashboard pages, job application,
 * and other theme-specific URL patterns.
 */
function civijobs_add_rewrite_rules(): void {
    // Job application endpoint: /jobs/{job-name}/apply/
    add_rewrite_rule(
        '^jobs/([^/]+)/apply/?$',
        'index.php?civi_job=$matches[1]&cj_action=apply',
        'top'
    );

    // Job application success: /jobs/{job-name}/apply/success/
    add_rewrite_rule(
        '^jobs/([^/]+)/apply/success/?$',
        'index.php?civi_job=$matches[1]&cj_action=apply_success',
        'top'
    );

    // Company jobs: /companies/{company-name}/jobs/
    add_rewrite_rule(
        '^companies/([^/]+)/jobs/?$',
        'index.php?civi_company=$matches[1]&cj_action=company_jobs',
        'top'
    );

    // Company reviews: /companies/{company-name}/reviews/
    add_rewrite_rule(
        '^companies/([^/]+)/reviews/?$',
        'index.php?civi_company=$matches[1]&cj_action=company_reviews',
        'top'
    );

    // Candidate profile: /candidates/{username}/
    add_rewrite_rule(
        '^candidates/([^/]+)/?$',
        'index.php?pagename=candidates&cj_candidate=$matches[1]',
        'top'
    );
}
add_action( 'init', 'civijobs_add_rewrite_rules' );

/**
 * Register custom query vars.
 *
 * @param  string[] $vars Existing query vars.
 * @return string[]
 */
function civijobs_register_query_vars( array $vars ): array {
    $vars[] = 'cj_action';
    $vars[] = 'cj_candidate';
    $vars[] = 'cj_tab';
    $vars[] = 'cj_message_thread';
    $vars[] = 'cj_meeting_id';
    $vars[] = 'cj_application_id';
    return $vars;
}
add_filter( 'query_vars', 'civijobs_register_query_vars' );

// ============================================================
// Post Status Registration
// ============================================================

/**
 * Register custom post statuses for jobs.
 */
function civijobs_register_post_statuses(): void {
    // Active (publicly visible)
    register_post_status( 'cj_active', [
        'label'                     => _x( 'Active', 'job status', 'civijobs' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        /* translators: %s: number of active jobs */
        'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'civijobs' ),
        'post_type'                 => [ 'civi_job' ],
    ] );

    // Expired
    register_post_status( 'cj_expired', [
        'label'                     => _x( 'Expired', 'job status', 'civijobs' ),
        'public'                    => false,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        /* translators: %s: number of expired jobs */
        'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'civijobs' ),
        'post_type'                 => [ 'civi_job' ],
    ] );

    // Filled
    register_post_status( 'cj_filled', [
        'label'                     => _x( 'Filled', 'job status', 'civijobs' ),
        'public'                    => false,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        /* translators: %s: number of filled jobs */
        'label_count'               => _n_noop( 'Filled <span class="count">(%s)</span>', 'Filled <span class="count">(%s)</span>', 'civijobs' ),
        'post_type'                 => [ 'civi_job' ],
    ] );

    // Pending review (awaiting moderation)
    register_post_status( 'cj_pending_review', [
        'label'                     => _x( 'Pending Review', 'job status', 'civijobs' ),
        'public'                    => false,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        /* translators: %s: number of jobs pending review */
        'label_count'               => _n_noop( 'Pending Review <span class="count">(%s)</span>', 'Pending Review <span class="count">(%s)</span>', 'civijobs' ),
        'post_type'                 => [ 'civi_job' ],
    ] );
}
add_action( 'init', 'civijobs_register_post_statuses' );

// ============================================================
// Automatic Job Expiry
// ============================================================

/**
 * Schedule the daily job expiry check if it doesn't exist.
 */
function civijobs_schedule_expiry_check(): void {
    if ( ! wp_next_scheduled( 'civijobs_check_job_expiry' ) ) {
        wp_schedule_event( time(), 'daily', 'civijobs_check_job_expiry' );
    }
}
add_action( 'wp', 'civijobs_schedule_expiry_check' );

/**
 * Run expired jobs check and update their status.
 */
function civijobs_check_job_expiry(): void {
    global $wpdb;

    $today = current_time( 'Y-m-d' );

    // Get all published jobs with a past deadline
    $expired_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT p.ID
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type   = 'civi_job'
               AND p.post_status = 'publish'
               AND pm.meta_key   = '_job_deadline'
               AND pm.meta_value != ''
               AND pm.meta_value < %s",
            $today
        )
    );

    if ( empty( $expired_ids ) ) {
        return;
    }

    foreach ( $expired_ids as $job_id ) {
        update_post_meta( $job_id, '_job_status', 'expired' );

        // Fire action so other systems (email, etc.) can react
        do_action( 'civijobs_job_expired', (int) $job_id );
    }
}
add_action( 'civijobs_check_job_expiry', 'civijobs_check_job_expiry' );

// ============================================================
// Meta Box Registration
// ============================================================

/**
 * Register meta boxes for custom post types.
 */
function civijobs_register_meta_boxes(): void {
    // Job details
    add_meta_box(
        'civi_job_details',
        __( 'Job Details', 'civijobs' ),
        'civijobs_render_job_details_metabox',
        'civi_job',
        'normal',
        'high'
    );

    // Company details
    add_meta_box(
        'civi_company_details',
        __( 'Company Details', 'civijobs' ),
        'civijobs_render_company_details_metabox',
        'civi_company',
        'normal',
        'high'
    );

    // Resume / candidate details
    add_meta_box(
        'civi_resume_details',
        __( 'Candidate Details', 'civijobs' ),
        'civijobs_render_resume_details_metabox',
        'civi_resume',
        'normal',
        'high'
    );

    // Package details
    add_meta_box(
        'civi_package_details',
        __( 'Package Details', 'civijobs' ),
        'civijobs_render_package_details_metabox',
        'civi_package',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'civijobs_register_meta_boxes' );

/**
 * Render job details meta box.
 *
 * @param \WP_Post $post Current post object.
 */
function civijobs_render_job_details_metabox( \WP_Post $post ): void {
    wp_nonce_field( 'civijobs_job_details_nonce', '_cj_job_nonce' );

    $fields = [
        '_job_company_id'   => [ 'label' => __( 'Company ID',     'civijobs' ), 'type' => 'number' ],
        '_job_location'     => [ 'label' => __( 'Location',       'civijobs' ), 'type' => 'text'   ],
        '_job_latitude'     => [ 'label' => __( 'Latitude',       'civijobs' ), 'type' => 'text'   ],
        '_job_longitude'    => [ 'label' => __( 'Longitude',      'civijobs' ), 'type' => 'text'   ],
        '_job_salary_min'   => [ 'label' => __( 'Salary Min',     'civijobs' ), 'type' => 'number' ],
        '_job_salary_max'   => [ 'label' => __( 'Salary Max',     'civijobs' ), 'type' => 'number' ],
        '_job_salary_type'  => [ 'label' => __( 'Salary Type',    'civijobs' ), 'type' => 'select', 'options' => [ 'hourly' => __( 'Hourly', 'civijobs' ), 'daily' => __( 'Daily', 'civijobs' ), 'weekly' => __( 'Weekly', 'civijobs' ), 'monthly' => __( 'Monthly', 'civijobs' ), 'yearly' => __( 'Yearly', 'civijobs' ) ] ],
        '_job_status'       => [ 'label' => __( 'Status',         'civijobs' ), 'type' => 'select', 'options' => [ 'active' => __( 'Active', 'civijobs' ), 'expired' => __( 'Expired', 'civijobs' ), 'filled' => __( 'Filled', 'civijobs' ), 'paused' => __( 'Paused', 'civijobs' ) ] ],
        '_job_deadline'     => [ 'label' => __( 'Deadline',       'civijobs' ), 'type' => 'date'   ],
        '_job_apply_url'    => [ 'label' => __( 'External Apply URL', 'civijobs' ), 'type' => 'url' ],
        '_job_featured'     => [ 'label' => __( 'Featured',       'civijobs' ), 'type' => 'checkbox' ],
        '_job_remote'       => [ 'label' => __( 'Remote',         'civijobs' ), 'type' => 'checkbox' ],
        '_job_vacancies'    => [ 'label' => __( 'Vacancies',      'civijobs' ), 'type' => 'number' ],
        '_job_employer_id'  => [ 'label' => __( 'Employer User ID', 'civijobs' ), 'type' => 'number' ],
    ];

    echo '<table class="form-table">';
    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post->ID, $key, true );
        echo '<tr>';
        printf( '<th><label for="%s">%s</label></th>', esc_attr( $key ), esc_html( $field['label'] ) );
        echo '<td>';

        switch ( $field['type'] ) {
            case 'select':
                printf( '<select id="%s" name="%s" class="regular-text">', esc_attr( $key ), esc_attr( $key ) );
                foreach ( $field['options'] as $opt_val => $opt_label ) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr( $opt_val ),
                        selected( $value, $opt_val, false ),
                        esc_html( $opt_label )
                    );
                }
                echo '</select>';
                break;
            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%s" name="%s" value="1"%s>',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    checked( $value, '1', false )
                );
                break;
            case 'url':
                printf(
                    '<input type="url" id="%s" name="%s" value="%s" class="regular-text">',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_url( $value )
                );
                break;
            default:
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">',
                    esc_attr( $field['type'] ),
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_attr( $value )
                );
        }

        echo '</td></tr>';
    }
    echo '</table>';
}

/**
 * Render company details meta box.
 *
 * @param \WP_Post $post Current post object.
 */
function civijobs_render_company_details_metabox( \WP_Post $post ): void {
    wp_nonce_field( 'civijobs_company_details_nonce', '_cj_company_nonce' );

    $fields = [
        '_company_website'    => [ 'label' => __( 'Website',        'civijobs' ), 'type' => 'url'    ],
        '_company_email'      => [ 'label' => __( 'Email',          'civijobs' ), 'type' => 'email'  ],
        '_company_phone'      => [ 'label' => __( 'Phone',          'civijobs' ), 'type' => 'text'   ],
        '_company_location'   => [ 'label' => __( 'Location',       'civijobs' ), 'type' => 'text'   ],
        '_company_latitude'   => [ 'label' => __( 'Latitude',       'civijobs' ), 'type' => 'text'   ],
        '_company_longitude'  => [ 'label' => __( 'Longitude',      'civijobs' ), 'type' => 'text'   ],
        '_company_founded'    => [ 'label' => __( 'Founded Year',   'civijobs' ), 'type' => 'number' ],
        '_company_linkedin'   => [ 'label' => __( 'LinkedIn URL',   'civijobs' ), 'type' => 'url'    ],
        '_company_twitter'    => [ 'label' => __( 'Twitter URL',    'civijobs' ), 'type' => 'url'    ],
        '_company_facebook'   => [ 'label' => __( 'Facebook URL',   'civijobs' ), 'type' => 'url'    ],
        '_company_employer_id'=> [ 'label' => __( 'Owner User ID',  'civijobs' ), 'type' => 'number' ],
        '_company_verified'   => [ 'label' => __( 'Verified',       'civijobs' ), 'type' => 'checkbox' ],
        '_company_featured'   => [ 'label' => __( 'Featured',       'civijobs' ), 'type' => 'checkbox' ],
    ];

    echo '<table class="form-table">';
    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post->ID, $key, true );
        echo '<tr>';
        printf( '<th><label for="%s">%s</label></th>', esc_attr( $key ), esc_html( $field['label'] ) );
        echo '<td>';

        switch ( $field['type'] ) {
            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%s" name="%s" value="1"%s>',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    checked( $value, '1', false )
                );
                break;
            case 'url':
                printf(
                    '<input type="url" id="%s" name="%s" value="%s" class="regular-text">',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_url( $value )
                );
                break;
            default:
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">',
                    esc_attr( $field['type'] ),
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_attr( $value )
                );
        }

        echo '</td></tr>';
    }
    echo '</table>';
}

/**
 * Render resume/candidate details meta box.
 *
 * @param \WP_Post $post Current post object.
 */
function civijobs_render_resume_details_metabox( \WP_Post $post ): void {
    wp_nonce_field( 'civijobs_resume_details_nonce', '_cj_resume_nonce' );

    $fields = [
        '_resume_user_id'    => [ 'label' => __( 'User ID',         'civijobs' ), 'type' => 'number' ],
        '_resume_title'      => [ 'label' => __( 'Job Title',       'civijobs' ), 'type' => 'text'   ],
        '_resume_location'   => [ 'label' => __( 'Location',        'civijobs' ), 'type' => 'text'   ],
        '_resume_salary'     => [ 'label' => __( 'Expected Salary', 'civijobs' ), 'type' => 'text'   ],
        '_resume_phone'      => [ 'label' => __( 'Phone',           'civijobs' ), 'type' => 'text'   ],
        '_resume_website'    => [ 'label' => __( 'Website',         'civijobs' ), 'type' => 'url'    ],
        '_resume_linkedin'   => [ 'label' => __( 'LinkedIn',        'civijobs' ), 'type' => 'url'    ],
        '_resume_github'     => [ 'label' => __( 'GitHub',          'civijobs' ), 'type' => 'url'    ],
        '_resume_available'  => [ 'label' => __( 'Available',       'civijobs' ), 'type' => 'checkbox' ],
        '_resume_featured'   => [ 'label' => __( 'Featured',        'civijobs' ), 'type' => 'checkbox' ],
    ];

    echo '<table class="form-table">';
    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post->ID, $key, true );
        echo '<tr>';
        printf( '<th><label for="%s">%s</label></th>', esc_attr( $key ), esc_html( $field['label'] ) );
        echo '<td>';

        switch ( $field['type'] ) {
            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%s" name="%s" value="1"%s>',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    checked( $value, '1', false )
                );
                break;
            case 'url':
                printf(
                    '<input type="url" id="%s" name="%s" value="%s" class="regular-text">',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_url( $value )
                );
                break;
            default:
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">',
                    esc_attr( $field['type'] ),
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_attr( $value )
                );
        }

        echo '</td></tr>';
    }
    echo '</table>';
}

/**
 * Render package details meta box.
 *
 * @param \WP_Post $post Current post object.
 */
function civijobs_render_package_details_metabox( \WP_Post $post ): void {
    wp_nonce_field( 'civijobs_package_details_nonce', '_cj_package_nonce' );

    $fields = [
        '_package_price'        => [ 'label' => __( 'Price',              'civijobs' ), 'type' => 'number' ],
        '_package_currency'     => [ 'label' => __( 'Currency',           'civijobs' ), 'type' => 'text'   ],
        '_package_duration'     => [ 'label' => __( 'Duration (days)',    'civijobs' ), 'type' => 'number' ],
        '_package_job_listings' => [ 'label' => __( 'Job Listings',       'civijobs' ), 'type' => 'number' ],
        '_package_featured_jobs'=> [ 'label' => __( 'Featured Jobs',      'civijobs' ), 'type' => 'number' ],
        '_package_resume_views' => [ 'label' => __( 'Resume Views',       'civijobs' ), 'type' => 'number' ],
        '_package_color'        => [ 'label' => __( 'Badge Color',        'civijobs' ), 'type' => 'color'  ],
        '_package_is_popular'   => [ 'label' => __( 'Mark as Popular',    'civijobs' ), 'type' => 'checkbox' ],
        '_package_woo_product'  => [ 'label' => __( 'WooCommerce Product ID', 'civijobs' ), 'type' => 'number' ],
    ];

    echo '<table class="form-table">';
    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post->ID, $key, true );
        echo '<tr>';
        printf( '<th><label for="%s">%s</label></th>', esc_attr( $key ), esc_html( $field['label'] ) );
        echo '<td>';

        switch ( $field['type'] ) {
            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%s" name="%s" value="1"%s>',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    checked( $value, '1', false )
                );
                break;
            default:
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">',
                    esc_attr( $field['type'] ),
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_attr( $value )
                );
        }

        echo '</td></tr>';
    }
    echo '</table>';
}

// ============================================================
// Meta Box Save
// ============================================================

/**
 * Save custom meta box fields for all CPTs.
 *
 * @param int      $post_id Post ID being saved.
 * @param \WP_Post $post    Post object.
 */
function civijobs_save_meta_boxes( int $post_id, \WP_Post $post ): void {
    // Don't save on autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Don't save during AJAX
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }

    // Check post type and nonce
    $nonce_map = [
        'civi_job'     => [ 'nonce' => '_cj_job_nonce',     'action' => 'civijobs_job_details_nonce' ],
        'civi_company' => [ 'nonce' => '_cj_company_nonce', 'action' => 'civijobs_company_details_nonce' ],
        'civi_resume'  => [ 'nonce' => '_cj_resume_nonce',  'action' => 'civijobs_resume_details_nonce' ],
        'civi_package' => [ 'nonce' => '_cj_package_nonce', 'action' => 'civijobs_package_details_nonce' ],
    ];

    if ( ! isset( $nonce_map[ $post->post_type ] ) ) {
        return;
    }

    $map = $nonce_map[ $post->post_type ];

    if ( ! isset( $_POST[ $map['nonce'] ] ) ) { // phpcs:ignore
        return;
    }

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $map['nonce'] ] ) ), $map['action'] ) ) { // phpcs:ignore
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Field definitions per post type
    $job_fields = [
        '_job_company_id', '_job_location', '_job_latitude', '_job_longitude',
        '_job_salary_min', '_job_salary_max', '_job_salary_type', '_job_status',
        '_job_deadline', '_job_apply_url', '_job_featured', '_job_remote',
        '_job_vacancies', '_job_employer_id',
    ];

    $company_fields = [
        '_company_website', '_company_email', '_company_phone', '_company_location',
        '_company_latitude', '_company_longitude', '_company_founded',
        '_company_linkedin', '_company_twitter', '_company_facebook',
        '_company_employer_id', '_company_verified', '_company_featured',
    ];

    $resume_fields = [
        '_resume_user_id', '_resume_title', '_resume_location', '_resume_salary',
        '_resume_phone', '_resume_website', '_resume_linkedin', '_resume_github',
        '_resume_available', '_resume_featured',
    ];

    $package_fields = [
        '_package_price', '_package_currency', '_package_duration',
        '_package_job_listings', '_package_featured_jobs', '_package_resume_views',
        '_package_color', '_package_is_popular', '_package_woo_product',
    ];

    $field_map = [
        'civi_job'     => $job_fields,
        'civi_company' => $company_fields,
        'civi_resume'  => $resume_fields,
        'civi_package' => $package_fields,
    ];

    $fields = $field_map[ $post->post_type ] ?? [];

    foreach ( $fields as $field_key ) {
        if ( ! isset( $_POST[ $field_key ] ) ) { // phpcs:ignore
            // Checkboxes not posted when unchecked
            if ( in_array( $field_key, [ '_job_featured', '_job_remote', '_company_verified', '_company_featured', '_resume_available', '_resume_featured', '_package_is_popular' ], true ) ) {
                update_post_meta( $post_id, $field_key, '0' );
            }
            continue;
        }

        $raw = $_POST[ $field_key ]; // phpcs:ignore

        // Sanitize based on field type
        if ( in_array( $field_key, [ '_job_apply_url', '_company_website', '_company_linkedin', '_company_twitter', '_company_facebook', '_resume_website', '_resume_linkedin', '_resume_github' ], true ) ) {
            $value = esc_url_raw( $raw );
        } elseif ( in_array( $field_key, [ '_job_salary_min', '_job_salary_max', '_job_company_id', '_job_vacancies', '_job_employer_id', '_company_founded', '_company_employer_id', '_resume_user_id', '_package_price', '_package_duration', '_package_job_listings', '_package_featured_jobs', '_package_resume_views', '_package_woo_product' ], true ) ) {
            $value = absint( $raw );
        } elseif ( in_array( $field_key, [ '_job_latitude', '_job_longitude', '_company_latitude', '_company_longitude' ], true ) ) {
            $value = (float) sanitize_text_field( $raw );
        } elseif ( in_array( $field_key, [ '_company_email', '_resume_phone' ], true ) ) {
            $value = sanitize_email( $raw );
        } elseif ( in_array( $field_key, [ '_job_featured', '_job_remote', '_company_verified', '_company_featured', '_resume_available', '_resume_featured', '_package_is_popular' ], true ) ) {
            $value = '1';
        } else {
            $value = sanitize_text_field( $raw );
        }

        update_post_meta( $post_id, $field_key, $value );
    }
}
add_action( 'save_post', 'civijobs_save_meta_boxes', 10, 2 );
