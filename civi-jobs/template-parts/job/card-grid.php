<?php
/**
 * Job card — grid view
 *
 * @package CiviJobs
 * @var WP_Post|null $args['job']
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
$deadline   = get_post_meta( $job_id, '_application_deadline', true );
$user_id    = get_current_user_id();
$is_saved   = $user_id ? civijobs_has_saved( $job_id, $user_id ) : false;

$company_name = $company_id ? get_the_title( $company_id ) : '';
$company_url  = $company_id ? get_permalink( $company_id ) : '';
$company_logo = $company_id ? get_the_post_thumbnail_url( $company_id, 'company-logo' ) : '';
?>
<div class="job-card<?php echo $featured ? ' job-card-featured' : ''; ?>" data-job-id="<?php echo esc_attr( $job_id ); ?>">
    <?php if ( $featured ) : ?>
        <div class="job-card-featured-label"><?php esc_html_e( 'Featured', 'civijobs' ); ?></div>
    <?php endif; ?>

    <div class="job-card-header">
        <?php if ( $company_logo ) : ?>
            <img src="<?php echo esc_url( $company_logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="job-logo" loading="lazy">
        <?php else : ?>
            <div class="job-logo-placeholder">
                <?php echo esc_html( mb_strtoupper( mb_substr( get_the_title( $job_id ), 0, 1 ) ) ); ?>
            </div>
        <?php endif; ?>

        <button class="save-job-btn<?php echo $is_saved ? ' is-saved' : ''; ?>"
                data-job-id="<?php echo esc_attr( $job_id ); ?>"
                aria-label="<?php echo $is_saved ? esc_attr__( 'Unsave job', 'civijobs' ) : esc_attr__( 'Save job', 'civijobs' ); ?>">
            <?php echo $is_saved ? '❤️' : '🤍'; ?>
        </button>
    </div>

    <div class="job-card-body">
        <?php if ( ! empty( $types ) ) : ?>
            <span class="badge badge-primary" style="margin-bottom:8px;font-size:0.72rem">
                <?php echo esc_html( $types[0] ); ?>
            </span>
        <?php endif; ?>

        <h3 class="job-title">
            <a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>"><?php echo esc_html( get_the_title( $job_id ) ); ?></a>
        </h3>

        <?php if ( $company_name ) : ?>
            <a href="<?php echo esc_url( $company_url ); ?>" class="job-company"><?php echo esc_html( $company_name ); ?></a>
        <?php endif; ?>

        <div class="job-meta">
            <?php if ( $location ) : ?>
                <span class="job-meta-item">📍 <?php echo esc_html( $location ); ?></span>
            <?php endif; ?>
            <?php if ( $remote ) : ?>
                <span class="badge badge-success" style="font-size:0.7rem"><?php esc_html_e( 'Remote', 'civijobs' ); ?></span>
            <?php endif; ?>
            <?php if ( $salary && $salary !== 'Negotiable' ) : ?>
                <span class="job-meta-item">💵 <?php echo esc_html( $salary ); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="job-card-footer">
        <span class="job-posted-date">
            🕒 <?php echo esc_html( civijobs_time_ago( get_the_date( 'Y-m-d H:i:s', $job_id ) ) ); ?>
        </span>
        <?php if ( $deadline && strtotime( $deadline ) < strtotime( '+7 days' ) ) : ?>
            <span class="badge badge-danger" style="font-size:0.7rem">
                <?php echo esc_html__( 'Closing soon', 'civijobs' ); ?>
            </span>
        <?php endif; ?>
        <a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>" class="btn btn-sm btn-primary">
            <?php esc_html_e( 'Apply', 'civijobs' ); ?>
        </a>
    </div>
</div>
