(function () {
    'use strict';

    var STORAGE_KEY = 'carno_lc_session';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Session = {

        get: function () {
            try {
                var raw = localStorage.getItem(STORAGE_KEY);
                return raw ? JSON.parse(raw) : null;
            } catch (e) {
                return null;
            }
        },

        save: function (name, sessionId) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    name:       name,
                    session_id: sessionId
                }));
            } catch (e) {}
        },

        clear: function () {
            localStorage.removeItem(STORAGE_KEY);
        },

        generateId: function () {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = Math.random() * 16 | 0;
                var v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
    };

}());
