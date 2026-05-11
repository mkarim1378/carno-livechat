<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Public_Ajax {

    public function register_user() {
        check_ajax_referer( 'carno_livechat_nonce', 'nonce' );

        $name       = isset( $_POST['name'] )       ? sanitize_text_field( wp_unslash( $_POST['name'] ) )       : '';
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $page_url   = isset( $_POST['page_url'] )   ? esc_url_raw( wp_unslash( $_POST['page_url'] ) )           : '';

        if ( empty( $name ) || empty( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid data.' ], 400 );
        }

        if ( mb_strlen( $name ) > 100 ) {
            wp_send_json_error( [ 'message' => 'Name too long.' ], 400 );
        }

        if ( ! preg_match( '/^[\x{0600}-\x{065F}\x{0670}-\x{06EF}\x{200C}\x{200D}\s]+$/u', $name ) ) {
            wp_send_json_error( [ 'message' => 'Name must contain only Persian characters.' ], 400 );
        }

        if ( ! $this->is_valid_uuid( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session format.' ], 400 );
        }

        $user_id = Carno_Livechat_Database::insert_user(
            $name,
            $session_id,
            $page_url,
            $this->get_client_ip()
        );

        wp_send_json_success( [ 'user_id' => $user_id ] );
    }

    public function get_messages() {
        check_ajax_referer( 'carno_livechat_nonce', 'nonce' );

        $last_id    = isset( $_POST['last_id'] )    ? absint( $_POST['last_id'] )                                        : 0;
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';

        $is_admin       = current_user_can( 'manage_options' );
        $viewer_session = ( $session_id && $this->is_valid_uuid( $session_id ) ) ? $session_id : null;

        if ( $viewer_session ) {
            Carno_Livechat_Database::update_last_seen( $viewer_session );
        }

        $messages    = Carno_Livechat_Database::get_messages_since( $last_id, 50, $viewer_session, $is_admin );
        $deleted_ids = Carno_Livechat_Database::get_deleted_ids();

        $is_banned = $session_id && $this->is_valid_uuid( $session_id )
            ? Carno_Livechat_Database::is_user_banned( $session_id )
            : false;

        wp_send_json_success( [
            'messages'     => $messages,
            'deleted_ids'  => $deleted_ids,
            'chat_enabled' => (bool) get_option( 'carno_livechat_chat_enabled', 0 ),
            'is_banned'    => $is_banned,
        ] );
    }

    public function send_user_message() {
        check_ajax_referer( 'carno_livechat_nonce', 'nonce' );

        if ( ! get_option( 'carno_livechat_chat_enabled', 0 ) ) {
            wp_send_json_error( [ 'message' => 'Chat is disabled.' ], 403 );
        }

        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        $message    = isset( $_POST['message'] )    ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

        if ( empty( $session_id ) || ! $this->is_valid_uuid( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session.' ], 400 );
        }

        if ( empty( $message ) ) {
            wp_send_json_error( [ 'message' => 'Message cannot be empty.' ], 400 );
        }

        if ( mb_strlen( $message ) > 500 ) {
            wp_send_json_error( [ 'message' => 'Message too long (max 500 characters).' ], 400 );
        }

        $user = Carno_Livechat_Database::get_user_by_session( $session_id );

        if ( ! $user ) {
            wp_send_json_error( [ 'message' => 'Session not found.' ], 403 );
        }

        if ( (int) $user->is_banned === 1 ) {
            wp_send_json_error( [ 'message' => 'banned' ], 403 );
        }

        if ( Carno_Livechat_Database::count_recent_user_messages( $session_id, 10 ) >= 5 ) {
            wp_send_json_error( [ 'message' => 'rate_limit' ], 429 );
        }

        $chat_mode = get_option( 'carno_livechat_chat_mode', 'public' );
        $id        = Carno_Livechat_Database::insert_user_message( $message, $session_id, $user->name, $chat_mode );

        wp_send_json_success( [
            'id'         => $id,
            'message'    => $message,
            'sent_by'    => $user->name,
            'session_id' => $session_id,
            'created_at' => current_time( 'mysql' ),
        ] );
    }

    public function heartbeat() {
        check_ajax_referer( 'carno_livechat_nonce', 'nonce' );

        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';

        if ( empty( $session_id ) || ! $this->is_valid_uuid( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session.' ], 400 );
        }

        Carno_Livechat_Database::update_last_seen( $session_id );

        wp_send_json_success();
    }

    public static function compute_viewer_count() {
        $real = Carno_Livechat_Database::count_online_users();

        if ( ! get_option( 'carno_livechat_live_mode', 0 ) ) {
            return (int) $real;
        }

        $base = $real * 2 + 12;

        if ( $base < 290 ) {
            return (int) $base;
        }

        $last = get_transient( 'clc_viewer_last_displayed' );

        if ( $last === false ) {
            $count = rand( 290, 296 );
        } else {
            $count = (int) $last + rand( -3, 3 );
            if ( $count < 290 ) $count = 290;
            if ( $count > 300 ) $count = rand( 297, 300 );
            // Avoid sticking at 300 — push back down 80% of the time
            if ( $count === 300 && rand( 1, 10 ) <= 8 ) $count = rand( 295, 299 );
        }

        set_transient( 'clc_viewer_last_displayed', $count, 300 );

        return $count;
    }

    public function get_viewer_count() {
        wp_send_json_success( [ 'count' => self::compute_viewer_count() ] );
    }

    private function is_valid_uuid( $uuid ) {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    private function get_client_ip() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ( $headers as $header ) {
            if ( empty( $_SERVER[ $header ] ) ) {
                continue;
            }

            $ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
            $ip = trim( explode( ',', $ip )[0] );

            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }

        return '';
    }
}
