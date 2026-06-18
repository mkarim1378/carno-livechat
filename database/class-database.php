<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Database {

    // Bump this whenever the schema or indexes change.
    const DB_VERSION = '1.3';

    private static function users_table() {
        global $wpdb;
        return $wpdb->prefix . 'livechat_users';
    }

    private static function messages_table() {
        global $wpdb;
        return $wpdb->prefix . 'livechat_messages';
    }

    // -------------------------------------------------------------------------
    // Table creation
    // -------------------------------------------------------------------------

    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $users_table    = self::users_table();
        $messages_table = self::messages_table();

        $sql_users = "CREATE TABLE IF NOT EXISTS {$users_table} (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name       VARCHAR(100)    NOT NULL,
            session_id VARCHAR(64)     NOT NULL,
            phone      VARCHAR(20)     NULL DEFAULT NULL,
            page_url   TEXT,
            ip_address VARCHAR(45),
            is_banned  TINYINT(1)      NOT NULL DEFAULT 0,
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_seen  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_session (session_id),
            KEY idx_last_seen (last_seen)
        ) {$charset_collate};";

        $sql_messages = "CREATE TABLE IF NOT EXISTS {$messages_table} (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            message    TEXT            NOT NULL,
            sent_by    VARCHAR(100)    NOT NULL DEFAULT 'admin',
            session_id VARCHAR(64)     NULL DEFAULT NULL,
            chat_mode  VARCHAR(10)     NULL DEFAULT NULL,
            is_deleted TINYINT(1)      NOT NULL DEFAULT 0,
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_created_at (created_at),
            KEY idx_session_id (session_id),
            KEY idx_deleted_id (is_deleted, id),
            KEY idx_deleted_created (is_deleted, created_at),
            KEY idx_session_created (session_id, created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_users );
        dbDelta( $sql_messages );
    }

    public static function maybe_upgrade() {
        // Fast path: one in-memory get_option() — no DB hit after first run.
        if ( get_option( 'carno_livechat_db_version' ) === self::DB_VERSION ) {
            return;
        }

        global $wpdb;

        $table       = self::messages_table();
        $users_table = self::users_table();

        // Add any columns that old installs may be missing.
        $columns = [
            [ $table,       'is_deleted', "TINYINT(1) NOT NULL DEFAULT 0" ],
            [ $table,       'session_id', "VARCHAR(64) NULL DEFAULT NULL" ],
            [ $table,       'chat_mode',  "VARCHAR(10) NULL DEFAULT NULL" ],
            [ $users_table, 'is_banned',  "TINYINT(1) NOT NULL DEFAULT 0" ],
            [ $users_table, 'phone',      "VARCHAR(20) NULL DEFAULT NULL" ],
        ];
        foreach ( $columns as [ $t, $col, $def ] ) {
            if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$t}` LIKE '{$col}'" ) ) {
                $wpdb->query( "ALTER TABLE `{$t}` ADD COLUMN `{$col}` {$def}" );
            }
        }

        // Add performance indexes that may be missing on old installs.
        self::add_index_if_missing( $table, 'idx_session_id',      'session_id' );
        self::add_index_if_missing( $table, 'idx_deleted_id',      'is_deleted, id' );
        self::add_index_if_missing( $table, 'idx_deleted_created', 'is_deleted, created_at' );
        self::add_index_if_missing( $table, 'idx_session_created', 'session_id, created_at' );

        update_option( 'carno_livechat_db_version', self::DB_VERSION );
    }

    private static function add_index_if_missing( $table, $index_name, $columns ) {
        global $wpdb;

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM information_schema.STATISTICS
                 WHERE table_schema = DATABASE() AND table_name = %s AND index_name = %s',
                $table,
                $index_name
            )
        );

        if ( ! $exists ) {
            $wpdb->query( "ALTER TABLE `{$table}` ADD INDEX `{$index_name}` ({$columns})" );
        }
    }

    // -------------------------------------------------------------------------
    // Users
    // -------------------------------------------------------------------------

    public static function insert_user( $name, $session_id, $page_url, $ip_address, $phone = '' ) {
        global $wpdb;

        $phone = sanitize_text_field( $phone );

        $existing = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . self::users_table() . ' WHERE session_id = %s',
                $session_id
            )
        );

        if ( $existing ) {
            $update_data   = [ 'last_seen' => current_time( 'mysql' ) ];
            $update_format = [ '%s' ];
            if ( $phone !== '' ) {
                $update_data['phone'] = $phone;
                $update_format[]      = '%s';
            }
            $wpdb->update(
                self::users_table(),
                $update_data,
                [ 'session_id' => $session_id ],
                $update_format,
                [ '%s' ]
            );
            return (int) $existing;
        }

        $wpdb->insert(
            self::users_table(),
            [
                'name'       => sanitize_text_field( $name ),
                'session_id' => sanitize_text_field( $session_id ),
                'phone'      => $phone !== '' ? $phone : null,
                'page_url'   => esc_url_raw( $page_url ),
                'ip_address' => sanitize_text_field( $ip_address ),
                'created_at' => current_time( 'mysql' ),
                'last_seen'  => current_time( 'mysql' ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        return (int) $wpdb->insert_id;
    }

    public static function update_last_seen( $session_id ) {
        global $wpdb;

        $wpdb->update(
            self::users_table(),
            [ 'last_seen' => current_time( 'mysql' ) ],
            [ 'session_id' => sanitize_text_field( $session_id ) ],
            [ '%s' ],
            [ '%s' ]
        );
    }

    public static function delete_inactive_users( $hours = 24 ) {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                'DELETE FROM ' . self::users_table() .
                ' WHERE last_seen < DATE_SUB(NOW(), INTERVAL %d HOUR)',
                absint( $hours )
            )
        );
    }

    public static function get_user_by_session( $session_id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT id, name, is_banned FROM ' . self::users_table() . ' WHERE session_id = %s LIMIT 1',
                sanitize_text_field( $session_id )
            )
        );
    }

    public static function count_all_users() {
        global $wpdb;
        return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::users_table() );
    }

    public static function get_all_users_for_export() {
        global $wpdb;

        return $wpdb->get_results(
            'SELECT name, phone, created_at, last_seen, is_banned, page_url, ip_address
             FROM ' . self::users_table() . ' ORDER BY created_at DESC',
            ARRAY_A
        );
    }

    public static function get_all_users( $limit = 30, $offset = 0 ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, name, session_id, phone, is_banned, created_at, last_seen,
                    CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 60 SECOND) THEN 1 ELSE 0 END AS is_online
                 FROM ' . self::users_table() .
                ' ORDER BY last_seen DESC LIMIT %d OFFSET %d',
                absint( $limit ),
                absint( $offset )
            )
        );
    }

    public static function ban_user( $session_id ) {
        global $wpdb;
        $wpdb->update(
            self::users_table(),
            [ 'is_banned'  => 1 ],
            [ 'session_id' => sanitize_text_field( $session_id ) ],
            [ '%d' ], [ '%s' ]
        );
    }

    public static function unban_user( $session_id ) {
        global $wpdb;
        $wpdb->update(
            self::users_table(),
            [ 'is_banned'  => 0 ],
            [ 'session_id' => sanitize_text_field( $session_id ) ],
            [ '%d' ], [ '%s' ]
        );
    }

    public static function is_user_banned( $session_id ) {
        global $wpdb;
        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT is_banned FROM ' . self::users_table() . ' WHERE session_id = %s LIMIT 1',
                sanitize_text_field( $session_id )
            )
        );
    }

    public static function delete_user_messages( $session_id ) {
        global $wpdb;
        $wpdb->update(
            self::messages_table(),
            [ 'is_deleted' => 1 ],
            [ 'session_id' => sanitize_text_field( $session_id ) ],
            [ '%d' ], [ '%s' ]
        );
        delete_transient( 'clc_deleted_ids' );
    }

    public static function count_online_users() {
        $cached = get_transient( 'clc_online_count' );
        if ( $cached !== false ) {
            return (int) $cached;
        }

        global $wpdb;

        $count = (int) $wpdb->get_var(
            'SELECT COUNT(*) FROM ' . self::users_table() .
            " WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 60 SECOND)"
        );

        set_transient( 'clc_online_count', $count, 15 );

        return $count;
    }

    // -------------------------------------------------------------------------
    // Messages
    // -------------------------------------------------------------------------

    public static function insert_message( $message, $sent_by = 'admin' ) {
        global $wpdb;

        $wpdb->insert(
            self::messages_table(),
            [
                'message'    => sanitize_textarea_field( $message ),
                'sent_by'    => sanitize_text_field( $sent_by ),
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%s', '%s', '%s' ]
        );

        return (int) $wpdb->insert_id;
    }

    public static function get_messages_since( $last_id = 0, $limit = 50, $viewer_session = null, $show_all = false ) {
        global $wpdb;

        $last_id = absint( $last_id );
        $limit   = absint( $limit );
        $table   = self::messages_table();

        $users_table = self::users_table();

        // Admin bypass: show every non-deleted message + phone from the sender.
        if ( $show_all ) {
            if ( $last_id === 0 ) {
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT m.id, m.message, m.sent_by, m.session_id, m.chat_mode, m.created_at, u.phone
                         FROM {$table} m
                         LEFT JOIN {$users_table} u ON u.session_id = m.session_id
                         WHERE m.is_deleted = 0 ORDER BY m.id ASC LIMIT %d",
                        $limit
                    )
                );
            }
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT m.id, m.message, m.sent_by, m.session_id, m.chat_mode, m.created_at, u.phone
                     FROM {$table} m
                     LEFT JOIN {$users_table} u ON u.session_id = m.session_id
                     WHERE m.id > %d AND m.is_deleted = 0 ORDER BY m.id ASC LIMIT %d",
                    $last_id, $limit
                )
            );
        }

        // Visibility rule (per-message chat_mode, set at send time):
        //   session_id IS NULL  → admin broadcast, always visible
        //   chat_mode = 'public' → user message sent while public, visible to all
        //   chat_mode = 'private' AND session_id = viewer → visible only to sender

        if ( $viewer_session !== null ) {
            $session = sanitize_text_field( $viewer_session );
            $where   = "is_deleted = 0 AND (
                            session_id IS NULL
                            OR chat_mode = 'public'
                            OR (chat_mode = 'private' AND session_id = %s)
                        )";
            if ( $last_id === 0 ) {
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT id, message, sent_by, session_id, chat_mode, created_at FROM {$table}
                         WHERE {$where} ORDER BY id ASC LIMIT %d",
                        $session, $limit
                    )
                );
            }
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, message, sent_by, session_id, chat_mode, created_at FROM {$table}
                     WHERE id > %d AND {$where} ORDER BY id ASC LIMIT %d",
                    $last_id, $session, $limit
                )
            );
        }

        // No session: show only admin messages and public user messages
        if ( $last_id === 0 ) {
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, message, sent_by, session_id, chat_mode, created_at FROM {$table}
                     WHERE is_deleted = 0 AND (session_id IS NULL OR chat_mode = 'public')
                     ORDER BY id ASC LIMIT %d",
                    $limit
                )
            );
        }

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, message, sent_by, session_id, chat_mode, created_at FROM {$table}
                 WHERE id > %d AND is_deleted = 0 AND (session_id IS NULL OR chat_mode = 'public')
                 ORDER BY id ASC LIMIT %d",
                $last_id, $limit
            )
        );
    }

    public static function insert_user_message( $message, $session_id, $user_name, $chat_mode = 'public' ) {
        global $wpdb;

        $wpdb->insert(
            self::messages_table(),
            [
                'message'    => sanitize_textarea_field( $message ),
                'sent_by'    => sanitize_text_field( $user_name ),
                'session_id' => sanitize_text_field( $session_id ),
                'chat_mode'  => in_array( $chat_mode, [ 'public', 'private' ], true ) ? $chat_mode : 'public',
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%s', '%s', '%s', '%s', '%s' ]
        );

        return (int) $wpdb->insert_id;
    }

    public static function count_recent_user_messages( $session_id, $seconds = 10 ) {
        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . self::messages_table() .
                ' WHERE session_id = %s AND is_deleted = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL %d SECOND)',
                sanitize_text_field( $session_id ),
                absint( $seconds )
            )
        );
    }

    public static function get_deleted_ids() {
        $cached = get_transient( 'clc_deleted_ids' );
        if ( $cached !== false ) {
            return $cached;
        }

        global $wpdb;

        $results = $wpdb->get_col(
            'SELECT id FROM ' . self::messages_table() .
            ' WHERE is_deleted = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)'
        );

        $ids = array_map( 'intval', $results );
        set_transient( 'clc_deleted_ids', $ids, 5 );

        return $ids;
    }

    public static function get_all_messages( $limit = 50 ) {
        global $wpdb;

        $table       = self::messages_table();
        $users_table = self::users_table();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT m.id, m.message, m.sent_by, m.session_id, m.chat_mode, m.created_at, u.phone
                 FROM {$table} m
                 LEFT JOIN {$users_table} u ON u.session_id = m.session_id
                 WHERE m.is_deleted = 0 ORDER BY m.id DESC LIMIT %d",
                absint( $limit )
            )
        );
    }

    public static function delete_message( $id ) {
        global $wpdb;

        $wpdb->update(
            self::messages_table(),
            [ 'is_deleted' => 1 ],
            [ 'id'         => absint( $id ) ],
            [ '%d' ],
            [ '%d' ]
        );

        delete_transient( 'clc_deleted_ids' );
    }

    public static function delete_all_messages() {
        global $wpdb;

        $wpdb->query( 'UPDATE ' . self::messages_table() . ' SET is_deleted = 1' );

        delete_transient( 'clc_deleted_ids' );
    }
}
