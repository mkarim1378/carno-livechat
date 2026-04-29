# Changelog

All notable changes to Carno LiveChat are documented here.
Versioning follows [Semantic Versioning](https://semver.org/): MINOR for new features, PATCH for fixes.

---

## [2.5.1] - 2026-04-29

- Fixed newlines in broadcast messages being collapsed to a single line — added `white-space: pre-wrap` to `.clc-message__text` in `public.css` and `.clc-admin__message-text` in `admin.css`

---

## [2.5.0] - 2026-04-29

- Added `Database::count_all_users()` — returns total user count for pagination math
- Updated `Database::get_all_users()` — added `$offset` parameter; default limit changed from 100 to 30
- Updated `Admin_Ajax::get_users()` — accepts `page` POST param; returns 30 users per page with `total`, `page`, `total_pages` in response
- Added AJAX pagination to Registered Users table in `admin.js` — `fetchUsers(page)` tracks `_usersPage`; `renderUserPagination()` renders prev/next buttons and page indicator; pagination hidden when total pages ≤ 1
- Added `#clc-user-pagination` container to `broadcast-panel.php`
- Moved Sent Messages box above Registered Users box in `broadcast-panel.php`
- Added `.clc-admin__pagination`, `.clc-admin__page-btn`, `.clc-admin__page-info` styles in `admin.css`
- Bumped plugin version to 2.5.0

---

## [2.4.0] - 2026-04-29

- Fixed broadcast messages containing URLs — `chat.js` now uses `_renderText()` helper that detects `http(s)://` URLs via regex and creates `<a target="_blank" rel="noopener noreferrer">` elements programmatically (XSS-safe; no innerHTML); plain text segments become `TextNode`s
- Added `.clc-message__text a` style in `public.css` — brand color link with `word-break: break-all` for long URLs
- Bumped plugin version to 2.4.0

---

## [2.3.0] - 2026-04-29

- Added `CarnoLC.Chat.renderWelcome(name)` in `chat.js` — renders a welcome bubble identical in structure and style to broadcast messages (`.clc-message` with text + HH:MM meta); appended after existing messages so broadcast history always comes first
- Added `_onFirstFetch` callback support to `Polling.start(onFirstFetch)` in `polling.js` — fires once after the first AJAX response completes then nulls itself; used to defer the welcome message until history is loaded
- Updated `startChat()` in `main.js` — passes `renderWelcome` as `onFirstFetch` callback so welcome always appears after any pre-existing broadcast messages; shown on every page load for both new and returning users
- Removed `.clc-message--welcome` custom style from `public.css` — welcome bubble now inherits standard `.clc-message` styling

---

## [2.2.1] - 2026-04-27

- Fixed duplicate empty-state accumulation — `removeDeleted` now checks for an existing `.clc-chat__empty` element before inserting a new one, preventing the "هنوز پیامی ارسال نشده است" message from stacking on every poll cycle when the chat is empty

---

## [2.2.0] - 2026-04-26

- Switched message deletion from hard-delete to soft-delete via `is_deleted` column on `wp_livechat_messages`
- Added `is_deleted TINYINT(1) DEFAULT 0` to messages table schema
- Added `Database::maybe_upgrade()` — checks for `is_deleted` column via `SHOW COLUMNS` and runs `ALTER TABLE` if missing; called on every plugin load for zero-downtime schema migration
- Updated `Database::delete_message()` — now `UPDATE SET is_deleted = 1` instead of `DELETE`
- Updated `Database::delete_all_messages()` — now `UPDATE SET is_deleted = 1` instead of `TRUNCATE`
- Updated `Database::get_messages_since()` — filters `WHERE is_deleted = 0`
- Updated `Database::get_all_messages()` — filters `WHERE is_deleted = 0`
- Added `Database::get_deleted_ids()` — returns all IDs where `is_deleted = 1`
- Updated `Public_Ajax::get_messages()` — now returns `{ messages, deleted_ids }` in response
- Updated `chat.js` — added `data-id` attribute to bubbles; added `removeDeleted(ids)` method that removes bubbles by data-id and restores empty state if no messages remain
- Updated `polling.js` — after each fetch, calls `CarnoLC.Chat.removeDeleted(deleted_ids)` so deleted messages disappear from active users' view within one poll cycle (~5s)

---

## [2.1.0] - 2026-04-26

- Added `Database::get_all_users($limit)` — fetches users ordered by `last_seen DESC` with computed `is_online` column (1 if last_seen within 60s)
- Added `Admin_Ajax::get_users()` — nonce + cap validated, returns up to 100 users
- Wired `wp_ajax_livechat_get_users` in `class-plugin.php`
- Updated `broadcast-panel.php` — added "Registered Users" table section with Name, First Visit, Last Visit, Status columns and a Refresh button
- Updated `admin.js` — `fetchUsers()` fetches and renders user rows; online/offline badge per user; Refresh button wired; users fetched on page load
- Updated `admin.css` — user table styles, online badge (green), offline badge (grey)

---

## [2.0.0] - 2026-04-26

- Added `Database::get_all_messages($limit)` — fetches messages ordered newest first for admin display
- Added `Database::delete_message($id)` — deletes single message by ID via `$wpdb->delete()`
- Added `Database::delete_all_messages()` — truncates messages table
- Added `Admin_Ajax::get_messages()` — nonce + cap validated, returns last 50 messages
- Added `Admin_Ajax::delete_message()` — nonce + cap validated, deletes single message by POST `message_id`
- Added `Admin_Ajax::delete_all_messages()` — nonce + cap validated, truncates table; requires `confirm()` in JS
- Wired three new AJAX actions in `class-plugin.php`: `livechat_admin_get_messages`, `livechat_delete_message`, `livechat_delete_all_messages`
- Updated `broadcast-panel.php` — added "Sent Messages" section with message list container and "Delete All" button
- Updated `admin.js` — `fetchMessages()` loads and renders message list; each row has inline delete button; after broadcast send, list refreshes; "Delete All" shows native `confirm()` dialog before executing
- Updated `admin.css` — message row layout, danger button style, empty state, meta timestamp styling

---

## [1.9.3] - 2026-04-26

- Fixed modal stuck open on returning visits — root cause: `.clc-modal` had `display: flex` in CSS by default, so it was always visible on page load; for returning visitors `Modal.hide()` was never called, leaving the modal visible but without any event listener on the button
- Changed `.clc-modal` CSS default to `display: none`; `Modal.show()` sets it to `display: flex` only for new visitors
- Added explicit `CarnoLC.Modal.hide()` call in `main.js` for returning visitors as an additional safeguard

---

## [1.9.2] - 2026-04-26

- Removed jQuery dependency from all JS files — plugin now uses zero third-party JS
- Added `CarnoLC._post()` XHR helper to `session.js` — shared by all modules that need AJAX
- Rewrote `modal.js` — vanilla DOM events, `XMLHttpRequest` for register call
- Rewrote `heartbeat.js` — vanilla `setInterval` + `CarnoLC._post()`
- Rewrote `chat.js` — `createElement`/`textContent` for XSS-safe bubble rendering, `removeChild` for empty state removal
- Rewrote `polling.js` — vanilla `CarnoLC._post()`, `_fetching` flag preserved
- Rewrote `main.js` — `document.addEventListener('DOMContentLoaded')`, `classList.add()` for chat visibility
- Rewrote `admin.js` — vanilla XHR helper, `DOMContentLoaded`, `textContent` for feedback
- Removed `'jquery'` from all `wp_enqueue_script()` dependency arrays in `class-public.php` and `class-admin.php`

---

## [1.9.1] - 2026-04-26

- Fixed duplicate message rendering in `polling.js` — added `_fetching` flag that blocks a new AJAX request if the previous one is still in-flight; flag is released in `.always()` regardless of success or failure

---

## [1.9.0] - 2026-04-26

**Security**
- Added UUID v4 format validation (`is_valid_uuid()`) in `class-public-ajax.php` — `register_user()` and `heartbeat()` reject malformed session IDs with 400
- Added `mb_strlen` name length check (max 100 chars) in `register_user()`
- Added `mb_strlen` message length check (max 2000 chars) in `send_broadcast()`
- Completed `uninstall.php` — drops `wp_livechat_users` and `wp_livechat_messages` tables and clears cron on uninstall

**Optimization**
- Created `includes/class-cron.php` — `Carno_Livechat_Cron::run_cleanup()` calls `Database::delete_inactive_users(24)`
- Added `Database::delete_inactive_users($hours)` — deletes users with `last_seen` older than N hours
- Scheduled daily WP-Cron event `carno_livechat_cleanup` on activation; unscheduled on deactivation
- Wired `carno_livechat_cleanup` action in `class-plugin.php`

**UI Polish**
- Removed `style="display:none"` from `#clc-chat` in template — visibility now controlled via CSS class
- Added `.clc-chat { display:none }` default and `.clc-chat--visible { display:flex; opacity:1; transform:none }` with fade+slide-up transition (0.3s)
- `main.js` now uses `addClass('clc-chat--visible')` instead of inline style
- Added `.clc-chat__empty` empty state message — "هنوز پیامی ارسال نشده است"
- `chat.js` removes empty state element on first message render

---

## [1.8.0] - 2026-04-26

- Updated `render_shortcode()` in `class-public.php` to accept `$atts` — supports `title` and `placeholder` attributes via `shortcode_atts()` with safe defaults
- Both attributes sanitized with `sanitize_text_field()` before passing to template
- Updated `templates/public/chat-widget.php` — header title and name input placeholder now dynamic via `$title` / `$placeholder` variables with fallback defaults
- Added shortcode usage panel to `templates/admin/broadcast-panel.php` — shows `[livechat]` and optional attributes example
- Added `.clc-admin__shortcode-info` styles to `admin.css` — info box with code highlighting
- Shortcode can now be placed on any page; title and placeholder are fully customizable: `[livechat title="عنوان دلخواه" placeholder="نام شما"]`

---

## [1.7.0] - 2026-04-26

- Created `admin/class-admin.php` — registers admin menu page under `dashicons-megaphone`, enqueues admin assets only on the plugin's own admin page via `$page_hook` comparison
- Created `admin/class-admin-ajax.php` — `send_broadcast()` validates nonce + `manage_options` cap, sanitizes message, inserts via `Database::insert_message()`; `get_online_count()` returns count of users active in last 60s
- Created `templates/admin/broadcast-panel.php` — online user counter display, broadcast textarea, send button, feedback message area
- Written `assets/css/admin.css` — stats bar, broadcast form, ok/error feedback colors
- Written `assets/js/admin.js` — sends broadcast via AJAX, shows Persian success/error feedback, polls online count every 10s; all strings stay in the DOM for i18n
- Wired admin hooks in `class-plugin.php`: `admin_menu`, `admin_enqueue_scripts`, `wp_ajax_livechat_broadcast`, `wp_ajax_livechat_online_count`
- Loaded `admin/class-admin.php` and `admin/class-admin-ajax.php` in `load_dependencies()`

---

## [1.6.0] - 2026-04-26

- Added `get_messages()` to `Carno_Livechat_Public_Ajax` — nonce-validated, reads `last_id` from POST, returns messages via `Database::get_messages_since()`
- Wired `livechat_get_messages` AJAX action for both `nopriv` and authenticated users in `class-plugin.php`
- Created `assets/js/public/chat.js` — `CarnoLC.Chat.render()` appends message bubbles using `.text()` (XSS-safe), `scrollToBottom()` auto-scrolls message list, `_formatTime()` formats MySQL datetime to HH:MM
- Created `assets/js/public/polling.js` — `CarnoLC.Polling.start()` fetches on load then polls every 5s, tracks `_lastId` to request only new messages, updates `_lastId` after each successful fetch
- Updated `assets/js/public/main.js` — `startChat()` now calls `CarnoLC.Polling.start()`
- Updated `enqueue_scripts()` in `class-public.php` — adds `clc-chat` and `clc-polling` to dependency chain before `main.js`

---

## [1.5.0] - 2026-04-26

- Created `public/class-public-ajax.php` with `Carno_Livechat_Public_Ajax` class
- Implemented `register_user()` — nonce-validated, sanitizes name/session_id/page_url, upserts user via `Database::insert_user()`
- Implemented `heartbeat()` — nonce-validated, updates `last_seen` via `Database::update_last_seen()`
- Implemented `get_client_ip()` — checks CF, X-Forwarded-For, X-Real-IP, REMOTE_ADDR in order; validates with `filter_var(FILTER_VALIDATE_IP)`
- Wired four AJAX actions in `class-plugin.php`: `livechat_register` and `livechat_heartbeat` for both `nopriv` and authenticated users
- Created `assets/js/public/session.js` — UUID v4 generator, localStorage read/write/clear via `CarnoLC.Session`
- Created `assets/js/public/modal.js` — shows modal, handles name submit + Enter key, calls `livechat_register`, saves session on success
- Created `assets/js/public/heartbeat.js` — `CarnoLC.Heartbeat.start(sessionId)` pings `livechat_heartbeat` every 20s
- Updated `assets/js/public/main.js` — on DOM ready: checks localStorage for existing session (returning visitor) or shows modal (new visitor), then boots heartbeat
- Updated `enqueue_scripts()` in `class-public.php` — enqueues `session.js`, `modal.js`, `heartbeat.js`, `main.js` with proper dependency chain via `jquery`

---

## [1.4.0] - 2026-04-26

- Created `templates/public/chat-widget.php` — full RTL chat UI template with name modal and chat container
- Modal: name input + submit button, all strings i18n-wrapped with `esc_html_e()` / `esc_attr_e()`
- Chat container: header, scrollable message list (`aria-live="polite"`), disabled footer input
- Disabled input placeholder: `گفتگو غیرفعال شده است`
- Written full `assets/css/public.css` — modal overlay, chat container, message bubbles, disabled input, RTL layout, scrollbar styling
- Added `render_shortcode()` to `Carno_Livechat_Public` — outputs template via `ob_start()`
- Added `register_shortcode()` to `Carno_Livechat_Public` — registers `[livechat]` shortcode
- Wired `init` hook for shortcode registration in `class-plugin.php`

---

## [1.3.0] - 2026-04-26

- Created `public/class-public.php` with `Carno_Livechat_Public` class
- Implemented `enqueue_styles()` — enqueues `public.css` only on pages containing `[livechat]` shortcode
- Implemented `enqueue_scripts()` — enqueues `main.js` only on pages containing `[livechat]` shortcode
- Script localization via `wp_localize_script()` exposes `CarnoLivechat` object: `ajax_url`, `nonce`, `polling_interval` (5000ms), `heartbeat_interval` (20000ms)
- Added `is_livechat_page()` helper — guards enqueue via `has_shortcode()` check
- Wired `wp_enqueue_scripts` hooks for styles and scripts in `class-plugin.php`
- Created placeholder asset files: `assets/css/public.css`, `assets/css/admin.css`, `assets/js/public/main.js`, `assets/js/admin.js`

---

## [1.2.0] - 2026-04-26

- Created `includes/class-loader.php` — hook/filter registration manager (`add_action`, `add_filter`, `run()`)
- Refactored `includes/class-plugin.php` — added `load_dependencies()`, `define_admin_hooks()`, `define_public_hooks()` methods
- `load_dependencies()` centralizes all `require_once` calls; future module requires are pre-stubbed as comments
- `define_admin_hooks()` and `define_public_hooks()` are structured stubs ready to wire modules in upcoming phases
- Added `get_loader()` accessor to expose the loader instance
- Loader is instantiated inside `load_dependencies()` — not in the main file

---

## [1.1.0] - 2026-04-26

- Created `database/class-database.php` with full `Carno_Livechat_Database` class
- Implemented `create_tables()` using `dbDelta()` — creates `wp_livechat_users` and `wp_livechat_messages`
- `wp_livechat_users` schema: `id`, `name`, `session_id` (UNIQUE), `page_url`, `ip_address`, `created_at`, `last_seen` — indexed on `session_id` and `last_seen`
- `wp_livechat_messages` schema: `id`, `message`, `sent_by`, `created_at` — indexed on `created_at`
- Implemented `insert_user()` — inserts new user or updates `last_seen` if session exists (upsert)
- Implemented `update_last_seen()` — heartbeat handler
- Implemented `count_online_users()` — counts users with `last_seen` within last 60 seconds
- Implemented `insert_message()` — stores admin broadcast message
- Implemented `get_messages_since()` — fetches messages after a given `last_id`, limit 50
- Updated `Carno_Livechat_Activator::activate()` to call `create_tables()` on plugin activation

---

## [1.0.0] - 2026-04-26

- Created main plugin entry file `carno-livechat.php` with WordPress plugin headers
- Defined core constants: `CARNO_LIVECHAT_VERSION`, `CARNO_LIVECHAT_FILE`, `CARNO_LIVECHAT_PATH`, `CARNO_LIVECHAT_URL`, `CARNO_LIVECHAT_BASENAME`
- Created `Carno_Livechat` bootstrap class — `includes/class-plugin.php`
- Created `Carno_Livechat_Activator` stub — `includes/class-activator.php`
- Created `Carno_Livechat_Deactivator` stub — `includes/class-deactivator.php`
- Registered `register_activation_hook` and `register_deactivation_hook` in main file
- Created `uninstall.php` stub for clean plugin removal
