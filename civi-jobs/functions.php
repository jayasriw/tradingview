<?php
/**
 * CiviJobs Theme Functions
 *
 * @package CiviJobs
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================
// Theme Constants
// ============================================================

define( 'CIVIJOBS_VERSION',   '1.0.0' );
define( 'CIVIJOBS_DIR',       get_template_directory() );
define( 'CIVIJOBS_URL',       get_template_directory_uri() );
define( 'CIVIJOBS_INC',       CIVIJOBS_DIR . '/inc' );
define( 'CIVIJOBS_ASSETS',    CIVIJOBS_URL . '/assets' );
define( 'CIVIJOBS_TEXT_DOMAIN', 'civijobs' );

// Minimum PHP & WP versions
define( 'CIVIJOBS_MIN_PHP', '8.0' );
define( 'CIVIJOBS_MIN_WP',  '6.0' );

// ============================================================
// Compatibility Check
// ============================================================

/**
 * Check PHP and WP version compatibility; show admin notices if below minimum.
 */
function civijobs_compatibility_check(): void {
    if ( version_compare( PHP_VERSION, CIVIJOBS_MIN_PHP, '<' ) ) {
        add_action( 'admin_notices', function () {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                sprintf(
                    /* translators: 1: current PHP version, 2: minimum required */
                    esc_html__( 'CiviJobs requires PHP %2$s or higher. You are running PHP %1$s.', 'civijobs' ),
                    esc_html( PHP_VERSION ),
                    esc_html( CIVIJOBS_MIN_PHP )
                )
            );
        } );
        return;
    }

    if ( version_compare( $GLOBALS['wp_version'], CIVIJOBS_MIN_WP, '<' ) ) {
        add_action( 'admin_notices', function () {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                sprintf(
                    /* translators: 1: current WP version, 2: minimum required */
                    esc_html__( 'CiviJobs requires WordPress %2$s or higher. You are running WordPress %1$s.', 'civijobs' ),
                    esc_html( $GLOBALS['wp_version'] ),
                    esc_html( CIVIJOBS_MIN_WP )
                )
            );
        } );
        return;
    }

    civijobs_load_includes();
}
add_action( 'after_setup_theme', 'civijobs_compatibility_check', 0 );

// ============================================================
// Include Files
// ============================================================

/**
 * Load all theme include files in the correct order.
 */
function civijobs_load_includes(): void {
    $includes = [
        '/database.php',
        '/post-types.php',
        '/taxonomies.php',
        '/roles.php',
        '/helpers.php',
        '/enqueue.php',
        '/email-notifications.php',
        '/notifications.php',
        '/messaging.php',
        '/applications.php',
        '/meetings.php',
        '/packages.php',
        '/reviews.php',
        '/wallet.php',
        '/job-alerts.php',
    ];

    foreach ( $includes as $file ) {
        $path = CIVIJOBS_INC . $file;
        if ( file_exists( $path ) ) {
            require_once $path;
        } else {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions
                trigger_error(
                    sprintf( 'CiviJobs: Missing include file: %s', esc_html( $path ) ),
                    E_USER_WARNING
                );
            }
        }
    }

    // Load optional integration files
    $optional = [
        '/woocommerce.php',
        '/ajax-handlers.php',
        '/cron.php',
        '/shortcodes.php',
        '/widgets.php',
        '/admin-settings.php',
    ];

    foreach ( $optional as $file ) {
        $path = CIVIJOBS_INC . $file;
        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
}

// ============================================================
// Theme Setup
// ============================================================

/**
 * Set up theme defaults and register support for various WordPress features.
 */
