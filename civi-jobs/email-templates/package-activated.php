<?php
/**
 * Email Template: Package Activated
 * Variables: $user_name, $package_name, $expires_at, $dashboard_url
 *
 * @package CiviJobs
 */
?>
<h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 12px">
    📦 <?php esc_html_e( 'Package Activated Successfully', 'civijobs' ); ?>
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $user_name ) ); ?>
</p>
<p style="color:#475569;margin:0 0 16px">
    <?php esc_html_e( 'Your membership package has been activated:', 'civijobs' ); ?>
</p>
<div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:12px;padding:24px;margin-bottom:20px;text-align:center">
    <div style="font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:6px"><?php echo esc_html( $package_name ); ?></div>
    <?php if ( $expires_at ) : ?>
        <div style="color:rgba(255,255,255,.8);font-size:0.875rem">
            <?php printf( esc_html__( 'Valid until: %s', 'civijobs' ), esc_html( $expires_at ) ); ?>
        </div>
    <?php else : ?>
        <div style="color:rgba(255,255,255,.8);font-size:0.875rem"><?php esc_html_e( 'Lifetime Access', 'civijobs' ); ?></div>
    <?php endif; ?>
</div>
<p style="color:#475569;margin:0 0 20px">
    <?php esc_html_e( 'Your package is now active. You can start using all the features included in your plan right away.', 'civijobs' ); ?>
</p>
<div style="text-align:center;margin:24px 0">
    <a href="<?php echo esc_url( $dashboard_url ); ?>"
       style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 28px;border-radius:8px;font-weight:600;text-decoration:none">
        <?php esc_html_e( 'Go to Dashboard', 'civijobs' ); ?>
    </a>
</div>
