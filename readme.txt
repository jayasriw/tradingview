=== JobPortal ===
Contributors: civiconnect
Tags: job-board, employment, hiring
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later

A modern job board WordPress theme.

== Description ==

JobPortal is a full-featured job board theme with:
- Employer and candidate dashboards
- Job listings with advanced search and map view
- Application management system
- Private messaging
- Job alerts
- WooCommerce membership packages
- Freelancer marketplace (services)
- Wallet system
- Company reviews
- Meeting scheduler

== Custom Database Tables ==

1. {prefix}jp_messages
2. {prefix}civi_notifications
3. {prefix}jp_meetings
4. {prefix}jp_job_alerts
5. {prefix}civi_applications
6. {prefix}civi_wallet
7. {prefix}civi_wallet_transactions
8. {prefix}jp_company_reviews
9. {prefix}civi_saved_jobs
10. {prefix}jp_package_orders

== Installation ==

1. Upload the theme folder to /wp-content/themes/
2. Activate the theme
3. Create pages and assign page templates:
   - Home: page-home.php
   - Jobs: page-jobs.php
   - Companies: page-companies.php
   - Candidates: page-candidates.php
   - Jobs Map: page-jobs-map.php
   - Login: page-login.php
   - Register: page-register.php
   - Pricing: page-pricing.php
   - Employer Dashboard: page-dashboard-employer.php
   - Candidate Dashboard: page-dashboard-candidate.php
