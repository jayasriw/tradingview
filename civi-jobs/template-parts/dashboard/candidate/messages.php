<?php
/**
 * Candidate: Messages
 *
 * @package CiviJobs
 */

$user_id      = get_current_user_id();
$threads      = civijobs_get_message_threads( $user_id );
$active_thread = isset( $_GET['thread'] ) ? absint( $_GET['thread'] ) : 0;
$unread_count  = civijobs_get_unread_messages( $user_id );
?>
<div class="dash-header">
    <div>
        <h1 class="dash-title"><?php esc_html_e( 'Messages', 'civijobs' ); ?></h1>
        <?php if ( $unread_count > 0 ) : ?>
            <p class="dash-subtitle"><?php printf( _n( '%d unread message', '%d unread messages', $unread_count, 'civijobs' ), $unread_count ); // phpcs:ignore ?></p>
        <?php endif; ?>
    </div>
    <button class="btn btn-primary" data-modal-target="compose-message-modal">
        ✉️ <?php esc_html_e( 'New Message', 'civijobs' ); ?>
    </button>
</div>

<div class="messaging-layout">
    <!-- Thread list -->
    <div class="messaging-sidebar" id="messaging-sidebar">
        <div class="messaging-search">
            <input type="text" id="thread-search" placeholder="<?php esc_attr_e( 'Search conversations…', 'civijobs' ); ?>" class="form-control">
        </div>
        <div class="thread-list" id="thread-list">
            <?php if ( $threads ) :
                foreach ( $threads as $thread ) :
                    $other_user_id = ( $thread->sender_id == $user_id ) ? $thread->receiver_id : $thread->sender_id;
                    $other_user    = get_userdata( $other_user_id );
                    $is_unread     = ! $thread->is_read && $thread->receiver_id == $user_id;
                    $is_active     = $active_thread === (int) $thread->thread_id;
            ?>
                <a href="?section=messages&thread=<?php echo esc_attr( $thread->thread_id ); ?>"
                   class="thread-item <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_unread ? 'unread' : ''; ?>"
                   data-thread-id="<?php echo esc_attr( $thread->thread_id ); ?>">
                    <div class="thread-avatar">
                        <img src="<?php echo esc_url( civijobs_get_avatar_url( $other_user_id, 40 ) ); ?>" alt="">
                    </div>
                    <div class="thread-info">
                        <div class="thread-name"><?php echo esc_html( $other_user ? $other_user->display_name : __( 'Unknown User', 'civijobs' ) ); ?></div>
                        <div class="thread-preview"><?php echo esc_html( wp_trim_words( $thread->message, 8 ) ); ?></div>
                    </div>
                    <div class="thread-meta">
                        <div class="thread-time"><?php echo esc_html( civijobs_time_ago( $thread->created_at ) ); ?></div>
                        <?php if ( $is_unread ) : ?>
                            <span class="thread-badge"></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach;
            else : ?>
                <div class="empty-state" style="padding:40px 20px">
                    <div class="empty-state-icon">✉️</div>
                    <div class="empty-state-title"><?php esc_html_e( 'No conversations yet', 'civijobs' ); ?></div>
                    <div class="empty-state-text"><?php esc_html_e( 'Your message threads will appear here.', 'civijobs' ); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Thread messages -->
    <div class="messaging-main" id="messaging-main">
        <?php if ( $active_thread ) :
            global $wpdb;
            $messages = $wpdb->get_results( $wpdb->prepare(
                "SELECT m.*, u.display_name FROM {$wpdb->prefix}civi_messages m
                 LEFT JOIN {$wpdb->users} u ON u.ID = m.sender_id
                 WHERE m.thread_id = %s ORDER BY m.created_at ASC",
                $active_thread
            ) );
            // Mark thread as read
            $wpdb->update( "{$wpdb->prefix}civi_messages",
                [ 'is_read' => 1 ],
                [ 'thread_id' => $active_thread, 'receiver_id' => $user_id ],
                [ '%d' ], [ '%s', '%d' ]
            );
        ?>
            <div class="messaging-header">
                <?php
                $first_msg = $messages ? $messages[0] : null;
                if ( $first_msg ) :
                    $other_id = ( $first_msg->sender_id == $user_id ) ? $first_msg->receiver_id : $first_msg->sender_id;
                    $other    = get_userdata( $other_id );
                ?>
                    <img src="<?php echo esc_url( civijobs_get_avatar_url( $other_id, 40 ) ); ?>" class="messaging-header-avatar" alt="">
                    <div>
                        <div class="messaging-header-name"><?php echo esc_html( $other ? $other->display_name : '' ); ?></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="messages-feed" id="messages-feed">
                <?php foreach ( $messages as $msg ) :
                    $is_mine = ( $msg->sender_id == $user_id );
                ?>
                    <div class="message-bubble <?php echo $is_mine ? 'mine' : 'theirs'; ?>">
                        <div class="message-text"><?php echo nl2br( esc_html( $msg->message ) ); ?></div>
                        <div class="message-time"><?php echo esc_html( civijobs_time_ago( $msg->created_at ) ); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form class="message-compose-form" id="reply-form" data-thread-id="<?php echo esc_attr( $active_thread ); ?>">
                <?php wp_nonce_field( 'civijobs_nonce', 'nonce' ); ?>
                <input type="hidden" name="receiver_id" value="<?php echo esc_attr( isset( $other_id ) ? $other_id : 0 ); ?>">
                <textarea name="message" id="reply-textarea" class="form-control message-textarea"
                          placeholder="<?php esc_attr_e( 'Type a message…', 'civijobs' ); ?>" rows="1" required></textarea>
                <button type="submit" class="btn btn-primary message-send-btn">
                    <?php esc_html_e( 'Send', 'civijobs' ); ?>
                </button>
            </form>
        <?php else : ?>
            <div class="messaging-empty">
                <div class="empty-state">
                    <div class="empty-state-icon">💬</div>
                    <div class="empty-state-title"><?php esc_html_e( 'Select a conversation', 'civijobs' ); ?></div>
                    <div class="empty-state-text"><?php esc_html_e( 'Choose a conversation from the left or start a new one.', 'civijobs' ); ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
