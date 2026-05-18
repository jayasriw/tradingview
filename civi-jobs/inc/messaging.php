<?php
/**
 * CiviJobs – Private Messaging System
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================================
// Core Functions
// ============================================================

/**
 * Send a private message between users.
 *
 * @param int    $sender_id   Sender user ID.
 * @param int    $receiver_id Receiver user ID.
 * @param string $subject     Message subject.
 * @param string $body        Message body.
 * @param int    $job_id      Optional related job ID.
 * @return int|false Inserted message ID or false on failure.
 */
function civijobs_send_message( int $sender_id, int $receiver_id, string $subject, string $body, int $job_id = 0 ) {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_messages';

	// Determine thread_id: reuse existing thread for same participant pair + optional job.
	$thread_id = civijobs_get_or_create_thread( $sender_id, $receiver_id, $job_id );

	$data = [
		'thread_id'   => $thread_id,
		'sender_id'   => $sender_id,
		'receiver_id' => $receiver_id,
		'subject'     => sanitize_text_field( $subject ),
		'body'        => wp_kses_post( $body ),
		'job_id'      => $job_id,
		'is_read'     => 0,
		'deleted_by'  => '',
		'sent_at'     => current_time( 'mysql' ),
	];

	$formats = [ '%s', '%d', '%d', '%s', '%s', '%d', '%d', '%s', '%s' ];

	$result = $wpdb->insert( $table, $data, $formats );

	if ( false === $result ) {
		return false;
	}

	$message_id = (int) $wpdb->insert_id;

	// Notify the receiver.
	$sender     = get_userdata( $sender_id );
	$sender_name = $sender ? esc_html( $sender->display_name ) : __( 'Someone', 'civijobs' );

	civijobs_add_notification(
		$receiver_id,
		'message',
		__( 'New Message', 'civijobs' ),
		/* translators: %s: sender display name */
		sprintf( __( 'You have a new message from %s.', 'civijobs' ), $sender_name ),
		home_url( '/messages/?thread=' . $thread_id )
	);

	if ( function_exists( 'civijobs_email_new_message' ) ) {
		civijobs_email_new_message( $message_id );
	}

	do_action( 'civijobs_message_sent', $message_id, $sender_id, $receiver_id );

	return $message_id;
}

/**
 * Get or create a thread_id for a participant pair + optional job.
 *
 * Threads are identified by a deterministic string key stored in the first
 * message of the conversation.
 *
 * @param int $sender_id   Sender user ID.
 * @param int $receiver_id Receiver user ID.
 * @param int $job_id      Optional job ID.
 * @return string Thread ID (unique key).
 */
function civijobs_get_or_create_thread( int $sender_id, int $receiver_id, int $job_id = 0 ): string {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_messages';

	// Build a canonical pair key (lower ID first for consistency).
	$low  = min( $sender_id, $receiver_id );
	$high = max( $sender_id, $receiver_id );

	$existing = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT thread_id FROM {$table} WHERE (( sender_id = %d AND receiver_id = %d ) OR ( sender_id = %d AND receiver_id = %d )) AND job_id = %d LIMIT 1", // phpcs:ignore
			$low,
			$high,
			$high,
			$low,
			$job_id
		)
	);

	if ( $existing ) {
		return $existing;
	}

	return 'thread_' . $low . '_' . $high . '_' . $job_id . '_' . time();
}

/**
 * Get a list of conversations for a user (grouped by thread_id, last message per thread).
 *
 * @param int $user_id User ID.
 * @return array List of conversation rows.
 */
function civijobs_get_conversations( int $user_id ): array {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_messages';

	// Latest message per thread where user is participant and has not deleted the thread.
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT m.* FROM {$table} m
			 INNER JOIN (
			     SELECT thread_id, MAX(sent_at) AS latest
			     FROM {$table}
			     WHERE ( sender_id = %d OR receiver_id = %d )
			       AND FIND_IN_SET( %d, deleted_by ) = 0
			     GROUP BY thread_id
			 ) grouped ON m.thread_id = grouped.thread_id AND m.sent_at = grouped.latest
			 WHERE ( m.sender_id = %d OR m.receiver_id = %d )
			 ORDER BY m.sent_at DESC", // phpcs:ignore
			$user_id,
			$user_id,
			$user_id,
			$user_id,
			$user_id
		)
	);

	return $rows ?: [];
}

/**
 * Get all messages within a thread, mark them as read for the current viewer.
 *
 * @param string $thread_id Thread ID string.
 * @param int    $user_id   Viewing user ID.
 * @return array List of message rows.
 */
function civijobs_get_thread_messages( string $thread_id, int $user_id ): array {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_messages';

	$messages = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table}
			 WHERE thread_id = %s
			   AND ( sender_id = %d OR receiver_id = %d )
			   AND FIND_IN_SET( %d, deleted_by ) = 0
			 ORDER BY sent_at ASC", // phpcs:ignore
			$thread_id,
			$user_id,
			$user_id,
			$user_id
		)
	);

	// Mark as read.
	civijobs_mark_thread_read( $thread_id, $user_id );

	return $messages ?: [];
}

