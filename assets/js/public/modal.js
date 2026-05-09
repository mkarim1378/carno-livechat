(function () {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Modal = {

        show: function () {
            var modal = document.getElementById('clc-modal');
            if (modal) modal.style.display = 'flex';
        },

        hide: function () {
            var modal = document.getElementById('clc-modal');
            if (modal) modal.style.display = 'none';
        },

        init: function (onSuccess) {
            var btn   = document.getElementById('clc-name-submit');
            var input = document.getElementById('clc-name-input');
            var error = document.getElementById('clc-name-error');

            if (!btn || !input) return;

            input.focus();

            var persianOnly = /[^\u0600-\u065F\u0670-\u06EF\u200C\u200D\s]/g;

            input.addEventListener('input', function () {
                var before = input.value;
                var after  = before.replace(persianOnly, '');
                if (before !== after) input.value = after;
            });

            btn.addEventListener('click', function () {
                var name = input.value.trim();

                if (!name) {
                    input.focus();
                    return;
                }

                if (persianOnly.test(name) || !/[\u0600-\u06FF]/.test(name)) {
                    if (error) error.textContent = 'لطفاً فقط حروف فارسی وارد کنید.';
                    input.focus();
                    return;
                }

                if (error) error.textContent = '';
                btn.disabled = true;

                var sessionId = CarnoLC.Session.generateId();

                CarnoLC._post(
                    CarnoLivechat.ajax_url,
                    {
                        action:     'livechat_register',
                        nonce:      CarnoLivechat.nonce,
                        name:       name,
                        session_id: sessionId,
                        page_url:   window.location.href
                    },
                    function (res) {
                        if (res.success) {
                            CarnoLC.Session.save(name, sessionId);
                            CarnoLC.Modal.hide();
                            onSuccess(name, sessionId);
                        } else {
                            btn.disabled = false;
                        }
                    },
                    function () {
                        btn.disabled = false;
                    }
                );
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') btn.click();
            });
        }
    };

}());
