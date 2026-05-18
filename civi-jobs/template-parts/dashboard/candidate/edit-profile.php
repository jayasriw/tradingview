<?php
/**
 * Candidate: Edit Profile
 *
 * @package CiviJobs
 */

$user_id = get_current_user_id();
$user    = wp_get_current_user();
$profile = civijobs_get_candidate_data( $user_id );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Edit Profile', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Keep your profile up to date to attract employers.', 'civijobs' ); ?></p>
    </div>
</div>

<form id="edit-profile-form" class="edit-profile-form" enctype="multipart/form-data">
    <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>

    <!-- Photo + Basic -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Profile Photo', 'civijobs' ); ?></h3>
        <div style="display:flex;align-items:center;gap:20px">
            <img id="avatar-preview" src="<?php echo esc_url( civijobs_get_avatar_url( $user_id, 100 ) ); ?>"
                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--color-primary-light)">
            <div>
                <input type="file" id="avatar-upload" accept="image/*" style="display:none">
                <button type="button" class="btn btn-outline btn-sm" id="trigger-avatar-upload">
                    <?php esc_html_e( 'Change Photo', 'civijobs' ); ?>
                </button>
                <p style="font-size:0.75rem;color:var(--color-gray-500);margin-top:6px"><?php esc_html_e( 'JPG, PNG or WebP. Max 2MB.', 'civijobs' ); ?></p>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Basic Information', 'civijobs' ); ?></h3>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Full Name', 'civijobs' ); ?></label>
                <input type="text" name="display_name" class="form-control" value="<?php echo esc_attr( $user->display_name ); ?>">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Professional Headline', 'civijobs' ); ?></label>
                <input type="text" name="civi_headline" class="form-control"
                       value="<?php echo esc_attr( $profile['headline'] ); ?>"
                       placeholder="<?php esc_attr_e( 'e.g. Senior UX Designer at Google', 'civijobs' ); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Location', 'civijobs' ); ?></label>
                <input type="text" name="civi_location" class="form-control" value="<?php echo esc_attr( $profile['location'] ); ?>"
                       placeholder="<?php esc_attr_e( 'City, Country', 'civijobs' ); ?>">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Phone', 'civijobs' ); ?></label>
                <input type="tel" name="civi_phone" class="form-control" value="<?php echo esc_attr( $profile['phone'] ); ?>">
            </div>
        </div>
        <div class="form-row single">
            <div class="form-group">
                <label><?php esc_html_e( 'Bio / Summary', 'civijobs' ); ?></label>
                <textarea name="civi_bio" class="form-control" rows="4" placeholder="<?php esc_attr_e( 'Tell employers about yourself…', 'civijobs' ); ?>"><?php echo esc_textarea( $profile['bio'] ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Social Links -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Online Profiles', 'civijobs' ); ?></h3>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Website', 'civijobs' ); ?></label>
                <input type="url" name="civi_website" class="form-control" value="<?php echo esc_attr( $profile['website'] ); ?>" placeholder="https://yoursite.com">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'LinkedIn', 'civijobs' ); ?></label>
                <input type="url" name="civi_linkedin" class="form-control" value="<?php echo esc_attr( $profile['linkedin'] ); ?>" placeholder="https://linkedin.com/in/...">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'GitHub', 'civijobs' ); ?></label>
                <input type="url" name="civi_github" class="form-control" value="<?php echo esc_attr( $profile['github'] ); ?>" placeholder="https://github.com/...">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Hourly Rate ($)', 'civijobs' ); ?></label>
                <input type="number" name="civi_hourly_rate" class="form-control" value="<?php echo esc_attr( $profile['hourly_rate'] ); ?>" min="0">
            </div>
        </div>
    </div>

    <!-- Skills -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Skills', 'civijobs' ); ?></h3>
        <div class="form-group">
            <div class="tags-input-container" data-input-name="civi_skills[]" id="skills-tags">
                <?php foreach ( (array) $profile['skills'] as $skill ) : ?>
                    <span class="tags-input-tag">
                        <?php echo esc_html( $skill ); ?>
                        <input type="hidden" name="civi_skills[]" value="<?php echo esc_attr( $skill ); ?>">
                        <button type="button">&times;</button>
                    </span>
                <?php endforeach; ?>
                <input type="text" class="tags-input-field" placeholder="<?php esc_attr_e( 'Add a skill and press Enter', 'civijobs' ); ?>">
            </div>
        </div>
    </div>

    <!-- CV Upload -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Resume / CV', 'civijobs' ); ?></h3>
        <?php $cv = $profile['cv_file']; ?>
        <?php if ( $cv ) : ?>
            <div class="alert alert-success" style="margin-bottom:12px">
                ✅ <a href="<?php echo esc_url( $cv ); ?>" target="_blank"><?php esc_html_e( 'View current CV', 'civijobs' ); ?></a>
            </div>
        <?php endif; ?>
        <input type="file" name="cv_file" id="cv-upload" accept=".pdf,.doc,.docx" style="display:none">
        <button type="button" class="btn btn-outline" id="trigger-cv-upload">
            📎 <?php echo $cv ? esc_html__( 'Replace CV', 'civijobs' ) : esc_html__( 'Upload CV', 'civijobs' ); ?>
        </button>
        <span id="cv-filename" style="font-size:0.8rem;color:var(--color-gray-500);margin-left:8px"></span>
        <p style="font-size:0.75rem;color:var(--color-gray-500);margin-top:6px"><?php esc_html_e( 'PDF or Word, max 5MB', 'civijobs' ); ?></p>
    </div>

    <!-- Availability -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Job Preferences', 'civijobs' ); ?></h3>
        <div class="form-row">
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="civi_available_for_hire" value="1"
                           <?php checked( $profile['available'], true ); ?>>
                    <?php esc_html_e( 'I am available for hire', 'civijobs' ); ?>
                </label>
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Profile Visibility', 'civijobs' ); ?></label>
                <select name="civi_visibility" class="form-control">
                    <option value="public" <?php selected( $profile['visibility'], 'public' ); ?>><?php esc_html_e( 'Public', 'civijobs' ); ?></option>
                    <option value="private" <?php selected( $profile['visibility'], 'private' ); ?>><?php esc_html_e( 'Private (Employers Only)', 'civijobs' ); ?></option>
                </select>
            </div>
        </div>
    </div>

    <div id="profile-save-messages"></div>
    <button type="submit" class="btn btn-primary btn-lg"><?php esc_html_e( 'Save Profile', 'civijobs' ); ?></button>
</form>

<script>
document.getElementById('trigger-avatar-upload').addEventListener('click', () => document.getElementById('avatar-upload').click());
document.getElementById('trigger-cv-upload').addEventListener('click', () => document.getElementById('cv-upload').click());
document.getElementById('avatar-upload').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
        reader.readAsDataURL(this.files[0]);
    }
});
document.getElementById('cv-upload').addEventListener('change', function() {
    const span = document.getElementById('cv-filename');
    span.textContent = this.files[0] ? this.files[0].name : '';
});
</script>
