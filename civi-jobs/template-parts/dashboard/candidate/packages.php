<?php
/**
 * Candidate: Packages / Membership
 *
 * @package CiviJobs
 */

$user_id        = get_current_user_id();
$current_pkg    = civijobs_get_user_package( $user_id );
$packages       = get_posts( [
    'post_type'      => 'civi_package',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_key'       => '_package_for',
    'meta_value'     => 'candidate',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
] );

// Fallback: show all packages if none specifically for candidates
if ( ! $packages ) {
    $packages = get_posts( [
        'post_type'      => 'civi_package',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ] );
}
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Membership Packages', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Upgrade your account to unlock premium features.', 'civijobs' ); ?></p>
    </div>
</div>

<?php if ( $current_pkg ) : ?>
    <div class="alert alert-success" style="margin-bottom:24px">
        <strong>✅ <?php esc_html_e( 'Active Package:', 'civijobs' ); ?></strong>
        <?php echo esc_html( $current_pkg->package_name ); ?>
        <?php if ( $current_pkg->expires_at ) : ?>
            — <?php printf( esc_html__( 'Expires %s', 'civijobs' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $current_pkg->expires_at ) ) ) ); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="packages-grid">
    <?php foreach ( $packages as $pkg ) :
        $price        = get_post_meta( $pkg->ID, '_package_price', true );
        $duration     = get_post_meta( $pkg->ID, '_package_duration', true );
        $features     = get_post_meta( $pkg->ID, '_package_features', true );
        $is_featured  = get_post_meta( $pkg->ID, '_package_featured', true );
        $wc_product   = get_post_meta( $pkg->ID, '_wc_product_id', true );
        $feature_list = $features ? array_filter( array_map( 'trim', explode( "\n", $features ) ) ) : [];
        $is_active    = $current_pkg && $current_pkg->package_id == $pkg->ID;
    ?>
        <div class="package-card <?php echo $is_featured ? 'package-card--featured' : ''; ?>">
            <?php if ( $is_featured ) : ?>
                <div class="package-badge"><?php esc_html_e( 'Most Popular', 'civijobs' ); ?></div>
            <?php endif; ?>
            <div class="package-name"><?php echo esc_html( $pkg->post_title ); ?></div>
            <div class="package-price">
                <span class="package-price-amount"><?php echo $price ? '$' . esc_html( number_format( (float) $price, 2 ) ) : esc_html__( 'Free', 'civijobs' ); ?></span>
                <?php if ( $duration && $price > 0 ) : ?>
                    <span class="package-price-period">/ <?php echo esc_html( $duration ); ?> <?php esc_html_e( 'days', 'civijobs' ); ?></span>
                <?php endif; ?>
            </div>
            <?php if ( $pkg->post_content ) : ?>
                <p class="package-desc"><?php echo esc_html( $pkg->post_content ); ?></p>
            <?php endif; ?>
            <ul class="package-features">
                <?php foreach ( $feature_list as $feat ) : ?>
                    <li>✓ <?php echo esc_html( $feat ); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if ( $is_active ) : ?>
                <button class="btn btn-outline btn-block" disabled><?php esc_html_e( 'Current Plan', 'civijobs' ); ?></button>
            <?php elseif ( $wc_product && class_exists( 'WooCommerce' ) ) : ?>
                <a href="<?php echo esc_url( wc_get_checkout_url() . '?add-to-cart=' . absint( $wc_product ) ); ?>"
                   class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-outline'; ?> btn-block">
                    <?php esc_html_e( 'Get Started', 'civijobs' ); ?>
                </a>
            <?php elseif ( $price == 0 ) : ?>
                <button class="activate-free-pkg btn btn-outline btn-block" data-pkg-id="<?php echo esc_attr( $pkg->ID ); ?>">
                    <?php esc_html_e( 'Activate Free Plan', 'civijobs' ); ?>
                </button>
            <?php else : ?>
                <a href="<?php echo esc_url( get_permalink( $pkg->ID ) ); ?>"
                   class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-outline'; ?> btn-block">
                    <?php esc_html_e( 'Get Started', 'civijobs' ); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
