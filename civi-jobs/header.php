<?php
/**
 * The site header template.
 *
 * @package CiviJobs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user    = wp_get_current_user();
$is_logged_in    = is_user_logged_in();
$user_id         = $is_logged_in ? get_current_user_id() : 0;
$user_role       = '';
$unread_count    = 0;

if ( $is_logged_in ) {
	$user_roles = (array) $current_user->roles;
	$user_role  = ! empty( $user_roles ) ? $user_roles[0] : 'subscriber';

	// Get unread notification count if function exists
	if ( function_exists( 'civijobs_get_unread_notification_count' ) ) {
		$unread_count = (int) civijobs_get_unread_notification_count( $user_id );
	}
}

// Determine dashboard URL based on role
$dashboard_url = home_url( '/dashboard/' );
if ( 'civi_employer' === $user_role ) {
	$employer_page = (int) get_theme_mod( 'civijobs_employer_dashboard_page' );
	if ( $employer_page ) {
		$dashboard_url = get_permalink( $employer_page );
	}
} elseif ( 'civi_candidate' === $user_role ) {
	$candidate_page = (int) get_theme_mod( 'civijobs_candidate_dashboard_page' );
	if ( $candidate_page ) {
		$dashboard_url = get_permalink( $candidate_page );
	}
}

$login_page_url    = ( (int) get_theme_mod( 'civijobs_login_page' ) ) ? get_permalink( (int) get_theme_mod( 'civijobs_login_page' ) ) : wp_login_url();
$register_page_url = ( (int) get_theme_mod( 'civijobs_register_page' ) ) ? get_permalink( (int) get_theme_mod( 'civijobs_register_page' ) ) : wp_registration_url();
$logout_url        = wp_logout_url( home_url( '/' ) );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php if ( is_singular() && ! is_front_page() ) : ?>
		<meta name="description" content="<?php echo esc_attr( wp_trim_words( get_the_excerpt(), 25 ) ); ?>">
	<?php else : ?>
		<meta name="description" content="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>">
	<?php endif; ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>
	<?php if ( $is_logged_in ) : ?>
		data-user-id="<?php echo esc_attr( $user_id ); ?>"
		data-user-role="<?php echo esc_attr( $user_role ); ?>"
	<?php else : ?>
		data-user-id="0"
		data-user-role="guest"
	<?php endif; ?>
>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<!-- Skip to content -->
	<a class="sr-only" href="#site-content"><?php esc_html_e( 'Skip to content', 'civijobs' ); ?></a>

	<!-- ===================== SITE HEADER ===================== -->
	<header id="site-header" class="cj-header" role="banner">
		<div class="cj-container cj-header-inner">

			<!-- Logo -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cj-header-logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
						<path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
					</svg>
					<span><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</a>

			<!-- Primary Navigation -->
			<nav class="cj-nav" id="site-navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'civijobs' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'cj-nav-menu',
						'container'      => false,
						'fallback_cb'    => function () {
							echo '<ul class="cj-nav-menu">';
							echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'civijobs' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/jobs/' ) ) . '">' . esc_html__( 'Jobs', 'civijobs' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/companies/' ) ) . '">' . esc_html__( 'Companies', 'civijobs' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/candidates/' ) ) . '">' . esc_html__( 'Candidates', 'civijobs' ) . '</a></li>';
							echo '</ul>';
						},
					)
				);
				?>
			</nav>

			<!-- Header Actions -->
			<div class="cj-nav-actions" id="header-actions">

				<?php if ( $is_logged_in ) : ?>

					<!-- Notification Bell -->
					<div class="cj-dropdown" id="notification-dropdown" aria-haspopup="true" aria-expanded="false">
						<button
							class="cj-notif-btn"
							id="notification-bell"
							aria-label="<?php esc_attr_e( 'Notifications', 'civijobs' ); ?>"
							type="button"
						>
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
								<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
							</svg>
							<?php if ( $unread_count > 0 ) : ?>
								<span class="cj-notif-count" id="notif-count" aria-label="<?php echo esc_attr( sprintf( _n( '%d unread notification', '%d unread notifications', $unread_count, 'civijobs' ), $unread_count ) ); ?>">
									<?php echo $unread_count > 99 ? '99+' : esc_html( $unread_count ); ?>
								</span>
							<?php else : ?>
								<span class="cj-notif-count cj-hidden" id="notif-count" aria-hidden="true">0</span>
							<?php endif; ?>
						</button>

						<!-- Notification Dropdown -->
						<div class="cj-dropdown-menu cj-notif-dropdown" id="notification-panel" role="menu" aria-label="<?php esc_attr_e( 'Recent Notifications', 'civijobs' ); ?>" style="min-width:320px;right:0;left:auto;">
							<div class="cj-notif-header" style="display:flex;align-items:center;justify-content:space-between;padding:var(--cj-space-4) var(--cj-space-4) var(--cj-space-3);border-bottom:1px solid var(--cj-gray-100);">
								<span style="font-size:var(--cj-text-sm);font-weight:var(--cj-font-semibold);color:var(--cj-gray-900);"><?php esc_html_e( 'Notifications', 'civijobs' ); ?></span>
								<button class="cj-btn-ghost cj-text-xs" id="mark-all-read" style="font-size:var(--cj-text-xs);background:none;border:none;color:var(--cj-primary);cursor:pointer;padding:0;" type="button">
									<?php esc_html_e( 'Mark all read', 'civijobs' ); ?>
								</button>
							</div>

							<!-- Notification items loaded via JS -->
							<div class="cj-notif-list" id="notification-list" style="max-height:320px;overflow-y:auto;">
								<div class="cj-notif-loading" style="padding:var(--cj-space-6);text-align:center;color:var(--cj-gray-400);font-size:var(--cj-text-sm);">
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:cj-spin 1s linear infinite;margin:0 auto var(--cj-space-2);">
										<path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
									</svg>
									<?php esc_html_e( 'Loading…', 'civijobs' ); ?>
								</div>
							</div>

							<div style="padding:var(--cj-space-3) var(--cj-space-4);border-top:1px solid var(--cj-gray-100);text-align:center;">
								<a href="<?php echo esc_url( home_url( '/notifications/' ) ); ?>" class="cj-text-sm" style="font-size:var(--cj-text-sm);color:var(--cj-primary);">
									<?php esc_html_e( 'View all notifications', 'civijobs' ); ?>
								</a>
							</div>
						</div>
					</div><!-- /notification-dropdown -->

					<!-- User Avatar + Dropdown -->
					<div class="cj-user-menu" id="user-menu-wrapper" aria-haspopup="true" aria-expanded="false">
						<button class="cj-user-menu-trigger" id="user-menu-trigger" type="button" aria-label="<?php esc_attr_e( 'User menu', 'civijobs' ); ?>">
							<?php
							$avatar_url = get_avatar_url( $user_id, array( 'size' => 64 ) );
							if ( function_exists( 'civijobs_get_user_avatar' ) ) {
								$custom_avatar = civijobs_get_user_avatar( $user_id, 'candidate-avatar-sm' );
								if ( $custom_avatar ) {
									$avatar_url = $custom_avatar;
								}
							}
							?>
							<img
								src="<?php echo esc_url( $avatar_url ); ?>"
								alt="<?php echo esc_attr( $current_user->display_name ); ?>"
								class="cj-user-avatar"
								width="32"
								height="32"
								loading="lazy"
							>
							<span class="cj-hide-md" style="font-size:var(--cj-text-sm);font-weight:var(--cj-font-medium);color:var(--cj-gray-700);max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
								<?php echo esc_html( $current_user->display_name ); ?>
							</span>
							<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="cj-hide-md">
								<polyline points="6 9 12 15 18 9"></polyline>
							</svg>
						</button>

						<div class="cj-user-menu-dropdown" id="user-menu-dropdown" role="menu">

							<!-- User info header -->
							<div style="padding:var(--cj-space-4);border-bottom:1px solid var(--cj-gray-100);">
								<div style="font-size:var(--cj-text-sm);font-weight:var(--cj-font-semibold);color:var(--cj-gray-900);"><?php echo esc_html( $current_user->display_name ); ?></div>
								<div style="font-size:var(--cj-text-xs);color:var(--cj-gray-500);margin-top:2px;"><?php echo esc_html( $current_user->user_email ); ?></div>
								<?php if ( $user_role ) : ?>
									<span class="cj-badge cj-badge-primary" style="margin-top:var(--cj-space-2);">
										<?php
										$role_labels = array(
											'civi_employer'  => __( 'Employer', 'civijobs' ),
											'civi_candidate' => __( 'Candidate', 'civijobs' ),
											'administrator'  => __( 'Admin', 'civijobs' ),
										);
										echo esc_html( $role_labels[ $user_role ] ?? ucfirst( $user_role ) );
										?>
									</span>
								<?php endif; ?>
							</div>

							<!-- Dashboard link -->
							<a href="<?php echo esc_url( $dashboard_url ); ?>" class="cj-user-menu-item" role="menuitem">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect>
									<rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
								</svg>
								<?php esc_html_e( 'Dashboard', 'civijobs' ); ?>
							</a>

							<!-- Profile link -->
							<?php if ( 'civi_employer' === $user_role ) : ?>
								<a href="<?php echo esc_url( home_url( '/company-profile/' ) ); ?>" class="cj-user-menu-item" role="menuitem">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
									</svg>
									<?php esc_html_e( 'Company Profile', 'civijobs' ); ?>
								</a>
								<a href="<?php echo esc_url( home_url( '/post-job/' ) ); ?>" class="cj-user-menu-item" role="menuitem">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line>
									</svg>
									<?php esc_html_e( 'Post a Job', 'civijobs' ); ?>
								</a>
							<?php elseif ( 'civi_candidate' === $user_role ) : ?>
								<a href="<?php echo esc_url( home_url( '/candidate-profile/' ) ); ?>" class="cj-user-menu-item" role="menuitem">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>
									</svg>
									<?php esc_html_e( 'My Profile', 'civijobs' ); ?>
								</a>
								<a href="<?php echo esc_url( home_url( '/saved-jobs/' ) ); ?>" class="cj-user-menu-item" role="menuitem">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
									</svg>
									<?php esc_html_e( 'Saved Jobs', 'civijobs' ); ?>
								</a>
							<?php endif; ?>

							<a href="<?php echo esc_url( home_url( '/messages/' ) ); ?>" class="cj-user-menu-item" role="menuitem">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
								</svg>
								<?php esc_html_e( 'Messages', 'civijobs' ); ?>
							</a>

							<?php if ( current_user_can( 'manage_options' ) ) : ?>
								<div class="cj-user-menu-divider" role="separator"></div>
								<a href="<?php echo esc_url( admin_url() ); ?>" class="cj-user-menu-item" role="menuitem">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<circle cx="12" cy="12" r="3"></circle>
										<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
									</svg>
									<?php esc_html_e( 'WordPress Admin', 'civijobs' ); ?>
								</a>
							<?php endif; ?>

							<div class="cj-user-menu-divider" role="separator"></div>

							<a href="<?php echo esc_url( $logout_url ); ?>" class="cj-user-menu-item danger" role="menuitem">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
									<polyline points="16 17 21 12 16 7"></polyline>
									<line x1="21" y1="12" x2="9" y2="12"></line>
								</svg>
								<?php esc_html_e( 'Log Out', 'civijobs' ); ?>
							</a>
						</div>
					</div><!-- /user-menu-wrapper -->

				<?php else : ?>

					<!-- Guest: Login + Register buttons -->
					<a href="<?php echo esc_url( $login_page_url ); ?>" class="cj-btn cj-btn-secondary cj-btn-sm cj-hide-sm">
						<?php esc_html_e( 'Login', 'civijobs' ); ?>
					</a>
					<a href="<?php echo esc_url( $register_page_url ); ?>" class="cj-btn cj-btn-primary cj-btn-sm">
						<?php esc_html_e( 'Register', 'civijobs' ); ?>
					</a>

				<?php endif; ?>

				<!-- Mobile Hamburger -->
				<button
					class="cj-hamburger"
					id="mobile-menu-toggle"
					aria-label="<?php esc_attr_e( 'Toggle mobile menu', 'civijobs' ); ?>"
					aria-expanded="false"
					aria-controls="site-navigation"
					type="button"
				>
					<span aria-hidden="true"></span>
					<span aria-hidden="true"></span>
					<span aria-hidden="true"></span>
				</button>

			</div><!-- /cj-nav-actions -->
		</div><!-- /cj-header-inner -->

		<!-- Mobile nav overlay -->
		<div class="cj-mobile-nav cj-hidden" id="mobile-nav" role="navigation" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'civijobs' ); ?>" style="position:absolute;top:100%;left:0;right:0;background:var(--cj-white);border-bottom:1px solid var(--cj-gray-200);box-shadow:var(--cj-shadow-lg);z-index:var(--cj-z-dropdown);padding:var(--cj-space-4);">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'mobile-primary-menu',
					'menu_class'     => 'cj-mobile-nav-list',
					'container'      => false,
					'fallback_cb'    => function () {
						echo '<ul class="cj-mobile-nav-list">';
						echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'civijobs' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/jobs/' ) ) . '">' . esc_html__( 'Jobs', 'civijobs' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/companies/' ) ) . '">' . esc_html__( 'Companies', 'civijobs' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/candidates/' ) ) . '">' . esc_html__( 'Candidates', 'civijobs' ) . '</a></li>';
						echo '</ul>';
					},
					'depth'          => 2,
				)
			);
			?>
			<?php if ( ! $is_logged_in ) : ?>
				<div style="display:flex;gap:var(--cj-space-3);margin-top:var(--cj-space-4);padding-top:var(--cj-space-4);border-top:1px solid var(--cj-gray-100);">
					<a href="<?php echo esc_url( $login_page_url ); ?>" class="cj-btn cj-btn-secondary cj-btn-sm" style="flex:1;justify-content:center;"><?php esc_html_e( 'Login', 'civijobs' ); ?></a>
					<a href="<?php echo esc_url( $register_page_url ); ?>" class="cj-btn cj-btn-primary cj-btn-sm" style="flex:1;justify-content:center;"><?php esc_html_e( 'Register', 'civijobs' ); ?></a>
				</div>
			<?php endif; ?>
		</div>

	</header><!-- /site-header -->

	<div id="site-content" class="site-content">
