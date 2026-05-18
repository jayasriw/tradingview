<?php
/**
 * CiviJobs – Job Application System
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
 * Submit a job application.
 *
 * @param int    $job_id       Post ID of the job.
 * @param int    $candidate_id User ID of the candidate.
 * @param string $cover_letter Cover letter text.
 * @param string $cv_file_url  URL of the uploaded CV.
 * @return int|false Application ID on success, false on failure.
 */
function civijobs_submit_application( int $job_id, int $candidate_id, string $cover_letter, string $cv_file_url ) {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_applications';

	$data = [
		'job_id'       => $job_id,
		'candidate_id' => $candidate_id,
		'cover_letter' => wp_kses_post( $cover_letter ),
		'cv_file_url'  => esc_url_raw( $cv_file_url ),
		'status'       => 'pending',
		'applied_at'   => current_time( 'mysql' ),
	];

	$formats = [ '%d', '%d', '%s', '%s', '%s', '%s' ];

	$result = $wpdb->insert( $table, $data, $formats );

	if ( false === $result ) {
		return false;
	}

	$application_id = (int) $wpdb->insert_id;

	// Notify the employer.
	$job        = get_post( $job_id );
	$employer_id = $job ? (int) $job->post_author : 0;

	if ( $employer_id ) {
		$candidate  = get_userdata( $candidate_id );
		$job_title  = get_the_title( $job_id );
		$name       = $candidate ? esc_html( $candidate->display_name ) : __( 'A candidate', 'civijobs' );

		civijobs_add_notification(
			$employer_id,
			'application',
			__( 'New Application Received', 'civijobs' ),
			/* translators: 1: candidate name, 2: job title */
			sprintf( __( '%1$s applied for %2$s.', 'civijobs' ), $name, $job_title ),
			admin_url( 'admin.php?page=civijobs-applications&job_id=' . $job_id )
		);
	}

	// Send email notifications.
	if ( function_exists( 'civijobs_email_application_submitted' ) ) {
		civijobs_email_application_submitted( $application_id );
	}

	/**
	 * Fires after an application is submitted.
	 *
	 * @param int $application_id Application ID.
	 * @param int $job_id         Job post ID.
	 * @param int $candidate_id   Candidate user ID.
	 */
	do_action( 'civijobs_application_submitted', $application_id, $job_id, $candidate_id );

	return $application_id;
}

/**
 * Get applications for a specific job.
 *
 * @param int   $job_id Job post ID.
 * @param array $args   Optional args: status, per_page, paged, orderby, order.
 * @return array{items: array, total: int, pages: int}
 */
function civijobs_get_applications( int $job_id, array $args = [] ): array {
	global $wpdb;

	$defaults = [
		'status'   => '',
		'per_page' => 20,
		'paged'    => 1,
		'orderby'  => 'applied_at',
		'order'    => 'DESC',
	];
	$args = wp_parse_args( $args, $defaults );

	$allowed_orderby = [ 'applied_at', 'status', 'candidate_id' ];
	$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'applied_at';
	$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
	$per_page        = max( 1, (int) $args['per_page'] );
	$offset          = ( max( 1, (int) $args['paged'] ) - 1 ) * $per_page;
	$table           = $wpdb->prefix . 'civi_applications';

	$where  = $wpdb->prepare( 'WHERE job_id = %d', $job_id );
	$params = [];

	if ( ! empty( $args['status'] ) ) {
		$where   .= $wpdb->prepare( ' AND status = %s', $args['status'] );
	}

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$table} {$where}"
	);

	$items = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
			$per_page,
			$offset
		)
	);
	// phpcs:enable

	return [
		'items' => $items ?: [],
		'total' => $total,
		'pages' => $per_page > 0 ? (int) ceil( $total / $per_page ) : 1,
	];
}

/**
 * Get all applications submitted by a candidate.
 *
 * @param int   $user_id Candidate user ID.
 * @param array $args    Optional args: status, per_page, paged, orderby, order.
 * @return array{items: array, total: int, pages: int}
 */
function civijobs_get_candidate_applications( int $user_id, array $args = [] ): array {
	global $wpdb;

	$defaults = [
		'status'   => '',
		'per_page' => 20,
		'paged'    => 1,
		'orderby'  => 'applied_at',
		'order'    => 'DESC',
	];
	$args = wp_parse_args( $args, $defaults );

	$allowed_orderby = [ 'applied_at', 'status', 'job_id' ];
	$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'applied_at';
	$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
	$per_page        = max( 1, (int) $args['per_page'] );
	$offset          = ( max( 1, (int) $args['paged'] ) - 1 ) * $per_page;
	$table           = $wpdb->prefix . 'civi_applications';

	$where = $wpdb->prepare( 'WHERE candidate_id = %d', $user_id );

	if ( ! empty( $args['status'] ) ) {
		$where .= $wpdb->prepare( ' AND status = %s', $args['status'] );
	}

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$table} {$where}"
	);

	$items = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
			$per_page,
			$offset
		)
	);
	// phpcs:enable

	return [
		'items' => $items ?: [],
		'total' => $total,
		'pages' => $per_page > 0 ? (int) ceil( $total / $per_page ) : 1,
	];
}

