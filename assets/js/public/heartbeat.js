(function () {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Heartbeat = {

        _timer: null,

        start: function (sessionId) {
            var interval = CarnoLivechat.heartbeat_interval || 20000;

            this._timer = setInterval(function () {
                CarnoLC._post(CarnoLivechat.ajax_url, {
                    action:     'livechat_heartbeat',
                    nonce:      CarnoLivechat.nonce,
                    session_id: sessionId
                });
            }, interval);
        },

        stop: function () {
            if (this._timer) {
                clearInterval(this._timer);
                this._timer = null;
            }
        }
    };

}());
