<?php
/**
 * Email Template: Job Alert
 * Variables: $user_name, $keyword, $jobs (array of job objects), $unsubscribe_url, $jobs_url
 *
 * @package CiviJobs
 */
?>
<h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 8px">
    🔔 <?php esc_html_e( 'New Jobs Matching Your Alert', 'civijobs' ); ?>
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $user_name ) ); ?>
</p>
<?php if ( $keyword ) : ?>
    <p style="color:#475569;margin:0 0 20px">
        <?php printf(
            esc_html__( 'Here are the latest jobs matching "%s":', 'civijobs' ),
            '<strong>' . esc_html( $keyword ) . '</strong>'
        ); ?>
    </p>
<?php else : ?>
    <p style="color:#475569;margin:0 0 20px"><?php esc_html_e( 'Here are the latest job listings:', 'civijobs' ); ?></p>
<?php endif; ?>

<?php foreach ( $jobs as $job ) :
    $job_title   = get_the_title( $job->ID );
    $job_url     = get_permalink( $job->ID );
    $company_id  = (int) get_post_meta( $job->ID, '_company_id', true );
    $company     = $company_id ? get_the_title( $company_id ) : '';
    $job_location = get_post_meta( $job->ID, '_job_location', true );
    $salary      = civijobs_format_salary( get_post_meta( $job->ID, '_salary_min', true ), get_post_meta( $job->ID, '_salary_max', true ), get_post_meta( $job->ID, '_salary_currency', true ) );
    $types       = wp_get_post_terms( $job->ID, 'job_type' );
?>
    <div style="border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:14px">
        <a href="<?php echo esc_url( $job_url ); ?>" style="font-weight:700;font-size:1rem;color:#1e293b;text-decoration:none;display:block;margin-bottom:4px">
            <?php echo esc_html( $job_title ); ?>
        </a>
        <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:0.82rem;color:#64748b;margin-bottom:12px">
            <?php if ( $company ) : ?>
                <span>🏢 <?php echo esc_html( $company ); ?></span>
            <?php endif; ?>
            <?php if ( $job_location ) : ?>
                <span>📍 <?php echo esc_html( $job_location ); ?></span>
            <?php endif; ?>
            <?php if ( $salary ) : ?>
                <span>💰 <?php echo esc_html( $salary ); ?></span>
            <?php endif; ?>
            <?php if ( $types ) : ?>
                <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:10px;font-weight:600">
                    <?php echo esc_html( $types[0]->name ); ?>
                </span>
            <?php endif; ?>
        </div>
        <a href="<?php echo esc_url( $job_url ); ?>"
           style="display:inline-block;background:#4f46e5;color:#fff;padding:8px 18px;border-radius:6px;font-weight:600;font-size:0.82rem;text-decoration:none">
            <?php esc_html_e( 'View & Apply', 'civijobs' ); ?>
        </a>
    </div>
<?php endforeach; ?>

<div style="text-align:center;margin:24px 0">
    <a href="<?php echo esc_url( $jobs_url ); ?>"
       style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 28px;border-radius:8px;font-weight:600;text-decoration:none">
        <?php esc_html_e( 'Browse All Jobs', 'civijobs' ); ?>
    </a>
</div>
<p style="color:#94a3b8;font-size:0.78rem;text-align:center;margin:0">
    <?php esc_html_e( 'You are receiving this because you set up a job alert.', 'civijobs' ); ?>
    <a href="<?php echo esc_url( $unsubscribe_url ); ?>" style="color:#94a3b8"><?php esc_html_e( 'Unsubscribe', 'civijobs' ); ?></a>
</p>
