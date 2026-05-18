<?php
/**
 * Database table creation
 *
 * @package JobPortal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'jobportal_create_tables' );

function jobportal_create_tables(): void {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $tables = [
        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jp_messages (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            thread_id varchar(64) NOT NULL,
            sender_id bigint(20) UNSIGNED NOT NULL,
            receiver_id bigint(20) UNSIGNED NOT NULL,
            message text NOT NULL,
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY thread_id (thread_id),
            KEY sender_id (sender_id),
            KEY receiver_id (receiver_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}civi_notifications (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            type varchar(50) NOT NULL DEFAULT 'system',
            message text NOT NULL,
            link varchar(500) DEFAULT NULL,
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jp_meetings (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            employer_id bigint(20) UNSIGNED NOT NULL,
            candidate_id bigint(20) UNSIGNED NOT NULL,
            job_id bigint(20) UNSIGNED DEFAULT NULL,
            title varchar(255) NOT NULL,
            description text DEFAULT NULL,
            date_time datetime NOT NULL,
            location varchar(500) DEFAULT NULL,
            meeting_url varchar(500) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY employer_id (employer_id),
            KEY candidate_id (candidate_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jp_job_alerts (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            keyword varchar(255) DEFAULT NULL,
            location varchar(255) DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            job_type varchar(100) DEFAULT NULL,
            frequency varchar(20) NOT NULL DEFAULT 'daily',
            last_sent datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}civi_applications (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            job_id bigint(20) UNSIGNED NOT NULL,
            candidate_id bigint(20) UNSIGNED NOT NULL,
            status varchar(30) NOT NULL DEFAULT 'pending',
            cover_letter text DEFAULT NULL,
            cv_file varchar(500) DEFAULT NULL,
            applied_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_application (job_id, candidate_id),
            KEY job_id (job_id),
            KEY candidate_id (candidate_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}civi_wallet (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            balance decimal(10,2) NOT NULL DEFAULT 0.00,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}civi_wallet_transactions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            type varchar(10) NOT NULL,
            amount decimal(10,2) NOT NULL,
            description varchar(500) DEFAULT NULL,
            reference_id bigint(20) UNSIGNED DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jp_company_reviews (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            company_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            rating tinyint(1) NOT NULL,
            title varchar(255) DEFAULT NULL,
            review text DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY company_id (company_id),
            KEY user_id (user_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}civi_saved_jobs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            job_id bigint(20) UNSIGNED NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_saved (user_id, job_id)
        ) $charset",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jp_package_orders (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            package_id bigint(20) UNSIGNED NOT NULL,
            package_name varchar(255) NOT NULL,
            order_id bigint(20) UNSIGNED DEFAULT NULL,
            jobs_limit int(11) NOT NULL DEFAULT 0,
            jobs_remaining int(11) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'active',
            activated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY package_id (package_id)
        ) $charset",
    ];

    foreach ( $tables as $sql ) {
        dbDelta( $sql );
    }
}
