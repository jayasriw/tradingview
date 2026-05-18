<?php
/**
 * CiviJobs – Notification System
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
 * Add a notification for a user.
 *
 * @param int    $user_id User ID.
 * @param string $type    Notification type: application|message|meeting|job_approved|job_expired|package_activated|review|alert.
 * @param string $title   Short notification title.
 * @param string $message Notification body text.
 * @param string $link    Optional URL to link to.
 * @return int|false Inserted notification ID or false on failure.
 */
function civijobs_add_notification( int $user_id, string $type, string $title, string $message, string $link = '' ) {
	global $wpdb;

	$allowed_types = [ 'application', 'message', 'meeting', 'job_approved', 'job_expired', 'package_activated', 'review', 'alert' ];

	if ( ! in_array( $type, $allowed_types, true ) ) {
		$type = 'application';
	}

	$table = $wpdb->prefix . 'civi_notifications';

	$result = $wpdb->insert(
		$table,
		[
			'user_id'    => $user_id,
			'type'       => $type,
			'title'      => sanitize_text_field( $title ),
			'message'    => sanitize_textarea_field( $message ),
			'link'       => esc_url_raw( $link ),
			'is_read'    => 0,
			'created_at' => current_time( 'mysql' ),
		],
		[ '%d', '%s', '%s', '%s', '%s', '%d', '%s' ]
	);

	if ( false === $result ) {
		return false;
	}

	return (int) $wpdb->insert_id;
}

/**
 * Get notifications for a user.
 *
 * @param int $user_id User ID.
 * @param int $limit   Number of notifications to fetch.
 * @param int $offset  Offset for pagination.
 * @return array List of notification rows.
 */
function civijobs_get_notifications( int $user_id, int $limit = 20, int $offset = 0 ): array {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_notifications';

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d", // phpcs:ignore
			$user_id,
			$limit,
			$offset
		)
	);

	return $rows ?: [];
}

/**
 * Mark a single notification as read.
 *
 * @param int $notification_id Notification row ID.
 * @param int $user_id         Owning user ID (ownership check).
 * @return bool
 */
function civijobs_mark_notification_read( int $notification_id, int $user_id ): bool {
	global $wpdb;

	$table  = $wpdb->prefix . 'civi_notifications';
	$result = $wpdb->update(
		$table,
		[ 'is_read' => 1 ],
		[ 'id' => $notification_id, 'user_id' => $user_id ],
		[ '%d' ],
		[ '%d', '%d' ]
	);

	return false !== $result;
}

/**
 * Mark all notifications as read for a user.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function civijobs_mark_all_read( int $user_id ): bool {
	global $wpdb;

	$table  = $wpdb->prefix . 'civi_notifications';
	$result = $wpdb->update(
		$table,
		[ 'is_read' => 1 ],
		[ 'user_id' => $user_id, 'is_read' => 0 ],
		[ '%d' ],
		[ '%d', '%d' ]
	);

	return false !== $result;
}

/**
 * Delete all notifications for a user.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function civijobs_clear_notifications( int $user_id ): bool {
	global $wpdb;

	$table  = $wpdb->prefix . 'civi_notifications';
	$result = $wpdb->delete( $table, [ 'user_id' => $user_id ], [ '%d' ] );

	return false !== $result;
}

/**
 * Get the count of unread notifications for a user.
 *
 * @param int $user_id User ID.
 * @return int
 */
function civijobs_get_unread_notification_count( int $user_id ): int {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_notifications';

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND is_read = 0", // phpcs:ignore
			$user_id
		)
	);
}

// ============================================================
// AJAX Handlers
// ============================================================

/**
 * AJAX: fetch notifications for current user.
 */
function civijobs_ajax_get_notifications(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$user_id = get_current_user_id();
	$limit   = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 20;
	$offset  = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	$limit   = min( $limit, 100 ); // Safety cap.

	$notifications = civijobs_get_notifications( $user_id, $limit, $offset );
	$unread_count  = civijobs_get_unread_notification_count( $user_id );

	wp_send_json_success( [
		'notifications' => $notifications,
		'unread_count'  => $unread_count,
	] );
}
add_action( 'wp_ajax_civijobs_get_notifications', 'civijobs_ajax_get_notifications' );

/**
 * AJAX: mark a single notification as read.
 */
function civijobs_ajax_mark_notification_read(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$notification_id = isset( $_POST['notification_id'] ) ? absint( $_POST['notification_id'] ) : 0;

	if ( ! $notification_id ) {
		wp_send_json_error( [ 'message' => __( 'Notification ID required.', 'civijobs' ) ], 400 );
	}

	$success = civijobs_mark_notification_read( $notification_id, get_current_user_id() );

	if ( ! $success ) {
		wp_send_json_error( [ 'message' => __( 'Could not mark notification as read.', 'civijobs' ) ], 500 );
	}

	wp_send_json_success( [ 'message' => __( 'Notification marked as read.', 'civijobs' ) ] );
}
add_action( 'wp_ajax_civijobs_mark_notification_read', 'civijobs_ajax_mark_notification_read' );

/**
 * AJAX: mark all notifications as read.
 */
function civijobs_ajax_mark_all_notifications_read(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	civijobs_mark_all_read( get_current_user_id() );

	wp_send_json_success( [ 'message' => __( 'All notifications marked as read.', 'civijobs' ) ] );
}
add_action( 'wp_ajax_civijobs_mark_all_notifications_read', 'civijobs_ajax_mark_all_notifications_read' );

/**
 * AJAX: clear all notifications for current user.
 */
function civijobs_ajax_clear_notifications(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	civijobs_clear_notifications( get_current_user_id() );

	wp_send_json_success( [ 'message' => __( 'All notifications cleared.', 'civijobs' ) ] );
}
add_action( 'wp_ajax_civijobs_clear_notifications', 'civijobs_ajax_clear_notifications' );

/**
 * AJAX: get unread notification count (used by header bell icon).
 */
function civijobs_ajax_get_notification_count(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$count = civijobs_get_unread_notification_count( get_current_user_id() );

	wp_send_json_success( [ 'count' => $count ] );
}
add_action( 'wp_ajax_civijobs_get_notification_count', 'civijobs_ajax_get_notification_count' );
