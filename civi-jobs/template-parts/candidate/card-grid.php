<?php
/**
 * Candidate card — grid view
 *
 * @package CiviJobs
 */

$candidate_post = $args['candidate'] ?? null;
if ( ! $candidate_post ) return;

$post_id    = $candidate_post->ID;
$author_id  = (int) get_post_field( 'post_author', $post_id );
$name       = get_the_title( $post_id );
$headline   = get_post_meta( $post_id, '_headline', true );
$location   = get_post_meta( $post_id, '_location', true );
$avatar     = civijobs_get_avatar_url( $author_id, 120 );
$available  = (bool) get_post_meta( $post_id, '_available', true );
$rate       = (float) get_post_meta( $post_id, '_hourly_rate', true );
$skills     = wp_get_post_terms( $post_id, 'resume_skills', [ 'fields' => 'names' ] );
$skill_max  = 4;
?>
<div class="candidate-card" data-candidate-id="<?php echo esc_attr( $post_id ); ?>">
    <?php if ( $available ) : ?>
        <span class="candidate-available-badge"><?php esc_html_e( 'Available for hire', 'civijobs' ); ?></span>
    <?php endif; ?>

    <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="candidate-avatar" loading="lazy">

    <div class="candidate-name"><?php echo esc_html( $name ); ?></div>

    <?php if ( $headline ) : ?>
        <div class="candidate-headline"><?php echo esc_html( $headline ); ?></div>
    <?php endif; ?>

    <?php if ( $location ) : ?>
        <div class="candidate-location">📍 <?php echo esc_html( $location ); ?></div>
    <?php endif; ?>

    <?php if ( ! empty( $skills ) ) : ?>
        <div class="candidate-skills">
            <?php foreach ( array_slice( $skills, 0, $skill_max ) as $skill ) : ?>
                <span class="candidate-skill-tag"><?php echo esc_html( $skill ); ?></span>
            <?php endforeach; ?>
            <?php if ( count( $skills ) > $skill_max ) : ?>
                <span class="candidate-skill-tag" style="color:var(--color-gray-400)">+<?php echo esc_html( count( $skills ) - $skill_max ); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ( $rate ) : ?>
        <div class="candidate-rate">$<?php echo esc_html( number_format( $rate, 0 ) ); ?><?php esc_html_e( '/hr', 'civijobs' ); ?></div>
    <?php endif; ?>

    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="btn btn-outline btn-sm" style="margin-top:4px">
        <?php esc_html_e( 'View Profile', 'civijobs' ); ?>
    </a>
</div>
