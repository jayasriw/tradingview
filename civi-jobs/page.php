<?php
/**
 * Default page template
 *
 * @package CiviJobs
 */

get_header(); ?>

<main class="site-main" id="main">
    <div class="container" style="max-width:900px;padding-top:48px;padding-bottom:64px">
        <?php while ( have_posts() ) :
            the_post(); ?>
            <article <?php post_class( 'page-content' ); ?>>
                <?php if ( ! is_front_page() ) : ?>
                    <header class="page-header-simple">
                        <h1 class="page-title"><?php the_title(); ?></h1>
                        <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>
                    </header>
                <?php endif; ?>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer();
