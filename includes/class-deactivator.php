<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Deactivator {

    public static function deactivate() {
        wp_clear_scheduled_hook( 'carno_livechat_cleanup' );
    }
}
