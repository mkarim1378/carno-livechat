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

</div>
