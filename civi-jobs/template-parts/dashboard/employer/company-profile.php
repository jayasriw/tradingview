<?php
/**
 * Employer: Company Profile
 *
 * @package CiviJobs
 */

$user_id    = get_current_user_id();
$company_id = civijobs_get_employer_company( $user_id );

if ( ! $company_id ) {
    // Create a company for this employer
    $company_id = wp_insert_post( [
        'post_type'   => 'civi_company',
        'post_status' => 'draft',
        'post_author' => $user_id,
        'post_title'  => __( 'My Company', 'civijobs' ),
    ] );
}

$company = get_post( $company_id );
$data    = civijobs_get_company_data( $company_id );

$industries = get_terms( [ 'taxonomy' => 'company_industry', 'hide_empty' => false ] );
$sizes      = get_terms( [ 'taxonomy' => 'company_size', 'hide_empty' => false ] );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Company Profile', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php esc_html_e( 'Keep your company profile updated to attract top talent.', 'civijobs' ); ?></p>
    </div>
    <?php if ( $company->post_status === 'publish' ) : ?>
        <a href="<?php echo esc_url( get_permalink( $company_id ) ); ?>" target="_blank" class="btn btn-outline">
            👁 <?php esc_html_e( 'Preview', 'civijobs' ); ?>
        </a>
    <?php endif; ?>
</div>

