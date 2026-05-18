<?php
/**
 * Employer: Post Job form
 *
 * @package CiviJobs
 */

$user_id = get_current_user_id();
if ( ! civijobs_can_post_job( $user_id ) ) : ?>
    <div class="dash-header"><h1 class="dash-title"><?php esc_html_e( 'Post a Job', 'civijobs' ); ?></h1></div>
    <div class="alert alert-warning">
        <strong><?php esc_html_e( 'No job posting credits remaining.', 'civijobs' ); ?></strong>
        <p><?php esc_html_e( 'Please upgrade your package to continue posting jobs.', 'civijobs' ); ?></p>
        <a href="?section=packages" class="btn btn-warning btn-sm"><?php esc_html_e( 'View Packages', 'civijobs' ); ?></a>
    </div>
    <?php return;
endif;

$categories = get_terms( [ 'taxonomy' => 'job_category', 'hide_empty' => false ] );
$types      = get_terms( [ 'taxonomy' => 'job_type',     'hide_empty' => false ] );
$experience = get_terms( [ 'taxonomy' => 'job_experience','hide_empty' => false ] );
$company    = civijobs_get_employer_company( $user_id );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Post a Job', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Fill in the details to attract great candidates.', 'civijobs' ); ?></p>
    </div>
</div>

<form id="post-job-form" class="post-job-form">
    <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>

    <!-- Basic Info -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Basic Information', 'civijobs' ); ?></h3>

        <div class="form-row single">
            <div class="form-group">
                <label><?php esc_html_e( 'Job Title', 'civijobs' ); ?> <span class="required">*</span></label>
                <input type="text" name="job_title" class="form-control" required
                       placeholder="<?php esc_attr_e( 'e.g. Senior Frontend Developer', 'civijobs' ); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Category', 'civijobs' ); ?></label>
                <select name="job_category[]" class="form-control" multiple size="4">
                    <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Job Type', 'civijobs' ); ?></label>
                <select name="job_type[]" class="form-control" multiple size="4">
                    <?php foreach ( $types as $type ) : ?>
                        <option value="<?php echo esc_attr( $type->term_id ); ?>"><?php echo esc_html( $type->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row single">
            <div class="form-group">
                <label><?php esc_html_e( 'Job Description', 'civijobs' ); ?> <span class="required">*</span></label>
                <textarea name="job_description" class="form-control rich-editor" required rows="10"
                    placeholder="<?php esc_attr_e( 'Describe the role, responsibilities, and what makes this opportunity great…', 'civijobs' ); ?>"></textarea>
            </div>
        </div>
    </div>

    <!-- Location & Salary -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Location & Compensation', 'civijobs' ); ?></h3>

        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Location', 'civijobs' ); ?></label>
                <input type="text" name="location" class="form-control"
                       placeholder="<?php esc_attr_e( 'City, State or Remote', 'civijobs' ); ?>">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Experience Level', 'civijobs' ); ?></label>
                <select name="experience" class="form-control">
                    <option value=""><?php esc_html_e( 'Select experience level', 'civijobs' ); ?></option>
                    <?php foreach ( $experience as $exp ) : ?>
                        <option value="<?php echo esc_attr( $exp->name ); ?>"><?php echo esc_html( $exp->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row triple">
            <div class="form-group">
                <label><?php esc_html_e( 'Min Salary', 'civijobs' ); ?></label>
                <input type="number" name="salary_min" class="form-control" min="0" placeholder="e.g. 50000">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Max Salary', 'civijobs' ); ?></label>
                <input type="number" name="salary_max" class="form-control" min="0" placeholder="e.g. 80000">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Salary Type', 'civijobs' ); ?></label>
                <select name="salary_type" class="form-control">
                    <option value="annual"><?php esc_html_e( 'Per Year', 'civijobs' ); ?></option>
                    <option value="monthly"><?php esc_html_e( 'Per Month', 'civijobs' ); ?></option>
                    <option value="hourly"><?php esc_html_e( 'Per Hour', 'civijobs' ); ?></option>
                    <option value="daily"><?php esc_html_e( 'Per Day', 'civijobs' ); ?></option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Vacancies', 'civijobs' ); ?></label>
                <input type="number" name="vacancies" class="form-control" min="1" value="1">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Application Deadline', 'civijobs' ); ?></label>
                <input type="date" name="deadline" class="form-control" min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
            </div>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" name="remote_ok" value="1">
                <?php esc_html_e( 'This is a remote-friendly position', 'civijobs' ); ?>
            </label>
        </div>
    </div>

    <!-- Skills & Benefits -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Skills & Benefits', 'civijobs' ); ?></h3>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Required Skills', 'civijobs' ); ?></label>
                <div class="tags-input-container" data-input-name="skills">
                    <input type="text" class="tags-input-field" placeholder="<?php esc_attr_e( 'Type a skill and press Enter', 'civijobs' ); ?>">
                </div>
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Benefits', 'civijobs' ); ?></label>
                <div class="tags-input-container" data-input-name="benefits">
                    <input type="text" class="tags-input-field" placeholder="<?php esc_attr_e( 'Type a benefit and press Enter', 'civijobs' ); ?>">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label><?php esc_html_e( 'Company Video URL', 'civijobs' ); ?></label>
            <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/embed/...">
            <span style="font-size:0.75rem;color:var(--color-gray-500)"><?php esc_html_e( 'Optional: YouTube or Vimeo embed URL to showcase your company culture.', 'civijobs' ); ?></span>
        </div>
    </div>

    <!-- Company -->
    <?php if ( $company ) : ?>
        <input type="hidden" name="company_id" value="<?php echo esc_attr( $company->ID ); ?>">
    <?php endif; ?>

    <div id="post-job-messages"></div>
    <button type="submit" class="btn btn-primary btn-lg">
        <?php esc_html_e( 'Submit Job', 'civijobs' ); ?>
    </button>
    <p style="font-size:0.8rem;color:var(--color-gray-500);margin-top:8px">
        <?php
        $auto = civijobs_get_setting( 'auto_approve_jobs', '0' );
        echo $auto
            ? esc_html__( 'Your job will be published immediately.', 'civijobs' )
            : esc_html__( 'Your job will be reviewed by our team before publishing.', 'civijobs' );
        ?>
    </p>
</form>
