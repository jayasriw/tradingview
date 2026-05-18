<?php
/**
 * Template Name: Home
 *
 * @package CiviJobs
 */

get_header();

$total_jobs        = wp_count_posts( 'civi_job' )->publish;
$total_companies   = wp_count_posts( 'civi_company' )->publish;
$total_candidates  = count_users()['avail_roles']['civi_candidate'] ?? 0;

$categories = get_terms( [
    'taxonomy'   => 'job_category',
    'hide_empty' => true,
    'number'     => 12,
] );

$featured_jobs = get_posts( [
    'post_type'      => 'civi_job',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'meta_key'       => '_is_featured',
    'meta_value'     => '1',
    'orderby'        => 'date',
    'order'          => 'DESC',
] );

if ( empty( $featured_jobs ) ) {
    $featured_jobs = get_posts( [
        'post_type'      => 'civi_job',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );
}

$featured_companies = get_posts( [
    'post_type'      => 'civi_company',
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    'orderby'        => 'meta_value_num',
    'meta_key'       => '_open_jobs_count',
    'order'          => 'DESC',
] );

$category_icons = [
    'technology'   => '💻', 'design'       => '🎨', 'marketing'    => '📣',
    'finance'      => '💰', 'healthcare'   => '🏥', 'education'    => '📚',
    'engineering'  => '⚙️', 'sales'        => '📈', 'legal'        => '⚖️',
    'hr'           => '👥', 'management'   => '🏢', 'creative'     => '✨',
];
?>
<main class="site-main" id="main">

    <!-- ===== HERO ===== -->
    <section class="hero" style="background:linear-gradient(135deg,#4f46e5 0%,#0ea5e9 100%);padding:80px 0 64px;color:#fff;text-align:center">
        <div class="container">
            <h1 class="hero-title" style="font-size:3rem;font-weight:900;margin-bottom:16px;color:#fff">
                <?php echo esc_html( civijobs_get_setting( 'hero_title', __( 'Find Your Dream Job Today', 'civijobs' ) ) ); ?>
            </h1>
            <p class="hero-subtitle" style="font-size:1.2rem;opacity:.9;max-width:600px;margin:0 auto 40px;color:#fff">
                <?php echo esc_html( civijobs_get_setting( 'hero_subtitle', __( 'Connecting top talent with leading companies worldwide.', 'civijobs' ) ) ); ?>
            </p>

            <?php get_template_part( 'template-parts/job/search-bar' ); ?>

            <div class="hero-stats" style="display:flex;gap:40px;justify-content:center;margin-top:40px;flex-wrap:wrap">
                <?php foreach ( [
                    [ $total_jobs,       __( 'Jobs Available', 'civijobs' ) ],
                    [ $total_companies,  __( 'Companies Hiring', 'civijobs' ) ],
                    [ $total_candidates, __( 'Candidates', 'civijobs' ) ],
                ] as [ $num, $label ] ) : ?>
                    <div style="text-align:center">
                        <div style="font-size:2rem;font-weight:800;color:#fff"><?php echo esc_html( number_format( (int) $num ) ); ?>+</div>
                        <div style="font-size:0.875rem;opacity:.8;color:#fff"><?php echo esc_html( $label ); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOB CATEGORIES ===== -->
    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
    <section class="section categories-section" style="padding:64px 0;background:#fff">
        <div class="container">
            <div style="text-align:center;margin-bottom:40px">
                <h2 style="font-size:1.875rem;font-weight:800;color:var(--color-gray-900)"><?php esc_html_e( 'Browse by Category', 'civijobs' ); ?></h2>
                <p style="color:var(--color-gray-500);margin-top:8px"><?php esc_html_e( 'Find the perfect job in your field', 'civijobs' ); ?></p>
            </div>
            <div class="category-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px">
                <?php foreach ( $categories as $cat ) :
                    $icon = $category_icons[ $cat->slug ] ?? '💼';
                    $cat_url = add_query_arg( 'job_category', $cat->slug, home_url( '/jobs' ) );
                ?>
                    <a href="<?php echo esc_url( $cat_url ); ?>" class="category-card" style="background:var(--color-gray-50);border:1px solid var(--color-gray-200);border-radius:var(--radius-lg);padding:24px;text-align:center;text-decoration:none;transition:all .2s">
                        <div style="font-size:2rem;margin-bottom:10px"><?php echo $icon; ?></div>
                        <div style="font-weight:700;color:var(--color-gray-900);font-size:0.9rem;margin-bottom:4px"><?php echo esc_html( $cat->name ); ?></div>
                        <div style="font-size:0.78rem;color:var(--color-gray-500)"><?php echo esc_html( sprintf( _n( '%d job', '%d jobs', $cat->count, 'civijobs' ), $cat->count ) ); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== FEATURED JOBS ===== -->
    <?php if ( ! empty( $featured_jobs ) ) : ?>
    <section class="section featured-jobs-section" style="padding:64px 0;background:var(--color-gray-50)">
        <div class="container">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:40px;flex-wrap:wrap;gap:16px">
                <div>
                    <h2 style="font-size:1.875rem;font-weight:800;color:var(--color-gray-900);margin:0"><?php esc_html_e( 'Featured Jobs', 'civijobs' ); ?></h2>
                    <p style="color:var(--color-gray-500);margin-top:6px;margin-bottom:0"><?php esc_html_e( 'Hand-picked opportunities from top companies', 'civijobs' ); ?></p>
                </div>
                <a href="<?php echo esc_url( home_url( '/jobs' ) ); ?>" class="btn btn-outline">
                    <?php esc_html_e( 'View All Jobs', 'civijobs' ); ?> →
                </a>
            </div>
            <div class="jobs-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px">
                <?php foreach ( $featured_jobs as $job ) :
                    get_template_part( 'template-parts/job/card-grid', null, [ 'job' => $job ] );
                endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== HOW IT WORKS ===== -->
    <section class="section how-it-works" style="padding:64px 0;background:#fff">
        <div class="container">
            <div style="text-align:center;margin-bottom:48px">
                <h2 style="font-size:1.875rem;font-weight:800;color:var(--color-gray-900)"><?php esc_html_e( 'How It Works', 'civijobs' ); ?></h2>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:40px;position:relative">
                <?php
                $steps = [
                    [ '1', '🔍', __( 'Search & Discover', 'civijobs' ),     __( 'Browse thousands of job listings or use our smart search to find roles that match your skills and experience.', 'civijobs' ) ],
                    [ '2', '📝', __( 'Create Your Profile', 'civijobs' ),    __( 'Build a compelling profile, upload your resume, and showcase your skills to stand out to employers.', 'civijobs' ) ],
                    [ '3', '🚀', __( 'Apply & Get Hired', 'civijobs' ),      __( 'Apply with one click, track your applications, and get hired faster with our smart matching technology.', 'civijobs' ) ],
                ];
                foreach ( $steps as [ $num, $icon, $title, $desc ] ) : ?>
                    <div style="text-align:center">
                        <div style="width:72px;height:72px;border-radius:50%;background:var(--color-primary-light);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.75rem">
                            <?php echo $icon; ?>
                        </div>
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--color-primary);color:#fff;font-size:0.8rem;font-weight:700;display:flex;align-items:center;justify-content:center;margin:-12px auto 16px;position:relative;top:-40px;left:22px">
                            <?php echo esc_html( $num ); ?>
                        </div>
                        <h3 style="font-size:1.1rem;font-weight:700;color:var(--color-gray-900);margin-bottom:10px"><?php echo esc_html( $title ); ?></h3>
                        <p style="color:var(--color-gray-600);line-height:1.7;font-size:0.9rem"><?php echo esc_html( $desc ); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FEATURED COMPANIES ===== -->
    <?php if ( ! empty( $featured_companies ) ) : ?>
    <section class="section" style="padding:64px 0;background:var(--color-gray-50)">
        <div class="container">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:40px;flex-wrap:wrap;gap:16px">
                <div>
                    <h2 style="font-size:1.875rem;font-weight:800;color:var(--color-gray-900);margin:0"><?php esc_html_e( 'Top Companies Hiring', 'civijobs' ); ?></h2>
                    <p style="color:var(--color-gray-500);margin-top:6px;margin-bottom:0"><?php esc_html_e( 'Join thousands of companies finding great talent', 'civijobs' ); ?></p>
                </div>
                <a href="<?php echo esc_url( home_url( '/companies' ) ); ?>" class="btn btn-outline">
                    <?php esc_html_e( 'Browse Companies', 'civijobs' ); ?> →
                </a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
                <?php foreach ( $featured_companies as $company ) :
                    $logo = get_the_post_thumbnail_url( $company->ID, 'company-logo' );
                    $jobs = (int) get_post_meta( $company->ID, '_open_jobs_count', true );
                ?>
                    <a href="<?php echo esc_url( get_permalink( $company->ID ) ); ?>"
                       style="background:#fff;border:1px solid var(--color-gray-200);border-radius:var(--radius-lg);padding:24px;text-align:center;text-decoration:none;display:flex;flex-direction:column;align-items:center;gap:10px;transition:all .2s">
                        <?php if ( $logo ) : ?>
                            <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_the_title( $company->ID ) ); ?>" style="width:56px;height:56px;object-fit:contain">
                        <?php else : ?>
                            <div style="width:56px;height:56px;background:var(--color-primary-light);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:700;color:var(--color-primary)">
                                <?php echo esc_html( mb_strtoupper( mb_substr( get_the_title( $company->ID ), 0, 1 ) ) ); ?>
                            </div>
                        <?php endif; ?>
                        <div style="font-weight:700;font-size:0.875rem;color:var(--color-gray-900)"><?php echo esc_html( get_the_title( $company->ID ) ); ?></div>
                        <div style="font-size:0.78rem;color:var(--color-primary);font-weight:600"><?php echo esc_html( $jobs ); ?> <?php esc_html_e( 'open positions', 'civijobs' ); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== CTA ===== -->
    <section class="cta-section" style="padding:80px 0;background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%);text-align:center;color:#fff">
        <div class="container">
            <h2 style="font-size:2.25rem;font-weight:900;color:#fff;margin-bottom:16px"><?php esc_html_e( 'Ready to Hire Top Talent?', 'civijobs' ); ?></h2>
            <p style="color:rgba(255,255,255,.8);font-size:1.1rem;max-width:560px;margin:0 auto 32px"><?php esc_html_e( 'Post your job today and reach thousands of qualified candidates. Get started with our flexible pricing plans.', 'civijobs' ); ?></p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
                <a href="<?php echo esc_url( home_url( '/post-job' ) ); ?>" class="btn btn-primary btn-lg"><?php esc_html_e( 'Post a Job', 'civijobs' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="btn btn-lg" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.3)"><?php esc_html_e( 'View Pricing', 'civijobs' ); ?></a>
            </div>
        </div>
    </section>

</main>

<?php get_footer();
