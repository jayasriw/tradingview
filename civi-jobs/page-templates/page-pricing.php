<?php
/**
 * Template Name: Pricing
 *
 * @package CiviJobs
 */

get_header();

$employer_pkgs = get_posts( [
    'post_type'      => 'civi_package',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_key'       => '_package_for',
    'meta_value'     => 'employer',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
] );

$candidate_pkgs = get_posts( [
    'post_type'      => 'civi_package',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_key'       => '_package_for',
    'meta_value'     => 'candidate',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
] );

// If no targeted packages, show all
if ( ! $employer_pkgs && ! $candidate_pkgs ) {
    $all = get_posts( [
        'post_type'      => 'civi_package',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ] );
    $employer_pkgs = $all;
}
?>

<div class="page-hero" style="background:linear-gradient(135deg,var(--color-primary) 0%,#7c3aed 100%);padding:72px 20px;text-align:center">
    <div class="container">
        <h1 style="color:#fff;font-size:2.8rem;font-weight:800;margin-bottom:12px"><?php esc_html_e( 'Simple, Transparent Pricing', 'civijobs' ); ?></h1>
        <p style="color:rgba(255,255,255,.8);font-size:1.1rem;max-width:520px;margin:0 auto"><?php esc_html_e( 'Choose the plan that fits your needs. No hidden fees.', 'civijobs' ); ?></p>
    </div>
</div>

<div class="container" style="padding:60px 20px">

    <?php if ( $employer_pkgs ) : ?>
        <!-- Employer Packages -->
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.8rem;font-weight:700;margin-bottom:8px"><?php esc_html_e( 'For Employers', 'civijobs' ); ?></h2>
            <p style="color:var(--color-gray-600)"><?php esc_html_e( 'Post jobs and find the best candidates.', 'civijobs' ); ?></p>
        </div>
        <div class="packages-grid" style="margin-bottom:60px">
            <?php foreach ( $employer_pkgs as $pkg ) :
                $price        = get_post_meta( $pkg->ID, '_package_price', true );
                $duration     = get_post_meta( $pkg->ID, '_package_duration', true );
                $features     = get_post_meta( $pkg->ID, '_package_features', true );
                $is_featured  = get_post_meta( $pkg->ID, '_package_featured', true );
                $wc_product   = get_post_meta( $pkg->ID, '_wc_product_id', true );
                $job_limit    = get_post_meta( $pkg->ID, '_package_job_limit', true );
                $feature_list = $features ? array_filter( array_map( 'trim', explode( "\n", $features ) ) ) : [];
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
                    <?php
                    $dashboard = civijobs_get_dashboard_url( 'employer' );
                    if ( $wc_product && class_exists( 'WooCommerce' ) ) : ?>
                        <a href="<?php echo esc_url( wc_get_checkout_url() . '?add-to-cart=' . absint( $wc_product ) ); ?>"
                           class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-outline'; ?> btn-block">
                            <?php esc_html_e( 'Get Started', 'civijobs' ); ?>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $dashboard ); ?>"
                           class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-outline'; ?> btn-block">
                            <?php esc_html_e( 'Get Started', 'civijobs' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ( $candidate_pkgs ) : ?>
        <!-- Candidate Packages -->
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.8rem;font-weight:700;margin-bottom:8px"><?php esc_html_e( 'For Job Seekers', 'civijobs' ); ?></h2>
            <p style="color:var(--color-gray-600)"><?php esc_html_e( 'Boost your profile and get noticed by top employers.', 'civijobs' ); ?></p>
        </div>
        <div class="packages-grid" style="margin-bottom:60px">
            <?php foreach ( $candidate_pkgs as $pkg ) :
                $price        = get_post_meta( $pkg->ID, '_package_price', true );
                $duration     = get_post_meta( $pkg->ID, '_package_duration', true );
                $features     = get_post_meta( $pkg->ID, '_package_features', true );
                $is_featured  = get_post_meta( $pkg->ID, '_package_featured', true );
                $wc_product   = get_post_meta( $pkg->ID, '_wc_product_id', true );
                $feature_list = $features ? array_filter( array_map( 'trim', explode( "\n", $features ) ) ) : [];
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
                    <?php if ( $wc_product && class_exists( 'WooCommerce' ) ) : ?>
                        <a href="<?php echo esc_url( wc_get_checkout_url() . '?add-to-cart=' . absint( $wc_product ) ); ?>"
                           class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-outline'; ?> btn-block">
                            <?php esc_html_e( 'Get Started', 'civijobs' ); ?>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( civijobs_get_dashboard_url( 'candidate' ) ); ?>"
                           class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-outline'; ?> btn-block">
                            <?php esc_html_e( 'Get Started', 'civijobs' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- FAQ Section -->
    <div style="max-width:680px;margin:0 auto">
        <h2 style="text-align:center;font-size:1.8rem;font-weight:700;margin-bottom:32px"><?php esc_html_e( 'Frequently Asked Questions', 'civijobs' ); ?></h2>
        <?php
        $faqs = [
            [ __( 'Can I cancel anytime?', 'civijobs' ), __( 'Yes, you can cancel your subscription at any time. Your access remains active until the end of the billing period.', 'civijobs' ) ],
            [ __( 'Do you offer refunds?', 'civijobs' ), __( 'We offer a 7-day money-back guarantee on all paid plans. Contact support if you are not satisfied.', 'civijobs' ) ],
            [ __( 'Is there a free plan?', 'civijobs' ), __( 'Yes! Our free plan lets you post a limited number of jobs and apply to positions. Upgrade to unlock premium features.', 'civijobs' ) ],
            [ __( 'What payment methods do you accept?', 'civijobs' ), __( 'We accept all major credit and debit cards via Stripe, as well as PayPal.', 'civijobs' ) ],
        ];
        foreach ( $faqs as [ $q, $a ] ) :
        ?>
            <details class="faq-item">
                <summary class="faq-question"><?php echo esc_html( $q ); ?></summary>
                <div class="faq-answer"><?php echo esc_html( $a ); ?></div>
            </details>
        <?php endforeach; ?>
    </div>
</div>

<style>
.faq-item { border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); margin-bottom: 12px; overflow: hidden; }
.faq-question { padding: 18px 20px; font-weight: 600; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; }
.faq-question::-webkit-details-marker { display: none; }
.faq-question::after { content: '+'; font-size: 1.25rem; color: var(--color-primary); transition: transform .2s; }
details[open] .faq-question::after { transform: rotate(45deg); }
.faq-answer { padding: 0 20px 18px; color: var(--color-gray-600); line-height: 1.7; }
</style>

<?php get_footer(); ?>
