<?php
/**
 * Site Footer
 *
 * @package JobPortal
 */

$user_id = get_current_user_id();
?>
</main><!-- #main-content -->

<footer class="site-footer">
    <div class="footer-widgets container">
        <div class="footer-widget">
            <div class="footer-logo"><?php bloginfo( 'name' ); ?></div>
            <p class="footer-tagline"><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Find your dream job today.', 'jobportal' ) ); ?></p>
        </div>
        <div class="footer-widget">
            <h4 class="footer-widget-title"><?php esc_html_e( 'For Job Seekers', 'jobportal' ); ?></h4>
            <ul class="footer-links">
                <li><a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>"><?php esc_html_e( 'Browse Jobs', 'jobportal' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/companies' ) ); ?>"><?php esc_html_e( 'Companies', 'jobportal' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/register' ) ); ?>"><?php esc_html_e( 'Create Account', 'jobportal' ); ?></a></li>
            </ul>
        </div>
        <div class="footer-widget">
            <h4 class="footer-widget-title"><?php esc_html_e( 'For Employers', 'jobportal' ); ?></h4>
            <ul class="footer-links">
                <li><a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>"><?php esc_html_e( 'Pricing', 'jobportal' ); ?></a></li>
                <li><a href="<?php echo esc_url( home_url( '/candidates' ) ); ?>"><?php esc_html_e( 'Browse Candidates', 'jobportal' ); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p class="footer-copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'jobportal' ); ?></p>
        </div>
    </div>
</footer>

<?php if ( $user_id ) : ?>
<div id="compose-message-modal" class="modal" role="dialog" aria-modal="true">
    <div class="modal-overlay" data-modal-close></div>
    <div class="modal-dialog" style="max-width:480px">
        <div class="modal-header">
            <h3><?php esc_html_e( 'New Message', 'jobportal' ); ?></h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
            <form id="compose-message-form">
                <?php wp_nonce_field( 'jobportal_nonce', 'nonce' ); ?>
                <div class="form-group">
                    <label><?php esc_html_e( 'To', 'jobportal' ); ?></label>
                    <input type="hidden" name="receiver_id" id="compose-receiver-id">
                    <input type="text" id="compose-receiver-name" class="form-control" placeholder="<?php esc_attr_e( 'Recipient name', 'jobportal' ); ?>" readonly>
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Message', 'jobportal' ); ?></label>
                    <textarea name="message" class="form-control" rows="5" required placeholder="<?php esc_attr_e( 'Write your message...', 'jobportal' ); ?>"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Send Message', 'jobportal' ); ?></button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
