<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Database {

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
            page_url   TEXT,
            ip_address VARCHAR(45),
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
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_users );
        dbDelta( $sql_messages );
    }

    // -------------------------------------------------------------------------
    // Users
    // -------------------------------------------------------------------------

    public static function insert_user( $name, $session_id, $page_url, $ip_address ) {
        global $wpdb;

        $existing = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . self::users_table() . ' WHERE session_id = %s',
                $session_id
            )
        );

        if ( $existing ) {
            $wpdb->update(
                self::users_table(),
                [ 'last_seen' => current_time( 'mysql' ) ],
                [ 'session_id' => $session_id ],
                [ '%s' ],
                [ '%s' ]
            );
            return (int) $existing;
        }

        $wpdb->insert(
            self::users_table(),
            [
                'name'       => sanitize_text_field( $name ),
                'session_id' => sanitize_text_field( $session_id ),
                'page_url'   => esc_url_raw( $page_url ),
                'ip_address' => sanitize_text_field( $ip_address ),
                'created_at' => current_time( 'mysql' ),
                'last_seen'  => current_time( 'mysql' ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s' ]
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

    public static function count_online_users() {
        global $wpdb;

        return (int) $wpdb->get_var(
            'SELECT COUNT(*) FROM ' . self::users_table() .
            " WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 60 SECOND)"
        );
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

    public static function get_messages_since( $last_id = 0, $limit = 50 ) {
        global $wpdb;

        $last_id = absint( $last_id );
        $limit   = absint( $limit );

        if ( $last_id === 0 ) {
            return $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT id, message, sent_by, created_at FROM ' . self::messages_table() .
                    ' ORDER BY id ASC LIMIT %d',
                    $limit
                )
            );
        }

        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, message, sent_by, created_at FROM ' . self::messages_table() .
                ' WHERE id > %d ORDER BY id ASC LIMIT %d',
                $last_id,
                $limit
            )
        );
    }
}
