<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
    return;
}
?>
<div class="wrap clc-admin">

    <h1><?php esc_html_e( 'Carno LiveChat — Broadcast Panel', 'carno-livechat' ); ?></h1>

    <div class="clc-admin__topbar">
        <div class="clc-admin__stats">
            <span class="clc-admin__stat-label"><?php esc_html_e( 'Online Users:', 'carno-livechat' ); ?></span>
            <strong id="clc-online-count">—</strong>
        </div>
        <div class="clc-admin__chat-toggle">
            <span class="clc-admin__stat-label"><?php esc_html_e( 'User Chat:', 'carno-livechat' ); ?></span>
            <button id="clc-toggle-chat" class="button clc-admin__toggle-btn<?php echo get_option( 'carno_livechat_chat_enabled' ) ? ' clc-admin__toggle-btn--on' : ''; ?>">
                <?php echo get_option( 'carno_livechat_chat_enabled' ) ? esc_html__( 'Enabled', 'carno-livechat' ) : esc_html__( 'Disabled', 'carno-livechat' ); ?>
            </button>
        </div>
        <div class="clc-admin__chat-toggle">
            <span class="clc-admin__stat-label"><?php esc_html_e( 'Live Mode:', 'carno-livechat' ); ?></span>
            <button id="clc-toggle-live-mode" class="button clc-admin__toggle-btn<?php echo get_option( 'carno_livechat_live_mode' ) ? ' clc-admin__toggle-btn--on' : ''; ?>">
                <?php echo get_option( 'carno_livechat_live_mode' ) ? esc_html__( 'Enabled', 'carno-livechat' ) : esc_html__( 'Disabled', 'carno-livechat' ); ?>
            </button>
        </div>
        <?php $chat_mode = get_option( 'carno_livechat_chat_mode', 'public' ); ?>
        <div class="clc-admin__chat-toggle">
            <span class="clc-admin__stat-label"><?php esc_html_e( 'Chat Mode:', 'carno-livechat' ); ?></span>
            <div class="clc-admin__mode-btns">
                <button data-mode="public" class="button clc-admin__mode-btn<?php echo $chat_mode === 'public' ? ' clc-admin__mode-btn--active' : ''; ?>">
                    <?php esc_html_e( 'Public', 'carno-livechat' ); ?>
                </button>
                <button data-mode="private" class="button clc-admin__mode-btn<?php echo $chat_mode === 'private' ? ' clc-admin__mode-btn--active' : ''; ?>">
                    <?php esc_html_e( 'Private', 'carno-livechat' ); ?>
                </button>
            </div>
        </div>
    </div>

    <div class="clc-admin__settings-card">
        <div class="clc-admin__settings-row">
            <label for="clc-polling-interval" class="clc-admin__settings-label">
                <?php esc_html_e( 'Polling Interval (seconds):', 'carno-livechat' ); ?>
            </label>
            <input
                type="number"
                id="clc-polling-interval"
                value="<?php echo (int) get_option( 'carno_livechat_polling_interval', 10 ); ?>"
                min="5"
                max="60"
                step="1"
                class="clc-admin__settings-input"
            />
            <button id="clc-save-polling-interval" class="button">
                <?php esc_html_e( 'Save', 'carno-livechat' ); ?>
            </button>
            <span id="clc-polling-feedback" class="clc-admin__feedback" style="display:inline-block;margin:0 8px;"></span>
        </div>
        <p class="clc-admin__settings-desc">
            <?php esc_html_e( 'هر چند ثانیه یک بار مرورگر کاربران برای دریافت پیام‌های جدید با سرور ارتباط می‌گیرد. عدد کوچک‌تر = پیام‌ها سریع‌تر می‌رسند اما فشار بیشتری روی سرور. عدد بزرگ‌تر = سرور راحت‌تر اما پیام‌ها با تأخیر بیشتری نمایش داده می‌شوند.', 'carno-livechat' ); ?>
        </p>
        <p class="clc-admin__settings-hint">
            <?php esc_html_e( '💡 مقدار پیشنهادی: ۱۰ ثانیه — تعادل مناسبی بین سرعت و فشار سرور. اگه بیش از ۱۰۰ کاربر همزمان دارید عدد را به ۱۵ تا ۲۰ افزایش دهید.', 'carno-livechat' ); ?>
        </p>
    </div>

    <div class="clc-admin__shortcode-info">
        <strong><?php esc_html_e( 'Shortcode:', 'carno-livechat' ); ?></strong>
        <code>[livechat]</code>
        <span class="clc-admin__shortcode-hint">
            <?php esc_html_e( 'Optional attributes:', 'carno-livechat' ); ?>
            <code>[livechat title="عنوان دلخواه" placeholder="نام شما"]</code>
        </span>
    </div>

    <div class="clc-admin__form">
        <label for="clc-broadcast-message">
            <?php esc_html_e( 'Broadcast Message', 'carno-livechat' ); ?>
        </label>
        <textarea
            id="clc-broadcast-message"
            rows="5"
            placeholder="<?php esc_attr_e( 'Type your broadcast message here...', 'carno-livechat' ); ?>"
        ></textarea>
        <button id="clc-broadcast-send" class="button button-primary">
            <?php esc_html_e( 'Send Broadcast', 'carno-livechat' ); ?>
        </button>
        <p id="clc-broadcast-feedback" class="clc-admin__feedback"></p>
    </div>

    <div class="clc-admin__message-list-wrap">
        <div class="clc-admin__message-list-header">
            <h2><?php esc_html_e( 'Sent Messages', 'carno-livechat' ); ?></h2>
            <button id="clc-delete-all" class="button clc-admin__btn-danger">
                <?php esc_html_e( 'Delete All', 'carno-livechat' ); ?>
            </button>
        </div>
        <div id="clc-message-list">
            <p class="clc-admin__list-empty"><?php esc_html_e( 'No messages yet.', 'carno-livechat' ); ?></p>
        </div>
    </div>

    <div class="clc-admin__user-list-wrap">
        <div class="clc-admin__message-list-header">
            <h2><?php esc_html_e( 'Registered Users', 'carno-livechat' ); ?></h2>
            <div style="display:flex;gap:8px;">
                <button id="clc-export-users" class="button">
                    <?php esc_html_e( 'Export CSV', 'carno-livechat' ); ?>
                </button>
                <button id="clc-refresh-users" class="button">
                    <?php esc_html_e( 'Refresh', 'carno-livechat' ); ?>
                </button>
                <button id="clc-delete-all-users" class="button clc-admin__btn-danger">
                    <?php esc_html_e( 'Delete All Users', 'carno-livechat' ); ?>
                </button>
            </div>
        </div>
        <table class="clc-admin__user-table widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Name', 'carno-livechat' ); ?></th>
                    <th><?php esc_html_e( 'Phone', 'carno-livechat' ); ?></th>
                    <th><?php esc_html_e( 'First Visit', 'carno-livechat' ); ?></th>
                    <th><?php esc_html_e( 'Last Visit', 'carno-livechat' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'carno-livechat' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'carno-livechat' ); ?></th>
                </tr>
            </thead>
            <tbody id="clc-user-list">
                <tr><td colspan="6" class="clc-admin__list-empty"><?php esc_html_e( 'No users yet.', 'carno-livechat' ); ?></td></tr>
            </tbody>
        </table>
        <div id="clc-user-pagination" class="clc-admin__pagination"></div>
    </div>

</div>
