<?php
/**
 * Admin settings class for MSC Last Updated.
 *
 * @package MSC_Last_Updated
 */

namespace MSC_Last_Updated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the Last Updated admin pages.
 */
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
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_pro_settings' ) );
		add_action( 'admin_post_msc-last-updated_save_settings', array( $this, 'handle_save' ) );
	}

	/**
	 * Register admin page.
	 */
	public function register_menu() {
		add_options_page(
			esc_html__( 'MSC Last Updated', 'msc-last-updated' ),
			esc_html__( 'MSC Last Updated', 'msc-last-updated' ),
			'manage_options',
			'msclu-settings',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Redirects legacy menu slugs to the unified Settings page.
	 *
	 * @return void
	 */
	public function maybe_redirect_to_pro_settings() {
		if ( ! is_admin() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe admin-page routing based on current screen query args.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( ! in_array( $page, array( 'msc-last-updated', 'msclup-settings', 'msc-site-care', 'msc-site-care-support', 'msc-site-care-upgrade' ), true ) ) {
			return;
		}

		$args = array(
			'page' => 'msclu-settings',
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserves a benign success flag during redirect.
		if ( isset( $_GET['updated'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserves a benign success flag during redirect.
			$args['updated'] = sanitize_key( wp_unslash( $_GET['updated'] ) );
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
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

		$options = array(
			'module_enabled' => $module_enabled,
			'post_types'     => $post_types,
		);

		/**
		 * Filters sanitized settings before they are persisted.
		 *
		 * @param array<string,mixed> $options Sanitized base options.
		 * @param array<string,mixed> $raw_post Raw request payload.
		 */
		$options = apply_filters( 'msclu_settings_sanitized_options', $options, $_POST );

		$this->plugin->update_options( $options );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'msclu-settings',
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
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = array(
			'module_enabled' => (int) $this->plugin->get_option( 'module_enabled', 1 ),
			'post_types'     => (array) $this->plugin->get_option( 'post_types', array( 'post', 'page' ) ),
		);

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'MSC Last Updated', 'msc-last-updated' ); ?></h1>

			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only success notice flag. ?>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings updated.', 'msc-last-updated' ); ?></p></div>
			<?php endif; ?>

			<p><?php echo esc_html__( 'Control where the updated date appears for your public content.', 'msc-last-updated' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="msc-last-updated_save_settings" />
				<?php wp_nonce_field( 'msc-last-updated_save_settings' ); ?>

				<h2><?php esc_html_e( 'General Settings', 'msc-last-updated' ); ?></h2>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Enable output', 'msc-last-updated' ); ?></th>
							<td>
								<label for="module_enabled">
									<input id="module_enabled" type="checkbox" name="module_enabled" value="1" <?php checked( 1, $options['module_enabled'] ); ?> />
									<?php esc_html_e( 'Show Last Updated output on enabled post types.', 'msc-last-updated' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Post types', 'msc-last-updated' ); ?></th>
							<td>
								<fieldset>
									<?php foreach ( $post_types as $post_type ) : ?>
										<label style="display:block; margin: 6px 0;">
											<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $options['post_types'], true ) ); ?> />
											<?php echo esc_html( $post_type->labels->singular_name ); ?>
										</label>
									<?php endforeach; ?>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>

				<?php
				/**
				 * Renders extension settings inside the shared form.
				 *
				 * @param array<string,mixed> $options Current options.
				 */
				do_action( 'msclu_settings_sections', $options );
				?>

				<?php submit_button( __( 'Save Settings', 'msc-last-updated' ) ); ?>
			</form>

			<h2><?php esc_html_e( 'Support', 'msc-last-updated' ); ?></h2>
			<p><?php esc_html_e( 'Need help with setup or troubleshooting?', 'msc-last-updated' ); ?></p>
			<p>
				<a class="button" href="https://anomalous.co.za" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Get Support', 'msc-last-updated' ); ?>
				</a>
			</p>

			<?php if ( ! $this->plugin->is_pro_active() ) : ?>
				<hr />
				<h2><?php esc_html_e( 'Upgrade to Pro', 'msc-last-updated' ); ?></h2>
				<p><?php esc_html_e( 'Unlock relative dates, style presets, per-post overrides, and shortcode controls.', 'msc-last-updated' ); ?></p>
				<p>
					<a class="button button-primary" href="https://anomalous.co.za" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View Pro Features', 'msc-last-updated' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}
