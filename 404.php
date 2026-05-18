<?php
/**
 * 404 Not Found template
 *
 * @package JobPortal
 */

get_header();
?>
<div class="container" style="padding:80px 20px;text-align:center">
    <div class="empty-state">
        <div style="font-size:6rem;margin-bottom:16px">&#128269;</div>
        <h1 style="font-size:2rem;font-weight:800;color:var(--color-gray-900);margin-bottom:12px">
            <?php esc_html_e( 'Page Not Found', 'jobportal' ); ?>
        </h1>
        <p style="color:var(--color-gray-500);font-size:1.1rem;max-width:480px;margin:0 auto 32px">
            <?php esc_html_e( "The page you're looking for doesn't exist or has been moved.", 'jobportal' ); ?>
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
                <?php esc_html_e( 'Go Home', 'jobportal' ); ?>
            </a>
            <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-outline">
                <?php esc_html_e( 'Browse Jobs', 'jobportal' ); ?>
            </a>
        </div>
        <div style="margin-top:40px">
            <?php get_search_form(); ?>
        </div>
    </div>
</div>
<?php
get_footer();
