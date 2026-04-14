<?php
/**
 * Unit tests for MSC Last Updated Module.
 *
 * @package MSCLU
 */

/**
 * Test MSCLU Module class.
 *
 * @coversDefaultClass MSCLU\Module
 */
class MSCLU_Module_Test extends WP_UnitTestCase {

	/**
	 * Test that the Module class can be instantiated.
	 *
	 * @coversNothing
	 */
	public function test_module_class_exists() {
		$this->assertTrue( class_exists( 'MSCLU\\Module' ) );
	}

	/**
	 * Test filter_content returns content unchanged when not singular.
	 *
	 * @covers ::filter_content
	 */
	public function test_filter_content_not_singular() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->setMethods( array( 'get_option' ) )
			->getMock();

		$plugin->expects( $this->any() )
			->method( 'get_option' )
			->willReturnCallback(
				function ( $key, $default = null ) {
					$options = array(
						'module_enabled' => 1,
						'position'       => 'after',
						'post_types'     => array( 'post' ),
						'post_type_mode' => 'include',
						'modified_only'  => 1,
						'date_mode'      => 'site',
						'custom_format'  => 'F j, Y',
						'label_text'     => 'Updated %s',
					);
					return $options[ $key ] ?? $default;
				}
			);

		$module = new MSCLU\Module( $plugin );

		// Set up a non-singular context.
		set_current_screen( 'index.php' );

		$content = '<p>Test content</p>';
		$result  = $module->filter_content( $content );

		// Should return unchanged content.
		$this->assertEquals( $content, $result );
	}

	/**
	 * Test filter_content returns content unchanged when disabled.
	 *
	 * @covers ::filter_content
	 */
	public function test_filter_content_disabled() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->setMethods( array( 'get_option' ) )
			->getMock();

		$plugin->expects( $this->any() )
			->method( 'get_option' )
			->willReturnCallback(
				function ( $key, $default = null ) {
					$options = array(
						'module_enabled' => 0,
						'position'       => 'after',
					);
					return $options[ $key ] ?? $default;
				}
			);

		$module = new MSCLU\Module( $plugin );

		$content = '<p>Test content</p>';
		$result  = $module->filter_content( $content );

		// Should return unchanged content.
		$this->assertEquals( $content, $result );
	}

	/**
	 * Test filter_content returns content unchanged in manual mode.
	 *
	 * @covers ::filter_content
	 */
	public function test_filter_content_manual_mode() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->setMethods( array( 'get_option' ) )
			->getMock();

		$plugin->expects( $this->any() )
			->method( 'get_option' )
			->willReturnCallback(
				function ( $key, $default = null ) {
					$options = array(
						'module_enabled' => 1,
						'position'       => 'manual',
					);
					return $options[ $key ] ?? $default;
				}
			);

		$module = new MSCLU\Module( $plugin );

		$content = '<p>Test content</p>';
		$result  = $module->filter_content( $content );

		// Should return unchanged content in manual mode.
		$this->assertEquals( $content, $result );
	}

	/**
	 * Test supports_post_type with include mode.
	 *
	 * @covers ::supports_post_type
	 */
	public function test_supports_post_type_include_mode() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->setMethods( array( 'get_option' ) )
			->getMock();

		$plugin->expects( $this->any() )
			->method( 'get_option' )
			->willReturnCallback(
				function ( $key, $default = null ) {
					$options = array(
						'post_type_mode' => 'include',
					);
					return $options[ $key ] ?? $default;
				}
			);

		$module = new MSCLU\Module( $plugin );

		// Should return true for included post type.
		$this->assertTrue( $module->supports_post_type( 'post', array( 'post', 'page' ) ) );

		// Should return false for excluded post type.
		$this->assertFalse( $module->supports_post_type( 'attachment', array( 'post', 'page' ) ) );
	}

	/**
	 * Test supports_post_type with exclude mode.
	 *
	 * @covers ::supports_post_type
	 */
	public function test_supports_post_type_exclude_mode() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->setMethods( array( 'get_option' ) )
			->getMock();

		$plugin->expects( $this->any() )
			->method( 'get_option' )
			->willReturnCallback(
				function ( $key, $default = null ) {
					$options = array(
						'post_type_mode' => 'exclude',
					);
					return $options[ $key ] ?? $default;
				}
			);

		$module = new MSCLU\Module( $plugin );

		// Should return false for excluded post type.
		$this->assertFalse( $module->supports_post_type( 'post', array( 'post', 'page' ) ) );

		// Should return true for non-excluded post type.
		$this->assertTrue( $module->supports_post_type( 'attachment', array( 'post', 'page' ) ) );
	}

	/**
	 * Test is_enabled returns correct value.
	 *
	 * @covers ::is_enabled
	 */
	public function test_is_enabled() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->setMethods( array( 'get_option' ) )
			->getMock();

		$plugin->expects( $this->any() )
			->method( 'get_option' )
			->willReturnCallback(
				function ( $key, $default = null ) {
					$options = array(
						'module_enabled' => 1,
					);
					return $options[ $key ] ?? $default;
				}
			);

		$module = new MSCLU\Module( $plugin );

		$this->assertTrue( $module->is_enabled() );
	}

	/**
	 * Test enqueue_assets does not enqueue when disabled.
	 *
	 * @covers ::enqueue_assets
	 */
	public function test_enqueue_assets_disabled() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->setMethods( array( 'get_option' ) )
			->getMock();

		$plugin->expects( $this->any() )
			->method( 'get_option' )
			->willReturnCallback(
				function ( $key, $default = null ) {
					$options = array(
						'module_enabled' => 0,
					);
					return $options[ $key ] ?? $default;
				}
			);

		$module = new MSCLU\Module( $plugin );
		$module->enqueue_assets();

		// No styles should be enqueued.
		$this->assertFalse( wp_style_is( 'msc-last-updated-styles', 'enqueued' ) );
	}
}
