<?php
/**
 * Single Company
 *
 * @package CiviJobs
 */

get_header();

global $wpdb;
$company_id = get_the_ID();
$data       = civijobs_get_company_data( $company_id );
$employer   = get_post_field( 'post_author', $company_id );
$rating     = civijobs_get_company_rating( $company_id );
$reviews    = civijobs_get_reviews( $company_id );
$open_jobs  = new WP_Query( [
    'post_type'      => 'civi_job',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_query'     => [ [ 'key' => '_company_id', 'value' => $company_id, 'compare' => '=', 'type' => 'NUMERIC' ] ],
] );

$logo       = get_the_post_thumbnail_url( $company_id, 'company-logo' );
$cover      = get_post_meta( $company_id, '_cover_image', true );
$website    = get_post_meta( $company_id, '_company_website', true );
$email      = get_post_meta( $company_id, '_company_email', true );
$location   = get_post_meta( $company_id, '_company_location', true );
$founded    = get_post_meta( $company_id, '_company_founded', true );
$linkedin   = get_post_meta( $company_id, '_company_linkedin', true );
$twitter    = get_post_meta( $company_id, '_company_twitter', true );
$lat        = get_post_meta( $company_id, '_lat', true );
$lng        = get_post_meta( $company_id, '_lng', true );

$industries = wp_get_post_terms( $company_id, 'company_industry' );
$sizes      = wp_get_post_terms( $company_id, 'company_size' );

$can_review = is_user_logged_in() && ! civijobs_has_reviewed( get_current_user_id(), $company_id );
?>

<?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

