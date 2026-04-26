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

        $base = CARNO_LIVECHAT_URL . 'assets/js/public/';
        $ver  = $this->version;

        wp_enqueue_script( 'clc-session',   $base . 'session.js',   [ 'jquery' ],                                              $ver, true );
        wp_enqueue_script( 'clc-modal',     $base . 'modal.js',     [ 'jquery', 'clc-session' ],                               $ver, true );
        wp_enqueue_script( 'clc-heartbeat', $base . 'heartbeat.js', [ 'jquery', 'clc-session' ],                               $ver, true );
        wp_enqueue_script( 'clc-chat',      $base . 'chat.js',      [ 'jquery' ],                                              $ver, true );
        wp_enqueue_script( 'clc-polling',   $base . 'polling.js',   [ 'jquery', 'clc-chat' ],                                  $ver, true );
        wp_enqueue_script(
            $this->plugin_name,
            $base . 'main.js',
            [ 'jquery', 'clc-session', 'clc-modal', 'clc-heartbeat', 'clc-chat', 'clc-polling' ],
            $ver,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'CarnoLivechat',
            [
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'nonce'              => wp_create_nonce( 'carno_livechat_nonce' ),
                'polling_interval'   => 5000,
                'heartbeat_interval' => 20000,
            ]
        );
    }

    public function register_shortcode() {
        add_shortcode( 'livechat', [ $this, 'render_shortcode' ] );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            [
                'title'       => __( 'پشتیبانی آنلاین', 'carno-livechat' ),
                'placeholder' => __( 'نام شما', 'carno-livechat' ),
            ],
            $atts,
            'livechat'
        );

        $title       = sanitize_text_field( $atts['title'] );
        $placeholder = sanitize_text_field( $atts['placeholder'] );

        ob_start();
        include CARNO_LIVECHAT_PATH . 'templates/public/chat-widget.php';
        return ob_get_clean();
    }

    private function is_livechat_page() {
        global $post;
        return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'livechat' );
    }
}
