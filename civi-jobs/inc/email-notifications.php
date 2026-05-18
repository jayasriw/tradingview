<?php
/**
 * Email notification system
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Set email content type to HTML.
 */
add_filter( 'wp_mail_content_type', static fn() => 'text/html' );

/**
 * Send a themed HTML email.
 */
function civijobs_send_email( string $to, string $subject, string $template, array $vars = [] ): bool {
    if ( ! is_email( $to ) ) {
        return false;
    }

    $from_name  = civijobs_get_setting( 'email_from_name', get_bloginfo( 'name' ) );
    $from_email = civijobs_get_setting( 'email_from_address', get_option( 'admin_email' ) );
    $headers    = [ "From: {$from_name} <{$from_email}>", 'Content-Type: text/html; charset=UTF-8' ];
    $body       = civijobs_get_email_template( $template, $vars );

    return wp_mail( $to, $subject, $body, $headers );
}

/**
 * Render an email template with variable substitution.
 */
function civijobs_get_email_template( string $template_name, array $vars = [] ): string {
    $file = CIVIJOBS_DIR . "/email-templates/{$template_name}.php";

    if ( ! file_exists( $file ) ) {
        // Fallback: plain text in basic wrapper
        return civijobs_email_wrap( implode( '<br>', $vars ), $vars['subject'] ?? '' );
    }

    ob_start();
    extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
    include $file;
    return ob_get_clean();
}

/**
 * Wrap content in a basic HTML email layout.
 */
function civijobs_email_wrap( string $content, string $preheader = '' ): string {
    $site_name = get_bloginfo( 'name' );
    $site_url  = home_url( '/' );
    $logo_url  = civijobs_get_setting( 'email_logo', '' );

    ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $site_name ); ?></title>
<style>
  body{margin:0;padding:0;background:#f3f4f6;font-family:'Inter',Arial,sans-serif;font-size:15px;color:#374151}
  .wrap{max-width:600px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}
  .header{background:#4f46e5;padding:28px 32px;text-align:center}
  .header a{color:#fff;font-size:22px;font-weight:700;text-decoration:none}
  .body{padding:32px}
  .footer{background:#f9fafb;padding:20px 32px;text-align:center;font-size:13px;color:#9ca3af}
  .btn{display:inline-block;padding:12px 28px;background:#4f46e5;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;margin:16px 0}
  .divider{border:none;border-top:1px solid #e5e7eb;margin:24px 0}
  h2{color:#111827;margin-top:0}
  p{line-height:1.7;color:#4b5563}
</style>
</head>
<body>
<?php if ( $preheader ) : ?><span style="display:none;max-height:0;overflow:hidden"><?php echo esc_html( $preheader ); ?></span><?php endif; ?>
<div class="wrap">
  <div class="header">
    <?php if ( $logo_url ) : ?>
      <a href="<?php echo esc_url( $site_url ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" style="max-height:50px"></a>
    <?php else : ?>
      <a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_html( $site_name ); ?></a>
    <?php endif; ?>
  </div>
  <div class="body">
    <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
  </div>
  <div class="footer">
    &copy; <?php echo esc_html( gmdate( 'Y' ) . ' ' . $site_name ); ?> &bull;
    <a href="<?php echo esc_url( $site_url ); ?>" style="color:#9ca3af"><?php echo esc_html( $site_url ); ?></a>
  </div>
</div>
</body></html>
    <?php
    return ob_get_clean();
}

// ---- Email trigger functions ----

function civijobs_email_new_user( int $user_id ): void {
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return;
    }
    $role = civijobs_get_user_role( $user_id );
    civijobs_send_email(
        $user->user_email,
        sprintf( __( 'Welcome to %s!', 'civijobs' ), get_bloginfo( 'name' ) ),
        'new-user',
        [
            'user_name'     => $user->display_name,
            'role'          => $role,
            'dashboard_url' => civijobs_get_dashboard_url( $role ),
            'site_name'     => get_bloginfo( 'name' ),
        ]
    );
}
add_action( 'civijobs_user_registered', 'civijobs_email_new_user' );

function civijobs_email_application_submitted( int $application_id ): void {
    global $wpdb;
    $app = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}civi_applications WHERE id = %d", $application_id
    ) );
    if ( ! $app ) {
        return;
    }

    $candidate = get_userdata( $app->candidate_id );
    $job_title = get_the_title( $app->job_id );
    $employer  = get_userdata( (int) get_post_meta( $app->job_id, '_employer_id', true ) );

    // Confirm to candidate
    if ( $candidate ) {
        civijobs_send_email(
            $candidate->user_email,
            sprintf( __( 'Application submitted for %s', 'civijobs' ), $job_title ),
            'application-submitted',
            [
                'candidate_name' => $candidate->display_name,
                'job_title'      => $job_title,
                'job_url'        => get_permalink( $app->job_id ),
                'applied_at'     => $app->applied_at,
            ]
        );
    }

    // Notify employer
    if ( $employer ) {
        civijobs_send_email(
            $employer->user_email,
            sprintf( __( 'New application for %s', 'civijobs' ), $job_title ),
            'new-application',
            [
                'employer_name'  => $employer->display_name,
                'candidate_name' => $candidate ? $candidate->display_name : __( 'A candidate', 'civijobs' ),
                'job_title'      => $job_title,
                'dashboard_url'  => civijobs_get_dashboard_url( 'employer' ),
            ]
        );
    }
}

function civijobs_email_application_status_changed( int $application_id ): void {
    global $wpdb;
    $app = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}civi_applications WHERE id = %d", $application_id
    ) );
    if ( ! $app ) {
        return;
    }

    $candidate = get_userdata( $app->candidate_id );
    if ( ! $candidate ) {
        return;
    }

    civijobs_send_email(
        $candidate->user_email,
        sprintf( __( 'Update on your application for %s', 'civijobs' ), get_the_title( $app->job_id ) ),
        'application-status',
        [
            'candidate_name' => $candidate->display_name,
            'job_title'      => get_the_title( $app->job_id ),
            'status'         => $app->status,
            'dashboard_url'  => civijobs_get_dashboard_url( 'candidate' ),
        ]
    );
}

