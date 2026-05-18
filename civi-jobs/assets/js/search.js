/**
 * CiviJobs - Advanced Search & Filter
 * Real-time search, AJAX filtering, URL state, salary slider,
 * location autocomplete, sort, active filter chips, infinite scroll / load more,
 * and map view sync.
 */
(function ($) {
    'use strict';

    /* =========================================================
       UTILITY
    ========================================================= */

    function debounce(fn, delay) {
        var timer;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
        };
    }

    function getAjaxUrl() {
        return (typeof civijobs_ajax !== 'undefined' && civijobs_ajax.ajaxurl) ? civijobs_ajax.ajaxurl : '/wp-admin/admin-ajax.php';
    }

    function getNonce() {
        return (typeof civijobs_ajax !== 'undefined' && civijobs_ajax.nonce) ? civijobs_ajax.nonce : '';
    }

    /* =========================================================
       STATE
    ========================================================= */

    var Search = {
        context     : 'jobs',   // jobs | companies | candidates
        filters     : {},
        page        : 1,
        perPage     : 10,
        totalPages  : 1,
        loading     : false,
        appendMode  : false,     // true when loading more
        lastQuery   : ''
    };

    // Action map for different contexts
    var ACTIONS = {
        jobs        : 'civijobs_search_jobs',
        companies   : 'civijobs_search_companies',
        candidates  : 'civijobs_search_candidates'
    };

    /* =========================================================
       DOM SELECTORS (resolved lazily)
    ========================================================= */

    function $results()       { return $('.cj-search-results, .cj-jobs-list, .cj-listing-results'); }
    function $loader()        { return $('.cj-search-loader, .cj-results-loader'); }
    function $loadMoreBtn()   { return $('.cj-load-more-btn'); }
    function $resultCount()   { return $('.cj-results-count, .search-results-count'); }
    function $activeChips()   { return $('.cj-active-filters'); }
    function $searchInput()   { return $('#cj-search-input, .cj-search-input, input[name="search_query"]').first(); }
    function $sortSelect()    { return $('#cj-sort, .cj-sort-select, select[name="sort_by"]').first(); }
    function $filterForm()    { return $('#cj-filter-form, .cj-filter-sidebar form, .cj-filter-form').first(); }

    /* =========================================================
       COLLECT CURRENT FILTERS FROM DOM
    ========================================================= */

    function collectFilters() {
        var filters = {};

        // Search keyword
        var keyword = $searchInput().val();
        if (keyword) { filters.keyword = keyword; }

        // Sort
        var sort = $sortSelect().val();
        if (sort) { filters.sort_by = sort; }

        // Context (jobs / companies / candidates)
        var ctx = $('.cj-search-context.is-active').data('context') || Search.context;
        filters.context = ctx;

        // Checkboxes: job_type, job_category, experience
        $filterForm().find('input[type="checkbox"]:checked').each(function () {
            var name = $(this).attr('name');
            var val  = $(this).val();
            if (!name) return;
            // Normalize array names like job_type[]
            name = name.replace(/\[\]$/, '');
            if (!filters[name]) { filters[name] = []; }
            filters[name].push(val);
        });

        // Salary range
        var $salaryMin = $('[name="salary_min"], #salary-min');
        var $salaryMax = $('[name="salary_max"], #salary-max');
        if ($salaryMin.length && $salaryMin.val()) { filters.salary_min = $salaryMin.val(); }
        if ($salaryMax.length && $salaryMax.val()) { filters.salary_max = $salaryMax.val(); }

        // Location
        var $locationInput = $('[name="location"], #cj-location-input');
        if ($locationInput.length && $locationInput.val()) {
            filters.location = $locationInput.val();
            var lat = $locationInput.data('lat');
            var lng = $locationInput.data('lng');
            if (lat && lng) {
                filters.lat = lat;
                filters.lng = lng;
            }
        }

        // Radius
        var $radius = $('[name="radius"], #cj-radius');
        if ($radius.length && $radius.val()) { filters.radius = $radius.val(); }

        // Radio buttons (single value)
        $filterForm().find('input[type="radio"]:checked').each(function () {
            var name = $(this).attr('name');
            if (name) { filters[name] = $(this).val(); }
        });

        // Select dropdowns inside filter form
        $filterForm().find('select').each(function () {
            var name = $(this).attr('name');
            var val  = $(this).val();
            if (name && val) { filters[name] = val; }
        });

        return filters;
    }

    /* =========================================================
       BUILD QUERY STRING & PUSH STATE
    ========================================================= */

    function filtersToQueryString(filters, page) {
        var parts = [];
        $.each(filters, function (key, val) {
            if ($.isArray(val)) {
                $.each(val, function (i, v) {
                    parts.push(encodeURIComponent(key + '[]') + '=' + encodeURIComponent(v));
                });
            } else {
                parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(val));
            }
        });
        if (page && page > 1) {
            parts.push('paged=' + page);
        }
        return parts.join('&');
    }

    function pushState(filters, page) {
        if (!window.history || !window.history.pushState) return;
        var qs = filtersToQueryString(filters, page);
        var url = window.location.pathname + (qs ? '?' + qs : '');
        history.pushState({ filters: filters, page: page }, '', url);
    }

    /* =========================================================
       PARSE URL STATE ON PAGE LOAD
    ========================================================= */

    function parseUrlState() {
        var params = {};
        var qs     = window.location.search.replace(/^\?/, '');
        if (!qs) return params;

        qs.split('&').forEach(function (part) {
            var kv  = part.split('=');
            var key = decodeURIComponent(kv[0]);
            var val = decodeURIComponent(kv[1] || '');
            // Array params
            if (key.slice(-2) === '[]') {
                key = key.slice(0, -2);
                if (!params[key]) { params[key] = []; }
                params[key].push(val);
            } else {
                params[key] = val;
            }
        });
        return params;
    }

    function applyUrlStateToForm(params) {
        if (!Object.keys(params).length) return;

        // Keyword
        if (params.keyword) { $searchInput().val(params.keyword); }

        // Sort
        if (params.sort_by) { $sortSelect().val(params.sort_by); }

        // Location
        if (params.location) { $('[name="location"], #cj-location-input').val(params.location); }

        // Salary
        if (params.salary_min) { $('[name="salary_min"], #salary-min').val(params.salary_min); }
        if (params.salary_max) { $('[name="salary_max"], #salary-max').val(params.salary_max); }

        // Radius
        if (params.radius) { $('[name="radius"], #cj-radius').val(params.radius); }

        // Checkboxes
        $.each(params, function (key, val) {
            var vals = $.isArray(val) ? val : [val];
            vals.forEach(function (v) {
                $filterForm().find('input[type="checkbox"][name="' + key + '[]"], input[type="checkbox"][name="' + key + '"]').filter('[value="' + v + '"]').prop('checked', true);
            });
        });

        // Radios
        $.each(params, function (key, val) {
            $filterForm().find('input[type="radio"][name="' + key + '"][value="' + val + '"]').prop('checked', true);
        });
    }

    /* =========================================================
       AJAX SEARCH REQUEST
    ========================================================= */

    var currentXhr = null;

    function doSearch(appendResults) {
        if (Search.loading) {
            if (currentXhr) { currentXhr.abort(); }
        }

        Search.appendMode = !!appendResults;
        if (!appendResults) { Search.page = 1; }

        var filters  = collectFilters();
        Search.filters = filters;

        var context  = filters.context || Search.context;
        var action   = ACTIONS[context] || ACTIONS.jobs;

        var data = $.extend({}, filters, {
            action   : action,
            nonce    : getNonce(),
            paged    : Search.page,
            per_page : Search.perPage
        });

        Search.loading = true;
        $loader().addClass('is-active');
        $loadMoreBtn().prop('disabled', true).text('Loading...');

        if (!appendResults) {
            $results().addClass('is-loading');
        }

        currentXhr = $.ajax({
            url      : getAjaxUrl(),
            type     : 'POST',
            dataType : 'json',
            data     : data,
            success  : function (res) {
                Search.loading = false;
                $loader().removeClass('is-active');
                $results().removeClass('is-loading');

                if (!res || !res.success) {
                    var errMsg = (res && res.data && res.data.message) ? res.data.message : 'An error occurred. Please try again.';
                    if (typeof CiviJobs !== 'undefined' && CiviJobs.toast) {
                        CiviJobs.toast(errMsg, 'error');
                    }
                    return;
                }

                var payload = res.data || {};
                Search.totalPages = parseInt(payload.total_pages, 10) || 1;
                Search.page       = parseInt(payload.current_page, 10) || 1;

                if (appendResults) {
                    $results().append(payload.html || '');
                } else {
                    $results().html(payload.html || '<p class="cj-no-results">No results found.</p>');
                }

                // Update result count
                if (payload.total_items !== undefined) {
                    $resultCount().text(payload.total_items + ' result' + (payload.total_items !== 1 ? 's' : '') + ' found');
                }

                // Update load more button
                if (Search.page >= Search.totalPages) {
                    $loadMoreBtn().hide();
                } else {
                    $loadMoreBtn().show().prop('disabled', false).text('Load More');
                }

                // Re-init lazy images in new content
                $(document).trigger('civijobs:content:updated');

                // Sync with map if map module loaded
                if (payload.map_markers) {
                    $(document).trigger('civijobs:map:update', [payload.map_markers]);
                }

                // Push URL state
                pushState(filters, Search.page);

                // Render active filter chips
                renderActiveFilters(filters);
            },
            error: function (xhr, status) {
                if (status === 'abort') return;
                Search.loading = false;
                $loader().removeClass('is-active');
                $results().removeClass('is-loading');
                $loadMoreBtn().prop('disabled', false).text('Load More');
                if (typeof CiviJobs !== 'undefined' && CiviJobs.toast) {
                    CiviJobs.toast('Search failed. Please try again.', 'error');
                }
            }
        });
    }

    /* =========================================================
       ACTIVE FILTER CHIPS
    ========================================================= */

    // Human-readable labels for filter keys
    var filterLabels = {
        keyword      : 'Search',
        job_type     : 'Job Type',
        job_category : 'Category',
        experience   : 'Experience',
        salary_min   : 'Min Salary',
        salary_max   : 'Max Salary',
        location     : 'Location',
        radius       : 'Radius',
        sort_by      : 'Sort',
        context      : null  // Hide context from chips
    };

    function renderActiveFilters(filters) {
        var $container = $activeChips();
        if (!$container.length) return;

        $container.empty();
        var hasFilters = false;

        $.each(filters, function (key, val) {
            var label = filterLabels.hasOwnProperty(key) ? filterLabels[key] : key;
            if (label === null) return; // Skip hidden keys

            var vals = $.isArray(val) ? val : [val];
            vals.forEach(function (v) {
                if (!v) return;
                hasFilters = true;
                var displayVal = v;

                // Try to find a human label from checkbox label
                var $checkbox = $filterForm().find('input[value="' + v + '"][name*="' + key + '"]');
                if ($checkbox.length) {
                    var $lbl = $checkbox.closest('label').clone();
                    $lbl.find('input').remove();
                    var lblText = $.trim($lbl.text());
                    if (lblText) { displayVal = lblText; }
                }

                var $chip = $('<span class="cj-filter-chip">' +
                    '<span class="cj-filter-chip__label">' + (label ? label + ': ' : '') + displayVal + '</span>' +
                    '<button class="cj-filter-chip__remove" aria-label="Remove filter" data-filter-key="' + key + '" data-filter-val="' + v + '">&times;</button>' +
                    '</span>');
                $container.append($chip);
            });
        });

        if (hasFilters) {
            $container.append('<button class="cj-clear-all-filters">Clear All</button>');
            $container.addClass('has-filters').show();
        } else {
            $container.removeClass('has-filters').hide();
        }
    }

    /* =========================================================
       REMOVE A SINGLE FILTER CHIP
    ========================================================= */

    function removeFilter(key, val) {
        // Handle checkboxes
        var $checkbox = $filterForm().find('input[type="checkbox"][name*="' + key + '"][value="' + val + '"]');
        if ($checkbox.length) {
            $checkbox.prop('checked', false);
            doSearch();
            return;
        }

        // Handle text / select fields
        var $field = $filterForm().find('[name="' + key + '"]');
        if (!$field.length) { $field = $('[name="' + key + '"]'); }
        if ($field.length) {
            $field.val('');
            if ($field.is('#cj-location-input, [name="location"]')) {
                $field.removeData('lat').removeData('lng').removeAttr('data-lat').removeAttr('data-lng');
            }
        }

        // Handle main search input
        if (key === 'keyword') { $searchInput().val(''); }
        if (key === 'sort_by') { $sortSelect().val(''); }

        doSearch();
    }

    /* =========================================================
       CLEAR ALL FILTERS
    ========================================================= */

    function clearAllFilters() {
        $searchInput().val('');
        $sortSelect().val('');
        $filterForm().find('input[type="checkbox"]').prop('checked', false);
        $filterForm().find('input[type="radio"]').prop('checked', false);
        $filterForm().find('select').val('');
        $filterForm().find('input[type="text"], input[type="number"]').val('');
        $('[name="location"], #cj-location-input').val('').removeData('lat').removeData('lng').removeAttr('data-lat').removeAttr('data-lng');

        // Reset salary slider
        var $salarySlider = $('#salary-range-slider');
        if ($salarySlider.length) {
            var min = $salarySlider.data('min') || 0;
            var max = $salarySlider.data('max') || 200000;
            resetSalarySlider(min, max);
        }

        doSearch();
    }

    /* =========================================================
       SALARY RANGE SLIDER
    ========================================================= */

    function initSalarySlider() {
        var $slider    = $('#salary-range-slider');
        if (!$slider.length) return;

        var $minInput  = $('#salary-min, [name="salary_min"]').first();
        var $maxInput  = $('#salary-max, [name="salary_max"]').first();
        var $minThumb  = $slider.find('.cj-range-thumb--min');
        var $maxThumb  = $slider.find('.cj-range-thumb--max');
        var $track     = $slider.find('.cj-range-track-fill');
        var $minLabel  = $slider.find('.cj-range-label--min');
        var $maxLabel  = $slider.find('.cj-range-label--max');

        var sliderMin  = parseFloat($slider.data('min'))  || 0;
        var sliderMax  = parseFloat($slider.data('max'))  || 200000;
        var curMin     = parseFloat($minInput.val())      || sliderMin;
        var curMax     = parseFloat($maxInput.val())      || sliderMax;

        function formatSalary(val) {
            if (val >= 1000) { return '$' + Math.round(val / 1000) + 'k'; }
            return '$' + val;
        }

        function updateSlider(min, max) {
            var minPct = ((min - sliderMin) / (sliderMax - sliderMin)) * 100;
            var maxPct = ((max - sliderMin) / (sliderMax - sliderMin)) * 100;

            if ($minThumb.length) { $minThumb.css('left', minPct + '%'); }
            if ($maxThumb.length) { $maxThumb.css('left', maxPct + '%'); }
            if ($track.length)    { $track.css({ left: minPct + '%', width: (maxPct - minPct) + '%' }); }
            if ($minLabel.length) { $minLabel.text(formatSalary(min)); }
            if ($maxLabel.length) { $maxLabel.text(formatSalary(max)); }

            $minInput.val(min);
            $maxInput.val(max);
        }

        // If using native range inputs (two overlapping inputs)
        var $rangeMin = $slider.find('input[type="range"].cj-range-min');
        var $rangeMax = $slider.find('input[type="range"].cj-range-max');

        if ($rangeMin.length && $rangeMax.length) {
            $rangeMin.attr({ min: sliderMin, max: sliderMax, value: curMin, step: $rangeMin.attr('step') || 1000 });
            $rangeMax.attr({ min: sliderMin, max: sliderMax, value: curMax, step: $rangeMax.attr('step') || 1000 });

            function syncRange() {
                var minVal = parseFloat($rangeMin.val());
                var maxVal = parseFloat($rangeMax.val());
                var gap    = parseFloat($rangeMin.attr('step') || 1000);

                if (minVal >= maxVal - gap) {
                    if ($(this).is($rangeMin)) {
                        $rangeMin.val(maxVal - gap);
                        minVal = maxVal - gap;
                    } else {
                        $rangeMax.val(minVal + gap);
                        maxVal = minVal + gap;
                    }
                }
                updateSlider(minVal, maxVal);
            }

            $rangeMin.on('input', syncRange);
            $rangeMax.on('input', syncRange);

            $rangeMin.on('change', debounce(function () { doSearch(); }, 400));
            $rangeMax.on('change', debounce(function () { doSearch(); }, 400));
        } else {
            // Draggable thumb implementation
            makeDraggableThumb($minThumb, $slider, sliderMin, sliderMax, 'min', function (val) {
                var max = parseFloat($maxInput.val()) || sliderMax;
                if (val >= max) { val = max - 1000; }
                updateSlider(val, max);
                doSearch();
            });
            makeDraggableThumb($maxThumb, $slider, sliderMin, sliderMax, 'max', function (val) {
                var min = parseFloat($minInput.val()) || sliderMin;
                if (val <= min) { val = min + 1000; }
                updateSlider(min, val);
                doSearch();
            });
        }

        updateSlider(curMin, curMax);

        window.resetSalarySlider = function (min, max) {
            if ($rangeMin.length && $rangeMax.length) {
                $rangeMin.val(min);
                $rangeMax.val(max);
            }
            updateSlider(min, max);
        };
    }

    function makeDraggableThumb($thumb, $slider, sliderMin, sliderMax, type, onChange) {
        if (!$thumb.length) return;

        var dragging = false;

        $thumb.on('mousedown touchstart', function (e) {
            e.preventDefault();
            dragging = true;
            $(document).one('mouseup touchend', function () { dragging = false; });
        });

        $(document).on('mousemove touchmove', function (e) {
            if (!dragging) return;
            var sliderLeft  = $slider.offset().left;
            var sliderWidth = $slider.outerWidth();
            var clientX     = e.type === 'mousemove' ? e.clientX : e.originalEvent.touches[0].clientX;
            var pct         = Math.max(0, Math.min(1, (clientX - sliderLeft) / sliderWidth));
            var val         = Math.round((sliderMin + pct * (sliderMax - sliderMin)) / 1000) * 1000;
            onChange(val);
        });
    }

    /* =========================================================
       LOCATION AUTOCOMPLETE
    ========================================================= */

    function initLocationAutocomplete() {
        var $input = $('#cj-location-input, [name="location"]').first();
        if (!$input.length) return;

        // If Google Maps Places API is loaded, use it
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            var autocomplete = new google.maps.places.Autocomplete($input[0], {
                types: ['(cities)']
            });

            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                if (place.geometry && place.geometry.location) {
                    $input.data('lat', place.geometry.location.lat())
                          .data('lng', place.geometry.location.lng())
                          .attr('data-lat', place.geometry.location.lat())
                          .attr('data-lng', place.geometry.location.lng());
                }
                doSearch();
            });
            return;
        }

        // Fallback: simple AJAX-powered autocomplete
        var $dropdown = $('<ul class="cj-location-dropdown"></ul>').hide().insertAfter($input);

        var fetchSuggestions = debounce(function (query) {
            if (query.length < 2) { $dropdown.hide().empty(); return; }

            $.ajax({
                url      : getAjaxUrl(),
                type     : 'POST',
                dataType : 'json',
                data     : { action: 'civijobs_location_suggest', nonce: getNonce(), query: query },
                success  : function (res) {
                    $dropdown.empty();
                    if (!res || !res.success || !res.data || !res.data.length) {
                        $dropdown.hide();
                        return;
                    }
                    res.data.forEach(function (item) {
                        var $li = $('<li tabindex="0"></li>').text(item.label);
                        $li.on('click keydown', function (e) {
                            if (e.type === 'click' || e.key === 'Enter') {
                                $input.val(item.label);
                                if (item.lat && item.lng) {
                                    $input.data('lat', item.lat).data('lng', item.lng)
                                          .attr('data-lat', item.lat).attr('data-lng', item.lng);
                                }
                                $dropdown.hide().empty();
                                doSearch();
                            }
                        });
                        $dropdown.append($li);
                    });
                    $dropdown.show();
                }
            });
        }, 300);

        $input.on('input', function () { fetchSuggestions($(this).val()); });

        $input.on('keydown', function (e) {
            if (e.key === 'Escape') { $dropdown.hide().empty(); }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                $dropdown.find('li:first').focus();
            }
        });

        $dropdown.on('keydown', 'li', function (e) {
            if (e.key === 'ArrowDown') { $(this).next().focus(); }
            if (e.key === 'ArrowUp')   { $(this).prev().length ? $(this).prev().focus() : $input.focus(); }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#cj-location-input, [name="location"], .cj-location-dropdown').length) {
                $dropdown.hide().empty();
            }
        });
    }

    /* =========================================================
       INFINITE SCROLL
    ========================================================= */

    function initInfiniteScroll() {
        var $sentinel = $('.cj-infinite-scroll-sentinel');
        if (!$sentinel.length) return;
        if (!('IntersectionObserver' in window)) {
            // Fallback: load more button only
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting && !Search.loading && Search.page < Search.totalPages) {
                    Search.page++;
                    doSearch(true);
                }
            });
        }, { rootMargin: '200px 0px' });

        observer.observe($sentinel[0]);
    }

    /* =========================================================
       BIND EVENTS
    ========================================================= */

    function bindEvents() {
        // Real-time search input (debounced 400ms)
        $(document).on('input', '#cj-search-input, .cj-search-input, input[name="search_query"]', debounce(function () {
            doSearch();
        }, 400));

        // Search form submit
        $(document).on('submit', '#cj-search-form, .cj-search-form', function (e) {
            e.preventDefault();
            doSearch();
        });

        // Filter sidebar change events
        $(document).on('change', '#cj-filter-form input[type="checkbox"], #cj-filter-form input[type="radio"], #cj-filter-form select, .cj-filter-sidebar input[type="checkbox"], .cj-filter-sidebar input[type="radio"], .cj-filter-sidebar select', function () {
            doSearch();
        });

        // Sort change
        $(document).on('change', '#cj-sort, .cj-sort-select, select[name="sort_by"]', function () {
            doSearch();
        });

        // Load more button
        $(document).on('click', '.cj-load-more-btn', function () {
            if (Search.loading || Search.page >= Search.totalPages) return;
            Search.page++;
            doSearch(true);
        });

        // Remove single filter chip
        $(document).on('click', '.cj-filter-chip__remove', function () {
            var key = $(this).data('filter-key');
            var val = $(this).data('filter-val');
            removeFilter(key, val);
        });

        // Clear all filters
        $(document).on('click', '.cj-clear-all-filters', function () {
            clearAllFilters();
        });

        // Context switcher (jobs / companies / candidates)
        $(document).on('click', '.cj-search-context', function () {
            $('.cj-search-context').removeClass('is-active');
            $(this).addClass('is-active');
            Search.context = $(this).data('context') || 'jobs';
            doSearch();
        });

        // Popstate: restore URL-driven state on back/forward
        $(window).on('popstate', function (e) {
            var state = e.originalEvent.state;
            if (state && state.filters) {
                applyUrlStateToForm(state.filters);
                Search.page = state.page || 1;
                doSearch();
            }
        });
    }

    /* =========================================================
       MAP VIEW SYNC
    ========================================================= */

    function initMapViewSync() {
        // When map layout is active, re-run search on filter changes
        $(document).on('civijobs:map:layout:active', function () {
            doSearch();
        });

        // When doSearch finishes, it fires civijobs:map:update (see AJAX success handler above)
    }

    /* =========================================================
       INITIALIZE
    ========================================================= */

    $(function () {
        if (!$results().length && !$searchInput().length) return;

        // Set context from DOM hint
        var ctxHint = $('.cj-search-context.is-active').data('context') ||
                      $results().data('context') ||
                      $('[data-search-context]').data('search-context');
        if (ctxHint) { Search.context = ctxHint; }

        // Per-page override
        var ppHint = $results().data('per-page');
        if (ppHint) { Search.perPage = parseInt(ppHint, 10) || 10; }

        // Restore URL state
        var urlState = parseUrlState();
        if (Object.keys(urlState).length) {
            applyUrlStateToForm(urlState);
            if (urlState.paged) { Search.page = parseInt(urlState.paged, 10) || 1; }
        }

        bindEvents();
        initSalarySlider();
        initLocationAutocomplete();
        initInfiniteScroll();
        initMapViewSync();

        // Trigger initial search if the page is a search/archive page
        if ($results().data('auto-search') || window.location.search.length > 1) {
            doSearch();
        }
    });

}(jQuery));
