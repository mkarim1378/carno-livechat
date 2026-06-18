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
            var nameInput  = document.getElementById('clc-name-input');
            var nameBtn    = document.getElementById('clc-name-submit');
            var nameError  = document.getElementById('clc-name-error');
            var stepName   = document.getElementById('clc-step-name');
            var phoneInput = document.getElementById('clc-phone-input');
            var phoneBtn   = document.getElementById('clc-phone-submit');
            var phoneError = document.getElementById('clc-phone-error');
            var stepPhone  = document.getElementById('clc-step-phone');

            if (!nameInput || !nameBtn) return;

            // Separate filter regex (with g) from test regex (without g) to avoid
            // stateful lastIndex bugs when calling .test() on a /g regex.
            var persianFilter = /[^؀-ٰٟ-ۯ‌‍\s]/g;
            var persianTest   = /[^؀-ٰٟ-ۯ‌‍\s]/;
            var hasPersian    = /[؀-ۿ]/;
            var phoneTest     = /^09[0-9]{9}$/;

            var validatedName = '';

            // --- Step 1: Name ---

            nameInput.addEventListener('input', function () {
                var cleaned = nameInput.value.replace(persianFilter, '');
                if (nameInput.value !== cleaned) nameInput.value = cleaned;
            });

            function submitName() {
                var name = nameInput.value.trim();

                if (!name) {
                    nameInput.focus();
                    return;
                }

                if (persianTest.test(name) || !hasPersian.test(name)) {
                    if (nameError) nameError.textContent = 'لطفاً فقط حروف فارسی وارد کنید.';
                    nameInput.focus();
                    return;
                }

                if (nameError) nameError.textContent = '';
                validatedName = name;

                stepName.style.display = 'none';
                stepPhone.style.display = '';
                if (phoneInput) phoneInput.focus();
            }

            nameBtn.addEventListener('click', submitName);
            nameInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') submitName();
            });

            nameInput.focus();

            // --- Step 2: Phone ---

            if (!phoneInput || !phoneBtn) return;

            phoneInput.addEventListener('input', function () {
                phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '');
            });

            function submitPhone() {
                var phone = phoneInput.value.trim();

                if (!phoneTest.test(phone)) {
                    if (phoneError) phoneError.textContent = 'شماره موبایل معتبر نیست (مثال: 09123456789)';
                    phoneInput.focus();
                    return;
                }

                if (phoneError) phoneError.textContent = '';
                phoneBtn.disabled = true;

                var sessionId = CarnoLC.Session.generateId();

                CarnoLC._post(
                    CarnoLivechat.ajax_url,
                    {
                        action:     'livechat_register',
                        nonce:      CarnoLivechat.nonce,
                        name:       validatedName,
                        phone:      phone,
                        session_id: sessionId,
                        page_url:   window.location.href
                    },
                    function (res) {
                        if (res.success) {
                            CarnoLC.Session.save(validatedName, sessionId, phone);
                            CarnoLC.Modal.hide();
                            onSuccess(validatedName, sessionId);
                        } else {
                            phoneBtn.disabled = false;
                        }
                    },
                    function () {
                        phoneBtn.disabled = false;
                    }
                );
            }

            phoneBtn.addEventListener('click', submitPhone);
            phoneInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') submitPhone();
            });
        }
    };

}());
