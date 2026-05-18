<?php
/**
 * Candidate: Job Alerts
 *
 * @package CiviJobs
 */

$user_id = get_current_user_id();
$alerts  = civijobs_get_user_alerts( $user_id );

$categories = get_terms( [ 'taxonomy' => 'job_category', 'hide_empty' => false ] );
$job_types  = get_terms( [ 'taxonomy' => 'job_type', 'hide_empty' => false ] );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Job Alerts', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Get notified when jobs matching your criteria are posted.', 'civijobs' ); ?></p>
    </div>
    <button class="btn btn-primary" data-modal-target="create-alert-modal">
        🔔 <?php esc_html_e( 'Create Alert', 'civijobs' ); ?>
    </button>
</div>

<?php if ( $alerts ) : ?>
    <div class="dash-card">
        <div class="dash-card-body" style="padding:0">
            <?php foreach ( $alerts as $alert ) :
                $freq_label = $alert->frequency === 'daily' ? __( 'Daily', 'civijobs' ) : __( 'Weekly', 'civijobs' );
            ?>
                <div class="alert-item">
                    <div class="alert-icon">🔔</div>
                    <div class="alert-info">
                        <div class="alert-name"><?php echo esc_html( $alert->keyword ?: __( 'Any keyword', 'civijobs' ) ); ?></div>
                        <div class="alert-meta">
                            <?php if ( $alert->location ) : ?>
                                <span>📍 <?php echo esc_html( $alert->location ); ?></span>
                            <?php endif; ?>
                            <?php if ( $alert->category ) :
                                $cat = get_term_by( 'slug', $alert->category, 'job_category' );
                                if ( $cat ) : ?>
                                    <span>🏷 <?php echo esc_html( $cat->name ); ?></span>
                                <?php endif;
                            endif; ?>
                            <span>🔁 <?php echo esc_html( $freq_label ); ?></span>
                            <span style="color:var(--color-gray-400)"><?php echo esc_html( civijobs_time_ago( $alert->created_at ) ); ?></span>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline del delete-alert-btn" data-alert-id="<?php echo esc_attr( $alert->id ); ?>">
                        <?php esc_html_e( 'Delete', 'civijobs' ); ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else : ?>
    <div class="empty-state">
        <div class="empty-state-icon">🔔</div>
        <div class="empty-state-title"><?php esc_html_e( 'No job alerts yet', 'civijobs' ); ?></div>
        <div class="empty-state-text"><?php esc_html_e( "Create alerts and we'll email you when matching jobs are posted.", 'civijobs' ); ?></div>
        <button class="btn btn-primary" data-modal-target="create-alert-modal" style="margin-top:16px">
            <?php esc_html_e( 'Create Your First Alert', 'civijobs' ); ?>
        </button>
    </div>
<?php endif; ?>

<!-- Create Alert Modal -->
<div id="create-alert-modal" class="modal" role="dialog" aria-modal="true">
    <div class="modal-overlay" data-modal-close></div>
    <div class="modal-dialog" style="max-width:480px">
        <div class="modal-header">
            <h3><?php esc_html_e( 'Create Job Alert', 'civijobs' ); ?></h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
            <form id="create-alert-form">
                <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                <div class="form-group">
                    <label><?php esc_html_e( 'Keywords', 'civijobs' ); ?></label>
                    <input type="text" name="keyword" class="form-control" placeholder="<?php esc_attr_e( 'e.g. PHP Developer', 'civijobs' ); ?>">
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Location', 'civijobs' ); ?></label>
                    <input type="text" name="location" class="form-control" placeholder="<?php esc_attr_e( 'City or Remote', 'civijobs' ); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php esc_html_e( 'Category', 'civijobs' ); ?></label>
                        <select name="category" class="form-control">
                            <option value=""><?php esc_html_e( 'Any Category', 'civijobs' ); ?></option>
                            <?php foreach ( $categories as $cat ) : ?>
                                <option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php esc_html_e( 'Job Type', 'civijobs' ); ?></label>
                        <select name="job_type" class="form-control">
                            <option value=""><?php esc_html_e( 'Any Type', 'civijobs' ); ?></option>
                            <?php foreach ( $job_types as $type ) : ?>
                                <option value="<?php echo esc_attr( $type->slug ); ?>"><?php echo esc_html( $type->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Alert Frequency', 'civijobs' ); ?></label>
                    <select name="frequency" class="form-control">
                        <option value="daily"><?php esc_html_e( 'Daily', 'civijobs' ); ?></option>
                        <option value="weekly"><?php esc_html_e( 'Weekly', 'civijobs' ); ?></option>
                    </select>
                </div>
                <div id="alert-form-messages"></div>
                <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Create Alert', 'civijobs' ); ?></button>
            </form>
        </div>
    </div>
</div>

<style>
.alert-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-gray-100);
}
.alert-item:last-child { border-bottom: none; }
.alert-icon { font-size: 1.5rem; flex-shrink: 0; }
.alert-info { flex: 1; }
.alert-name { font-weight: 600; color: var(--color-gray-900); margin-bottom: 4px; }
.alert-meta { display: flex; gap: 12px; flex-wrap: wrap; font-size: 0.78rem; color: var(--color-gray-500); }
</style>
