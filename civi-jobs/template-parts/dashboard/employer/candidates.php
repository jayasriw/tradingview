<?php
/**
 * Employer: Browse Candidates
 *
 * @package CiviJobs
 */

$paged = max( 1, absint( $_GET['pg'] ?? 1 ) );
$search = sanitize_text_field( $_GET['search'] ?? '' );
$skill_filter = sanitize_text_field( $_GET['skill'] ?? '' );

$meta_query = [ [ 'key' => 'civi_visibility', 'compare' => 'NOT EXISTS' ] ];
if ( $skill_filter ) {
    $meta_query[] = [ 'key' => 'civi_skills', 'value' => $skill_filter, 'compare' => 'LIKE' ];
}

$candidates_query = new WP_User_Query( [
    'role'           => 'civi_candidate',
    'number'         => 12,
    'paged'          => $paged,
    'search'         => $search ? '*' . $search . '*' : '',
    'search_columns' => [ 'display_name', 'user_email' ],
    'meta_query'     => $meta_query,
] );

$candidates  = $candidates_query->get_results();
$total       = $candidates_query->get_total();
$total_pages = ceil( $total / 12 );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Browse Candidates', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php printf( _n( '%d candidate', '%d candidates', $total, 'civijobs' ), $total ); // phpcs:ignore ?></p>
    </div>
</div>

<!-- Search/Filter Bar -->
<form method="GET" action="" style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
    <input type="hidden" name="section" value="candidates">
    <input type="text" name="search" class="form-control" value="<?php echo esc_attr( $search ); ?>"
           placeholder="<?php esc_attr_e( 'Search by name or email…', 'civijobs' ); ?>" style="flex:1;min-width:200px">
    <input type="text" name="skill" class="form-control" value="<?php echo esc_attr( $skill_filter ); ?>"
           placeholder="<?php esc_attr_e( 'Filter by skill…', 'civijobs' ); ?>" style="width:180px">
    <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Search', 'civijobs' ); ?></button>
    <?php if ( $search || $skill_filter ) : ?>
        <a href="?section=candidates" class="btn btn-outline"><?php esc_html_e( 'Clear', 'civijobs' ); ?></a>
    <?php endif; ?>
</form>

<?php if ( $candidates ) : ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;margin-bottom:28px">
        <?php foreach ( $candidates as $candidate ) :
            $profile  = civijobs_get_candidate_data( $candidate->ID );
            $skills   = (array) $profile['skills'];
            $headline = $profile['headline'];
            $location = $profile['location'];
            $rate     = $profile['hourly_rate'];
            $avail    = $profile['available'];
        ?>
            <div class="candidate-card-employer">
                <div class="candidate-card-top">
                    <img src="<?php echo esc_url( civijobs_get_avatar_url( $candidate->ID, 64 ) ); ?>"
                         class="candidate-card-avatar" alt="">
                    <div>
                        <div class="candidate-card-name"><?php echo esc_html( $candidate->display_name ); ?></div>
                        <?php if ( $headline ) : ?>
                            <div class="candidate-card-headline"><?php echo esc_html( $headline ); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ( $avail ) : ?>
                        <span class="badge badge-green" style="margin-left:auto;flex-shrink:0"><?php esc_html_e( 'Available', 'civijobs' ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="candidate-card-meta">
                    <?php if ( $location ) : ?>
                        <span>📍 <?php echo esc_html( $location ); ?></span>
                    <?php endif; ?>
                    <?php if ( $rate ) : ?>
                        <span>💰 $<?php echo esc_html( $rate ); ?>/hr</span>
                    <?php endif; ?>
                </div>
                <?php if ( $skills ) : ?>
                    <div class="candidate-card-skills">
                        <?php foreach ( array_slice( $skills, 0, 4 ) as $skill ) : ?>
                            <span class="skill-tag"><?php echo esc_html( $skill ); ?></span>
                        <?php endforeach; ?>
                        <?php if ( count( $skills ) > 4 ) : ?>
                            <span class="skill-tag skill-tag--more">+<?php echo count( $skills ) - 4; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="candidate-card-actions">
                    <a href="<?php echo esc_url( home_url( '/candidate/' . $candidate->user_login ) ); ?>"
                       class="btn btn-outline btn-sm"><?php esc_html_e( 'View Profile', 'civijobs' ); ?></a>
                    <button class="btn btn-primary btn-sm compose-message-btn"
                            data-receiver-id="<?php echo esc_attr( $candidate->ID ); ?>"
                            data-receiver-name="<?php echo esc_attr( $candidate->display_name ); ?>">
                        ✉️ <?php esc_html_e( 'Message', 'civijobs' ); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ( $total_pages > 1 ) :
        $base_url = add_query_arg( [ 'section' => 'candidates', 'search' => $search, 'skill' => $skill_filter ], '' );
    ?>
        <div style="display:flex;justify-content:center;gap:6px">
            <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'pg', $i, $base_url ) ); ?>"
                   class="btn btn-sm <?php echo $i === $paged ? 'btn-primary' : 'btn-outline'; ?>">
                    <?php echo esc_html( $i ); ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php else : ?>
    <div class="empty-state">
        <div class="empty-state-icon">👥</div>
        <div class="empty-state-title"><?php esc_html_e( 'No candidates found', 'civijobs' ); ?></div>
        <div class="empty-state-text"><?php esc_html_e( 'Try adjusting your search criteria.', 'civijobs' ); ?></div>
    </div>
<?php endif; ?>

<style>
.candidate-card-employer {
    background: #fff;
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.candidate-card-top { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.candidate-card-avatar { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.candidate-card-name { font-weight: 700; color: var(--color-gray-900); }
.candidate-card-headline { font-size: 0.8rem; color: var(--color-gray-500); }
.candidate-card-meta { display: flex; gap: 12px; flex-wrap: wrap; font-size: 0.8rem; color: var(--color-gray-500); }
.candidate-card-skills { display: flex; gap: 6px; flex-wrap: wrap; }
.skill-tag { background: var(--color-gray-100); color: var(--color-gray-700); font-size: 0.72rem; padding: 3px 8px; border-radius: var(--radius-full); }
.skill-tag--more { background: var(--color-primary-light); color: var(--color-primary); }
.candidate-card-actions { display: flex; gap: 8px; margin-top: auto; }
</style>