/**
 * Mark all messages in a thread as read for the given user.
 *
 * @param string $thread_id Thread ID.
 * @param int    $user_id   Reader user ID.
 * @return bool
 */
function civijobs_mark_thread_read( string $thread_id, int $user_id ): bool {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_messages';

	$result = $wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table} SET is_read = 1 WHERE thread_id = %s AND receiver_id = %d AND is_read = 0", // phpcs:ignore
			$thread_id,
			$user_id
		)
	);

	return false !== $result;
}

/**
 * Count unread messages for a user.
 *
 * @param int $user_id User ID.
 * @return int
 */
function civijobs_get_unread_count( int $user_id ): int {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_messages';

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE receiver_id = %d AND is_read = 0 AND FIND_IN_SET( %d, deleted_by ) = 0", // phpcs:ignore
			$user_id,
			$user_id
		)
	);
}

/**
 * Soft-delete a message for a user (adds user ID to deleted_by CSV).
 *
 * @param int $message_id Message row ID.
 * @param int $user_id    User requesting the deletion.
 * @return bool
 */
function civijobs_delete_message( int $message_id, int $user_id ): bool {
	global $wpdb;

	$table   = $wpdb->prefix . 'civi_messages';
	$message = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $message_id ) // phpcs:ignore
	);

	if ( ! $message ) {
		return false;
	}

	if ( (int) $message->sender_id !== $user_id && (int) $message->receiver_id !== $user_id ) {
		return false;
	}

	$deleted_by = array_filter( explode( ',', (string) $message->deleted_by ) );
	if ( ! in_array( (string) $user_id, $deleted_by, true ) ) {
		$deleted_by[] = (string) $user_id;
	}

	$result = $wpdb->update(
		$table,
		[ 'deleted_by' => implode( ',', $deleted_by ) ],
		[ 'id' => $message_id ],
		[ '%s' ],
		[ '%d' ]
	);

	return false !== $result;
}

// ============================================================
// AJAX Handlers
// ============================================================

/**
 * AJAX: send a message.
 */
function civijobs_ajax_send_message(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$sender_id   = get_current_user_id();
	$receiver_id = isset( $_POST['receiver_id'] ) ? absint( $_POST['receiver_id'] ) : 0;
	$subject     = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$body        = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
	$job_id      = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;

	if ( ! $receiver_id || ! $body ) {
		wp_send_json_error( [ 'message' => __( 'Receiver and message body are required.', 'civijobs' ) ], 400 );
	}

	if ( $receiver_id === $sender_id ) {
		wp_send_json_error( [ 'message' => __( 'You cannot message yourself.', 'civijobs' ) ], 400 );
	}

	if ( ! get_userdata( $receiver_id ) ) {
		wp_send_json_error( [ 'message' => __( 'Invalid recipient.', 'civijobs' ) ], 400 );
	}

	$message_id = civijobs_send_message( $sender_id, $receiver_id, $subject, $body, $job_id );

	if ( ! $message_id ) {
		wp_send_json_error( [ 'message' => __( 'Failed to send message.', 'civijobs' ) ], 500 );
	}

	wp_send_json_success( [
		'message_id' => $message_id,
		'message'    => __( 'Message sent.', 'civijobs' ),
	] );
}
add_action( 'wp_ajax_civijobs_send_message', 'civijobs_ajax_send_message' );

/**
 * AJAX: get messages for a thread.
 */
function civijobs_ajax_get_messages(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$thread_id = isset( $_POST['thread_id'] ) ? sanitize_text_field( wp_unslash( $_POST['thread_id'] ) ) : '';

	if ( ! $thread_id ) {
		wp_send_json_error( [ 'message' => __( 'Thread ID required.', 'civijobs' ) ], 400 );
	}

	$user_id  = get_current_user_id();
	$messages = civijobs_get_thread_messages( $thread_id, $user_id );

	wp_send_json_success( [ 'messages' => $messages ] );
}
add_action( 'wp_ajax_civijobs_get_messages', 'civijobs_ajax_get_messages' );

/**
 * AJAX: get conversations list for current user.
 */
function civijobs_ajax_get_conversations(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$user_id       = get_current_user_id();
	$conversations = civijobs_get_conversations( $user_id );

	wp_send_json_success( [ 'conversations' => $conversations ] );
}
add_action( 'wp_ajax_civijobs_get_conversations', 'civijobs_ajax_get_conversations' );

/**
 * AJAX: mark messages in a thread as read.
 */
function civijobs_ajax_mark_messages_read(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$thread_id = isset( $_POST['thread_id'] ) ? sanitize_text_field( wp_unslash( $_POST['thread_id'] ) ) : '';

	if ( ! $thread_id ) {
		wp_send_json_error( [ 'message' => __( 'Thread ID required.', 'civijobs' ) ], 400 );
	}

	$user_id = get_current_user_id();
	civijobs_mark_thread_read( $thread_id, $user_id );

	wp_send_json_success( [ 'message' => __( 'Messages marked as read.', 'civijobs' ) ] );
}
add_action( 'wp_ajax_civijobs_mark_messages_read', 'civijobs_ajax_mark_messages_read' );
