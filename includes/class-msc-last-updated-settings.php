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

		// Verify nonce with better error handling.
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'msc-last-updated_save_settings' ) ) {
			wp_die( esc_html__( 'Security check failed. Please try again.', 'msc-last-updated' ) );
		}

		error_log( 'MSC Last Updated: Settings save initiated' );
		error_log( 'MSC Last Updated: Full $_POST keys: ' . implode( ', ', array_keys( $_POST ) ) );

		$module_enabled = isset( $_POST['module_enabled'] ) ? 1 : 0;
		$post_types     = isset( $_POST['post_types'] ) ? (array) wp_unslash( $_POST['post_types'] ) : array();
		$post_types     = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );
		$post_type_mode = isset( $_POST['post_type_mode'] ) ? sanitize_key( wp_unslash( $_POST['post_type_mode'] ) ) : 'include';
		$position       = isset( $_POST['position'] ) ? sanitize_key( wp_unslash( $_POST['position'] ) ) : 'after';
		$label_text     = isset( $_POST['label_text'] ) ? sanitize_text_field( wp_unslash( $_POST['label_text'] ) ) : __( 'Updated %s', 'msc-last-updated' );
		$date_mode      = isset( $_POST['date_mode'] ) ? sanitize_key( wp_unslash( $_POST['date_mode'] ) ) : 'site';
		$custom_format  = isset( $_POST['custom_format'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_format'] ) ) : 'F j, Y';
		$modified_only  = isset( $_POST['modified_only'] ) ? 1 : 0;

		if ( empty( $post_types ) ) {
			$post_types = array( 'post', 'page' );
		}

		if ( ! in_array( $post_type_mode, array( 'include', 'exclude' ), true ) ) {
			$post_type_mode = 'include';
		}

		if ( ! in_array( $position, array( 'before', 'after', 'both', 'manual' ), true ) ) {
			$position = 'after';
		}

		if ( ! in_array( $date_mode, array( 'site', 'custom' ), true ) ) {
			$date_mode = 'site';
		}

		if ( '' === $label_text ) {
			$label_text = __( 'Updated %s', 'msc-last-updated' );
		}

		if ( '' === $custom_format ) {
			$custom_format = 'F j, Y';
		}

		$options = array(
			'module_enabled' => $module_enabled,
			'post_types'     => $post_types,
			'post_type_mode' => $post_type_mode,
			'position'       => $position,
			'label_text'     => $label_text,
			'date_mode'      => $date_mode,
			'custom_format'  => $custom_format,
			'modified_only'  => $modified_only,
		);

		error_log( 'MSC Last Updated: Base options before filter: ' . wp_json_encode( $options ) );

		/**
		 * Filters sanitized settings before they are persisted.
		 *
		 * @param array<string,mixed> $options Sanitized base options.
		 * @param array<string,mixed> $raw_post Raw request payload.
		 */
		$options = apply_filters( 'msclu_settings_sanitized_options', $options, $_POST );

		error_log( 'MSC Last Updated: Options after filter: ' . wp_json_encode( $options ) );

		$this->plugin->update_options( $options );

		error_log( 'MSC Last Updated: Options saved to database' );

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
			'post_type_mode' => (string) $this->plugin->get_option( 'post_type_mode', 'include' ),
			'position'       => (string) $this->plugin->get_option( 'position', 'after' ),
			'label_text'     => (string) $this->plugin->get_option( 'label_text', __( 'Updated %s', 'msc-last-updated' ) ),
			'date_mode'      => (string) $this->plugin->get_option( 'date_mode', 'site' ),
			'custom_format'  => (string) $this->plugin->get_option( 'custom_format', 'F j, Y' ),
			'modified_only'  => (int) $this->plugin->get_option( 'modified_only', 1 ),
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
							<th scope="row"><label for="post_type_mode"><?php esc_html_e( 'Post type visibility mode', 'msc-last-updated' ); ?></label></th>
							<td>
								<select id="post_type_mode" name="post_type_mode">
									<option value="include" <?php selected( 'include', $options['post_type_mode'] ); ?>><?php esc_html_e( 'Show only on selected post types', 'msc-last-updated' ); ?></option>
									<option value="exclude" <?php selected( 'exclude', $options['post_type_mode'] ); ?>><?php esc_html_e( 'Show on all public post types except selected', 'msc-last-updated' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="position"><?php esc_html_e( 'Automatic placement', 'msc-last-updated' ); ?></label></th>
							<td>
								<select id="position" name="position">
									<option value="after" <?php selected( 'after', $options['position'] ); ?>><?php esc_html_e( 'After content', 'msc-last-updated' ); ?></option>
									<option value="before" <?php selected( 'before', $options['position'] ); ?>><?php esc_html_e( 'Before content', 'msc-last-updated' ); ?></option>
									<option value="both" <?php selected( 'both', $options['position'] ); ?>><?php esc_html_e( 'Before and after content', 'msc-last-updated' ); ?></option>
									<option value="manual" <?php selected( 'manual', $options['position'] ); ?>><?php esc_html_e( 'Manual only (template tag/shortcode)', 'msc-last-updated' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="label_text"><?php esc_html_e( 'Label template', 'msc-last-updated' ); ?></label></th>
							<td>
								<input id="label_text" name="label_text" type="text" class="regular-text" value="<?php echo esc_attr( $options['label_text'] ); ?>" />
								<p class="description"><?php esc_html_e( 'Use %s where the formatted date should appear.', 'msc-last-updated' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="date_mode"><?php esc_html_e( 'Date format source', 'msc-last-updated' ); ?></label></th>
							<td>
								<select id="date_mode" name="date_mode">
									<option value="site" <?php selected( 'site', $options['date_mode'] ); ?>><?php esc_html_e( 'Use WordPress site date format', 'msc-last-updated' ); ?></option>
									<option value="custom" <?php selected( 'custom', $options['date_mode'] ); ?>><?php esc_html_e( 'Use custom date format', 'msc-last-updated' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Custom format uses WordPress date format tokens.', 'msc-last-updated' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="custom_format"><?php esc_html_e( 'Custom date format', 'msc-last-updated' ); ?></label></th>
							<td>
								<input id="custom_format" name="custom_format" type="text" class="regular-text" value="<?php echo esc_attr( $options['custom_format'] ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Visibility', 'msc-last-updated' ); ?></th>
							<td>
								<label for="modified_only">
									<input id="modified_only" type="checkbox" name="modified_only" value="1" <?php checked( 1, $options['modified_only'] ); ?> />
									<?php esc_html_e( 'Only show when modified date differs from publish date.', 'msc-last-updated' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Post types', 'msc-last-updated' ); ?></th>
							<td>
								<fieldset>
									<?php foreach ( $post_types as $post_type ) : ?>
										<label>
											<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $options['post_types'], true ) ); ?> />
											<?php echo esc_html( $post_type->labels->singular_name ); ?>
										</label>
										<br />
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
