<?php
/**
 * Plugin Name: Micro Site Care: Last Updated
 * Plugin URI: https://anomalous.co.za
 * Description: Display and control the post last-updated date in flexible positions.
 * Version: 1.0.0
 * Author: Anomalous Developers
 * Author URI: https://anomalous.co.za
 * Text Domain: msc-last-updated
 * Domain Path: /languages
 * Requires at least: 5.9
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current plugin version.
 */
define( 'MSCLU_PLUGIN_VERSION', '1.0.0' );

/**
 * Absolute path to the main plugin file.
 */
define( 'MSCLU_PLUGIN_FILE', __FILE__ );

/**
 * Absolute path to the plugin directory.
 */
define( 'MSCLU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * URL to the plugin directory.
 */
define( 'MSCLU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MSCLU_PLUGIN_DIR . 'includes/class-msc-last-updated-module.php';
require_once MSCLU_PLUGIN_DIR . 'includes/class-msc-last-updated-settings.php';
require_once MSCLU_PLUGIN_DIR . 'includes/class-msc-last-updated.php';

if ( false ) {
	require_once MSCLU_PLUGIN_DIR . 'includes/class-msc-last-updated-analytics.php';
}

if ( false ) {
	require_once MSCLU_PLUGIN_DIR . 'includes/class-msc-last-updated-admin-analytics.php';
}

register_activation_hook(
	__FILE__,
	array( 'MSC_Last_Updated\\Plugin', 'activate' )
);

register_deactivation_hook(
	__FILE__,
	array( 'MSC_Last_Updated\\Plugin', 'deactivate' )
);

add_action(
	'plugins_loaded',
	static function () {
		// Load translation files from the plugin languages directory.
		load_plugin_textdomain(
			'msc-last-updated',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		MSC_Last_Updated\Plugin::instance();
	}
);
