<?php
/**
 * Employer dashboard overview
 *
 * @package CiviJobs
 */

global $wpdb;
$user_id = get_current_user_id();

$total_jobs = (int) $wpdb->get_var( $wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'civi_job' AND post_author = %d AND post_status IN ('publish','pending','draft')",
    $user_id
) );

$total_applications = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications a
     INNER JOIN {$wpdb->posts} p ON p.ID = a.job_id
     WHERE p.post_author = {$user_id}"
);

$pending = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications a
     INNER JOIN {$wpdb->posts} p ON p.ID = a.job_id
     WHERE p.post_author = {$user_id} AND a.status = 'pending'"
);

$shortlisted = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications a
     INNER JOIN {$wpdb->posts} p ON p.ID = a.job_id
     WHERE p.post_author = {$user_id} AND a.status = 'shortlisted'"
);

$total_views = (int) $wpdb->get_var( $wpdb->prepare(
    "SELECT SUM(meta_value) FROM {$wpdb->postmeta} pm
     INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
     WHERE pm.meta_key = '_job_views' AND p.post_author = %d",
    $user_id
) );

$recent_jobs = get_posts( [
    'post_type'      => 'civi_job',
    'author'         => $user_id,
    'posts_per_page' => 5,
    'post_status'    => [ 'publish', 'pending', 'draft' ],
    'orderby'        => 'date',
    'order'          => 'DESC',
] );

$recent_applications = $wpdb->get_results(
    "SELECT a.*, p.post_title as job_title, u.display_name as candidate_name, u.user_email as candidate_email
     FROM {$wpdb->prefix}civi_applications a
     INNER JOIN {$wpdb->posts} p ON p.ID = a.job_id
     INNER JOIN {$wpdb->users} u ON u.ID = a.candidate_id
     WHERE p.post_author = {$user_id}
     ORDER BY a.applied_at DESC
     LIMIT 5"
);
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Dashboard', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( "Welcome back! Here's what's happening.", 'civijobs' ); ?></p>
    </div>
    <div style="display:flex;gap:10px">
        <?php if ( civijobs_can_post_job( $user_id ) ) : ?>
            <a href="?section=post-job" class="btn btn-primary">➕ <?php esc_html_e( 'Post a Job', 'civijobs' ); ?></a>
        <?php else : ?>
            <a href="?section=packages" class="btn btn-warning">📦 <?php esc_html_e( 'Upgrade Package', 'civijobs' ); ?></a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats -->
<div class="dash-stats-grid">
    <?php
    $stats = [
        [ '💼', __( 'Active Jobs', 'civijobs' ),    $total_jobs,        'purple' ],
        [ '📋', __( 'Applications', 'civijobs' ),   $total_applications,'blue' ],
        [ '⏳', __( 'Pending Review', 'civijobs' ), $pending,           'orange' ],
        [ '⭐', __( 'Shortlisted', 'civijobs' ),    $shortlisted,       'green' ],
        [ '👁', __( 'Total Views', 'civijobs' ),    $total_views,       'blue' ],
    ];
    foreach ( $stats as [ $icon, $label, $value, $color ] ) : ?>
        <div class="dash-stat-card">
            <div class="dash-stat-icon <?php echo esc_attr( $color ); ?>"><?php echo $icon; ?></div>
            <div>
                <div class="dash-stat-number"><?php echo esc_html( number_format( $value ) ); ?></div>
                <div class="dash-stat-label"><?php echo esc_html( $label ); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

    <!-- Recent Jobs -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3 class="dash-card-title"><?php esc_html_e( 'Recent Jobs', 'civijobs' ); ?></h3>
            <a href="?section=manage-jobs" style="font-size:0.8rem;color:var(--color-primary)"><?php esc_html_e( 'View all', 'civijobs' ); ?></a>
        </div>
        <?php if ( $recent_jobs ) : ?>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <tbody>
                        <?php foreach ( $recent_jobs as $job ) : ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:0.875rem;color:var(--color-gray-900)"><?php echo esc_html( get_the_title( $job->ID ) ); ?></div>
                                    <div style="font-size:0.75rem;color:var(--color-gray-500)"><?php echo esc_html( civijobs_get_application_count( $job->ID ) ); ?> <?php esc_html_e( 'applications', 'civijobs' ); ?></div>
                                </td>
                                <td><?php echo civijobs_get_status_badge( $job->post_status ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
                                <td>
                                    <a href="?section=applications&job_id=<?php echo esc_attr( $job->ID ); ?>" class="dash-action-btn view">
                                        <?php esc_html_e( 'View', 'civijobs' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="dash-card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">💼</div>
                    <div class="empty-state-title"><?php esc_html_e( 'No jobs posted yet', 'civijobs' ); ?></div>
                    <a href="?section=post-job" class="btn btn-primary btn-sm"><?php esc_html_e( 'Post Your First Job', 'civijobs' ); ?></a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Applications -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3 class="dash-card-title"><?php esc_html_e( 'Recent Applications', 'civijobs' ); ?></h3>
            <a href="?section=applications" style="font-size:0.8rem;color:var(--color-primary)"><?php esc_html_e( 'View all', 'civijobs' ); ?></a>
        </div>
        <?php if ( $recent_applications ) : ?>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <tbody>
                        <?php foreach ( $recent_applications as $app ) : ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:0.875rem;color:var(--color-gray-900)"><?php echo esc_html( $app->candidate_name ); ?></div>
                                    <div style="font-size:0.75rem;color:var(--color-gray-500)"><?php echo esc_html( $app->job_title ); ?></div>
                                </td>
                                <td><?php echo civijobs_get_status_badge( $app->status ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
                                <td style="font-size:0.75rem;color:var(--color-gray-400)"><?php echo esc_html( civijobs_time_ago( $app->applied_at ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="dash-card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-title"><?php esc_html_e( 'No applications yet', 'civijobs' ); ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Package info -->
<?php $package = civijobs_get_user_package( $user_id ); ?>
<?php if ( ! $package ) : ?>
    <div class="alert alert-warning" style="margin-top:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <strong><?php esc_html_e( 'No active package', 'civijobs' ); ?></strong>
            <p style="margin:4px 0 0;font-size:0.875rem"><?php esc_html_e( 'Purchase a package to start posting jobs.', 'civijobs' ); ?></p>
        </div>
        <a href="?section=packages" class="btn btn-warning btn-sm"><?php esc_html_e( 'View Packages', 'civijobs' ); ?></a>
    </div>
<?php endif; ?>
