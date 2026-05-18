<?php
/**
 * Main index template
 *
 * @package JobPortal
 */

get_header();
?>
<div class="container" style="padding-top:40px;padding-bottom:60px">
    <?php if ( have_posts() ) : ?>
        <h1 class="page-title"><?php esc_html_e( 'Latest Posts', 'jobportal' ); ?></h1>
        <div class="posts-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                            <?php the_post_thumbnail( 'medium_large' ); ?>
                        </a>
                    <?php endif; ?>
                    <div class="post-card-body">
                        <h2 class="post-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="post-meta">
                            <span><?php echo esc_html( get_the_date() ); ?></span>
                        </div>
                        <div class="post-excerpt"><?php the_excerpt(); ?></div>
                        <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm"><?php esc_html_e( 'Read More', 'jobportal' ); ?></a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php get_template_part( 'template-parts/global/pagination' ); ?>
    <?php else : ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128221;</div>
            <div class="empty-state-title"><?php esc_html_e( 'No posts found', 'jobportal' ); ?></div>
        </div>
    <?php endif; ?>
</div>
<?php
get_footer();
