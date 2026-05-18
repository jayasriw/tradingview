<?php
/**
 * JobPortal Theme Functions
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

define( 'JOBPORTAL_VERSION', '1.0.0' );
define( 'JOBPORTAL_DIR',     get_template_directory() );
define( 'JOBPORTAL_URI',     get_template_directory_uri() );
define( 'JOBPORTAL_INC',     JOBPORTAL_DIR . '/inc/' );

add_action( 'after_setup_theme', function() {
    if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>' .
                 esc_html__( 'JobPortal requires PHP 8.0 or higher.', 'jobportal' ) .
                 '</p></div>';
        } );
    }
} );

$includes = [
    'database',
    'post-types',
    'taxonomies',
    'roles',
    'helpers',
    'enqueue',
    'ajax-handlers',
    'applications',
    'messaging',
    'notifications',
    'job-alerts',
    'meetings',
    'reviews',
    'packages',
    'wallet',
    'shortcodes',
    'email-notifications',
    'admin-settings',
    'woocommerce',
];

foreach ( $includes as $file ) {
    $path = JOBPORTAL_INC . $file . '.php';
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

add_action( 'after_setup_theme', function() {
    load_theme_textdomain( 'jobportal', JOBPORTAL_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    register_nav_menus( [
        'primary' => __( 'Primary Menu', 'jobportal' ),
        'footer'  => __( 'Footer Menu', 'jobportal' ),
    ] );
} );

add_action( 'after_setup_theme', function() {
    add_image_size( 'company-logo',     200, 200, true );
    add_image_size( 'candidate-avatar', 150, 150, true );
    add_image_size( 'cover-image',      1200, 400, true );
} );

add_action( 'widgets_init', function() {
    register_sidebar( [
        'name'          => __( 'Footer Widget Area', 'jobportal' ),
        'id'            => 'footer-1',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ] );
} );

add_filter( 'body_class', function( $classes ) {
    if ( is_user_logged_in() ) {
        $classes[] = 'user-logged-in';
        $classes[] = 'role-' . jobportal_get_user_role( get_current_user_id() );
    } else {
        $classes[] = 'user-guest';
    }
    return $classes;
} );

add_action( 'customize_register', function( $wp_customize ) {
    $wp_customize->add_section( 'jobportal_colors', [
        'title'    => __( 'JobPortal Colors', 'jobportal' ),
        'priority' => 30,
    ] );
    $color_settings = [
        [ 'jobportal_primary_color',   '#6366f1', __( 'Primary Color', 'jobportal' ) ],
        [ 'jobportal_secondary_color', '#f59e0b', __( 'Secondary Color', 'jobportal' ) ],
    ];
    foreach ( $color_settings as [ $id, $default, $label ] ) {
        $wp_customize->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage' ] );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, [ 'label' => $label, 'section' => 'jobportal_colors' ] ) );
    }
} );

add_action( 'wp_head', function() {
    $primary   = get_theme_mod( 'jobportal_primary_color',   '#6366f1' );
    $secondary = get_theme_mod( 'jobportal_secondary_color', '#f59e0b' );
    echo '<style>:root{--color-primary:' . esc_attr( $primary ) . ';--color-secondary:' . esc_attr( $secondary ) . ';}</style>';
}, 999 );

if ( ! function_exists( 'jobportal_get_avatar_url' ) ) {
    function jobportal_get_avatar_url( int $user_id, int $size = 80 ): string {
        $custom = get_user_meta( $user_id, 'civi_avatar', true );
        if ( $custom ) return esc_url( $custom );
        return esc_url( get_avatar_url( $user_id, [ 'size' => $size ] ) );
    }
}
