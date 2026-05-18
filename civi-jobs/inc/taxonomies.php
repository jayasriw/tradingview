<?php
/**
 * CiviJobs Custom Taxonomies
 *
 * Registers all custom taxonomies and seeds default terms.
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register all custom taxonomies.
 */
function civijobs_register_taxonomies(): void {
    civijobs_register_tax_job_category();
    civijobs_register_tax_job_type();
    civijobs_register_tax_job_location();
    civijobs_register_tax_job_experience();
    civijobs_register_tax_job_salary();
    civijobs_register_tax_company_industry();
    civijobs_register_tax_company_size();
    civijobs_register_tax_service_category();
    civijobs_register_tax_resume_skills();
}
add_action( 'init', 'civijobs_register_taxonomies' );

// ============================================================
// job_category — Job Categories (hierarchical)
// ============================================================

function civijobs_register_tax_job_category(): void {
    $labels = [
        'name'                       => _x( 'Job Categories',            'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Job Category',              'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Job Categories',                                'civijobs' ),
        'popular_items'              => __( 'Popular Job Categories',                               'civijobs' ),
        'all_items'                  => __( 'All Job Categories',                                   'civijobs' ),
        'parent_item'                => __( 'Parent Category',                                      'civijobs' ),
        'parent_item_colon'          => __( 'Parent Category:',                                     'civijobs' ),
        'edit_item'                  => __( 'Edit Job Category',                                    'civijobs' ),
        'update_item'                => __( 'Update Job Category',                                  'civijobs' ),
        'add_new_item'               => __( 'Add New Job Category',                                 'civijobs' ),
        'new_item_name'              => __( 'New Job Category Name',                                'civijobs' ),
        'separate_items_with_commas' => __( 'Separate categories with commas',                      'civijobs' ),
        'add_or_remove_items'        => __( 'Add or remove categories',                             'civijobs' ),
        'choose_from_most_used'      => __( 'Choose from the most used categories',                 'civijobs' ),
        'not_found'                  => __( 'No categories found.',                                 'civijobs' ),
        'menu_name'                  => __( 'Job Categories',                                       'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Job Categories',                        'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'show_tagcloud'     => true,
        'query_var'         => true,
        'rewrite'           => [
            'slug'         => apply_filters( 'civijobs_job_category_slug', 'job-category' ),
            'with_front'   => false,
            'hierarchical' => true,
        ],
        'capabilities'      => [
            'manage_terms' => 'manage_categories',
            'edit_terms'   => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'edit_posts',
        ],
        'sort'              => true,
    ];

    register_taxonomy( 'job_category', [ 'civi_job', 'civi_resume' ], apply_filters( 'civijobs_tax_job_category_args', $args ) );
}

// ============================================================
// job_type — Job Types (flat)
// ============================================================

function civijobs_register_tax_job_type(): void {
    $labels = [
        'name'                       => _x( 'Job Types',                 'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Job Type',                  'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Job Types',                                     'civijobs' ),
        'popular_items'              => __( 'Popular Job Types',                                    'civijobs' ),
        'all_items'                  => __( 'All Job Types',                                        'civijobs' ),
        'edit_item'                  => __( 'Edit Job Type',                                        'civijobs' ),
        'update_item'                => __( 'Update Job Type',                                      'civijobs' ),
        'add_new_item'               => __( 'Add New Job Type',                                     'civijobs' ),
        'new_item_name'              => __( 'New Job Type Name',                                    'civijobs' ),
        'separate_items_with_commas' => __( 'Separate types with commas',                           'civijobs' ),
        'add_or_remove_items'        => __( 'Add or remove job types',                              'civijobs' ),
        'choose_from_most_used'      => __( 'Choose from the most used types',                      'civijobs' ),
        'not_found'                  => __( 'No job types found.',                                  'civijobs' ),
        'menu_name'                  => __( 'Job Types',                                            'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Job Types',                             'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => false,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'show_tagcloud'     => true,
        'query_var'         => true,
        'rewrite'           => [
            'slug'       => apply_filters( 'civijobs_job_type_slug', 'job-type' ),
            'with_front' => false,
        ],
    ];

    register_taxonomy( 'job_type', [ 'civi_job' ], apply_filters( 'civijobs_tax_job_type_args', $args ) );
}

// ============================================================
// job_location — Job Locations (flat tags)
// ============================================================

function civijobs_register_tax_job_location(): void {
    $labels = [
        'name'                       => _x( 'Job Locations',             'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Job Location',              'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Locations',                                     'civijobs' ),
        'popular_items'              => __( 'Popular Locations',                                    'civijobs' ),
        'all_items'                  => __( 'All Locations',                                        'civijobs' ),
        'edit_item'                  => __( 'Edit Location',                                        'civijobs' ),
        'update_item'                => __( 'Update Location',                                      'civijobs' ),
        'add_new_item'               => __( 'Add New Location',                                     'civijobs' ),
        'new_item_name'              => __( 'New Location Name',                                    'civijobs' ),
        'separate_items_with_commas' => __( 'Separate locations with commas',                       'civijobs' ),
        'add_or_remove_items'        => __( 'Add or remove locations',                              'civijobs' ),
        'choose_from_most_used'      => __( 'Choose from the most used locations',                  'civijobs' ),
        'not_found'                  => __( 'No locations found.',                                  'civijobs' ),
        'menu_name'                  => __( 'Job Locations',                                        'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Locations',                             'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => false,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => false,
        'show_tagcloud'     => true,
        'query_var'         => true,
        'rewrite'           => [
            'slug'       => apply_filters( 'civijobs_job_location_slug', 'job-location' ),
            'with_front' => false,
        ],
    ];

    register_taxonomy( 'job_location', [ 'civi_job' ], apply_filters( 'civijobs_tax_job_location_args', $args ) );
}

// ============================================================
// job_experience — Experience Levels (flat)
// ============================================================

function civijobs_register_tax_job_experience(): void {
    $labels = [
        'name'                       => _x( 'Experience Levels',         'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Experience Level',          'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Experience Levels',                             'civijobs' ),
        'all_items'                  => __( 'All Experience Levels',                                'civijobs' ),
        'edit_item'                  => __( 'Edit Experience Level',                                'civijobs' ),
        'update_item'                => __( 'Update Experience Level',                              'civijobs' ),
        'add_new_item'               => __( 'Add New Experience Level',                             'civijobs' ),
        'new_item_name'              => __( 'New Experience Level',                                 'civijobs' ),
        'not_found'                  => __( 'No experience levels found.',                          'civijobs' ),
        'menu_name'                  => __( 'Experience Levels',                                    'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Experience Levels',                     'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => false,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'show_tagcloud'     => false,
        'query_var'         => true,
        'rewrite'           => [
            'slug'       => apply_filters( 'civijobs_job_experience_slug', 'experience' ),
            'with_front' => false,
        ],
    ];

    register_taxonomy( 'job_experience', [ 'civi_job', 'civi_resume' ], apply_filters( 'civijobs_tax_job_experience_args', $args ) );
}

// ============================================================
// job_salary — Salary Ranges (flat)
// ============================================================

function civijobs_register_tax_job_salary(): void {
    $labels = [
        'name'                       => _x( 'Salary Ranges',             'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Salary Range',              'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Salary Ranges',                                 'civijobs' ),
        'all_items'                  => __( 'All Salary Ranges',                                    'civijobs' ),
        'edit_item'                  => __( 'Edit Salary Range',                                    'civijobs' ),
        'update_item'                => __( 'Update Salary Range',                                  'civijobs' ),
        'add_new_item'               => __( 'Add New Salary Range',                                 'civijobs' ),
        'new_item_name'              => __( 'New Salary Range',                                     'civijobs' ),
        'not_found'                  => __( 'No salary ranges found.',                              'civijobs' ),
        'menu_name'                  => __( 'Salary Ranges',                                        'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Salary Ranges',                         'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => false,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => false,
        'show_tagcloud'     => false,
        'query_var'         => true,
        'rewrite'           => [
            'slug'       => apply_filters( 'civijobs_job_salary_slug', 'salary-range' ),
            'with_front' => false,
        ],
        'meta_box_cb'       => false, // We use a custom meta box for salary
    ];

    register_taxonomy( 'job_salary', [ 'civi_job' ], apply_filters( 'civijobs_tax_job_salary_args', $args ) );
}

// ============================================================
// company_industry — Company Industries (hierarchical)
// ============================================================

function civijobs_register_tax_company_industry(): void {
    $labels = [
        'name'                       => _x( 'Industries',                'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Industry',                  'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Industries',                                    'civijobs' ),
        'popular_items'              => __( 'Popular Industries',                                   'civijobs' ),
        'all_items'                  => __( 'All Industries',                                       'civijobs' ),
        'parent_item'                => __( 'Parent Industry',                                      'civijobs' ),
        'parent_item_colon'          => __( 'Parent Industry:',                                     'civijobs' ),
        'edit_item'                  => __( 'Edit Industry',                                        'civijobs' ),
        'update_item'                => __( 'Update Industry',                                      'civijobs' ),
        'add_new_item'               => __( 'Add New Industry',                                     'civijobs' ),
        'new_item_name'              => __( 'New Industry Name',                                    'civijobs' ),
        'not_found'                  => __( 'No industries found.',                                 'civijobs' ),
        'menu_name'                  => __( 'Industries',                                           'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Industries',                            'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'show_tagcloud'     => false,
        'query_var'         => true,
        'rewrite'           => [
            'slug'         => apply_filters( 'civijobs_company_industry_slug', 'industry' ),
            'with_front'   => false,
            'hierarchical' => true,
        ],
    ];

    register_taxonomy( 'company_industry', [ 'civi_company' ], apply_filters( 'civijobs_tax_company_industry_args', $args ) );
}

// ============================================================
// company_size — Company Size (flat)
// ============================================================

function civijobs_register_tax_company_size(): void {
    $labels = [
        'name'                       => _x( 'Company Sizes',             'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Company Size',              'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Company Sizes',                                 'civijobs' ),
        'all_items'                  => __( 'All Company Sizes',                                    'civijobs' ),
        'edit_item'                  => __( 'Edit Company Size',                                    'civijobs' ),
        'update_item'                => __( 'Update Company Size',                                  'civijobs' ),
        'add_new_item'               => __( 'Add New Company Size',                                 'civijobs' ),
        'new_item_name'              => __( 'New Company Size',                                     'civijobs' ),
        'not_found'                  => __( 'No company sizes found.',                              'civijobs' ),
        'menu_name'                  => __( 'Company Sizes',                                        'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Company Sizes',                         'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => false,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'show_tagcloud'     => false,
        'query_var'         => true,
        'rewrite'           => [
            'slug'       => apply_filters( 'civijobs_company_size_slug', 'company-size' ),
            'with_front' => false,
        ],
    ];

    register_taxonomy( 'company_size', [ 'civi_company' ], apply_filters( 'civijobs_tax_company_size_args', $args ) );
}

// ============================================================
// service_category — Service Categories (hierarchical)
// ============================================================

function civijobs_register_tax_service_category(): void {
    $labels = [
        'name'                       => _x( 'Service Categories',        'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Service Category',          'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Service Categories',                            'civijobs' ),
        'popular_items'              => __( 'Popular Service Categories',                           'civijobs' ),
        'all_items'                  => __( 'All Service Categories',                               'civijobs' ),
        'parent_item'                => __( 'Parent Category',                                      'civijobs' ),
        'parent_item_colon'          => __( 'Parent Category:',                                     'civijobs' ),
        'edit_item'                  => __( 'Edit Service Category',                                'civijobs' ),
        'update_item'                => __( 'Update Service Category',                              'civijobs' ),
        'add_new_item'               => __( 'Add New Service Category',                             'civijobs' ),
        'new_item_name'              => __( 'New Service Category Name',                            'civijobs' ),
        'not_found'                  => __( 'No service categories found.',                         'civijobs' ),
        'menu_name'                  => __( 'Service Categories',                                   'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Service Categories',                    'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'show_tagcloud'     => false,
        'query_var'         => true,
        'rewrite'           => [
            'slug'         => apply_filters( 'civijobs_service_category_slug', 'service-category' ),
            'with_front'   => false,
            'hierarchical' => true,
        ],
    ];

    register_taxonomy( 'service_category', [ 'civi_service' ], apply_filters( 'civijobs_tax_service_category_args', $args ) );
}

// ============================================================
// resume_skills — Candidate Skills (flat tags)
// ============================================================

function civijobs_register_tax_resume_skills(): void {
    $labels = [
        'name'                       => _x( 'Skills',                    'taxonomy general name',  'civijobs' ),
        'singular_name'              => _x( 'Skill',                     'taxonomy singular name', 'civijobs' ),
        'search_items'               => __( 'Search Skills',                                        'civijobs' ),
        'popular_items'              => __( 'Popular Skills',                                       'civijobs' ),
        'all_items'                  => __( 'All Skills',                                           'civijobs' ),
        'edit_item'                  => __( 'Edit Skill',                                           'civijobs' ),
        'update_item'                => __( 'Update Skill',                                         'civijobs' ),
        'add_new_item'               => __( 'Add New Skill',                                        'civijobs' ),
        'new_item_name'              => __( 'New Skill Name',                                       'civijobs' ),
        'separate_items_with_commas' => __( 'Separate skills with commas',                          'civijobs' ),
        'add_or_remove_items'        => __( 'Add or remove skills',                                 'civijobs' ),
        'choose_from_most_used'      => __( 'Choose from the most used skills',                     'civijobs' ),
        'not_found'                  => __( 'No skills found.',                                     'civijobs' ),
        'menu_name'                  => __( 'Skills',                                               'civijobs' ),
        'back_to_items'              => __( '&larr; Back to Skills',                                'civijobs' ),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => false,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'show_tagcloud'     => true,
        'query_var'         => true,
        'rewrite'           => [
            'slug'       => apply_filters( 'civijobs_resume_skills_slug', 'skills' ),
            'with_front' => false,
        ],
    ];

    register_taxonomy( 'resume_skills', [ 'civi_resume', 'civi_service' ], apply_filters( 'civijobs_tax_resume_skills_args', $args ) );
}

// ============================================================
// Default Term Seeding
// ============================================================

/**
 * Insert default taxonomy terms on theme activation.
 * Uses get_option guard so it only runs once.
 */
function civijobs_seed_default_terms(): void {
    if ( get_option( 'civijobs_terms_seeded' ) ) {
        return;
    }

    // --- Job Categories ---
    $job_categories = [
        'Technology'          => [ 'Web Development', 'Mobile Development', 'Data Science', 'DevOps', 'Cybersecurity', 'AI & Machine Learning', 'IT Support', 'QA & Testing', 'Blockchain' ],
        'Marketing'           => [ 'Digital Marketing', 'Content Marketing', 'SEO / SEM', 'Social Media', 'Email Marketing', 'Brand Management', 'Growth Hacking', 'Affiliate Marketing' ],
        'Design'              => [ 'Graphic Design', 'UI/UX Design', 'Product Design', 'Motion Graphics', 'Illustration', '3D Design', 'Video Editing', 'Photography' ],
        'Finance'             => [ 'Accounting', 'Financial Analysis', 'Investment Banking', 'Tax & Audit', 'Payroll', 'Venture Capital', 'Insurance', 'Cryptocurrency' ],
        'Healthcare'          => [ 'Nursing', 'Medical Research', 'Pharmacy', 'Mental Health', 'Telemedicine', 'Dentistry', 'Physical Therapy', 'Veterinary' ],
        'Education'           => [ 'Teaching', 'E-Learning', 'Tutoring', 'Curriculum Design', 'School Administration', 'Corporate Training', 'Special Education' ],
        'Engineering'         => [ 'Mechanical Engineering', 'Civil Engineering', 'Electrical Engineering', 'Chemical Engineering', 'Aerospace Engineering', 'Robotics' ],
        'Sales'               => [ 'Inside Sales', 'Outside Sales', 'Account Management', 'Business Development', 'Sales Engineering', 'Retail Sales' ],
        'Human Resources'     => [ 'Recruitment', 'HR Management', 'Talent Acquisition', 'Compensation & Benefits', 'Employee Relations', 'Organizational Development' ],
        'Legal'               => [ 'Corporate Law', 'Criminal Law', 'Intellectual Property', 'Compliance', 'Paralegal', 'Contract Management' ],
        'Customer Service'    => [ 'Customer Support', 'Technical Support', 'Customer Success', 'Chat Support', 'Call Center' ],
        'Operations'          => [ 'Project Management', 'Supply Chain', 'Logistics', 'Warehouse', 'Quality Control', 'Procurement' ],
        'Writing'             => [ 'Copywriting', 'Technical Writing', 'Content Writing', 'Proofreading & Editing', 'Translation', 'Journalism' ],
        'Administrative'      => [ 'Executive Assistant', 'Office Manager', 'Data Entry', 'Virtual Assistant', 'Receptionist' ],
        'Construction'        => [ 'Architecture', 'Interior Design', 'Plumbing', 'Electrical', 'HVAC', 'Carpentry' ],
        'Hospitality'         => [ 'Hotel Management', 'Event Planning', 'Food & Beverage', 'Travel & Tourism', 'Catering' ],
        'Science'             => [ 'Biology', 'Chemistry', 'Physics', 'Environmental Science', 'Geology', 'Research' ],
        'Arts & Entertainment' => [ 'Music', 'Acting', 'Film & TV', 'Writing & Poetry', 'Dance', 'Gaming' ],
    ];

    foreach ( $job_categories as $parent_name => $children ) {
        $parent = wp_insert_term( $parent_name, 'job_category' );
        if ( is_wp_error( $parent ) ) {
            $parent = get_term_by( 'name', $parent_name, 'job_category' );
            $parent_id = $parent ? $parent->term_id : 0;
        } else {
            $parent_id = $parent['term_id'];
        }

        if ( $parent_id ) {
            foreach ( $children as $child_name ) {
                $existing = get_term_by( 'name', $child_name, 'job_category' );
                if ( ! $existing ) {
                    wp_insert_term( $child_name, 'job_category', [ 'parent' => $parent_id ] );
                }
            }
        }
    }

    // --- Job Types ---
    $job_types = [
        [ 'name' => 'Full-Time',   'slug' => 'full-time',   'description' => __( 'Full-time employment (40+ hours/week)', 'civijobs' ),   'color' => '#22c55e' ],
        [ 'name' => 'Part-Time',   'slug' => 'part-time',   'description' => __( 'Part-time employment (less than 30 hours/week)', 'civijobs' ), 'color' => '#3b82f6' ],
        [ 'name' => 'Contract',    'slug' => 'contract',    'description' => __( 'Fixed-term contract position', 'civijobs' ),             'color' => '#f59e0b' ],
        [ 'name' => 'Freelance',   'slug' => 'freelance',   'description' => __( 'Independent freelance work', 'civijobs' ),              'color' => '#8b5cf6' ],
        [ 'name' => 'Internship',  'slug' => 'internship',  'description' => __( 'Internship or work-placement program', 'civijobs' ),   'color' => '#ec4899' ],
        [ 'name' => 'Remote',      'slug' => 'remote',      'description' => __( 'Work from anywhere remotely', 'civijobs' ),             'color' => '#0ea5e9' ],
        [ 'name' => 'Temporary',   'slug' => 'temporary',   'description' => __( 'Short-term or seasonal position', 'civijobs' ),        'color' => '#ef4444' ],
        [ 'name' => 'Volunteer',   'slug' => 'volunteer',   'description' => __( 'Unpaid volunteer opportunity', 'civijobs' ),            'color' => '#64748b' ],
    ];

    foreach ( $job_types as $type ) {
        $existing = get_term_by( 'slug', $type['slug'], 'job_type' );
        if ( ! $existing ) {
            $term = wp_insert_term( $type['name'], 'job_type', [
                'slug'        => $type['slug'],
                'description' => $type['description'],
            ] );
            if ( ! is_wp_error( $term ) ) {
                add_term_meta( $term['term_id'], '_type_color', $type['color'] );
            }
        }
    }

    // --- Experience Levels ---
    $experience_levels = [
        [ 'name' => 'Internship',     'slug' => 'internship-level',  'description' => __( 'Student or recent graduate',       'civijobs' ), 'order' => 1 ],
        [ 'name' => 'Entry Level',    'slug' => 'entry-level',       'description' => __( '0–2 years of experience',           'civijobs' ), 'order' => 2 ],
        [ 'name' => 'Mid Level',      'slug' => 'mid-level',         'description' => __( '3–5 years of experience',           'civijobs' ), 'order' => 3 ],
        [ 'name' => 'Senior Level',   'slug' => 'senior-level',      'description' => __( '6–10 years of experience',          'civijobs' ), 'order' => 4 ],
        [ 'name' => 'Lead',           'slug' => 'lead',              'description' => __( 'Team lead or tech lead role',        'civijobs' ), 'order' => 5 ],
        [ 'name' => 'Manager',        'slug' => 'manager',           'description' => __( 'Managerial position',                'civijobs' ), 'order' => 6 ],
        [ 'name' => 'Director',       'slug' => 'director',          'description' => __( 'Director or department head',        'civijobs' ), 'order' => 7 ],
        [ 'name' => 'VP',             'slug' => 'vp',                'description' => __( 'Vice President level',               'civijobs' ), 'order' => 8 ],
        [ 'name' => 'Executive',      'slug' => 'executive',         'description' => __( 'C-Suite or executive leadership',    'civijobs' ), 'order' => 9 ],
    ];

    foreach ( $experience_levels as $level ) {
        $existing = get_term_by( 'slug', $level['slug'], 'job_experience' );
        if ( ! $existing ) {
            $term = wp_insert_term( $level['name'], 'job_experience', [
                'slug'        => $level['slug'],
                'description' => $level['description'],
            ] );
            if ( ! is_wp_error( $term ) ) {
                add_term_meta( $term['term_id'], '_experience_order', $level['order'] );
            }
        }
    }

    // --- Salary Ranges ---
    $salary_ranges = [
        [ 'name' => 'Under $30K',      'slug' => 'under-30k',       'min' => 0,       'max' => 30000  ],
        [ 'name' => '$30K – $50K',     'slug' => '30k-50k',         'min' => 30000,   'max' => 50000  ],
        [ 'name' => '$50K – $80K',     'slug' => '50k-80k',         'min' => 50000,   'max' => 80000  ],
        [ 'name' => '$80K – $120K',    'slug' => '80k-120k',        'min' => 80000,   'max' => 120000 ],
        [ 'name' => '$120K – $180K',   'slug' => '120k-180k',       'min' => 120000,  'max' => 180000 ],
        [ 'name' => 'Over $180K',      'slug' => 'over-180k',       'min' => 180000,  'max' => 999999 ],
    ];

    foreach ( $salary_ranges as $range ) {
        $existing = get_term_by( 'slug', $range['slug'], 'job_salary' );
        if ( ! $existing ) {
            $term = wp_insert_term( $range['name'], 'job_salary', [ 'slug' => $range['slug'] ] );
            if ( ! is_wp_error( $term ) ) {
                add_term_meta( $term['term_id'], '_salary_min', $range['min'] );
                add_term_meta( $term['term_id'], '_salary_max', $range['max'] );
            }
        }
    }

    // --- Company Industries (mirrors job_category top-level) ---
    $industries = array_keys( $job_categories );
    foreach ( $industries as $industry_name ) {
        $existing = get_term_by( 'name', $industry_name, 'company_industry' );
        if ( ! $existing ) {
            wp_insert_term( $industry_name, 'company_industry' );
        }
    }

    // --- Company Sizes ---
    $company_sizes = [
        [ 'name' => '1–10 employees',        'slug' => '1-10'         ],
        [ 'name' => '11–50 employees',       'slug' => '11-50'        ],
        [ 'name' => '51–200 employees',      'slug' => '51-200'       ],
        [ 'name' => '201–500 employees',     'slug' => '201-500'      ],
        [ 'name' => '501–1,000 employees',   'slug' => '501-1000'     ],
        [ 'name' => '1,001–5,000 employees', 'slug' => '1001-5000'    ],
        [ 'name' => '5,001–10,000 employees','slug' => '5001-10000'   ],
        [ 'name' => '10,000+ employees',     'slug' => '10000-plus'   ],
    ];

    foreach ( $company_sizes as $size ) {
        $existing = get_term_by( 'slug', $size['slug'], 'company_size' );
        if ( ! $existing ) {
            wp_insert_term( $size['name'], 'company_size', [ 'slug' => $size['slug'] ] );
        }
    }

    // --- Service Categories (mirrors job_category) ---
    $service_cats = [
        'Web Development'   => [ 'WordPress', 'React & Next.js', 'E-Commerce', 'Landing Pages', 'Shopify', 'Webflow' ],
        'Mobile Apps'       => [ 'iOS Development', 'Android Development', 'React Native', 'Flutter' ],
        'Design'            => [ 'Logo & Branding', 'UI/UX Design', 'Social Media Graphics', 'Print Design', 'Video Editing' ],
        'Writing'           => [ 'Blog Posts', 'Copywriting', 'Technical Writing', 'Translation', 'Proofreading' ],
        'Marketing'         => [ 'SEO', 'PPC Advertising', 'Social Media Management', 'Email Campaigns', 'Influencer Marketing' ],
        'Finance'           => [ 'Bookkeeping', 'Tax Preparation', 'Financial Modeling', 'Business Valuation' ],
        'Business'          => [ 'Business Planning', 'Market Research', 'Legal Services', 'HR Consulting' ],
        'Data'              => [ 'Data Analysis', 'Machine Learning', 'Data Visualization', 'Web Scraping' ],
        'Music & Audio'     => [ 'Music Production', 'Voice Over', 'Podcast Editing', 'Sound Design' ],
        'Video & Animation' => [ 'Animation', 'Whiteboard Videos', 'Explainer Videos', 'Video Ads' ],
    ];

    foreach ( $service_cats as $parent_name => $children ) {
        $parent = wp_insert_term( $parent_name, 'service_category' );
        if ( is_wp_error( $parent ) ) {
            $parent_obj = get_term_by( 'name', $parent_name, 'service_category' );
            $parent_id  = $parent_obj ? $parent_obj->term_id : 0;
        } else {
            $parent_id = $parent['term_id'];
        }

        if ( $parent_id ) {
            foreach ( $children as $child_name ) {
                $existing = get_term_by( 'name', $child_name, 'service_category' );
                if ( ! $existing ) {
                    wp_insert_term( $child_name, 'service_category', [ 'parent' => $parent_id ] );
                }
            }
        }
    }

    // --- Common Skills ---
    $skills = [
        'JavaScript', 'TypeScript', 'Python', 'PHP', 'Java', 'C#', 'C++', 'Ruby', 'Go', 'Rust', 'Swift', 'Kotlin',
        'React', 'Vue.js', 'Angular', 'Next.js', 'Nuxt.js', 'Node.js', 'Express.js', 'Django', 'Laravel',
        'WordPress', 'Shopify', 'WooCommerce', 'Magento',
        'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Elasticsearch',
        'AWS', 'Google Cloud', 'Azure', 'Docker', 'Kubernetes', 'Terraform',
        'Git', 'GitHub', 'GitLab', 'CI/CD', 'Agile', 'Scrum', 'JIRA',
        'Figma', 'Adobe Photoshop', 'Adobe Illustrator', 'Sketch', 'InVision',
        'SEO', 'Google Analytics', 'Google Ads', 'Facebook Ads', 'HubSpot', 'Salesforce',
        'Microsoft Excel', 'Microsoft Word', 'Google Workspace', 'Slack', 'Notion',
        'Communication', 'Leadership', 'Problem Solving', 'Critical Thinking', 'Time Management',
        'Project Management', 'Team Management', 'Customer Service', 'Sales', 'Negotiation',
        'Machine Learning', 'Deep Learning', 'Data Analysis', 'Tableau', 'Power BI', 'R', 'MATLAB',
    ];

    foreach ( $skills as $skill_name ) {
        $slug     = sanitize_title( $skill_name );
        $existing = get_term_by( 'slug', $slug, 'resume_skills' );
        if ( ! $existing ) {
            wp_insert_term( $skill_name, 'resume_skills', [ 'slug' => $slug ] );
        }
    }

    // Mark as done
    update_option( 'civijobs_terms_seeded', CIVIJOBS_VERSION );
}
add_action( 'after_switch_theme', 'civijobs_seed_default_terms' );

// ============================================================
// Taxonomy Term Meta Fields
// ============================================================

/**
 * Add custom fields to job_category term add/edit forms.
 */
function civijobs_job_category_add_form_fields(): void {
    ?>
    <div class="form-field">
        <label for="term_icon"><?php esc_html_e( 'Icon (dashicon class or emoji)', 'civijobs' ); ?></label>
        <input type="text" id="term_icon" name="term_icon" value="" placeholder="dashicons-portfolio or 💼">
        <p class="description"><?php esc_html_e( 'Used in category cards on the front-end.', 'civijobs' ); ?></p>
    </div>
    <div class="form-field">
        <label for="term_color"><?php esc_html_e( 'Color', 'civijobs' ); ?></label>
        <input type="color" id="term_color" name="term_color" value="#4f46e5">
    </div>
    <?php
}
add_action( 'job_category_add_form_fields', 'civijobs_job_category_add_form_fields' );

/**
 * Add custom fields to job_category term edit form.
 *
 * @param \WP_Term $term Current term object.
 */
function civijobs_job_category_edit_form_fields( \WP_Term $term ): void {
    $icon  = get_term_meta( $term->term_id, '_category_icon', true );
    $color = get_term_meta( $term->term_id, '_category_color', true ) ?: '#4f46e5';
    ?>
    <tr class="form-field">
        <th><label for="term_icon"><?php esc_html_e( 'Icon', 'civijobs' ); ?></label></th>
        <td>
            <input type="text" id="term_icon" name="term_icon" value="<?php echo esc_attr( $icon ); ?>" placeholder="dashicons-portfolio or 💼">
            <p class="description"><?php esc_html_e( 'Used in category cards on the front-end.', 'civijobs' ); ?></p>
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="term_color"><?php esc_html_e( 'Color', 'civijobs' ); ?></label></th>
        <td>
            <input type="color" id="term_color" name="term_color" value="<?php echo esc_attr( $color ); ?>">
        </td>
    </tr>
    <?php
}
add_action( 'job_category_edit_form_fields', 'civijobs_job_category_edit_form_fields' );

/**
 * Save custom term meta for job_category.
 *
 * @param int $term_id Term ID.
 */
function civijobs_save_job_category_meta( int $term_id ): void {
    if ( isset( $_POST['term_icon'] ) ) { // phpcs:ignore
        update_term_meta( $term_id, '_category_icon', sanitize_text_field( wp_unslash( $_POST['term_icon'] ) ) ); // phpcs:ignore
    }
    if ( isset( $_POST['term_color'] ) ) { // phpcs:ignore
        $color = sanitize_hex_color( wp_unslash( $_POST['term_color'] ) ); // phpcs:ignore
        if ( $color ) {
            update_term_meta( $term_id, '_category_color', $color );
        }
    }
}
add_action( 'created_job_category', 'civijobs_save_job_category_meta' );
add_action( 'edited_job_category',  'civijobs_save_job_category_meta' );

// ============================================================
// Helper: Get Terms as Select Options
// ============================================================

/**
 * Get taxonomy terms formatted as an associative array for <select> fields.
 *
 * @param  string $taxonomy  Taxonomy name.
 * @param  bool   $with_blank Whether to prepend a blank option.
 * @return array<int|string, string>
 */
function civijobs_get_term_options( string $taxonomy, bool $with_blank = true ): array {
    $terms = get_terms( [
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );

    $options = [];

    if ( $with_blank ) {
        $options[''] = __( 'Any', 'civijobs' );
    }

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return $options;
    }

    foreach ( $terms as $term ) {
        $options[ $term->term_id ] = $term->name;
    }

    return $options;
}

/**
 * Get hierarchical terms as a flat indented list for <select> fields.
 *
 * @param  string $taxonomy  Taxonomy name.
 * @param  int    $parent_id Parent term ID (0 for root).
 * @param  int    $depth     Current recursion depth.
 * @param  bool   $with_blank Whether to prepend a blank option at root.
 * @return array<int|string, string>
 */
function civijobs_get_hierarchical_term_options( string $taxonomy, int $parent_id = 0, int $depth = 0, bool $with_blank = true ): array {
    $options = [];

    if ( 0 === $depth && $with_blank ) {
        $options[''] = __( 'All Categories', 'civijobs' );
    }

    $terms = get_terms( [
        'taxonomy'   => $taxonomy,
        'parent'     => $parent_id,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return $options;
    }

    $prefix = str_repeat( '&nbsp;&nbsp;&nbsp;', $depth );

    foreach ( $terms as $term ) {
        $options[ $term->term_id ] = $prefix . ( $depth > 0 ? '&mdash; ' : '' ) . $term->name;
        // Recurse for children
        $children = civijobs_get_hierarchical_term_options( $taxonomy, $term->term_id, $depth + 1, false );
        if ( ! empty( $children ) ) {
            $options = array_replace( $options, $children );
        }
    }

    return $options;
}
