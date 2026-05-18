<?php
/**
 * Template Name: Register
 *
 * @package CiviJobs
 */

if ( is_user_logged_in() ) {
    wp_safe_redirect( civijobs_get_dashboard_url() );
    exit;
}

if ( ! civijobs_get_setting( 'enable_registration', '1' ) ) {
    wp_safe_redirect( home_url( '/' ) );
    exit;
}

$error   = '';
$success = false;
$role    = sanitize_key( $_POST['civi_role'] ?? $_GET['role'] ?? 'civi_candidate' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['civi_register_nonce'] ) ) {
    if ( ! wp_verify_nonce( sanitize_text_field( $_POST['civi_register_nonce'] ), 'civi_register' ) ) {
        $error = __( 'Security check failed. Please try again.', 'civijobs' );
    } else {
        $email     = sanitize_email( $_POST['email'] ?? '' );
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $role      = sanitize_key( $_POST['civi_role'] ?? 'civi_candidate' );
        $name      = sanitize_text_field( $_POST['display_name'] ?? '' );
        $company   = sanitize_text_field( $_POST['company_name'] ?? '' );
        $terms     = isset( $_POST['agree_terms'] );

        if ( ! $email || ! is_email( $email ) ) {
            $error = __( 'Please enter a valid email address.', 'civijobs' );
        } elseif ( ! $password || strlen( $password ) < 6 ) {
            $error = __( 'Password must be at least 6 characters.', 'civijobs' );
        } elseif ( $password !== $password2 ) {
            $error = __( 'Passwords do not match.', 'civijobs' );
        } elseif ( ! $terms ) {
            $error = __( 'Please accept the terms of service.', 'civijobs' );
        } elseif ( email_exists( $email ) ) {
            $error = __( 'An account with this email already exists.', 'civijobs' );
        } else {
            $user_id = wp_insert_user( [
                'user_login'   => $email,
                'user_email'   => $email,
                'user_pass'    => $password,
                'display_name' => $name ?: $email,
                'role'         => in_array( $role, [ 'civi_employer', 'civi_candidate' ], true ) ? $role : 'civi_candidate',
            ] );

            if ( is_wp_error( $user_id ) ) {
                $error = $user_id->get_error_message();
            } else {
                // Auto-login
                wp_set_current_user( $user_id );
                wp_set_auth_cookie( $user_id );

                // Create company post for employer
                if ( $role === 'civi_employer' && $company ) {
                    $company_id = wp_insert_post( [
                        'post_title'  => $company,
                        'post_type'   => 'civi_company',
                        'post_status' => 'publish',
                        'post_author' => $user_id,
                    ] );
                    if ( $company_id && ! is_wp_error( $company_id ) ) {
                        update_post_meta( $company_id, '_owner_id', $user_id );
                        update_user_meta( $user_id, 'civi_company_id', $company_id );
                    }
                }

                do_action( 'civijobs_user_registered', $user_id );

                wp_safe_redirect( civijobs_get_dashboard_url( civijobs_get_user_role( $user_id ) ) );
                exit;
            }
        }
    }
}

