<?php
/**
 * Company card — grid view
 *
 * @package CiviJobs
 */

$company_post = $args['company'] ?? null;
if ( ! $company_post ) return;

$company_id  = $company_post->ID;
$logo        = get_the_post_thumbnail_url( $company_id, 'company-logo' );
$name        = get_the_title( $company_id );
$location    = get_post_meta( $company_id, '_company_location', true );
$open_jobs   = (int) get_post_meta( $company_id, '_open_jobs_count', true );
$is_verified = (bool) get_post_meta( $company_id, '_is_verified', true );
$rating      = civijobs_get_company_rating( $company_id );
$industries  = wp_get_post_terms( $company_id, 'company_industry', [ 'fields' => 'names' ] );
$sizes       = wp_get_post_terms( $company_id, 'company_size', [ 'fields' => 'names' ] );
?>
<a href="<?php echo esc_url( get_permalink( $company_id ) ); ?>" class="company-card" data-company-id="<?php echo esc_attr( $company_id ); ?>">
    <?php if ( $is_verified ) : ?>
        <div class="company-verified" title="<?php esc_attr_e( 'Verified Company', 'civijobs' ); ?>">✓</div>
    <?php endif; ?>

    <div class="company-card-header">
        <?php if ( $logo ) : ?>
            <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="company-logo" loading="lazy">
        <?php else : ?>
            <div class="company-logo-placeholder"><?php echo esc_html( mb_strtoupper( mb_substr( $name, 0, 1 ) ) ); ?></div>
        <?php endif; ?>

        <div class="company-card-title">
            <div class="company-name"><?php echo esc_html( $name ); ?></div>
            <?php if ( ! empty( $industries ) ) : ?>
                <span class="company-industry-tag"><?php echo esc_html( $industries[0] ); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="company-card-meta">
        <?php if ( $location ) : ?>
            <div class="company-meta-row">
                <span class="company-meta-icon">📍</span>
                <span><?php echo esc_html( $location ); ?></span>
            </div>
        <?php endif; ?>
        <?php if ( ! empty( $sizes ) ) : ?>
            <div class="company-meta-row">
                <span class="company-meta-icon">👥</span>
                <span><?php echo esc_html( $sizes[0] ); ?> <?php esc_html_e( 'employees', 'civijobs' ); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="company-card-footer">
        <span class="company-open-jobs">
            <?php echo esc_html( sprintf( _n( '%d Open Job', '%d Open Jobs', $open_jobs, 'civijobs' ), $open_jobs ) ); ?>
        </span>
        <?php if ( $rating['count'] > 0 ) : ?>
            <span class="company-rating-badge">
                ⭐ <?php echo esc_html( $rating['average'] ); ?>
                <span style="color:var(--color-gray-400);font-weight:400">(<?php echo esc_html( $rating['count'] ); ?>)</span>
            </span>
        <?php endif; ?>
    </div>
</a>
