<?php
/**
 * Plugin Name: Micro Site Care: Last Updated
 * Plugin URI:  https://anomalous.co.za
 * Description: Automatically display a "last updated" label on posts and pages when content has been modified after publication.
 * Version:     0.1.0
 * Author:      Anomalous Developers
 * Author URI:  https://anomalous.co.za
 * Text Domain: msc-last-updated
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MSCLU_PLUGIN_VERSION', '0.1.0' );
define( 'MSCLU_PLUGIN_FILE', __FILE__ );
define( 'MSCLU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MSCLU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MSCLU_PLUGIN_DIR . 'includes/class-msc-last-updated-module.php';
require_once MSCLU_PLUGIN_DIR . 'includes/class-msc-last-updated-settings.php';
require_once MSCLU_PLUGIN_DIR . 'includes/class-msc-last-updated.php';

register_activation_hook( __FILE__, array( 'MSC_Last_Updated', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MSC_Last_Updated', 'deactivate' ) );

add_action(
    'plugins_loaded',
    static function () {
        load_plugin_textdomain( 'msc-last-updated', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        MSC_Last_Updated::instance();
    }
);
