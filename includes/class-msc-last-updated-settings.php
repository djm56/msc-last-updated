<?php
/**
 * Admin settings class for MSC Last Updated.
 */

namespace MSC_Last_Updated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

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

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_msc-last-updated_save_settings', array( $this, 'handle_save' ) );
	}

	/**
	 * Register admin page.
	 */
	public function register_menu() {
		global $admin_page_hooks;

		if ( ! isset( $admin_page_hooks['msc-site-care'] ) ) {
			add_menu_page(
				esc_html__( 'Site Care', 'msc-last-updated' ),
				esc_html__( 'Site Care', 'msc-last-updated' ),
				'manage_options',
				'msc-site-care',
				array( __CLASS__, 'render_landing_page' ),
				'dashicons-shield-alt',
				65
			);
		}

		add_submenu_page(
			'msc-site-care',
			'MSC Last Updated',
			'MSC Last Updated',
			'manage_options',
			'msc-last-updated',
			array( $this, 'render_page' )
		);

		if ( true && true ) {
			$this->maybe_register_upgrade_submenu();
		}
	}

	/**
	 * Render top-level Site Care landing page when free plugin owns the parent.
	 */
	public static function render_landing_page() {
		echo '<div class="wrap msc-admin-wrap">';
		echo '<div class="msc-admin-header"><h1>' . esc_html__( 'Site Care', 'msc-last-updated' ) . '</h1></div>';
		echo '<div class="msc-admin-card">';
		echo '<p>' . esc_html__( 'Welcome to Site Care by Anomalous Developers. Use the submenu items to configure installed modules.', 'msc-last-updated' ) . '</p>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Register one contextual upgrade submenu for free plugin only.
	 */
	private function maybe_register_upgrade_submenu() {
		global $submenu;

		$upgrade_slug       = 'msc-site-care-upgrade';
		$already_registered = false;

		if ( ! empty( $submenu['msc-site-care'] ) ) {
			foreach ( $submenu['msc-site-care'] as $item ) {
				if ( isset( $item[2] ) && $upgrade_slug === $item[2] ) {
					$already_registered = true;
					break;
				}
			}
		}

		if ( $already_registered ) {
			return;
		}

		add_submenu_page(
			'msc-site-care',
			esc_html__( 'Upgrade to Pro', 'msc-last-updated' ),
			esc_html__( 'Upgrade to Pro', 'msc-last-updated' ),
			'manage_options',
			$upgrade_slug,
			array( $this, 'render_upgrade_page' )
		);
	}

	/**
	 * Handle settings save.
	 */
	public function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'msc-last-updated' ) );
		}

		check_admin_referer( 'msc-last-updated_save_settings' );

		$module_enabled = isset( $_POST['module_enabled'] ) ? 1 : 0;
		$post_types     = isset( $_POST['post_types'] ) ? (array) wp_unslash( $_POST['post_types'] ) : array();
		$post_types     = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );

		if ( empty( $post_types ) ) {
			$post_types = array( 'post', 'page' );
		}

		$this->plugin->update_options(
			array(
				'module_enabled' => $module_enabled,
				'post_types'     => $post_types,
			)
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'msc-last-updated',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Render settings page.
	 */
	public function render_page() {
		$options = array(
			'module_enabled' => (int) $this->plugin->get_option( 'module_enabled', 1 ),
			'post_types'     => (array) $this->plugin->get_option( 'post_types', array( 'post', 'page' ) ),
		);
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		?>
		<div class="wrap msc-admin-wrap">
			<div class="msc-admin-header">
				<h1><?php echo esc_html( 'MSC Last Updated' ); ?></h1>
			</div>
			<div class="msc-admin-card">
				<?php if ( isset( $_GET['updated'] ) ) : ?>
					<div class="msc-admin-notice">
						<?php echo esc_html__( 'Settings updated.', 'msc-last-updated' ); ?>
					</div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="msc-last-updated_save_settings" />
					<?php wp_nonce_field( 'msc-last-updated_save_settings' ); ?>

					<div class="msc-admin-form-row">
						<label for="module_enabled">
							<input id="module_enabled" type="checkbox" name="module_enabled" value="1" <?php checked( 1, $options['module_enabled'] ); ?> />
							<?php echo esc_html__( 'Enable module', 'msc-last-updated' ); ?>
						</label>
					</div>

					<div class="msc-admin-form-row">
						<p><strong><?php echo esc_html__( 'Apply to post types', 'msc-last-updated' ); ?></strong></p>
						<?php foreach ( $post_types as $post_type ) : ?>
							<label style="display:block; margin: 6px 0;">
								<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $options['post_types'], true ) ); ?> />
								<?php echo esc_html( $post_type->labels->singular_name ); ?>
							</label>
						<?php endforeach; ?>
					</div>

					<button type="submit" class="msc-admin-button msc-admin-button-primary">
						<?php echo esc_html__( 'Save Settings', 'msc-last-updated' ); ?>
					</button>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render contextual upgrade page for free variant.
	 */
	public function render_upgrade_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap msc-admin-wrap">
			<div class="msc-admin-header">
				<h1><?php echo esc_html__( 'Upgrade to Pro', 'msc-last-updated' ); ?></h1>
			</div>
			<div class="msc-admin-card">
				<p><?php echo esc_html__( 'Unlock advanced features with the Pro version.', 'msc-last-updated' ); ?></p>
				<p>
					<a class="button button-primary msc-admin-button msc-admin-button-primary" href="https://anomalous.co.za" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html__( 'Learn More', 'msc-last-updated' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}
}
