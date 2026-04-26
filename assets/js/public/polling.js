(function ($) {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Polling = {

        _lastId: 0,
        _timer:  null,

        start: function () {
            this._fetch();

            var self     = this;
            var interval = CarnoLivechat.polling_interval || 5000;

            this._timer = setInterval(function () {
                self._fetch();
            }, interval);
        },

        stop: function () {
            if (this._timer) {
                clearInterval(this._timer);
                this._timer = null;
            }
        },

        _fetch: function () {
            var self = this;

            $.post(CarnoLivechat.ajax_url, {
                action:  'livechat_get_messages',
                nonce:   CarnoLivechat.nonce,
                last_id: self._lastId
            })
            .done(function (res) {
                if (res.success && res.data.messages && res.data.messages.length) {
                    CarnoLC.Chat.render(res.data.messages);
                    self._lastId = parseInt(res.data.messages[res.data.messages.length - 1].id, 10);
                }
            });
        }
    };

}(jQuery));
