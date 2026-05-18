<?php
/**
 * Private Messaging System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_send_message( int $sender_id, int $receiver_id, string $message ): int|false {
    global $wpdb;
    $thread_id = jobportal_get_or_create_thread( $sender_id, $receiver_id );

    $result = $wpdb->insert(
        $wpdb->prefix . 'jp_messages',
        [
            'thread_id'   => $thread_id,
            'sender_id'   => $sender_id,
            'receiver_id' => $receiver_id,
            'message'     => sanitize_textarea_field( $message ),
            'is_read'     => 0,
            'created_at'  => current_time( 'mysql' ),
        ],
        [ '%s', '%d', '%d', '%s', '%d', '%s' ]
    );

    if ( $result ) {
        $sender = get_userdata( $sender_id );
        jobportal_add_notification( $receiver_id, 'message',
            sprintf( __( 'New message from %s', 'jobportal' ), $sender->display_name )
        );
        do_action( 'jobportal_message_sent', $wpdb->insert_id, $sender_id, $receiver_id );
        return $wpdb->insert_id;
    }
    return false;
}

function jobportal_get_or_create_thread( int $user1, int $user2 ): string {
    $ids = [ $user1, $user2 ];
    sort( $ids );
    return implode( '_', $ids );
}

function jobportal_get_conversations( int $user_id ): array {
    return jobportal_get_message_threads( $user_id );
}

function jobportal_get_thread_messages( string $thread_id, int $user_id ): array {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT m.*, u.display_name FROM {$wpdb->prefix}jp_messages m LEFT JOIN {$wpdb->users} u ON u.ID = m.sender_id WHERE m.thread_id = %s ORDER BY m.created_at ASC",
        $thread_id
    ) ) ?: [];
}

function jobportal_mark_thread_read( string $thread_id, int $user_id ): void {
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'jp_messages',
        [ 'is_read' => 1 ],
        [ 'thread_id' => $thread_id, 'receiver_id' => $user_id ],
        [ '%d' ],
        [ '%s', '%d' ]
    );
}

function jobportal_get_unread_count( int $user_id ): int {
    return jobportal_get_unread_messages( $user_id );
}

// AJAX: Send message
add_action( 'wp_ajax_jobportal_send_message', 'jobportal_ajax_send_message' );

function jobportal_ajax_send_message(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $sender_id   = get_current_user_id();
    $receiver_id = (int) ( $_POST['receiver_id'] ?? 0 );
    $message     = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( ! $sender_id || ! $receiver_id || ! $message ) {
        wp_send_json_error( __( 'Invalid request.', 'jobportal' ) );
    }

    $result = jobportal_send_message( $sender_id, $receiver_id, $message );
    if ( $result ) {
        wp_send_json_success( [ 'message_id' => $result ] );
    } else {
        wp_send_json_error( __( 'Failed to send message.', 'jobportal' ) );
    }
}

// AJAX: Get messages
add_action( 'wp_ajax_jobportal_get_messages', 'jobportal_ajax_get_messages' );

function jobportal_ajax_get_messages(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id   = get_current_user_id();
    $thread_id = sanitize_text_field( $_POST['thread_id'] ?? '' );

    if ( ! $user_id || ! $thread_id ) wp_send_json_error();

    $messages = jobportal_get_thread_messages( $thread_id, $user_id );
    jobportal_mark_thread_read( $thread_id, $user_id );
    wp_send_json_success( $messages );
}

// AJAX: Get conversations
add_action( 'wp_ajax_jobportal_get_conversations', 'jobportal_ajax_get_conversations' );

function jobportal_ajax_get_conversations(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) wp_send_json_error();
    wp_send_json_success( jobportal_get_conversations( $user_id ) );
}
