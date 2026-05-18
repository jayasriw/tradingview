<?php
/**
 * Employer: Manage Jobs
 *
 * @package CiviJobs
 */

$user_id  = get_current_user_id();
$status   = sanitize_key( $_GET['job_status'] ?? 'all' );
$paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$per_page = 10;

$statuses = $status === 'all' ? [ 'publish', 'pending', 'draft' ] : $status;

$query = new WP_Query( [
    'post_type'      => 'civi_job',
    'author'         => $user_id,
    'post_status'    => $statuses,
    'posts_per_page' => $per_page,
    'paged'          => $paged,
] );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'My Jobs', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php printf( _n( '%d job posted', '%d jobs posted', $query->found_posts, 'civijobs' ), $query->found_posts ); // phpcs:ignore ?></p>
    </div>
    <a href="?section=post-job" class="btn btn-primary">➕ <?php esc_html_e( 'Post New Job', 'civijobs' ); ?></a>
</div>

<!-- Status filter -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach ( [ 'all' => __( 'All', 'civijobs' ), 'publish' => __( 'Published', 'civijobs' ), 'pending' => __( 'Pending', 'civijobs' ), 'draft' => __( 'Draft', 'civijobs' ) ] as $s => $label ) : ?>
        <a href="?section=manage-jobs&job_status=<?php echo esc_attr( $s ); ?>"
           class="badge <?php echo $status === $s ? 'badge-primary' : 'badge-gray'; ?>" style="padding:6px 14px;text-decoration:none;cursor:pointer">
            <?php echo esc_html( $label ); ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="dash-card">
    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Job Title', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Views', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Applications', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Deadline', 'civijobs' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'civijobs' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $query->have_posts() ) :
                    while ( $query->have_posts() ) :
                        $query->the_post();
                        $job_id   = get_the_ID();
                        $types    = wp_get_post_terms( $job_id, 'job_type', [ 'fields' => 'names' ] );
                        $deadline = get_post_meta( $job_id, '_application_deadline', true );
                        $views    = (int) get_post_meta( $job_id, '_job_views', true );
                        $featured = (bool) get_post_meta( $job_id, '_is_featured', true );
                        ?>
                        <tr data-job-id="<?php echo esc_attr( $job_id ); ?>">
                            <td>
                                <div class="job-title-cell">
                                    <div>
                                        <div style="font-weight:600;color:var(--color-gray-900)">
                                            <?php the_title(); ?>
                                            <?php if ( $featured ) : ?>
                                                <span class="badge badge-warning" style="font-size:0.68rem">⭐ <?php esc_html_e( 'Featured', 'civijobs' ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size:0.75rem;color:var(--color-gray-500)"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:0.8rem"><?php echo esc_html( implode( ', ', $types ) ); ?></td>
                            <td><?php echo civijobs_get_status_badge( get_post_status() ); // phpcs:ignore ?></td>
                            <td><?php echo esc_html( number_format( $views ) ); ?></td>
                            <td>
                                <a href="?section=applications&job_id=<?php echo esc_attr( $job_id ); ?>" style="font-weight:600;color:var(--color-primary)">
                                    <?php echo esc_html( civijobs_get_application_count( $job_id ) ); ?>
                                </a>
                            </td>
                            <td style="font-size:0.8rem">
                                <?php echo $deadline ? esc_html( gmdate( 'M j, Y', strtotime( $deadline ) ) ) : '—'; ?>
                            </td>
                            <td>
                                <div class="dash-table-actions">
                                    <a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>" target="_blank" class="dash-action-btn view"><?php esc_html_e( 'View', 'civijobs' ); ?></a>
                                    <a href="<?php echo esc_url( get_edit_post_link( $job_id ) ); ?>" class="dash-action-btn edit"><?php esc_html_e( 'Edit', 'civijobs' ); ?></a>
                                    <button class="dash-action-btn del delete-job-btn" data-job-id="<?php echo esc_attr( $job_id ); ?>"><?php esc_html_e( 'Delete', 'civijobs' ); ?></button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile;
                    wp_reset_postdata();
                else : ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">💼</div>
                                <div class="empty-state-title"><?php esc_html_e( 'No jobs found', 'civijobs' ); ?></div>
                                <a href="?section=post-job" class="btn btn-primary btn-sm" style="margin-top:12px"><?php esc_html_e( 'Post Your First Job', 'civijobs' ); ?></a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
