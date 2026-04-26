<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Admin {

    private $plugin_name;
    private $version;
    private $page_hook;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function add_menu_page() {
        $this->page_hook = add_menu_page(
            __( 'Carno LiveChat', 'carno-livechat' ),
            __( 'LiveChat', 'carno-livechat' ),
            'manage_options',
            'carno-livechat',
            [ $this, 'render_page' ],
            'dashicons-megaphone',
            80
        );
    }

    public function enqueue_styles( $hook ) {
        if ( $hook !== $this->page_hook ) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            CARNO_LIVECHAT_URL . 'assets/css/admin.css',
            [],
            $this->version
        );
    }

    public function enqueue_scripts( $hook ) {
        if ( $hook !== $this->page_hook ) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            CARNO_LIVECHAT_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name . '-admin',
            'CarnoLivechatAdmin',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'carno_livechat_admin_nonce' ),
            ]
        );
    }

    public function render_page() {
        include CARNO_LIVECHAT_PATH . 'templates/admin/broadcast-panel.php';
    }
}