function civijobs_theme_setup(): void {
    // Load theme text domain
    load_theme_textdomain( CIVIJOBS_TEXT_DOMAIN, CIVIJOBS_DIR . '/languages' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for post thumbnails
    add_theme_support( 'post-thumbnails' );

    // HTML5 markup support
    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ] );

    // Automatic feed links
    add_theme_support( 'automatic-feed-links' );

    // Selective refresh for widgets in the customizer
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Custom logo
    add_theme_support( 'custom-logo', [
        'height'               => 60,
        'width'                => 200,
        'flex-height'          => true,
        'flex-width'           => true,
        'header-text'          => [ 'site-title', 'site-description' ],
        'unlink-homepage-logo' => false,
    ] );

    // Custom header
    add_theme_support( 'custom-header', [
        'default-image'          => '',
        'random-default'         => false,
        'width'                  => 1920,
        'height'                 => 600,
        'flex-height'            => true,
        'flex-width'             => true,
        'uploads'                => true,
        'wp-head-callback'       => '__return_false',
        'admin-head-callback'    => '__return_false',
        'admin-preview-callback' => '__return_false',
    ] );

    // Custom background
    add_theme_support( 'custom-background', [
        'default-color' => 'f8fafc',
        'default-image' => '',
    ] );

    // Post formats
    add_theme_support( 'post-formats', [ 'image', 'video', 'gallery', 'link', 'quote' ] );

    // Wide / full-width block alignment
    add_theme_support( 'align-wide' );

    // Block editor color palette
    add_theme_support( 'editor-color-palette', [
        [
            'name'  => __( 'Primary', 'civijobs' ),
            'slug'  => 'primary',
            'color' => '#4f46e5',
        ],
        [
            'name'  => __( 'Primary Dark', 'civijobs' ),
            'slug'  => 'primary-dark',
            'color' => '#3730a3',
        ],
        [
            'name'  => __( 'Accent', 'civijobs' ),
            'slug'  => 'accent',
            'color' => '#0ea5e9',
        ],
        [
            'name'  => __( 'Success', 'civijobs' ),
            'slug'  => 'success',
            'color' => '#22c55e',
        ],
        [
            'name'  => __( 'Warning', 'civijobs' ),
            'slug'  => 'warning',
            'color' => '#f59e0b',
        ],
        [
            'name'  => __( 'Danger', 'civijobs' ),
            'slug'  => 'danger',
            'color' => '#ef4444',
        ],
        [
            'name'  => __( 'Dark', 'civijobs' ),
            'slug'  => 'dark',
            'color' => '#0f172a',
        ],
        [
            'name'  => __( 'Gray', 'civijobs' ),
            'slug'  => 'gray',
            'color' => '#64748b',
        ],
        [
            'name'  => __( 'Light', 'civijobs' ),
            'slug'  => 'light',
            'color' => '#f8fafc',
        ],
    ] );

    // Block editor font sizes
    add_theme_support( 'editor-font-sizes', [
        [ 'name' => __( 'Small',        'civijobs' ), 'slug' => 'small',        'size' => 13 ],
        [ 'name' => __( 'Normal',       'civijobs' ), 'slug' => 'normal',       'size' => 16 ],
        [ 'name' => __( 'Medium',       'civijobs' ), 'slug' => 'medium',       'size' => 18 ],
        [ 'name' => __( 'Large',        'civijobs' ), 'slug' => 'large',        'size' => 24 ],
        [ 'name' => __( 'Extra Large',  'civijobs' ), 'slug' => 'extra-large',  'size' => 32 ],
        [ 'name' => __( 'Huge',         'civijobs' ), 'slug' => 'huge',         'size' => 48 ],
    ] );

    // Responsive embeds
    add_theme_support( 'responsive-embeds' );

    // Disable core block patterns
    remove_theme_support( 'core-block-patterns' );

    // Register navigation menus
    register_nav_menus( [
        'primary'    => __( 'Primary Navigation',   'civijobs' ),
        'footer'     => __( 'Footer Navigation',    'civijobs' ),
        'dashboard'  => __( 'Dashboard Navigation', 'civijobs' ),
        'footer-col2' => __( 'Footer Column 2',     'civijobs' ),
        'footer-col3' => __( 'Footer Column 3',     'civijobs' ),
        'footer-col4' => __( 'Footer Column 4',     'civijobs' ),
    ] );
}
add_action( 'after_setup_theme', 'civijobs_theme_setup' );

// ============================================================
// Image Sizes
// ============================================================

/**
 * Register custom image sizes for the theme.
 */
