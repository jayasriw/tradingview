<?php
/**
 * Single job template
 *
 * @package JobPortal
 */

get_header();

if ( ! have_posts() ) {
    get_footer();
    return;
}

the_post();
$job_id     = get_the_ID();
$company_id = (int) get_post_meta( $job_id, '_company_id', true );
$location   = get_post_meta( $job_id, '_job_location', true );
$remote     = (bool) get_post_meta( $job_id, '_remote_ok', true );
$salary     = jobportal_format_salary(
    (float) get_post_meta( $job_id, '_salary_min', true ),
    (float) get_post_meta( $job_id, '_salary_max', true ),
    get_post_meta( $job_id, '_salary_type', true ) ?: 'annual'
);
$deadline   = get_post_meta( $job_id, '_application_deadline', true );
$featured   = (bool) get_post_meta( $job_id, '_is_featured', true );
$user_id    = get_current_user_id();
$has_applied= $user_id ? jobportal_has_applied( $job_id, $user_id ) : false;
$is_saved   = $user_id ? jobportal_has_saved( $job_id, $user_id ) : false;
$types      = wp_get_post_terms( $job_id, 'job_type', [ 'fields' => 'names' ] );
$skills     = get_post_meta( $job_id, '_required_skills', true );
$benefits   = get_post_meta( $job_id, '_benefits', true );
$video_url  = get_post_meta( $job_id, '_video_url', true );

$company_name = $company_id ? get_the_title( $company_id ) : '';
$company_logo = $company_id ? get_the_post_thumbnail_url( $company_id, 'company-logo' ) : '';
$company_url  = $company_id ? get_permalink( $company_id ) : '';

jobportal_increment_job_views( $job_id );
?>
<div class="container" style="padding-top:32px;padding-bottom:60px">
    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

    <div class="single-job-layout">
        <div class="single-job-main">

            <!-- Job Header -->
            <div class="job-detail-header">
                <?php if ( $company_logo ) : ?>
                    <img src="<?php echo esc_url( $company_logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="job-detail-logo">
                <?php else : ?>
                    <div class="job-logo-placeholder large"><?php echo esc_html( mb_strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?></div>
                <?php endif; ?>

                <div class="job-detail-info">
                    <h1 class="job-detail-title"><?php the_title(); ?></h1>
                    <?php if ( $company_name ) : ?>
                        <a href="<?php echo esc_url( $company_url ); ?>" class="job-detail-company"><?php echo esc_html( $company_name ); ?></a>
                    <?php endif; ?>
                    <div class="job-detail-meta">
                        <?php if ( $location ) : ?>
                            <span>&#128205; <?php echo esc_html( $location ); ?></span>
                        <?php endif; ?>
                        <?php if ( $remote ) : ?>
                            <span class="badge badge-success">Remote</span>
                        <?php endif; ?>
                        <?php if ( $salary && $salary !== 'Negotiable' ) : ?>
                            <span>&#128181; <?php echo esc_html( $salary ); ?></span>
                        <?php endif; ?>
                        <?php if ( ! empty( $types ) ) : ?>
                            <span class="badge badge-primary"><?php echo esc_html( $types[0] ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Job Description -->
            <div class="dash-card">
                <div class="dash-card-body">
                    <h2><?php esc_html_e( 'Job Description', 'jobportal' ); ?></h2>
                    <div class="job-description-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <aside class="single-job-sidebar">
            <div class="dash-card" style="margin-bottom:20px">
                <div class="dash-card-body">
                    <?php if ( $has_applied ) : ?>
                        <div class="alert alert-success"><?php esc_html_e( 'You have applied for this job.', 'jobportal' ); ?></div>
                    <?php elseif ( $user_id ) : ?>
                        <button class="btn btn-primary btn-block apply-job-btn" data-job-id="<?php echo esc_attr( $job_id ); ?>">
                            <?php esc_html_e( 'Apply Now', 'jobportal' ); ?>
                        </button>
                    <?php else : ?>
                        <a href="<?php echo esc_url( home_url( '/login?redirect_to=' . urlencode( get_permalink() ) ) ); ?>" class="btn btn-primary btn-block">
                            <?php esc_html_e( 'Login to Apply', 'jobportal' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( $user_id ) : ?>
                        <button class="save-job-btn btn btn-outline btn-block" data-job-id="<?php echo esc_attr( $job_id ); ?>" style="margin-top:8px">
                            <?php echo $is_saved ? esc_html__( 'Saved', 'jobportal' ) : esc_html__( 'Save Job', 'jobportal' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-header"><h3 class="dash-card-title"><?php esc_html_e( 'Job Overview', 'jobportal' ); ?></h3></div>
                <div class="dash-card-body">
                    <?php if ( $deadline ) : ?>
                        <div class="job-overview-row">
                            <span><?php esc_html_e( 'Deadline', 'jobportal' ); ?></span>
                            <strong><?php echo esc_html( gmdate( 'M j, Y', strtotime( $deadline ) ) ); ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="job-overview-row">
                        <span><?php esc_html_e( 'Posted', 'jobportal' ); ?></span>
                        <strong><?php echo esc_html( get_the_date() ); ?></strong>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
<?php
get_footer();
