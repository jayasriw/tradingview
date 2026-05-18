<?php
/**
 * Pagination component
 *
 * @package CiviJobs
 */

$pagination = paginate_links( [
    'prev_text' => '&larr; ' . __( 'Prev', 'civijobs' ),
    'next_text' => __( 'Next', 'civijobs' ) . ' &rarr;',
    'type'      => 'array',
] );

if ( $pagination ) : ?>
    <nav class="pagination" aria-label="<?php esc_attr_e( 'Posts pagination', 'civijobs' ); ?>">
        <?php foreach ( $pagination as $page ) :
            echo $page; // phpcs:ignore WordPress.Security.EscapeOutput
        endforeach; ?>
    </nav>
<?php endif;
