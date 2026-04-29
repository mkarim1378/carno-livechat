(function () {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Polling = {

        _lastId:       0,
        _timer:        null,
        _fetching:     false,
        _onFirstFetch: null,

        start: function (onFirstFetch) {
            this._onFirstFetch = onFirstFetch || null;
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
            if (this._fetching) return;

            this._fetching = true;
            var self = this;

            CarnoLC._post(
                CarnoLivechat.ajax_url,
                {
                    action:  'livechat_get_messages',
                    nonce:   CarnoLivechat.nonce,
                    last_id: self._lastId
                },
                function (res) {
                    if (res.success) {
                        if (res.data.messages && res.data.messages.length) {
                            CarnoLC.Chat.render(res.data.messages);
                            self._lastId = parseInt(res.data.messages[res.data.messages.length - 1].id, 10);
                        }

                        if (res.data.deleted_ids && res.data.deleted_ids.length) {
                            CarnoLC.Chat.removeDeleted(res.data.deleted_ids);
                        }
                    }

                    if (self._onFirstFetch) {
                        self._onFirstFetch();
                        self._onFirstFetch = null;
                    }

                    self._fetching = false;
                },
                function () {
                    self._fetching = false;
                }
            );
        }
    };

}());
