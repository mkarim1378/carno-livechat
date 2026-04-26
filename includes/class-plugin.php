<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat {

    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'carno-livechat';
        $this->version     = CARNO_LIVECHAT_VERSION;
    }

    public function run() {
        // Modules and hooks are wired here in Phase 3
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
}
