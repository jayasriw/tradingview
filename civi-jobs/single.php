<?php
/**
 * Default single post template
 *
 * @package CiviJobs
 */

get_header(); ?>

<main class="site-main" id="main">
    <div class="container" style="max-width:900px;padding-top:48px;padding-bottom:64px">
        <?php while ( have_posts() ) :
            the_post(); ?>
            <article <?php post_class( 'single-post' ); ?>>
                <header class="entry-header">
                    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    <div class="entry-meta">
                        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
                        <span>&bull;</span>
                        <span><?php the_author(); ?></span>
                        <?php if ( has_category() ) : ?>
                            <span>&bull;</span>
                            <?php the_category( ', ' ); ?>
                        <?php endif; ?>
                    </div>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="entry-thumbnail"><?php the_post_thumbnail( 'large' ); ?></div>
                    <?php endif; ?>
                </header>
                <div class="entry-content"><?php the_content(); ?></div>
                <footer class="entry-footer">
                    <?php the_tags( '<div class="entry-tags">', ', ', '</div>' ); ?>
                    <nav class="post-navigation">
                        <div class="nav-prev"><?php previous_post_link( '%link', '&larr; %title' ); ?></div>
                        <div class="nav-next"><?php next_post_link( '%link', '%title &rarr;' ); ?></div>
                    </nav>
                </footer>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer();
