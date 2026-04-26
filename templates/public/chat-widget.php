<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$title       = isset( $title )       ? $title       : __( 'پشتیبانی آنلاین', 'carno-livechat' );
$placeholder = isset( $placeholder ) ? $placeholder : __( 'نام شما', 'carno-livechat' );
?>
<div id="carno-livechat-wrap" dir="rtl">

    <div id="clc-modal" class="clc-modal">
        <div class="clc-modal__box">
            <p class="clc-modal__label"><?php esc_html_e( 'برای ورود نام خود را وارد کنید', 'carno-livechat' ); ?></p>
            <input
                type="text"
                id="clc-name-input"
                placeholder="<?php echo esc_attr( $placeholder ); ?>"
                maxlength="100"
                autocomplete="off"
            />
            <button id="clc-name-submit" type="button">
                <?php esc_html_e( 'ورود به گفتگو', 'carno-livechat' ); ?>
            </button>
        </div>
    </div>

    <div id="clc-chat" class="clc-chat" style="display:none;">
        <div class="clc-chat__header">
            <span class="clc-chat__title"><?php echo esc_html( $title ); ?></span>
            <span class="clc-chat__status"></span>
        </div>

        <div id="clc-messages" class="clc-chat__messages" role="log" aria-live="polite">
        </div>

        <div class="clc-chat__footer">
            <input
                type="text"
                class="clc-chat__input"
                disabled
                placeholder="<?php esc_attr_e( 'گفتگو غیرفعال شده است', 'carno-livechat' ); ?>"
            />
        </div>
    </div>

</div>