function civijobs_add_image_sizes(): void {
    // Job listing thumbnail - used in job cards
    add_image_size( 'job-thumb', 300, 200, true );

    // Company logo - square crop
    add_image_size( 'company-logo', 120, 120, true );

    // Company banner / hero
    add_image_size( 'company-banner', 1200, 400, true );

    // Candidate / user avatar
    add_image_size( 'candidate-avatar', 200, 200, true );

    // Candidate avatar small (for cards / lists)
    add_image_size( 'candidate-avatar-sm', 80, 80, true );

    // Service / package image
    add_image_size( 'service-thumb', 400, 250, true );

    // Blog / news featured image
    add_image_size( 'blog-thumb', 800, 450, true );

    // Blog card thumbnail
    add_image_size( 'blog-card', 400, 225, true );
}
add_action( 'after_setup_theme', 'civijobs_add_image_sizes' );

/**
 * Add custom image size names to the media library.
 *
 * @param  array<string, string> $sizes Existing image size names.
 * @return array<string, string>
 */
function civijobs_custom_image_size_names( array $sizes ): array {
    return array_merge( $sizes, [
        'job-thumb'          => __( 'Job Thumbnail',       'civijobs' ),
        'company-logo'       => __( 'Company Logo',        'civijobs' ),
        'company-banner'     => __( 'Company Banner',      'civijobs' ),
        'candidate-avatar'   => __( 'Candidate Avatar',    'civijobs' ),
        'candidate-avatar-sm'=> __( 'Candidate Avatar (Small)', 'civijobs' ),
        'service-thumb'      => __( 'Service Thumbnail',   'civijobs' ),
        'blog-thumb'         => __( 'Blog Featured Image', 'civijobs' ),
        'blog-card'          => __( 'Blog Card',           'civijobs' ),
    ] );
}
add_filter( 'image_size_names_choose', 'civijobs_custom_image_size_names' );

// ============================================================
// WooCommerce Integration
// ============================================================

/**
 * Declare WooCommerce compatibility.
 */
function civijobs_woocommerce_support(): void {
    add_theme_support( 'woocommerce', [
        'thumbnail_image_width'         => 300,
        'single_image_width'            => 600,
        'product_grid'                  => [
            'default_columns' => 3,
            'default_rows'    => 4,
            'min_columns'     => 1,
            'max_columns'     => 6,
            'min_rows'        => 1,
        ],
    ] );

    // Gallery features
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'civijobs_woocommerce_support' );

/**
 * Remove default WooCommerce styles.
 */
function civijobs_dequeue_woocommerce_styles( array $enqueue_styles ): array {
    unset( $enqueue_styles['woocommerce-general'] );
    unset( $enqueue_styles['woocommerce-layout'] );
    unset( $enqueue_styles['woocommerce-smallscreen'] );
    return $enqueue_styles;
}
add_filter( 'woocommerce_enqueue_styles', 'civijobs_dequeue_woocommerce_styles' );

/**
 * Remove default WooCommerce wrappers.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );

/**
 * Add custom WooCommerce wrappers.
 */
function civijobs_woocommerce_wrapper_before(): void {
    echo '<main id="site-content" class="cj-woo-content cj-container"><div class="cj-woo-inner">';
}
add_action( 'woocommerce_before_main_content', 'civijobs_woocommerce_wrapper_before', 10 );

function civijobs_woocommerce_wrapper_after(): void {
    echo '</div></main>';
}
add_action( 'woocommerce_after_main_content', 'civijobs_woocommerce_wrapper_after', 10 );

// ============================================================
// Widgets
// ============================================================

/**
 * Register widget areas.
 */
function civijobs_register_widget_areas(): void {
    $widget_areas = [
        [
            'name'          => __( 'Sidebar', 'civijobs' ),
            'id'            => 'sidebar-1',
            'description'   => __( 'Main sidebar widget area.', 'civijobs' ),
        ],
        [
            'name'          => __( 'Job Listing Sidebar', 'civijobs' ),
            'id'            => 'sidebar-jobs',
            'description'   => __( 'Displayed on job listing pages.', 'civijobs' ),
        ],
        [
            'name'          => __( 'Dashboard Sidebar', 'civijobs' ),
            'id'            => 'sidebar-dashboard',
            'description'   => __( 'Displayed in the user dashboard.', 'civijobs' ),
        ],
        [
            'name'          => __( 'Footer Column 1', 'civijobs' ),
            'id'            => 'footer-1',
            'description'   => __( 'Footer column one widget area.', 'civijobs' ),
        ],
        [
            'name'          => __( 'Footer Column 2', 'civijobs' ),
            'id'            => 'footer-2',
            'description'   => __( 'Footer column two widget area.', 'civijobs' ),
        ],
        [
            'name'          => __( 'Footer Column 3', 'civijobs' ),
            'id'            => 'footer-3',
            'description'   => __( 'Footer column three widget area.', 'civijobs' ),
        ],
        [
            'name'          => __( 'Footer Column 4', 'civijobs' ),
            'id'            => 'footer-4',
            'description'   => __( 'Footer column four widget area.', 'civijobs' ),
        ],
    ];

    $defaults = [
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ];

    foreach ( $widget_areas as $area ) {
        register_sidebar( array_merge( $defaults, $area ) );
    }
}
add_action( 'widgets_init', 'civijobs_register_widget_areas' );

