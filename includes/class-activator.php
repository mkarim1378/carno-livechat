<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Activator {

    public static function activate() {
        require_once CARNO_LIVECHAT_PATH . 'database/class-database.php';
        Carno_Livechat_Database::create_tables();

        if ( ! wp_next_scheduled( 'carno_livechat_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'carno_livechat_cleanup' );
        }
    }
}
