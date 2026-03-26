<?php
/**
 * Module class for MSC Last Updated.
 */

namespace MSC_Last_Updated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module {

	/**
	 * Main plugin instance.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'the_content', array( $this, 'filter_content' ), 20 );

		if ( $this->plugin->has_feature( 'shortcode' ) ) {
			add_shortcode( 'msc-last-updated_shortcode', array( $this, 'render_shortcode' ) );
		}

		if ( $this->plugin->has_feature( 'ajax' ) ) {
			add_action( 'wp_ajax_msc-last-updated_event', array( $this, 'handle_ajax' ) );
			add_action( 'wp_ajax_nopriv_msc-last-updated_event', array( $this, 'handle_ajax' ) );
		}
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_assets() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( true ) {
			wp_enqueue_style(
				'msc-last-updated-styles',
				MSCLU_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				MSCLU_PLUGIN_VERSION
			);
		}
	}

	/**
	 * Content filter placeholder.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_content( $content ) {
		if ( ! $this->is_enabled() ) {
			return $content;
		}

		return $content;
	}

	/**
	 * Whether module is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		return (bool) $this->plugin->get_option( 'module_enabled', 1 );
	}

	/**
	 * Shortcode placeholder.
	 *
	 * @return string
	 */
	public function render_shortcode() {
		return '';
	}

	/**
	 * AJAX placeholder.
	 */
	public function handle_ajax() {
		wp_send_json_success( array( 'ok' => true ) );
	}
}
