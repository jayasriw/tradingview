<?php
/**
 * Job card — list view
 *
 * @package CiviJobs
 */

$job_post   = $args['job'] ?? null;
if ( ! $job_post ) return;

$job_id     = $job_post->ID;
$company_id = (int) get_post_meta( $job_id, '_company_id', true );
$types      = wp_get_post_terms( $job_id, 'job_type', [ 'fields' => 'names' ] );
$salary     = civijobs_format_salary(
    (float) get_post_meta( $job_id, '_salary_min', true ),
    (float) get_post_meta( $job_id, '_salary_max', true ),
    get_post_meta( $job_id, '_salary_type', true ) ?: 'annual'
);
$location   = get_post_meta( $job_id, '_job_location', true );
$featured   = (bool) get_post_meta( $job_id, '_is_featured', true );
$remote     = (bool) get_post_meta( $job_id, '_remote_ok', true );

$company_name = $company_id ? get_the_title( $company_id ) : '';
$company_url  = $company_id ? get_permalink( $company_id ) : '';
$company_logo = $company_id ? get_the_post_thumbnail_url( $company_id, 'company-logo' ) : '';
$user_id      = get_current_user_id();
$is_saved     = $user_id ? civijobs_has_saved( $job_id, $user_id ) : false;
?>
<div class="job-card-list<?php echo $featured ? ' job-card-featured' : ''; ?>" data-job-id="<?php echo esc_attr( $job_id ); ?>">
    <div class="job-logo-wrap">
        <?php if ( $company_logo ) : ?>
            <img src="<?php echo esc_url( $company_logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="job-logo" loading="lazy">
        <?php else : ?>
            <div class="job-logo-placeholder"><?php echo esc_html( mb_strtoupper( mb_substr( get_the_title( $job_id ), 0, 1 ) ) ); ?></div>
        <?php endif; ?>
    </div>

    <div class="job-list-body">
        <div class="job-list-main">
            <h3 class="job-title" style="font-size:1rem;margin-bottom:4px">
                <a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>"><?php echo esc_html( get_the_title( $job_id ) ); ?></a>
                <?php if ( $featured ) : ?><span class="badge badge-warning" style="font-size:0.68rem;margin-left:6px"><?php esc_html_e( 'Featured', 'civijobs' ); ?></span><?php endif; ?>
            </h3>
            <?php if ( $company_name ) : ?>
                <a href="<?php echo esc_url( $company_url ); ?>" class="job-company" style="font-size:0.875rem"><?php echo esc_html( $company_name ); ?></a>
            <?php endif; ?>
            <div class="job-meta" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:10px">
                <?php if ( $location ) : ?>
                    <span style="font-size:0.8rem;color:var(--color-gray-600)">📍 <?php echo esc_html( $location ); ?></span>
                <?php endif; ?>
                <?php if ( $remote ) : ?>
                    <span class="badge badge-success" style="font-size:0.7rem"><?php esc_html_e( 'Remote', 'civijobs' ); ?></span>
                <?php endif; ?>
                <?php if ( ! empty( $types ) ) : ?>
                    <span class="badge badge-primary" style="font-size:0.7rem"><?php echo esc_html( $types[0] ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="job-list-aside" style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0">
            <?php if ( $salary ) : ?>
                <span style="font-size:0.9rem;font-weight:700;color:var(--color-gray-800)"><?php echo esc_html( $salary ); ?></span>
            <?php endif; ?>
            <span style="font-size:0.75rem;color:var(--color-gray-400)"><?php echo esc_html( civijobs_time_ago( get_the_date( 'Y-m-d H:i:s', $job_id ) ) ); ?></span>
            <div style="display:flex;gap:6px">
                <button class="save-job-btn<?php echo $is_saved ? ' is-saved' : ''; ?>" data-job-id="<?php echo esc_attr( $job_id ); ?>" style="background:none;border:1px solid var(--color-gray-200);border-radius:var(--radius);padding:5px 8px;cursor:pointer;font-size:0.8rem">
                    <?php echo $is_saved ? '❤️' : '🤍'; ?>
                </button>
                <a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>" class="btn btn-primary btn-sm"><?php esc_html_e( 'Apply', 'civijobs' ); ?></a>
            </div>
        </div>
    </div>
</div>
