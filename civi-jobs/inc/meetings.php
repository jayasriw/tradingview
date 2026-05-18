<?php
/**
 * CiviJobs – Meeting Scheduling System
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
 * Schedule a meeting between an employer and a candidate.
 *
 * @param int    $employer_id  Employer user ID.
 * @param int    $candidate_id Candidate user ID.
 * @param int    $job_id       Related job post ID (0 if not job-specific).
 * @param string $title        Meeting title.
 * @param string $description  Meeting description.
 * @param string $date_time    Date and time string (Y-m-d H:i:s).
 * @param string $location     Physical location (optional).
 * @param string $meeting_url  Video call URL (optional).
 * @return int|false Meeting ID on success, false on failure.
 */
function civijobs_schedule_meeting( int $employer_id, int $candidate_id, int $job_id, string $title, string $description, string $date_time, string $location = '', string $meeting_url = '' ) {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_meetings';

	// Validate date/time.
	$dt = date_create( $date_time );
	if ( ! $dt ) {
		return false;
	}
	$normalized_dt = date_format( $dt, 'Y-m-d H:i:s' );

	$result = $wpdb->insert(
		$table,
		[
			'employer_id'  => $employer_id,
			'candidate_id' => $candidate_id,
			'job_id'       => $job_id,
			'title'        => sanitize_text_field( $title ),
			'description'  => wp_kses_post( $description ),
			'date_time'    => $normalized_dt,
			'location'     => sanitize_text_field( $location ),
			'meeting_url'  => esc_url_raw( $meeting_url ),
			'status'       => 'pending',
			'created_at'   => current_time( 'mysql' ),
		],
		[ '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
	);

	if ( false === $result ) {
		return false;
	}

	$meeting_id = (int) $wpdb->insert_id;

	// Notify the candidate.
	$employer     = get_userdata( $employer_id );
	$employer_name = $employer ? esc_html( $employer->display_name ) : __( 'An employer', 'civijobs' );

	civijobs_add_notification(
		$candidate_id,
		'meeting',
		__( 'Meeting Scheduled', 'civijobs' ),
		/* translators: 1: employer name, 2: meeting title */
		sprintf( __( '%1$s has scheduled a meeting: %2$s.', 'civijobs' ), $employer_name, sanitize_text_field( $title ) ),
		home_url( '/meetings/?meeting_id=' . $meeting_id )
	);

	if ( function_exists( 'civijobs_email_meeting_scheduled' ) ) {
		civijobs_email_meeting_scheduled( $meeting_id );
	}

	do_action( 'civijobs_meeting_scheduled', $meeting_id, $employer_id, $candidate_id );

	return $meeting_id;
}

/**
 * Get meetings for a user.
 *
 * @param int    $user_id User ID.
 * @param string $role    'employer', 'candidate', or 'both'.
 * @return array List of meeting rows.
 */
function civijobs_get_meetings( int $user_id, string $role = 'both' ): array {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_meetings';

	if ( 'employer' === $role ) {
		$where  = $wpdb->prepare( 'WHERE employer_id = %d', $user_id );
	} elseif ( 'candidate' === $role ) {
		$where  = $wpdb->prepare( 'WHERE candidate_id = %d', $user_id );
	} else {
		$where  = $wpdb->prepare( 'WHERE employer_id = %d OR candidate_id = %d', $user_id, $user_id );
	}

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		"SELECT * FROM {$table} {$where} ORDER BY date_time DESC"
	);
	// phpcs:enable

	return $rows ?: [];
}

/**
 * Update the status of a meeting (confirm or cancel).
 *
 * @param int    $meeting_id Meeting row ID.
 * @param string $status     New status: 'confirmed', 'cancelled', 'completed'.
 * @param int    $user_id    User performing the action (must be a participant).
 * @return bool True on success.
 */
