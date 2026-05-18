<?php
/**
 * Employer: Messages
 *
 * @package CiviJobs
 */
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Messages', 'civijobs' ); ?></h1>
    </div>
    <button class="btn btn-primary new-message-btn" data-modal-target="compose-message-modal">
        ✉️ <?php esc_html_e( 'New Message', 'civijobs' ); ?>
    </button>
</div>

<div class="messages-layout">
    <div class="message-list-panel">
        <div class="message-list-header">
            <?php esc_html_e( 'Conversations', 'civijobs' ); ?>
            <span class="unread-badge" id="msg-unread-count"></span>
        </div>
        <div class="message-search">
            <input type="text" placeholder="<?php esc_attr_e( 'Search conversations…', 'civijobs' ); ?>">
        </div>
        <div class="message-list" id="message-list">
            <div style="padding:20px;text-align:center;color:var(--color-gray-400)">
                <?php esc_html_e( 'Loading conversations…', 'civijobs' ); ?>
            </div>
        </div>
    </div>

    <div class="message-thread-panel" id="message-thread-panel" style="display:none">
        <div class="thread-header">
            <img class="message-avatar" src="" alt="" style="width:40px;height:40px;border-radius:50%">
            <div>
                <div class="message-sender" style="font-weight:700"></div>
            </div>
        </div>
        <div class="thread-messages" id="thread-messages"></div>
        <div class="thread-input-area">
            <textarea class="thread-textarea" placeholder="<?php esc_attr_e( 'Type a message…', 'civijobs' ); ?>" rows="1"></textarea>
            <button class="btn btn-primary send-message-btn"><?php esc_html_e( 'Send', 'civijobs' ); ?></button>
        </div>
    </div>

    <div class="no-thread-selected">
        <div style="font-size:3rem;opacity:.3">✉️</div>
        <p><?php esc_html_e( 'Select a conversation to start messaging', 'civijobs' ); ?></p>
    </div>
</div>
