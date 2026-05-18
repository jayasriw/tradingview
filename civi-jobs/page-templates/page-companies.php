<?php
/**
 * Template Name: Companies Listing
 *
 * @package CiviJobs
 */

get_header();

$paged      = max( 1, get_query_var( 'paged' ) );
$per_page   = 12;
$search     = sanitize_text_field( $_GET['search'] ?? '' );
$industry   = sanitize_text_field( $_GET['industry'] ?? '' );
$size       = sanitize_text_field( $_GET['size'] ?? '' );

$tax_query = [];
if ( $industry ) {
    $tax_query[] = [ 'taxonomy' => 'company_industry', 'field' => 'slug', 'terms' => $industry ];
}
if ( $size ) {
    $tax_query[] = [ 'taxonomy' => 'company_size', 'field' => 'slug', 'terms' => $size ];
}

$args = [
    'post_type'      => 'civi_company',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'tax_query'      => $tax_query,
];

if ( $search ) {
    $args['s'] = $search;
}

$query      = new WP_Query( $args );
$industries = get_terms( [ 'taxonomy' => 'company_industry', 'hide_empty' => true ] );
$sizes      = get_terms( [ 'taxonomy' => 'company_size', 'hide_empty' => true ] );
?>

<div class="page-hero page-hero--compact" style="background:linear-gradient(135deg,var(--color-primary) 0%,#7c3aed 100%)">
    <div class="container">
        <h1 class="page-hero-title" style="color:#fff"><?php esc_html_e( 'Browse Companies', 'civijobs' ); ?></h1>
        <p class="page-hero-subtitle" style="color:rgba(255,255,255,.85)">
            <?php printf( esc_html__( '%d companies are hiring', 'civijobs' ), (int) wp_count_posts( 'civi_company' )->publish ); ?>
        </p>
        <form method="GET" action="" class="companies-search-form">
            <div class="search-bar search-bar--compact">
                <div class="search-bar-field">
                    <span class="search-bar-icon">🔍</span>
                    <input type="text" name="search" value="<?php echo esc_attr( $search ); ?>"
                           placeholder="<?php esc_attr_e( 'Search company name…', 'civijobs' ); ?>" class="search-bar-input">
                </div>
                <button type="submit" class="btn btn-white"><?php esc_html_e( 'Search', 'civijobs' ); ?></button>
            </div>
        </form>
    </div>
</div>

<div class="container" style="padding:40px 20px">
    <!-- Filter chips -->
    <div class="filter-chips">
        <?php foreach ( $industries as $ind ) : ?>
            <a href="<?php echo esc_url( add_query_arg( [ 'industry' => $ind->slug, 'size' => $size, 'search' => $search ] ) ); ?>"
               class="filter-chip <?php echo $industry === $ind->slug ? 'active' : ''; ?>">
                <?php echo esc_html( $ind->name ); ?>
                <span class="filter-chip-count"><?php echo esc_html( $ind->count ); ?></span>
            </a>
        <?php endforeach; ?>
        <?php if ( $industry ) : ?>
            <a href="<?php echo esc_url( add_query_arg( [ 'industry' => '', 'search' => $search ] ) ); ?>" class="filter-chip filter-chip--clear">
                ✕ <?php esc_html_e( 'Clear', 'civijobs' ); ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Results header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <p style="color:var(--color-gray-600)">
            <?php printf( _n( '%d company found', '%d companies found', $query->found_posts, 'civijobs' ), $query->found_posts ); // phpcs:ignore ?>
        </p>
        <?php if ( $size || $industry ) : ?>
            <a href="<?php echo esc_url( get_permalink() ); ?>" style="font-size:0.85rem;color:var(--color-primary)">
                <?php esc_html_e( 'Clear all filters', 'civijobs' ); ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if ( $query->have_posts() ) : ?>
        <div class="companies-grid">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <?php get_template_part( 'template-parts/company/card-grid' ); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <?php
        set_query_var( 'max_num_pages', $query->max_num_pages );
        get_template_part( 'template-parts/global/pagination' );
        ?>
    <?php else : ?>
        <div class="empty-state">
            <div class="empty-state-icon">🏢</div>
            <div class="empty-state-title"><?php esc_html_e( 'No companies found', 'civijobs' ); ?></div>
            <div class="empty-state-text"><?php esc_html_e( 'Try different search terms or filters.', 'civijobs' ); ?></div>
            <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-primary" style="margin-top:16px">
                <?php esc_html_e( 'View All Companies', 'civijobs' ); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
