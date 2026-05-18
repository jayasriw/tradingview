<?php
/**
 * CiviJobs – Membership Packages System
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
 * Get all active packages of a given type.
 *
 * @param string $type Package type: 'employer' or 'candidate'.
 * @return WP_Post[] Array of package posts.
 */
function civijobs_get_packages( string $type = 'employer' ): array {
	$type = in_array( $type, [ 'employer', 'candidate' ], true ) ? $type : 'employer';

	$posts = get_posts( [
		'post_type'      => 'civi_package',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'meta_query'     => [
			[
				'key'     => '_package_type',
				'value'   => $type,
				'compare' => '=',
			],
		],
	] );

	return $posts ?: [];
}

/**
 * Get the currently active package order for a user.
 *
 * @param int $user_id User ID.
 * @return object|null Package order row or null.
 */
function civijobs_get_user_active_package( int $user_id ): ?object {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_package_orders';

	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE user_id = %d AND status = 'active' AND ( expires_at IS NULL OR expires_at >= %s ) ORDER BY created_at DESC LIMIT 1", // phpcs:ignore
			$user_id,
			current_time( 'mysql' )
		)
	);
}

/**
 * Activate a package for a user.
 *
 * @param int $user_id    User ID.
 * @param int $package_id Package post ID.
 * @param int $order_id   WooCommerce order ID (0 if manual/admin).
 * @return int|false Package order row ID on success, false on failure.
 */
function civijobs_activate_package( int $user_id, int $package_id, int $order_id = 0 ) {
	global $wpdb;

	$table = $wpdb->prefix . 'civi_package_orders';

	// Deactivate any existing active packages.
	$wpdb->update(
		$table,
		[ 'status' => 'expired' ],
		[ 'user_id' => $user_id, 'status' => 'active' ],
		[ '%s' ],
		[ '%d', '%s' ]
	);

	$duration_days = (int) get_post_meta( $package_id, '_package_duration_days', true );
	$job_credits   = (int) get_post_meta( $package_id, '_package_job_limit', true );
	$unlimited     = (string) get_post_meta( $package_id, '_package_unlimited', true );

	$expires_at = null;
	if ( $duration_days > 0 ) {
		$expires_at = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $duration_days . ' days', current_time( 'timestamp' ) ) );
	}

	$result = $wpdb->insert(
		$table,
		[
			'user_id'          => $user_id,
			'package_id'       => $package_id,
			'order_id'         => $order_id,
			'status'           => 'active',
			'jobs_remaining'   => ( '1' === $unlimited ) ? -1 : $job_credits,
			'expires_at'       => $expires_at,
			'created_at'       => current_time( 'mysql' ),
		],
		[ '%d', '%d', '%d', '%s', '%d', '%s', '%s' ]
	);

	if ( false === $result ) {
		return false;
	}

	$row_id = (int) $wpdb->insert_id;

	// Notify the user.
	civijobs_add_notification(
		$user_id,
		'package_activated',
		__( 'Package Activated', 'civijobs' ),
		/* translators: %s: package name */
		sprintf( __( 'Your "%s" package has been activated.', 'civijobs' ), get_the_title( $package_id ) ),
		home_url( '/packages/' )
	);

	if ( function_exists( 'civijobs_email_package_activated' ) ) {
		civijobs_email_package_activated( $user_id, $package_id );
	}

	do_action( 'civijobs_package_activated', $user_id, $package_id, $row_id );

	return $row_id;
}

/**
 * Decrement the number of job posting credits for a user's active package.
 *
 * @param int $user_id User ID.
 * @return bool True if decremented successfully.
 */
function civijobs_decrement_package_jobs( int $user_id ): bool {
	global $wpdb;

	$table        = $wpdb->prefix . 'civi_package_orders';
	$active_order = civijobs_get_user_active_package( $user_id );

	if ( ! $active_order ) {
		return false;
	}

	// -1 = unlimited; do not decrement.
	if ( (int) $active_order->jobs_remaining === -1 ) {
		return true;
	}

	if ( (int) $active_order->jobs_remaining <= 0 ) {
		return false;
	}

	$result = $wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table} SET jobs_remaining = jobs_remaining - 1 WHERE id = %d AND jobs_remaining > 0", // phpcs:ignore
			(int) $active_order->id
		)
	);

	return (bool) $result;
}

/**
 * Check if an employer has sufficient job posting credits.
 *
 * @param int $user_id Employer user ID.
 * @return bool
 */
