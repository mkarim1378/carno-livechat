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
}