<!-- Hero -->
<div class="company-hero" style="<?php echo $cover ? 'background-image:url(' . esc_url( $cover ) . ')' : ''; ?>">
    <div class="company-hero-overlay">
        <div class="container">
            <div class="company-hero-inner">
                <div class="company-hero-logo">
                    <?php if ( $logo ) : ?>
                        <img src="<?php echo esc_url( $logo ); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php else : ?>
                        <div class="company-hero-logo-placeholder">🏢</div>
                    <?php endif; ?>
                </div>
                <div class="company-hero-info">
                    <h1 class="company-hero-name"><?php the_title(); ?></h1>
                    <div class="company-hero-meta">
                        <?php if ( ! empty( $industries ) ) : ?>
                            <span>🏭 <?php echo esc_html( $industries[0]->name ); ?></span>
                        <?php endif; ?>
                        <?php if ( $location ) : ?>
                            <span>📍 <?php echo esc_html( $location ); ?></span>
                        <?php endif; ?>
                        <?php if ( ! empty( $sizes ) ) : ?>
                            <span>👥 <?php echo esc_html( $sizes[0]->name ); ?></span>
                        <?php endif; ?>
                        <?php if ( $founded ) : ?>
                            <span>📅 <?php printf( esc_html__( 'Founded %s', 'civijobs' ), esc_html( $founded ) ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $rating['count'] > 0 ) : ?>
                        <div class="company-hero-rating">
                            <?php echo civijobs_render_stars( $rating['average'] ); // phpcs:ignore ?>
                            <span><?php echo esc_html( number_format( $rating['average'], 1 ) ); ?></span>
                            <span style="color:rgba(255,255,255,.7)">
                                (<?php printf( _n( '%d review', '%d reviews', $rating['count'], 'civijobs' ), $rating['count'] ); // phpcs:ignore ?>)
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="company-hero-actions">
                    <?php if ( $website ) : ?>
                        <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener" class="btn btn-white btn-sm">
                            🌐 <?php esc_html_e( 'Website', 'civijobs' ); ?>
                        </a>
                    <?php endif; ?>
                    <a href="#open-jobs" class="btn btn-primary btn-sm">
                        <?php printf( _n( '%d Open Job', '%d Open Jobs', $open_jobs->found_posts, 'civijobs' ), $open_jobs->found_posts ); // phpcs:ignore ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding:40px 20px">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:32px">
        <!-- Main Content -->
        <div>
            <!-- Tabs -->
            <div class="tabs" style="margin-bottom:32px">
                <button class="tab-btn active" data-tab="about"><?php esc_html_e( 'About', 'civijobs' ); ?></button>
                <button class="tab-btn" data-tab="jobs"><?php esc_html_e( 'Jobs', 'civijobs' ); ?> <span class="badge badge-blue"><?php echo esc_html( $open_jobs->found_posts ); ?></span></button>
                <button class="tab-btn" data-tab="reviews"><?php esc_html_e( 'Reviews', 'civijobs' ); ?> <span class="badge badge-gray"><?php echo esc_html( $rating['count'] ); ?></span></button>
                <?php if ( $lat && $lng ) : ?>
                    <button class="tab-btn" data-tab="location"><?php esc_html_e( 'Location', 'civijobs' ); ?></button>
                <?php endif; ?>
            </div>

            <!-- About Tab -->
            <div class="tab-pane active" id="tab-about">
                <div class="prose" style="color:var(--color-gray-700);line-height:1.8">
                    <?php the_content(); ?>
                </div>
            </div>

            <!-- Jobs Tab -->
            <div class="tab-pane" id="tab-jobs">
                <div id="open-jobs">
                    <h2 style="font-size:1.25rem;font-weight:700;margin-bottom:20px">
                        <?php printf( _n( '%d Open Position', '%d Open Positions', $open_jobs->found_posts, 'civijobs' ), $open_jobs->found_posts ); // phpcs:ignore ?>
                    </h2>
                    <?php if ( $open_jobs->have_posts() ) :
                        while ( $open_jobs->have_posts() ) : $open_jobs->the_post(); ?>
                            <?php get_template_part( 'template-parts/job/card-list' ); ?>
                        <?php endwhile; wp_reset_postdata();
                    else : ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title"><?php esc_html_e( 'No open positions right now', 'civijobs' ); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane" id="tab-reviews">
                <?php if ( $rating['count'] > 0 ) : ?>
                    <div class="reviews-summary">
                        <div class="reviews-summary-score"><?php echo esc_html( number_format( $rating['average'], 1 ) ); ?></div>
                        <div>
                            <?php echo civijobs_render_stars( $rating['average'] ); // phpcs:ignore ?>
                            <div style="font-size:0.85rem;color:var(--color-gray-500)">
                                <?php printf( _n( 'Based on %d review', 'Based on %d reviews', $rating['count'], 'civijobs' ), $rating['count'] ); // phpcs:ignore ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $can_review ) : ?>
                    <div class="write-review-section">
                        <h3><?php esc_html_e( 'Write a Review', 'civijobs' ); ?></h3>
                        <form id="review-form" data-company-id="<?php echo esc_attr( $company_id ); ?>">
                            <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                            <div class="star-picker" id="star-picker">
                                <?php for ( $i = 5; $i >= 1; $i-- ) : ?>
                                    <input type="radio" name="rating" id="star-<?php echo esc_attr( $i ); ?>" value="<?php echo esc_attr( $i ); ?>">
                                    <label for="star-<?php echo esc_attr( $i ); ?>">★</label>
                                <?php endfor; ?>
                            </div>
                            <div class="form-group" style="margin-top:12px">
                                <input type="text" name="title" class="form-control" placeholder="<?php esc_attr_e( 'Review title', 'civijobs' ); ?>" required>
                            </div>
                            <div class="form-group">
                                <textarea name="content" class="form-control" rows="4" required
                                          placeholder="<?php esc_attr_e( 'Share your experience working here or applying to this company…', 'civijobs' ); ?>"></textarea>
                            </div>
                            <div id="review-messages"></div>
                            <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Submit Review', 'civijobs' ); ?></button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="reviews-list" id="reviews-list">
                    <?php foreach ( $reviews as $review ) :
                        $reviewer = get_userdata( $review->author_id );
                    ?>
                        <div class="review-card" data-review-id="<?php echo esc_attr( $review->id ); ?>">
                            <div class="review-header">
                                <img src="<?php echo esc_url( civijobs_get_avatar_url( $review->author_id, 40 ) ); ?>" class="review-avatar" alt="">
                                <div>
                                    <div class="review-author"><?php echo esc_html( $reviewer ? $reviewer->display_name : __( 'Anonymous', 'civijobs' ) ); ?></div>
                                    <div style="display:flex;gap:8px;align-items:center">
                                        <?php echo civijobs_render_stars( $review->rating ); // phpcs:ignore ?>
                                        <span style="font-size:0.75rem;color:var(--color-gray-500)"><?php echo esc_html( civijobs_time_ago( $review->created_at ) ); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php if ( $review->title ) : ?>
                                <h4 class="review-title"><?php echo esc_html( $review->title ); ?></h4>
                            <?php endif; ?>
                            <p class="review-content"><?php echo esc_html( $review->content ); ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php if ( empty( $reviews ) && ! $can_review ) : ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">⭐</div>
                            <div class="empty-state-title"><?php esc_html_e( 'No reviews yet', 'civijobs' ); ?></div>
                            <?php if ( ! is_user_logged_in() ) : ?>
                                <a href="<?php echo esc_url( home_url( '/login' ) ); ?>" class="btn btn-primary btn-sm" style="margin-top:12px">
                                    <?php esc_html_e( 'Login to write a review', 'civijobs' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Location Tab -->
            <?php if ( $lat && $lng ) : ?>
                <div class="tab-pane" id="tab-location">
                    <div id="company-map" style="height:400px;border-radius:var(--radius-lg);overflow:hidden"></div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof L !== 'undefined') {
                            var map = L.map('company-map').setView([<?php echo esc_js( $lat ); ?>, <?php echo esc_js( $lng ); ?>], 14);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);
                            L.marker([<?php echo esc_js( $lat ); ?>, <?php echo esc_js( $lng ); ?>])
                                .bindPopup('<?php echo esc_js( get_the_title() ); ?>')
                                .addTo(map).openPopup();
                        }
                    });
                    </script>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="dash-card" style="margin-bottom:20px">
                <div class="dash-card-header"><h3 class="dash-card-title"><?php esc_html_e( 'Company Details', 'civijobs' ); ?></h3></div>
                <div class="dash-card-body">
                    <dl class="company-details-list">
                        <?php if ( ! empty( $industries ) ) : ?>
                            <dt><?php esc_html_e( 'Industry', 'civijobs' ); ?></dt>
                            <dd><?php echo esc_html( $industries[0]->name ); ?></dd>
                        <?php endif; ?>
                        <?php if ( ! empty( $sizes ) ) : ?>
                            <dt><?php esc_html_e( 'Company Size', 'civijobs' ); ?></dt>
                            <dd><?php echo esc_html( $sizes[0]->name ); ?></dd>
                        <?php endif; ?>
                        <?php if ( $location ) : ?>
                            <dt><?php esc_html_e( 'Location', 'civijobs' ); ?></dt>
                            <dd><?php echo esc_html( $location ); ?></dd>
                        <?php endif; ?>
                        <?php if ( $founded ) : ?>
                            <dt><?php esc_html_e( 'Founded', 'civijobs' ); ?></dt>
                            <dd><?php echo esc_html( $founded ); ?></dd>
                        <?php endif; ?>
                        <?php if ( $website ) : ?>
                            <dt><?php esc_html_e( 'Website', 'civijobs' ); ?></dt>
                            <dd><a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener"><?php echo esc_html( parse_url( $website, PHP_URL_HOST ) ); ?></a></dd>
                        <?php endif; ?>
                    </dl>
                    <?php if ( $linkedin || $twitter ) : ?>
                        <div style="display:flex;gap:8px;margin-top:14px">
                            <?php if ( $linkedin ) : ?>
                                <a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">LinkedIn</a>
                            <?php endif; ?>
                            <?php if ( $twitter ) : ?>
                                <a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Twitter</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ( $open_jobs->found_posts > 0 ) : ?>
                <a href="#tab-jobs" class="btn btn-primary btn-block" onclick="document.querySelector('[data-tab=jobs]').click()">
                    <?php printf( _n( 'View %d Open Job', 'View %d Open Jobs', $open_jobs->found_posts, 'civijobs' ), $open_jobs->found_posts ); // phpcs:ignore ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.company-hero { background: var(--color-gray-800); background-size: cover; background-position: center; }
