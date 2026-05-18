<?php
/**
 * Email Template: Meeting Scheduled
 * Variables: $recipient_name, $other_party_name, $title, $date_formatted, $time_formatted, $location, $meeting_url, $dashboard_url
 *
 * @package CiviJobs
 */
?>
<h2 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 12px">
    📅 <?php esc_html_e( 'Meeting Scheduled', 'civijobs' ); ?>
</h2>
<p style="color:#475569;margin:0 0 16px">
    <?php printf( esc_html__( 'Hi %s,', 'civijobs' ), esc_html( $recipient_name ) ); ?>
</p>
<p style="color:#475569;margin:0 0 20px">
    <?php printf(
        esc_html__( 'A meeting has been scheduled with %s.', 'civijobs' ),
        '<strong>' . esc_html( $other_party_name ) . '</strong>'
    ); ?>
</p>
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:24px">
    <div style="font-size:1.1rem;font-weight:700;color:#1e293b;margin-bottom:16px"><?php echo esc_html( $title ); ?></div>
    <table style="width:100%;border-collapse:collapse">
        <tr>
            <td style="padding:8px 0;color:#64748b;font-size:0.875rem;width:120px">📅 <?php esc_html_e( 'Date', 'civijobs' ); ?></td>
            <td style="padding:8px 0;font-weight:600;color:#1e293b;font-size:0.875rem"><?php echo esc_html( $date_formatted ); ?></td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#64748b;font-size:0.875rem">🕒 <?php esc_html_e( 'Time', 'civijobs' ); ?></td>
            <td style="padding:8px 0;font-weight:600;color:#1e293b;font-size:0.875rem"><?php echo esc_html( $time_formatted ); ?></td>
        </tr>
        <?php if ( $location ) : ?>
            <tr>
                <td style="padding:8px 0;color:#64748b;font-size:0.875rem">📍 <?php esc_html_e( 'Location', 'civijobs' ); ?></td>
                <td style="padding:8px 0;font-weight:600;color:#1e293b;font-size:0.875rem"><?php echo esc_html( $location ); ?></td>
            </tr>
        <?php endif; ?>
        <?php if ( $meeting_url ) : ?>
            <tr>
                <td style="padding:8px 0;color:#64748b;font-size:0.875rem">🔗 <?php esc_html_e( 'Link', 'civijobs' ); ?></td>
                <td style="padding:8px 0;font-size:0.875rem">
                    <a href="<?php echo esc_url( $meeting_url ); ?>" style="color:#4f46e5;font-weight:600"><?php esc_html_e( 'Join Meeting', 'civijobs' ); ?></a>
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>
<div style="text-align:center;margin:20px 0;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <?php if ( $meeting_url ) : ?>
        <a href="<?php echo esc_url( $meeting_url ); ?>"
           style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none">
            <?php esc_html_e( 'Join Meeting', 'civijobs' ); ?>
        </a>
    <?php endif; ?>
    <a href="<?php echo esc_url( $dashboard_url ); ?>"
       style="display:inline-block;background:#fff;color:#4f46e5;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none;border:1px solid #4f46e5">
        <?php esc_html_e( 'View in Dashboard', 'civijobs' ); ?>
    </a>
</div>
