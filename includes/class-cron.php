<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Carno_Livechat_Cron {

    public function run_cleanup() {
        Carno_Livechat_Database::delete_inactive_users( 24 );
    }
}
