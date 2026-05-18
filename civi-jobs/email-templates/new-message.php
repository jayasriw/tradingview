<?php
/**
 * Email Template: New Message
 * Variables: $recipient_name, $sender_name, $message_preview, $inbox_url
 *
 * @package CiviJobs
 */
?>
<h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 12px">
    ✉️ <?php esc_html_e( 'You have a new message', 'civijobs' ); ?>
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $recipient_name ) ); ?>
</p>
<p style="color:#475569;margin:0 0 16px">
    <?php printf(
        esc_html__( '%s sent you a message:', 'civijobs' ),
        '<strong>' . esc_html( $sender_name ) . '</strong>'
    ); ?>
</p>
<div style="background:#f8fafc;border-left:4px solid #4f46e5;padding:16px 20px;border-radius:0 8px 8px 0;margin-bottom:20px;font-style:italic;color:#475569">
    <?php echo esc_html( $message_preview ); ?>
</div>
<div style="text-align:center;margin:24px 0">
    <a href="<?php echo esc_url( $inbox_url ); ?>"
       style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 28px;border-radius:8px;font-weight:600;text-decoration:none">
        <?php esc_html_e( 'Reply in Dashboard', 'civijobs' ); ?>
    </a>
</div>
<p style="color:#94a3b8;font-size:0.82rem;margin:0">
    <?php esc_html_e( 'You can manage your notification preferences from your account settings.', 'civijobs' ); ?>
</p>
