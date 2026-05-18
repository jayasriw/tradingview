<?php
/**
 * Employer: Applications manager
 *
 * @package CiviJobs
 */

global $wpdb;
$user_id = get_current_user_id();
$job_id  = (int) ( $_GET['job_id'] ?? 0 );
$status  = sanitize_key( $_GET['app_status'] ?? '' );

$where = "WHERE p.post_author = {$user_id}";
if ( $job_id ) $where .= " AND a.job_id = {$job_id}";
if ( $status ) $where .= $wpdb->prepare( ' AND a.status = %s', $status );

$applications = $wpdb->get_results(
    "SELECT a.*, p.post_title as job_title, u.display_name as candidate_name, u.user_email as candidate_email
     FROM {$wpdb->prefix}civi_applications a
     INNER JOIN {$wpdb->posts} p ON p.ID = a.job_id
     INNER JOIN {$wpdb->users} u ON u.ID = a.candidate_id
     {$where}
     ORDER BY a.applied_at DESC
     LIMIT 100"
);
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Applications', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php printf( _n( '%d application', '%d applications', count( $applications ), 'civijobs' ), count( $applications ) ); // phpcs:ignore ?></p>
    </div>
</div>

<!-- Filters -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach ( [ '' => __( 'All', 'civijobs' ), 'pending' => __( 'Pending', 'civijobs' ), 'reviewing' => __( 'Reviewing', 'civijobs' ), 'shortlisted' => __( 'Shortlisted', 'civijobs' ), 'hired' => __( 'Hired', 'civijobs' ), 'rejected' => __( 'Rejected', 'civijobs' ) ] as $s => $label ) : ?>
        <a href="?section=applications<?php echo $job_id ? '&job_id=' . $job_id : ''; ?>&app_status=<?php echo esc_attr( $s ); ?>"
           class="badge <?php echo $status === $s ? 'badge-primary' : 'badge-gray'; ?>" style="padding:6px 14px;text-decoration:none">
            <?php echo esc_html( $label ); ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="dash-card">
    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Candidate', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Job', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'CV', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Applied', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'civijobs' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $applications ) :
                    foreach ( $applications as $app ) :
                        $candidate_data = civijobs_get_candidate_data( $app->candidate_id );
                ?>
                <tr data-app-id="<?php echo esc_attr( $app->id ); ?>">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <img src="<?php echo esc_url( civijobs_get_avatar_url( (int) $app->candidate_id, 40 ) ); ?>"
                                 style="width:40px;height:40px;border-radius:50%;object-fit:cover">
                            <div>
                                <div style="font-weight:600;font-size:0.875rem"><?php echo esc_html( $app->candidate_name ); ?></div>
                                <div style="font-size:0.75rem;color:var(--color-gray-500)"><?php echo esc_html( $app->candidate_email ); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:0.875rem"><?php echo esc_html( $app->job_title ); ?></td>
                    <td>
                        <select class="application-status-select form-control"
                                data-app-id="<?php echo esc_attr( $app->id ); ?>"
                                style="font-size:0.78rem;padding:4px 8px;width:auto">
                            <?php foreach ( [ 'pending', 'reviewing', 'shortlisted', 'rejected', 'hired' ] as $s ) : ?>
                                <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $app->status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <?php if ( $app->cv_file ) : ?>
                            <a href="<?php echo esc_url( $app->cv_file ); ?>" target="_blank" class="btn btn-sm btn-outline">
                                ⬇ <?php esc_html_e( 'CV', 'civijobs' ); ?>
                            </a>
                        <?php else : ?>
                            <span style="color:var(--color-gray-400);font-size:0.78rem"><?php esc_html_e( 'No file', 'civijobs' ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.78rem;color:var(--color-gray-500)"><?php echo esc_html( civijobs_time_ago( $app->applied_at ) ); ?></td>
                    <td>
                        <div class="dash-table-actions">
                            <button class="dash-action-btn view view-cover-letter-btn"
                                    data-cover="<?php echo esc_attr( $app->cover_letter ); ?>">
                                <?php esc_html_e( 'Cover Letter', 'civijobs' ); ?>
                            </button>
                            <a href="?section=messages&compose_to=<?php echo esc_attr( $app->candidate_id ); ?>&job_id=<?php echo esc_attr( $app->job_id ); ?>" class="dash-action-btn edit">
                                <?php esc_html_e( 'Message', 'civijobs' ); ?>
                            </a>
                            <a href="?section=meetings&candidate_id=<?php echo esc_attr( $app->candidate_id ); ?>&job_id=<?php echo esc_attr( $app->job_id ); ?>" class="dash-action-btn">
                                📅
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach;
                else : ?>
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title"><?php esc_html_e( 'No applications yet', 'civijobs' ); ?></div>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
