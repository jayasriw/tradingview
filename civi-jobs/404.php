<?php
/**
 * 404 Not Found template
 *
 * @package CiviJobs
 */

get_header(); ?>

<main class="site-main" id="main">
    <div class="container">
        <div class="error-404" style="text-align:center;padding:100px 20px">
            <div class="error-code" style="font-size:8rem;font-weight:900;color:var(--color-gray-100);line-height:1">404</div>
            <h1 style="font-size:2rem;font-weight:800;color:var(--color-gray-900);margin-top:-20px">
                <?php esc_html_e( 'Page Not Found', 'civijobs' ); ?>
            </h1>
            <p style="color:var(--color-gray-600);font-size:1.05rem;margin-bottom:32px;max-width:480px;margin-left:auto;margin-right:auto">
                <?php esc_html_e( "The page you're looking for doesn't exist or has been moved.", 'civijobs' ); ?>
            </p>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:48px">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
                    <?php esc_html_e( 'Go Home', 'civijobs' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-outline">
                    <?php esc_html_e( 'Browse Jobs', 'civijobs' ); ?>
                </a>
            </div>

            <div style="max-width:420px;margin:0 auto">
                <?php get_search_form(); ?>
            </div>

            <div class="quick-links" style="margin-top:40px">
                <p style="font-size:0.875rem;color:var(--color-gray-500);margin-bottom:12px"><?php esc_html_e( 'Quick links:', 'civijobs' ); ?></p>
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                    <?php
                    $links = [
                        __( 'Jobs', 'civijobs' )      => '/jobs',
                        __( 'Companies', 'civijobs' ) => '/companies',
                        __( 'Pricing', 'civijobs' )   => '/pricing',
                        __( 'Contact', 'civijobs' )   => '/contact',
                    ];
                    foreach ( $links as $label => $path ) : ?>
                        <a href="<?php echo esc_url( home_url( $path ) ); ?>" class="badge badge-gray" style="font-size:0.875rem;padding:6px 14px">
                            <?php echo esc_html( $label ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer();