// ============================================================
// Content Width
// ============================================================

/**
 * Set the content width in pixels, based on theme layout.
 *
 * @global int $content_width
 */
function civijobs_content_width(): void {
    $GLOBALS['content_width'] = apply_filters( 'civijobs_content_width', 1200 );
}
add_action( 'after_setup_theme', 'civijobs_content_width', 0 );

// ============================================================
// Body Classes
// ============================================================

/**
 * Add custom body classes.
 *
 * @param  string[] $classes Existing body classes.
 * @return string[]
 */
function civijobs_body_classes( array $classes ): array {
    // Add a class if there is no sidebar
    if ( ! is_active_sidebar( 'sidebar-1' ) ) {
        $classes[] = 'no-sidebar';
    }

    // Add user role class
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();

        if ( function_exists( 'civijobs_get_user_role' ) ) {
            $role      = civijobs_get_user_role( $user_id );
            $classes[] = 'cj-logged-in';
            $classes[] = 'cj-role-' . sanitize_html_class( $role );
        }
    } else {
        $classes[] = 'cj-logged-out';
    }

    // Dashboard page
    if ( civijobs_is_dashboard_page() ) {
        $classes[] = 'cj-dashboard-page';
    }

    // Job archive
    if ( is_post_type_archive( 'civi_job' ) || is_tax( [ 'job_category', 'job_type', 'job_location', 'job_experience', 'job_salary' ] ) ) {
        $classes[] = 'cj-jobs-archive';
    }

    return $classes;
}
add_filter( 'body_class', 'civijobs_body_classes' );

// ============================================================
// Title Filter
// ============================================================

/**
 * Customize the document title separator.
 *
 * @param  array<string, string> $sep_data Title parts and separator.
 * @return array<string, string>
 */
function civijobs_document_title_separator( array $sep_data ): array {
    $sep_data['separator'] = '|';
    return $sep_data;
}
add_filter( 'document_title_separator', 'civijobs_document_title_separator' );

// ============================================================
// Excerpt
// ============================================================

/**
 * Change the excerpt length.
 *
 * @return int
 */
function civijobs_excerpt_length(): int {
    return 30;
}
add_filter( 'excerpt_length', 'civijobs_excerpt_length', 999 );

/**
 * Replace the default excerpt "more" string.
 *
 * @return string
 */
function civijobs_excerpt_more(): string {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'civijobs_excerpt_more' );

// ============================================================
// Head Cleanup
// ============================================================

/**
 * Remove unnecessary items from <head>.
 */
function civijobs_head_cleanup(): void {
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
}
add_action( 'init', 'civijobs_head_cleanup' );

// ============================================================
// Security
// ============================================================

/**
 * Remove WordPress version from public output.
 *
 * @return string
 */
function civijobs_remove_version(): string {
    return '';
}
add_filter( 'the_generator', 'civijobs_remove_version' );

/**
 * Disable XML-RPC.
 *
 * @return false
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Add security headers.
 */
function civijobs_security_headers(): void {
    if ( ! is_admin() ) {
        header( 'X-Content-Type-Options: nosniff' );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'Referrer-Policy: strict-origin-when-cross-origin' );
        header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(self)' );
    }
}
add_action( 'send_headers', 'civijobs_security_headers' );

// ============================================================
// Page Templates
// ============================================================

/**
 * Check if the current page is a dashboard page.
 *
 * @return bool
 */
