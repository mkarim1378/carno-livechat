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

    <div class="clc-admin__stats">
        <span class="clc-admin__stat-label"><?php esc_html_e( 'Online Users:', 'carno-livechat' ); ?></span>
        <strong id="clc-online-count">—</strong>
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
            <button id="clc-refresh-users" class="button">
                <?php esc_html_e( 'Refresh', 'carno-livechat' ); ?>
            </button>
        </div>
        <table class="clc-admin__user-table widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Name', 'carno-livechat' ); ?></th>
                    <th><?php esc_html_e( 'First Visit', 'carno-livechat' ); ?></th>
                    <th><?php esc_html_e( 'Last Visit', 'carno-livechat' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'carno-livechat' ); ?></th>
                </tr>
            </thead>
            <tbody id="clc-user-list">
                <tr><td colspan="4" class="clc-admin__list-empty"><?php esc_html_e( 'No users yet.', 'carno-livechat' ); ?></td></tr>
            </tbody>
        </table>
        <div id="clc-user-pagination" class="clc-admin__pagination"></div>
    </div>

</div>
