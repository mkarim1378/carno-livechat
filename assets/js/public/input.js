(function () {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Input = {

        _sessionId: null,
        _sending:   false,

        init: function (sessionId) {
            this._sessionId = sessionId;

            var btn = document.getElementById('clc-send-btn');
            if (btn) {
                btn.addEventListener('click', function () {
                    CarnoLC.Input.send();
                });
            }
        },

        send: function () {
            if (this._sending) return;

            var input = document.getElementById('clc-chat-input');
            var btn   = document.getElementById('clc-send-btn');

            if (!input || input.disabled) return;

            var message = input.value.trim();
            if (!message) { input.focus(); return; }

            this._sending  = true;
            if (btn) btn.disabled = true;

            var self = this;

            CarnoLC._post(
                CarnoLivechat.ajax_url,
                {
                    action:     'livechat_send_message',
                    nonce:      CarnoLivechat.nonce,
                    session_id: self._sessionId,
                    message:    message
                },
                function (res) {
                    if (res.success) {
                        input.value = '';
                        CarnoLC.Chat.render([ res.data ]);
                        CarnoLC.Polling._lastId = Math.max(
                            CarnoLC.Polling._lastId,
                            parseInt(res.data.id, 10)
                        );
                    } else {
                        var code = res.data && res.data.message;
                        if (code === 'banned') {
                            CarnoLC.Chat.setBanned();
                        } else if (code === 'rate_limit') {
                            CarnoLC.Input._flashBtn(btn, 'کمی صبر کنید...');
                        }
                    }
                    self._sending = false;
                    if (btn) btn.disabled = false;
                    if (!input.disabled) input.focus();
                },
                function () {
                    self._sending = false;
                    if (btn) btn.disabled = false;
                }
            );
        },

        _flashBtn: function (btn, text) {
            if (!btn) return;
            var original = btn.textContent;
            btn.textContent = text;
            setTimeout(function () {
                btn.textContent = original;
            }, 2000);
        }
    };

}());
