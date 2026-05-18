<?php
/**
 * Single company page
 *
 * @package JobPortal
 */

get_header();

if ( ! have_posts() ) {
    get_footer();
    return;
}

the_post();
$company_id  = get_the_ID();
$data        = jobportal_get_company_data( $company_id );
$rating      = jobportal_get_company_rating( $company_id );
$logo        = get_the_post_thumbnail_url( $company_id, 'company-logo' );
$cover       = get_post_meta( $company_id, '_cover_image', true );
$is_verified = (bool) get_post_meta( $company_id, '_is_verified', true );
$open_jobs   = (int) get_post_meta( $company_id, '_open_jobs_count', true );
$industries  = wp_get_post_terms( $company_id, 'company_industry', [ 'fields' => 'names' ] );
$sizes       = wp_get_post_terms( $company_id, 'company_size', [ 'fields' => 'names' ] );
?>
<div class="single-company">
    <!-- Cover -->
    <div class="company-cover" style="<?php echo $cover ? 'background-image:url(' . esc_url( $cover ) . ')' : ''; ?>">
        <div class="container company-cover-inner">
            <?php if ( $logo ) : ?>
                <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="company-cover-logo">
            <?php else : ?>
                <div class="company-logo-placeholder large"><?php echo esc_html( mb_strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?></div>
            <?php endif; ?>
            <div class="company-cover-info">
                <h1 class="company-cover-title">
                    <?php the_title(); ?>
                    <?php if ( $is_verified ) : ?>
                        <span class="company-verified" title="Verified">&#10003;</span>
                    <?php endif; ?>
                </h1>
                <?php if ( ! empty( $industries ) ) : ?>
                    <span class="badge badge-primary"><?php echo esc_html( $industries[0] ); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container" style="padding-top:32px;padding-bottom:60px">
        <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

        <div class="single-company-layout">
            <div class="single-company-main">
                <div class="dash-card">
                    <div class="dash-card-body">
                        <h2><?php esc_html_e( 'About', 'jobportal' ); ?></h2>
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <aside class="single-company-sidebar">
                <div class="dash-card">
                    <div class="dash-card-body">
                        <?php if ( $data['location'] ) : ?>
                            <div class="company-detail-row">&#128205; <?php echo esc_html( $data['location'] ); ?></div>
                        <?php endif; ?>
                        <?php if ( $data['website'] ) : ?>
                            <div class="company-detail-row">
                                <a href="<?php echo esc_url( $data['website'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $data['website'] ); ?></a>
                            </div>
                        <?php endif; ?>
                        <?php if ( $open_jobs ) : ?>
                            <div class="company-detail-row"><?php echo esc_html( $open_jobs ); ?> <?php esc_html_e( 'Open Jobs', 'jobportal' ); ?></div>
                        <?php endif; ?>
                        <?php if ( $rating['count'] > 0 ) : ?>
                            <div class="company-detail-row">&#11088; <?php echo esc_html( $rating['average'] ); ?> (<?php echo esc_html( $rating['count'] ); ?> <?php esc_html_e( 'reviews', 'jobportal' ); ?>)</div>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
<?php
get_footer();
