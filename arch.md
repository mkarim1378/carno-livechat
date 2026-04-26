# Architecture Design — Broadcast Chat Plugin

---

## 1. Folder Structure

```
carno-livechat/
├── carno-livechat.php                  # Main plugin entry point
├── uninstall.php                       # Cleanup on uninstall
│
├── includes/
│   ├── class-plugin.php                # Bootstrap: loads all modules, registers hooks
│   ├── class-loader.php                # Hook/filter registration manager
│   ├── class-activator.php             # Activation logic (DB creation)
│   ├── class-deactivator.php           # Deactivation cleanup
│   └── class-i18n.php                  # Internationalization
│
├── database/
│   └── class-database.php             # All DB queries (users + messages)
│
├── admin/
│   ├── class-admin.php                # Admin menu, page rendering
│   └── class-admin-ajax.php           # Admin-side AJAX (send broadcast, get user count)
│
├── public/
│   ├── class-public.php               # Shortcode, frontend hook registration
│   └── class-public-ajax.php          # Public-side AJAX (register user, heartbeat, get messages)
│
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── public.css
│   └── js/
│       ├── admin.js
│       └── public/
│           ├── main.js                # Entry: initializes all modules
│           ├── modal.js               # Name collection modal
│           ├── session.js             # Session ID + localStorage
│           ├── chat.js                # Message rendering + auto-scroll
│           ├── polling.js             # Message polling loop
│           └── heartbeat.js           # Heartbeat loop
│
└── templates/
    ├── admin/
    │   └── broadcast-panel.php        # Admin broadcast UI template
    └── public/
        └── chat-widget.php            # Chat widget HTML template
```

---

## 2. Class Architecture & Responsibilities

| Class | Responsibility |
|---|---|
| `Carno_Livechat` | Bootstrap — loads all classes, wires hooks via Loader, defines constants |
| `Carno_Livechat_Loader` | Collects all `add_action` / `add_filter` calls, executes them on `run()` |
| `Carno_Livechat_Activator` | Creates `wp_livechat_users` and `wp_livechat_messages` tables with indexes |
| `Carno_Livechat_Deactivator` | Deactivation hook (tables preserved; optional transient cleanup) |
| `Carno_Livechat_Database` | Single DB abstraction — all `$wpdb` queries centralized here, zero SQL elsewhere |
| `Carno_Livechat_Admin` | Registers admin menu, enqueues admin assets, renders broadcast panel template |
| `Carno_Livechat_Admin_Ajax` | Handles `wp_ajax_livechat_broadcast` and `wp_ajax_livechat_online_count` |
| `Carno_Livechat_Public` | Registers `[livechat]` shortcode, enqueues public assets, localizes JS vars |
| `Carno_Livechat_Public_Ajax` | Handles `wp_ajax_nopriv_livechat_register`, `_heartbeat`, `_get_messages` |

---

## 3. Data Flow

### Visitor — First Visit
```
Browser loads page with [livechat] shortcode
  → Public::shortcode() outputs chat-widget.php template
  → main.js boots → checks localStorage for session_id
  → No session found → modal.js shows name modal
  → User submits name → session.js generates UUID
  → AJAX POST: livechat_register
      → Public_Ajax::register_user()
      → Database::insert_user(name, session_id, page_url, ip)
      → Response: { success: true, session_id }
  → session.js saves session_id to localStorage
  → chat.js reveals chat UI
  → polling.js starts  (last_id = 0 → fetches full history)
  → heartbeat.js starts
```

### Visitor — Returning Visit
```
Browser loads page
  → session_id found in localStorage → no modal shown
  → AJAX POST: livechat_register (upsert → updates last_seen)
  → polling.js starts (last_id = 0 → loads history)
```

### Polling Loop — every 5s
```
polling.js
  → AJAX POST: livechat_get_messages { last_id: N }
      → Public_Ajax::get_messages()
      → Database::get_messages_since(last_id)
      → Response: [{ id, message, created_at }]
  → chat.js renders new messages, updates last_id, auto-scrolls
```

