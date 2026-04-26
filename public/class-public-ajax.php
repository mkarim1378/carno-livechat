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

        $user_id = Carno_Livechat_Database::insert_user(
            $name,
            $session_id,
            $page_url,
            $this->get_client_ip()
        );

        wp_send_json_success( [ 'user_id' => $user_id ] );
    }

    public function heartbeat() {
        check_ajax_referer( 'carno_livechat_nonce', 'nonce' );

        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';

        if ( empty( $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session.' ], 400 );
        }

        Carno_Livechat_Database::update_last_seen( $session_id );

        wp_send_json_success();
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