function civijobs_is_dashboard_page(): bool {
    $dashboard_slugs = apply_filters( 'civijobs_dashboard_page_slugs', [
        'dashboard',
        'employer-dashboard',
        'candidate-dashboard',
        'post-job',
        'manage-jobs',
        'manage-applications',
        'company-profile',
        'candidate-profile',
        'messages',
        'notifications',
        'meetings',
        'job-alerts',
        'saved-jobs',
        'packages',
        'wallet',
    ] );

    if ( is_page() ) {
        $slug = get_post_field( 'post_name', get_the_ID() );
        if ( in_array( $slug, $dashboard_slugs, true ) ) {
            return true;
        }
    }

    return false;
}

// ============================================================
// Flush Rewrite Rules
// ============================================================

/**
 * Flush rewrite rules after the theme is activated.
 */
function civijobs_on_theme_activation(): void {
    // Register CPTs and taxonomies first
    if ( function_exists( 'civijobs_register_post_types' ) ) {
        civijobs_register_post_types();
    }
    if ( function_exists( 'civijobs_register_taxonomies' ) ) {
        civijobs_register_taxonomies();
    }
    if ( function_exists( 'civijobs_install' ) ) {
        civijobs_install();
    }

    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'civijobs_on_theme_activation' );

/**
 * Flush rewrite rules after the theme is deactivated.
 */
function civijobs_on_theme_deactivation(): void {
    flush_rewrite_rules();
}
add_action( 'switch_theme', 'civijobs_on_theme_deactivation' );

// ============================================================
// Admin Columns (Quick info in wp-admin)
// ============================================================

/**
 * Add custom admin columns to job list table.
 *
 * @param  array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function civijobs_job_admin_columns( array $columns ): array {
    $new = [];
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'title' === $key ) {
            $new['company']      = __( 'Company',      'civijobs' );
            $new['job_status']   = __( 'Status',       'civijobs' );
            $new['applications'] = __( 'Applications', 'civijobs' );
            $new['expires']      = __( 'Expires',      'civijobs' );
        }
    }
    return $new;
}
add_filter( 'manage_civi_job_posts_columns', 'civijobs_job_admin_columns' );

/**
 * Render custom admin column data for jobs.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function civijobs_job_admin_column_data( string $column, int $post_id ): void {
    switch ( $column ) {
        case 'company':
            $company_id = (int) get_post_meta( $post_id, '_job_company_id', true );
            if ( $company_id ) {
                printf(
                    '<a href="%s">%s</a>',
                    esc_url( get_edit_post_link( $company_id ) ),
                    esc_html( get_the_title( $company_id ) )
                );
            } else {
                echo '&mdash;';
            }
            break;

        case 'job_status':
            $status = get_post_meta( $post_id, '_job_status', true ) ?: 'active';
            $labels = [
                'active'   => '<span style="color:#15803d;">&#9679; Active</span>',
                'expired'  => '<span style="color:#ef4444;">&#9679; Expired</span>',
                'filled'   => '<span style="color:#f59e0b;">&#9679; Filled</span>',
                'draft'    => '<span style="color:#94a3b8;">&#9679; Draft</span>',
                'paused'   => '<span style="color:#64748b;">&#9679; Paused</span>',
            ];
            echo isset( $labels[ $status ] ) ? $labels[ $status ] : esc_html( ucfirst( $status ) ); // phpcs:ignore
            break;

        case 'applications':
            if ( function_exists( 'civijobs_get_application_count' ) ) {
                $count = civijobs_get_application_count( $post_id );
                printf(
                    '<a href="%s">%d</a>',
                    esc_url( admin_url( 'edit.php?post_type=civi_job&job_id=' . $post_id ) ),
                    (int) $count
                );
            }
            break;

        case 'expires':
            $date = get_post_meta( $post_id, '_job_deadline', true );
            echo $date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ) : '&mdash;'; // phpcs:ignore
            break;
    }
}
add_action( 'manage_civi_job_posts_custom_column', 'civijobs_job_admin_column_data', 10, 2 );

// ============================================================
// Theme Customizer
// ============================================================

/**
 * Add theme customizer settings.
 *
 * @param \WP_Customize_Manager $wp_customize Customizer instance.
 */
