(function () {
    'use strict';

    function post(data, onSuccess, onError) {
        var body = Object.keys(data).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
        }).join('&');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', CarnoLivechatAdmin.ajax_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (onSuccess) onSuccess(res);
                } catch (e) {
                    if (onError) onError();
                }
            } else {
                if (onError) onError();
            }
        };

        xhr.onerror = function () {
            if (onError) onError();
        };

        xhr.send(body);
    }

    function fetchOnlineCount() {
        post(
            { action: 'livechat_online_count', nonce: CarnoLivechatAdmin.nonce },
            function (res) {
                if (res.success) {
                    var el = document.getElementById('clc-online-count');
                    if (el) el.textContent = res.data.count;
                }
            }
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btn      = document.getElementById('clc-broadcast-send');
        var textarea = document.getElementById('clc-broadcast-message');
        var feedback = document.getElementById('clc-broadcast-feedback');

        if (!btn || !textarea || !feedback) return;

        btn.addEventListener('click', function () {
            var message = textarea.value.trim();

            if (!message) {
                textarea.focus();
                return;
            }

            btn.disabled = true;
            feedback.textContent = '';
            feedback.className   = 'clc-admin__feedback';

            post(
                {
                    action:  'livechat_broadcast',
                    nonce:   CarnoLivechatAdmin.nonce,
                    message: message
                },
                function (res) {
                    if (res.success) {
                        textarea.value       = '';
                        feedback.textContent = 'پیام با موفقیت ارسال شد.';
                        feedback.className   = 'clc-admin__feedback clc-admin__feedback--ok';
                    } else {
                        feedback.textContent = 'خطا در ارسال پیام.';
                        feedback.className   = 'clc-admin__feedback clc-admin__feedback--error';
                    }
                    btn.disabled = false;
                },
                function () {
                    feedback.textContent = 'خطا در ارسال پیام.';
                    feedback.className   = 'clc-admin__feedback clc-admin__feedback--error';
                    btn.disabled = false;
                }
            );
        });

        fetchOnlineCount();
        setInterval(fetchOnlineCount, 10000);
    });

}());