get_header();
?>
<main class="site-main" id="main" style="background:var(--color-gray-50);min-height:calc(100vh - 140px);display:flex;align-items:center;padding:40px 0">
    <div class="container">
        <div class="auth-card" style="max-width:520px;margin:0 auto;background:#fff;border-radius:var(--radius-xl);padding:40px;box-shadow:0 4px 24px rgba(0,0,0,.08)">

            <div style="text-align:center;margin-bottom:28px">
                <?php if ( has_custom_logo() ) the_custom_logo(); ?>
                <h1 style="font-size:1.625rem;font-weight:800;color:var(--color-gray-900);margin:16px 0 6px"><?php esc_html_e( 'Create your account', 'civijobs' ); ?></h1>
                <p style="color:var(--color-gray-500);margin:0"><?php esc_html_e( 'Join thousands of professionals on our platform', 'civijobs' ); ?></p>
            </div>

            <!-- Role Tabs -->
            <div class="role-tabs" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:28px">
                <button type="button" class="role-tab-btn <?php echo $role !== 'civi_employer' ? 'active' : ''; ?>" data-role="civi_candidate"
                    style="padding:12px;border-radius:var(--radius);border:2px solid <?php echo $role !== 'civi_employer' ? 'var(--color-primary)' : 'var(--color-gray-200)'; ?>;background:<?php echo $role !== 'civi_employer' ? 'var(--color-primary-light)' : '#fff'; ?>;font-weight:600;cursor:pointer;transition:all .15s">
                    👤 <?php esc_html_e( 'Job Seeker', 'civijobs' ); ?>
                </button>
                <button type="button" class="role-tab-btn <?php echo $role === 'civi_employer' ? 'active' : ''; ?>" data-role="civi_employer"
                    style="padding:12px;border-radius:var(--radius);border:2px solid <?php echo $role === 'civi_employer' ? 'var(--color-primary)' : 'var(--color-gray-200)'; ?>;background:<?php echo $role === 'civi_employer' ? 'var(--color-primary-light)' : '#fff'; ?>;font-weight:600;cursor:pointer;transition:all .15s">
                    🏢 <?php esc_html_e( 'Employer', 'civijobs' ); ?>
                </button>
            </div>

            <?php if ( $error ) : ?>
                <div class="alert alert-danger" style="margin-bottom:20px"><?php echo wp_kses_post( $error ); ?></div>
            <?php endif; ?>

            <form method="post" id="register-form">
                <?php wp_nonce_field( 'civi_register', 'civi_register_nonce' ); ?>
                <input type="hidden" name="civi_role" id="civi-role-input" value="<?php echo esc_attr( $role ); ?>">

                <div class="form-group" style="margin-bottom:16px">
                    <label style="font-size:0.875rem;font-weight:600;color:var(--color-gray-700);display:block;margin-bottom:6px">
                        <?php esc_html_e( 'Full Name', 'civijobs' ); ?> <span style="color:var(--color-danger)">*</span>
                    </label>
                    <input type="text" name="display_name" class="form-control" required
                           value="<?php echo esc_attr( $_POST['display_name'] ?? '' ); ?>"
                           placeholder="<?php esc_attr_e( 'Your full name', 'civijobs' ); ?>">
                </div>

                <!-- Employer-only: company name -->
                <div class="form-group employer-only" style="margin-bottom:16px;<?php echo $role !== 'civi_employer' ? 'display:none' : ''; ?>">
                    <label style="font-size:0.875rem;font-weight:600;color:var(--color-gray-700);display:block;margin-bottom:6px">
                        <?php esc_html_e( 'Company Name', 'civijobs' ); ?> <span style="color:var(--color-danger)">*</span>
                    </label>
                    <input type="text" name="company_name" class="form-control"
                           value="<?php echo esc_attr( $_POST['company_name'] ?? '' ); ?>"
                           placeholder="<?php esc_attr_e( 'Your company name', 'civijobs' ); ?>">
                </div>

                <div class="form-group" style="margin-bottom:16px">
                    <label style="font-size:0.875rem;font-weight:600;color:var(--color-gray-700);display:block;margin-bottom:6px">
                        <?php esc_html_e( 'Email Address', 'civijobs' ); ?> <span style="color:var(--color-danger)">*</span>
                    </label>
                    <input type="email" name="email" class="form-control" required
                           value="<?php echo esc_attr( $_POST['email'] ?? '' ); ?>"
                           placeholder="you@example.com" autocomplete="email">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                    <div class="form-group">
                        <label style="font-size:0.875rem;font-weight:600;color:var(--color-gray-700);display:block;margin-bottom:6px">
                            <?php esc_html_e( 'Password', 'civijobs' ); ?> <span style="color:var(--color-danger)">*</span>
                        </label>
                        <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label style="font-size:0.875rem;font-weight:600;color:var(--color-gray-700);display:block;margin-bottom:6px">
                            <?php esc_html_e( 'Confirm Password', 'civijobs' ); ?> <span style="color:var(--color-danger)">*</span>
                        </label>
                        <input type="password" name="password2" class="form-control" required autocomplete="new-password">
                    </div>
                </div>

                <label style="display:flex;align-items:flex-start;gap:8px;font-size:0.8rem;color:var(--color-gray-600);margin-bottom:24px;cursor:pointer">
                    <input type="checkbox" name="agree_terms" value="1" style="margin-top:2px;flex-shrink:0">
                    <span><?php
                        printf(
                            esc_html__( 'I agree to the %s and %s', 'civijobs' ),
                            '<a href="' . esc_url( home_url( '/terms' ) ) . '" target="_blank">' . esc_html__( 'Terms of Service', 'civijobs' ) . '</a>',
                            '<a href="' . esc_url( home_url( '/privacy-policy' ) ) . '" target="_blank">' . esc_html__( 'Privacy Policy', 'civijobs' ) . '</a>'
                        );
                    ?></span>
                </label>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <?php esc_html_e( 'Create Account', 'civijobs' ); ?>
                </button>
            </form>

            <div style="text-align:center;margin-top:24px;padding-top:24px;border-top:1px solid var(--color-gray-100)">
                <p style="font-size:0.875rem;color:var(--color-gray-600)">
                    <?php esc_html_e( 'Already have an account?', 'civijobs' ); ?>
                    <a href="<?php echo esc_url( home_url( '/login' ) ); ?>" style="color:var(--color-primary);font-weight:600"><?php esc_html_e( 'Sign in', 'civijobs' ); ?></a>
                </p>
            </div>
        </div>
    </div>
</main>

<script>
document.querySelectorAll('.role-tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const role = this.dataset.role;
        document.getElementById('civi-role-input').value = role;
        document.querySelectorAll('.role-tab-btn').forEach(b => {
            b.style.borderColor = '#e5e7eb';
            b.style.background = '#fff';
        });
        this.style.borderColor = '#4f46e5';
        this.style.background = '#eef2ff';
        document.querySelectorAll('.employer-only').forEach(el => {
            el.style.display = role === 'civi_employer' ? 'block' : 'none';
        });
    });
});
</script>
<?php get_footer();
