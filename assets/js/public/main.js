(function () {
    'use strict';

    function startChat(name, sessionId) {
        var chat = document.getElementById('clc-chat');
        if (chat) chat.classList.add('clc-chat--visible');
        CarnoLC.Chat.setChatState(!!CarnoLivechat.chat_enabled);
        CarnoLC.Input.init(sessionId);
        CarnoLC.Polling.start(function () {
            CarnoLC.Chat.renderWelcome(name);
        });

        var input = document.getElementById('clc-chat-input');
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey && !input.disabled) {
                    e.preventDefault();
                    CarnoLC.Input.send();
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var chat  = document.getElementById('clc-chat');
        var modal = document.getElementById('clc-modal');

        if (!chat || !modal) return;

        var session = CarnoLC.Session.get();

        if (session && session.session_id) {
            CarnoLC.Modal.hide();

            CarnoLC._post(CarnoLivechat.ajax_url, {
                action:     'livechat_register',
                nonce:      CarnoLivechat.nonce,
                name:       session.name,
                session_id: session.session_id,
                page_url:   window.location.href
            });

            startChat(session.name, session.session_id);

        } else {
            CarnoLC.Modal.show();
            CarnoLC.Modal.init(function (name, sessionId) {
                startChat(name, sessionId);
            });
        }
    });

}());
