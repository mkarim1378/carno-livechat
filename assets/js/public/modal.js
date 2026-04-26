(function ($) {
    'use strict';

    window.CarnoLC = window.CarnoLC || {};

    CarnoLC.Modal = {

        show: function () {
            $('#clc-modal').css('display', 'flex');
        },

        hide: function () {
            $('#clc-modal').hide();
        },

        init: function (onSuccess) {
            var $btn   = $('#clc-name-submit');
            var $input = $('#clc-name-input');

            if (!$btn.length || !$input.length) {
                return;
            }

            $input.focus();

            $btn.on('click', function () {
                var name = $input.val().trim();

                if (!name) {
                    $input.focus();
                    return;
                }

                $btn.prop('disabled', true);

                var sessionId = CarnoLC.Session.generateId();

                $.post(CarnoLivechat.ajax_url, {
                    action:     'livechat_register',
                    nonce:      CarnoLivechat.nonce,
                    name:       name,
                    session_id: sessionId,
                    page_url:   window.location.href
                })
                .done(function (res) {
                    if (res.success) {
                        CarnoLC.Session.save(name, sessionId);
                        CarnoLC.Modal.hide();
                        onSuccess(name, sessionId);
                    } else {
                        $btn.prop('disabled', false);
                    }
                })
                .fail(function () {
                    $btn.prop('disabled', false);
                });
            });

            $input.on('keydown', function (e) {
                if (e.key === 'Enter') {
                    $btn.trigger('click');
                }
            });
        }
    };

}(jQuery));
