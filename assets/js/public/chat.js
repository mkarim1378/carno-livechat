(function () {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Chat = {

        render: function (messages) {
            var list = document.getElementById('clc-messages');
            if (!list || !messages.length) return;

            var empty = list.querySelector('.clc-chat__empty');
            if (empty) empty.parentNode.removeChild(empty);

            var session   = CarnoLC.Session ? CarnoLC.Session.get() : null;
            var mySession = session ? session.session_id : null;

            messages.forEach(function (msg) {
                var isOwn  = msg.session_id && msg.session_id === mySession;
                var isUser = msg.session_id && msg.session_id !== mySession;

                var bubble = document.createElement('div');
                bubble.dataset.id = msg.id;
                bubble.className  = 'clc-message' +
                    (isOwn  ? ' clc-message--own'  : '') +
                    (isUser ? ' clc-message--user' : '');

                if (isUser) {
                    var sender = document.createElement('span');
                    sender.className   = 'clc-message__sender';
                    sender.textContent = msg.sent_by;
                    bubble.appendChild(sender);
                }

                var text = document.createElement('span');
                text.className = 'clc-message__text';
                CarnoLC.Chat._renderText(text, msg.message);

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

        setBanned: function () {
            var input = document.getElementById('clc-chat-input');
            var btn   = document.getElementById('clc-send-btn');
            if (input) {
                input.disabled     = true;
                input.placeholder  = 'شما از چت محروم شده‌اید';
            }
            if (btn) btn.style.display = 'none';
        },

        setChatState: function (enabled) {
            var input = document.getElementById('clc-chat-input');
            var btn   = document.getElementById('clc-send-btn');
            if (!input) return;

            if (enabled) {
                input.disabled     = false;
                input.placeholder  = input.dataset.activePlaceholder || '';
                if (btn) btn.style.display = '';
            } else {
                input.disabled     = true;
                input.placeholder  = input.dataset.disabledPlaceholder || '';
                if (btn) btn.style.display = 'none';
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

        _renderText: function (el, text) {
            var urlPattern = /https?:\/\/[^\s]+/g;
            var last = 0;
            var match;

            while ((match = urlPattern.exec(text)) !== null) {
                if (match.index > last) {
                    el.appendChild(document.createTextNode(text.slice(last, match.index)));
                }
                var a = document.createElement('a');
                a.href             = match[0];
                a.textContent      = match[0];
                a.target           = '_blank';
                a.rel              = 'noopener noreferrer';
                el.appendChild(a);
                last = match.index + match[0].length;
            }

            if (last < text.length) {
                el.appendChild(document.createTextNode(text.slice(last)));
            }
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
