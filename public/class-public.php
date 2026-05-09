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

        wp_enqueue_script( 'clc-session',   $base . 'session.js',   [],                                                                    $ver, true );
        wp_enqueue_script( 'clc-modal',     $base . 'modal.js',     [ 'clc-session' ],                                                    $ver, true );
        wp_enqueue_script( 'clc-heartbeat', $base . 'heartbeat.js', [ 'clc-session' ],                                                    $ver, true );
        wp_enqueue_script( 'clc-chat',      $base . 'chat.js',      [ 'clc-session' ],                                                    $ver, true );
        wp_enqueue_script( 'clc-polling',   $base . 'polling.js',   [ 'clc-session', 'clc-chat' ],                                        $ver, true );
        wp_enqueue_script( 'clc-input',     $base . 'input.js',     [ 'clc-session', 'clc-chat', 'clc-polling' ],                         $ver, true );
        wp_enqueue_script(
            $this->plugin_name,
            $base . 'main.js',
            [ 'clc-session', 'clc-modal', 'clc-heartbeat', 'clc-chat', 'clc-polling', 'clc-input' ],
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
                'chat_enabled'       => (bool) get_option( 'carno_livechat_chat_enabled', 0 ),
            ]
        );
    }

    public function register_shortcode() {
        add_shortcode( 'livechat',         [ $this, 'render_shortcode' ] );
        add_shortcode( 'livechat_viewers', [ $this, 'render_viewer_shortcode' ] );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            [
                'title'       => __( 'پشتیبانی آنلاین', 'carno-livechat' ),
                'placeholder' => __( 'نام شما (فارسی بنویسید)', 'carno-livechat' ),
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

    public function render_viewer_shortcode() {
        static $script_printed = false;

        $real  = Carno_Livechat_Database::count_online_users();
        $count = get_option( 'carno_livechat_live_mode', 0 ) ? $real * 2 + 12 : $real;

        $html = '<span class="clc-viewer-count">' . esc_html( $count ) . '</span>';

        if ( ! $script_printed ) {
            $script_printed = true;
            $ajax_url       = esc_url( admin_url( 'admin-ajax.php' ) );
            $html .= '<script>(function(){';
            $html .= 'var u=' . wp_json_encode( $ajax_url ) . ';';
            $html .= 'function f(){var x=new XMLHttpRequest();x.open("POST",u,true);';
            $html .= 'x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");';
            $html .= 'x.onload=function(){try{var r=JSON.parse(x.responseText);if(r.success){';
            $html .= 'var els=document.querySelectorAll(".clc-viewer-count");';
            $html .= 'for(var i=0;i<els.length;i++)els[i].textContent=r.data.count;';
            $html .= '}}catch(e){}};x.send("action=livechat_viewer_count");}';
            $html .= 'setInterval(f,10000);';
            $html .= '}());</script>';
        }

        return $html;
    }

    private function is_livechat_page() {
        global $post;
        return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'livechat' );
    }
}
