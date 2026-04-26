(function ($) {
    'use strict';

    var $count    = $('#clc-online-count');
    var $textarea = $('#clc-broadcast-message');
    var $btn      = $('#clc-broadcast-send');
    var $feedback = $('#clc-broadcast-feedback');

    function fetchOnlineCount() {
        $.post(CarnoLivechatAdmin.ajax_url, {
            action: 'livechat_online_count',
            nonce:  CarnoLivechatAdmin.nonce
        })
        .done(function (res) {
            if (res.success) {
                $count.text(res.data.count);
            }
        });
    }

    $btn.on('click', function () {
        var message = $textarea.val().trim();

        if (!message) {
            $textarea.focus();
            return;
        }

        $btn.prop('disabled', true);
        $feedback.text('').removeClass('clc-admin__feedback--ok clc-admin__feedback--error');

        $.post(CarnoLivechatAdmin.ajax_url, {
            action:  'livechat_broadcast',
            nonce:   CarnoLivechatAdmin.nonce,
            message: message
        })
        .done(function (res) {
            if (res.success) {
                $textarea.val('');
                $feedback.text('پیام با موفقیت ارسال شد.').addClass('clc-admin__feedback--ok');
            } else {
                $feedback.text('خطا در ارسال پیام.').addClass('clc-admin__feedback--error');
            }
        })
        .fail(function () {
            $feedback.text('خطا در ارسال پیام.').addClass('clc-admin__feedback--error');
        })
        .always(function () {
            $btn.prop('disabled', false);
        });
    });

    fetchOnlineCount();
    setInterval(fetchOnlineCount, 10000);

}(jQuery));
