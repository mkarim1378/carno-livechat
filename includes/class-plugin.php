<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'carno-livechat';
        $this->version     = CARNO_LIVECHAT_VERSION;

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once CARNO_LIVECHAT_PATH . 'includes/class-loader.php';
        require_once CARNO_LIVECHAT_PATH . 'database/class-database.php';

        require_once CARNO_LIVECHAT_PATH . 'admin/class-admin.php';
        require_once CARNO_LIVECHAT_PATH . 'admin/class-admin-ajax.php';

        require_once CARNO_LIVECHAT_PATH . 'public/class-public.php';
        require_once CARNO_LIVECHAT_PATH . 'public/class-public-ajax.php';
        require_once CARNO_LIVECHAT_PATH . 'includes/class-cron.php';

        $this->loader = new Carno_Livechat_Loader();
    }

    private function define_admin_hooks() {
        $admin      = new Carno_Livechat_Admin( $this->plugin_name, $this->version );
        $admin_ajax = new Carno_Livechat_Admin_Ajax();
        $cron       = new Carno_Livechat_Cron();

        $this->loader->add_action( 'admin_menu',            $admin, 'add_menu_page' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

        $this->loader->add_action( 'wp_ajax_livechat_broadcast',    $admin_ajax, 'send_broadcast' );
        $this->loader->add_action( 'wp_ajax_livechat_online_count', $admin_ajax, 'get_online_count' );
        $this->loader->add_action( 'carno_livechat_cleanup',        $cron,       'run_cleanup' );
    }

    private function define_public_hooks() {
        $public      = new Carno_Livechat_Public( $this->plugin_name, $this->version );
        $public_ajax = new Carno_Livechat_Public_Ajax();

        $this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );
        $this->loader->add_action( 'init',               $public, 'register_shortcode' );

        $this->loader->add_action( 'wp_ajax_nopriv_livechat_register',  $public_ajax, 'register_user' );
        $this->loader->add_action( 'wp_ajax_livechat_register',         $public_ajax, 'register_user' );
        $this->loader->add_action( 'wp_ajax_nopriv_livechat_heartbeat',     $public_ajax, 'heartbeat' );
        $this->loader->add_action( 'wp_ajax_livechat_heartbeat',            $public_ajax, 'heartbeat' );
        $this->loader->add_action( 'wp_ajax_nopriv_livechat_get_messages',  $public_ajax, 'get_messages' );
        $this->loader->add_action( 'wp_ajax_livechat_get_messages',         $public_ajax, 'get_messages' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function get_loader() {
        return $this->loader;
    }
}
