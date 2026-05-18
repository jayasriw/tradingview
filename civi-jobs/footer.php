<?php
/**
 * Site footer
 *
 * @package CiviJobs
 */
?>
    </main><!-- /.site-main -->

    <footer class="site-footer">
        <div class="footer-widgets">
            <div class="container">
                <div class="footer-widgets-grid">
                    <div class="footer-widget footer-about">
                        <?php if ( has_custom_logo() ) : ?>
                            <div class="footer-logo"><?php the_custom_logo(); ?></div>
                        <?php else : ?>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo-text">
                                <?php bloginfo( 'name' ); ?>
                            </a>
                        <?php endif; ?>
                        <p class="footer-tagline">
                            <?php echo esc_html( civijobs_get_setting( 'site_tagline', __( 'Find your dream job or hire top talent.', 'civijobs' ) ) ); ?>
                        </p>
                        <div class="footer-social">
                            <?php foreach ( [
                                'twitter'  => [ 'Twitter/X',   '𝕏' ],
                                'facebook' => [ 'Facebook',    'f' ],
                                'linkedin' => [ 'LinkedIn',    'in' ],
                                'instagram'=> [ 'Instagram',   '✦' ],
                            ] as $key => [ $label, $icon ] ) :
                                $url = civijobs_get_setting( "social_{$key}", '' );
                                if ( $url ) : ?>
                            <a href="<?php echo esc_url( $url ); ?>" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $label ); ?>">
                                <?php echo esc_html( $icon ); ?>
                            </a>
                            <?php endif;
                            endforeach; ?>
                        </div>
                    </div>

                    <div class="footer-widget">
                        <h4 class="footer-widget-title"><?php esc_html_e( 'For Job Seekers', 'civijobs' ); ?></h4>
                        <ul class="footer-links">
                            <li><a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>"><?php esc_html_e( 'Browse Jobs', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/candidates' ) ); ?>"><?php esc_html_e( 'Candidate Dashboard', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/register' ) ); ?>"><?php esc_html_e( 'Create Account', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>"><?php esc_html_e( 'Premium Plans', 'civijobs' ); ?></a></li>
                        </ul>
                    </div>

                    <div class="footer-widget">
                        <h4 class="footer-widget-title"><?php esc_html_e( 'For Employers', 'civijobs' ); ?></h4>
                        <ul class="footer-links">
                            <li><a href="<?php echo esc_url( home_url( '/post-job' ) ); ?>"><?php esc_html_e( 'Post a Job', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/companies' ) ); ?>"><?php esc_html_e( 'Browse Candidates', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>"><?php esc_html_e( 'Pricing Plans', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/register' ) ); ?>"><?php esc_html_e( 'Employer Sign Up', 'civijobs' ); ?></a></li>
                        </ul>
                    </div>

                    <div class="footer-widget">
                        <h4 class="footer-widget-title"><?php esc_html_e( 'Company', 'civijobs' ); ?></h4>
                        <ul class="footer-links">
                            <li><a href="<?php echo esc_url( home_url( '/about' ) ); ?>"><?php esc_html_e( 'About Us', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'civijobs' ); ?></a></li>
                            <li><a href="<?php echo esc_url( home_url( '/terms' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'civijobs' ); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-inner">
                    <p class="footer-copy">
                        &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>.
                        <?php esc_html_e( 'All rights reserved.', 'civijobs' ); ?>
                    </p>
                    <nav class="footer-bottom-nav" aria-label="<?php esc_attr_e( 'Footer', 'civijobs' ); ?>">
                        <?php wp_nav_menu( [
                            'theme_location' => 'footer',
                            'container'      => false,
                            'menu_class'     => 'footer-bottom-links',
                            'depth'          => 1,
                            'fallback_cb'    => false,
                        ] ); ?>
                    </nav>
                </div>
            </div>
        </div>
    </footer><!-- /.site-footer -->

    <!-- Compose Message Modal -->
    <div id="compose-message-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="compose-modal-title">
        <div class="modal-overlay" data-modal-close></div>
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="compose-modal-title"><?php esc_html_e( 'New Message', 'civijobs' ); ?></h3>
                <button class="modal-close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'civijobs' ); ?>">&times;</button>
            </div>
            <div class="modal-body">
                <form id="compose-message-form">
                    <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                    <div class="form-group">
                        <label><?php esc_html_e( 'To (User ID or username)', 'civijobs' ); ?></label>
                        <input type="text" name="receiver_id" class="form-control" required placeholder="<?php esc_attr_e( 'Search recipient…', 'civijobs' ); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php esc_html_e( 'Subject', 'civijobs' ); ?></label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><?php esc_html_e( 'Message', 'civijobs' ); ?></label>
                        <textarea name="body" class="form-control" rows="5" required></textarea>
                    </div>
                    <input type="hidden" name="job_id" value="0">
                    <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Send Message', 'civijobs' ); ?></button>
                </form>
            </div>
        </div>
    </div>

<?php wp_footer(); ?>
</body>
</html>
