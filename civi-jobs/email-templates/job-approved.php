<?php
/**
 * Email Template: Job Approved
 * Variables: $employer_name, $job_title, $job_url, $dashboard_url
 *
 * @package CiviJobs
 */
?>
<h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 12px">
    ✅ <?php esc_html_e( 'Your Job Listing is Live!', 'civijobs' ); ?>
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $employer_name ) ); ?>
</p>
<p style="color:#475569;margin:0 0 16px">
    <?php esc_html_e( 'Great news! Your job listing has been approved and is now live on the site:', 'civijobs' ); ?>
</p>
<div style="background:#f0fdf4;border-left:4px solid #22c55e;padding:16px 20px;border-radius:0 8px 8px 0;margin-bottom:20px">
    <div style="font-weight:700;font-size:1.05rem;color:#1e293b"><?php echo esc_html( $job_title ); ?></div>
</div>
<p style="color:#475569;margin:0 0 20px">
    <?php esc_html_e( 'Candidates can now discover and apply to your job. You will receive email notifications for each new application.', 'civijobs' ); ?>
</p>
<div style="text-align:center;margin:24px 0;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="<?php echo esc_url( $job_url ); ?>"
       style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none">
        <?php esc_html_e( 'View Live Job', 'civijobs' ); ?>
    </a>
    <a href="<?php echo esc_url( $dashboard_url ); ?>"
       style="display:inline-block;background:#fff;color:#4f46e5;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none;border:1px solid #4f46e5">
        <?php esc_html_e( 'Manage Jobs', 'civijobs' ); ?>
    </a>
</div>
