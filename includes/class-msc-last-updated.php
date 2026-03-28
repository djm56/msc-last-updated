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
	 * @param string $key     Option key.
	 * @param mixed  $default Fallback value.
	 * @return mixed
	 */
	public function get_option( $key, $default = null ) {
		$db_options    = (array) get_option( self::OPTION_KEY, array() );
		$free_defaults = self::default_options();
		// DB values take priority; defaults fill gaps for any unset Free keys.
		$options = array_merge( $free_defaults, $db_options );
		return array_key_exists( $key, $options ) ? $options[ $key ] : $default;
	}

	/**
	 * Save merged options.
	 *
	 * @param array<string,mixed> $new_options New values.
	 * @return bool
	 */
	public function update_options( $new_options ) {
		// Read current row without applying defaults, to preserve any Pro-extended fields.
		$current = (array) get_option( self::OPTION_KEY, array() );
		$merged  = array_merge( $current, $new_options );
		return (bool) update_option( self::OPTION_KEY, $merged );
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
