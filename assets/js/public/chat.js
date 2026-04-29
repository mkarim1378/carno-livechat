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
                bubble.className    = 'clc-message';
                bubble.dataset.id   = msg.id;

                var text = document.createElement('span');
                text.className   = 'clc-message__text';
                text.textContent = msg.message;

                var meta = document.createElement('span');
                meta.className   = 'clc-message__meta';
                meta.textContent = CarnoLC.Chat._formatTime(msg.created_at);

                bubble.appendChild(text);
                bubble.appendChild(meta);
                list.appendChild(bubble);
            });

            CarnoLC.Chat.scrollToBottom();
        },

        removeDeleted: function (deletedIds) {
            if (!deletedIds || !deletedIds.length) return;

            var list = document.getElementById('clc-messages');
            if (!list) return;

            deletedIds.forEach(function (id) {
                var bubble = list.querySelector('[data-id="' + id + '"]');
                if (bubble) bubble.parentNode.removeChild(bubble);
            });

            if (!list.querySelector('.clc-message') && !list.querySelector('.clc-chat__empty')) {
                var empty = document.createElement('p');
                empty.className   = 'clc-chat__empty';
                empty.textContent = 'هنوز پیامی ارسال نشده است.';
                list.appendChild(empty);
            }
        },

        renderWelcome: function (name) {
            var list = document.getElementById('clc-messages');
            if (!list) return;

            var empty = list.querySelector('.clc-chat__empty');
            if (empty) empty.parentNode.removeChild(empty);

            var now = new Date();
            var h   = String(now.getHours()).padStart(2, '0');
            var m   = String(now.getMinutes()).padStart(2, '0');

            var bubble = document.createElement('div');
            bubble.className = 'clc-message';

            var text = document.createElement('span');
            text.className   = 'clc-message__text';
            text.textContent = 'سلام ' + name + ' عزیز، خوش آمدی!';

            var meta = document.createElement('span');
            meta.className   = 'clc-message__meta';
            meta.textContent = h + ':' + m;

            bubble.appendChild(text);
            bubble.appendChild(meta);
            list.appendChild(bubble);

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
