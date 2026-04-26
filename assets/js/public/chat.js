(function () {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Chat = {

        render: function (messages) {
            var list = document.getElementById('clc-messages');
            if (!list || !messages.length) return;

            var empty = list.querySelector('.clc-chat__empty');
            if (empty) empty.parentNode.removeChild(empty);

            messages.forEach(function (msg) {
                var bubble = document.createElement('div');
                bubble.className = 'clc-message';

                var text = document.createElement('span');
                text.className = 'clc-message__text';
                text.textContent = msg.message;

                var meta = document.createElement('span');
                meta.className = 'clc-message__meta';
                meta.textContent = CarnoLC.Chat._formatTime(msg.created_at);

                bubble.appendChild(text);
                bubble.appendChild(meta);
                list.appendChild(bubble);
            });

            CarnoLC.Chat.scrollToBottom();
        },

        scrollToBottom: function () {
            var list = document.getElementById('clc-messages');
            if (list) list.scrollTop = list.scrollHeight;
        },

        _formatTime: function (datetime) {
            if (!datetime) return '';
            var parts = datetime.split(' ');
            if (parts.length < 2) return '';
            var t = parts[1].split(':');
            return t[0] + ':' + t[1];
        }
    };

}());
