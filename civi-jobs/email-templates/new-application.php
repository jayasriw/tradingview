<?php
/**
 * Email Template: New Application (sent to employer)
 * Variables: $employer_name, $candidate_name, $job_title, $applications_url, $candidate_cv
 *
 * @package CiviJobs
 */
?>
<h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 12px">
    <?php esc_html_e( 'New Job Application Received', 'civijobs' ); ?> 📋
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $employer_name ) ); ?>
</p>
<p style="color:#475569;margin:0 0 16px">
    <?php printf(
        /* translators: 1: candidate name, 2: job title */
        esc_html__( '%1$s has applied for the position of %2$s.', 'civijobs' ),
        '<strong>' . esc_html( $candidate_name ) . '</strong>',
        '<strong>' . esc_html( $job_title ) . '</strong>'
    ); ?>
</p>
<?php if ( isset( $cover_letter ) && $cover_letter ) : ?>
    <div style="background:#f8fafc;padding:16px 20px;border-radius:8px;margin-bottom:20px">
        <div style="font-weight:600;font-size:0.85rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px">
            <?php esc_html_e( 'Cover Letter', 'civijobs' ); ?>
        </div>
        <p style="color:#334155;line-height:1.7;margin:0"><?php echo esc_html( wp_trim_words( $cover_letter, 60 ) ); ?></p>
    </div>
<?php endif; ?>
<div style="text-align:center;margin:28px 0;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="<?php echo esc_url( $applications_url ); ?>"
       style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none">
        <?php esc_html_e( 'View Application', 'civijobs' ); ?>
    </a>
    <?php if ( $candidate_cv ) : ?>
        <a href="<?php echo esc_url( $candidate_cv ); ?>"
           style="display:inline-block;background:#fff;color:#4f46e5;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none;border:1px solid #4f46e5">
            <?php esc_html_e( 'Download CV', 'civijobs' ); ?>
        </a>
    <?php endif; ?>
</div>
<p style="color:#94a3b8;font-size:0.82rem;margin:0">
    <?php esc_html_e( 'You can manage all your applications from the employer dashboard.', 'civijobs' ); ?>
</p>