### Heartbeat — every 20s
```
heartbeat.js
  → AJAX POST: livechat_heartbeat { session_id }
      → Public_Ajax::heartbeat()
      → Database::update_last_seen(session_id)
```

### Admin Broadcast
```
Admin types message → clicks Send
  → AJAX POST: livechat_broadcast (admin nonce)
      → Admin_Ajax::send_broadcast()
      → Database::insert_message(message, 'admin')
      → Response: { success: true }
  → Admin panel polls livechat_online_count every 10s
      → Database::count_online_users(within: 60s)
```

---

## 4. Database Schema

### `wp_livechat_users`

```sql
id           BIGINT UNSIGNED  AUTO_INCREMENT  PRIMARY KEY
name         VARCHAR(100)     NOT NULL
session_id   VARCHAR(64)      NOT NULL  UNIQUE
page_url     TEXT
ip_address   VARCHAR(45)
created_at   DATETIME         DEFAULT CURRENT_TIMESTAMP
last_seen    DATETIME         DEFAULT CURRENT_TIMESTAMP

INDEX (session_id)
INDEX (last_seen)
```

### `wp_livechat_messages`

```sql
id           BIGINT UNSIGNED  AUTO_INCREMENT  PRIMARY KEY
message      TEXT             NOT NULL
sent_by      VARCHAR(100)     DEFAULT 'admin'
created_at   DATETIME         DEFAULT CURRENT_TIMESTAMP

INDEX (id)
INDEX (created_at)
```

---

## 5. Security Considerations

| Concern | Mitigation |
|---|---|
| Unauthenticated AJAX abuse | Nonce on all public AJAX calls; IP-based rate limiting at DB level |
| XSS in messages | `esc_html()` on all output; `wp_kses_post()` if HTML is needed |
| SQL injection | Exclusively `$wpdb->prepare()` inside the Database class |
| Admin broadcast protection | `check_ajax_referer()` + `current_user_can('manage_options')` |
| Session spoofing | UUID is client-generated; server treats it as identifier only, no privilege implied |
| IP logging | `$_SERVER['REMOTE_ADDR']` with awareness of reverse proxy headers |

---

## 6. Performance Considerations

- Assets enqueued **only when `[livechat]` shortcode is present** on the page (`has_shortcode` check)
- Polling query: `WHERE id > last_id` — index scan, scales well on large tables
- Online count query: `WHERE last_seen > NOW() - INTERVAL 60 SECOND` — covered by `last_seen` index
- Initial message history capped at **last 50 messages**
- No transients needed — queries are already lightweight
- Inactive user cleanup via optional **WP-Cron** scheduled job

---

## 7. Suggested Improvements

- **WP-Cron cleanup** — purge users with `last_seen` older than 24h to keep the table lean
- **Configurable history limit** — admin setting for max messages shown (default: 50)
- **Soft delete** — `is_deleted` flag on messages so admin can retract a broadcast
- **i18n-ready from day one** — all strings in `__()` / `_e()`, RTL-compatible for Persian UI

---

## 8. Phase Mapping

| Phase | Description | Files Involved |
|---|---|---|
| 1 | Project Initialization | `carno-livechat.php`, `class-plugin.php`, constants, activation hooks |
| 2 | Database Layer | `class-activator.php`, `class-database.php`, both tables + indexes |
| 3 | Core Architecture | `class-loader.php`, full module wiring in `class-plugin.php` |
| 4 | Asset System | `class-public.php` enqueue logic with shortcode detection |
| 5 | Chat UI | `chat-widget.php`, `public.css`, `chat.js`, `modal.js` |
| 6 | User Session | `session.js`, `livechat_register` AJAX handler |
| 7 | Message Polling | `polling.js`, `livechat_get_messages` AJAX handler |
| 8 | Admin Panel | `class-admin.php`, `class-admin-ajax.php`, `broadcast-panel.php` |
| 9 | Shortcode | `Public::register_shortcode()` |
| 10 | Security & Optimization | Nonces, sanitization, WP-Cron cleanup, query limits |
