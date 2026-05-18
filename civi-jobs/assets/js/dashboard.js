/**
 * CiviJobs - Dashboard JavaScript
 * Sidebar collapse, application status, save/unsave job, apply modal,
 * delete job, featured job, download CV, meetings, job alerts, wallet,
 * package purchase, and Chart.js analytics.
 */
(function ($) {
    'use strict';

    /* =========================================================
       UTILITY
    ========================================================= */

    function getAjaxUrl() {
        return (typeof civijobs_ajax !== 'undefined' && civijobs_ajax.ajaxurl) ? civijobs_ajax.ajaxurl : '/wp-admin/admin-ajax.php';
    }

    function getNonce() {
        return (typeof civijobs_ajax !== 'undefined' && civijobs_ajax.nonce) ? civijobs_ajax.nonce : '';
    }

    function toast(msg, type) {
        if (typeof CiviJobs !== 'undefined' && CiviJobs.toast) {
            CiviJobs.toast(msg, type || 'info');
        }
    }

    function openModal(id) {
        if (typeof CiviJobs !== 'undefined' && CiviJobs.openModal) {
            CiviJobs.openModal(id);
        } else {
            $('#' + id).addClass('is-open');
            $('body').addClass('modal-open');
        }
    }

    function closeModal(id) {
        if (typeof CiviJobs !== 'undefined' && CiviJobs.closeModal) {
            CiviJobs.closeModal(id);
        } else {
            $('#' + id).removeClass('is-open');
            $('body').removeClass('modal-open');
        }
    }

    /* =========================================================
       1. DASHBOARD SIDEBAR COLLAPSE (mobile)
    ========================================================= */

    function initSidebarCollapse() {
        var $toggle  = $('.cj-dashboard-sidebar-toggle, [data-toggle="dashboard-sidebar"]');
        var $sidebar = $('.cj-dashboard-sidebar, #dashboard-sidebar');

        $toggle.on('click', function () {
            var isOpen = $sidebar.hasClass('is-open');
            $sidebar.toggleClass('is-open', !isOpen);
            $(this).toggleClass('is-active', !isOpen).attr('aria-expanded', !isOpen);
            $('body').toggleClass('dashboard-sidebar-open', !isOpen);
            $(document).trigger('civijobs:sidebar:toggle', [!isOpen]);
        });

        // Close on outside click (mobile)
        $(document).on('click', function (e) {
            if (window.innerWidth < 992 &&
                $sidebar.hasClass('is-open') &&
                !$(e.target).closest('.cj-dashboard-sidebar, [data-toggle="dashboard-sidebar"]').length) {
                $sidebar.removeClass('is-open');
                $toggle.removeClass('is-active').attr('aria-expanded', false);
                $('body').removeClass('dashboard-sidebar-open');
            }
        });
    }

    /* =========================================================
       2. APPLICATION STATUS UPDATE (employer)
    ========================================================= */

    function initApplicationStatus() {
        $(document).on('change', '.cj-application-status-select, [data-action="update-application-status"]', function () {
            var $select    = $(this);
            var appId      = $select.data('application-id') || $select.closest('[data-application-id]').data('application-id');
            var status     = $select.val();

            if (!appId || !status) return;

            var $row = $select.closest('.cj-application-row, tr, .cj-application-card');
            $row.addClass('is-updating');

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : {
                    action         : 'civijobs_update_application_status',
                    nonce          : getNonce(),
                    application_id : appId,
                    status         : status
                },
                success: function (res) {
                    $row.removeClass('is-updating');
                    if (res && res.success) {
                        toast(res.data && res.data.message ? res.data.message : 'Status updated.', 'success');
                        // Update status badge if present
                        $row.find('.cj-status-badge').text(status).attr('data-status', status);
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Failed to update status.', 'error');
                        // Revert select
                        var prev = $row.data('prev-status');
                        if (prev) { $select.val(prev); }
                    }
                },
                error: function () {
                    $row.removeClass('is-updating');
                    toast('Connection error. Please try again.', 'error');
                }
            });

            // Remember previous value for revert
            $row.data('prev-status', $select.find('option:not(:selected)').filter(function () {
                return $(this).val() !== status;
            }).first().val());
        });
    }

    /* =========================================================
       3. SAVE / UNSAVE JOB (heart icon)
    ========================================================= */

    function initSaveJob() {
        $(document).on('click', '.cj-save-job-btn, [data-action="save-job"]', function (e) {
            e.preventDefault();

            var $btn  = $(this);
            if ($btn.hasClass('is-loading')) return;

            var jobId = $btn.data('job-id') || $btn.closest('[data-job-id]').data('job-id');
            if (!jobId) return;

            var isSaved = $btn.hasClass('is-saved');
            $btn.addClass('is-loading');

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : {
                    action : 'civijobs_toggle_saved_job',
                    nonce  : getNonce(),
                    job_id : jobId
                },
                success: function (res) {
                    $btn.removeClass('is-loading');
                    if (res && res.success) {
                        var nowSaved = res.data && res.data.saved;
                        $btn.toggleClass('is-saved', nowSaved);
                        $btn.attr('aria-label', nowSaved ? 'Unsave job' : 'Save job');
                        $btn.attr('title',      nowSaved ? 'Unsave job' : 'Save job');

                        // Update count if present
                        var $count = $btn.find('.cj-save-count');
                        if ($count.length && res.data && res.data.count !== undefined) {
                            $count.text(res.data.count);
                        }

                        toast(nowSaved ? 'Job saved!' : 'Job removed from saved.', 'success');
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Could not save job.', 'error');
                    }
                },
                error: function () {
                    $btn.removeClass('is-loading');
                    toast('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    /* =========================================================
       4. APPLY FOR JOB MODAL
    ========================================================= */

    function initApplyJob() {
        // Open apply modal
        $(document).on('click', '.cj-apply-btn, [data-action="apply-job"]', function (e) {
            e.preventDefault();
            var $btn  = $(this);
            var jobId = $btn.data('job-id') || $btn.closest('[data-job-id]').data('job-id');

            // Populate hidden field in modal
            $('#cj-apply-modal').find('[name="job_id"], #apply_job_id').val(jobId);
            // Show job title in modal header
            var jobTitle = $btn.data('job-title') || $btn.closest('[data-job-title]').data('job-title');
            if (jobTitle) {
                $('#cj-apply-modal').find('.cj-apply-modal__job-title').text(jobTitle);
            }
            openModal('cj-apply-modal');
        });

        // Handle CV file input display
        $(document).on('change', '#apply_cv_file, [name="cv_file"]', function () {
            var file = this.files[0];
            var $label = $(this).siblings('.cj-file-label, .custom-file-label');
            if (file && $label.length) {
                $label.text(file.name);
            }
        });

        // Submit application form
        $(document).on('submit', '#cj-apply-form, .cj-apply-form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $submit = $form.find('[type="submit"]');

            if ($submit.hasClass('is-loading')) return;

            // Basic validation
            var $coverLetter = $form.find('[name="cover_letter"], #apply_cover_letter');
            if ($coverLetter.length && !$.trim($coverLetter.val())) {
                toast('Please write a cover letter before applying.', 'warning');
                $coverLetter.addClass('is-invalid').focus();
                return;
            }

            $submit.addClass('is-loading').prop('disabled', true).data('original-text', $submit.text()).text('Submitting…');

            var formData = new FormData(this);
            formData.append('action', 'civijobs_apply_job');
            formData.append('nonce',  getNonce());

            $.ajax({
                url         : getAjaxUrl(),
                type        : 'POST',
                data        : formData,
                processData : false,
                contentType : false,
                dataType    : 'json',
                success: function (res) {
                    $submit.removeClass('is-loading').prop('disabled', false).text($submit.data('original-text') || 'Apply');
                    if (res && res.success) {
                        closeModal('cj-apply-modal');
                        toast(res.data && res.data.message ? res.data.message : 'Application submitted successfully!', 'success');
                        $form[0].reset();

                        // Update apply button state
                        var jobId = $form.find('[name="job_id"]').val();
                        $('[data-action="apply-job"][data-job-id="' + jobId + '"]')
                            .addClass('is-applied').prop('disabled', true).text('Applied');
                    } else {
                        var errMsg = res && res.data && res.data.message ? res.data.message : 'Application failed. Please try again.';
                        $form.find('.cj-apply-error, .cj-form-error').text(errMsg).show();
                        toast(errMsg, 'error');
                    }
                },
                error: function () {
                    $submit.removeClass('is-loading').prop('disabled', false).text($submit.data('original-text') || 'Apply');
                    toast('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    /* =========================================================
       5. DELETE JOB (employer)
    ========================================================= */

    function initDeleteJob() {
        $(document).on('click', '.cj-delete-job-btn, [data-action="delete-job"]', function (e) {
            e.preventDefault();
            var $btn  = $(this);
            var jobId = $btn.data('job-id') || $btn.closest('[data-job-id]').data('job-id');

            if (!jobId) return;

            var jobTitle = $btn.data('job-title') || 'this job';
            if (!confirm('Are you sure you want to delete "' + jobTitle + '"? This action cannot be undone.')) {
                return;
            }

            $btn.addClass('is-loading').prop('disabled', true);

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : {
                    action : 'civijobs_delete_job',
                    nonce  : getNonce(),
                    job_id : jobId
                },
                success: function (res) {
                    $btn.removeClass('is-loading').prop('disabled', false);
                    if (res && res.success) {
                        toast(res.data && res.data.message ? res.data.message : 'Job deleted.', 'success');

                        // Remove job card/row from DOM
                        var $row = $btn.closest('.cj-job-row, .cj-job-card, tr');
                        $row.slideUp(400, function () { $(this).remove(); });
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Could not delete job.', 'error');
                    }
                },
                error: function () {
                    $btn.removeClass('is-loading').prop('disabled', false);
                    toast('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    /* =========================================================
       6. FEATURED JOB TOGGLE (employer)
    ========================================================= */

    function initFeaturedJob() {
        $(document).on('click', '.cj-feature-job-btn, [data-action="toggle-featured"]', function (e) {
            e.preventDefault();
            var $btn  = $(this);
            if ($btn.hasClass('is-loading')) return;

            var jobId = $btn.data('job-id') || $btn.closest('[data-job-id]').data('job-id');
            if (!jobId) return;

            $btn.addClass('is-loading');

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : {
                    action : 'civijobs_toggle_featured_job',
                    nonce  : getNonce(),
                    job_id : jobId
                },
                success: function (res) {
                    $btn.removeClass('is-loading');
                    if (res && res.success) {
                        var isFeatured = res.data && res.data.featured;
                        $btn.toggleClass('is-featured', isFeatured);
                        $btn.attr('title', isFeatured ? 'Remove from featured' : 'Set as featured');

                        // Update featured badge on card
                        var $card = $btn.closest('.cj-job-card, .cj-job-row, tr');
                        $card.find('.cj-featured-badge').toggleClass('is-visible', isFeatured);

                        toast(isFeatured ? 'Job marked as featured.' : 'Job removed from featured.', 'success');
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Could not update featured status.', 'error');
                    }
                },
                error: function () {
                    $btn.removeClass('is-loading');
                    toast('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    /* =========================================================
       7. DOWNLOAD CV (employer)
    ========================================================= */

    function initDownloadCV() {
        $(document).on('click', '.cj-download-cv-btn, [data-action="download-cv"]', function (e) {
            e.preventDefault();
            var $btn      = $(this);
            var cvUrl     = $btn.data('cv-url') || $btn.attr('href');
            var fileName  = $btn.data('filename') || 'cv.pdf';

            if (!cvUrl) { toast('CV file not available.', 'warning'); return; }

            // Create temporary anchor for download
            var $a = $('<a>')
                .attr({ href: cvUrl, download: fileName, target: '_blank' })
                .css({ position: 'fixed', top: '-9999px', left: '-9999px' });
            $('body').append($a);
            $a[0].click();
            $a.remove();
        });
    }

    /* =========================================================
       8. SCHEDULE MEETING
    ========================================================= */

    function initScheduleMeeting() {
        // Open meeting modal
        $(document).on('click', '.cj-schedule-meeting-btn, [data-action="schedule-meeting"]', function (e) {
            e.preventDefault();
            var $btn      = $(this);
            var appId     = $btn.data('application-id') || $btn.closest('[data-application-id]').data('application-id');
            var candidateName = $btn.data('candidate-name') || '';

            $('#cj-meeting-modal').find('[name="application_id"], #meeting_application_id').val(appId);
            if (candidateName) {
                $('#cj-meeting-modal').find('.cj-meeting-modal__candidate').text(candidateName);
            }

            // Set minimum date to today
            var today = new Date().toISOString().split('T')[0];
            $('#cj-meeting-modal').find('[name="meeting_date"], #meeting_date').attr('min', today);

            openModal('cj-meeting-modal');
        });

        // Submit meeting form
        $(document).on('submit', '#cj-meeting-form, .cj-meeting-form', function (e) {
            e.preventDefault();
            var $form   = $(this);
            var $submit = $form.find('[type="submit"]');

            if ($submit.hasClass('is-loading')) return;

            // Validate date/time
            var date = $form.find('[name="meeting_date"]').val();
            var time = $form.find('[name="meeting_time"]').val();
            if (!date || !time) {
                toast('Please select a meeting date and time.', 'warning');
                return;
            }

            $submit.addClass('is-loading').prop('disabled', true).data('orig', $submit.text()).text('Scheduling…');

            var formData = $form.serialize() + '&action=civijobs_schedule_meeting&nonce=' + getNonce();

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : formData,
                success: function (res) {
                    $submit.removeClass('is-loading').prop('disabled', false).text($submit.data('orig') || 'Schedule');
                    if (res && res.success) {
                        closeModal('cj-meeting-modal');
                        toast(res.data && res.data.message ? res.data.message : 'Meeting scheduled!', 'success');
                        $form[0].reset();

                        // Refresh meeting list if on meetings tab
                        if ($('.cj-meetings-list').length) {
                            reloadMeetingsList();
                        }
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Failed to schedule meeting.', 'error');
                    }
                },
                error: function () {
                    $submit.removeClass('is-loading').prop('disabled', false).text($submit.data('orig') || 'Schedule');
                    toast('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    /* =========================================================
       9. MEETING STATUS UPDATE (confirm / cancel)
    ========================================================= */

    function initMeetingStatus() {
        $(document).on('click', '[data-action="confirm-meeting"], [data-action="cancel-meeting"]', function (e) {
            e.preventDefault();
            var $btn      = $(this);
            if ($btn.hasClass('is-loading')) return;

            var meetingId = $btn.data('meeting-id') || $btn.closest('[data-meeting-id]').data('meeting-id');
            var action    = $btn.data('action');
            var status    = (action === 'confirm-meeting') ? 'confirmed' : 'cancelled';

            if (status === 'cancelled') {
                if (!confirm('Are you sure you want to cancel this meeting?')) return;
            }

            $btn.addClass('is-loading').prop('disabled', true);

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : {
                    action     : 'civijobs_update_meeting_status',
                    nonce      : getNonce(),
                    meeting_id : meetingId,
                    status     : status
                },
                success: function (res) {
                    $btn.removeClass('is-loading').prop('disabled', false);
                    if (res && res.success) {
                        toast(res.data && res.data.message ? res.data.message : 'Meeting ' + status + '.', 'success');

                        // Update UI
                        var $row = $btn.closest('.cj-meeting-row, .cj-meeting-card, tr');
                        $row.find('.cj-meeting-status').text(status).attr('data-status', status);

                        if (status === 'cancelled') {
                            $row.addClass('is-cancelled');
                            $btn.closest('.cj-meeting-actions').find('[data-action]').prop('disabled', true);
                        } else if (status === 'confirmed') {
                            $row.addClass('is-confirmed');
                            $btn.closest('.cj-meeting-actions').find('[data-action="confirm-meeting"]').prop('disabled', true);
                        }
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Could not update meeting.', 'error');
                    }
                },
                error: function () {
                    $btn.removeClass('is-loading').prop('disabled', false);
                    toast('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    function reloadMeetingsList() {
        var $list = $('.cj-meetings-list');
        if (!$list.length) return;
        $list.addClass('is-loading');
        $.ajax({
            url      : getAjaxUrl(),
            type     : 'POST',
            dataType : 'json',
            data     : { action: 'civijobs_get_meetings', nonce: getNonce() },
            success: function (res) {
                $list.removeClass('is-loading');
                if (res && res.success && res.data && res.data.html) {
                    $list.html(res.data.html);
                }
            },
            error: function () { $list.removeClass('is-loading'); }
        });
    }

    /* =========================================================
       10. JOB ALERT SUBSCRIBE
    ========================================================= */

    function initJobAlerts() {
        $(document).on('submit', '#cj-job-alert-form, .cj-job-alert-form', function (e) {
            e.preventDefault();
            var $form   = $(this);
            var $submit = $form.find('[type="submit"]');

            if ($submit.hasClass('is-loading')) return;

            $submit.addClass('is-loading').prop('disabled', true).data('orig', $submit.text()).text('Subscribing…');

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : $form.serialize() + '&action=civijobs_subscribe_job_alert&nonce=' + getNonce(),
                success: function (res) {
                    $submit.removeClass('is-loading').prop('disabled', false).text($submit.data('orig') || 'Subscribe');
                    if (res && res.success) {
                        toast(res.data && res.data.message ? res.data.message : 'Job alert created!', 'success');
                        $form[0].reset();
                        closeModal('cj-job-alert-modal');
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Could not create job alert.', 'error');
                    }
                },
                error: function () {
                    $submit.removeClass('is-loading').prop('disabled', false).text($submit.data('orig') || 'Subscribe');
                    toast('Connection error. Please try again.', 'error');
                }
            });
        });

        // Delete job alert
        $(document).on('click', '[data-action="delete-job-alert"]', function (e) {
            e.preventDefault();
            var $btn     = $(this);
            var alertId  = $btn.data('alert-id');
            if (!alertId || !confirm('Delete this job alert?')) return;

            $btn.addClass('is-loading');
            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : { action: 'civijobs_delete_job_alert', nonce: getNonce(), alert_id: alertId },
                success: function (res) {
                    $btn.removeClass('is-loading');
                    if (res && res.success) {
                        $btn.closest('.cj-alert-row, .cj-alert-card, tr').slideUp(300, function () { $(this).remove(); });
                        toast('Job alert deleted.', 'success');
                    } else {
                        toast('Could not delete alert.', 'error');
                    }
                },
                error: function () {
                    $btn.removeClass('is-loading');
                    toast('Connection error.', 'error');
                }
            });
        });
    }

    /* =========================================================
       11. WALLET RECHARGE MODAL
    ========================================================= */

    function initWalletRecharge() {
        $(document).on('click', '.cj-recharge-wallet-btn, [data-action="recharge-wallet"]', function (e) {
            e.preventDefault();
            openModal('cj-wallet-modal');
        });

        $(document).on('submit', '#cj-wallet-form, .cj-wallet-form', function (e) {
            e.preventDefault();
            var $form   = $(this);
            var $submit = $form.find('[type="submit"]');
            var amount  = $form.find('[name="amount"]').val();

            if (!amount || parseFloat(amount) <= 0) {
                toast('Please enter a valid amount.', 'warning');
                return;
            }

            $submit.addClass('is-loading').prop('disabled', true).data('orig', $submit.text()).text('Processing…');

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : $form.serialize() + '&action=civijobs_recharge_wallet&nonce=' + getNonce(),
                success: function (res) {
                    $submit.removeClass('is-loading').prop('disabled', false).text($submit.data('orig') || 'Recharge');
                    if (res && res.success) {
                        if (res.data && res.data.redirect_url) {
                            window.location.href = res.data.redirect_url;
                        } else {
                            closeModal('cj-wallet-modal');
                            toast(res.data && res.data.message ? res.data.message : 'Wallet recharged!', 'success');

                            // Update wallet balance display
                            if (res.data && res.data.new_balance !== undefined) {
                                $('.cj-wallet-balance').text(res.data.currency_symbol + res.data.new_balance);
                            }
                        }
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Payment failed.', 'error');
                    }
                },
                error: function () {
                    $submit.removeClass('is-loading').prop('disabled', false).text($submit.data('orig') || 'Recharge');
                    toast('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    /* =========================================================
       12. PACKAGE PURCHASE (WooCommerce redirect)
    ========================================================= */

    function initPackagePurchase() {
        $(document).on('click', '.cj-buy-package-btn, [data-action="buy-package"]', function (e) {
            e.preventDefault();
            var $btn      = $(this);
            var packageId = $btn.data('package-id');
            var productId = $btn.data('product-id');

            if (!packageId && !productId) {
                toast('Invalid package.', 'error');
                return;
            }

            $btn.addClass('is-loading').prop('disabled', true).data('orig', $btn.text()).text('Please wait…');

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : {
                    action     : 'civijobs_get_package_checkout_url',
                    nonce      : getNonce(),
                    package_id : packageId,
                    product_id : productId
                },
                success: function (res) {
                    $btn.removeClass('is-loading').prop('disabled', false).text($btn.data('orig') || 'Buy');
                    if (res && res.success && res.data && res.data.checkout_url) {
                        window.location.href = res.data.checkout_url;
                    } else {
                        toast(res && res.data && res.data.message ? res.data.message : 'Could not start checkout.', 'error');
                    }
                },
                error: function () {
                    $btn.removeClass('is-loading').prop('disabled', false).text($btn.data('orig') || 'Buy');
                    toast('Connection error.', 'error');
                }
            });
        });
    }

    /* =========================================================
       13. CHART.JS ANALYTICS (employer dashboard)
    ========================================================= */

    function initDashboardCharts() {
        if (typeof Chart === 'undefined') return;

        // Applications Over Time
        var $appChart = $('#cj-applications-chart');
        if ($appChart.length) {
            var appData;
            try {
                appData = JSON.parse($appChart.attr('data-chart') || 'null');
            } catch (e) { appData = null; }

            if (appData) {
                var appCtx = $appChart[0].getContext('2d');
                new Chart(appCtx, {
                    type: 'line',
                    data: {
                        labels  : appData.labels || [],
                        datasets: [{
                            label           : 'Applications',
                            data            : appData.values || [],
                            borderColor     : '#2563eb',
                            backgroundColor : 'rgba(37,99,235,0.08)',
                            borderWidth     : 2,
                            pointRadius     : 4,
                            pointBackgroundColor: '#2563eb',
                            fill            : true,
                            tension         : 0.4
                        }]
                    },
                    options: {
                        responsive         : true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        scales: {
                            x: {
                                grid  : { display: false },
                                ticks : { color: '#6b7280' }
                            },
                            y: {
                                beginAtZero: true,
                                ticks       : { stepSize: 1, color: '#6b7280' },
                                grid        : { color: 'rgba(107,114,128,0.1)' }
                            }
                        }
                    }
                });
            }
        }

        // Job Views Chart
        var $viewsChart = $('#cj-views-chart');
        if ($viewsChart.length) {
            var viewsData;
            try {
                viewsData = JSON.parse($viewsChart.attr('data-chart') || 'null');
            } catch (e) { viewsData = null; }

            if (viewsData) {
                var viewsCtx = $viewsChart[0].getContext('2d');
                new Chart(viewsCtx, {
                    type: 'bar',
                    data: {
                        labels  : viewsData.labels || [],
                        datasets: [{
                            label           : 'Views',
                            data            : viewsData.values || [],
                            backgroundColor : 'rgba(16,185,129,0.8)',
                            borderColor     : '#10b981',
                            borderWidth     : 1,
                            borderRadius    : 4
                        }]
                    },
                    options: {
                        responsive         : true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        scales: {
                            x: {
                                grid  : { display: false },
                                ticks : { color: '#6b7280' }
                            },
                            y: {
                                beginAtZero: true,
                                ticks       : { color: '#6b7280' },
                                grid        : { color: 'rgba(107,114,128,0.1)' }
                            }
                        }
                    }
                });
            }
        }

        // Job-level stats (per job doughnut - e.g. application sources)
        $('.cj-job-stats-chart').each(function () {
            var $canvas = $(this);
            var statsData;
            try {
                statsData = JSON.parse($canvas.attr('data-chart') || 'null');
            } catch (e) { statsData = null; }

            if (!statsData) return;

            var ctx = this.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels  : statsData.labels || [],
                    datasets: [{
                        data           : statsData.values || [],
                        backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                        borderWidth    : 0,
                        hoverOffset    : 4
                    }]
                },
                options: {
                    responsive         : true,
                    maintainAspectRatio: false,
                    cutout             : '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 16, color: '#374151' } }
                    }
                }
            });
        });

        // Load Chart.js data via AJAX if not embedded
        if (!$appChart.attr('data-chart') && $appChart.length) {
            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : { action: 'civijobs_get_dashboard_stats', nonce: getNonce() },
                success: function (res) {
                    if (res && res.success && res.data) {
                        if (res.data.applications) {
                            $appChart.attr('data-chart', JSON.stringify(res.data.applications));
                        }
                        if (res.data.views) {
                            $viewsChart.attr('data-chart', JSON.stringify(res.data.views));
                        }
                        // Recurse now that data is set
                        initDashboardCharts();
                    }
                }
            });
        }
    }

    /* =========================================================
       INITIALIZE
    ========================================================= */

    $(function () {
        // Only run on dashboard pages
        if (!$('.cj-dashboard, #cj-dashboard, .cj-employer-dashboard, .cj-candidate-dashboard').length) return;

        initSidebarCollapse();
        initApplicationStatus();
        initSaveJob();
        initApplyJob();
        initDeleteJob();
        initFeaturedJob();
        initDownloadCV();
        initScheduleMeeting();
        initMeetingStatus();
        initJobAlerts();
        initWalletRecharge();
        initPackagePurchase();
        initDashboardCharts();
    });

    // Also init save-job on non-dashboard pages (job listing pages)
    $(function () {
        initSaveJob();
    });

}(jQuery));
