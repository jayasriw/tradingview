<?php
/**
 * Candidate: Meetings
 *
 * @package CiviJobs
 */

$user_id  = get_current_user_id();
$meetings = civijobs_get_meetings( $user_id, 'candidate' );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Meetings', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Your scheduled interviews and calls.', 'civijobs' ); ?></p>
    </div>
</div>

<?php if ( $meetings ) : ?>
    <div class="meetings-list">
        <?php foreach ( $meetings as $meeting ) :
            $employer   = get_userdata( $meeting->employer_id );
            $company_id = civijobs_get_employer_company( $meeting->employer_id );
            $day        = gmdate( 'j', strtotime( $meeting->date_time ) );
            $month      = gmdate( 'M', strtotime( $meeting->date_time ) );
            $is_past    = strtotime( $meeting->date_time ) < time();
        ?>
            <div class="meeting-card <?php echo $is_past ? 'meeting-past' : ''; ?>">
                <div class="meeting-date-box">
                    <div class="meeting-date-day"><?php echo esc_html( $day ); ?></div>
                    <div class="meeting-date-month"><?php echo esc_html( $month ); ?></div>
                </div>
                <div class="meeting-info">
                    <div class="meeting-title"><?php echo esc_html( $meeting->title ); ?></div>
                    <div class="meeting-meta">
                        <?php if ( $employer ) : ?>
                            <div class="meeting-meta-item">
                                🏢 <?php echo esc_html( $company_id ? get_the_title( $company_id ) : $employer->display_name ); ?>
                            </div>
                        <?php endif; ?>
                        <div class="meeting-meta-item">🕒 <?php echo esc_html( gmdate( 'g:i A', strtotime( $meeting->date_time ) ) ); ?></div>
                        <?php if ( $meeting->location ) : ?>
                            <div class="meeting-meta-item">📍 <?php echo esc_html( $meeting->location ); ?></div>
                        <?php endif; ?>
                        <?php if ( $meeting->meeting_url ) : ?>
                            <div class="meeting-meta-item">
                                🔗 <a href="<?php echo esc_url( $meeting->meeting_url ); ?>" target="_blank" rel="noopener">
                                    <?php esc_html_e( 'Join Meeting', 'civijobs' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ( $meeting->job_id ) : ?>
                            <div class="meeting-meta-item">
                                📋 <a href="<?php echo esc_url( get_permalink( $meeting->job_id ) ); ?>">
                                    <?php echo esc_html( get_the_title( $meeting->job_id ) ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ( $meeting->description ) : ?>
                        <p style="font-size:0.82rem;color:var(--color-gray-600);margin-top:8px"><?php echo esc_html( $meeting->description ); ?></p>
                    <?php endif; ?>
                    <?php echo civijobs_get_status_badge( $meeting->status ); // phpcs:ignore ?>
                </div>
                <div class="meeting-actions">
                    <?php if ( ! $is_past && $meeting->meeting_url && $meeting->status !== 'cancelled' ) : ?>
                        <a href="<?php echo esc_url( $meeting->meeting_url ); ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                            <?php esc_html_e( 'Join', 'civijobs' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="empty-state">
        <div class="empty-state-icon">📅</div>
        <div class="empty-state-title"><?php esc_html_e( 'No meetings scheduled', 'civijobs' ); ?></div>
        <div class="empty-state-text"><?php esc_html_e( 'Employers can schedule interviews with you from your application. Check back later.', 'civijobs' ); ?></div>
        <a href="?section=applied-jobs" class="btn btn-primary" style="margin-top:16px"><?php esc_html_e( 'View Applications', 'civijobs' ); ?></a>
    </div>
<?php endif; ?>
