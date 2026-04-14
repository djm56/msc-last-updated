<?php
/**
 * Unit tests for MSC Last Updated core functionality.
 *
 * @package MSCLU
 */

/**
 * Test MSCLU Plugin core class.
 *
 * @coversDefaultClass MSCLU\Plugin
 */
class MSCLU_Core_Test extends WP_UnitTestCase {

	/**
	 * Test that the Plugin class is a singleton.
	 *
	 * @covers ::instance
	 */
	public function test_plugin_singleton() {
		$instance1 = MSCLU\Plugin::instance();
		$instance2 = MSCLU\Plugin::instance();

		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test that Plugin class cannot be cloned.
	 *
	 * @covers ::__clone
	 */
	public function test_plugin_clone_protection() {
		$instance = MSCLU\Plugin::instance();

		$this->expectException( 'Error' );
		clone $instance;
	}

	/**
	 * Test that Plugin class cannot be unserialized.
	 *
	 * @covers ::__wakeup
	 */
	public function test_plugin_unserialize_protection() {
		$instance = MSCLU\Plugin::instance();

		$this->expectException( 'Error' );
		unserialize( serialize( $instance ) );
	}

	/**
	 * Test default_options returns expected structure.
	 *
	 * @covers ::default_options
	 */
	public function test_default_options_structure() {
		$defaults = MSCLU\Plugin::default_options();

		$expected_keys = array(
			'module_enabled',
			'post_types',
			'post_type_mode',
			'position',
			'label_text',
			'date_mode',
			'custom_format',
			'modified_only',
		);

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $defaults, "Missing key: $key" );
		}
	}

	/**
	 * Test get_option returns default when option not set.
	 *
	 * @covers ::get_option
	 */
	public function test_get_option_default_value() {
		delete_option( MSCLU\Plugin::OPTION_KEY );

		$plugin = MSCLU\Plugin::instance();
		$value  = $plugin->get_option( 'module_enabled', 1 );

		$this->assertEquals( 1, $value );
	}

	/**
	 * Test get_option returns stored value.
	 *
	 * @covers ::get_option
	 */
	public function test_get_option_stored_value() {
		$plugin = MSCLU\Plugin::instance();

		update_option(
			MSCLU\Plugin::OPTION_KEY,
			array(
				'module_enabled' => 0,
			)
		);

		$value = $plugin->get_option( 'module_enabled', 1 );

		$this->assertEquals( 0, $value );

		// Cleanup.
		delete_option( MSCLU\Plugin::OPTION_KEY );
	}

	/**
	 * Test update_options merges with existing options.
	 *
	 * @covers ::update_options
	 */
	public function test_update_options_merges() {
		$plugin = MSCLU\Plugin::instance();

		// Set initial options.
		update_option(
			MSCLU\Plugin::OPTION_KEY,
			array(
				'module_enabled' => 1,
				'position'       => 'before',
			)
		);

		// Update with new options.
		$plugin->update_options(
			array(
				'module_enabled' => 0,
			)
		);

		// Verify both old and new options are preserved.
		$options = get_option( MSCLU\Plugin::OPTION_KEY );

		$this->assertEquals( 0, $options['module_enabled'] );
		$this->assertEquals( 'before', $options['position'] );

		// Cleanup.
		delete_option( MSCLU\Plugin::OPTION_KEY );
	}

	/**
	 * Test is_pro_active returns false by default.
	 *
	 * @covers ::is_pro_active
	 */
	public function test_is_pro_active_default() {
		$plugin = MSCLU\Plugin::instance();

		$this->assertFalse( $plugin->is_pro_active() );
	}

	/**
	 * Test has_feature returns false for unmapped features.
	 *
	 * @covers ::has_feature
	 */
	public function test_has_feature_unmapped() {
		$plugin = MSCLU\Plugin::instance();

		$this->assertFalse( $plugin->has_feature( 'nonexistent_feature' ) );
	}

	/**
	 * Test activate creates default options.
	 *
	 * @covers ::activate
	 */
	public function test_activate_creates_defaults() {
		delete_option( MSCLU\Plugin::OPTION_KEY );

		MSCLU\Plugin::activate();

		$options = get_option( MSCLU\Plugin::OPTION_KEY );

		$this->assertIsArray( $options );
		$this->assertArrayHasKey( 'module_enabled', $options );
		$this->assertEquals( 1, $options['module_enabled'] );

		// Cleanup.
		delete_option( MSCLU\Plugin::OPTION_KEY );
	}
}
