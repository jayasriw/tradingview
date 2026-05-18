<?php
/**
 * WordPress admin settings and admin menu pages
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---- Admin menu ----

add_action( 'admin_menu', 'civijobs_admin_menu' );

function civijobs_admin_menu(): void {
    add_menu_page(
        __( 'CiviJobs', 'civijobs' ),
        __( 'CiviJobs', 'civijobs' ),
        'manage_options',
        'civijobs',
        'civijobs_admin_overview_page',
        'dashicons-id-alt',
        25
    );

    $subpages = [
        [ 'civijobs-jobs',         __( 'Manage Jobs', 'civijobs' ),         'civijobs_admin_jobs_page' ],
        [ 'civijobs-companies',    __( 'Companies', 'civijobs' ),            'civijobs_admin_companies_page' ],
        [ 'civijobs-candidates',   __( 'Candidates', 'civijobs' ),           'civijobs_admin_candidates_page' ],
        [ 'civijobs-applications', __( 'Applications', 'civijobs' ),         'civijobs_admin_applications_page' ],
        [ 'civijobs-packages',     __( 'Packages', 'civijobs' ),             'civijobs_admin_packages_page' ],
        [ 'civijobs-wallet',       __( 'Wallet & Commissions', 'civijobs' ), 'civijobs_admin_wallet_page' ],
        [ 'civijobs-settings',     __( 'Settings', 'civijobs' ),             'civijobs_admin_settings_page' ],
    ];

    foreach ( $subpages as [ $slug, $title, $callback ] ) {
        add_submenu_page( 'civijobs', $title, $title, 'manage_options', $slug, $callback );
    }
}

// ---- Overview page ----

function civijobs_admin_overview_page(): void {
    global $wpdb;

    $total_jobs        = wp_count_posts( 'civi_job' )->publish;
    $total_companies   = wp_count_posts( 'civi_company' )->publish;
    $total_candidates  = count_users()['avail_roles']['civi_candidate'] ?? 0;
    $total_employers   = count_users()['avail_roles']['civi_employer'] ?? 0;
    $total_applications = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}civi_applications" );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'CiviJobs Dashboard', 'civijobs' ); ?></h1>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:20px">
            <?php
            $cards = [
                [ __( 'Active Jobs', 'civijobs' ),     $total_jobs,         '#4f46e5' ],
                [ __( 'Companies', 'civijobs' ),        $total_companies,    '#0ea5e9' ],
                [ __( 'Candidates', 'civijobs' ),       $total_candidates,   '#10b981' ],
                [ __( 'Employers', 'civijobs' ),        $total_employers,    '#f59e0b' ],
                [ __( 'Applications', 'civijobs' ),     $total_applications, '#ef4444' ],
            ];
            foreach ( $cards as [ $label, $value, $color ] ) : ?>
            <div style="background:#fff;padding:24px;border-radius:12px;border-left:4px solid <?php echo esc_attr( $color ); ?>;box-shadow:0 1px 4px rgba(0,0,0,.1)">
                <div style="font-size:32px;font-weight:700;color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( number_format( (int) $value ) ); ?></div>
                <div style="color:#6b7280;margin-top:4px"><?php echo esc_html( $label ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

// ---- Jobs page ----

function civijobs_admin_jobs_page(): void {
    $status = sanitize_key( $_GET['job_status'] ?? 'any' );
    $args   = [
        'post_type'      => 'civi_job',
        'post_status'    => $status === 'any' ? [ 'publish', 'pending', 'draft', 'trash' ] : $status,
        'posts_per_page' => 50,
        'paged'          => max( 1, (int) ( $_GET['paged'] ?? 1 ) ),
    ];
    $query = new WP_Query( $args );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Manage Jobs', 'civijobs' ); ?></h1>
        <ul class="subsubsub">
            <?php foreach ( [ 'any' => __( 'All', 'civijobs' ), 'publish' => __( 'Published', 'civijobs' ), 'pending' => __( 'Pending', 'civijobs' ), 'trash' => __( 'Trash', 'civijobs' ) ] as $s => $label ) : ?>
            <li><a href="<?php echo esc_url( add_query_arg( 'job_status', $s ) ); ?>" <?php echo $status === $s ? 'class="current"' : ''; ?>><?php echo esc_html( $label ); ?></a> |</li>
            <?php endforeach; ?>
        </ul>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Job Title', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Company', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Applications', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'civijobs' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $query->posts as $job ) :
                    $company_id = (int) get_post_meta( $job->ID, '_company_id', true );
                    ?>
                <tr>
                    <td><strong><?php echo esc_html( get_the_title( $job->ID ) ); ?></strong></td>
                    <td><?php echo esc_html( $company_id ? get_the_title( $company_id ) : '—' ); ?></td>
                    <td><?php echo civijobs_get_status_badge( $job->post_status ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
                    <td><?php echo esc_html( civijobs_get_application_count( $job->ID ) ); ?></td>
                    <td><?php echo esc_html( get_the_date( 'M j, Y', $job->ID ) ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( get_edit_post_link( $job->ID ) ); ?>"><?php esc_html_e( 'Edit', 'civijobs' ); ?></a> |
                        <a href="<?php echo esc_url( get_permalink( $job->ID ) ); ?>" target="_blank"><?php esc_html_e( 'View', 'civijobs' ); ?></a>
                        <?php if ( $job->post_status === 'pending' ) : ?>
                        | <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'approve_job', 'job_id' => $job->ID ] ), 'approve_job' ) ); ?>"><?php esc_html_e( 'Approve', 'civijobs' ); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ---- Applications page ----

function civijobs_admin_applications_page(): void {
    global $wpdb;

    $status = sanitize_key( $_GET['app_status'] ?? '' );
    $where  = $status ? $wpdb->prepare( ' WHERE a.status = %s', $status ) : '';

    $applications = $wpdb->get_results(
        "SELECT a.*, p.post_title as job_title, u.display_name as candidate_name
         FROM {$wpdb->prefix}civi_applications a
         LEFT JOIN {$wpdb->posts} p ON p.ID = a.job_id
         LEFT JOIN {$wpdb->users} u ON u.ID = a.candidate_id
         $where
         ORDER BY a.applied_at DESC
         LIMIT 100"
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Applications', 'civijobs' ); ?></h1>
        <ul class="subsubsub">
            <?php foreach ( [ '' => __( 'All', 'civijobs' ), 'pending' => __( 'Pending', 'civijobs' ), 'reviewing' => __( 'Reviewing', 'civijobs' ), 'shortlisted' => __( 'Shortlisted', 'civijobs' ), 'hired' => __( 'Hired', 'civijobs' ), 'rejected' => __( 'Rejected', 'civijobs' ) ] as $s => $label ) : ?>
            <li><a href="<?php echo esc_url( add_query_arg( 'app_status', $s ) ); ?>" <?php echo $status === $s ? 'class="current"' : ''; ?>><?php echo esc_html( $label ); ?></a> |</li>
            <?php endforeach; ?>
        </ul>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Candidate', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Job', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'CV', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Applied', 'civijobs' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $applications as $app ) : ?>
                <tr>
                    <td><?php echo esc_html( $app->candidate_name ); ?></td>
                    <td><?php echo esc_html( $app->job_title ); ?></td>
                    <td><?php echo civijobs_get_status_badge( $app->status ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
                    <td><?php if ( $app->cv_file ) : ?><a href="<?php echo esc_url( $app->cv_file ); ?>" target="_blank"><?php esc_html_e( 'Download', 'civijobs' ); ?></a><?php else : ?>—<?php endif; ?></td>
                    <td><?php echo esc_html( gmdate( 'M j, Y', strtotime( $app->applied_at ) ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ---- Packages page ----

function civijobs_admin_packages_page(): void {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Membership Packages', 'civijobs' ); ?></h1>
        <p><?php esc_html_e( 'Packages are created as posts of the "civi_package" type. Manage them via the WordPress post editor.', 'civijobs' ); ?></p>
        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=civi_package' ) ); ?>" class="button button-primary">
            <?php esc_html_e( 'Add New Package', 'civijobs' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=civi_package' ) ); ?>" class="button" style="margin-left:8px">
            <?php esc_html_e( 'View All Packages', 'civijobs' ); ?>
        </a>
    </div>
    <?php
}

// ---- Wallet / commission page ----

function civijobs_admin_wallet_page(): void {
    $total_commission = (float) get_option( 'civijobs_total_commission', 0 );
    global $wpdb;
    $recent = $wpdb->get_results( "SELECT t.*, u.display_name FROM {$wpdb->prefix}civi_wallet_transactions t LEFT JOIN {$wpdb->users} u ON u.ID = t.user_id ORDER BY created_at DESC LIMIT 50" );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Wallet & Commissions', 'civijobs' ); ?></h1>
        <div style="background:#fff;padding:20px;border-radius:8px;display:inline-block;margin-bottom:24px;box-shadow:0 1px 4px rgba(0,0,0,.1)">
            <div style="font-size:28px;font-weight:700;color:#4f46e5">$<?php echo esc_html( number_format( $total_commission, 2 ) ); ?></div>
            <div style="color:#6b7280"><?php esc_html_e( 'Total Platform Commission Earned', 'civijobs' ); ?></div>
        </div>
        <h2><?php esc_html_e( 'Recent Transactions', 'civijobs' ); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'User', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Amount', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Description', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'civijobs' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $recent as $tx ) : ?>
                <tr>
                    <td><?php echo esc_html( $tx->display_name ); ?></td>
                    <td style="color:<?php echo $tx->type === 'credit' ? '#10b981' : '#ef4444'; ?>">
                        <?php echo esc_html( ( $tx->type === 'credit' ? '+' : '-' ) . '$' . number_format( $tx->amount, 2 ) ); ?>
                    </td>
                    <td><?php echo esc_html( ucfirst( $tx->type ) ); ?></td>
                    <td><?php echo esc_html( $tx->description ); ?></td>
                    <td><?php echo esc_html( gmdate( 'M j, Y g:i a', strtotime( $tx->created_at ) ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ---- Companies page ----
function civijobs_admin_companies_page(): void {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Companies', 'civijobs' ); ?></h1>
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=civi_company' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Manage Companies', 'civijobs' ); ?></a>
    </div>
    <?php
}

// ---- Candidates page ----
function civijobs_admin_candidates_page(): void {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Candidates', 'civijobs' ); ?></h1>
        <a href="<?php echo esc_url( admin_url( 'users.php?role=civi_candidate' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Manage Candidates', 'civijobs' ); ?></a>
    </div>
    <?php
}

// ---- Settings page ----

add_action( 'admin_init', 'civijobs_register_settings' );

function civijobs_register_settings(): void {
    register_setting( 'civijobs_settings_group', 'civijobs_settings', 'civijobs_sanitize_settings' );

    add_settings_section( 'general', __( 'General', 'civijobs' ), '__return_false', 'civijobs-settings' );
    add_settings_section( 'jobs',    __( 'Jobs', 'civijobs' ),    '__return_false', 'civijobs-settings' );
    add_settings_section( 'email',   __( 'Email', 'civijobs' ),   '__return_false', 'civijobs-settings' );
    add_settings_section( 'payment', __( 'Payment', 'civijobs' ), '__return_false', 'civijobs-settings' );
    add_settings_section( 'maps',    __( 'Maps', 'civijobs' ),    '__return_false', 'civijobs-settings' );

    $fields = [
        [ 'general', 'site_tagline',            __( 'Site Tagline', 'civijobs' ),           'text' ],
        [ 'general', 'enable_registration',     __( 'Enable Registration', 'civijobs' ),    'checkbox' ],
        [ 'general', 'enable_freelancer',       __( 'Enable Freelancer Mode', 'civijobs' ), 'checkbox' ],
        [ 'jobs',    'jobs_per_page',            __( 'Jobs Per Page', 'civijobs' ),          'number' ],
        [ 'jobs',    'default_job_expiry',       __( 'Default Job Expiry (days)', 'civijobs' ), 'number' ],
        [ 'jobs',    'auto_approve_jobs',        __( 'Auto-approve Jobs', 'civijobs' ),      'checkbox' ],
        [ 'email',   'email_from_name',          __( 'From Name', 'civijobs' ),              'text' ],
        [ 'email',   'email_from_address',       __( 'From Email', 'civijobs' ),             'email' ],
        [ 'payment', 'commission_rate',          __( 'Commission Rate (%)', 'civijobs' ),    'number' ],
        [ 'payment', 'enable_wire_transfer',     __( 'Enable Wire Transfer', 'civijobs' ),   'checkbox' ],
        [ 'maps',    'google_maps_api_key',      __( 'Google Maps API Key', 'civijobs' ),    'text' ],
        [ 'maps',    'default_map_lat',          __( 'Default Map Latitude', 'civijobs' ),   'text' ],
        [ 'maps',    'default_map_lng',          __( 'Default Map Longitude', 'civijobs' ),  'text' ],
    ];

    foreach ( $fields as [ $section, $key, $label, $type ] ) {
        add_settings_field(
            "civijobs_{$key}",
            $label,
            static function () use ( $key, $type ) {
                $value = civijobs_get_setting( $key, '' );
                if ( $type === 'checkbox' ) {
                    echo '<input type="checkbox" name="civijobs_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( $value, '1', false ) . '>';
                } else {
                    echo '<input type="' . esc_attr( $type ) . '" name="civijobs_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="regular-text">';
                }
            },
            'civijobs-settings',
            $section
        );
    }
}

function civijobs_sanitize_settings( array $input ): array {
    $clean = [];
    foreach ( $input as $key => $value ) {
        $clean[ sanitize_key( $key ) ] = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
    }
    return $clean;
}

function civijobs_admin_settings_page(): void {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'CiviJobs Settings', 'civijobs' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'civijobs_settings_group' );
            do_settings_sections( 'civijobs-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// ---- Job approval via admin ----
add_action( 'admin_action_approve_job', 'civijobs_admin_approve_job' );

function civijobs_admin_approve_job(): void {
    check_admin_referer( 'approve_job' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Unauthorized.', 'civijobs' ) );
    }

    $job_id = (int) ( $_GET['job_id'] ?? 0 );
    wp_update_post( [ 'ID' => $job_id, 'post_status' => 'publish' ] );
    civijobs_email_job_approved( $job_id );
    civijobs_add_notification(
        (int) get_post_field( 'post_author', $job_id ),
        'job_approved',
        __( 'Job Approved', 'civijobs' ),
        sprintf( __( 'Your job "%s" has been approved.', 'civijobs' ), get_the_title( $job_id ) ),
        get_permalink( $job_id )
    );

    wp_safe_redirect( admin_url( 'admin.php?page=civijobs-jobs&approved=1' ) );
    exit;
}
