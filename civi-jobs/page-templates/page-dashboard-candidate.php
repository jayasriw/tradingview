<?php
/**
 * Template Name: Candidate Dashboard
 *
 * @package CiviJobs
 */

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( get_permalink() ) );
    exit;
}

$user_id = get_current_user_id();
if ( ! civijobs_is_candidate( $user_id ) ) {
    wp_safe_redirect( civijobs_get_dashboard_url() );
    exit;
}

$section      = sanitize_key( $_GET['section'] ?? 'overview' );
$user         = wp_get_current_user();
$unread_msg   = civijobs_get_unread_messages( $user_id );
$unread_notif = civijobs_get_unread_notifications( $user_id );
$package      = civijobs_get_user_package( $user_id );

get_header();
?>
<main class="site-main" id="main">
    <div class="dashboard-layout">

        <!-- ===== SIDEBAR ===== -->
        <aside class="dashboard-sidebar">
            <div class="dash-user-panel">
                <img src="<?php echo esc_url( civijobs_get_avatar_url( $user_id ) ); ?>"
                     alt="<?php echo esc_attr( $user->display_name ); ?>"
                     class="dash-user-avatar">
                <div>
                    <div class="dash-user-name"><?php echo esc_html( $user->display_name ); ?></div>
                    <span class="dash-user-role"><?php esc_html_e( 'Candidate', 'civijobs' ); ?></span>
                    <?php if ( $package ) : ?>
                        <div class="dash-user-package">📦 <?php echo esc_html( $package->package_name ); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="dash-nav">
                <?php
                $nav_items = [
                    [ 'overview',      '📊', __( 'Dashboard', 'civijobs' ),      '' ],
                    [ 'edit-profile',  '✏️', __( 'Edit Profile', 'civijobs' ),    '' ],
                    [ 'applied-jobs',  '📋', __( 'Applied Jobs', 'civijobs' ),    '' ],
                    [ 'saved-jobs',    '❤️', __( 'Saved Jobs', 'civijobs' ),       '' ],
                    [ 'messages',      '✉️', __( 'Messages', 'civijobs' ),         $unread_msg ?: '' ],
                    [ 'meetings',      '📅', __( 'Meetings', 'civijobs' ),         '' ],
                    [ 'job-alerts',    '🔔', __( 'Job Alerts', 'civijobs' ),       '' ],
                    [ 'services',      '🛠', __( 'My Services', 'civijobs' ),      '' ],
                    [ 'wallet',        '💳', __( 'Wallet', 'civijobs' ),           '' ],
                    [ 'packages',      '📦', __( 'Packages', 'civijobs' ),         '' ],
                    [ 'notifications', '🔔', __( 'Notifications', 'civijobs' ),    $unread_notif ?: '' ],
                    [ 'settings',      '⚙️', __( 'Settings', 'civijobs' ),          '' ],
                ];
                foreach ( $nav_items as [ $s, $icon, $label, $badge ] ) : ?>
                    <div class="dash-nav-item">
                        <a href="?section=<?php echo esc_attr( $s ); ?>"
                           class="dash-nav-link<?php echo $section === $s ? ' active' : ''; ?>">
                            <span class="dash-nav-icon"><?php echo $icon; ?></span>
                            <?php echo esc_html( $label ); ?>
                            <?php if ( $badge ) : ?>
                                <span class="dash-nav-badge"><?php echo esc_html( $badge ); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </nav>

            <div class="dash-sidebar-footer">
                <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>">🌐 <?php esc_html_e( 'Browse Jobs', 'civijobs' ); ?></a>
                <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" style="color:var(--color-danger)">🚪 <?php esc_html_e( 'Log Out', 'civijobs' ); ?></a>
            </div>
        </aside>

        <!-- ===== MAIN ===== -->
        <div class="dashboard-main">
            <?php
            switch ( $section ) {
                case 'edit-profile':
                    get_template_part( 'template-parts/dashboard/candidate/edit-profile' );
                    break;
                case 'applied-jobs':
                    get_template_part( 'template-parts/dashboard/candidate/applied-jobs' );
                    break;
                case 'saved-jobs':
                    get_template_part( 'template-parts/dashboard/candidate/saved-jobs' );
                    break;
                case 'messages':
                    get_template_part( 'template-parts/dashboard/candidate/messages' );
                    break;
                case 'meetings':
                    get_template_part( 'template-parts/dashboard/candidate/meetings' );
                    break;
                case 'job-alerts':
                    get_template_part( 'template-parts/dashboard/candidate/job-alerts' );
                    break;
                case 'services':
                    get_template_part( 'template-parts/dashboard/candidate/services' );
                    break;
                case 'wallet':
                    get_template_part( 'template-parts/dashboard/candidate/wallet' );
                    break;
                case 'packages':
                    get_template_part( 'template-parts/dashboard/candidate/packages' );
                    break;
                case 'notifications':
                    get_template_part( 'template-parts/dashboard/candidate/notifications' );
                    break;
                case 'settings':
                    get_template_part( 'template-parts/dashboard/candidate/settings' );
                    break;
                default:
                    get_template_part( 'template-parts/dashboard/candidate/main' );
            }
            ?>
        </div>

    </div>
</main>
<?php get_footer();
