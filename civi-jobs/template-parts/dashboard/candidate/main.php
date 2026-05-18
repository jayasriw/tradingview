<?php
/**
 * Candidate dashboard overview
 *
 * @package CiviJobs
 */

global $wpdb;
$user_id = get_current_user_id();
$profile = civijobs_get_candidate_data( $user_id );

$total_applied = (int) $wpdb->get_var( $wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications WHERE candidate_id = %d", $user_id
) );
$total_saved = (int) $wpdb->get_var( $wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}civi_saved_jobs WHERE user_id = %d", $user_id
) );
$pending_apps = (int) $wpdb->get_var( $wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications WHERE candidate_id = %d AND status = 'pending'", $user_id
) );
$shortlisted = (int) $wpdb->get_var( $wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications WHERE candidate_id = %d AND status = 'shortlisted'", $user_id
) );

$recent_applications = $wpdb->get_results( $wpdb->prepare(
    "SELECT a.*, p.post_title as job_title
     FROM {$wpdb->prefix}civi_applications a
     INNER JOIN {$wpdb->posts} p ON p.ID = a.job_id
     WHERE a.candidate_id = %d ORDER BY a.applied_at DESC LIMIT 5",
    $user_id
) );

// Profile completeness
$fields = [ 'bio', 'location', 'phone', 'linkedin', 'civi_cv_file', 'headline' ];
$filled = 0;
foreach ( $fields as $f ) {
    if ( ! empty( $profile[ $f ] ?? get_user_meta( $user_id, $f, true ) ) ) $filled++;
}
$completeness = (int) round( ( $filled / count( $fields ) ) * 100 );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Dashboard', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( "Here's an overview of your job search activity.", 'civijobs' ); ?></p>
    </div>
    <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-primary">🔍 <?php esc_html_e( 'Browse Jobs', 'civijobs' ); ?></a>
</div>

<!-- Stats -->
<div class="dash-stats-grid">
    <?php foreach ( [
        [ '📋', __( 'Applied Jobs', 'civijobs' ),  $total_applied,  'blue' ],
        [ '❤️', __( 'Saved Jobs', 'civijobs' ),    $total_saved,    'orange' ],
        [ '⏳', __( 'Pending', 'civijobs' ),       $pending_apps,   'purple' ],
        [ '⭐', __( 'Shortlisted', 'civijobs' ),   $shortlisted,    'green' ],
    ] as [ $icon, $label, $val, $color ] ) : ?>
        <div class="dash-stat-card">
            <div class="dash-stat-icon <?php echo esc_attr( $color ); ?>"><?php echo $icon; ?></div>
            <div>
                <div class="dash-stat-number"><?php echo esc_html( number_format( $val ) ); ?></div>
                <div class="dash-stat-label"><?php echo esc_html( $label ); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:24px">

    <!-- Recent Applications -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3 class="dash-card-title"><?php esc_html_e( 'Recent Applications', 'civijobs' ); ?></h3>
            <a href="?section=applied-jobs" style="font-size:0.8rem;color:var(--color-primary)"><?php esc_html_e( 'View all', 'civijobs' ); ?></a>
        </div>
        <?php if ( $recent_applications ) : ?>
            <table class="dash-table">
                <thead><tr>
                    <th><?php esc_html_e( 'Job', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Applied', 'civijobs' ); ?></th>
                </tr></thead>
                <tbody>
                    <?php foreach ( $recent_applications as $app ) : ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url( get_permalink( $app->job_id ) ); ?>" style="font-weight:600;font-size:0.875rem;color:var(--color-gray-900)">
                                <?php echo esc_html( $app->job_title ); ?>
                            </a>
                        </td>
                        <td><?php echo civijobs_get_status_badge( $app->status ); // phpcs:ignore ?></td>
                        <td style="font-size:0.75rem;color:var(--color-gray-500)"><?php echo esc_html( civijobs_time_ago( $app->applied_at ) ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="dash-card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-title"><?php esc_html_e( 'No applications yet', 'civijobs' ); ?></div>
                    <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-primary btn-sm" style="margin-top:12px"><?php esc_html_e( 'Find Jobs', 'civijobs' ); ?></a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Profile Completeness + Sidebar -->
    <div style="display:flex;flex-direction:column;gap:20px">
        <!-- Profile completion -->
        <div class="dash-card">
            <div class="dash-card-header"><h3 class="dash-card-title"><?php esc_html_e( 'Profile Strength', 'civijobs' ); ?></h3></div>
            <div class="dash-card-body">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <span style="font-size:0.875rem;color:var(--color-gray-600)"><?php esc_html_e( 'Completeness', 'civijobs' ); ?></span>
                    <span style="font-weight:700;color:var(--color-primary)"><?php echo esc_html( $completeness ); ?>%</span>
                </div>
                <div style="height:8px;background:var(--color-gray-200);border-radius:var(--radius-full);overflow:hidden">
                    <div style="height:100%;width:<?php echo esc_attr( $completeness ); ?>%;background:var(--color-primary);border-radius:var(--radius-full);transition:width .5s"></div>
                </div>
                <?php if ( $completeness < 100 ) : ?>
                    <a href="?section=edit-profile" class="btn btn-outline btn-sm btn-block" style="margin-top:14px"><?php esc_html_e( 'Complete Profile', 'civijobs' ); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="dash-card">
            <div class="dash-card-header"><h3 class="dash-card-title"><?php esc_html_e( 'Quick Actions', 'civijobs' ); ?></h3></div>
            <div class="dash-card-body" style="display:flex;flex-direction:column;gap:8px">
                <a href="?section=edit-profile" class="btn btn-outline btn-sm btn-block">✏️ <?php esc_html_e( 'Edit Profile', 'civijobs' ); ?></a>
                <a href="?section=job-alerts" class="btn btn-outline btn-sm btn-block">🔔 <?php esc_html_e( 'Set Job Alert', 'civijobs' ); ?></a>
                <a href="?section=saved-jobs" class="btn btn-outline btn-sm btn-block">❤️ <?php esc_html_e( 'Saved Jobs', 'civijobs' ); ?></a>
                <a href="?section=services" class="btn btn-outline btn-sm btn-block">🛠 <?php esc_html_e( 'My Services', 'civijobs' ); ?></a>
            </div>
        </div>
    </div>

</div>