.company-hero-overlay { background: linear-gradient(to bottom,rgba(0,0,0,.3),rgba(0,0,0,.7)); padding: 48px 0; }
.company-hero-inner { display: flex; align-items: flex-end; gap: 24px; flex-wrap: wrap; }
.company-hero-logo { width: 100px; height: 100px; border-radius: 12px; overflow: hidden; border: 3px solid #fff; background: #fff; flex-shrink: 0; }
.company-hero-logo img { width: 100%; height: 100%; object-fit: contain; }
.company-hero-logo-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem; background: var(--color-gray-100); }
.company-hero-info { flex: 1; }
.company-hero-name { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 8px; }
.company-hero-meta { display: flex; gap: 16px; flex-wrap: wrap; font-size: 0.875rem; color: rgba(255,255,255,.85); }
.company-hero-rating { display: flex; gap: 6px; align-items: center; margin-top: 8px; }
.company-hero-actions { display: flex; gap: 10px; flex-shrink: 0; }
.company-details-list dt { font-size: 0.75rem; text-transform: uppercase; letter-spacing: .04em; color: var(--color-gray-500); margin-bottom: 2px; }
.company-details-list dd { font-weight: 500; color: var(--color-gray-800); margin-bottom: 12px; }
.reviews-summary { display: flex; align-items: center; gap: 20px; padding: 20px; background: var(--color-gray-50); border-radius: var(--radius-lg); margin-bottom: 24px; }
.reviews-summary-score { font-size: 3rem; font-weight: 800; color: var(--color-primary); }
.write-review-section { background: #f5f3ff; border-radius: var(--radius-lg); padding: 20px; margin-bottom: 24px; }
.review-card { padding: 20px; border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); margin-bottom: 16px; }
.review-header { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 12px; }
.review-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.review-author { font-weight: 600; font-size: 0.875rem; }
.review-title { font-weight: 700; margin-bottom: 8px; }
.review-content { color: var(--color-gray-600); line-height: 1.7; font-size: 0.875rem; }
</style>

<?php get_footer(); ?>
