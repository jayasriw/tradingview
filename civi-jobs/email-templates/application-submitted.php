<?php
/**
 * Email Template: Application Submitted (sent to candidate)
 * Variables: $candidate_name, $job_title, $company_name, $job_url, $dashboard_url
 *
 * @package CiviJobs
 */
?>
<h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 12px">
    <?php esc_html_e( 'Application Submitted', 'civijobs' ); ?> ✅
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $candidate_name ) ); ?>
</p>
<p style="color:#475569;margin:0 0 16px">
    <?php esc_html_e( 'Your application has been successfully submitted for:', 'civijobs' ); ?>
</p>
<div style="background:#f8fafc;border-left:4px solid #4f46e5;padding:16px 20px;border-radius:0 8px 8px 0;margin-bottom:20px">
    <div style="font-weight:700;font-size:1.05rem;color:#1e293b"><?php echo esc_html( $job_title ); ?></div>
    <div style="color:#64748b;margin-top:4px"><?php echo esc_html( $company_name ); ?></div>
</div>
<p style="color:#475569;margin:0 0 20px">
    <?php esc_html_e( 'The employer will review your application and get in touch. You can track the status of your application in your dashboard.', 'civijobs' ); ?>
</p>
<div style="text-align:center;margin:28px 0;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="<?php echo esc_url( $job_url ); ?>"
       style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none">
        <?php esc_html_e( 'View Job', 'civijobs' ); ?>
    </a>
    <a href="<?php echo esc_url( $dashboard_url ); ?>"
       style="display:inline-block;background:#fff;color:#4f46e5;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none;border:1px solid #4f46e5">
        <?php esc_html_e( 'My Dashboard', 'civijobs' ); ?>
    </a>
</div>
<p style="color:#94a3b8;font-size:0.82rem;margin:0">
    <?php esc_html_e( 'Good luck with your application! 🤞', 'civijobs' ); ?>
</p>
