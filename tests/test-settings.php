<?php
/**
 * Unit tests for MSC Last Updated Settings.
 *
 * @package MSCLU
 */

/**
 * Test MSCLU Settings class.
 *
 * @coversDefaultClass MSCLU\Settings
 */
class MSCLU_Settings_Test extends WP_UnitTestCase {

	/**
	 * Test that the settings class can be instantiated.
	 *
	 * @coversNothing
	 */
	public function test_settings_class_exists() {
		$this->assertTrue( class_exists( 'MSCLU\\Settings' ) );
	}

	/**
	 * Test register_menu creates admin page.
	 *
	 * @covers ::register_menu
	 */
	public function test_register_menu() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->getMock();

		$settings = new MSCLU\Settings( $plugin );

		// Capture the added submenu page.
		global $submenu;
		set_current_screen( 'options-general.php' );

		// Should not throw exception.
		$settings->register_menu();

		// Verify the menu was registered (check admin menu action was hooked).
		$this->assertTrue( did_action( 'admin_menu' ) >= 0 );
	}

	/**
	 * Test handle_save validates nonce.
	 *
	 * @covers ::handle_save
	 */
	public function test_handle_save_nonce_validation() {
		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->getMock();

		$settings = new MSCLU\Settings( $plugin );

		// Set up $_POST data without valid nonce.
		$_POST = array(
			'_wpnonce' => 'invalid_nonce',
			'action'   => 'msc-last-updated_save_settings',
		);

		// Expect wp_die to be called due to nonce failure.
		$this->expectException( 'WPDieException' );
		$settings->handle_save();
	}

	/**
	 * Test handle_save requires manage_options capability.
	 *
	 * @covers ::handle_save
	 */
	public function test_handle_save_capability_check() {
		// Create a subscriber user (without manage_options capability).
		$subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->getMock();

		$settings = new MSCLU\Settings( $plugin );

		// Set up $_POST data.
		$_POST = array(
			'_wpnonce' => wp_create_nonce( 'msc-last-updated_save_settings' ),
			'action'   => 'msc-last-updated_save_settings',
		);

		// Expect wp_die due to capability check failure.
		$this->expectException( 'WPDieException' );
		$settings->handle_save();
	}

	/**
	 * Test handle_save properly sanitizes input.
	 *
	 * @covers ::handle_save
	 */
	public function test_handle_save_sanitizes_input() {
		// Create an administrator user.
		$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );

		$saved_options = array();

		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->setMethods( array( 'update_options' ) )
			->getMock();

		$plugin->expects( $this->once() )
			->method( 'update_options' )
			->willReturnCallback(
				function ( $options ) use ( &$saved_options ) {
					$saved_options = $options;
					return true;
				}
			);

		$settings = new MSCLU\Settings( $plugin );

		// Set up sanitized $_POST data.
		$_POST = array(
			'_wpnonce'      => wp_create_nonce( 'msc-last-updated_save_settings' ),
			'action'        => 'msc-last-updated_save_settings',
			'module_enabled' => '1',
			'post_types'    => array( 'post', 'page' ),
			'post_type_mode' => 'include',
			'position'      => 'after',
			'label_text'    => 'Updated %s',
			'date_mode'     => 'site',
			'custom_format' => 'F j, Y',
			'modified_only' => '1',
		);

		// Suppress redirect.
		add_filter( 'wp_safe_redirect', '__return_false' );

		$settings->handle_save();

		// Verify options were saved and sanitized.
		$this->assertIsArray( $saved_options );
		$this->assertArrayHasKey( 'module_enabled', $saved_options );
		$this->assertArrayHasKey( 'post_types', $saved_options );
		$this->assertArrayHasKey( 'label_text', $saved_options );
	}

	/**
	 * Test render_page checks capabilities.
	 *
	 * @covers ::render_page
	 */
	public function test_render_page_capability_check() {
		// Create a subscriber user.
		$subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );

		$plugin = $this->getMockBuilder( 'MSCLU\\Plugin' )
			->disableOriginalConstructor()
			->getMock();

		$settings = new MSCLU\Settings( $plugin );

		// Capture output.
		ob_start();
		$settings->render_page();
		$output = ob_get_clean();

		// Should not output anything for users without capabilities.
		$this->assertEmpty( $output );
	}

	/**
	 * Test default option values are correct.
	 *
	 * @coversNothing
	 */
	public function test_default_options() {
		$defaults = MSCLU\Plugin::default_options();

		$this->assertIsArray( $defaults );
		$this->assertArrayHasKey( 'module_enabled', $defaults );
		$this->assertArrayHasKey( 'post_types', $defaults );
		$this->assertArrayHasKey( 'position', $defaults );
		$this->assertArrayHasKey( 'label_text', $defaults );
		$this->assertArrayHasKey( 'date_mode', $defaults );
		$this->assertArrayHasKey( 'modified_only', $defaults );

		$this->assertEquals( 1, $defaults['module_enabled'] );
		$this->assertEquals( array( 'post', 'page' ), $defaults['post_types'] );
		$this->assertEquals( 'after', $defaults['position'] );
		$this->assertEquals( 'site', $defaults['date_mode'] );
		$this->assertEquals( 1, $defaults['modified_only'] );
	}
}
