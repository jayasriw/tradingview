<?php
/**
 * Site Header
 *
 * @package JobPortal
 */

$user_id        = get_current_user_id();
$user_role      = $user_id ? jobportal_get_user_role( $user_id ) : '';
$unread_msgs    = $user_id ? jobportal_get_unread_messages( $user_id ) : 0;
$unread_notifs  = $user_id ? jobportal_get_unread_notifications( $user_id ) : 0;
$dashboard_url  = $user_id ? jobportal_get_dashboard_url( $user_id ) : '';
$current_user   = $user_id ? wp_get_current_user() : null;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-user-id="<?php echo esc_attr( $user_id ); ?>" data-user-role="<?php echo esc_attr( $user_role ); ?>">
<?php wp_body_open(); ?>

<header class="site-header" id="site-header">
    <div class="header-inner container">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
            <?php
            $logo = get_theme_mod( 'custom_logo' );
            if ( $logo ) :
                echo wp_get_attachment_image( $logo, 'full', false, [ 'class' => 'logo-img', 'alt' => get_bloginfo( 'name' ) ] );
            else :
            ?>
                <span class="logo-text"><?php bloginfo( 'name' ); ?></span>
            <?php endif; ?>
        </a>

        <nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'jobportal' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'nav-menu',
                'fallback_cb'    => function() {
                    echo '<ul class="nav-menu">';
                    echo '<li><a href="' . esc_url( home_url( '/jobs' ) ) . '">' . esc_html__( 'Find Jobs', 'jobportal' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/companies' ) ) . '">' . esc_html__( 'Companies', 'jobportal' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/candidates' ) ) . '">' . esc_html__( 'Candidates', 'jobportal' ) . '</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/pricing' ) ) . '">' . esc_html__( 'Pricing', 'jobportal' ) . '</a></li>';
                    echo '</ul>';
                },
            ] );
            ?>
        </nav>

        <div class="header-actions">
            <?php if ( $user_id ) : ?>
                <div class="header-icon-btn notification-trigger" id="notification-trigger" aria-label="<?php esc_attr_e( 'Notifications', 'jobportal' ); ?>">
                    &#128276;
                    <?php if ( $unread_notifs > 0 ) : ?>
                        <span class="notification-badge" id="notification-badge"><?php echo esc_html( $unread_notifs > 9 ? '9+' : $unread_notifs ); ?></span>
                    <?php else : ?>
                        <span class="notification-badge hidden" id="notification-badge"></span>
                    <?php endif; ?>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notification-dropdown-header">
                            <span><?php esc_html_e( 'Notifications', 'jobportal' ); ?></span>
                            <?php if ( $unread_notifs > 0 ) : ?>
                                <button class="mark-all-read-btn" id="header-mark-all-read"><?php esc_html_e( 'Mark all read', 'jobportal' ); ?></button>
                            <?php endif; ?>
                        </div>
                        <div class="notification-list" id="notification-list">
                            <div style="padding:20px;text-align:center;color:var(--color-gray-400)"><?php esc_html_e( 'Loading...', 'jobportal' ); ?></div>
                        </div>
                    </div>
                </div>

                <div class="user-menu" id="user-menu">
                    <button class="user-menu-trigger" aria-expanded="false" aria-controls="user-dropdown">
                        <img src="<?php echo esc_url( jobportal_get_avatar_url( $user_id, 36 ) ); ?>"
                             alt="<?php echo esc_attr( $current_user->display_name ); ?>"
                             class="user-menu-avatar">
                        <span class="user-menu-name"><?php echo esc_html( wp_trim_words( $current_user->display_name, 2, '' ) ); ?></span>
                        <span class="user-menu-arrow">&#9662;</span>
                    </button>
                    <div class="user-dropdown" id="user-dropdown" role="menu">
                        <div class="user-dropdown-header">
                            <div class="user-dropdown-name"><?php echo esc_html( $current_user->display_name ); ?></div>
                            <div class="user-dropdown-role"><?php echo esc_html( ucfirst( str_replace( 'jp_', '', $user_role ) ) ); ?></div>
                        </div>
                        <a href="<?php echo esc_url( $dashboard_url ); ?>" class="user-dropdown-item" role="menuitem">
                            &#128202; <?php esc_html_e( 'Dashboard', 'jobportal' ); ?>
                        </a>
                        <?php if ( current_user_can( 'manage_options' ) ) : ?>
                            <a href="<?php echo esc_url( admin_url() ); ?>" class="user-dropdown-item" role="menuitem">
                                &#9881;&#65039; <?php esc_html_e( 'Admin Panel', 'jobportal' ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="user-dropdown-divider"></div>
                        <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="user-dropdown-item user-dropdown-item--danger" role="menuitem">
                            <?php esc_html_e( 'Log Out', 'jobportal' ); ?>
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/login' ) ); ?>" class="btn btn-outline btn-sm">
                    <?php esc_html_e( 'Log In', 'jobportal' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/register' ) ); ?>" class="btn btn-primary btn-sm">
                    <?php esc_html_e( 'Sign Up', 'jobportal' ); ?>
                </a>
            <?php endif; ?>

            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="<?php esc_attr_e( 'Toggle mobile menu', 'jobportal' ); ?>" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<div class="mobile-nav-overlay" id="mobile-nav-overlay">
    <div class="mobile-nav">
        <div class="mobile-nav-header">
            <span class="logo-text"><?php bloginfo( 'name' ); ?></span>
            <button class="mobile-nav-close" id="mobile-nav-close">&times;</button>
        </div>
        <?php
        wp_nav_menu( [
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'mobile-nav-menu',
        ] );
        ?>
        <?php if ( $user_id ) : ?>
            <div class="mobile-nav-footer">
                <a href="<?php echo esc_url( $dashboard_url ); ?>" class="btn btn-primary btn-block"><?php esc_html_e( 'Dashboard', 'jobportal' ); ?></a>
            </div>
        <?php else : ?>
            <div class="mobile-nav-footer">
                <a href="<?php echo esc_url( home_url( '/login' ) ); ?>" class="btn btn-outline btn-block" style="margin-bottom:8px"><?php esc_html_e( 'Log In', 'jobportal' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/register' ) ); ?>" class="btn btn-primary btn-block"><?php esc_html_e( 'Sign Up', 'jobportal' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<main id="main-content">
