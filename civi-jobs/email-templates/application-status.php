<?php
/**
 * Email Template: Application Status Changed
 * Variables: $candidate_name, $job_title, $company_name, $status, $status_label, $dashboard_url
 *
 * @package CiviJobs
 */

$status_colors = [
    'shortlisted' => '#15803d',
    'interview'   => '#1d4ed8',
    'hired'       => '#7c3aed',
    'rejected'    => '#dc2626',
    'pending'     => '#c2410c',
];
$icon_map = [
    'shortlisted' => '⭐',
    'interview'   => '📅',
    'hired'       => '🎉',
    'rejected'    => '📭',
    'pending'     => '⏳',
];
$color = $status_colors[ $status ] ?? '#64748b';
$icon  = $icon_map[ $status ] ?? '🔔';
?>
<h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 12px">
    <?php echo $icon; // phpcs:ignore ?> <?php esc_html_e( 'Application Status Update', 'civijobs' ); ?>
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $candidate_name ) ); ?>
</p>
<p style="color:#475569;margin:0 0 16px">
    <?php esc_html_e( 'Your application status has been updated:', 'civijobs' ); ?>
</p>
<div style="background:#f8fafc;padding:20px;border-radius:8px;margin-bottom:20px;text-align:center">
    <div style="font-weight:700;font-size:1rem;color:#1e293b;margin-bottom:6px"><?php echo esc_html( $job_title ); ?></div>
    <div style="color:#64748b;font-size:0.875rem;margin-bottom:14px"><?php echo esc_html( $company_name ); ?></div>
    <span style="display:inline-block;background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>;padding:8px 20px;border-radius:20px;font-weight:700;font-size:0.95rem;border:1px solid <?php echo esc_attr( $color ); ?>33">
        <?php echo esc_html( $status_label ); ?>
    </span>
</div>
<?php if ( $status === 'shortlisted' ) : ?>
    <p style="color:#475569;margin:0 0 20px">
        <?php esc_html_e( "Great news! You've been shortlisted. The employer may reach out to schedule an interview soon.", 'civijobs' ); ?>
    </p>
<?php elseif ( $status === 'interview' ) : ?>
    <p style="color:#475569;margin:0 0 20px">
        <?php esc_html_e( "Congratulations! The employer wants to schedule an interview with you. Check your dashboard for details.", 'civijobs' ); ?>
    </p>
<?php elseif ( $status === 'hired' ) : ?>
    <p style="color:#475569;margin:0 0 20px">
        <?php esc_html_e( "🎉 Amazing! You have been selected for this position. The employer will be in contact with next steps.", 'civijobs' ); ?>
    </p>
<?php elseif ( $status === 'rejected' ) : ?>
    <p style="color:#475569;margin:0 0 20px">
        <?php esc_html_e( "Unfortunately, the employer has decided to move forward with other candidates. Don't give up — there are many other great opportunities waiting for you!", 'civijobs' ); ?>
    </p>
<?php endif; ?>
<div style="text-align:center;margin:24px 0">
    <a href="<?php echo esc_url( $dashboard_url ); ?>"
       style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 28px;border-radius:8px;font-weight:600;text-decoration:none">
        <?php esc_html_e( 'View My Applications', 'civijobs' ); ?>
    </a>
</div>
