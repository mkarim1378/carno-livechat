(function ($) {
    'use strict';

    function startChat(name, sessionId) {
        $('#clc-chat').css('display', 'flex');
        CarnoLC.Heartbeat.start(sessionId);
        CarnoLC.Polling.start();
    }

    $(document).ready(function () {
        var $chat  = $('#clc-chat');
        var $modal = $('#clc-modal');

        if (!$chat.length || !$modal.length) {
            return;
        }

        var session = CarnoLC.Session.get();

        if (session && session.session_id) {
            // Returning visitor: silently update last_seen
            $.post(CarnoLivechat.ajax_url, {
                action:     'livechat_register',
                nonce:      CarnoLivechat.nonce,
                name:       session.name,
                session_id: session.session_id,
                page_url:   window.location.href
            });

            startChat(session.name, session.session_id);

        } else {
            // New visitor: show name modal
            CarnoLC.Modal.show();
            CarnoLC.Modal.init(function (name, sessionId) {
                startChat(name, sessionId);
            });
        }
    });

}(jQuery));
