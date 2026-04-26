# Changelog

All notable changes to Carno LiveChat are documented here.
Versioning follows [Semantic Versioning](https://semver.org/): MINOR for new features, PATCH for fixes.

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