function civijobs_email_new_message( int $message_id ): void {
    global $wpdb;
    $msg = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}civi_messages WHERE id = %d", $message_id
    ) );
    if ( ! $msg ) {
        return;
    }

    $receiver = get_userdata( $msg->receiver_id );
    $sender   = get_userdata( $msg->sender_id );
    if ( ! $receiver ) {
        return;
    }

    civijobs_send_email(
        $receiver->user_email,
        sprintf( __( 'New message from %s', 'civijobs' ), $sender ? $sender->display_name : __( 'Someone', 'civijobs' ) ),
        'new-message',
        [
            'receiver_name' => $receiver->display_name,
            'sender_name'   => $sender ? $sender->display_name : '',
            'subject'       => $msg->subject,
            'preview'       => wp_trim_words( $msg->body, 20 ),
            'dashboard_url' => civijobs_get_dashboard_url( civijobs_get_user_role( $msg->receiver_id ) ),
        ]
    );
}

function civijobs_email_job_approved( int $job_id ): void {
    $employer_id = (int) get_post_meta( $job_id, '_employer_id', true );
    $employer    = get_userdata( $employer_id );
    if ( ! $employer ) {
        return;
    }
    civijobs_send_email(
        $employer->user_email,
        sprintf( __( 'Your job "%s" has been approved', 'civijobs' ), get_the_title( $job_id ) ),
        'job-approved',
        [
            'employer_name' => $employer->display_name,
            'job_title'     => get_the_title( $job_id ),
            'job_url'       => get_permalink( $job_id ),
        ]
    );
}

function civijobs_email_job_expired( int $job_id ): void {
    $employer_id = (int) get_post_meta( $job_id, '_employer_id', true );
    $employer    = get_userdata( $employer_id );
    if ( ! $employer ) {
        return;
    }
    civijobs_send_email(
        $employer->user_email,
        sprintf( __( 'Your job "%s" has expired', 'civijobs' ), get_the_title( $job_id ) ),
        'job-expired',
        [
            'employer_name'  => $employer->display_name,
            'job_title'      => get_the_title( $job_id ),
            'dashboard_url'  => civijobs_get_dashboard_url( 'employer' ),
        ]
    );
}

function civijobs_email_package_activated( int $user_id, int $package_id ): void {
    $user    = get_userdata( $user_id );
    $package = get_the_title( $package_id );
    if ( ! $user ) {
        return;
    }
    civijobs_send_email(
        $user->user_email,
        sprintf( __( 'Package "%s" activated!', 'civijobs' ), $package ),
        'package-activated',
        [
            'user_name'    => $user->display_name,
            'package_name' => $package,
            'dashboard_url'=> civijobs_get_dashboard_url( civijobs_get_user_role( $user_id ) ),
        ]
    );
}

function civijobs_email_meeting_scheduled( int $meeting_id ): void {
    global $wpdb;
    $meeting = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}civi_meetings WHERE id = %d", $meeting_id
    ) );
    if ( ! $meeting ) {
        return;
    }
    $candidate = get_userdata( $meeting->candidate_id );
    $employer  = get_userdata( $meeting->employer_id );
    if ( ! $candidate ) {
        return;
    }
    civijobs_send_email(
        $candidate->user_email,
        sprintf( __( 'Meeting request from %s', 'civijobs' ), $employer ? $employer->display_name : '' ),
        'meeting-scheduled',
        [
            'candidate_name' => $candidate->display_name,
            'employer_name'  => $employer ? $employer->display_name : '',
            'meeting_title'  => $meeting->title,
            'meeting_date'   => gmdate( 'l, F j, Y \a\t g:i A', strtotime( $meeting->date_time ) ),
            'location'       => $meeting->location,
            'meeting_url'    => $meeting->meeting_url,
            'dashboard_url'  => civijobs_get_dashboard_url( 'candidate' ),
        ]
    );
}

function civijobs_email_job_alert( int $user_id, array $jobs ): void {
    $user = get_userdata( $user_id );
    if ( ! $user || empty( $jobs ) ) {
        return;
    }
    civijobs_send_email(
        $user->user_email,
        sprintf( __( '%d new jobs matching your alert', 'civijobs' ), count( $jobs ) ),
        'job-alert',
        [
            'user_name' => $user->display_name,
            'jobs'      => $jobs,
            'jobs_url'  => get_post_type_archive_link( 'civi_job' ),
        ]
    );
}
