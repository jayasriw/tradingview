<?php
/**
 * User Roles
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'jobportal_register_roles' );

function jobportal_register_roles(): void {
    add_role( 'jp_employer', __( 'Employer', 'jobportal' ), [
        'read'         => true,
        'upload_files' => true,
    ] );

    add_role( 'jp_candidate', __( 'Candidate', 'jobportal' ), [
        'read'         => true,
        'upload_files' => true,
    ] );
}

function jobportal_remove_roles(): void {
    remove_role( 'jp_employer' );
    remove_role( 'jp_candidate' );
}

function jobportal_get_user_role( int $user_id ): string {
    $user = get_userdata( $user_id );
    if ( ! $user ) return '';
    $roles = (array) $user->roles;
    if ( in_array( 'jp_employer', $roles, true ) ) return 'jp_employer';
    if ( in_array( 'jp_candidate', $roles, true ) ) return 'jp_candidate';
    if ( in_array( 'administrator', $roles, true ) ) return 'administrator';
    return $roles[0] ?? '';
}

function jobportal_is_employer( int $user_id = 0 ): bool {
    $user_id = $user_id ?: get_current_user_id();
    return jobportal_get_user_role( $user_id ) === 'jp_employer';
}

function jobportal_is_candidate( int $user_id = 0 ): bool {
    $user_id = $user_id ?: get_current_user_id();
    return jobportal_get_user_role( $user_id ) === 'jp_candidate';
}

// Set user role on registration
add_action( 'user_register', function( $user_id ) {
    $role = sanitize_key( $_POST['role'] ?? '' );
    if ( in_array( $role, [ 'jp_employer', 'jp_candidate' ], true ) ) {
        $user = new WP_User( $user_id );
        $user->set_role( $role );
    }
}, 10 );

// Login redirect
add_filter( 'login_redirect', function( $redirect_to, $request, $user ) {
    if ( ! isset( $user->roles ) ) return $redirect_to;
    return jobportal_get_dashboard_url( $user->ID ) ?: $redirect_to;
}, 10, 3 );
