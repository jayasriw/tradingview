<?php
/**
 * Employer: Notifications
 *
 * @package CiviJobs
 */

global $wpdb;
$user_id       = get_current_user_id();
$notifications = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}civi_notifications WHERE user_id = %d ORDER BY created_at DESC LIMIT 50",
    $user_id
) );
$unread = array_filter( $notifications, fn( $n ) => ! $n->is_read );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Notifications', 'civijobs' ); ?></h1>
        <?php if ( $unread ) : ?>
            <p class="dash-subtitle"><?php printf( _n( '%d unread', '%d unread', count( $unread ), 'civijobs' ), count( $unread ) ); // phpcs:ignore ?></p>
        <?php endif; ?>
    </div>
    <?php if ( $notifications ) : ?>
        <button class="btn btn-outline" id="mark-all-read-btn">
            <?php esc_html_e( 'Mark All Read', 'civijobs' ); ?>
        </button>
    <?php endif; ?>
</div>

<?php if ( $notifications ) : ?>
    <div class="dash-card" style="padding:0">
        <?php foreach ( $notifications as $notif ) :
            $type_icons = [
                'application'  => '📋',
                'message'      => '✉️',
                'meeting'      => '📅',
                'job_approved' => '✅',
                'job_expired'  => '⏰',
                'package'      => '📦',
                'review'       => '⭐',
                'system'       => '🔔',
            ];
            $icon = $type_icons[ $notif->type ] ?? '🔔';
        ?>
            <div class="notification-row <?php echo ! $notif->is_read ? 'unread' : ''; ?>"
                 data-notification-id="<?php echo esc_attr( $notif->id ); ?>">
                <div class="notification-icon"><?php echo $icon; // phpcs:ignore ?></div>
                <div class="notification-content">
                    <div class="notification-message"><?php echo esc_html( $notif->message ); ?></div>
                    <div class="notification-time"><?php echo esc_html( civijobs_time_ago( $notif->created_at ) ); ?></div>
                </div>
                <?php if ( $notif->link ) : ?>
                    <a href="<?php echo esc_url( $notif->link ); ?>" class="notification-action">
                        <?php esc_html_e( 'View', 'civijobs' ); ?> →
                    </a>
                <?php endif; ?>
                <?php if ( ! $notif->is_read ) : ?>
                    <span class="notification-unread-dot"></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="empty-state">
        <div class="empty-state-icon">🔔</div>
        <div class="empty-state-title"><?php esc_html_e( 'No notifications yet', 'civijobs' ); ?></div>
        <div class="empty-state-text"><?php esc_html_e( "You'll see application updates, messages and more here.", 'civijobs' ); ?></div>
    </div>
<?php endif; ?>

<style>
.notification-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--color-gray-100);
    position: relative;
    transition: background .15s;
}
.notification-row:last-child { border-bottom: none; }
.notification-row.unread { background: #f5f3ff; }
.notification-row:hover { background: var(--color-gray-50); }
.notification-icon { font-size: 1.4rem; flex-shrink: 0; }
.notification-content { flex: 1; }
.notification-message { font-size: 0.875rem; color: var(--color-gray-800); }
.notification-time { font-size: 0.75rem; color: var(--color-gray-500); margin-top: 3px; }
.notification-action { font-size: 0.8rem; color: var(--color-primary); white-space: nowrap; flex-shrink: 0; }
.notification-unread-dot {
    width: 8px; height: 8px; background: var(--color-primary);
    border-radius: 50%; flex-shrink: 0;
}
</style>
