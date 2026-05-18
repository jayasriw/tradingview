<?php
/**
 * Job filters sidebar
 *
 * @package CiviJobs
 */

$selected = [
    'job_type'       => (array) ( $_GET['job_type'] ?? [] ),
    'job_category'   => (array) ( $_GET['job_category'] ?? [] ),
    'job_experience' => (array) ( $_GET['job_experience'] ?? [] ),
    'job_salary'     => (array) ( $_GET['job_salary'] ?? [] ),
];
$has_filters = array_filter( $selected, fn( $v ) => ! empty( $v ) );
?>
<aside class="filters-sidebar" id="filters-sidebar" aria-label="<?php esc_attr_e( 'Job Filters', 'civijobs' ); ?>">
    <div class="filters-header">
        <h3 class="filters-title"><?php esc_html_e( 'Filter Jobs', 'civijobs' ); ?></h3>
        <?php if ( $has_filters ) : ?>
            <a href="<?php echo esc_url( remove_query_arg( [ 'job_type', 'job_category', 'job_experience', 'job_salary' ] ) ); ?>" class="filters-clear">
                <?php esc_html_e( 'Clear all', 'civijobs' ); ?>
            </a>
        <?php endif; ?>
    </div>

    <?php
    $filter_groups = [
        [
            'label'    => __( 'Job Type', 'civijobs' ),
            'taxonomy' => 'job_type',
        ],
        [
            'label'    => __( 'Category', 'civijobs' ),
            'taxonomy' => 'job_category',
        ],
        [
            'label'    => __( 'Experience Level', 'civijobs' ),
            'taxonomy' => 'job_experience',
        ],
        [
            'label'    => __( 'Salary Range', 'civijobs' ),
            'taxonomy' => 'job_salary',
        ],
    ];

    foreach ( $filter_groups as $group ) :
        $terms = get_terms( [ 'taxonomy' => $group['taxonomy'], 'hide_empty' => true ] );
        if ( is_wp_error( $terms ) || empty( $terms ) ) continue;
    ?>
    <div class="filter-group">
        <button class="filter-group-toggle" aria-expanded="true">
            <span class="filter-group-title"><?php echo esc_html( $group['label'] ); ?></span>
            <span class="filter-group-arrow">▼</span>
        </button>
        <div class="filter-group-body">
            <?php foreach ( $terms as $term ) :
                $is_checked = in_array( $term->slug, $selected[ $group['taxonomy'] ], true );
            ?>
                <label class="filter-checkbox<?php echo $is_checked ? ' checked' : ''; ?>">
                    <input type="checkbox"
                           name="<?php echo esc_attr( $group['taxonomy'] ); ?>[]"
                           value="<?php echo esc_attr( $term->slug ); ?>"
                           class="filter-input"
                           data-filter-group="<?php echo esc_attr( $group['taxonomy'] ); ?>"
                           <?php checked( $is_checked ); ?>>
                    <span class="filter-checkbox-label"><?php echo esc_html( $term->name ); ?></span>
                    <span class="filter-count"><?php echo esc_html( $term->count ); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Remote only toggle -->
    <div class="filter-group">
        <label class="filter-checkbox" style="border:none;padding:12px 0 0">
            <input type="checkbox" name="remote_only" value="1"
                   class="filter-input" data-filter-group="remote_only"
                   <?php checked( isset( $_GET['remote_only'] ) && $_GET['remote_only'] == '1' ); ?>>
            <span class="filter-checkbox-label"><?php esc_html_e( 'Remote Jobs Only', 'civijobs' ); ?></span>
        </label>
    </div>

    <button class="btn btn-primary btn-block filter-apply-btn" style="margin-top:20px" id="apply-filters-btn">
        <?php esc_html_e( 'Apply Filters', 'civijobs' ); ?>
    </button>
</aside>
