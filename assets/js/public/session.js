(function () {
    'use strict';

    var STORAGE_KEY = 'carno_lc_session';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC._post = function (url, data, onSuccess, onError) {
        var body = Object.keys(data).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
        }).join('&');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (onSuccess) onSuccess(res);
                } catch (e) {
                    if (onError) onError();
                }
            } else {
                if (onError) onError();
            }
        };

        xhr.onerror = function () {
            if (onError) onError();
        };

        xhr.send(body);
    };

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
