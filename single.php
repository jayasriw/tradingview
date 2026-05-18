<?php
/**
 * Default single post template
 *
 * @package JobPortal
 */

get_header();
?>
<div class="container" style="padding-top:40px;padding-bottom:60px;max-width:800px">
    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="entry-title" style="font-size:2rem;font-weight:800;margin-bottom:16px"><?php the_title(); ?></h1>
            <div class="post-meta" style="margin-bottom:24px;color:var(--color-gray-500);font-size:0.875rem">
                <span><?php echo esc_html( get_the_date() ); ?></span>
                <span style="margin:0 8px">&middot;</span>
                <span><?php the_author(); ?></span>
            </div>
            <?php if ( has_post_thumbnail() ) : ?>
                <div style="margin-bottom:32px;border-radius:var(--radius-xl);overflow:hidden">
                    <?php the_post_thumbnail( 'large', [ 'style' => 'width:100%;height:auto' ] ); ?>
                </div>
            <?php endif; ?>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>
<?php
get_footer();