/**
 * Update the status of an application.
 *
 * @param int    $application_id Application ID.
 * @param string $status         New status: pending|reviewing|shortlisted|rejected|hired.
 * @param int    $employer_id    Employer user ID (ownership check).
 * @return bool True on success.
 */
function civijobs_update_application_status( int $application_id, string $status, int $employer_id ): bool {
	global $wpdb;

	$allowed_statuses = [ 'pending', 'reviewing', 'shortlisted', 'rejected', 'hired' ];
	if ( ! in_array( $status, $allowed_statuses, true ) ) {
		return false;
	}

	$table       = $wpdb->prefix . 'civi_applications';
	$application = civijobs_get_application( $application_id );

	if ( ! $application ) {
		return false;
	}

	// Verify the employer owns the job.
	$job = get_post( (int) $application->job_id );
	if ( ! $job || (int) $job->post_author !== $employer_id ) {
		return false;
	}

	$updated = $wpdb->update(
		$table,
		[ 'status' => $status, 'updated_at' => current_time( 'mysql' ) ],
		[ 'id' => $application_id ],
		[ '%s', '%s' ],
		[ '%d' ]
	);

	if ( false === $updated ) {
		return false;
	}

	// Notify the candidate.
	$status_labels = [
		'pending'     => __( 'Pending Review', 'civijobs' ),
		'reviewing'   => __( 'Under Review', 'civijobs' ),
		'shortlisted' => __( 'Shortlisted', 'civijobs' ),
		'rejected'    => __( 'Not Selected', 'civijobs' ),
		'hired'       => __( 'Hired', 'civijobs' ),
	];

	$job_title    = get_the_title( (int) $application->job_id );
	$status_label = $status_labels[ $status ] ?? ucfirst( $status );

	civijobs_add_notification(
		(int) $application->candidate_id,
		'application',
		__( 'Application Status Updated', 'civijobs' ),
		/* translators: 1: job title, 2: new status */
		sprintf( __( 'Your application for %1$s has been updated to: %2$s.', 'civijobs' ), $job_title, $status_label )
	);

	if ( function_exists( 'civijobs_email_application_status_changed' ) ) {
		civijobs_email_application_status_changed( $application_id );
	}

	do_action( 'civijobs_application_status_changed', $application_id, $status, $application );

	return true;
}

/**
 * Get a single application by ID.
 *
 * @param int $application_id Application ID.
 * @return object|null Application row or null.
 */
function civijobs_get_application( int $application_id ): ?object {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_applications';

	return $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $application_id ) // phpcs:ignore
	);
}

/**
 * Delete an application record.
 *
 * @param int $application_id Application ID.
 * @return bool True on success.
 */
function civijobs_delete_application( int $application_id ): bool {
	global $wpdb;

	$table  = $wpdb->prefix . 'civi_applications';
	$result = $wpdb->delete( $table, [ 'id' => $application_id ], [ '%d' ] );

	return false !== $result;
}

/**
 * Count applications for a job.
 *
 * @param int    $job_id Job post ID.
 * @param string $status Optional status filter.
 * @return int
 */
function civijobs_get_application_count( int $job_id, string $status = '' ): int {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_applications';

	if ( $status ) {
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE job_id = %d AND status = %s", // phpcs:ignore
				$job_id,
				$status
			)
		);
	}

	return (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE job_id = %d", $job_id ) // phpcs:ignore
	);
}

/**
 * Check if a candidate has already applied to a job.
 *
 * @param int $job_id       Job post ID.
 * @param int $candidate_id Candidate user ID.
 * @return bool
 */
function civijobs_already_applied( int $job_id, int $candidate_id ): bool {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_applications';

	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE job_id = %d AND candidate_id = %d", // phpcs:ignore
			$job_id,
			$candidate_id
		)
	);

	return $count > 0;
}

// ============================================================
// AJAX Handlers
// ============================================================

/**
 * AJAX: apply for a job.
 */
