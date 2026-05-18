<?php
/**
 * Default page template
 *
 * @package JobPortal
 */

get_header();
?>
<div class="container" style="padding-top:40px;padding-bottom:60px">
    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="page-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>
            <h1 class="page-title"><?php the_title(); ?></h1>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>
<?php
get_footer();
