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
	 * Appends rendered output to post content.
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

		$position = (string) $this->plugin->get_option( 'position', 'after' );
		if ( 'manual' === $position ) {
			return $content;
		}

		$output = $this->get_last_updated_html(
			$post->ID,
			array(
				'source' => 'the_content',
			)
		);

		if ( '' === $output ) {
			return $content;
		}

		if ( 'before' === $position ) {
			return $output . $content;
		}

		if ( 'both' === $position ) {
			return $output . $content . $output;
		}

		return $content . $output;
	}

	/**
	 * Returns the rendered last-updated markup for a post.
	 *
	 * @param int                $post_id Post ID.
	 * @param array<string,mixed> $context Render context.
	 * @return string
	 */
	public function get_last_updated_html( $post_id = 0, $context = array() ) {
		$post = $post_id ? get_post( $post_id ) : get_post();
		if ( ! $post ) {
			return '';
		}

		$post_types = (array) $this->plugin->get_option( 'post_types', array( 'post', 'page' ) );
		if ( ! $this->supports_post_type( $post->post_type, $post_types ) ) {
			return '';
		}

		$published = get_post_time( 'U', true, $post );
		$modified  = get_post_modified_time( 'U', true, $post );
		$modified_only = (bool) $this->plugin->get_option( 'modified_only', 1 );

		$should_display = ! $modified_only || $modified > $published;

		/**
		 * Filters visibility for a single post render.
		 *
		 * @param bool               $should_display Whether to display output.
		 * @param \WP_Post           $post Current post object.
		 * @param int                $modified Modified timestamp.
		 * @param int                $published Published timestamp.
		 * @param array<string,mixed> $context Render context.
		 */
		$should_display = (bool) apply_filters( 'msclu_should_display', $should_display, $post, $modified, $published, $context );

		if ( ! $should_display ) {
			return '';
		}

		$date_mode      = (string) $this->plugin->get_option( 'date_mode', 'site' );
		$custom_format  = (string) $this->plugin->get_option( 'custom_format', 'F j, Y' );
		$date_format    = 'custom' === $date_mode ? $custom_format : get_option( 'date_format' );
		if ( empty( $date_format ) ) {
			$date_format = get_option( 'date_format' );
		}

		$formatted_date = wp_date( $date_format, $modified );
		$label_template = (string) $this->plugin->get_option( 'label_text', __( 'Updated %s', 'msc-last-updated' ) );
		if ( false === strpos( $label_template, '%s' ) ) {
			$label_template = __( 'Updated %s', 'msc-last-updated' );
		}
		$label          = sprintf(
			$label_template,
			$formatted_date
		);

		/**
		 * Filters the visible label text.
		 *
		 * @param string             $label Full visible label text.
		 * @param \WP_Post           $post Current post object.
		 * @param string             $formatted_date Formatted absolute date.
		 * @param int                $modified Modified timestamp.
		 * @param int                $published Published timestamp.
		 * @param array<string,mixed> $context Render context.
		 */
		$label = (string) apply_filters( 'msclu_label_text', $label, $post, $formatted_date, $modified, $published, $context );

		$classes = array( 'msclu-last-updated' );

		/**
		 * Filters wrapper classes for the output element.
		 *
		 * @param array<int,string>  $classes CSS classes.
		 * @param \WP_Post           $post Current post object.
		 * @param array<string,mixed> $context Render context.
		 */
		$classes = apply_filters( 'msclu_wrapper_classes', $classes, $post, $context );
		$classes = array_values( array_filter( array_map( 'sanitize_html_class', (array) $classes ) ) );
		$class   = implode( ' ', $classes );

		$time_markup = '<time datetime="' . esc_attr( gmdate( 'c', $modified ) ) . '">' . esc_html( $label ) . '</time>';
		$output      = '<p class="' . esc_attr( $class ) . '">' . $time_markup . '</p>';

		/**
		 * Filters final HTML output.
		 *
		 * @param string             $output Final HTML output.
		 * @param \WP_Post           $post Current post object.
		 * @param string             $label Label text.
		 * @param int                $modified Modified timestamp.
		 * @param array<string,mixed> $context Render context.
		 */
		$output = (string) apply_filters( 'msclu_output_html', $output, $post, $label, $modified, $context );

		return $output;
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
	 * Determines whether output applies to a post type.
	 *
	 * @param string            $post_type Current post type.
	 * @param array<int,string> $selected_types Selected post types.
	 * @return bool
	 */
	private function supports_post_type( $post_type, $selected_types ) {
		$mode = (string) $this->plugin->get_option( 'post_type_mode', 'include' );

		if ( 'exclude' === $mode ) {
			return ! in_array( $post_type, $selected_types, true );
		}

		return in_array( $post_type, $selected_types, true );
	}

}
