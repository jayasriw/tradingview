<?php
/**
 * Candidate: Applied Jobs
 *
 * @package CiviJobs
 */

$user_id      = get_current_user_id();
$applications = civijobs_get_candidate_applications( $user_id );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Applied Jobs', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php printf( _n( '%d application', '%d applications', count( $applications ), 'civijobs' ), count( $applications ) ); // phpcs:ignore ?></p>
    </div>
</div>

<div class="dash-card">
    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Job', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Company', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Applied', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'civijobs' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $applications ) ) :
                    foreach ( $applications as $app ) :
                        $company_id  = (int) get_post_meta( $app->job_id, '_company_id', true );
                        $company_name= $company_id ? get_the_title( $company_id ) : '—';
                        $company_logo= $company_id ? get_the_post_thumbnail_url( $company_id, 'company-logo' ) : '';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <?php if ( $company_logo ) : ?>
                                <img src="<?php echo esc_url( $company_logo ); ?>" style="width:36px;height:36px;object-fit:contain;border-radius:4px;border:1px solid var(--color-gray-200)">
                            <?php endif; ?>
                            <div>
                                <a href="<?php echo esc_url( get_permalink( $app->job_id ) ); ?>" style="font-weight:600;color:var(--color-gray-900);font-size:0.875rem">
                                    <?php echo esc_html( $app->job_title ); ?>
                                </a>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:0.875rem"><?php echo esc_html( $company_name ); ?></td>
                    <td><?php echo civijobs_get_status_badge( $app->status ); // phpcs:ignore ?></td>
                    <td style="font-size:0.78rem;color:var(--color-gray-500)"><?php echo esc_html( civijobs_time_ago( $app->applied_at ) ); ?></td>
                    <td>
                        <div class="dash-table-actions">
                            <a href="<?php echo esc_url( get_permalink( $app->job_id ) ); ?>" class="dash-action-btn view"><?php esc_html_e( 'View Job', 'civijobs' ); ?></a>
                            <?php if ( $app->status === 'pending' ) : ?>
                                <button class="dash-action-btn del withdraw-application-btn"
                                        data-app-id="<?php echo esc_attr( $app->id ); ?>">
                                    <?php esc_html_e( 'Withdraw', 'civijobs' ); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach;
                else : ?>
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title"><?php esc_html_e( "You haven't applied to any jobs yet.", 'civijobs' ); ?></div>
                            <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-primary btn-sm" style="margin-top:12px"><?php esc_html_e( 'Find Jobs', 'civijobs' ); ?></a>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
