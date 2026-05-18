<?php
/**
 * Wallet / payment system for freelancer marketplace
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get wallet balance for a user, creating the wallet record if needed.
 */
function civijobs_get_wallet_balance( int $user_id ): float {
    global $wpdb;

    $balance = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT balance FROM {$wpdb->prefix}civi_wallet WHERE user_id = %d",
            $user_id
        )
    );

    if ( $balance === null ) {
        $wpdb->insert(
            "{$wpdb->prefix}civi_wallet",
            [ 'user_id' => $user_id, 'balance' => 0.00 ],
            [ '%d', '%f' ]
        );
        return 0.00;
    }

    return (float) $balance;
}

/**
 * Credit an amount to a user's wallet.
 */
function civijobs_credit_wallet( int $user_id, float $amount, string $description, string $reference_id = '' ): bool {
    global $wpdb;

    if ( $amount <= 0 ) {
        return false;
    }

    // Ensure wallet exists
    civijobs_get_wallet_balance( $user_id );

    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}civi_wallet SET balance = balance + %f WHERE user_id = %d",
            $amount,
            $user_id
        )
    );

    if ( $updated !== false ) {
        $wpdb->insert(
            "{$wpdb->prefix}civi_wallet_transactions",
            [
                'user_id'      => $user_id,
                'amount'       => $amount,
                'type'         => 'credit',
                'description'  => sanitize_text_field( $description ),
                'reference_id' => sanitize_text_field( $reference_id ),
            ],
            [ '%d', '%f', '%s', '%s', '%s' ]
        );
        return true;
    }

    return false;
}

/**
 * Debit an amount from a user's wallet.
 */
function civijobs_debit_wallet( int $user_id, float $amount, string $description, string $reference_id = '' ): bool {
    global $wpdb;

    if ( $amount <= 0 ) {
        return false;
    }

    $balance = civijobs_get_wallet_balance( $user_id );
    if ( $balance < $amount ) {
        return false; // Insufficient funds
    }

    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}civi_wallet SET balance = balance - %f WHERE user_id = %d",
            $amount,
            $user_id
        )
    );

    if ( $updated !== false ) {
        $wpdb->insert(
            "{$wpdb->prefix}civi_wallet_transactions",
            [
                'user_id'      => $user_id,
                'amount'       => $amount,
                'type'         => 'debit',
                'description'  => sanitize_text_field( $description ),
                'reference_id' => sanitize_text_field( $reference_id ),
            ],
            [ '%d', '%f', '%s', '%s', '%s' ]
        );
        return true;
    }

    return false;
}

/**
 * Get transaction history for a user.
 */
function civijobs_get_transactions( int $user_id, int $limit = 20, int $offset = 0 ): array {
    global $wpdb;
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}civi_wallet_transactions
             WHERE user_id = %d
             ORDER BY created_at DESC
             LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        )
    );
}

/**
 * Process a service payment: buyer → admin commission + seller.
 */
function civijobs_process_service_payment( int $buyer_id, int $seller_id, int $service_id, float $amount ): bool {
    $commission_rate = (float) civijobs_get_setting( 'commission_rate', 10 ) / 100;
    $commission      = round( $amount * $commission_rate, 2 );
    $seller_payout   = $amount - $commission;
    $service_title   = get_the_title( $service_id );

    // Debit buyer
    if ( ! civijobs_debit_wallet( $buyer_id, $amount,
        sprintf( __( 'Payment for service: %s', 'civijobs' ), $service_title ),
        "service_{$service_id}"
    ) ) {
        return false;
    }

    // Credit seller (minus commission)
    civijobs_credit_wallet( $seller_id, $seller_payout,
        sprintf( __( 'Earnings from service: %s', 'civijobs' ), $service_title ),
        "service_{$service_id}"
    );

    // Log commission to admin meta
    $admin_commission = (float) get_option( 'civijobs_total_commission', 0 );
    update_option( 'civijobs_total_commission', $admin_commission + $commission );

    do_action( 'civijobs_service_payment_processed', $buyer_id, $seller_id, $service_id, $amount, $commission );

    return true;
}

/**
 * Get platform commission rate.
 */
function civijobs_get_commission_rate(): float {
    return (float) civijobs_get_setting( 'commission_rate', 10 );
}

// ---- AJAX ----

add_action( 'wp_ajax_civijobs_get_wallet_balance', 'civijobs_ajax_get_wallet_balance' );
function civijobs_ajax_get_wallet_balance(): void {
    check_ajax_referer( 'civijobs_nonce', 'nonce' );
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error();
    }
    wp_send_json_success( [
        'balance'      => civijobs_get_wallet_balance( $user_id ),
        'transactions' => civijobs_get_transactions( $user_id, 10 ),
    ] );
}
