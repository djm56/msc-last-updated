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

		if ( file_exists( MSCLU_PLUGIN_DIR . 'assets/css/last-updated.css' ) ) {
			wp_enqueue_style(
				'msc-last-updated-styles',
				MSCLU_PLUGIN_URL . 'assets/css/last-updated.css',
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
		if ( ! $this->is_enabled() || ! is_singular() ) {
			return $content;
		}

		$post = get_post();
		if ( ! $post ) {
			return $content;
		}

		$post_types = (array) $this->plugin->get_option( 'post_types', array( 'post', 'page' ) );
		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return $content;
		}

		$published = get_post_time( 'U', true, $post );
		$modified  = get_post_modified_time( 'U', true, $post );

		if ( $modified <= $published ) {
			return $content;
		}

		$label = sprintf(
			/* translators: %s formatted date */
			esc_html__( 'Updated %s', 'msc-last-updated' ),
			esc_html( wp_date( get_option( 'date_format' ), $modified ) )
		);

		$content .= '<p class="msclu-last-updated">' . $label . '</p>';

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
