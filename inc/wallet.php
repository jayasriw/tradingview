<?php
/**
 * Wallet / Payment System
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

function jobportal_get_wallet_balance( int $user_id ): float {
    global $wpdb;
    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT balance FROM {$wpdb->prefix}civi_wallet WHERE user_id = %d",
        $user_id
    ) );
    return $row ? (float) $row->balance : 0.0;
}

function jobportal_credit_wallet( int $user_id, float $amount, string $description = '', int $reference_id = 0 ): bool {
    global $wpdb;
    $table = $wpdb->prefix . 'civi_wallet';

    // Upsert balance
    $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE user_id = %d", $user_id ) );
    if ( $existing ) {
        $wpdb->query( $wpdb->prepare( "UPDATE $table SET balance = balance + %f WHERE user_id = %d", $amount, $user_id ) );
    } else {
        $wpdb->insert( $table, [ 'user_id' => $user_id, 'balance' => $amount ], [ '%d', '%f' ] );
    }

    return (bool) $wpdb->insert(
        $wpdb->prefix . 'civi_wallet_transactions',
        [
            'user_id'      => $user_id,
            'type'         => 'credit',
            'amount'       => $amount,
            'description'  => sanitize_text_field( $description ),
            'reference_id' => $reference_id,
            'created_at'   => current_time( 'mysql' ),
        ],
        [ '%d', '%s', '%f', '%s', '%d', '%s' ]
    );
}

function jobportal_debit_wallet( int $user_id, float $amount, string $description = '', int $reference_id = 0 ): bool {
    $balance = jobportal_get_wallet_balance( $user_id );
    if ( $balance < $amount ) return false;

    global $wpdb;
    $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->prefix}civi_wallet SET balance = balance - %f WHERE user_id = %d",
        $amount, $user_id
    ) );

    return (bool) $wpdb->insert(
        $wpdb->prefix . 'civi_wallet_transactions',
        [
            'user_id'      => $user_id,
            'type'         => 'debit',
            'amount'       => $amount,
            'description'  => sanitize_text_field( $description ),
            'reference_id' => $reference_id,
            'created_at'   => current_time( 'mysql' ),
        ],
        [ '%d', '%s', '%f', '%s', '%d', '%s' ]
    );
}

function jobportal_get_transactions( int $user_id, int $limit = 20 ): array {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}civi_wallet_transactions WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
        $user_id, $limit
    ) ) ?: [];
}

function jobportal_get_commission_rate(): float {
    return (float) jobportal_get_setting( 'commission_rate', 10 );
}

function jobportal_process_service_payment( int $buyer_id, int $seller_id, float $amount, int $service_id ): bool {
    $commission = $amount * ( jobportal_get_commission_rate() / 100 );
    $seller_cut  = $amount - $commission;

    return jobportal_credit_wallet( $seller_id, $seller_cut,
        sprintf( __( 'Payment for service #%d', 'jobportal' ), $service_id ), $service_id );
}

// AJAX
add_action( 'wp_ajax_jobportal_get_wallet_balance', 'jobportal_ajax_get_wallet_balance' );

function jobportal_ajax_get_wallet_balance(): void {
    check_ajax_referer( 'jobportal_nonce', 'nonce' );
    $user_id = get_current_user_id();
    wp_send_json_success( [ 'balance' => jobportal_get_wallet_balance( $user_id ) ] );
}