function civijobs_ajax_apply_job(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'You must be logged in to apply.', 'civijobs' ) ], 401 );
	}

	$user_id = get_current_user_id();
	$user    = get_userdata( $user_id );

	// Only candidates may apply.
	if ( ! $user || ! in_array( 'civi_candidate', (array) $user->roles, true ) ) {
		wp_send_json_error( [ 'message' => __( 'Only candidates can apply for jobs.', 'civijobs' ) ], 403 );
	}

	$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
	if ( ! $job_id || 'civi_job' !== get_post_type( $job_id ) ) {
		wp_send_json_error( [ 'message' => __( 'Invalid job.', 'civijobs' ) ], 400 );
	}

	if ( civijobs_already_applied( $job_id, $user_id ) ) {
		wp_send_json_error( [ 'message' => __( 'You have already applied for this job.', 'civijobs' ) ], 400 );
	}

	$cover_letter = isset( $_POST['cover_letter'] ) ? wp_kses_post( wp_unslash( $_POST['cover_letter'] ) ) : '';
	$cv_file_url  = '';

	// Handle optional CV upload.
	if ( ! empty( $_FILES['cv_file']['name'] ) ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$allowed_types = [ 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ];
		$file_type     = $_FILES['cv_file']['type'] ?? '';

		if ( ! in_array( $file_type, $allowed_types, true ) ) {
			wp_send_json_error( [ 'message' => __( 'Only PDF and DOC/DOCX files are allowed.', 'civijobs' ) ], 400 );
		}

		if ( $_FILES['cv_file']['size'] > 5 * MB_IN_BYTES ) {
			wp_send_json_error( [ 'message' => __( 'CV file must not exceed 5 MB.', 'civijobs' ) ], 400 );
		}

		$upload = wp_handle_upload( $_FILES['cv_file'], [ 'test_form' => false ] );

		if ( isset( $upload['error'] ) ) {
			wp_send_json_error( [ 'message' => $upload['error'] ], 400 );
		}

		$cv_file_url = $upload['url'];
	}

	$application_id = civijobs_submit_application( $job_id, $user_id, $cover_letter, $cv_file_url );

	if ( ! $application_id ) {
		wp_send_json_error( [ 'message' => __( 'Failed to submit application. Please try again.', 'civijobs' ) ], 500 );
	}

	wp_send_json_success( [
		'message'        => __( 'Application submitted successfully!', 'civijobs' ),
		'application_id' => $application_id,
	] );
}
add_action( 'wp_ajax_civijobs_apply_job', 'civijobs_ajax_apply_job' );

/**
 * AJAX: update application status (employer action).
 */
function civijobs_ajax_update_application_status(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$application_id = isset( $_POST['application_id'] ) ? absint( $_POST['application_id'] ) : 0;
	$status         = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';
	$employer_id    = get_current_user_id();

	if ( ! $application_id || ! $status ) {
		wp_send_json_error( [ 'message' => __( 'Missing required fields.', 'civijobs' ) ], 400 );
	}

	$success = civijobs_update_application_status( $application_id, $status, $employer_id );

	if ( ! $success ) {
		wp_send_json_error( [ 'message' => __( 'Failed to update application status.', 'civijobs' ) ], 403 );
	}

	wp_send_json_success( [ 'message' => __( 'Application status updated.', 'civijobs' ) ] );
}
add_action( 'wp_ajax_civijobs_update_application_status', 'civijobs_ajax_update_application_status' );

/**
 * AJAX: candidate withdraws their own application.
 */
function civijobs_ajax_withdraw_application(): void {
	check_ajax_referer( 'civijobs_ajax', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'civijobs' ) ], 401 );
	}

	$application_id = isset( $_POST['application_id'] ) ? absint( $_POST['application_id'] ) : 0;
	$user_id        = get_current_user_id();

	if ( ! $application_id ) {
		wp_send_json_error( [ 'message' => __( 'Invalid application.', 'civijobs' ) ], 400 );
	}

	$application = civijobs_get_application( $application_id );

	if ( ! $application || (int) $application->candidate_id !== $user_id ) {
		wp_send_json_error( [ 'message' => __( 'You cannot withdraw this application.', 'civijobs' ) ], 403 );
	}

	$deleted = civijobs_delete_application( $application_id );

	if ( ! $deleted ) {
		wp_send_json_error( [ 'message' => __( 'Failed to withdraw application.', 'civijobs' ) ], 500 );
	}

	do_action( 'civijobs_application_withdrawn', $application_id, $user_id );

	wp_send_json_success( [ 'message' => __( 'Application withdrawn successfully.', 'civijobs' ) ] );
}
add_action( 'wp_ajax_civijobs_withdraw_application', 'civijobs_ajax_withdraw_application' );
