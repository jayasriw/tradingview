/**
 * CiviJobs — Private Messaging
 */
( function( $, cfg ) {
    'use strict';

    let activeThread = null;
    let pollTimer    = null;

    const el = {
        list      : $( '.message-list' ),
        thread    : $( '.thread-messages' ),
        textarea  : $( '.thread-textarea' ),
        sendBtn   : $( '.send-message-btn' ),
        noThread  : $( '.no-thread-selected' ),
        threadPanel: $( '.message-thread-panel' ),
        searchInput: $( '.message-search input' ),
        newBtn    : $( '.new-message-btn' ),
    };

    // ---- Init ----
    function init() {
        if ( ! el.list.length ) return;

        loadConversations();
        bindEvents();

        // Open thread from URL hash
        const hash = window.location.hash.replace( '#thread-', '' );
        if ( hash ) {
            setTimeout( () => openThread( hash ), 600 );
        }
    }

    // ---- Load conversation list ----
    function loadConversations() {
        $.ajax( {
            url  : cfg.ajaxurl,
            type : 'POST',
            data : { action: 'civijobs_get_conversations', nonce: cfg.nonce },
            success( res ) {
                if ( res.success ) {
                    renderConversations( res.data.conversations );
                }
            }
        } );
    }

    function renderConversations( conversations ) {
        el.list.empty();

        if ( ! conversations.length ) {
            el.list.html( '<div class="empty-state" style="padding:30px;text-align:center;color:#9ca3af">No conversations yet.</div>' );
            return;
        }

        conversations.forEach( conv => {
            const unread   = conv.unread_count > 0;
            const $item    = $( `
                <div class="message-item ${ unread ? 'unread' : '' }" data-thread="${ esc( conv.thread_id ) }">
                    <img class="message-avatar" src="${ esc( conv.other_avatar ) }" alt="">
                    <div class="message-info">
                        <div class="message-sender">${ esc( conv.other_name ) }</div>
                        <div class="message-preview">${ esc( conv.last_message ) }</div>
                    </div>
                    <div class="message-time">${ esc( conv.time_ago ) }</div>
                    ${ unread ? '<div class="message-unread-dot"></div>' : '' }
                </div>
            ` );
            el.list.append( $item );
        } );
    }

    // ---- Open a thread ----
    function openThread( threadId ) {
        activeThread = threadId;
        window.location.hash = `thread-${ threadId }`;

        el.list.find( '.message-item' ).removeClass( 'active' );
        el.list.find( `[data-thread="${ threadId }"]` ).addClass( 'active' ).removeClass( 'unread' )
            .find( '.message-unread-dot' ).remove();

        el.noThread.hide();
        el.threadPanel.show();

        fetchMessages( threadId );

        clearInterval( pollTimer );
        pollTimer = setInterval( () => {
            if ( activeThread ) fetchMessages( activeThread, true );
        }, 30000 );
    }

    function fetchMessages( threadId, silent = false ) {
        if ( ! silent ) {
            el.thread.html( '<div style="text-align:center;padding:20px;color:#9ca3af">Loading…</div>' );
        }

        $.ajax( {
            url  : cfg.ajaxurl,
            type : 'POST',
            data : { action: 'civijobs_get_messages', nonce: cfg.nonce, thread_id: threadId },
            success( res ) {
                if ( res.success ) {
                    renderMessages( res.data.messages, silent );
                    updateThreadHeader( res.data.other_user );
                }
            }
        } );
    }

    function renderMessages( messages, silent ) {
        const scrolledToBottom = isScrolledToBottom();
        const currentCount     = el.thread.children().length;

        if ( silent && messages.length === currentCount ) return;

        el.thread.empty();

        messages.forEach( msg => {
            const isMine = parseInt( msg.sender_id ) === parseInt( cfg.current_user_id );
            const $wrap  = $( `
                <div class="message-bubble-wrap ${ isMine ? 'mine' : '' }">
                    <img class="bubble-avatar" src="${ esc( msg.sender_avatar ) }" alt="">
                    <div>
                        <div class="message-bubble">${ escHtml( msg.body ) }</div>
                        <div class="bubble-time">${ esc( msg.time_ago ) }</div>
                    </div>
                </div>
            ` );
            el.thread.append( $wrap );
        } );

        if ( ! silent || scrolledToBottom ) {
            el.thread.scrollTop( el.thread[0].scrollHeight );
        }
    }

    function updateThreadHeader( user ) {
        if ( ! user ) return;
        $( '.thread-header .message-sender' ).text( user.name );
        $( '.thread-header .message-avatar' ).attr( 'src', user.avatar );
    }

    function isScrolledToBottom() {
        const t = el.thread[0];
        return ! t || ( t.scrollHeight - t.scrollTop - t.clientHeight < 50 );
    }

    // ---- Send message ----
    function sendMessage() {
        const body = el.textarea.val().trim();
        if ( ! body || ! activeThread ) return;

        el.sendBtn.prop( 'disabled', true );

        $.ajax( {
            url  : cfg.ajaxurl,
            type : 'POST',
            data : {
                action    : 'civijobs_send_message',
                nonce     : cfg.nonce,
                thread_id : activeThread,
                body      : body,
            },
            success( res ) {
                if ( res.success ) {
                    el.textarea.val( '' ).css( 'height', 'auto' );
                    fetchMessages( activeThread );
                    loadConversations();
                } else {
                    window.civijobs && civijobs.toast( res.data.message, 'error' );
                }
            },
            complete() {
                el.sendBtn.prop( 'disabled', false );
            }
        } );
    }

    // ---- Compose new message ----
    function openComposeModal() {
        const $modal = $( '#compose-message-modal' );
        if ( $modal.length ) {
            $modal.addClass( 'is-open' );
        }
    }

    // ---- Handle compose form submit ----
    $( document ).on( 'submit', '#compose-message-form', function( e ) {
        e.preventDefault();
        const $form  = $( this );
        const data   = {
            action      : 'civijobs_send_message',
            nonce       : cfg.nonce,
            receiver_id : $form.find( '[name="receiver_id"]' ).val(),
            subject     : $form.find( '[name="subject"]' ).val(),
            body        : $form.find( '[name="body"]' ).val(),
            job_id      : $form.find( '[name="job_id"]' ).val() || 0,
        };

        $form.find( '[type="submit"]' ).prop( 'disabled', true ).text( cfg.i18n.sending );

        $.ajax( {
            url  : cfg.ajaxurl,
            type : 'POST',
            data : data,
            success( res ) {
                if ( res.success ) {
                    $( '#compose-message-modal' ).removeClass( 'is-open' );
                    $form[0].reset();
                    window.civijobs && civijobs.toast( cfg.i18n.sent, 'success' );
                    loadConversations();
                    if ( res.data.thread_id ) openThread( res.data.thread_id );
                } else {
                    window.civijobs && civijobs.toast( res.data.message, 'error' );
                }
            },
            complete() {
                $form.find( '[type="submit"]' ).prop( 'disabled', false ).text( 'Send Message' );
            }
        } );
    } );

    // ---- Search conversations ----
    let searchTimer = null;
    el.searchInput.on( 'input', function() {
        clearTimeout( searchTimer );
        const q = $( this ).val().toLowerCase();
        searchTimer = setTimeout( () => {
            el.list.find( '.message-item' ).each( function() {
                const name = $( this ).find( '.message-sender' ).text().toLowerCase();
                const prev = $( this ).find( '.message-preview' ).text().toLowerCase();
                $( this ).toggle( name.includes( q ) || prev.includes( q ) );
            } );
        }, 200 );
    } );

    // ---- Bind events ----
    function bindEvents() {
        el.list.on( 'click', '.message-item', function() {
            const threadId = $( this ).data( 'thread' );
            if ( threadId ) openThread( threadId );
        } );

        el.sendBtn.on( 'click', sendMessage );

        el.textarea.on( 'keydown', function( e ) {
            if ( e.key === 'Enter' && ! e.shiftKey ) {
                e.preventDefault();
                sendMessage();
            }
        } );

        // Auto-resize textarea
        el.textarea.on( 'input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min( this.scrollHeight, 120 ) + 'px';
        } );

        el.newBtn.on( 'click', openComposeModal );
    }

    // ---- Escape helpers ----
    function esc( str ) {
        return String( str || '' );
    }

    function escHtml( str ) {
        return String( str || '' )
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' )
            .replace( /"/g, '&quot;' )
            .replace( /\n/g, '<br>' );
    }

    $( document ).ready( init );

} )( jQuery, typeof civijobs_ajax !== 'undefined' ? civijobs_ajax : {} );
