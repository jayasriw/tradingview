<?php
/**
 * Breadcrumb navigation
 *
 * @package CiviJobs
 */

$crumbs = [];
$crumbs[] = [ 'label' => __( 'Home', 'civijobs' ), 'url' => home_url( '/' ) ];

if ( is_singular( 'civi_job' ) ) {
    $crumbs[] = [ 'label' => __( 'Jobs', 'civijobs' ), 'url' => home_url( '/jobs' ) ];
    $crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
} elseif ( is_singular( 'civi_company' ) ) {
    $crumbs[] = [ 'label' => __( 'Companies', 'civijobs' ), 'url' => home_url( '/companies' ) ];
    $crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
} elseif ( is_singular( 'civi_resume' ) ) {
    $crumbs[] = [ 'label' => __( 'Candidates', 'civijobs' ), 'url' => home_url( '/candidates' ) ];
    $crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
} elseif ( is_page() ) {
    $ancestors = get_post_ancestors( get_the_ID() );
    foreach ( array_reverse( $ancestors ) as $ancestor ) {
        $crumbs[] = [ 'label' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) ];
    }
    $crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
} elseif ( is_tax() || is_category() || is_tag() ) {
    $crumbs[] = [ 'label' => single_cat_title( '', false ), 'url' => '' ];
} elseif ( is_search() ) {
    $crumbs[] = [ 'label' => sprintf( __( 'Search: %s', 'civijobs' ), get_search_query() ), 'url' => '' ];
} elseif ( is_404() ) {
    $crumbs[] = [ 'label' => __( '404 Not Found', 'civijobs' ), 'url' => '' ];
}

if ( count( $crumbs ) < 2 ) return;
?>
<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'civijobs' ); ?>">
    <ol class="breadcrumb-list">
        <?php foreach ( $crumbs as $i => $crumb ) :
            $is_last = ( $i === count( $crumbs ) - 1 ); ?>
            <li class="breadcrumb-item<?php echo $is_last ? ' active' : ''; ?>">
                <?php if ( ! $is_last && $crumb['url'] ) : ?>
                    <a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
                    <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <?php else : ?>
                    <span aria-current="page"><?php echo esc_html( $crumb['label'] ); ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
