/**
 * CiviJobs - Map Functionality (Leaflet.js)
 * Initializes maps, custom markers, popups, sidebar sync,
 * half/top layouts, clustering, geolocation, radius filter.
 */
(function ($) {
    'use strict';

    /* =========================================================
       GUARD: Leaflet must be loaded
    ========================================================= */

    if (typeof L === 'undefined') {
        $(function () {
            if ($('.cj-map-container, .cj-map-wrapper, #cj-jobs-map').length) {
                console.warn('CiviJobs Map: Leaflet.js is not loaded. Map features disabled.');
            }
        });
        return;
    }

    /* =========================================================
       DEFAULTS
    ========================================================= */

    var DEFAULT_LAT    = 40.7128;
    var DEFAULT_LNG    = -74.0060;
    var DEFAULT_ZOOM   = 11;
    var TILE_URL       = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var TILE_ATTR      = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
    var CLUSTER_RADIUS = 80;

    /* =========================================================
       MAP STORE
    ========================================================= */

    var MapStore = {
        map          : null,
        markerLayer  : null,
        clusterGroup : null,
        markers      : {},   // keyed by job/company ID
        radiusCircle : null,
        currentCenter: null
    };

    /* =========================================================
       UTILITY
    ========================================================= */

    function getAjaxUrl() {
        return (typeof civijobs_ajax !== 'undefined' && civijobs_ajax.ajaxurl) ? civijobs_ajax.ajaxurl : '/wp-admin/admin-ajax.php';
    }

    function getNonce() {
        return (typeof civijobs_ajax !== 'undefined' && civijobs_ajax.nonce) ? civijobs_ajax.nonce : '';
    }

    function parseJsonAttr($el, attr) {
        try {
            var raw = $el.attr(attr) || $el.data(attr.replace('data-', ''));
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    /* =========================================================
       CUSTOM ICON FACTORY
    ========================================================= */

    /**
     * Create a custom Leaflet icon using a company logo thumbnail.
     * Falls back to a styled div icon if no logo provided.
     *
     * @param {object} markerData - { logo_url, title, color }
     * @returns {L.Icon|L.DivIcon}
     */
    function createCustomIcon(markerData) {
        var color = markerData.color || '#2563eb';

        if (markerData.logo_url) {
            return L.divIcon({
                className  : 'cj-map-marker cj-map-marker--logo',
                html       : '<div class="cj-map-marker__wrapper" style="border-color:' + color + '">' +
                             '<img src="' + markerData.logo_url + '" alt="' + (markerData.company || '') + '" loading="lazy">' +
                             '<span class="cj-map-marker__arrow" style="border-top-color:' + color + '"></span>' +
                             '</div>',
                iconSize   : [48, 56],
                iconAnchor : [24, 56],
                popupAnchor: [0, -58]
            });
        }

        // Text/initials icon
        var initials = (markerData.company || markerData.title || 'J').charAt(0).toUpperCase();
        return L.divIcon({
            className  : 'cj-map-marker cj-map-marker--initials',
            html       : '<div class="cj-map-marker__wrapper" style="background:' + color + '">' +
                         '<span>' + initials + '</span>' +
                         '<span class="cj-map-marker__arrow" style="border-top-color:' + color + '"></span>' +
                         '</div>',
            iconSize   : [40, 48],
            iconAnchor : [20, 48],
            popupAnchor: [0, -50]
        });
    }

    /* =========================================================
       POPUP HTML BUILDER
    ========================================================= */

    function buildPopupHtml(m) {
        var salary = '';
        if (m.salary_min || m.salary_max) {
            var parts = [];
            if (m.salary_min) { parts.push('$' + parseInt(m.salary_min, 10).toLocaleString()); }
            if (m.salary_max) { parts.push('$' + parseInt(m.salary_max, 10).toLocaleString()); }
            salary = '<span class="cj-popup__salary">' + parts.join(' – ') + '</span>';
        }

        var logo = m.logo_url
            ? '<img class="cj-popup__logo" src="' + m.logo_url + '" alt="' + (m.company || '') + '">'
            : '';

        var jobType = m.job_type
            ? '<span class="cj-popup__badge cj-popup__badge--' + m.job_type.toLowerCase().replace(/\s+/g, '-') + '">' + m.job_type + '</span>'
            : '';

        return '<div class="cj-map-popup" data-job-id="' + (m.id || '') + '">' +
               (logo ? '<div class="cj-popup__logo-wrap">' + logo + '</div>' : '') +
               '<div class="cj-popup__body">' +
               (m.title   ? '<h4 class="cj-popup__title">' + m.title + '</h4>' : '') +
               (m.company ? '<p class="cj-popup__company">' + m.company + '</p>' : '') +
               (m.location ? '<p class="cj-popup__location"><svg class="cj-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>' + m.location + '</p>' : '') +
               '<div class="cj-popup__meta">' + jobType + salary + '</div>' +
               (m.url ? '<a href="' + m.url + '" class="cj-btn cj-btn--sm cj-btn--primary">View Job</a>' : '') +
               '</div>' +
               '</div>';
    }

    /* =========================================================
       MARKER MANAGEMENT
    ========================================================= */

    /**
     * Add markers to the map from an array of marker data objects.
     * Each object: { id, lat, lng, title, company, location, salary_min, salary_max, url, logo_url, color, job_type }
     */
    function addMarkers(markersData) {
        if (!MapStore.map) return;

        // Clear existing
        clearMarkers();

        if (!markersData || !markersData.length) return;

        var layer = MapStore.clusterGroup || MapStore.map;

        markersData.forEach(function (m) {
            if (!m.lat || !m.lng) return;

            var icon   = createCustomIcon(m);
            var marker = L.marker([parseFloat(m.lat), parseFloat(m.lng)], { icon: icon, title: m.title || '' });
            var id     = m.id || (m.lat + '_' + m.lng);

            // Bind popup
            marker.bindPopup(buildPopupHtml(m), {
                maxWidth   : 280,
                minWidth   : 220,
                className  : 'cj-leaflet-popup'
            });

            // Hover & click events for sidebar sync
            marker.on('click', function () {
                highlightCard(id);
                if (window.innerWidth >= 768) {
                    MapStore.map.setView([parseFloat(m.lat), parseFloat(m.lng)], Math.max(MapStore.map.getZoom(), 13));
                }
                $(document).trigger('civijobs:map:marker:click', [id, m]);
            });

            marker.on('mouseover', function () {
                this.openPopup();
                highlightCard(id);
            });

            marker.on('mouseout', function () {
                if (!marker.isPopupOpen() || !$(document.querySelector('.cj-map-popup')).is(':hover')) {
                    // Only close if popup was opened on hover (not on click)
                }
                unhighlightAll();
            });

            // Store reference
            MapStore.markers[id] = marker;
            m._leafletMarker = marker;

            if (MapStore.clusterGroup) {
                MapStore.clusterGroup.addLayer(marker);
            } else {
                marker.addTo(layer);
            }
        });

        // Fit bounds
        fitMapToMarkers();
    }

    function clearMarkers() {
        if (MapStore.clusterGroup) {
            MapStore.clusterGroup.clearLayers();
        } else {
            $.each(MapStore.markers, function (id, marker) {
                if (MapStore.map) { MapStore.map.removeLayer(marker); }
            });
        }
        MapStore.markers = {};
    }

    function fitMapToMarkers() {
        var bounds = [];
        $.each(MapStore.markers, function (id, marker) {
            bounds.push(marker.getLatLng());
        });
        if (bounds.length === 1) {
            MapStore.map.setView(bounds[0], DEFAULT_ZOOM);
        } else if (bounds.length > 1) {
            MapStore.map.fitBounds(L.latLngBounds(bounds), { padding: [40, 40], maxZoom: 15 });
        }
    }

    /* =========================================================
       SIDEBAR CARD SYNC
    ========================================================= */

    function highlightCard(id) {
        $('.cj-job-card, .cj-listing-card').removeClass('map-highlighted');
        $('.cj-job-card[data-job-id="' + id + '"], .cj-listing-card[data-job-id="' + id + '"]').addClass('map-highlighted');

        // Scroll card into view in the sidebar
        var $card = $('.cj-job-card[data-job-id="' + id + '"]').first();
        if ($card.length) {
            var $list = $card.closest('.cj-map-list-panel, .cj-jobs-list');
            if ($list.length) {
                var cardTop  = $card.position().top + $list.scrollTop();
                var listHeight = $list.outerHeight();
                var cardHeight = $card.outerHeight();
                $list.animate({ scrollTop: cardTop - (listHeight / 2) + (cardHeight / 2) }, 200);
            }
        }
    }

    function unhighlightAll() {
        $('.cj-job-card, .cj-listing-card').removeClass('map-highlighted');
    }

    function highlightMarker(id) {
        var marker = MapStore.markers[id];
        if (marker) {
            marker.openPopup();
            var el = marker.getElement();
            if (el) {
                $(el).addClass('cj-map-marker--active');
            }
        }
        $.each(MapStore.markers, function (markerId, m) {
            if (markerId !== id) {
                var mEl = m.getElement();
                if (mEl) { $(mEl).removeClass('cj-map-marker--active'); }
            }
        });
    }

    /* =========================================================
       CARD HOVER/CLICK -> MAP SYNC
    ========================================================= */

    function initCardMapSync() {
        // Hover over card -> highlight marker
        $(document).on('mouseenter', '.cj-job-card[data-job-id], .cj-listing-card[data-job-id]', function () {
            var id = $(this).data('job-id');
            if (id) { highlightMarker(String(id)); }
        });

        $(document).on('mouseleave', '.cj-job-card[data-job-id], .cj-listing-card[data-job-id]', function () {
            $.each(MapStore.markers, function (id, marker) {
                var el = marker.getElement();
                if (el) { $(el).removeClass('cj-map-marker--active'); }
            });
        });
    }

    /* =========================================================
       GEOLOCATION BUTTON
    ========================================================= */

    function initGeolocation() {
        var $btn = $('.cj-map-geolocate, #cj-map-geolocate');
        if (!$btn.length) return;

        $btn.on('click', function () {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }
            var $b = $(this);
            $b.addClass('is-loading').prop('disabled', true);

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    $b.removeClass('is-loading').prop('disabled', false);
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    MapStore.currentCenter = L.latLng(lat, lng);
                    MapStore.map.setView(MapStore.currentCenter, DEFAULT_ZOOM);

                    // Add/update "You are here" marker
                    if (MapStore._userMarker) {
                        MapStore.map.removeLayer(MapStore._userMarker);
                    }
                    MapStore._userMarker = L.circleMarker(MapStore.currentCenter, {
                        radius      : 10,
                        fillColor   : '#2563eb',
                        fillOpacity : 1,
                        color       : '#fff',
                        weight      : 3
                    }).addTo(MapStore.map).bindPopup('You are here');

                    // Populate location fields if they exist
                    $('[name="location"], #cj-location-input').val('Current location')
                        .attr('data-lat', lat).attr('data-lng', lng)
                        .data('lat', lat).data('lng', lng);

                    $(document).trigger('civijobs:map:geolocated', [lat, lng]);
                },
                function (err) {
                    $b.removeClass('is-loading').prop('disabled', false);
                    var msgs = {
                        1: 'Location access denied.',
                        2: 'Location unavailable.',
                        3: 'Location request timed out.'
                    };
                    var msg = msgs[err.code] || 'Could not get your location.';
                    if (typeof CiviJobs !== 'undefined' && CiviJobs.toast) {
                        CiviJobs.toast(msg, 'error');
                    } else {
                        alert(msg);
                    }
                },
                { timeout: 10000, maximumAge: 60000 }
            );
        });
    }

    /* =========================================================
       RADIUS CIRCLE
    ========================================================= */

    function drawRadiusCircle(lat, lng, radiusKm) {
        if (MapStore.radiusCircle) {
            MapStore.map.removeLayer(MapStore.radiusCircle);
            MapStore.radiusCircle = null;
        }
        if (!radiusKm || radiusKm <= 0) return;

        MapStore.radiusCircle = L.circle([lat, lng], {
            radius      : radiusKm * 1000,  // metres
            color       : '#2563eb',
            fillColor   : '#2563eb',
            fillOpacity : 0.08,
            weight      : 2,
            dashArray   : '6 4'
        }).addTo(MapStore.map);

        MapStore.map.fitBounds(MapStore.radiusCircle.getBounds(), { padding: [20, 20] });
    }

    /* =========================================================
       LAYOUT HELPERS
    ========================================================= */

    function setLayout(layout) {
        var $wrapper = $('.cj-map-wrapper, .cj-map-layout');
        $wrapper.removeClass('layout-half layout-top layout-full').addClass('layout-' + layout);

        // Invalidate map size after CSS transition
        if (MapStore.map) {
            setTimeout(function () {
                MapStore.map.invalidateSize();
            }, 350);
        }

        // Tell search module map layout is active
        if (layout !== 'none') {
            $(document).trigger('civijobs:map:layout:active');
        }
    }

    /* =========================================================
       MAIN MAP INIT
    ========================================================= */

    function initMap($el) {
        var elId     = $el.attr('id') || 'cj-jobs-map';
        $el.attr('id', elId);

        var lat      = parseFloat($el.data('lat'))  || DEFAULT_LAT;
        var lng      = parseFloat($el.data('lng'))  || DEFAULT_LNG;
        var zoom     = parseInt($el.data('zoom'), 10) || DEFAULT_ZOOM;
        var layout   = $el.data('layout') || 'half';  // half | top | full

        var map = L.map(elId, {
            center         : [lat, lng],
            zoom           : zoom,
            scrollWheelZoom: false,
            zoomControl    : true
        });

        L.tileLayer(TILE_URL, {
            attribution: TILE_ATTR,
            maxZoom    : 19
        }).addTo(map);

        // Enable scroll wheel zoom on focus
        map.once('focus', function () { map.scrollWheelZoom.enable(); });
        map.on('blur',    function () { map.scrollWheelZoom.disable(); });

        MapStore.map = map;

        // Initialize marker cluster if available
        if (typeof L.markerClusterGroup !== 'undefined') {
            MapStore.clusterGroup = L.markerClusterGroup({
                maxClusterRadius    : CLUSTER_RADIUS,
                iconCreateFunction  : function (cluster) {
                    var count = cluster.getChildCount();
                    return L.divIcon({
                        html       : '<div class="cj-cluster-icon"><span>' + count + '</span></div>',
                        className  : 'cj-marker-cluster',
                        iconSize   : [40, 40]
                    });
                },
                spiderfyOnMaxZoom   : true,
                showCoverageOnHover : false
            });
            map.addLayer(MapStore.clusterGroup);
        }

        // Load initial markers from data attribute
        var initialMarkers = parseJsonAttr($el, 'data-markers');
        if (initialMarkers.length) {
            addMarkers(initialMarkers);
        }

        // Apply layout
        setLayout(layout);

        // Recalculate size when sidebar toggles
        $(document).on('civijobs:sidebar:toggle', function () {
            setTimeout(function () { map.invalidateSize(); }, 300);
        });

        // Re-center button
        var $recenter = $('.cj-map-recenter, #cj-map-recenter');
        $recenter.on('click', function () {
            if (MapStore.markers && Object.keys(MapStore.markers).length) {
                fitMapToMarkers();
            } else {
                map.setView([lat, lng], zoom);
            }
        });

        // Store map on element
        $el.data('leaflet-map', map);

        $(document).trigger('civijobs:map:ready', [map, $el]);
        return map;
    }

    /* =========================================================
       RESPOND TO SEARCH MODULE UPDATES
    ========================================================= */

    $(document).on('civijobs:map:update', function (e, markersData) {
        if (!MapStore.map) return;
        addMarkers(markersData);
    });

    // Radius filter: draw circle when location + radius are set
    $(document).on('civijobs:map:geolocated', function (e, lat, lng) {
        var radius = parseFloat($('[name="radius"], #cj-radius').val()) || 0;
        if (radius > 0) {
            drawRadiusCircle(lat, lng, radius);
        }
    });

    $(document).on('change', '[name="radius"], #cj-radius', function () {
        var $locationInput = $('[name="location"], #cj-location-input');
        var lat = parseFloat($locationInput.data('lat') || $locationInput.attr('data-lat'));
        var lng = parseFloat($locationInput.data('lng') || $locationInput.attr('data-lng'));
        var radius = parseFloat($(this).val()) || 0;

        if (lat && lng && radius > 0) {
            drawRadiusCircle(lat, lng, radius);
        } else if (MapStore.radiusCircle) {
            MapStore.map.removeLayer(MapStore.radiusCircle);
            MapStore.radiusCircle = null;
        }
    });

    /* =========================================================
       LAYOUT TOGGLE BUTTONS
    ========================================================= */

    $(document).on('click', '[data-map-layout]', function () {
        var layout = $(this).data('map-layout');
        $('[data-map-layout]').removeClass('is-active');
        $(this).addClass('is-active');
        setLayout(layout);
    });

    /* =========================================================
       INITIALIZE ON DOM READY
    ========================================================= */

    $(function () {
        var $mapEls = $('.cj-map-container, .cj-map-wrapper > .cj-map, #cj-jobs-map, [data-map="civijobs"]');
        if (!$mapEls.length) return;

        $mapEls.each(function () {
            initMap($(this));
        });

        initCardMapSync();
        initGeolocation();
    });

    /* =========================================================
       PUBLIC API
    ========================================================= */

    window.CiviJobsMap = {
        addMarkers    : addMarkers,
        clearMarkers  : clearMarkers,
        fitMapToMarkers: fitMapToMarkers,
        drawRadiusCircle: drawRadiusCircle,
        setLayout     : setLayout,
        getMap        : function () { return MapStore.map; },
        getMarkers    : function () { return MapStore.markers; }
    };

}(jQuery));
