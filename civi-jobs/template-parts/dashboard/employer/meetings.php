<?php
/**
 * Employer: Meetings
 *
 * @package CiviJobs
 */

$user_id  = get_current_user_id();
$meetings = civijobs_get_meetings( $user_id, 'employer' );

// Pre-fill from applications redirect
$compose_candidate = (int) ( $_GET['candidate_id'] ?? 0 );
$compose_job       = (int) ( $_GET['job_id'] ?? 0 );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Meetings', 'civijobs' ); ?></h1>
    </div>
    <button class="btn btn-primary" data-modal-target="schedule-meeting-modal">
        📅 <?php esc_html_e( 'Schedule Meeting', 'civijobs' ); ?>
    </button>
</div>

<?php if ( $meetings ) : ?>
    <div class="meetings-list">
        <?php foreach ( $meetings as $meeting ) :
            $candidate  = get_userdata( $meeting->candidate_id );
            $day        = gmdate( 'j', strtotime( $meeting->date_time ) );
            $month      = gmdate( 'M', strtotime( $meeting->date_time ) );
        ?>
            <div class="meeting-card">
                <div class="meeting-date-box">
                    <div class="meeting-date-day"><?php echo esc_html( $day ); ?></div>
                    <div class="meeting-date-month"><?php echo esc_html( $month ); ?></div>
                </div>
                <div class="meeting-info">
                    <div class="meeting-title"><?php echo esc_html( $meeting->title ); ?></div>
                    <div class="meeting-meta">
                        <div class="meeting-meta-item">👤 <?php echo esc_html( $candidate ? $candidate->display_name : '—' ); ?></div>
                        <div class="meeting-meta-item">🕒 <?php echo esc_html( gmdate( 'g:i A', strtotime( $meeting->date_time ) ) ); ?></div>
                        <?php if ( $meeting->location ) : ?>
                            <div class="meeting-meta-item">📍 <?php echo esc_html( $meeting->location ); ?></div>
                        <?php endif; ?>
                        <?php if ( $meeting->meeting_url ) : ?>
                            <div class="meeting-meta-item">🔗 <a href="<?php echo esc_url( $meeting->meeting_url ); ?>" target="_blank"><?php esc_html_e( 'Join Meeting', 'civijobs' ); ?></a></div>
                        <?php endif; ?>
                    </div>
                    <?php echo civijobs_get_status_badge( $meeting->status ); // phpcs:ignore ?>
                </div>
                <div class="meeting-actions">
                    <?php if ( $meeting->status === 'pending' ) : ?>
                        <button class="btn btn-danger btn-sm cancel-meeting-btn" data-meeting-id="<?php echo esc_attr( $meeting->id ); ?>">
                            <?php esc_html_e( 'Cancel', 'civijobs' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="empty-state">
        <div class="empty-state-icon">📅</div>
        <div class="empty-state-title"><?php esc_html_e( 'No meetings scheduled', 'civijobs' ); ?></div>
        <div class="empty-state-text"><?php esc_html_e( 'Schedule interviews with candidates from the Applications section.', 'civijobs' ); ?></div>
    </div>
<?php endif; ?>

<!-- Schedule Meeting Modal -->
<div id="schedule-meeting-modal" class="modal" role="dialog" aria-modal="true">
    <div class="modal-overlay" data-modal-close></div>
    <div class="modal-dialog" style="max-width:540px">
        <div class="modal-header">
            <h3><?php esc_html_e( 'Schedule a Meeting', 'civijobs' ); ?></h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
            <form id="schedule-meeting-form">
                <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                <input type="hidden" name="employer_id" value="<?php echo esc_attr( $user_id ); ?>">
                <div class="form-group">
                    <label><?php esc_html_e( 'Candidate ID / Email', 'civijobs' ); ?> *</label>
                    <input type="text" name="candidate_id" class="form-control" required
                           value="<?php echo esc_attr( $compose_candidate ?: '' ); ?>"
                           placeholder="<?php esc_attr_e( 'Enter candidate user ID', 'civijobs' ); ?>">
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Related Job', 'civijobs' ); ?></label>
                    <input type="hidden" name="job_id" value="<?php echo esc_attr( $compose_job ); ?>">
                    <input type="text" class="form-control" value="<?php echo $compose_job ? esc_attr( get_the_title( $compose_job ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Optional job title', 'civijobs' ); ?>" readonly>
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Meeting Title', 'civijobs' ); ?> *</label>
                    <input type="text" name="title" class="form-control" required placeholder="<?php esc_attr_e( 'e.g. Initial Interview', 'civijobs' ); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php esc_html_e( 'Date & Time', 'civijobs' ); ?> *</label>
                        <input type="datetime-local" name="date_time" class="form-control" required min="<?php echo esc_attr( gmdate( 'Y-m-d\TH:i' ) ); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php esc_html_e( 'Location', 'civijobs' ); ?></label>
                        <input type="text" name="location" class="form-control" placeholder="<?php esc_attr_e( 'Office or online', 'civijobs' ); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Video Meeting URL', 'civijobs' ); ?></label>
                    <input type="url" name="meeting_url" class="form-control" placeholder="https://meet.google.com/...">
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Description', 'civijobs' ); ?></label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Schedule Meeting', 'civijobs' ); ?></button>
            </form>
        </div>
    </div>
</div>
