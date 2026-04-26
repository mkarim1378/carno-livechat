(function ($) {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Chat = {

        render: function (messages) {
            var $list = $('#clc-messages');
            if (!$list.length || !messages.length) {
                return;
            }

            $list.find('.clc-chat__empty').remove();

            $.each(messages, function (i, msg) {
                var $bubble = $(
                    '<div class="clc-message">' +
                        '<span class="clc-message__text"></span>' +
                        '<span class="clc-message__meta"></span>' +
                    '</div>'
                );
                $bubble.find('.clc-message__text').text(msg.message);
                $bubble.find('.clc-message__meta').text(CarnoLC.Chat._formatTime(msg.created_at));
                $list.append($bubble);
            });

            CarnoLC.Chat.scrollToBottom();
        },

        scrollToBottom: function () {
            var el = document.getElementById('clc-messages');
            if (el) {
                el.scrollTop = el.scrollHeight;
            }
        },

        _formatTime: function (datetime) {
            if (!datetime) {
                return '';
            }
            var parts = datetime.split(' ');
            if (parts.length < 2) {
                return '';
            }
            var t = parts[1].split(':');
            return t[0] + ':' + t[1];
        }
    };

}(jQuery));
