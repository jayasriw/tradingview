<?php
/**
 * Template Name: Login
 *
 * @package CiviJobs
 */

// Redirect logged-in users
if ( is_user_logged_in() ) {
    wp_safe_redirect( civijobs_get_dashboard_url() );
    exit;
}

$error   = '';
$redirect_to = sanitize_url( $_GET['redirect_to'] ?? '' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['civi_login_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_text_field( $_POST['civi_login_nonce'] ), 'civi_login' ) ) {
        $creds = [
            'user_login'    => sanitize_text_field( $_POST['username'] ?? '' ),
            'user_password' => $_POST['password'] ?? '',
            'remember'      => isset( $_POST['remember'] ),
        ];
        $user = wp_signon( $creds, is_ssl() );
        if ( is_wp_error( $user ) ) {
            $error = $user->get_error_message();
        } else {
            $to = $redirect_to ?: civijobs_get_dashboard_url( civijobs_get_user_role( $user->ID ) );
            wp_safe_redirect( $to );
            exit;
        }
    }
}

get_header();
?>
<main class="site-main" id="main" style="background:var(--color-gray-50);min-height:calc(100vh - 140px);display:flex;align-items:center;padding:40px 0">
    <div class="container">
        <div class="auth-card" style="max-width:460px;margin:0 auto;background:#fff;border-radius:var(--radius-xl);padding:40px;box-shadow:0 4px 24px rgba(0,0,0,.08)">

            <div style="text-align:center;margin-bottom:32px">
                <?php if ( has_custom_logo() ) the_custom_logo(); ?>
                <h1 style="font-size:1.625rem;font-weight:800;color:var(--color-gray-900);margin:16px 0 6px"><?php esc_html_e( 'Welcome back', 'civijobs' ); ?></h1>
                <p style="color:var(--color-gray-500);margin:0"><?php esc_html_e( "Sign in to your account", 'civijobs' ); ?></p>
            </div>

            <?php if ( $error ) : ?>
                <div class="alert alert-danger" style="margin-bottom:20px"><?php echo wp_kses_post( $error ); ?></div>
            <?php endif; ?>

            <?php if ( isset( $_GET['registered'] ) ) : ?>
                <div class="alert alert-success" style="margin-bottom:20px">✅ <?php esc_html_e( 'Account created! You can now log in.', 'civijobs' ); ?></div>
            <?php endif; ?>

            <form method="post" id="login-form">
                <?php wp_nonce_field( 'civi_login', 'civi_login_nonce' ); ?>
                <?php if ( $redirect_to ) : ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
                <?php endif; ?>

                <div class="form-group" style="margin-bottom:16px">
                    <label for="username" style="font-size:0.875rem;font-weight:600;color:var(--color-gray-700);display:block;margin-bottom:6px">
                        <?php esc_html_e( 'Email or Username', 'civijobs' ); ?>
                    </label>
                    <input type="text" id="username" name="username" class="form-control"
                           value="<?php echo esc_attr( $_POST['username'] ?? '' ); ?>"
                           required autocomplete="username" autofocus>
                </div>

                <div class="form-group" style="margin-bottom:20px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <label for="password" style="font-size:0.875rem;font-weight:600;color:var(--color-gray-700)">
                            <?php esc_html_e( 'Password', 'civijobs' ); ?>
                        </label>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="font-size:0.8rem;color:var(--color-primary)">
                            <?php esc_html_e( 'Forgot password?', 'civijobs' ); ?>
                        </a>
                    </div>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                </div>

                <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--color-gray-600);margin-bottom:20px;cursor:pointer">
                    <input type="checkbox" name="remember" value="1">
                    <?php esc_html_e( 'Keep me logged in', 'civijobs' ); ?>
                </label>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <?php esc_html_e( 'Sign In', 'civijobs' ); ?>
                </button>
            </form>

            <div style="text-align:center;margin-top:24px;padding-top:24px;border-top:1px solid var(--color-gray-100)">
                <p style="font-size:0.875rem;color:var(--color-gray-600)">
                    <?php esc_html_e( "Don't have an account?", 'civijobs' ); ?>
                    <a href="<?php echo esc_url( home_url( '/register' ) ); ?>" style="color:var(--color-primary);font-weight:600">
                        <?php esc_html_e( 'Sign up', 'civijobs' ); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</main>
<?php get_footer();