<form id="company-profile-form" class="edit-profile-form" enctype="multipart/form-data">
    <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
    <input type="hidden" name="company_id" value="<?php echo esc_attr( $company_id ); ?>">

    <!-- Logo & Cover -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Branding', 'civijobs' ); ?></h3>
        <div style="display:flex;gap:40px;flex-wrap:wrap">
            <div>
                <label style="font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block"><?php esc_html_e( 'Company Logo', 'civijobs' ); ?></label>
                <?php $logo_url = get_the_post_thumbnail_url( $company_id, 'company-logo' ); ?>
                <img id="logo-preview" src="<?php echo $logo_url ? esc_url( $logo_url ) : esc_url( get_template_directory_uri() . '/assets/images/company-placeholder.png' ); ?>"
                     style="width:100px;height:100px;object-fit:contain;border:1px solid var(--color-gray-200);border-radius:8px;background:#f9fafb">
                <div style="margin-top:10px">
                    <input type="file" id="logo-upload" name="company_logo" accept="image/*" style="display:none">
                    <button type="button" class="btn btn-outline btn-sm" id="trigger-logo-upload">
                        <?php esc_html_e( 'Change Logo', 'civijobs' ); ?>
                    </button>
                </div>
            </div>
            <div style="flex:1;min-width:200px">
                <label style="font-size:0.85rem;font-weight:600;margin-bottom:8px;display:block"><?php esc_html_e( 'Cover Image', 'civijobs' ); ?></label>
                <?php $cover_url = get_post_meta( $company_id, '_cover_image', true ); ?>
                <?php if ( $cover_url ) : ?>
                    <img src="<?php echo esc_url( $cover_url ); ?>"
                         style="width:100%;max-width:320px;height:100px;object-fit:cover;border-radius:8px;border:1px solid var(--color-gray-200)">
                <?php else : ?>
                    <div style="width:100%;max-width:320px;height:100px;background:var(--color-gray-100);border-radius:8px;border:2px dashed var(--color-gray-300);display:flex;align-items:center;justify-content:center;color:var(--color-gray-400)">
                        <?php esc_html_e( 'No cover image', 'civijobs' ); ?>
                    </div>
                <?php endif; ?>
                <div style="margin-top:10px">
                    <input type="file" id="cover-upload" name="company_cover" accept="image/*" style="display:none">
                    <button type="button" class="btn btn-outline btn-sm" id="trigger-cover-upload">
                        <?php esc_html_e( 'Change Cover', 'civijobs' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Basic Info -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Basic Information', 'civijobs' ); ?></h3>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Company Name', 'civijobs' ); ?> *</label>
                <input type="text" name="company_name" class="form-control" value="<?php echo esc_attr( $company->post_title ); ?>" required>
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Tagline', 'civijobs' ); ?></label>
                <input type="text" name="company_tagline" class="form-control" value="<?php echo esc_attr( $data['tagline'] ?? '' ); ?>"
                       placeholder="<?php esc_attr_e( 'e.g. Building the future of work', 'civijobs' ); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Industry', 'civijobs' ); ?></label>
                <select name="company_industry" class="form-control">
                    <option value=""><?php esc_html_e( 'Select Industry', 'civijobs' ); ?></option>
                    <?php
                    $current_industry = wp_get_post_terms( $company_id, 'company_industry', [ 'fields' => 'slugs' ] );
                    foreach ( $industries as $ind ) : ?>
                        <option value="<?php echo esc_attr( $ind->slug ); ?>"
                                <?php selected( in_array( $ind->slug, (array) $current_industry, true ) ); ?>>
                            <?php echo esc_html( $ind->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Company Size', 'civijobs' ); ?></label>
                <select name="company_size" class="form-control">
                    <option value=""><?php esc_html_e( 'Select Size', 'civijobs' ); ?></option>
                    <?php
                    $current_size = wp_get_post_terms( $company_id, 'company_size', [ 'fields' => 'slugs' ] );
                    foreach ( $sizes as $sz ) : ?>
                        <option value="<?php echo esc_attr( $sz->slug ); ?>"
                                <?php selected( in_array( $sz->slug, (array) $current_size, true ) ); ?>>
                            <?php echo esc_html( $sz->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Location', 'civijobs' ); ?></label>
                <input type="text" name="company_location" class="form-control"
                       value="<?php echo esc_attr( get_post_meta( $company_id, '_company_location', true ) ); ?>"
                       placeholder="<?php esc_attr_e( 'City, Country', 'civijobs' ); ?>">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Founded Year', 'civijobs' ); ?></label>
                <input type="number" name="company_founded" class="form-control"
                       value="<?php echo esc_attr( get_post_meta( $company_id, '_company_founded', true ) ); ?>"
                       min="1900" max="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. 2015', 'civijobs' ); ?>">
            </div>
        </div>
        <div class="form-row single">
            <div class="form-group">
                <label><?php esc_html_e( 'About the Company', 'civijobs' ); ?></label>
                <textarea name="company_description" class="form-control" rows="6"
                          placeholder="<?php esc_attr_e( 'Tell candidates about your company, culture and mission…', 'civijobs' ); ?>"><?php echo esc_textarea( $company->post_content ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Contact & Social -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Contact & Social', 'civijobs' ); ?></h3>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Website', 'civijobs' ); ?></label>
                <input type="url" name="company_website" class="form-control"
                       value="<?php echo esc_attr( get_post_meta( $company_id, '_company_website', true ) ); ?>"
                       placeholder="https://yourcompany.com">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Email', 'civijobs' ); ?></label>
                <input type="email" name="company_email" class="form-control"
                       value="<?php echo esc_attr( get_post_meta( $company_id, '_company_email', true ) ); ?>"
                       placeholder="contact@company.com">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'LinkedIn', 'civijobs' ); ?></label>
                <input type="url" name="company_linkedin" class="form-control"
                       value="<?php echo esc_attr( get_post_meta( $company_id, '_company_linkedin', true ) ); ?>"
                       placeholder="https://linkedin.com/company/...">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Twitter / X', 'civijobs' ); ?></label>
                <input type="url" name="company_twitter" class="form-control"
                       value="<?php echo esc_attr( get_post_meta( $company_id, '_company_twitter', true ) ); ?>"
                       placeholder="https://twitter.com/...">
            </div>
        </div>
    </div>

    <!-- Map Location -->
    <div class="form-section">
        <h3 class="form-section-title"><?php esc_html_e( 'Map Location', 'civijobs' ); ?></h3>
        <div class="form-row">
            <div class="form-group">
                <label><?php esc_html_e( 'Latitude', 'civijobs' ); ?></label>
                <input type="text" name="company_lat" class="form-control"
                       value="<?php echo esc_attr( get_post_meta( $company_id, '_lat', true ) ); ?>"
                       placeholder="e.g. 40.7128">
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Longitude', 'civijobs' ); ?></label>
                <input type="text" name="company_lng" class="form-control"
                       value="<?php echo esc_attr( get_post_meta( $company_id, '_lng', true ) ); ?>"
                       placeholder="e.g. -74.0060">
            </div>
        </div>
    </div>

    <div id="company-profile-messages"></div>
    <button type="submit" class="btn btn-primary btn-lg"><?php esc_html_e( 'Save Company Profile', 'civijobs' ); ?></button>
</form>

<script>
document.getElementById('trigger-logo-upload').addEventListener('click', () => document.getElementById('logo-upload').click());
document.getElementById('trigger-cover-upload').addEventListener('click', () => document.getElementById('cover-upload').click());
document.getElementById('logo-upload').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('logo-preview').src = e.target.result;
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
