<?php
/**
 * User roles and capabilities
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register custom user roles on theme activation.
 */
function civijobs_register_roles(): void {
    add_role( 'civi_employer', __( 'Employer', 'civijobs' ), [
        'read'          => true,
        'upload_files'  => true,
        'edit_posts'    => false,
        'publish_posts' => false,
        // Custom caps
        'post_jobs'          => true,
        'manage_company'     => true,
        'view_applications'  => true,
        'message_candidates' => true,
        'schedule_meetings'  => true,
    ] );

    add_role( 'civi_candidate', __( 'Candidate', 'civijobs' ), [
        'read'         => true,
        'upload_files' => true,
        // Custom caps
        'apply_jobs'    => true,
        'manage_resume' => true,
        'post_services' => true,
        'save_jobs'     => true,
    ] );
}
add_action( 'after_switch_theme', 'civijobs_register_roles' );

/**
 * Remove custom roles on theme deactivation.
 */
function civijobs_remove_roles(): void {
    remove_role( 'civi_employer' );
    remove_role( 'civi_candidate' );
}
add_action( 'switch_theme', 'civijobs_remove_roles' );

/**
 * Get the CiviJobs role for a user.
 *
 * @param int $user_id
 * @return string 'employer' | 'candidate' | 'admin' | ''
 */
function civijobs_get_user_role( int $user_id ): string {
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return '';
    }
    if ( in_array( 'administrator', (array) $user->roles, true ) ) {
        return 'admin';
    }
    if ( in_array( 'civi_employer', (array) $user->roles, true ) ) {
        return 'employer';
    }
    if ( in_array( 'civi_candidate', (array) $user->roles, true ) ) {
        return 'candidate';
    }
    return '';
}

/**
 * Check if a user is an employer.
 */
function civijobs_is_employer( int $user_id = 0 ): bool {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    return civijobs_get_user_role( $user_id ) === 'employer';
}

/**
 * Check if a user is a candidate.
 */
function civijobs_is_candidate( int $user_id = 0 ): bool {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    return civijobs_get_user_role( $user_id ) === 'candidate';
}

/**
 * Assign role from registration form data.
 * Hooked into user_register to set role after wp_insert_user().
 */
function civijobs_set_user_role_on_register( int $user_id ): void {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $role = isset( $_POST['civi_role'] ) ? sanitize_key( $_POST['civi_role'] ) : '';
    if ( in_array( $role, [ 'civi_employer', 'civi_candidate' ], true ) ) {
        $user = new WP_User( $user_id );
        $user->set_role( $role );
    }
}
add_action( 'user_register', 'civijobs_set_user_role_on_register' );

/**
 * Redirect users to their dashboard after login.
 */
function civijobs_login_redirect( string $redirect_to, string $request, WP_User $user ): string {
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        if ( in_array( 'civi_employer', $user->roles, true ) ) {
            return civijobs_get_dashboard_url( 'employer' );
        }
        if ( in_array( 'civi_candidate', $user->roles, true ) ) {
            return civijobs_get_dashboard_url( 'candidate' );
        }
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'civijobs_login_redirect', 10, 3 );
