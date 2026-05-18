/**
 * CiviJobs - Main JavaScript
 * Core UI interactions: menu, modals, tabs, scroll, alerts, toasts, forms, maps
 */
(function ($) {
    'use strict';

    /* =========================================================
       UTILITY
    ========================================================= */

    var CiviJobs = window.CiviJobs = window.CiviJobs || {};

    /**
     * Debounce helper
     */
    function debounce(fn, delay) {
        var timer;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(ctx, args);
            }, delay);
        };
    }

    /* =========================================================
       MOBILE MENU TOGGLE
    ========================================================= */

    function initMobileMenu() {
        var $toggle = $('.cj-hamburger, .mobile-menu-toggle, [data-toggle="mobile-menu"]');
        var $nav    = $('.cj-nav, .site-nav, .main-navigation');

        $toggle.on('click', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $target = $nav;

            // Allow explicit target override
            var targetSel = $btn.data('target');
            if (targetSel) {
                $target = $(targetSel);
            }

            var isOpen = $btn.hasClass('is-active');

            $btn.toggleClass('is-active').attr('aria-expanded', !isOpen);
            $target.toggleClass('is-open');
            $('body').toggleClass('mobile-menu-open', !isOpen);
        });

        // Close on outside click
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.cj-hamburger, .mobile-menu-toggle, [data-toggle="mobile-menu"], .cj-nav, .site-nav, .main-navigation').length) {
                $('.cj-hamburger, .mobile-menu-toggle, [data-toggle="mobile-menu"]').removeClass('is-active').attr('aria-expanded', false);
                $('.cj-nav, .site-nav, .main-navigation').removeClass('is-open');
                $('body').removeClass('mobile-menu-open');
            }
        });

        // Close on ESC
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                $('.cj-hamburger, .mobile-menu-toggle, [data-toggle="mobile-menu"]').removeClass('is-active').attr('aria-expanded', false);
                $('.cj-nav, .site-nav, .main-navigation').removeClass('is-open');
                $('body').removeClass('mobile-menu-open');
            }
        });
    }

    /* =========================================================
       DROPDOWN MENUS
    ========================================================= */

    function initDropdowns() {
        var $items = $('.menu-item-has-children, .cj-dropdown');
        var hoverDelay = 200;
        var closeTimer;

        // Desktop: hover open
        if (window.matchMedia('(hover: hover)').matches) {
            $items.on('mouseenter', function () {
                clearTimeout(closeTimer);
                var $el = $(this);
                $items.not($el).removeClass('dropdown-open').find('.sub-menu, .cj-dropdown-menu').stop(true, true).slideUp(150);
                $el.addClass('dropdown-open').find('> .sub-menu, > .cj-dropdown-menu').stop(true, true).slideDown(150);
            });

            $items.on('mouseleave', function () {
                var $el = $(this);
                closeTimer = setTimeout(function () {
                    $el.removeClass('dropdown-open').find('> .sub-menu, > .cj-dropdown-menu').stop(true, true).slideUp(150);
                }, hoverDelay);
            });
        }

        // Click toggle (also for touch/mobile)
        $items.find('> a, > .dropdown-toggle').on('click', function (e) {
            var $parent = $(this).parent();
            var $sub    = $parent.find('> .sub-menu, > .cj-dropdown-menu');

            if ($sub.length) {
                if (window.matchMedia('(max-width: 991px)').matches || !window.matchMedia('(hover: hover)').matches) {
                    e.preventDefault();
                    var isOpen = $parent.hasClass('dropdown-open');
                    $items.not($parent).removeClass('dropdown-open').find('.sub-menu, .cj-dropdown-menu').slideUp(150);
                    $parent.toggleClass('dropdown-open', !isOpen);
                    $sub.stop(true, true).slideToggle(150);
                }
            }
        });

        // Close dropdowns on outside click
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.menu-item-has-children, .cj-dropdown').length) {
                $items.removeClass('dropdown-open').find('.sub-menu, .cj-dropdown-menu').slideUp(150);
            }
        });
    }

    /* =========================================================
       MODAL SYSTEM
    ========================================================= */

    function initModals() {
        // Open via data-modal-target="modal-id"
        $(document).on('click', '[data-modal-target]', function (e) {
            e.preventDefault();
            var id = $(this).data('modal-target');
            openModal(id);
        });

        // Close via .cj-modal-close or clicking overlay
        $(document).on('click', '.cj-modal-close, [data-modal-close]', function (e) {
            e.preventDefault();
            var $modal = $(this).closest('.cj-modal');
            closeModal($modal.attr('id'));
        });

        $(document).on('click', '.cj-modal-overlay', function (e) {
            if ($(e.target).hasClass('cj-modal-overlay') || $(e.target).hasClass('cj-modal')) {
                var $modal = $(this).closest('.cj-modal');
                if (!$modal.length) {
                    $modal = $(e.target).closest('.cj-modal');
                }
                closeModal($modal.attr('id'));
            }
        });

        // ESC to close topmost modal
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                var $open = $('.cj-modal.is-open');
                if ($open.length) {
                    closeModal($open.last().attr('id'));
                }
            }
        });
    }

    function openModal(id) {
        var $modal = id ? $('#' + id) : null;
        if (!$modal || !$modal.length) return;

        $modal.addClass('is-open').attr('aria-hidden', false);
        $('body').addClass('modal-open');

        // Focus first focusable element
        var $focusable = $modal.find('input, button, select, textarea, a[href], [tabindex]:not([tabindex="-1"])').filter(':visible').first();
        if ($focusable.length) {
            setTimeout(function () { $focusable.focus(); }, 50);
        }

        $(document).trigger('civijobs:modal:open', [id, $modal]);
    }

    function closeModal(id) {
        var $modal = id ? $('#' + id) : $('.cj-modal.is-open').last();
        if (!$modal || !$modal.length) return;

        $modal.removeClass('is-open').attr('aria-hidden', true);

        // Remove modal-open if no more modals
        if (!$('.cj-modal.is-open').length) {
            $('body').removeClass('modal-open');
        }

        $(document).trigger('civijobs:modal:close', [id, $modal]);
    }

    CiviJobs.openModal  = openModal;
    CiviJobs.closeModal = closeModal;

    /* =========================================================
       STICKY HEADER
    ========================================================= */

    function initStickyHeader() {
        var $header     = $('.cj-header, .site-header, #masthead');
        var $body       = $('body');
        var threshold   = 80;
        var lastScrollY = 0;

        if (!$header.length) return;

        var headerHeight = $header.outerHeight();
        $body.css('padding-top', $header.hasClass('cj-header--fixed') ? headerHeight : 0);

        $(window).on('scroll.stickyHeader', debounce(function () {
            var scrollY = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollY > threshold) {
                $header.addClass('is-sticky');
            } else {
                $header.removeClass('is-sticky');
            }

            // Hide on scroll down, show on scroll up (smart hide)
            if (scrollY > lastScrollY && scrollY > headerHeight * 2) {
                $header.addClass('is-hidden');
            } else {
                $header.removeClass('is-hidden');
            }

            lastScrollY = scrollY;
        }, 10));
    }

    /* =========================================================
       TAB SWITCHING
    ========================================================= */

    function initTabs() {
        $(document).on('click', '.cj-tabs .cj-tab-nav a, .tabs .tab-nav a, [data-tab-target]', function (e) {
            e.preventDefault();
            var $link       = $(this);
            var $tabsWrap   = $link.closest('.cj-tabs, .tabs');
            var target      = $link.data('tab-target') || $link.attr('href');

            if (!target) return;

            // Deactivate all
            $tabsWrap.find('.cj-tab-nav a, .tab-nav a, [data-tab-target]').removeClass('active').attr('aria-selected', false);
            $tabsWrap.find('.cj-tab-panel, .tab-panel').removeClass('active').attr('hidden', true);

            // Activate clicked
            $link.addClass('active').attr('aria-selected', true);
            var $panel = $tabsWrap.find(target);
            if (!$panel.length) {
                $panel = $(target);
            }
            $panel.addClass('active').removeAttr('hidden');

            // Save to localStorage if tab group has ID
            var groupId = $tabsWrap.attr('id');
            if (groupId) {
                try { localStorage.setItem('cj_tab_' + groupId, target); } catch (err) {}
            }

            $(document).trigger('civijobs:tab:change', [target, $panel, $tabsWrap]);
        });

        // Restore saved tab states
        $('.cj-tabs[id], .tabs[id]').each(function () {
            var $wrap = $(this);
            var groupId = $wrap.attr('id');
            var saved;
            try { saved = localStorage.getItem('cj_tab_' + groupId); } catch (err) {}
            if (saved) {
                $wrap.find('[data-tab-target="' + saved + '"], [href="' + saved + '"]').first().trigger('click');
            } else {
                // Default activate first
                $wrap.find('.cj-tab-nav a:first, .tab-nav a:first, [data-tab-target]:first').first().trigger('click');
            }
        });
    }

    /* =========================================================
       SMOOTH SCROLL
    ========================================================= */

    function initSmoothScroll() {
        $(document).on('click', 'a[href^="#"]', function (e) {
            var href   = $(this).attr('href');
            var $target;

            // Skip modals, tabs, carousels
            if ($(this).is('[data-modal-target], [data-tab-target], [data-toggle], [data-slide]')) return;
            if (href === '#' || href === '#!') return;

            try { $target = $(href); } catch (err) { return; }
            if (!$target.length) return;

            e.preventDefault();

            var headerOffset = $('.cj-header.is-sticky, .site-header.is-sticky, #masthead.is-sticky').outerHeight() || 0;
            var offset       = $target.offset().top - headerOffset - 20;

            $('html, body').animate({ scrollTop: offset }, 500, 'swing');
        });
    }

    /* =========================================================
       ALERT DISMISSAL
    ========================================================= */

    function initAlerts() {
        $(document).on('click', '.cj-alert .cj-alert-close, .alert .alert-close, [data-dismiss="alert"]', function () {
            var $alert = $(this).closest('.cj-alert, .alert');
            $alert.slideUp(300, function () { $(this).remove(); });
        });
    }

    /* =========================================================
       BACK TO TOP BUTTON
    ========================================================= */

    function initBackToTop() {
        var $btn = $('.cj-back-to-top, #back-to-top, .back-to-top');

        if (!$btn.length) {
            $btn = $('<button class="cj-back-to-top" aria-label="Back to top" title="Back to top">' +
                     '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                     '<path d="M10 4L3 11H7V16H13V11H17L10 4Z" fill="currentColor"/></svg>' +
                     '</button>');
            $('body').append($btn);
        }

        $(window).on('scroll.backToTop', debounce(function () {
            if (window.pageYOffset > 400) {
                $btn.addClass('is-visible');
            } else {
                $btn.removeClass('is-visible');
            }
        }, 100));

        $btn.on('click', function () {
            $('html, body').animate({ scrollTop: 0 }, 500);
        });
    }

    /* =========================================================
       VIEW TOGGLE (Grid / List) for job listings
    ========================================================= */

    function initViewToggle() {
        var STORAGE_KEY = 'cj_listing_view';
        var $toggles    = $('.cj-view-toggle [data-view], [data-view-toggle]');
        var $listings   = $('.cj-jobs-list, .cj-listing-grid, .jobs-listing-container');

        function applyView(view) {
            $listings.attr('data-view', view).removeClass('view-grid view-list').addClass('view-' + view);
            $toggles.removeClass('active').filter('[data-view="' + view + '"]').addClass('active');
            try { localStorage.setItem(STORAGE_KEY, view); } catch (err) {}
        }

        $toggles.on('click', function () {
            var view = $(this).data('view') || $(this).data('view-toggle');
            if (view) applyView(view);
        });

        // Restore preference
        var saved;
        try { saved = localStorage.getItem(STORAGE_KEY); } catch (err) {}
        if (saved && $listings.length) {
            applyView(saved);
        }
    }

    /* =========================================================
       STAR RATING PICKER
    ========================================================= */

    function initStarRating() {
        $(document).on('mouseenter', '.cj-star-rating .cj-star', function () {
            var $star  = $(this);
            var $group = $star.closest('.cj-star-rating');
            var val    = parseInt($star.data('value'), 10);

            $group.find('.cj-star').each(function () {
                $(this).toggleClass('is-highlighted', parseInt($(this).data('value'), 10) <= val);
            });
        });

        $(document).on('mouseleave', '.cj-star-rating', function () {
            var $group   = $(this);
            var selected = parseInt($group.find('input[type="hidden"]').val() || 0, 10);

            $group.find('.cj-star').each(function () {
                $(this).toggleClass('is-highlighted', false)
                       .toggleClass('is-selected', parseInt($(this).data('value'), 10) <= selected);
            });
        });

        $(document).on('click', '.cj-star-rating .cj-star', function () {
            var $star  = $(this);
            var $group = $star.closest('.cj-star-rating');
            var val    = parseInt($star.data('value'), 10);
            var $input = $group.find('input[type="hidden"]');

            $input.val(val).trigger('change');

            $group.find('.cj-star').each(function () {
                $(this).toggleClass('is-selected', parseInt($(this).data('value'), 10) <= val)
                       .toggleClass('is-highlighted', false);
            });

            $(document).trigger('civijobs:rating:change', [val, $group]);
        });
    }

    /* =========================================================
       FORM VALIDATION HELPERS
    ========================================================= */

    function initFormValidation() {
        // Mark field invalid on blur if empty and required
        $(document).on('blur', '.cj-form input[required], .cj-form select[required], .cj-form textarea[required]', function () {
            validateField($(this));
        });

        $(document).on('input change', '.cj-form input, .cj-form select, .cj-form textarea', function () {
            var $field = $(this);
            if ($field.hasClass('is-invalid')) {
                validateField($field);
            }
        });

        // Validate all on submit
        $(document).on('submit', '.cj-form', function (e) {
            var $form    = $(this);
            var valid    = true;
            var $fields  = $form.find('input[required], select[required], textarea[required]');

            $fields.each(function () {
                if (!validateField($(this))) {
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
                var $first = $form.find('.is-invalid').first();
                if ($first.length) {
                    $('html, body').animate({ scrollTop: $first.offset().top - 100 }, 300);
                    $first.focus();
                }
            }
        });
    }

    function validateField($field) {
        var val   = $.trim($field.val());
        var type  = $field.attr('type');
        var valid = true;
        var msg   = '';

        if ($field.prop('required') && !val) {
            valid = false;
            msg   = $field.data('error-required') || 'This field is required.';
        } else if (type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            valid = false;
            msg   = $field.data('error-email') || 'Please enter a valid email address.';
        } else if (type === 'url' && val && !/^https?:\/\/.+/.test(val)) {
            valid = false;
            msg   = $field.data('error-url') || 'Please enter a valid URL (starting with http:// or https://).';
        } else if ($field.attr('minlength') && val.length < parseInt($field.attr('minlength'), 10)) {
            valid = false;
            msg   = $field.data('error-minlength') || 'Minimum ' + $field.attr('minlength') + ' characters required.';
        } else if ($field.attr('maxlength') && val.length > parseInt($field.attr('maxlength'), 10)) {
            valid = false;
            msg   = 'Maximum ' + $field.attr('maxlength') + ' characters allowed.';
        }

        // Pattern validation
        if (valid && $field.attr('pattern') && val) {
            var re = new RegExp('^(?:' + $field.attr('pattern') + ')$');
            if (!re.test(val)) {
                valid = false;
                msg   = $field.data('error-pattern') || 'Please match the required format.';
            }
        }

        $field.toggleClass('is-invalid', !valid).toggleClass('is-valid', valid && val.length > 0);

        var $group = $field.closest('.cj-form-group, .form-group');
        $group.find('.cj-field-error, .field-error').remove();
        if (!valid) {
            $group.append('<span class="cj-field-error">' + msg + '</span>');
        }

        return valid;
    }

    CiviJobs.validateField = validateField;

    /* =========================================================
       TOAST NOTIFICATION SYSTEM
    ========================================================= */

    var $toastContainer;

    function getToastContainer() {
        if (!$toastContainer || !$toastContainer.length) {
            $toastContainer = $('#cj-toast-container');
            if (!$toastContainer.length) {
                $toastContainer = $('<div id="cj-toast-container" aria-live="polite" aria-atomic="false"></div>');
                $('body').append($toastContainer);
            }
        }
        return $toastContainer;
    }

    /**
     * Show a toast message
     * @param {string} message  - Message text (may contain HTML)
     * @param {string} type     - 'success' | 'error' | 'warning' | 'info'
     * @param {number} duration - ms before auto-dismiss (0 = sticky). Default 4000.
     */
    function toast(message, type, duration) {
        type     = type     || 'info';
        duration = (duration === undefined) ? 4000 : duration;

        var icons = {
            success : '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 011.414-1.414L8.414 12.172l6.879-6.879a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>',
            error   : '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
            warning : '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            info    : '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
        };

        var $toast = $(
            '<div class="cj-toast cj-toast--' + type + '" role="alert" aria-live="assertive">' +
            '<span class="cj-toast__icon">' + (icons[type] || icons.info) + '</span>' +
            '<span class="cj-toast__msg">' + message + '</span>' +
            '<button class="cj-toast__close" aria-label="Dismiss">&times;</button>' +
            '</div>'
        );

        getToastContainer().append($toast);

        // Animate in
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                $toast.addClass('cj-toast--show');
            });
        });

        var dismiss = function () {
            $toast.removeClass('cj-toast--show');
            setTimeout(function () { $toast.remove(); }, 350);
        };

        $toast.find('.cj-toast__close').on('click', dismiss);

        if (duration > 0) {
            setTimeout(dismiss, duration);
        }

        return $toast;
    }

    CiviJobs.toast = toast;

    /* =========================================================
       IMAGE LAZY LOADING
    ========================================================= */

    function initLazyLoad() {
        // Use native lazy loading where possible, polyfill for older browsers
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        var src = img.dataset.src;
                        if (src) {
                            img.src = src;
                            img.removeAttribute('data-src');
                        }
                        var srcset = img.dataset.srcset;
                        if (srcset) {
                            img.srcset = srcset;
                            img.removeAttribute('data-srcset');
                        }
                        img.classList.remove('cj-lazy');
                        img.classList.add('cj-lazy--loaded');
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: '200px 0px' });

            $('img[data-src], img.cj-lazy').each(function () {
                observer.observe(this);
            });
        } else {
            // Fallback: load all
            $('img[data-src]').each(function () {
                var $img = $(this);
                if ($img.data('src'))    $img.attr('src',    $img.data('src'));
                if ($img.data('srcset')) $img.attr('srcset', $img.data('srcset'));
            });
        }
    }

    /* =========================================================
       LEAFLET MAP INITIALIZATION
    ========================================================= */

    function initLeafletMap() {
        var $maps = $('.leaflet-map');
        if (!$maps.length) return;
        if (typeof L === 'undefined') {
            console.warn('CiviJobs: Leaflet.js not loaded. Cannot initialize .leaflet-map elements.');
            return;
        }

        $maps.each(function () {
            var $el      = $(this);
            var lat      = parseFloat($el.data('lat'))  || 51.505;
            var lng      = parseFloat($el.data('lng'))  || -0.09;
            var zoom     = parseInt($el.data('zoom'), 10) || 12;
            var markersData;

            try {
                markersData = JSON.parse($el.attr('data-markers') || '[]');
            } catch (err) {
                markersData = [];
            }

            var map = L.map(this, { scrollWheelZoom: false }).setView([lat, lng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            // Add markers from data attribute
            if (markersData.length) {
                var bounds = [];
                markersData.forEach(function (m) {
                    if (!m.lat || !m.lng) return;
                    var marker = L.marker([m.lat, m.lng]).addTo(map);
                    if (m.popup) {
                        marker.bindPopup(m.popup);
                    } else if (m.title) {
                        marker.bindPopup('<strong>' + m.title + '</strong>');
                    }
                    bounds.push([m.lat, m.lng]);
                });
                if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [30, 30] });
                }
            }

            // Store map instance
            $el.data('leaflet-map', map);
            $(document).trigger('civijobs:map:init', [map, $el]);
        });
    }

    /* =========================================================
       INITIALIZE
    ========================================================= */

    $(function () {
        initMobileMenu();
        initDropdowns();
        initModals();
        initStickyHeader();
        initTabs();
        initSmoothScroll();
        initAlerts();
        initBackToTop();
        initViewToggle();
        initStarRating();
        initFormValidation();
        initLazyLoad();
        initLeafletMap();

        $(document).trigger('civijobs:ready');
    });

    // Re-run lazy load on dynamic content
    $(document).on('civijobs:content:updated', function () {
        initLazyLoad();
    });

}(jQuery));
