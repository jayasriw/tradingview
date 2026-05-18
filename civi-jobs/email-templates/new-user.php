<?php
/**
 * Email Template: New User Welcome
 * Variables: $user_name, $role, $login_url
 *
 * @package CiviJobs
 */
?>
<h2 style="font-size:1.5rem;font-weight:700;color:#1e293b;margin:0 0 12px">
    <?php esc_html_e( 'Welcome to CiviJobs!', 'civijobs' ); ?> 🎉
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $user_name ) ); ?>
</p>
<p style="color:#475569;margin:0 0 16px">
    <?php esc_html_e( "Your account has been created successfully. We're excited to have you on board!", 'civijobs' ); ?>
</p>
<?php if ( $role === 'civi_employer' ) : ?>
    <p style="color:#475569;margin:0 0 20px">
        <?php esc_html_e( "As an employer, you can now post jobs, browse candidates, and manage applications all from your dashboard.", 'civijobs' ); ?>
    </p>
<?php else : ?>
    <p style="color:#475569;margin:0 0 20px">
        <?php esc_html_e( "As a job seeker, you can now browse thousands of jobs, save your favorites, and apply with a single click.", 'civijobs' ); ?>
    </p>
<?php endif; ?>
<div style="text-align:center;margin:28px 0">
    <a href="<?php echo esc_url( $login_url ); ?>"
       style="display:inline-block;background:#4f46e5;color:#fff;padding:14px 32px;border-radius:8px;font-weight:700;text-decoration:none;font-size:1rem">
        <?php esc_html_e( 'Go to Your Dashboard', 'civijobs' ); ?>
    </a>
</div>
<p style="color:#94a3b8;font-size:0.875rem;margin:0">
    <?php esc_html_e( 'If you have any questions, just reply to this email — we are always happy to help.', 'civijobs' ); ?>
</p>
