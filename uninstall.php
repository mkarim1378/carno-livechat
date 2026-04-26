<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}livechat_users" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}livechat_messages" );

wp_clear_scheduled_hook( 'carno_livechat_cleanup' );
