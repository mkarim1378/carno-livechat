(function () {
    'use strict';

    function startChat(name, sessionId, showWelcome) {
        var chat = document.getElementById('clc-chat');
        if (chat) chat.classList.add('clc-chat--visible');
        CarnoLC.Heartbeat.start(sessionId);
        if (showWelcome) CarnoLC.Chat.renderWelcome(name);
        CarnoLC.Polling.start();
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
                startChat(name, sessionId, true);
            });
        }
    });

}());
