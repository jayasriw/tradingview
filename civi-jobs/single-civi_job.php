<?php
/**
 * Single Job template
 *
 * @package CiviJobs
 */

get_header();
the_post();

$job_id     = get_the_ID();
$job        = civijobs_get_job_data( $job_id );
$company_id = $job['company_id'];
$company    = $company_id ? civijobs_get_company_data( $company_id ) : [];

civijobs_increment_job_views( $job_id );

$user_id    = get_current_user_id();
$has_applied = $user_id ? civijobs_has_applied( $job_id, $user_id ) : false;
$has_saved  = $user_id ? civijobs_has_saved( $job_id, $user_id ) : false;
$can_apply  = ! is_user_logged_in() || civijobs_is_candidate( $user_id );
?>
<main class="site-main" id="main">

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar" style="background:var(--color-gray-50);border-bottom:1px solid var(--color-gray-200)">
        <div class="container" style="padding-top:12px;padding-bottom:12px">
            <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>
        </div>
    </div>

    <div class="container" style="padding-top:40px;padding-bottom:64px">
        <div class="job-detail-layout" style="display:grid;grid-template-columns:1fr 340px;gap:32px;align-items:start">

            <!-- Main Content -->
            <div class="job-detail-main">

                <!-- Job Header Card -->
                <div class="job-detail-header card" style="margin-bottom:24px">
                    <div style="display:flex;gap:20px;align-items:flex-start">
                        <?php if ( $company && $company['logo'] ) : ?>
                            <img src="<?php echo esc_url( $company['logo'] ); ?>" alt="<?php echo esc_attr( $company['name'] ); ?>" class="company-logo" style="width:88px;height:88px">
                        <?php else : ?>
                            <div class="company-logo-placeholder" style="width:88px;height:88px">
                                <?php echo esc_html( mb_strtoupper( mb_substr( get_the_title(), 0, 1 ) ) ); ?>
                            </div>
                        <?php endif; ?>

                        <div style="flex:1">
                            <h1 style="font-size:1.625rem;font-weight:800;margin-bottom:6px"><?php the_title(); ?></h1>

                            <?php if ( $company ) : ?>
                                <a href="<?php echo esc_url( $company['url'] ); ?>" style="color:var(--color-primary);font-weight:600;text-decoration:none">
                                    <?php echo esc_html( $company['name'] ); ?>
                                </a>
                                <?php if ( $company['is_verified'] ) : ?>
                                    <span title="Verified" style="color:var(--color-success);margin-left:4px">✓</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="job-header-meta" style="display:flex;flex-wrap:wrap;gap:16px;margin-top:14px">
                                <?php if ( $job['location'] ) : ?>
                                    <span style="display:flex;align-items:center;gap:5px;font-size:0.875rem;color:var(--color-gray-600)">
                                        📍 <?php echo esc_html( $job['location'] ); ?>
                                        <?php if ( $job['remote'] ) : ?>
                                            <span class="badge badge-success" style="margin-left:4px"><?php esc_html_e( 'Remote OK', 'civijobs' ); ?></span>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ( ! empty( $job['types'] ) ) : ?>
                                    <span class="badge badge-primary"><?php echo esc_html( implode( ', ', $job['types'] ) ); ?></span>
                                <?php endif; ?>

                                <?php if ( $job['salary_min'] || $job['salary_max'] ) : ?>
                                    <span style="font-size:0.875rem;font-weight:600;color:var(--color-gray-800)">
                                        💵 <?php echo esc_html( civijobs_format_salary( $job['salary_min'], $job['salary_max'], $job['salary_type'] ) ); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ( $job['experience'] ) : ?>
                                    <span style="font-size:0.875rem;color:var(--color-gray-600)">
                                        🎓 <?php echo esc_html( $job['experience'] ); ?>
                                    </span>
                                <?php endif; ?>

                                <span style="font-size:0.875rem;color:var(--color-gray-500)">
                                    🕒 <?php echo esc_html( civijobs_time_ago( $job['date'] ) ); ?>
                                </span>
                            </div>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
                            <?php if ( $job['is_featured'] ) : ?>
                                <span class="badge badge-warning">⭐ <?php esc_html_e( 'Featured', 'civijobs' ); ?></span>
                            <?php endif; ?>
                            <button class="save-job-btn btn btn-outline btn-sm <?php echo $has_saved ? 'is-saved' : ''; ?>"
                                    data-job-id="<?php echo esc_attr( $job_id ); ?>"
                                    <?php if ( ! is_user_logged_in() ) echo 'data-require-login="1"'; ?>>
                                <?php echo $has_saved ? '❤️ ' . esc_html__( 'Saved', 'civijobs' ) : '🤍 ' . esc_html__( 'Save Job', 'civijobs' ); ?>
                            </button>
                        </div>
                    </div>

                    <?php if ( ! empty( $job['categories'] ) ) : ?>
                        <div class="tag-list" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--color-gray-100)">
                            <?php foreach ( $job['categories'] as $cat ) : ?>
                                <span class="tag"><?php echo esc_html( $cat ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Video -->
                <?php if ( $job['video_url'] ) : ?>
                    <div class="card" style="margin-bottom:24px;overflow:hidden">
                        <div class="card-header">
                            <h3 style="margin:0;font-size:1rem;font-weight:700"><?php esc_html_e( 'About the Company', 'civijobs' ); ?></h3>
                        </div>
                        <div class="job-video-wrap" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden">
                            <iframe src="<?php echo esc_url( $job['video_url'] ); ?>"
                                    style="position:absolute;top:0;left:0;width:100%;height:100%"
                                    frameborder="0" allowfullscreen loading="lazy"></iframe>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Job Description -->
                <div class="card" style="margin-bottom:24px">
                    <div class="card-header">
                        <h2 style="margin:0;font-size:1rem;font-weight:700"><?php esc_html_e( 'Job Description', 'civijobs' ); ?></h2>
                    </div>
                    <div class="card-body job-description">
                        <?php the_content(); ?>
                    </div>
                </div>

                <!-- Skills required -->
                <?php if ( ! empty( $job['skills'] ) ) : ?>
                    <div class="card" style="margin-bottom:24px">
                        <div class="card-header">
                            <h3 style="margin:0;font-size:1rem;font-weight:700"><?php esc_html_e( 'Required Skills', 'civijobs' ); ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="tag-list">
                                <?php foreach ( (array) $job['skills'] as $skill ) : ?>
                                    <span class="tag"><?php echo esc_html( $skill ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Benefits -->
                <?php if ( ! empty( $job['benefits'] ) ) : ?>
                    <div class="card" style="margin-bottom:24px">
                        <div class="card-header">
                            <h3 style="margin:0;font-size:1rem;font-weight:700"><?php esc_html_e( 'Benefits', 'civijobs' ); ?></h3>
                        </div>
                        <div class="card-body">
                            <ul style="margin:0;padding-left:20px;display:grid;grid-template-columns:1fr 1fr;gap:8px">
                                <?php foreach ( (array) $job['benefits'] as $benefit ) : ?>
                                    <li style="color:var(--color-gray-700)"><?php echo esc_html( $benefit ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Related Jobs -->
                <?php
                $related = get_posts( [
                    'post_type'      => 'civi_job',
                    'post_status'    => 'publish',
                    'posts_per_page' => 3,
                    'post__not_in'   => [ $job_id ],
                    'tax_query'      => [ [
                        'taxonomy' => 'job_category',
                        'field'    => 'term_id',
                        'terms'    => wp_get_post_terms( $job_id, 'job_category', [ 'fields' => 'ids' ] ),
                    ] ],
                ] );
                if ( $related ) : ?>
                    <div class="related-jobs" style="margin-top:32px">
                        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px"><?php esc_html_e( 'Similar Jobs', 'civijobs' ); ?></h3>
                        <div style="display:flex;flex-direction:column;gap:12px">
                            <?php foreach ( $related as $r_job ) :
                                setup_postdata( $r_job ); ?>
                                <?php get_template_part( 'template-parts/job/card-list', null, [ 'job' => $r_job ] ); ?>
                            <?php endforeach; wp_reset_postdata(); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div><!-- .job-detail-main -->

            <!-- Sidebar -->
            <aside class="job-detail-sidebar">

                <!-- Apply Box -->
                <div class="card" style="margin-bottom:20px;position:sticky;top:90px">
                    <div class="card-body" style="text-align:center">
                        <div style="font-size:2rem;font-weight:800;color:var(--color-gray-900);margin-bottom:4px">
                            <?php echo esc_html( civijobs_format_salary( $job['salary_min'], $job['salary_max'], $job['salary_type'] ) ); ?>
                        </div>
                        <div style="color:var(--color-gray-500);font-size:0.875rem;margin-bottom:20px">
                            <?php esc_html_e( 'Estimated salary', 'civijobs' ); ?>
                        </div>

                        <?php if ( $has_applied ) : ?>
                            <div class="alert alert-success" style="margin-bottom:16px">
                                ✅ <?php esc_html_e( 'You have applied for this job.', 'civijobs' ); ?>
                            </div>
                        <?php elseif ( ! is_user_logged_in() ) : ?>
                            <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="btn btn-primary btn-lg btn-block">
                                <?php esc_html_e( 'Login to Apply', 'civijobs' ); ?>
                            </a>
                            <p style="font-size:0.8rem;color:var(--color-gray-500);margin-top:10px">
                                <?php esc_html_e( "Don't have an account?", 'civijobs' ); ?>
                                <a href="<?php echo esc_url( home_url( '/register' ) ); ?>"><?php esc_html_e( 'Register', 'civijobs' ); ?></a>
                            </p>
                        <?php elseif ( $can_apply ) : ?>
                            <button class="btn btn-primary btn-lg btn-block" data-modal-target="apply-modal">
                                <?php esc_html_e( 'Apply Now', 'civijobs' ); ?>
                            </button>
                        <?php endif; ?>

                        <?php if ( $job['deadline'] ) : ?>
                            <div style="margin-top:14px;font-size:0.8rem;color:var(--color-gray-500)">
                                ⏳ <?php esc_html_e( 'Deadline:', 'civijobs' ); ?>
                                <strong><?php echo esc_html( gmdate( 'M j, Y', strtotime( $job['deadline'] ) ) ); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Job Overview -->
                <div class="card" style="margin-bottom:20px">
                    <div class="card-header">
                        <h3 style="margin:0;font-size:0.95rem;font-weight:700"><?php esc_html_e( 'Job Overview', 'civijobs' ); ?></h3>
                    </div>
                    <div class="card-body">
                        <?php $overview = [
                            [ '📅', __( 'Posted', 'civijobs' ),    get_the_date() ],
                            [ '💼', __( 'Job Type', 'civijobs' ),   implode( ', ', $job['types'] ) ],
                            [ '📍', __( 'Location', 'civijobs' ),   $job['location'] ],
                            [ '🎓', __( 'Experience', 'civijobs' ), $job['experience'] ],
                            [ '👥', __( 'Vacancies', 'civijobs' ),  $job['vacancies'] ],
                            [ '👁', __( 'Views', 'civijobs' ),      $job['views'] ],
                            [ '📋', __( 'Applications', 'civijobs' ), $job['apply_count'] ],
                        ];
                        foreach ( $overview as [ $icon, $label, $value ] ) :
                            if ( ! $value ) continue; ?>
                            <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid var(--color-gray-100)">
                                <span style="font-size:1rem;width:20px;flex-shrink:0"><?php echo $icon; ?></span>
                                <div>
                                    <div style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--color-gray-500)"><?php echo esc_html( $label ); ?></div>
                                    <div style="font-size:0.875rem;color:var(--color-gray-800);font-weight:500"><?php echo esc_html( $value ); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Company card -->
                <?php if ( $company ) : ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin:0;font-size:0.95rem;font-weight:700"><?php esc_html_e( 'About the Company', 'civijobs' ); ?></h3>
                        </div>
                        <div class="card-body" style="text-align:center">
                            <?php if ( $company['logo'] ) : ?>
                                <img src="<?php echo esc_url( $company['logo'] ); ?>" alt="<?php echo esc_attr( $company['name'] ); ?>" style="width:64px;height:64px;border-radius:var(--radius);object-fit:contain;border:1px solid var(--color-gray-200);margin-bottom:12px">
                            <?php endif; ?>
                            <div style="font-weight:700;color:var(--color-gray-900);margin-bottom:4px"><?php echo esc_html( $company['name'] ); ?></div>
                            <?php if ( ! empty( $company['industry'] ) ) : ?>
                                <div style="font-size:0.8rem;color:var(--color-gray-500);margin-bottom:12px"><?php echo esc_html( implode( ', ', $company['industry'] ) ); ?></div>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( $company['url'] ); ?>" class="btn btn-outline btn-sm btn-block">
                                <?php esc_html_e( 'View Company', 'civijobs' ); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Job Alert -->
                <div class="card" style="margin-top:20px">
                    <div class="card-body">
                        <h4 style="font-size:0.95rem;font-weight:700;margin-bottom:8px"><?php esc_html_e( 'Get Similar Jobs by Email', 'civijobs' ); ?></h4>
                        <p style="font-size:0.8rem;color:var(--color-gray-600);margin-bottom:14px"><?php esc_html_e( 'Create a job alert and receive new jobs via email.', 'civijobs' ); ?></p>
                        <button class="btn btn-outline btn-sm btn-block" data-modal-target="job-alert-modal">
                            <?php esc_html_e( 'Create Job Alert', 'civijobs' ); ?>
                        </button>
                    </div>
                </div>

            </aside>

        </div><!-- .job-detail-layout -->
    </div>

    <!-- Apply Modal -->
    <div id="apply-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="apply-modal-title">
        <div class="modal-overlay" data-modal-close></div>
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="apply-modal-title"><?php esc_html_e( 'Apply for', 'civijobs' ); ?> <?php the_title(); ?></h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <form id="apply-job-form">
                    <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                    <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>">
                    <div class="form-group">
                        <label><?php esc_html_e( 'Cover Letter', 'civijobs' ); ?> <span class="required">*</span></label>
                        <textarea name="cover_letter" class="form-control" rows="6" required
                            placeholder="<?php esc_attr_e( 'Introduce yourself and explain why you are a great fit for this role…', 'civijobs' ); ?>"></textarea>
                    </div>
                    <div class="form-group">
                        <label><?php esc_html_e( 'CV / Resume', 'civijobs' ); ?></label>
                        <?php $cv = get_user_meta( $user_id, 'civi_cv_file', true ); ?>
                        <?php if ( $cv ) : ?>
                            <p style="font-size:0.8rem;color:var(--color-success)">✅ <?php esc_html_e( 'Using your saved CV.', 'civijobs' ); ?> <a href="<?php echo esc_url( $cv ); ?>" target="_blank"><?php esc_html_e( 'View', 'civijobs' ); ?></a></p>
                            <input type="hidden" name="cv_url" value="<?php echo esc_attr( $cv ); ?>">
                        <?php else : ?>
                            <input type="file" name="cv_file" class="form-control" accept=".pdf,.doc,.docx">
                            <span style="font-size:0.75rem;color:var(--color-gray-500)"><?php esc_html_e( 'PDF or Word, max 5MB', 'civijobs' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <?php esc_html_e( 'Submit Application', 'civijobs' ); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Job Alert Modal -->
    <div id="job-alert-modal" class="modal" role="dialog" aria-modal="true">
        <div class="modal-overlay" data-modal-close></div>
        <div class="modal-dialog" style="max-width:460px">
            <div class="modal-header">
                <h3><?php esc_html_e( 'Create Job Alert', 'civijobs' ); ?></h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <form id="job-alert-form">
                    <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                    <div class="form-group">
                        <label><?php esc_html_e( 'Keywords', 'civijobs' ); ?></label>
                        <input type="text" name="keywords" class="form-control" value="<?php echo esc_attr( get_the_title() ); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php esc_html_e( 'Location', 'civijobs' ); ?></label>
                        <input type="text" name="location" class="form-control" value="<?php echo esc_attr( $job['location'] ); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php esc_html_e( 'Frequency', 'civijobs' ); ?></label>
                        <select name="frequency" class="form-control">
                            <option value="daily"><?php esc_html_e( 'Daily', 'civijobs' ); ?></option>
                            <option value="weekly"><?php esc_html_e( 'Weekly', 'civijobs' ); ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Subscribe', 'civijobs' ); ?></button>
                </form>
            </div>
        </div>
    </div>

</main>

<?php get_footer();
