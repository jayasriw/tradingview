<?php
/**
 * Candidate: Wallet
 *
 * @package CiviJobs
 */

$user_id      = get_current_user_id();
$balance      = civijobs_get_wallet_balance( $user_id );
$transactions = civijobs_get_transactions( $user_id, 20 );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Wallet', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Manage your earnings from services.', 'civijobs' ); ?></p>
    </div>
</div>

<!-- Balance card -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:28px">
    <div class="dash-card" style="background:linear-gradient(135deg,var(--color-primary),#7c3aed);color:#fff;padding:28px">
        <div style="font-size:0.85rem;opacity:.8;margin-bottom:8px"><?php esc_html_e( 'Available Balance', 'civijobs' ); ?></div>
        <div style="font-size:2.5rem;font-weight:800"><?php echo esc_html( '$' . number_format( $balance, 2 ) ); ?></div>
        <div style="margin-top:16px">
            <button class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border-color:rgba(255,255,255,.3)"
                    data-modal-target="withdraw-modal">
                <?php esc_html_e( 'Withdraw', 'civijobs' ); ?>
            </button>
        </div>
    </div>
    <?php
    global $wpdb;
    $total_earned = (float) $wpdb->get_var( $wpdb->prepare(
        "SELECT SUM(amount) FROM {$wpdb->prefix}civi_wallet_transactions WHERE user_id = %d AND type = 'credit'",
        $user_id
    ) );
    $total_spent = (float) $wpdb->get_var( $wpdb->prepare(
        "SELECT SUM(amount) FROM {$wpdb->prefix}civi_wallet_transactions WHERE user_id = %d AND type = 'debit'",
        $user_id
    ) );
    ?>
    <div class="dash-stat-card">
        <div class="dash-stat-icon green">💰</div>
        <div>
            <div class="dash-stat-number"><?php echo esc_html( '$' . number_format( $total_earned, 2 ) ); ?></div>
            <div class="dash-stat-label"><?php esc_html_e( 'Total Earned', 'civijobs' ); ?></div>
        </div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon orange">📤</div>
        <div>
            <div class="dash-stat-number"><?php echo esc_html( '$' . number_format( $total_spent, 2 ) ); ?></div>
            <div class="dash-stat-label"><?php esc_html_e( 'Total Withdrawn', 'civijobs' ); ?></div>
        </div>
    </div>
</div>

<!-- Transactions -->
<div class="dash-card">
    <div class="dash-card-header">
        <h3 class="dash-card-title"><?php esc_html_e( 'Transaction History', 'civijobs' ); ?></h3>
    </div>
    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Description', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Amount', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'civijobs' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $transactions ) :
                    foreach ( $transactions as $tx ) : ?>
                    <tr>
                        <td style="font-size:0.875rem"><?php echo esc_html( $tx->description ); ?></td>
                        <td>
                            <?php if ( $tx->type === 'credit' ) : ?>
                                <span class="badge badge-green"><?php esc_html_e( 'Credit', 'civijobs' ); ?></span>
                            <?php else : ?>
                                <span class="badge badge-orange"><?php esc_html_e( 'Debit', 'civijobs' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:700;<?php echo $tx->type === 'credit' ? 'color:var(--color-success)' : 'color:var(--color-danger)'; ?>">
                            <?php echo esc_html( ( $tx->type === 'credit' ? '+' : '-' ) . '$' . number_format( $tx->amount, 2 ) ); ?>
                        </td>
                        <td style="font-size:0.78rem;color:var(--color-gray-500)"><?php echo esc_html( civijobs_time_ago( $tx->created_at ) ); ?></td>
                    </tr>
                <?php endforeach;
                else : ?>
                    <tr><td colspan="4">
                        <div class="empty-state">
                            <div class="empty-state-icon">💸</div>
                            <div class="empty-state-title"><?php esc_html_e( 'No transactions yet', 'civijobs' ); ?></div>
                            <div class="empty-state-text"><?php esc_html_e( 'Earnings from your services will appear here.', 'civijobs' ); ?></div>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Withdraw Modal -->
<div id="withdraw-modal" class="modal" role="dialog" aria-modal="true">
    <div class="modal-overlay" data-modal-close></div>
    <div class="modal-dialog" style="max-width:440px">
        <div class="modal-header">
            <h3><?php esc_html_e( 'Withdraw Funds', 'civijobs' ); ?></h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
            <p style="color:var(--color-gray-600);margin-bottom:20px">
                <?php printf(
                    /* translators: %s: available balance */
                    esc_html__( 'Available balance: %s', 'civijobs' ),
                    '<strong>$' . esc_html( number_format( $balance, 2 ) ) . '</strong>'
                ); ?>
            </p>
            <form id="withdraw-form">
                <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                <div class="form-group">
                    <label><?php esc_html_e( 'Amount to Withdraw ($)', 'civijobs' ); ?></label>
                    <input type="number" name="amount" class="form-control" min="1" max="<?php echo esc_attr( $balance ); ?>" step="0.01" required>
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'PayPal Email', 'civijobs' ); ?></label>
                    <input type="email" name="paypal_email" class="form-control" placeholder="paypal@example.com" required>
                </div>
                <div class="alert alert-info" style="font-size:0.82rem">
                    <?php esc_html_e( 'Withdrawal requests are processed within 3-5 business days.', 'civijobs' ); ?>
                </div>
                <div id="withdraw-messages"></div>
                <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Submit Request', 'civijobs' ); ?></button>
            </form>
        </div>
    </div>
</div>