function civijobs_update_meeting_status( int $meeting_id, string $status, int $user_id ): bool {
	global $wpdb;

	$allowed_statuses = [ 'pending', 'confirmed', 'cancelled', 'completed' ];
	if ( ! in_array( $status, $allowed_statuses, true ) ) {
		return false;
	}

	$meeting = civijobs_get_meeting( $meeting_id );
	if ( ! $meeting ) {
		return false;
	}

	// Only participants can update status.
	if ( (int) $meeting->employer_id !== $user_id && (int) $meeting->candidate_id !== $user_id ) {
		return false;
	}

	$table  = $wpdb->prefix . 'civi_meetings';
	$result = $wpdb->update(
		$table,
		[ 'status' => $status, 'updated_at' => current_time( 'mysql' ) ],
		[ 'id' => $meeting_id ],
		[ '%s', '%s' ],
		[ '%d' ]
	);

	if ( false === $result ) {
		return false;
	}

	// Notify the other participant.
	$notify_id = ( (int) $meeting->employer_id === $user_id ) ? (int) $meeting->candidate_id : (int) $meeting->employer_id;

	$status_labels = [
		'confirmed'  => __( 'Confirmed', 'civijobs' ),
		'cancelled'  => __( 'Cancelled', 'civijobs' ),
		'completed'  => __( 'Completed', 'civijobs' ),
	];
	$status_label = $status_labels[ $status ] ?? ucfirst( $status );

	civijobs_add_notification(
		$notify_id,
		'meeting',
		__( 'Meeting Status Updated', 'civijobs' ),
		/* translators: 1: meeting title, 2: new status */
		sprintf( __( 'Meeting "%1$s" has been marked as: %2$s.', 'civijobs' ), esc_html( $meeting->title ), $status_label ),
		home_url( '/meetings/?meeting_id=' . $meeting_id )
	);

	if ( function_exists( 'civijobs_email_meeting_status' ) ) {
		civijobs_email_meeting_status( $meeting_id );
	}

	do_action( 'civijobs_meeting_status_updated', $meeting_id, $status, $user_id );

	return true;
}

/**
 * Get a single meeting by ID.
 *
 * @param int $meeting_id Meeting row ID.
 * @return object|null Meeting row or null.
 */
function civijobs_get_meeting( int $meeting_id ): ?object {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_meetings';

	return $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $meeting_id ) // phpcs:ignore
	);
}

// ============================================================
// AJAX Handlers
// ============================================================

/**
 * AJAX: schedule a new meeting (employer action).
 */
function civijobs_ajax_schedule_meeting(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$user    = wp_get_current_user();
	$user_id = (int) $user->ID;

	// Employers schedule meetings.
	if ( ! in_array( 'civi_employer', (array) $user->roles, true ) && ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Only employers can schedule meetings.', 'civijobs' ) ], 403 );
	}

	$candidate_id = isset( $_POST['candidate_id'] ) ? absint( $_POST['candidate_id'] ) : 0;
	$job_id       = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
	$title        = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	$description  = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
	$date_time    = isset( $_POST['date_time'] ) ? sanitize_text_field( wp_unslash( $_POST['date_time'] ) ) : '';
	$location     = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
	$meeting_url  = isset( $_POST['meeting_url'] ) ? esc_url_raw( wp_unslash( $_POST['meeting_url'] ) ) : '';

	if ( ! $candidate_id || ! $title || ! $date_time ) {
		wp_send_json_error( [ 'message' => __( 'Candidate, title, and date/time are required.', 'civijobs' ) ], 400 );
	}

	if ( ! get_userdata( $candidate_id ) ) {
		wp_send_json_error( [ 'message' => __( 'Invalid candidate.', 'civijobs' ) ], 400 );
	}

	$meeting_id = civijobs_schedule_meeting( $user_id, $candidate_id, $job_id, $title, $description, $date_time, $location, $meeting_url );

	if ( ! $meeting_id ) {
		wp_send_json_error( [ 'message' => __( 'Failed to schedule meeting.', 'civijobs' ) ], 500 );
	}

	wp_send_json_success( [
		'meeting_id' => $meeting_id,
		'message'    => __( 'Meeting scheduled successfully.', 'civijobs' ),
	] );
}
add_action( 'wp_ajax_civijobs_schedule_meeting', 'civijobs_ajax_schedule_meeting' );

/**
 * AJAX: update meeting status.
 */
function civijobs_ajax_update_meeting_status(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$meeting_id = isset( $_POST['meeting_id'] ) ? absint( $_POST['meeting_id'] ) : 0;
	$status     = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';

	if ( ! $meeting_id || ! $status ) {
		wp_send_json_error( [ 'message' => __( 'Meeting ID and status are required.', 'civijobs' ) ], 400 );
	}

	$success = civijobs_update_meeting_status( $meeting_id, $status, get_current_user_id() );

	if ( ! $success ) {
		wp_send_json_error( [ 'message' => __( 'Failed to update meeting status.', 'civijobs' ) ], 403 );
	}

	wp_send_json_success( [ 'message' => __( 'Meeting status updated.', 'civijobs' ) ] );
}
add_action( 'wp_ajax_civijobs_update_meeting_status', 'civijobs_ajax_update_meeting_status' );

/**
 * AJAX: get meetings for the current user.
 */
function civijobs_ajax_get_meetings(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$user_id = get_current_user_id();
	$role    = isset( $_POST['role'] ) ? sanitize_key( $_POST['role'] ) : 'both';

	$meetings = civijobs_get_meetings( $user_id, $role );

	wp_send_json_success( [ 'meetings' => $meetings ] );
}
add_action( 'wp_ajax_civijobs_get_meetings', 'civijobs_ajax_get_meetings' );
