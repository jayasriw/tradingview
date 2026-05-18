<?php
/**
 * Main index template — falls back to standard blog loop
 *
 * @package CiviJobs
 */

get_header(); ?>

<main class="site-main" id="main">
    <div class="container" style="padding-top:48px;padding-bottom:48px">

        <?php if ( have_posts() ) : ?>
            <h1 class="page-title"><?php
                if ( is_home() && ! is_front_page() ) {
                    single_post_title();
                } elseif ( is_search() ) {
                    printf( esc_html__( 'Search Results for: %s', 'civijobs' ), '<span>' . get_search_query() . '</span>' );
                } else {
                    esc_html_e( 'Latest Posts', 'civijobs' );
                }
            ?></h1>

            <div class="posts-grid">
                <?php while ( have_posts() ) :
                    the_post(); ?>
                    <article <?php post_class( 'post-card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="post-card-body">
                            <div class="post-meta">
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                                <span>&bull;</span>
                                <?php the_author(); ?>
                            </div>
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <p class="post-excerpt"><?php the_excerpt(); ?></p>
                            <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm">
                                <?php esc_html_e( 'Read More', 'civijobs' ); ?>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php get_template_part( 'template-parts/global/pagination' ); ?>

        <?php else : ?>
            <div class="not-found" style="text-align:center;padding:80px 0">
                <h2><?php esc_html_e( 'Nothing found.', 'civijobs' ); ?></h2>
                <p><?php esc_html_e( 'Try a different search or check back later.', 'civijobs' ); ?></p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
                    <?php esc_html_e( 'Go Home', 'civijobs' ); ?>
                </a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer();
