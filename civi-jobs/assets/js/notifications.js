/**
 * CiviJobs — Notification system
 */
( function( $, cfg ) {
    'use strict';

    const POLL_INTERVAL = 60000; // 60 seconds
    let pollTimer       = null;
    let isOpen          = false;

    const $bell     = $( '.notification-bell' );
    const $count    = $( '.notification-count' );
    const $dropdown = $( '.notification-dropdown' );
    const $list     = $( '.notification-list' );

    function init() {
        if ( ! cfg.is_logged_in ) return;

        fetchNotifications();
        pollTimer = setInterval( fetchNotifications, POLL_INTERVAL );
        bindEvents();
    }

    function fetchNotifications() {
        $.ajax( {
            url  : cfg.ajaxurl,
            type : 'POST',
            data : { action: 'civijobs_get_notifications', nonce: cfg.nonce },
            success( res ) {
                if ( res.success ) {
                    updateBadge( res.data.unread_count );
                    if ( isOpen ) renderList( res.data.notifications );
                }
            }
        } );
    }

    function updateBadge( count ) {
        if ( count > 0 ) {
            $count.text( count > 99 ? '99+' : count ).show();
        } else {
            $count.hide();
        }
    }

    function renderList( notifications ) {
        $list.empty();

        if ( ! notifications.length ) {
            $list.html( '<div class="notification-empty">No notifications yet.</div>' );
            return;
        }

        notifications.forEach( n => {
            const iconMap = {
                application : '📋',
                message     : '✉️',
                meeting     : '📅',
                job_approved: '✅',
                job_expired : '⏰',
                package     : '🎁',
                review      : '⭐',
                alert       : '🔔',
            };
            const icon = iconMap[ n.type ] || '🔔';

            const $item = $( `
                <div class="notification-item ${ n.is_read == '0' ? 'unread' : '' }"
                     data-id="${ n.id }"
                     data-link="${ escAttr( n.link ) }">
                    <div class="notification-icon ${ escAttr( n.type ) }">${ icon }</div>
                    <div class="notification-body">
                        <div class="notification-title">${ escHtml( n.title ) }</div>
                        <div class="notification-message">${ escHtml( n.message ) }</div>
                    </div>
                    <div class="notification-time">${ escHtml( n.time_ago ) }</div>
                </div>
            ` );
            $list.append( $item );
        } );
    }

    function markAllRead() {
        $.ajax( {
            url  : cfg.ajaxurl,
            type : 'POST',
            data : { action: 'civijobs_mark_all_notifications_read', nonce: cfg.nonce },
            success() {
                $count.hide().text( '0' );
                $list.find( '.notification-item' ).removeClass( 'unread' );
            }
        } );
    }

    function markOneRead( id, link ) {
        $.ajax( {
            url  : cfg.ajaxurl,
            type : 'POST',
            data : { action: 'civijobs_mark_notification_read', nonce: cfg.nonce, notification_id: id },
        } );
        if ( link ) {
            window.location.href = link;
        }
    }

    function openDropdown() {
        isOpen = true;
        $dropdown.addClass( 'is-open' );

        // Load notifications now
        $.ajax( {
            url  : cfg.ajaxurl,
            type : 'POST',
            data : { action: 'civijobs_get_notifications', nonce: cfg.nonce },
            success( res ) {
                if ( res.success ) {
                    updateBadge( res.data.unread_count );
                    renderList( res.data.notifications );
                }
            }
        } );
    }

    function closeDropdown() {
        isOpen = false;
        $dropdown.removeClass( 'is-open' );
    }

    function bindEvents() {
        // Toggle dropdown on bell click
        $bell.on( 'click', function( e ) {
            e.stopPropagation();
            if ( isOpen ) {
                closeDropdown();
            } else {
                openDropdown();
            }
        } );

        // Close on outside click
        $( document ).on( 'click', function( e ) {
            if ( isOpen && ! $( e.target ).closest( '.notification-bell-wrap' ).length ) {
                closeDropdown();
            }
        } );

        // Click on notification item
        $list.on( 'click', '.notification-item', function() {
            const id   = $( this ).data( 'id' );
            const link = $( this ).data( 'link' );
            $( this ).removeClass( 'unread' );
            markOneRead( id, link );
        } );

        // Mark all read
        $( document ).on( 'click', '.mark-all-read-btn', function( e ) {
            e.preventDefault();
            markAllRead();
        } );

        // Clear all
        $( document ).on( 'click', '.clear-notifications-btn', function( e ) {
            e.preventDefault();
            $.ajax( {
                url  : cfg.ajaxurl,
                type : 'POST',
                data : { action: 'civijobs_clear_notifications', nonce: cfg.nonce },
                success() {
                    $list.html( '<div class="notification-empty">No notifications yet.</div>' );
                    $count.hide();
                }
            } );
        } );
    }

    function escHtml( str ) {
        return String( str || '' )
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' );
    }

    function escAttr( str ) {
        return String( str || '' ).replace( /"/g, '&quot;' );
    }

    $( document ).ready( init );

} )( jQuery, typeof civijobs_ajax !== 'undefined' ? civijobs_ajax : {} );
