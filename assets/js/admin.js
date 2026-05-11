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
            tbody.innerHTML = '<tr><td colspan="5" class="clc-admin__list-empty">No users yet.</td></tr>';
            return;
        }

        users.forEach(function (user) {
            var tr = document.createElement('tr');
            var isBanned = user.is_banned === '1' || user.is_banned === 1;

            var tdName = document.createElement('td');
            tdName.textContent = user.name;

            var tdFirst = document.createElement('td');
            tdFirst.textContent = formatTime(user.created_at);

            var tdLast = document.createElement('td');
            tdLast.textContent = formatTime(user.last_seen);

            var tdStatus = document.createElement('td');
            var badge = document.createElement('span');
            if (isBanned) {
                badge.textContent = 'Banned';
                badge.className   = 'clc-admin__badge clc-admin__badge--banned';
            } else if (user.is_online === '1') {
                badge.textContent = 'Online';
                badge.className   = 'clc-admin__badge clc-admin__badge--online';
            } else {
                badge.textContent = 'Offline';
                badge.className   = 'clc-admin__badge clc-admin__badge--offline';
            }
            tdStatus.appendChild(badge);

            var tdActions = document.createElement('td');

            var delMsgsBtn = document.createElement('button');
            delMsgsBtn.className   = 'button clc-admin__btn-danger clc-admin__btn-sm';
            delMsgsBtn.textContent = 'حذف پیام‌ها';
            delMsgsBtn.addEventListener('click', function () {
                deleteUserMessages(user.session_id);
            });
            tdActions.appendChild(delMsgsBtn);

            var banBtn = document.createElement('button');
            if (isBanned) {
                banBtn.className   = 'button clc-admin__btn-sm';
                banBtn.textContent = 'آزاد کردن';
                banBtn.addEventListener('click', function () {
                    unbanUser(user.session_id, tr, badge, banBtn);
                });
            } else {
                banBtn.className   = 'button clc-admin__btn-danger clc-admin__btn-sm';
                banBtn.textContent = 'بن کردن';
                banBtn.addEventListener('click', function () {
                    banUser(user.session_id, tr, badge, banBtn);
                });
            }
            tdActions.appendChild(banBtn);

            tr.appendChild(tdName);
            tr.appendChild(tdFirst);
            tr.appendChild(tdLast);
            tr.appendChild(tdStatus);
            tr.appendChild(tdActions);
            tbody.appendChild(tr);
        });
    }

    function banUser(sessionId, tr, badge, btn) {
        btn.disabled = true;
        post(
            { action: 'livechat_ban_user', nonce: CarnoLivechatAdmin.nonce, session_id: sessionId },
            function (res) {
                if (res.success) {
                    badge.textContent = 'Banned';
                    badge.className   = 'clc-admin__badge clc-admin__badge--banned';
                    btn.textContent   = 'آزاد کردن';
                    btn.className     = 'button clc-admin__btn-sm';
                    btn.disabled      = false;
                    btn.onclick = null;
                    btn.addEventListener('click', function () {
                        unbanUser(sessionId, tr, badge, btn);
                    });
                } else {
                    btn.disabled = false;
                }
            },
            function () { btn.disabled = false; }
        );
    }

    function unbanUser(sessionId, tr, badge, btn) {
        btn.disabled = true;
        post(
            { action: 'livechat_unban_user', nonce: CarnoLivechatAdmin.nonce, session_id: sessionId },
            function (res) {
                if (res.success) {
                    badge.textContent = 'Offline';
                    badge.className   = 'clc-admin__badge clc-admin__badge--offline';
                    btn.textContent   = 'بن کردن';
                    btn.className     = 'button clc-admin__btn-danger clc-admin__btn-sm';
                    btn.disabled      = false;
                    btn.onclick = null;
                    btn.addEventListener('click', function () {
                        banUser(sessionId, tr, badge, btn);
                    });
                } else {
                    btn.disabled = false;
                }
            },
            function () { btn.disabled = false; }
        );
    }

    function deleteUserMessages(sessionId) {
        post(
            { action: 'livechat_delete_user_messages', nonce: CarnoLivechatAdmin.nonce, session_id: sessionId },
            function (res) {
                if (res.success) fetchMessages();
            }
        );
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
            var isUserMsg = msg.session_id && msg.session_id !== '';

            var row = document.createElement('div');
            row.className  = 'clc-admin__message-row' + (isUserMsg ? ' clc-admin__message-row--user' : '');
            row.dataset.id = msg.id;

            if (isUserMsg) {
                var senderBadge = document.createElement('span');
                senderBadge.className   = 'clc-admin__user-badge';
                senderBadge.textContent = msg.chat_mode === 'private'
                    ? '(خصوصی) ' + msg.sent_by
                    : msg.sent_by;
                row.appendChild(senderBadge);
            }

            var text = document.createElement('span');
            text.className   = 'clc-admin__message-text';
            text.textContent = msg.message;

            var meta = document.createElement('span');
            meta.className   = 'clc-admin__message-meta';
            meta.textContent = formatTime(msg.created_at);

            var delBtn = document.createElement('button');
            delBtn.className   = 'button clc-admin__btn-danger clc-admin__btn-delete';
            delBtn.textContent = 'حذف';
            delBtn.dataset.id  = msg.id;
            delBtn.addEventListener('click', function () {
                deleteMessage(parseInt(delBtn.dataset.id, 10), row);
            });

            row.appendChild(text);
            row.appendChild(meta);

            if (isUserMsg) {
                var delAllBtn = document.createElement('button');
                delAllBtn.className   = 'button clc-admin__btn-sm';
                delAllBtn.textContent = 'حذف همه پیام‌های این کاربر';
                delAllBtn.addEventListener('click', function () {
                    deleteUserMessages(msg.session_id);
                });
                row.appendChild(delAllBtn);
            }

            row.appendChild(delBtn);
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

        document.querySelectorAll('.clc-admin__mode-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var mode = btn.dataset.mode;
                if (btn.classList.contains('clc-admin__mode-btn--active')) return;

                document.querySelectorAll('.clc-admin__mode-btn').forEach(function (b) {
                    b.disabled = true;
                });

                post(
                    { action: 'livechat_set_chat_mode', nonce: CarnoLivechatAdmin.nonce, mode: mode },
                    function (res) {
                        if (res.success) {
                            document.querySelectorAll('.clc-admin__mode-btn').forEach(function (b) {
                                b.classList.toggle('clc-admin__mode-btn--active', b.dataset.mode === mode);
                                b.disabled = false;
                            });
                        } else {
                            document.querySelectorAll('.clc-admin__mode-btn').forEach(function (b) {
                                b.disabled = false;
                            });
                        }
                    },
                    function () {
                        document.querySelectorAll('.clc-admin__mode-btn').forEach(function (b) {
                            b.disabled = false;
                        });
                    }
                );
            });
        });

        var savePollingBtn = document.getElementById('clc-save-polling-interval');
        if (savePollingBtn) {
            savePollingBtn.addEventListener('click', function () {
                var input    = document.getElementById('clc-polling-interval');
                var feedback = document.getElementById('clc-polling-feedback');
                var seconds  = parseInt(input.value, 10);
                if (!seconds || seconds < 5 || seconds > 60) {
                    feedback.textContent = 'عدد باید بین ۵ تا ۶۰ باشد.';
                    feedback.className   = 'clc-admin__feedback clc-admin__feedback--error';
                    return;
                }
                savePollingBtn.disabled = true;
                post(
                    { action: 'livechat_save_polling_interval', nonce: CarnoLivechatAdmin.nonce, seconds: seconds },
                    function (res) {
                        if (res.success) {
                            feedback.textContent = 'ذخیره شد.';
                            feedback.className   = 'clc-admin__feedback clc-admin__feedback--ok';
                        } else {
                            feedback.textContent = 'خطا.';
                            feedback.className   = 'clc-admin__feedback clc-admin__feedback--error';
                        }
                        savePollingBtn.disabled = false;
                        setTimeout(function () { feedback.textContent = ''; }, 3000);
                    },
                    function () {
                        feedback.textContent = 'خطا.';
                        feedback.className   = 'clc-admin__feedback clc-admin__feedback--error';
                        savePollingBtn.disabled = false;
                    }
                );
            });
        }

        var toggleLiveModeBtn = document.getElementById('clc-toggle-live-mode');
        if (toggleLiveModeBtn) {
            toggleLiveModeBtn.addEventListener('click', function () {
                toggleLiveModeBtn.disabled = true;
                post(
                    { action: 'livechat_toggle_live_mode', nonce: CarnoLivechatAdmin.nonce },
                    function (res) {
                        if (res.success) {
                            var enabled = res.data.live_mode;
                            toggleLiveModeBtn.textContent = enabled ? 'Enabled' : 'Disabled';
                            toggleLiveModeBtn.classList.toggle('clc-admin__toggle-btn--on', enabled);
                        }
                        toggleLiveModeBtn.disabled = false;
                    },
                    function () { toggleLiveModeBtn.disabled = false; }
                );
            });
        }

        var toggleChatBtn = document.getElementById('clc-toggle-chat');
        if (toggleChatBtn) {
            toggleChatBtn.addEventListener('click', function () {
                toggleChatBtn.disabled = true;
                post(
                    { action: 'livechat_toggle_chat', nonce: CarnoLivechatAdmin.nonce },
                    function (res) {
                        if (res.success) {
                            var enabled = res.data.chat_enabled;
                            toggleChatBtn.textContent = enabled ? 'Enabled' : 'Disabled';
                            toggleChatBtn.classList.toggle('clc-admin__toggle-btn--on', enabled);
                        }
                        toggleChatBtn.disabled = false;
                    },
                    function () {
                        toggleChatBtn.disabled = false;
                    }
                );
            });
        }

        fetchOnlineCount();
        setInterval(fetchOnlineCount, 10000);
        fetchUsers();
        fetchMessages();
    });

}());
