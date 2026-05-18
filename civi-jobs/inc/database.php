<?php
/**
 * Database table creation and management
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create all custom database tables.
 */
function civijobs_create_tables(): void {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Messages
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_messages (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        thread_id   VARCHAR(64)     NOT NULL DEFAULT '',
        sender_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
        receiver_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        job_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
        subject     VARCHAR(255)    NOT NULL DEFAULT '',
        body        LONGTEXT        NOT NULL,
        is_read     TINYINT(1)      NOT NULL DEFAULT 0,
        sender_deleted   TINYINT(1) NOT NULL DEFAULT 0,
        receiver_deleted TINYINT(1) NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY thread_id (thread_id),
        KEY sender_id (sender_id),
        KEY receiver_id (receiver_id)
    ) $charset_collate;" );

    // Notifications
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_notifications (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
        type       VARCHAR(50)     NOT NULL DEFAULT '',
        title      VARCHAR(255)    NOT NULL DEFAULT '',
        message    TEXT            NOT NULL,
        link       VARCHAR(500)    NOT NULL DEFAULT '',
        is_read    TINYINT(1)      NOT NULL DEFAULT 0,
        created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY is_read (is_read)
    ) $charset_collate;" );

    // Meetings
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_meetings (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        employer_id  BIGINT UNSIGNED NOT NULL DEFAULT 0,
        candidate_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        job_id       BIGINT UNSIGNED NOT NULL DEFAULT 0,
        title        VARCHAR(255)    NOT NULL DEFAULT '',
        description  TEXT            NOT NULL,
        date_time    DATETIME        NOT NULL,
        status       VARCHAR(20)     NOT NULL DEFAULT 'pending',
        location     VARCHAR(255)    NOT NULL DEFAULT '',
        meeting_url  VARCHAR(500)    NOT NULL DEFAULT '',
        created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY employer_id (employer_id),
        KEY candidate_id (candidate_id),
        KEY status (status)
    ) $charset_collate;" );

    // Job Alerts
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_job_alerts (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
        keywords     VARCHAR(255)    NOT NULL DEFAULT '',
        location     VARCHAR(255)    NOT NULL DEFAULT '',
        job_category VARCHAR(255)    NOT NULL DEFAULT '',
        job_type     VARCHAR(100)    NOT NULL DEFAULT '',
        frequency    VARCHAR(10)     NOT NULL DEFAULT 'daily',
        last_sent    DATETIME                 DEFAULT NULL,
        created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY frequency (frequency)
    ) $charset_collate;" );

    // Applications
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_applications (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        job_id       BIGINT UNSIGNED NOT NULL DEFAULT 0,
        candidate_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        cover_letter LONGTEXT        NOT NULL,
        cv_file      VARCHAR(500)    NOT NULL DEFAULT '',
        status       VARCHAR(20)     NOT NULL DEFAULT 'pending',
        applied_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY job_candidate (job_id, candidate_id),
        KEY job_id (job_id),
        KEY candidate_id (candidate_id),
        KEY status (status)
    ) $charset_collate;" );

    // Wallet
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_wallet (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
        balance    DECIMAL(10,2)   NOT NULL DEFAULT '0.00',
        created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id)
    ) $charset_collate;" );

    // Wallet Transactions
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_wallet_transactions (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
        amount       DECIMAL(10,2)   NOT NULL DEFAULT '0.00',
        type         VARCHAR(10)     NOT NULL DEFAULT 'credit',
        description  VARCHAR(500)    NOT NULL DEFAULT '',
        reference_id VARCHAR(100)    NOT NULL DEFAULT '',
        created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY type (type)
    ) $charset_collate;" );

    // Company Reviews
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_company_reviews (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        company_id  BIGINT UNSIGNED NOT NULL DEFAULT 0,
        reviewer_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        rating      TINYINT         NOT NULL DEFAULT 5,
        title       VARCHAR(255)    NOT NULL DEFAULT '',
        review      LONGTEXT        NOT NULL,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY company_reviewer (company_id, reviewer_id),
        KEY company_id (company_id),
        KEY rating (rating)
    ) $charset_collate;" );

    // Saved Jobs
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_saved_jobs (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
        job_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_job (user_id, job_id),
        KEY user_id (user_id)
    ) $charset_collate;" );

    // Package Orders
    dbDelta( "CREATE TABLE {$wpdb->prefix}civi_package_orders (
        id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id        BIGINT UNSIGNED NOT NULL DEFAULT 0,
        package_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
        order_id       BIGINT UNSIGNED NOT NULL DEFAULT 0,
        status         VARCHAR(20)     NOT NULL DEFAULT 'active',
        jobs_remaining INT             NOT NULL DEFAULT 0,
        expires_at     DATETIME                 DEFAULT NULL,
        created_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY status (status)
    ) $charset_collate;" );

    update_option( 'civijobs_db_version', '1.0.0' );
}
add_action( 'after_switch_theme', 'civijobs_create_tables' );
