<?php
/**
 * Admin Settings Page
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function() {
    add_menu_page(
        __( 'JobPortal', 'jobportal' ),
        __( 'JobPortal', 'jobportal' ),
        'manage_options',
        'jobportal',
        'jobportal_admin_overview',
        'dashicons-businessman',
        30
    );
    add_submenu_page( 'jobportal', __( 'Settings', 'jobportal' ),     __( 'Settings', 'jobportal' ),     'manage_options', 'jobportal-settings',     'jobportal_admin_settings_page' );
    add_submenu_page( 'jobportal', __( 'Applications', 'jobportal' ), __( 'Applications', 'jobportal' ), 'manage_options', 'jobportal-applications', 'jobportal_admin_applications_page' );
} );

function jobportal_admin_overview(): void {
    $total_jobs       = wp_count_posts( 'jp_job' )->publish;
    $total_companies  = wp_count_posts( 'jp_company' )->publish;
    $pending_jobs     = wp_count_posts( 'jp_job' )->pending;
    $total_candidates = count_users()['avail_roles']['jp_candidate'] ?? 0;
    $total_employers  = count_users()['avail_roles']['jp_employer']  ?? 0;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'JobPortal Overview', 'jobportal' ); ?></h1>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-top:20px">
            <?php foreach ( [
                [ __( 'Active Jobs', 'jobportal' ),      $total_jobs ],
                [ __( 'Companies', 'jobportal' ),        $total_companies ],
                [ __( 'Pending Jobs', 'jobportal' ),     $pending_jobs ],
                [ __( 'Candidates', 'jobportal' ),       $total_candidates ],
                [ __( 'Employers', 'jobportal' ),        $total_employers ],
            ] as [ $label, $value ] ) : ?>
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;text-align:center">
                    <div style="font-size:2rem;font-weight:800;color:#6366f1"><?php echo esc_html( number_format( $value ) ); ?></div>
                    <div style="color:#6b7280;font-size:0.875rem"><?php echo esc_html( $label ); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function jobportal_admin_settings_page(): void {
    if ( isset( $_POST['jobportal_settings_nonce'] ) && wp_verify_nonce( $_POST['jobportal_settings_nonce'], 'jobportal_save_settings' ) ) {
        $settings = [
            'auto_approve_jobs'  => isset( $_POST['auto_approve_jobs'] ) ? '1' : '0',
            'commission_rate'    => (float) ( $_POST['commission_rate'] ?? 10 ),
            'jobs_per_page'      => (int)   ( $_POST['jobs_per_page']   ?? 12 ),
        ];
        update_option( 'jobportal_settings', $settings );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'jobportal' ) . '</p></div>';
    }

    $settings = get_option( 'jobportal_settings', [] );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'JobPortal Settings', 'jobportal' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'jobportal_save_settings', 'jobportal_settings_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Auto-approve Jobs', 'jobportal' ); ?></th>
                    <td><input type="checkbox" name="auto_approve_jobs" value="1" <?php checked( $settings['auto_approve_jobs'] ?? '', '1' ); ?>></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Commission Rate (%)', 'jobportal' ); ?></th>
                    <td><input type="number" name="commission_rate" value="<?php echo esc_attr( $settings['commission_rate'] ?? 10 ); ?>" min="0" max="100" step="0.5" class="small-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Jobs Per Page', 'jobportal' ); ?></th>
                    <td><input type="number" name="jobs_per_page" value="<?php echo esc_attr( $settings['jobs_per_page'] ?? 12 ); ?>" min="1" max="100" class="small-text"></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function jobportal_admin_applications_page(): void {
    global $wpdb;
    $applications = $wpdb->get_results(
        "SELECT a.*, p.post_title as job_title, u.display_name as candidate_name, e.display_name as employer_name
         FROM {$wpdb->prefix}civi_applications a
         INNER JOIN {$wpdb->posts} p ON p.ID = a.job_id
         INNER JOIN {$wpdb->users} u ON u.ID = a.candidate_id
         INNER JOIN {$wpdb->users} e ON e.ID = p.post_author
         ORDER BY a.applied_at DESC
         LIMIT 100"
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Applications', 'jobportal' ); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Candidate', 'jobportal' ); ?></th>
                    <th><?php esc_html_e( 'Job', 'jobportal' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'jobportal' ); ?></th>
                    <th><?php esc_html_e( 'Applied', 'jobportal' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $applications as $app ) : ?>
                    <tr>
                        <td><?php echo esc_html( $app->candidate_name ); ?></td>
                        <td><?php echo esc_html( $app->job_title ); ?></td>
                        <td><?php echo esc_html( ucfirst( $app->status ) ); ?></td>
                        <td><?php echo esc_html( $app->applied_at ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
