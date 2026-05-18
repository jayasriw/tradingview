<?php
/**
 * Candidate: Saved Jobs
 *
 * @package CiviJobs
 */

global $wpdb;
$user_id    = get_current_user_id();
$saved_rows = $wpdb->get_results( $wpdb->prepare(
    "SELECT sj.job_id, sj.created_at FROM {$wpdb->prefix}civi_saved_jobs sj WHERE sj.user_id = %d ORDER BY sj.created_at DESC",
    $user_id
) );
$job_ids = wp_list_pluck( $saved_rows, 'job_id' );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Saved Jobs', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php printf( _n( '%d saved job', '%d saved jobs', count( $job_ids ), 'civijobs' ), count( $job_ids ) ); // phpcs:ignore ?></p>
    </div>
    <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-outline">🔍 <?php esc_html_e( 'Browse More Jobs', 'civijobs' ); ?></a>
</div>

<?php if ( ! empty( $job_ids ) ) :
    $jobs = get_posts( [ 'post_type' => 'civi_job', 'include' => $job_ids, 'posts_per_page' => -1, 'post_status' => 'any' ] );
    foreach ( $jobs as $job ) :
        $is_expired = $job->post_status !== 'publish';
?>
    <div style="display:flex;gap:16px;background:#fff;border:1px solid var(--color-gray-200);border-radius:var(--radius-lg);padding:20px;margin-bottom:12px;align-items:center;<?php echo $is_expired ? 'opacity:.6' : ''; ?>">
        <?php $company_id = (int) get_post_meta( $job->ID, '_company_id', true );
              $logo = $company_id ? get_the_post_thumbnail_url( $company_id, 'company-logo' ) : '';
        ?>
        <?php if ( $logo ) : ?>
            <img src="<?php echo esc_url( $logo ); ?>" style="width:48px;height:48px;object-fit:contain;border-radius:4px;border:1px solid var(--color-gray-200);flex-shrink:0">
        <?php endif; ?>
        <div style="flex:1">
            <a href="<?php echo esc_url( get_permalink( $job->ID ) ); ?>" style="font-weight:700;color:var(--color-gray-900)"><?php echo esc_html( get_the_title( $job->ID ) ); ?></a>
            <?php if ( $company_id ) : ?>
                <div style="font-size:0.8rem;color:var(--color-gray-500)"><?php echo esc_html( get_the_title( $company_id ) ); ?></div>
            <?php endif; ?>
            <div style="display:flex;gap:10px;margin-top:6px;flex-wrap:wrap">
                <?php $location = get_post_meta( $job->ID, '_job_location', true );
                if ( $location ) : ?>
                    <span style="font-size:0.78rem;color:var(--color-gray-500)">📍 <?php echo esc_html( $location ); ?></span>
                <?php endif; ?>
                <?php if ( $is_expired ) : ?>
                    <span class="badge badge-gray" style="font-size:0.68rem"><?php esc_html_e( 'No longer available', 'civijobs' ); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0">
            <?php if ( ! $is_expired ) : ?>
                <a href="<?php echo esc_url( get_permalink( $job->ID ) ); ?>" class="btn btn-primary btn-sm"><?php esc_html_e( 'Apply', 'civijobs' ); ?></a>
            <?php endif; ?>
            <button class="save-job-btn is-saved btn btn-sm btn-outline" data-job-id="<?php echo esc_attr( $job->ID ); ?>" title="<?php esc_attr_e( 'Unsave', 'civijobs' ); ?>">❤️</button>
        </div>
    </div>
<?php endforeach;
else : ?>
    <div class="empty-state">
        <div class="empty-state-icon">❤️</div>
        <div class="empty-state-title"><?php esc_html_e( 'No saved jobs yet', 'civijobs' ); ?></div>
        <div class="empty-state-text"><?php esc_html_e( 'Click the heart icon on any job listing to save it here.', 'civijobs' ); ?></div>
        <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-primary" style="margin-top:16px"><?php esc_html_e( 'Browse Jobs', 'civijobs' ); ?></a>
    </div>
<?php endif; ?>