function civijobs_can_post_job( int $user_id ): bool {
	// Admins can always post.
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}

	$active_order = civijobs_get_user_active_package( $user_id );

	if ( ! $active_order ) {
		return false;
	}

	// -1 = unlimited.
	if ( (int) $active_order->jobs_remaining === -1 ) {
		return true;
	}

	return (int) $active_order->jobs_remaining > 0;
}

/**
 * Get the feature list for a package.
 *
 * @param int $package_id Package post ID.
 * @return array List of feature strings.
 */
function civijobs_get_package_features( int $package_id ): array {
	$raw = get_post_meta( $package_id, '_package_features', true );

	if ( is_array( $raw ) ) {
		return array_map( 'sanitize_text_field', $raw );
	}

	if ( is_string( $raw ) && ! empty( $raw ) ) {
		$lines = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
		return array_values( array_map( 'sanitize_text_field', $lines ) );
	}

	return [];
}

// ============================================================
// WooCommerce Integration
// ============================================================

/**
 * Auto-activate a package when a WooCommerce order completes.
 *
 * Expects each WooCommerce product that represents a package to have the
 * '_civijobs_package_id' product meta set to the civi_package post ID.
 *
 * @param int $order_id WooCommerce order ID.
 */
function civijobs_woocommerce_order_completed( int $order_id ): void {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$user_id = (int) $order->get_user_id();
	if ( ! $user_id ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		$product_id = (int) $item->get_product_id();
		$package_id = (int) get_post_meta( $product_id, '_civijobs_package_id', true );

		if ( $package_id && get_post_type( $package_id ) === 'civi_package' ) {
			civijobs_activate_package( $user_id, $package_id, $order_id );
		}
	}
}
add_action( 'woocommerce_order_status_completed', 'civijobs_woocommerce_order_completed' );

// ============================================================
// Default Package Seeder
// ============================================================

/**
 * Create default employer packages if none exist.
 * Called on theme activation via civijobs_install().
 */
function civijobs_seed_default_packages(): void {
	// Only seed if no packages exist yet.
	$existing = get_posts( [
		'post_type'      => 'civi_package',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] );

	if ( ! empty( $existing ) ) {
		return;
	}

	$defaults = [
		[
			'title'       => __( 'Basic', 'civijobs' ),
			'price'       => '29.00',
			'job_limit'   => 5,
			'unlimited'   => '0',
			'duration'    => 30,
			'type'        => 'employer',
			'order'       => 1,
			'features'    => [
				__( '5 Job Postings', 'civijobs' ),
				__( '30 Day Access', 'civijobs' ),
				__( 'Standard Support', 'civijobs' ),
			],
		],
		[
			'title'       => __( 'Pro', 'civijobs' ),
			'price'       => '79.00',
			'job_limit'   => 20,
			'unlimited'   => '0',
			'duration'    => 60,
			'type'        => 'employer',
			'order'       => 2,
			'features'    => [
				__( '20 Job Postings', 'civijobs' ),
				__( '60 Day Access', 'civijobs' ),
				__( 'Featured Job Badges', 'civijobs' ),
				__( 'Priority Support', 'civijobs' ),
			],
		],
		[
			'title'       => __( 'Enterprise', 'civijobs' ),
			'price'       => '199.00',
			'job_limit'   => 0,
			'unlimited'   => '1',
			'duration'    => 365,
			'type'        => 'employer',
			'order'       => 3,
			'features'    => [
				__( 'Unlimited Job Postings', 'civijobs' ),
				__( '365 Day Access', 'civijobs' ),
				__( 'Featured Job Badges', 'civijobs' ),
				__( 'Dedicated Account Manager', 'civijobs' ),
				__( 'Analytics Dashboard', 'civijobs' ),
			],
		],
	];

	foreach ( $defaults as $pkg ) {
		$post_id = wp_insert_post( [
			'post_title'   => $pkg['title'],
			'post_type'    => 'civi_package',
			'post_status'  => 'publish',
			'menu_order'   => $pkg['order'],
		] );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_package_type',          $pkg['type'] );
		update_post_meta( $post_id, '_package_price',         $pkg['price'] );
		update_post_meta( $post_id, '_package_job_limit',     $pkg['job_limit'] );
		update_post_meta( $post_id, '_package_unlimited',     $pkg['unlimited'] );
		update_post_meta( $post_id, '_package_duration_days', $pkg['duration'] );
		update_post_meta( $post_id, '_package_features',      $pkg['features'] );
	}
}
add_action( 'civijobs_theme_installed', 'civijobs_seed_default_packages' );
