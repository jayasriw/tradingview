<?php
/**
 * Candidate: Settings
 *
 * @package CiviJobs
 */

$user_id = get_current_user_id();
$user    = wp_get_current_user();
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Account Settings', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Manage your account security and preferences.', 'civijobs' ); ?></p>
    </div>
</div>

<!-- Change Password -->
<div class="dash-card" style="margin-bottom:24px">
    <div class="dash-card-header">
        <h3 class="dash-card-title">🔒 <?php esc_html_e( 'Change Password', 'civijobs' ); ?></h3>
    </div>
    <div class="dash-card-body">
        <form id="change-password-form">
            <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
            <div class="form-row">
                <div class="form-group">
                    <label><?php esc_html_e( 'Current Password', 'civijobs' ); ?></label>
                    <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><?php esc_html_e( 'New Password', 'civijobs' ); ?></label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required autocomplete="new-password" minlength="8">
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Confirm New Password', 'civijobs' ); ?></label>
                    <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password">
                </div>
            </div>
            <div id="password-messages"></div>
            <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Update Password', 'civijobs' ); ?></button>
        </form>
    </div>
</div>

<!-- Email Notifications -->
<div class="dash-card" style="margin-bottom:24px">
    <div class="dash-card-header">
        <h3 class="dash-card-title">📧 <?php esc_html_e( 'Email Notifications', 'civijobs' ); ?></h3>
    </div>
    <div class="dash-card-body">
        <form id="email-prefs-form">
            <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
            <?php
            $prefs = [
                'notify_application_update' => __( 'Application status updates', 'civijobs' ),
                'notify_new_message'        => __( 'New messages', 'civijobs' ),
                'notify_meeting_scheduled'  => __( 'Meeting scheduled / updated', 'civijobs' ),
                'notify_job_alert'          => __( 'Job alert emails', 'civijobs' ),
                'notify_newsletter'         => __( 'Newsletter and tips', 'civijobs' ),
            ];
            foreach ( $prefs as $key => $label ) :
                $checked = get_user_meta( $user_id, $key, true );
                $checked = ( $checked === '' ) ? true : (bool) $checked;
            ?>
                <label class="toggle-label" style="display:flex;align-items:center;gap:12px;margin-bottom:14px;cursor:pointer">
                    <input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1"
                           class="toggle-input" <?php checked( $checked, true ); ?>>
                    <span class="toggle-switch"></span>
                    <span><?php echo esc_html( $label ); ?></span>
                </label>
            <?php endforeach; ?>
            <div id="email-prefs-messages"></div>
            <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Save Preferences', 'civijobs' ); ?></button>
        </form>
    </div>
</div>

<!-- Danger Zone -->
<div class="dash-card" style="border-color:var(--color-danger)">
    <div class="dash-card-header">
        <h3 class="dash-card-title" style="color:var(--color-danger)">⚠️ <?php esc_html_e( 'Danger Zone', 'civijobs' ); ?></h3>
    </div>
    <div class="dash-card-body">
        <p style="color:var(--color-gray-600);margin-bottom:16px">
            <?php esc_html_e( 'Once you delete your account, there is no going back. All your data, applications and saved jobs will be permanently removed.', 'civijobs' ); ?>
        </p>
        <button class="btn btn-danger" id="delete-account-btn" data-confirm="<?php esc_attr_e( 'Are you sure you want to delete your account? This cannot be undone.', 'civijobs' ); ?>">
            <?php esc_html_e( 'Delete My Account', 'civijobs' ); ?>
        </button>
    </div>
</div>

<style>
.toggle-input { display: none; }
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 22px;
    background: var(--color-gray-300);
    border-radius: 11px;
    transition: background .2s;
    flex-shrink: 0;
}
.toggle-switch::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
}
.toggle-input:checked + .toggle-switch { background: var(--color-primary); }
.toggle-input:checked + .toggle-switch::after { transform: translateX(18px); }
</style>
