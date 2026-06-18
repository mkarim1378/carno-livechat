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

            <div id="clc-step-name">
                <p class="clc-modal__label"><?php esc_html_e( 'برای ورود نام خود را وارد کنید', 'carno-livechat' ); ?></p>
                <input
                    type="text"
                    id="clc-name-input"
                    placeholder="<?php echo esc_attr( $placeholder ); ?>"
                    maxlength="32"
                    autocomplete="off"
                />
                <button id="clc-name-submit" type="button">
                    <?php esc_html_e( 'مرحله بعد', 'carno-livechat' ); ?>
                </button>
                <p id="clc-name-error" class="clc-modal__error"></p>
            </div>

            <div id="clc-step-phone" style="display:none">
                <p class="clc-modal__label"><?php esc_html_e( 'شماره موبایل خود را وارد کنید', 'carno-livechat' ); ?></p>
                <input
                    type="tel"
                    id="clc-phone-input"
                    placeholder="09123456789"
                    maxlength="11"
                    autocomplete="off"
                    dir="ltr"
                />
                <button id="clc-phone-submit" type="button">
                    <?php esc_html_e( 'ورود به گفتگو', 'carno-livechat' ); ?>
                </button>
                <p id="clc-phone-error" class="clc-modal__error"></p>
            </div>

        </div>
    </div>

    <div id="clc-chat" class="clc-chat">
        <div class="clc-chat__header">
            <span class="clc-chat__title"><?php echo esc_html( $title ); ?></span>
            <span class="clc-chat__status"></span>
        </div>

        <div id="clc-messages" class="clc-chat__messages" role="log" aria-live="polite">
            <p class="clc-chat__empty"><?php esc_html_e( 'هنوز پیامی ارسال نشده است.', 'carno-livechat' ); ?></p>
        </div>

        <div class="clc-chat__footer">
            <input
                type="text"
                id="clc-chat-input"
                class="clc-chat__input"
                disabled
                placeholder="<?php esc_attr_e( 'گفتگو غیرفعال شده است', 'carno-livechat' ); ?>"
                data-disabled-placeholder="<?php esc_attr_e( 'گفتگو غیرفعال شده است', 'carno-livechat' ); ?>"
                data-active-placeholder="<?php esc_attr_e( 'پیام خود را بنویسید...', 'carno-livechat' ); ?>"
                autocomplete="off"
            />
            <button id="clc-send-btn" class="clc-chat__send" type="button" style="display:none">
                <?php esc_html_e( 'ارسال', 'carno-livechat' ); ?>
            </button>
            <p id="clc-ban-notice" class="clc-chat__ban-notice" style="display:none">
                <?php esc_html_e( 'شما از ارسال پیام محروم شده‌اید.', 'carno-livechat' ); ?>
            </p>
        </div>
    </div>

</div>
