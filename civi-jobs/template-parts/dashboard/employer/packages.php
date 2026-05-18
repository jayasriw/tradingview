<?php
/**
 * Employer: Packages
 *
 * @package CiviJobs
 */

$user_id        = get_current_user_id();
$active_package = civijobs_get_user_package( $user_id );
$packages       = civijobs_get_packages( 'employer' );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Employer Packages', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Choose a plan to start posting jobs.', 'civijobs' ); ?></p>
    </div>
</div>

<?php if ( $active_package ) : ?>
    <div class="alert alert-success" style="margin-bottom:24px">
        ✅ <strong><?php esc_html_e( 'Active Package:', 'civijobs' ); ?></strong>
        <?php echo esc_html( $active_package->package_name ); ?>
        <?php if ( $active_package->jobs_remaining !== -1 ) : ?>
            — <?php echo esc_html( $active_package->jobs_remaining ); ?> <?php esc_html_e( 'job postings remaining', 'civijobs' ); ?>
        <?php else : ?>
            — <?php esc_html_e( 'Unlimited postings', 'civijobs' ); ?>
        <?php endif; ?>
        <?php if ( $active_package->expires_at ) : ?>
            | <?php esc_html_e( 'Expires:', 'civijobs' ); ?> <?php echo esc_html( gmdate( 'M j, Y', strtotime( $active_package->expires_at ) ) ); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="packages-grid">
    <?php if ( ! empty( $packages ) ) :
        foreach ( $packages as $package ) :
            $features      = get_post_meta( $package->ID, '_features', true );
            $price         = (float) get_post_meta( $package->ID, '_price', true );
            $jobs_limit    = (int) get_post_meta( $package->ID, '_jobs_limit', true );
            $duration_days = (int) get_post_meta( $package->ID, '_duration_days', true );
            $is_featured   = (bool) get_post_meta( $package->ID, '_is_featured', true );
            $wc_product_id = (int) get_post_meta( $package->ID, '_wc_product_id', true );
            $is_active     = $active_package && (int) $active_package->package_id === $package->ID;

            $buy_url = $wc_product_id
                ? add_query_arg( 'add-to-cart', $wc_product_id, wc_get_cart_url() )
                : add_query_arg( 'civijobs_buy_package', $package->ID, get_permalink() );
        ?>
            <div class="package-card<?php echo $is_featured ? ' featured' : ''; ?><?php echo $is_active ? ' active' : ''; ?>">
                <?php if ( $is_featured ) : ?>
                    <div class="package-badge"><?php esc_html_e( 'Most Popular', 'civijobs' ); ?></div>
                <?php elseif ( $is_active ) : ?>
                    <div class="package-badge active">✅ <?php esc_html_e( 'Current Plan', 'civijobs' ); ?></div>
                <?php endif; ?>

                <div class="package-name"><?php echo esc_html( $package->post_title ); ?></div>

                <div class="package-price">
                    <span class="package-price-currency">$</span>
                    <span class="package-price-amount"><?php echo esc_html( number_format( $price, 0 ) ); ?></span>
                    <?php if ( $duration_days ) : ?>
                        <span class="package-price-period">/ <?php echo esc_html( $duration_days ); ?> <?php esc_html_e( 'days', 'civijobs' ); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $features ) ) : ?>
                    <ul class="package-features">
                        <?php foreach ( (array) $features as $feature ) : ?>
                            <li class="package-feature">
                                <span class="package-feature-icon yes">✓</span>
                                <?php echo esc_html( $feature ); ?>
                            </li>
                        <?php endforeach; ?>
                        <li class="package-feature">
                            <span class="package-feature-icon yes">✓</span>
                            <?php echo esc_html( $jobs_limit > 0 ? sprintf( __( '%d Job Postings', 'civijobs' ), $jobs_limit ) : __( 'Unlimited Job Postings', 'civijobs' ) ); ?>
                        </li>
                    </ul>
                <?php endif; ?>

                <?php if ( $is_active ) : ?>
                    <button class="btn btn-outline btn-block" disabled>✅ <?php esc_html_e( 'Current Plan', 'civijobs' ); ?></button>
                <?php else : ?>
                    <a href="<?php echo esc_url( $buy_url ); ?>" class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-outline'; ?> btn-block">
                        <?php esc_html_e( 'Get Started', 'civijobs' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach;
    else : ?>
        <div class="empty-state" style="grid-column:1/-1">
            <div class="empty-state-icon">📦</div>
            <div class="empty-state-title"><?php esc_html_e( 'No packages available yet.', 'civijobs' ); ?></div>
        </div>
    <?php endif; ?>
</div>
