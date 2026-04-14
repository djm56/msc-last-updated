<?php
/**
 * Plugin Name: Micro Site Care: Post Last Updated Date
 * Plugin URI: https://anomalous.co.za
 * Description: Display and control the post last-updated date in flexible positions.
 * Version: 1.3.0
 * Author: Anomalous Developers
 * Text Domain: micro-site-care-post-last-updated-date
 * Domain Path: /languages
 * Requires at least: 5.9
 * Tested up to: 6.9
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
define( 'MSCLU_PLUGIN_VERSION', '1.3.0' );

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

register_activation_hook(
	__FILE__,
	array( 'MSCLU\\Plugin', 'activate' )
);

register_deactivation_hook(
	__FILE__,
	array( 'MSCLU\\Plugin', 'deactivate' )
);

add_action(
	'plugins_loaded',
	static function () {
		MSCLU\Plugin::instance();
	}
);

if ( ! function_exists( 'msclup_get_last_updated' ) ) {
	/**
	 * Returns rendered Last Updated markup for a post.
	 *
	 * @param int                $post_id Post ID.
	 * @param array<string,mixed> $context Render context.
	 * @return string
	 */
	function msclup_get_last_updated( $post_id = 0, $context = array() ) {
		if ( ! class_exists( 'MSCLU\\Plugin' ) ) {
			return '';
		}

		return MSCLU\Plugin::instance()->get_last_updated_markup( (int) $post_id, (array) $context );
	}
}

if ( ! function_exists( 'msclup_the_last_updated' ) ) {
	/**
	 * Echoes rendered Last Updated markup for a post.
	 *
	 * @param int                $post_id Post ID.
	 * @param array<string,mixed> $context Render context.
	 * @return void
	 */
	function msclup_the_last_updated( $post_id = 0, $context = array() ) {
		echo wp_kses_post( msclup_get_last_updated( $post_id, $context ) );
	}
}
