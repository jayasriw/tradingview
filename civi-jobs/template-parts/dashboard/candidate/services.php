<?php
/**
 * Candidate: My Services (Freelancer Marketplace)
 *
 * @package CiviJobs
 */

$user_id  = get_current_user_id();
$services = get_posts( [
    'post_type'      => 'civi_service',
    'author'         => $user_id,
    'posts_per_page' => -1,
    'post_status'    => [ 'publish', 'pending', 'draft' ],
] );

$service_cats = get_terms( [ 'taxonomy' => 'service_category', 'hide_empty' => false ] );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'My Services', 'civijobs' ); ?></h1>
        <p class="dash-subtitle"><?php printf( _n( '%d service', '%d services', count( $services ), 'civijobs' ), count( $services ) ); // phpcs:ignore ?></p>
    </div>
    <button class="btn btn-primary" data-modal-target="add-service-modal">
        + <?php esc_html_e( 'Add Service', 'civijobs' ); ?>
    </button>
</div>

<?php if ( $services ) : ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
        <?php foreach ( $services as $service ) :
            $price      = get_post_meta( $service->ID, '_service_price', true );
            $delivery   = get_post_meta( $service->ID, '_service_delivery', true );
            $thumb      = get_the_post_thumbnail_url( $service->ID, 'medium' );
            $is_publish = $service->post_status === 'publish';
        ?>
            <div class="service-manage-card">
                <?php if ( $thumb ) : ?>
                    <div class="service-manage-thumb" style="background-image:url('<?php echo esc_url( $thumb ); ?>')"></div>
                <?php else : ?>
                    <div class="service-manage-thumb service-manage-thumb--placeholder">🛠</div>
                <?php endif; ?>
                <div class="service-manage-body">
                    <div class="service-manage-title"><?php echo esc_html( $service->post_title ); ?></div>
                    <div class="service-manage-meta">
                        <?php if ( $price ) : ?>
                            <span style="font-weight:700;color:var(--color-primary)">$<?php echo esc_html( number_format( $price, 2 ) ); ?></span>
                        <?php endif; ?>
                        <?php if ( $delivery ) : ?>
                            <span style="color:var(--color-gray-500);font-size:0.8rem">⏱ <?php echo esc_html( $delivery ); ?> <?php esc_html_e( 'days delivery', 'civijobs' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top:10px;display:flex;gap:8px;align-items:center">
                        <?php if ( $is_publish ) : ?>
                            <span class="badge badge-green"><?php esc_html_e( 'Active', 'civijobs' ); ?></span>
                        <?php elseif ( $service->post_status === 'pending' ) : ?>
                            <span class="badge badge-orange"><?php esc_html_e( 'Pending', 'civijobs' ); ?></span>
                        <?php else : ?>
                            <span class="badge badge-gray"><?php esc_html_e( 'Draft', 'civijobs' ); ?></span>
                        <?php endif; ?>
                        <div style="margin-left:auto;display:flex;gap:6px">
                            <?php if ( $is_publish ) : ?>
                                <a href="<?php echo esc_url( get_permalink( $service->ID ) ); ?>" class="btn btn-sm btn-outline" target="_blank">
                                    <?php esc_html_e( 'View', 'civijobs' ); ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $service->ID . '&action=edit' ) ); ?>" class="btn btn-sm btn-outline">
                                <?php esc_html_e( 'Edit', 'civijobs' ); ?>
                            </a>
                            <button class="btn btn-sm btn-outline del delete-service-btn" data-id="<?php echo esc_attr( $service->ID ); ?>">
                                <?php esc_html_e( 'Delete', 'civijobs' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="empty-state">
        <div class="empty-state-icon">🛠</div>
        <div class="empty-state-title"><?php esc_html_e( 'No services yet', 'civijobs' ); ?></div>
        <div class="empty-state-text"><?php esc_html_e( 'Offer your skills as a service and get hired by clients.', 'civijobs' ); ?></div>
        <button class="btn btn-primary" data-modal-target="add-service-modal" style="margin-top:16px">
            <?php esc_html_e( 'Create Your First Service', 'civijobs' ); ?>
        </button>
    </div>
<?php endif; ?>

<!-- Add Service Modal -->
<div id="add-service-modal" class="modal" role="dialog" aria-modal="true">
    <div class="modal-overlay" data-modal-close></div>
    <div class="modal-dialog" style="max-width:580px">
        <div class="modal-header">
            <h3><?php esc_html_e( 'Add New Service', 'civijobs' ); ?></h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-service-form" enctype="multipart/form-data">
                <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                <div class="form-group">
                    <label><?php esc_html_e( 'Service Title', 'civijobs' ); ?> *</label>
                    <input type="text" name="title" class="form-control" required placeholder="<?php esc_attr_e( 'e.g. I will build a WordPress website', 'civijobs' ); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php esc_html_e( 'Category', 'civijobs' ); ?></label>
                        <select name="category" class="form-control">
                            <option value=""><?php esc_html_e( 'Select Category', 'civijobs' ); ?></option>
                            <?php foreach ( $service_cats as $cat ) : ?>
                                <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php esc_html_e( 'Price ($)', 'civijobs' ); ?> *</label>
                        <input type="number" name="price" class="form-control" required min="1" step="0.01">
                    </div>
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Delivery Time (days)', 'civijobs' ); ?></label>
                    <input type="number" name="delivery_days" class="form-control" min="1" value="3">
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Description', 'civijobs' ); ?> *</label>
                    <textarea name="description" class="form-control" rows="5" required
                              placeholder="<?php esc_attr_e( 'Describe what you will deliver…', 'civijobs' ); ?>"></textarea>
                </div>
                <div class="form-group">
                    <label><?php esc_html_e( 'Service Image', 'civijobs' ); ?></label>
                    <input type="file" name="service_image" accept="image/*" class="form-control">
                </div>
                <div id="service-form-messages"></div>
                <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Submit Service', 'civijobs' ); ?></button>
            </form>
        </div>
    </div>
</div>

<style>
.service-manage-card { background: #fff; border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); overflow: hidden; }
.service-manage-thumb { height: 160px; background: var(--color-gray-100); background-size: cover; background-position: center; }
.service-manage-thumb--placeholder { display: flex; align-items: center; justify-content: center; font-size: 3rem; }
.service-manage-body { padding: 16px; }
.service-manage-title { font-weight: 600; color: var(--color-gray-900); margin-bottom: 8px; }
.service-manage-meta { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
</style>
