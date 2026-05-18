<?php
/**
 * Template Name: Candidates Listing
 *
 * @package CiviJobs
 */

get_header();

$paged        = max( 1, get_query_var( 'paged' ) );
$per_page     = 12;
$search       = sanitize_text_field( $_GET['search'] ?? '' );
$skill_filter = sanitize_text_field( $_GET['skill'] ?? '' );
$available    = isset( $_GET['available'] ) ? (bool) $_GET['available'] : false;

$meta_query = [
    [ 'key' => 'civi_visibility', 'compare' => 'NOT EXISTS' ],
    'relation' => 'OR',
    [ 'key' => 'civi_visibility', 'value' => 'public', 'compare' => '=' ],
];

if ( $skill_filter ) {
    $meta_query[] = [ 'key' => 'civi_skills', 'value' => $skill_filter, 'compare' => 'LIKE' ];
}
if ( $available ) {
    $meta_query[] = [ 'key' => 'civi_available_for_hire', 'value' => '1', 'compare' => '=' ];
}

$candidates_query = new WP_User_Query( [
    'role'           => 'civi_candidate',
    'number'         => $per_page,
    'paged'          => $paged,
    'search'         => $search ? '*' . $search . '*' : '',
    'search_columns' => [ 'display_name' ],
    'meta_query'     => $meta_query,
] );

$candidates  = $candidates_query->get_results();
$total       = $candidates_query->get_total();
$total_pages = ceil( $total / $per_page );
?>

<div class="page-hero page-hero--compact" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%)">
    <div class="container">
        <h1 class="page-hero-title" style="color:#fff"><?php esc_html_e( 'Find Talent', 'civijobs' ); ?></h1>
        <p class="page-hero-subtitle" style="color:rgba(255,255,255,.75)">
            <?php printf( esc_html__( '%d candidates available for hire', 'civijobs' ), $total ); ?>
        </p>
        <form method="GET" action="" class="search-bar search-bar--compact">
            <div class="search-bar-field">
                <span class="search-bar-icon">👤</span>
                <input type="text" name="search" value="<?php echo esc_attr( $search ); ?>"
                       placeholder="<?php esc_attr_e( 'Search by name…', 'civijobs' ); ?>" class="search-bar-input">
            </div>
            <div class="search-bar-field">
                <span class="search-bar-icon">🔧</span>
                <input type="text" name="skill" value="<?php echo esc_attr( $skill_filter ); ?>"
                       placeholder="<?php esc_attr_e( 'Skill, e.g. React, PHP…', 'civijobs' ); ?>" class="search-bar-input">
            </div>
            <button type="submit" class="btn btn-white"><?php esc_html_e( 'Search', 'civijobs' ); ?></button>
        </form>
    </div>
</div>

<div class="container" style="padding:40px 20px">
    <!-- Filters bar -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px">
        <p style="color:var(--color-gray-600)">
            <?php printf( _n( '%d candidate found', '%d candidates found', $total, 'civijobs' ), $total ); // phpcs:ignore ?>
        </p>
        <div style="display:flex;gap:8px;align-items:center">
            <label style="display:flex;align-items:center;gap:6px;font-size:0.875rem;cursor:pointer">
                <input type="checkbox" id="available-filter" <?php checked( $available ); ?> onchange="window.location=this.checked?'<?php echo esc_js( add_query_arg( [ 'available' => '1', 'search' => $search, 'skill' => $skill_filter ] ) ); ?>':'<?php echo esc_js( add_query_arg( [ 'available' => '', 'search' => $search, 'skill' => $skill_filter ] ) ); ?>'">
                <?php esc_html_e( 'Available for hire only', 'civijobs' ); ?>
            </label>
        </div>
    </div>

    <?php if ( $candidates ) : ?>
        <div class="candidates-grid">
            <?php foreach ( $candidates as $candidate ) : ?>
                <?php
                set_query_var( 'candidate_user', $candidate );
                get_template_part( 'template-parts/candidate/card-grid' );
                ?>
            <?php endforeach; ?>
        </div>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="pagination" style="margin-top:40px">
                <?php
                echo paginate_links( [ // phpcs:ignore
                    'total'   => $total_pages,
                    'current' => $paged,
                    'format'  => '?paged=%#%',
                    'add_args' => [ 'search' => $search, 'skill' => $skill_filter, 'available' => $available ? '1' : '' ],
                ] );
                ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <div class="empty-state-title"><?php esc_html_e( 'No candidates found', 'civijobs' ); ?></div>
            <div class="empty-state-text"><?php esc_html_e( 'Try different search terms.', 'civijobs' ); ?></div>
            <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-primary" style="margin-top:16px">
                <?php esc_html_e( 'View All Candidates', 'civijobs' ); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
