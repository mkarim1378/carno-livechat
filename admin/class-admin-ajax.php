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

    public function delete_all_messages() {
        check_ajax_referer( 'carno_livechat_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        Carno_Livechat_Database::delete_all_messages();

        wp_send_json_success();
    }
}
