<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Admin_Ajax {

    public function send_broadcast() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

        if ( empty( $message ) ) {
            wp_send_json_error( [ 'message' => 'Message cannot be empty.' ], 400 );
        }

        if ( mb_strlen( $message ) > 2000 ) {
            wp_send_json_error( [ 'message' => 'Message too long (max 2000 characters).' ], 400 );
        }

        $id = Carno_Livechat_Database::insert_message( $message, 'admin' );

        wp_send_json_success( [ 'message_id' => $id ] );
    }

    public function get_online_count() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $count = Carno_Livechat_Database::count_online_users();

        wp_send_json_success( [ 'count' => $count ] );
    }

    public function get_messages() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $messages = Carno_Livechat_Database::get_all_messages( 50 );

        wp_send_json_success( [ 'messages' => $messages ] );
    }

    public function delete_message() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $id = isset( $_POST['message_id'] ) ? absint( $_POST['message_id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( [ 'message' => 'Invalid message ID.' ], 400 );
        }

        Carno_Livechat_Database::delete_message( $id );

        wp_send_json_success();
    }

    public function get_users() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $per_page    = 30;
        $page        = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
        $offset      = ( $page - 1 ) * $per_page;
        $total       = Carno_Livechat_Database::count_all_users();
        $total_pages = (int) ceil( $total / $per_page );
        $users       = Carno_Livechat_Database::get_all_users( $per_page, $offset );

        wp_send_json_success( [
            'users'       => $users,
            'total'       => $total,
            'page'        => $page,
            'total_pages' => $total_pages,
        ] );
    }

    public function ban_user() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        if ( empty( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session.' ], 400 );
        }
        Carno_Livechat_Database::ban_user( $session_id );
        wp_send_json_success();
    }

    public function unban_user() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        if ( empty( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session.' ], 400 );
        }
        Carno_Livechat_Database::unban_user( $session_id );
        wp_send_json_success();
    }

    public function delete_user_messages() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        if ( empty( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session.' ], 400 );
        }
        Carno_Livechat_Database::delete_user_messages( $session_id );
        wp_send_json_success();
    }

    public function toggle_live_mode() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $current = (int) get_option( 'carno_livechat_live_mode', 0 );
        $new     = $current ? 0 : 1;
        update_option( 'carno_livechat_live_mode', $new );

        wp_send_json_success( [ 'live_mode' => (bool) $new ] );
    }

    public function set_chat_mode() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : '';

        if ( ! in_array( $mode, [ 'public', 'private' ], true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid mode.' ], 400 );
        }

        update_option( 'carno_livechat_chat_mode', $mode );

        wp_send_json_success( [ 'chat_mode' => $mode ] );
    }

    public function toggle_chat() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $current = (int) get_option( 'carno_livechat_chat_enabled', 0 );
        $new     = $current ? 0 : 1;
        update_option( 'carno_livechat_chat_enabled', $new );

        wp_send_json_success( [ 'chat_enabled' => (bool) $new ] );
    }

    public function delete_all_messages() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        Carno_Livechat_Database::delete_all_messages();

        wp_send_json_success();
    }
}
