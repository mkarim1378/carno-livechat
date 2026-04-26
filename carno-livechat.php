<?php
/**
 * Plugin Name: Carno LiveChat
 * Description: One-way admin broadcast chat system presented as a chat UI.
 * Version:     1.1.0
 * Author:      Carno
 * Text Domain: carno-livechat
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CARNO_LIVECHAT_VERSION',  '1.1.0' );
define( 'CARNO_LIVECHAT_FILE',     __FILE__ );
define( 'CARNO_LIVECHAT_PATH',     plugin_dir_path( __FILE__ ) );
define( 'CARNO_LIVECHAT_URL',      plugin_dir_url( __FILE__ ) );
define( 'CARNO_LIVECHAT_BASENAME', plugin_basename( __FILE__ ) );

require_once CARNO_LIVECHAT_PATH . 'includes/class-activator.php';
require_once CARNO_LIVECHAT_PATH . 'includes/class-deactivator.php';
require_once CARNO_LIVECHAT_PATH . 'includes/class-plugin.php';

register_activation_hook( __FILE__, [ 'Carno_Livechat_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Carno_Livechat_Deactivator', 'deactivate' ] );

function carno_livechat_run() {
    $plugin = new Carno_Livechat();
    $plugin->run();
}
carno_livechat_run();