function civijobs_customize_register( \WP_Customize_Manager $wp_customize ): void {
    // Panel
    $wp_customize->add_panel( 'civijobs_panel', [
        'title'    => __( 'CiviJobs Settings', 'civijobs' ),
        'priority' => 30,
    ] );

    // ----- General Section -----
    $wp_customize->add_section( 'civijobs_general', [
        'title' => __( 'General', 'civijobs' ),
        'panel' => 'civijobs_panel',
    ] );

    // Primary color
    $wp_customize->add_setting( 'civijobs_primary_color', [
        'default'           => '#4f46e5',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'civijobs_primary_color', [
        'label'   => __( 'Primary Color', 'civijobs' ),
        'section' => 'civijobs_general',
    ] ) );

    // Jobs per page
    $wp_customize->add_setting( 'civijobs_jobs_per_page', [
        'default'           => 12,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'civijobs_jobs_per_page', [
        'label'   => __( 'Jobs per page', 'civijobs' ),
        'section' => 'civijobs_general',
        'type'    => 'number',
    ] );

    // ----- Hero Section -----
    $wp_customize->add_section( 'civijobs_hero', [
        'title' => __( 'Hero Section', 'civijobs' ),
        'panel' => 'civijobs_panel',
    ] );

    $wp_customize->add_setting( 'civijobs_hero_title', [
        'default'           => __( 'Find Your Dream Job Today', 'civijobs' ),
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'civijobs_hero_title', [
        'label'   => __( 'Hero Title', 'civijobs' ),
        'section' => 'civijobs_hero',
        'type'    => 'text',
    ] );

    $wp_customize->add_setting( 'civijobs_hero_subtitle', [
        'default'           => __( 'Connect with top employers and land your next opportunity.', 'civijobs' ),
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'civijobs_hero_subtitle', [
        'label'   => __( 'Hero Subtitle', 'civijobs' ),
        'section' => 'civijobs_hero',
        'type'    => 'textarea',
    ] );

    // ----- Job Settings Section -----
    $wp_customize->add_section( 'civijobs_jobs', [
        'title' => __( 'Job Settings', 'civijobs' ),
        'panel' => 'civijobs_panel',
    ] );

    $wp_customize->add_setting( 'civijobs_require_login_apply', [
        'default'           => '1',
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'civijobs_require_login_apply', [
        'label'   => __( 'Require login to apply for jobs', 'civijobs' ),
        'section' => 'civijobs_jobs',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 'civijobs_job_expiry_days', [
        'default'           => 30,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'civijobs_job_expiry_days', [
        'label'       => __( 'Default job expiry (days)', 'civijobs' ),
        'description' => __( 'How many days before a job listing expires.', 'civijobs' ),
        'section'     => 'civijobs_jobs',
        'type'        => 'number',
    ] );

    $wp_customize->add_setting( 'civijobs_moderate_jobs', [
        'default'           => '0',
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'civijobs_moderate_jobs', [
        'label'   => __( 'Moderate new job submissions', 'civijobs' ),
        'section' => 'civijobs_jobs',
        'type'    => 'checkbox',
    ] );

    // ----- Dashboard Section -----
    $wp_customize->add_section( 'civijobs_dashboard', [
        'title' => __( 'Dashboard Pages', 'civijobs' ),
        'panel' => 'civijobs_panel',
    ] );

    $pages = get_pages();
    $page_options = [ '' => __( '— Select Page —', 'civijobs' ) ];
    foreach ( $pages as $page ) {
        $page_options[ $page->ID ] = $page->post_title;
    }

    $dashboard_pages = [
        'civijobs_employer_dashboard_page' => __( 'Employer Dashboard Page', 'civijobs' ),
        'civijobs_candidate_dashboard_page' => __( 'Candidate Dashboard Page', 'civijobs' ),
        'civijobs_login_page'              => __( 'Login Page', 'civijobs' ),
        'civijobs_register_page'           => __( 'Register Page', 'civijobs' ),
        'civijobs_post_job_page'           => __( 'Post Job Page', 'civijobs' ),
        'civijobs_packages_page'           => __( 'Packages Page', 'civijobs' ),
    ];

    foreach ( $dashboard_pages as $setting_id => $label ) {
        $wp_customize->add_setting( $setting_id, [
            'default'           => '',
            'sanitize_callback' => 'absint',
        ] );
        $wp_customize->add_control( $setting_id, [
            'label'   => $label,
            'section' => 'civijobs_dashboard',
            'type'    => 'select',
            'choices' => $page_options,
        ] );
    }
}
add_action( 'customize_register', 'civijobs_customize_register' );

// ============================================================
// Dynamic CSS from Customizer
// ============================================================

/**
 * Output customizer CSS overrides in <head>.
 */
function civijobs_customizer_css(): void {
    $primary = get_theme_mod( 'civijobs_primary_color', '#4f46e5' );

    if ( '#4f46e5' === $primary ) {
        return; // Default color, no override needed
    }

    $primary_sanitized = sanitize_hex_color( $primary );
    if ( ! $primary_sanitized ) {
        return;
    }

    printf(
        '<style id="civijobs-customizer-css">:root { --cj-primary: %s; --cj-primary-600: %s; }</style>',
        esc_attr( $primary_sanitized ),
        esc_attr( $primary_sanitized )
    );
}
add_action( 'wp_head', 'civijobs_customizer_css' );

// ============================================================
// Deregister Unused Scripts
// ============================================================

/**
 * Deregister scripts/styles that are not needed on the frontend.
 */
function civijobs_deregister_scripts(): void {
    // Remove block library styles if not using blocks on this request
    if ( ! is_admin() && ! has_blocks() ) {
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
        wp_dequeue_style( 'wc-blocks-style' );
    }

    // Remove classic theme styles
    wp_dequeue_style( 'classic-theme-styles' );

    // Remove global styles (if not needed)
    wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'civijobs_deregister_scripts', 100 );

// ============================================================
// AJAX Nonce Refresh
// ============================================================

/**
 * Refresh the nonce for logged-in users via AJAX heartbeat.
 *
 * @param  array<string, mixed> $response  Heartbeat response.
 * @param  array<string, mixed> $data      Heartbeat request data.
 * @return array<string, mixed>
 */
function civijobs_heartbeat_nonce( array $response, array $data ): array {
    if ( ! empty( $data['civijobs_nonce_refresh'] ) ) {
        $response['civijobs_nonce'] = wp_create_nonce( 'civijobs_ajax' );
    }
    return $response;
}
add_filter( 'heartbeat_received', 'civijobs_heartbeat_nonce', 10, 2 );

// ============================================================
// Login/Register Redirect
// ============================================================

/**
 * Redirect users to their role-based dashboard after login.
 *
 * @param  string           $redirect_to  Default redirect URL.
 * @param  string           $requested    The requested redirect URL.
 * @param  \WP_User|\WP_Error $user       The logged-in user or error.
 * @return string
 */
function civijobs_login_redirect( string $redirect_to, string $requested, $user ): string {
    if ( ! ( $user instanceof \WP_User ) ) {
        return $redirect_to;
    }

    if ( ! empty( $requested ) && admin_url() !== $requested ) {
        return $redirect_to;
    }

    if ( in_array( 'administrator', $user->roles, true ) ) {
        return admin_url();
    }

    if ( in_array( 'civi_employer', $user->roles, true ) ) {
        $page_id = (int) get_theme_mod( 'civijobs_employer_dashboard_page' );
        if ( $page_id ) {
            return get_permalink( $page_id );
        }
    }

    if ( in_array( 'civi_candidate', $user->roles, true ) ) {
        $page_id = (int) get_theme_mod( 'civijobs_candidate_dashboard_page' );
        if ( $page_id ) {
            return get_permalink( $page_id );
        }
    }

    return home_url( '/dashboard/' );
}
add_filter( 'login_redirect', 'civijobs_login_redirect', 10, 3 );

// ============================================================
// Comment Form Defaults
// ============================================================

/**
 * Customize comment form fields.
 *
 * @param  array<string, mixed> $defaults Default comment form defaults.
 * @return array<string, mixed>
 */
function civijobs_comment_form_defaults( array $defaults ): array {
    $defaults['class_submit'] = 'cj-btn cj-btn-primary';
    $defaults['submit_button'] = '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />';
    return $defaults;
}
add_filter( 'comment_form_defaults', 'civijobs_comment_form_defaults' );

// ============================================================
// Search Filter — restrict searches to relevant post types
// ============================================================

/**
 * Filter search results to include relevant CPTs.
 *
 * @param  \WP_Query $query The WP_Query instance.
 */
function civijobs_search_filter( \WP_Query $query ): void {
    if ( $query->is_search() && ! is_admin() && $query->is_main_query() ) {
        $query->set( 'post_type', [ 'post', 'page', 'civi_job', 'civi_company', 'civi_resume', 'civi_service' ] );
    }
}
add_action( 'pre_get_posts', 'civijobs_search_filter' );

// ============================================================
// Job Archive Query
// ============================================================

/**
 * Modify the main query for job archives.
 *
 * @param  \WP_Query $query The WP_Query instance.
 */
function civijobs_job_archive_query( \WP_Query $query ): void {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( ! $query->is_post_type_archive( 'civi_job' ) && ! $query->is_tax( [ 'job_category', 'job_type', 'job_location', 'job_experience', 'job_salary' ] ) ) {
        return;
    }

    $per_page = (int) get_theme_mod( 'civijobs_jobs_per_page', 12 );
    $query->set( 'posts_per_page', $per_page );
    $query->set( 'orderby', 'meta_value date' );
    $query->set( 'meta_key', '_job_featured' );
    $query->set( 'order', 'DESC' );

    // Filter by active status
    $meta_query = [
        'relation' => 'OR',
        [
            'key'     => '_job_status',
            'value'   => 'active',
            'compare' => '=',
        ],
        [
            'key'     => '_job_status',
            'compare' => 'NOT EXISTS',
        ],
    ];

    // Deadline filter — only show non-expired jobs
    $today = date( 'Y-m-d' );
    $meta_query[] = [
        'relation' => 'OR',
        [
            'key'     => '_job_deadline',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ],
        [
            'key'     => '_job_deadline',
            'compare' => 'NOT EXISTS',
        ],
        [
            'key'   => '_job_deadline',
            'value' => '',
        ],
    ];

    $query->set( 'meta_query', $meta_query );

    // Apply GET filter params
    if ( ! empty( $_GET['orderby'] ) ) { // phpcs:ignore
        $allowed_orderby = [ 'date', 'title', 'salary', 'relevance' ];
        $orderby         = sanitize_key( $_GET['orderby'] ); // phpcs:ignore
        if ( in_array( $orderby, $allowed_orderby, true ) ) {
            if ( 'salary' === $orderby ) {
                $query->set( 'orderby', 'meta_value_num' );
                $query->set( 'meta_key', '_job_salary_max' );
            } else {
                $query->set( 'orderby', $orderby );
            }
        }
    }
}
add_action( 'pre_get_posts', 'civijobs_job_archive_query' );

// ============================================================
// Miscellaneous Helpers
// ============================================================

/**
 * Get a theme option with a default fallback.
 *
 * @param  string $option  Theme mod key.
 * @param  mixed  $default Default value.
 * @return mixed
 */
function civijobs_option( string $option, mixed $default = false ): mixed {
    return get_theme_mod( 'civijobs_' . $option, $default );
}

/**
 * Output a theme template part, with optional data context.
 *
 * @param  string               $slug Template slug.
 * @param  string               $name Optional template name variant.
 * @param  array<string, mixed> $args Optional data to pass to the template.
 */
function civijobs_template_part( string $slug, string $name = '', array $args = [] ): void {
    if ( ! empty( $args ) ) {
        // Make args available inside the template
        extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
    }
    get_template_part( $slug, $name, $args );
}

/**
 * Check if WooCommerce is active.
 *
 * @return bool
 */
function civijobs_woocommerce_active(): bool {
    return class_exists( 'WooCommerce' );
}

/**
 * Sanitize a textarea field keeping basic formatting.
 *
 * @param  string $input Raw input.
 * @return string
 */
function civijobs_sanitize_textarea( string $input ): string {
    return wp_kses( $input, [
        'a'      => [ 'href' => [], 'title' => [], 'target' => [] ],
        'br'     => [],
        'em'     => [],
        'strong' => [],
        'p'      => [],
        'ul'     => [],
        'ol'     => [],
        'li'     => [],
    ] );
}
