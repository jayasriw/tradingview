<?php
/**
 * Notification System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_add_notification( int $user_id, string $type, string $message, string $link = '' ): int|false {
    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'civi_notifications',
        [
            'user_id'    => $user_id,
            'type'       => sanitize_key( $type ),
            'message'    => sanitize_text_field( $message ),
            'link'       => esc_url_raw( $link ),
            'is_read'    => 0,
            'created_at' => current_time( 'mysql' ),
        ],
        [ '%d', '%s', '%s', '%s', '%d', '%s' ]
    );
    return $result ? $wpdb->insert_id : false;
}

function jobportal_get_notifications( int $user_id, int $limit = 20 ): array {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}civi_notifications WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
        $user_id, $limit
    ) ) ?: [];
}

function jobportal_mark_notification_read( int $notification_id, int $user_id ): bool {
    global $wpdb;
    return (bool) $wpdb->update(
        $wpdb->prefix . 'civi_notifications',
        [ 'is_read' => 1 ],
        [ 'id' => $notification_id, 'user_id' => $user_id ],
        [ '%d' ],
        [ '%d', '%d' ]
    );
}

function jobportal_mark_all_read( int $user_id ): void {
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'civi_notifications',
        [ 'is_read' => 1 ],
        [ 'user_id' => $user_id, 'is_read' => 0 ],
        [ '%d' ],
        [ '%d', '%d' ]
    );
}

function jobportal_get_unread_notification_count( int $user_id ): int {
    return jobportal_get_unread_notifications( $user_id );
}

// AJAX handlers
add_action( 'wp_ajax_jobportal_get_notifications',        'jobportal_ajax_get_notifications' );
add_action( 'wp_ajax_jobportal_mark_notification_read',   'jobportal_ajax_mark_notification_read' );
add_action( 'wp_ajax_jobportal_mark_all_notifications_read', 'jobportal_ajax_mark_all_notifications_read' );
add_action( 'wp_ajax_jobportal_get_notification_count',   'jobportal_ajax_get_notification_count' );

function jobportal_ajax_get_notifications(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) wp_send_json_error();
    wp_send_json_success( jobportal_get_notifications( $user_id ) );
}

function jobportal_ajax_mark_notification_read(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    $notif_id = (int) ( $_POST['notification_id'] ?? 0 );
    jobportal_mark_notification_read( $notif_id, $user_id );
    wp_send_json_success();
}

function jobportal_ajax_mark_all_notifications_read(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) wp_send_json_error();
    jobportal_mark_all_read( $user_id );
    wp_send_json_success();
}

function jobportal_ajax_get_notification_count(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    wp_send_json_success( [ 'count' => jobportal_get_unread_notification_count( $user_id ) ] );
}
