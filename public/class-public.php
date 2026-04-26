<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        if ( ! $this->is_livechat_page() ) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name,
            CARNO_LIVECHAT_URL . 'assets/css/public.css',
            [],
            $this->version
        );
    }

    public function enqueue_scripts() {
        if ( ! $this->is_livechat_page() ) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name,
            CARNO_LIVECHAT_URL . 'assets/js/public/main.js',
            [],
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'CarnoLivechat',
            [
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'nonce'             => wp_create_nonce( 'carno_livechat_nonce' ),
                'polling_interval'  => 5000,
                'heartbeat_interval'=> 20000,
            ]
        );
    }

    private function is_livechat_page() {
        global $post;
        return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'livechat' );
    }
}
