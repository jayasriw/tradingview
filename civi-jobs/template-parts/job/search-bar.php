<?php
/**
 * Job search bar component
 *
 * @package CiviJobs
 */

$categories = get_terms( [ 'taxonomy' => 'job_category', 'hide_empty' => true ] );
$current_keyword  = sanitize_text_field( $_GET['keyword'] ?? '' );
$current_location = sanitize_text_field( $_GET['location'] ?? '' );
$current_category = sanitize_text_field( $_GET['job_category'] ?? '' );
?>
<div class="search-bar">
    <form class="search-form" id="job-search-form" method="get" action="<?php echo esc_url( home_url( '/jobs' ) ); ?>">
        <div class="search-field">
            <span class="search-field-icon">🔍</span>
            <input type="text" name="keyword" value="<?php echo esc_attr( $current_keyword ); ?>"
                   placeholder="<?php esc_attr_e( 'Job title, keyword, or company', 'civijobs' ); ?>"
                   class="search-input" autocomplete="off">
        </div>

        <div class="search-divider"></div>

        <div class="search-field">
            <span class="search-field-icon">📍</span>
            <input type="text" name="location" value="<?php echo esc_attr( $current_location ); ?>"
                   placeholder="<?php esc_attr_e( 'City, state, or remote', 'civijobs' ); ?>"
                   class="search-input" autocomplete="off">
        </div>

        <div class="search-divider"></div>

        <div class="search-field search-field-select">
            <span class="search-field-icon">📂</span>
            <select name="job_category" class="search-select">
                <option value=""><?php esc_html_e( 'All Categories', 'civijobs' ); ?></option>
                <?php if ( ! is_wp_error( $categories ) ) :
                    foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $current_category, $cat->slug ); ?>>
                            <?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
                        </option>
                    <?php endforeach;
                endif; ?>
            </select>
        </div>

        <button type="submit" class="search-btn btn btn-primary">
            <?php esc_html_e( 'Find Jobs', 'civijobs' ); ?>
        </button>
    </form>
</div>
