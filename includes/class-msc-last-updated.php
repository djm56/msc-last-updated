<?php
/**
 * Main bootstrap class for MSC Last Updated.
 */

namespace MSC_Last_Updated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	const OPTION_KEY = 'msclu_options';

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Module instance.
	 *
	 * @var Module|null
	 */
	private $module = null;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Analytics instance.
	 *
	 * @var object|null
	 */
	private $analytics = null;

	/**
	 * Admin analytics instance.
	 *
	 * @var object|null
	 */
	private $admin_analytics = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Activate plugin.
	 */
	public static function activate() {
		$options = get_option( self::OPTION_KEY );
		if ( ! is_array( $options ) ) {
			update_option( self::OPTION_KEY, self::default_options() );
		}
	}

	/**
	 * Deactivate plugin.
	 */
	public static function deactivate() {
		// Reserved for deactivation cleanup hooks.
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->settings = new Settings( $this );

		if ( false ) {
			$this->analytics = class_exists( __NAMESPACE__ . '\\Plugin_Analytics' ) ? new Plugin_Analytics() : null;
		}

		if ( false ) {
			$this->admin_analytics = class_exists( __NAMESPACE__ . '\\Plugin_Admin_Analytics' ) ? new Plugin_Admin_Analytics() : null;
			if ( is_object( $this->admin_analytics ) && method_exists( $this->admin_analytics, 'hooks' ) ) {
				$this->admin_analytics->hooks();
			}
		}

		$this->module = new Module( $this );
	}

	/**
	 * Default options.
	 *
	 * @return array<string,mixed>
	 */
	public static function default_options() {
		return array(
			'module_enabled' => 1,
			'post_types'     => array( 'post', 'page' ),
			'post_type_mode' => 'include',
			'position'       => 'after',
			'label_text'     => __( 'Updated %s', 'msc-last-updated' ),
			'date_mode'      => 'site',
			'custom_format'  => 'F j, Y',
			'modified_only'  => 1,
		);
	}

	/**
	 * Option getter.
	 *
	 * @param string $key Key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_option( $key, $default = null ) {
		// Get all options from database, then apply Free defaults ONLY for Free keys that are missing.
		$db_options = (array) get_option( self::OPTION_KEY, array() );
		$free_defaults = self::default_options();

		// Merge: DB options first (top priority), then fill gaps from defaults.
		// This preserves Pro fields AND allows Free fields to fall back to defaults if not in DB.
		$options = array_merge( $free_defaults, $db_options );

		error_log( 'MSC Last Updated: get_option(' . $key . ') - DB has: ' . wp_json_encode( array_keys( $db_options ) ) . ', returning: ' . wp_json_encode( $options[ $key ] ?? null ) );

		return array_key_exists( $key, $options ) ? $options[ $key ] : $default;
	}

	/**
	 * Save merged options.
	 *
	 * @param array<string,mixed> $new_options New values.
	 * @return bool
	 */
	public function update_options( $new_options ) {
		// Get current options from database WITHOUT applying defaults, to preserve all Pro fields.
		$current = (array) get_option( self::OPTION_KEY, array() );
		error_log( 'MSC Last Updated: update_options - current from DB: ' . wp_json_encode( $current ) );
		error_log( 'MSC Last Updated: update_options - new options to merge: ' . wp_json_encode( $new_options ) );

		// Merge new options over current, preserving all existing keys not being overwritten.
		$merged = array_merge( $current, $new_options );

		error_log( 'MSC Last Updated: update_options - final merged: ' . wp_json_encode( $merged ) );

		$result = (bool) update_option( self::OPTION_KEY, $merged );

		error_log( 'MSC Last Updated: update_options - update_option returned: ' . ( $result ? 'true' : 'false' ) );

		// Clear WordPress cache for this option to ensure fresh reads.
		wp_cache_delete( self::OPTION_KEY, 'options' );
		error_log( 'MSC Last Updated: update_options - cache cleared' );

		return $result;
	}

	/**
	 * Whether pro plugin is active.
	 *
	 * @return bool
	 */
	public function is_pro_active() {
		$legacy = (bool) apply_filters( 'msc-last-updated_pro_active', false );
		$current = (bool) apply_filters( 'msclu_pro_active', false );

		return $legacy || $current;
	}

	/**
	 * Feature switch helper.
	 *
	 * @param string $feature Feature key.
	 * @return bool
	 */
	public function has_feature( $feature ) {
		$map = array(
			'analytics'         => false,
			'admin_analytics'   => false,
			'cron'              => false,
			'meta_registration' => false,
			'bulk_actions'      => false,
			'shortcode'         => false,
			'ajax'              => false,
		);

		return ! empty( $map[ $feature ] );
	}
}
