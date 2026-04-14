<?php
/**
 * Bootstrap file for MSC Last Updated test suite.
 *
 * @package MSCLU
 */

/**
 * Set up WordPress test environment.
 */

// If the test bootstrap file exists in the test runner directory, use it.
if ( getenv( 'WP_TESTS_DIR' ) ) {
	$test_dir = getenv( 'WP_TESTS_DIR' );
} elseif ( getenv( 'WP_CORE_DIR' ) ) {
	$test_dir = getenv( 'WP_CORE_DIR' ) . '/tests/phpunit';
} else {
	// Look for the standard WordPress test suite location.
	$test_dir = '/tmp/msc-testing';
}

if ( file_exists( $test_dir . '/includes/functions.php' ) ) {
	// Load the WordPress test framework bootstrap.
	require_once $test_dir . '/includes/functions.php';
} else {
	// Fallback: set up minimal test environment.
	define( 'ABSPATH', '/tmp/wordpress/' );
	define( 'WP_DEBUG', true );

	// Mock WordPress functions that aren't available outside WordPress context.
	if ( ! function_exists( 'get_post' ) ) {
		/**
		 * Mock get_post function.
		 *
		 * @param int|WP_Post $post   Optional. Post ID or post object.
		 * @param string     $output Optional. The required return type.
		 * @param string     $filter Optional. Post field filter.
		 * @return WP_Post|array|null
		 */
		function get_post( $post = null, $output = 'OBJECT', $filter = 'raw' ) {
			return null;
		}
	}

	if ( ! function_exists( 'get_post_time' ) ) {
		/**
		 * Mock get_post_time function.
		 *
		 * @param string $format    Optional. Format to use.
		 * @param bool   $gmt       Optional. Whether to use GMT timezone.
		 * @param int|WP_Post $post Optional. Post ID or post object.
		 * @return string|false
		 */
		function get_post_time( $format = 'U', $gmt = false, $post = null ) {
			return time();
		}
	}

	if ( ! function_exists( 'get_post_modified_time' ) ) {
		/**
		 * Mock get_post_modified_time function.
		 *
		 * @param string $format    Optional. Format to use.
		 * @param bool   $gmt       Optional. Whether to use GMT timezone.
		 * @param int|WP_Post $post Optional. Post ID or post object.
		 * @return string|false
		 */
		function get_post_modified_time( $format = 'U', $gmt = false, $post = null ) {
			return time();
		}
	}

	if ( ! function_exists( 'wp_date' ) ) {
		/**
		 * Mock wp_date function.
		 *
		 * @param string $format    Optional. Format to use.
		 * @param int    $timestamp Optional. Unix timestamp.
		 * @param bool   $gmt       Optional. Whether to use GMT timezone.
		 * @return string
		 */
		function wp_date( $format, $timestamp = null, $gmt = false ) {
			if ( null === $timestamp ) {
				$timestamp = time();
			}
			return date_i18n( $format, $timestamp );
		}
	}

	if ( ! function_exists( 'date_i18n' ) ) {
		/**
		 * Mock date_i18n function.
		 *
		 * @param string $format    Optional. Format to use.
		 * @param int    $timestamp Optional. Unix timestamp.
		 * @return string
		 */
		function date_i18n( $format, $timestamp = null ) {
			if ( null === $timestamp ) {
				$timestamp = time();
			}
			return date( $format, $timestamp );
		}
	}

	if ( ! function_exists( 'apply_filters' ) ) {
		/**
		 * Mock apply_filters function.
		 *
		 * @param string $tag   Filter tag.
		 * @param mixed  $value Value to filter.
		 * @param mixed  $args  Additional arguments.
		 * @return mixed
		 */
		function apply_filters( $tag, $value, ...$args ) {
			return $value;
		}
	}

	if ( ! function_exists( 'esc_html' ) ) {
		/**
		 * Mock esc_html function.
		 *
		 * @param string $text Text to escape.
		 * @return string
		 */
		function esc_html( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}

	if ( ! function_exists( 'esc_attr' ) ) {
		/**
		 * Mock esc_attr function.
		 *
		 * @param string $text Text to escape.
		 * @return string
		 */
		function esc_attr( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
		}
	}

	if ( ! function_exists( 'sanitize_text_field' ) ) {
		/**
		 * Mock sanitize_text_field function.
		 *
		 * @param string $str String to sanitize.
		 * @return string
		 */
		function sanitize_text_field( $str ) {
			return trim( wp_strip_all_tags( $str ) );
		}
	}

	if ( ! function_exists( 'wp_strip_all_tags' ) ) {
		/**
		 * Mock wp_strip_all_tags function.
		 *
		 * @param string $string String to strip tags from.
		 * @return string
		 */
		function wp_strip_all_tags( $string ) {
			return strip_tags( $string );
		}
	}

	if ( ! function_exists( 'sanitize_key' ) ) {
		/**
		 * Mock sanitize_key function.
		 *
		 * @param string $key Key to sanitize.
		 * @return string
		 */
		function sanitize_key( $key ) {
			return preg_replace( '/[^a-z0-9_]/', '', strtolower( $key ) );
		}
	}

	if ( ! function_exists( 'sanitize_html_class' ) ) {
		/**
		 * Mock sanitize_html_class function.
		 *
		 * @param string $class   Class name.
		 * @param string $fallback Optional. Fallback class.
		 * @return string
		 */
		function sanitize_html_class( $class, $fallback = '' ) {
			$class = preg_replace( '/[^a-z0-9_-]/', '', strtolower( $class ) );
			if ( empty( $class ) ) {
				return $fallback;
			}
			return $class;
		}
	}

	if ( ! function_exists( 'wp_unslash' ) ) {
		/**
		 * Mock wp_unslash function.
		 *
		 * @param string|array $value Value to unslash.
		 * @return string|array
		 */
		function wp_unslash( $value ) {
			return stripslashes( $value );
		}
	}

	if ( ! function_exists( 'esc_html__' ) ) {
		/**
		 * Mock esc_html__ function.
		 *
		 * @param string $text Text to translate and escape.
		 * @param string $domain Optional. Text domain.
		 * @return string
		 */
		function esc_html__( $text, $domain = 'default' ) {
			return esc_html( $text );
		}
	}

	if ( ! function_exists( 'esc_attr__' ) ) {
		/**
		 * Mock esc_attr__ function.
		 *
		 * @param string $text Text to translate and escape.
		 * @param string $domain Optional. Text domain.
		 * @return string
		 */
		function esc_attr__( $text, $domain = 'default' ) {
			return esc_attr( $text );
		}
	}

	if ( ! function_exists( '__' ) ) {
		/**
		 * Mock translation function.
		 *
		 * @param string $text Text to translate.
		 * @param string $domain Optional. Text domain.
		 * @return string
		 */
		function __( $text, $domain = 'default' ) {
			return $text;
		}
	}

	if ( ! function_exists( '_e' ) ) {
		/**
		 * Mock translation echo function.
		 *
		 * @param string $text Text to translate and echo.
		 * @param string $domain Optional. Text domain.
		 * @return void
		 */
		function _e( $text, $domain = 'default' ) {
			echo $text;
		}
	}

	if ( ! function_exists( 'get_option' ) ) {
		/**
		 * Mock get_option function.
		 *
		 * @param string $option  Option name.
		 * @param mixed  $default Optional. Default value.
		 * @return mixed
		 */
		function get_option( $option, $default = false ) {
			return $default;
		}
	}

	if ( ! function_exists( 'update_option' ) ) {
		/**
		 * Mock update_option function.
		 *
		 * @param string $option   Option name.
		 * @param mixed  $value    Option value.
		 * @return bool
		 */
		function update_option( $option, $value ) {
			return true;
		}
	}

	if ( ! function_exists( 'is_singular' ) ) {
		/**
		 * Mock is_singular function.
		 *
		 * @param string|array $post_types Optional. Post types.
		 * @return bool
		 */
		function is_singular( $post_types = '' ) {
			return true;
		}
	}

	if ( ! function_exists( 'get_post_types' ) ) {
		/**
		 * Mock get_post_types function.
		 *
		 * @param array $args    Optional. Arguments.
		 * @param string $output Optional. Output type.
		 * @return array
		 */
		function get_post_types( $args = array(), $output = 'names' ) {
			return array( 'post' => (object) array( 'name' => 'post', 'labels' => (object) array( 'singular_name' => 'Post' ) ) );
		}
	}

	if ( ! defined( 'MSCLU_PLUGIN_VERSION' ) ) {
		define( 'MSCLU_PLUGIN_VERSION', '1.3.0' );
	}

	if ( ! defined( 'MSCLU_PLUGIN_FILE' ) ) {
		define( 'MSCLU_PLUGIN_FILE', dirname( __FILE__ ) . '/micro-site-care-post-last-updated-date.php' );
	}

	if ( ! defined( 'MSCLU_PLUGIN_DIR' ) ) {
		define( 'MSCLU_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
	}

	if ( ! defined( 'MSCLU_PLUGIN_URL' ) ) {
		define( 'MSCLU_PLUGIN_URL', 'https://example.com/wp-content/plugins/micro-site-care-post-last-updated-date/' );
	}
}

// Define the plugin option key constant.
if ( ! defined( 'MSCLU\\Plugin::OPTION_KEY' ) ) {
	// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	class MSCLU_Constants {
		const OPTION_KEY = 'msclu_options';
	}
}
