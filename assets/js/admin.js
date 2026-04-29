(function () {
    'use strict';

    function post(data, onSuccess, onError) {
        var body = Object.keys(data).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
        }).join('&');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', CarnoLivechatAdmin.ajax_url, true);
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
    }

    // -------------------------------------------------------------------------
    // Online count
    // -------------------------------------------------------------------------

    function fetchOnlineCount() {
        post(
            { action: 'livechat_online_count', nonce: CarnoLivechatAdmin.nonce },
            function (res) {
                if (res.success) {
                    var el = document.getElementById('clc-online-count');
                    if (el) el.textContent = res.data.count;
                }
            }
        );
    }

    // -------------------------------------------------------------------------
    // User list + pagination
    // -------------------------------------------------------------------------

    var _usersPage = 1;

    function renderUserList(users) {
        var tbody = document.getElementById('clc-user-list');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!users || !users.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="clc-admin__list-empty">No users yet.</td></tr>';
            return;
        }

        users.forEach(function (user) {
            var tr = document.createElement('tr');

            var tdName = document.createElement('td');
            tdName.textContent = user.name;

            var tdFirst = document.createElement('td');
            tdFirst.textContent = formatTime(user.created_at);

            var tdLast = document.createElement('td');
            tdLast.textContent = formatTime(user.last_seen);

            var tdStatus = document.createElement('td');
            var badge = document.createElement('span');
            badge.textContent = user.is_online === '1' ? 'Online' : 'Offline';
            badge.className   = user.is_online === '1' ? 'clc-admin__badge clc-admin__badge--online' : 'clc-admin__badge clc-admin__badge--offline';
            tdStatus.appendChild(badge);

            tr.appendChild(tdName);
            tr.appendChild(tdFirst);
            tr.appendChild(tdLast);
            tr.appendChild(tdStatus);
            tbody.appendChild(tr);
        });
    }

    function renderUserPagination(page, totalPages) {
        var container = document.getElementById('clc-user-pagination');
        if (!container) return;

        container.innerHTML = '';

        if (totalPages <= 1) return;

        var prev = document.createElement('button');
        prev.className   = 'button clc-admin__page-btn';
        prev.textContent = '←';
        prev.disabled    = page <= 1;
        prev.addEventListener('click', function () {
            if (_usersPage > 1) fetchUsers(_usersPage - 1);
        });

        var info = document.createElement('span');
        info.className   = 'clc-admin__page-info';
        info.textContent = page + ' / ' + totalPages;

        var next = document.createElement('button');
        next.className   = 'button clc-admin__page-btn';
        next.textContent = '→';
        next.disabled    = page >= totalPages;
        next.addEventListener('click', function () {
            if (_usersPage < totalPages) fetchUsers(_usersPage + 1);
        });

        container.appendChild(prev);
        container.appendChild(info);
        container.appendChild(next);
    }

    function fetchUsers(page) {
        _usersPage = page || 1;
        post(
            { action: 'livechat_get_users', nonce: CarnoLivechatAdmin.nonce, page: _usersPage },
            function (res) {
                if (res.success) {
                    renderUserList(res.data.users);
                    renderUserPagination(res.data.page, res.data.total_pages);
                }
            }
        );
    }

    // -------------------------------------------------------------------------
    // Message list
    // -------------------------------------------------------------------------

    function formatTime(datetime) {
        if (!datetime) return '';
        var parts = datetime.split(' ');
        return parts.length >= 2 ? parts[0] + ' ' + parts[1].substring(0, 5) : datetime;
    }

    function renderMessageList(messages) {
        var list = document.getElementById('clc-message-list');
        if (!list) return;

        list.innerHTML = '';

        if (!messages || !messages.length) {
            list.innerHTML = '<p class="clc-admin__list-empty">No messages yet.</p>';
            return;
        }

        messages.forEach(function (msg) {
            var row = document.createElement('div');
            row.className = 'clc-admin__message-row';
            row.dataset.id = msg.id;

            var text = document.createElement('span');
            text.className = 'clc-admin__message-text';
            text.textContent = msg.message;

            var meta = document.createElement('span');
            meta.className = 'clc-admin__message-meta';
            meta.textContent = formatTime(msg.created_at);

            var btn = document.createElement('button');
            btn.className = 'button clc-admin__btn-danger clc-admin__btn-delete';
            btn.textContent = 'حذف';
            btn.dataset.id = msg.id;

            btn.addEventListener('click', function () {
                deleteMessage(parseInt(btn.dataset.id, 10), row);
            });

            row.appendChild(text);
            row.appendChild(meta);
            row.appendChild(btn);
            list.appendChild(row);
        });
    }

    function fetchMessages() {
        post(
            { action: 'livechat_admin_get_messages', nonce: CarnoLivechatAdmin.nonce },
            function (res) {
                if (res.success) renderMessageList(res.data.messages);
            }
        );
    }

    function deleteMessage(id, rowEl) {
        post(
            { action: 'livechat_delete_message', nonce: CarnoLivechatAdmin.nonce, message_id: id },
            function (res) {
                if (res.success && rowEl) {
                    rowEl.parentNode.removeChild(rowEl);
                    var list = document.getElementById('clc-message-list');
                    if (list && !list.querySelector('.clc-admin__message-row')) {
                        list.innerHTML = '<p class="clc-admin__list-empty">No messages yet.</p>';
                    }
                }
            }
        );
    }

    // -------------------------------------------------------------------------
    // Broadcast send
    // -------------------------------------------------------------------------

    document.addEventListener('DOMContentLoaded', function () {
        var btn      = document.getElementById('clc-broadcast-send');
        var textarea = document.getElementById('clc-broadcast-message');
        var feedback = document.getElementById('clc-broadcast-feedback');
        var deleteAllBtn = document.getElementById('clc-delete-all');

        if (btn && textarea && feedback) {
            btn.addEventListener('click', function () {
                var message = textarea.value.trim();
                if (!message) { textarea.focus(); return; }

                btn.disabled    = true;
                feedback.textContent = '';
                feedback.className   = 'clc-admin__feedback';

                post(
                    { action: 'livechat_broadcast', nonce: CarnoLivechatAdmin.nonce, message: message },
                    function (res) {
                        if (res.success) {
                            textarea.value       = '';
                            feedback.textContent = 'پیام با موفقیت ارسال شد.';
                            feedback.className   = 'clc-admin__feedback clc-admin__feedback--ok';
                            fetchMessages();
                        } else {
                            feedback.textContent = 'خطا در ارسال پیام.';
                            feedback.className   = 'clc-admin__feedback clc-admin__feedback--error';
                        }
                        btn.disabled = false;
                    },
                    function () {
                        feedback.textContent = 'خطا در ارسال پیام.';
                        feedback.className   = 'clc-admin__feedback clc-admin__feedback--error';
                        btn.disabled = false;
                    }
                );
            });
        }

        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', function () {
                if (!window.confirm('Are you sure you want to delete all messages? This cannot be undone.')) return;

                post(
                    { action: 'livechat_delete_all_messages', nonce: CarnoLivechatAdmin.nonce },
                    function (res) {
                        if (res.success) fetchMessages();
                    }
                );
            });
        }

        var refreshUsersBtn = document.getElementById('clc-refresh-users');
        if (refreshUsersBtn) {
            refreshUsersBtn.addEventListener('click', fetchUsers);
        }

        fetchOnlineCount();
        setInterval(fetchOnlineCount, 10000);
        fetchUsers();
        fetchMessages();
    });

}());
