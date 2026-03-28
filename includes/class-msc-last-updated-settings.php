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

		wp_safe_redirect( add_query_arg( $args, admin_url( 'options-general.php' ) ) );
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
				admin_url( 'options-general.php' )
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

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab is a safe UI routing parameter.
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';
		if ( ! in_array( $active_tab, array( 'settings', 'usage' ), true ) ) {
			$active_tab = 'settings';
		}

		$tab_url_settings = add_query_arg( array( 'page' => 'msclu-settings', 'tab' => 'settings' ), admin_url( 'options-general.php' ) );
		$tab_url_usage    = add_query_arg( array( 'page' => 'msclu-settings', 'tab' => 'usage' ), admin_url( 'options-general.php' ) );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'MSC Last Updated', 'msc-last-updated' ); ?></h1>

			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only success notice flag. ?>
			<?php if ( isset( $_GET['updated'] ) && '1' === $_GET['updated'] ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'msc-last-updated' ); ?></p></div>
			<?php endif; ?>

			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_url( $tab_url_settings ); ?>" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Settings', 'msc-last-updated' ); ?>
				</a>
				<a href="<?php echo esc_url( $tab_url_usage ); ?>" class="nav-tab <?php echo 'usage' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Usage &amp; Support', 'msc-last-updated' ); ?>
				</a>
			</nav>

			<?php if ( 'settings' === $active_tab ) : ?>

				<div class="msclu-settings-layout" style="display:flex;gap:20px;align-items:flex-start;margin-top:1em;">
					<div class="msclu-settings-sidebar" style="width:240px;flex-shrink:0;order:2;">
						<?php if ( ! $this->plugin->is_pro_active() ) : ?>
							<div class="postbox">
								<div class="postbox-header">
									<h2 class="hndle" style="font-size:14px;padding:8px 12px;">
										<?php esc_html_e( 'Upgrade to Pro', 'msc-last-updated' ); ?>
									</h2>
								</div>
								<div class="inside">
									<p><?php esc_html_e( 'Unlock powerful extras:', 'msc-last-updated' ); ?></p>
									<ul style="list-style:disc;padding-left:1.2em;margin:0 0 12px;">
										<li><?php esc_html_e( 'Relative dates (e.g. "3 days ago")', 'msc-last-updated' ); ?></li>
										<li><?php esc_html_e( 'Hybrid mode — relative then absolute', 'msc-last-updated' ); ?></li>
										<li><?php esc_html_e( 'Style presets (muted, pill, badge)', 'msc-last-updated' ); ?></li>
										<li><?php esc_html_e( 'Per-post override (show/hide/global)', 'msc-last-updated' ); ?></li>
										<li><?php esc_html_e( 'Shortcode with per-instance overrides', 'msc-last-updated' ); ?></li>
									</ul>
									<a class="button button-primary" style="width:100%;text-align:center;box-sizing:border-box;" href="https://anomalous.co.za" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'View Pro Features', 'msc-last-updated' ); ?>
									</a>
								</div>
							</div>
						<?php endif; ?>
						<div class="postbox">
							<div class="postbox-header">
								<h2 class="hndle" style="font-size:14px;padding:8px 12px;">
									<?php esc_html_e( 'Support', 'msc-last-updated' ); ?>
								</h2>
							</div>
							<div class="inside">
								<p><?php esc_html_e( 'Questions, bugs, or setup help?', 'msc-last-updated' ); ?></p>
								<a class="button" style="width:100%;text-align:center;box-sizing:border-box;" href="https://anomalous.co.za" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Get Support', 'msc-last-updated' ); ?>
								</a>
							</div>
						</div>
					</div><!-- .msclu-settings-sidebar -->

					<div class="msclu-settings-main" style="flex:1;min-width:0;order:1;">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="msc-last-updated_save_settings" />
							<?php wp_nonce_field( 'msc-last-updated_save_settings' ); ?>

							<table class="form-table" role="presentation">
								<tbody>
									<tr>
										<th scope="row"><?php esc_html_e( 'Enable output', 'msc-last-updated' ); ?></th>
										<td>
											<label for="module_enabled">
												<input id="module_enabled" type="checkbox" name="module_enabled" value="1" <?php checked( 1, $options['module_enabled'] ); ?> />
												<?php esc_html_e( 'Show Last Updated date on enabled post types.', 'msc-last-updated' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="position"><?php esc_html_e( 'Automatic placement', 'msc-last-updated' ); ?></label></th>
										<td>
											<select id="position" name="position">
												<option value="after"  <?php selected( 'after',  $options['position'] ); ?>><?php esc_html_e( 'After content',                        'msc-last-updated' ); ?></option>
												<option value="before" <?php selected( 'before', $options['position'] ); ?>><?php esc_html_e( 'Before content',                       'msc-last-updated' ); ?></option>
												<option value="both"   <?php selected( 'both',   $options['position'] ); ?>><?php esc_html_e( 'Before and after content',             'msc-last-updated' ); ?></option>
												<option value="manual" <?php selected( 'manual', $options['position'] ); ?>><?php esc_html_e( 'Manual only (template tag)',           'msc-last-updated' ); ?></option>
											</select>
											<p class="description"><?php esc_html_e( 'Use Manual to place output via the template tag in your theme. See the Usage tab for examples.', 'msc-last-updated' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="label_text"><?php esc_html_e( 'Label template', 'msc-last-updated' ); ?></label></th>
										<td>
											<input id="label_text" name="label_text" type="text" class="regular-text" value="<?php echo esc_attr( $options['label_text'] ); ?>" />
											<p class="description">
												<?php
												/* translators: %s is a literal placeholder token, shown verbatim */
												printf(
													esc_html__( 'Use %s where the date should appear, e.g. %s. Omit %s to show the label with no date.', 'msc-last-updated' ),
													'<code>%s</code>',
													'<code>Updated %s</code>',
													'<code>%s</code>'
												);
												?>
											</p>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="date_mode"><?php esc_html_e( 'Date format source', 'msc-last-updated' ); ?></label></th>
										<td>
											<select id="date_mode" name="date_mode">
												<option value="site"   <?php selected( 'site',   $options['date_mode'] ); ?>><?php esc_html_e( 'Use WordPress site date format', 'msc-last-updated' ); ?></option>
												<option value="custom" <?php selected( 'custom', $options['date_mode'] ); ?>><?php esc_html_e( 'Use custom date format',          'msc-last-updated' ); ?></option>
											</select>
											<p class="description">
												<?php
												printf(
													/* translators: %s is a URL */
													esc_html__( 'Custom format uses PHP date format tokens. %s', 'msc-last-updated' ),
													'<a href="https://wordpress.org/documentation/article/customize-date-and-time-format/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Format reference', 'msc-last-updated' ) . '</a>'
												);
												?>
											</p>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="custom_format"><?php esc_html_e( 'Custom date format', 'msc-last-updated' ); ?></label></th>
										<td>
											<input id="custom_format" name="custom_format" type="text" class="regular-text" value="<?php echo esc_attr( $options['custom_format'] ); ?>" />
											<p class="description"><?php esc_html_e( 'Only used when Date format source is set to Custom. Default: F j, Y', 'msc-last-updated' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Visibility condition', 'msc-last-updated' ); ?></th>
										<td>
											<label for="modified_only">
												<input id="modified_only" type="checkbox" name="modified_only" value="1" <?php checked( 1, $options['modified_only'] ); ?> />
												<?php esc_html_e( 'Only show when modified date differs from publish date.', 'msc-last-updated' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="post_type_mode"><?php esc_html_e( 'Post type mode', 'msc-last-updated' ); ?></label></th>
										<td>
											<select id="post_type_mode" name="post_type_mode">
												<option value="include" <?php selected( 'include', $options['post_type_mode'] ); ?>><?php esc_html_e( 'Show only on selected post types',                   'msc-last-updated' ); ?></option>
												<option value="exclude" <?php selected( 'exclude', $options['post_type_mode'] ); ?>><?php esc_html_e( 'Show on all public post types except selected', 'msc-last-updated' ); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Post types', 'msc-last-updated' ); ?></th>
										<td>
											<fieldset>
												<?php foreach ( $post_types as $post_type ) : ?>
													<label style="display:block;margin-bottom:4px;">
														<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $options['post_types'], true ) ); ?> />
														<?php echo esc_html( $post_type->labels->singular_name ); ?>
														<span style="color:#888;font-size:12px;">(<?php echo esc_html( $post_type->name ); ?>)</span>
													</label>
												<?php endforeach; ?>
											</fieldset>
										</td>
									</tr>
								</tbody>
							</table>

							<?php
							/**
							 * Renders extension settings inside the shared form (used by Pro).
							 *
							 * @param array<string,mixed> $options Current options.
							 */
							do_action( 'msclu_settings_sections', $options );
							?>

							<?php submit_button( __( 'Save Settings', 'msc-last-updated' ) ); ?>
						</form>
					</div><!-- .msclu-settings-main -->
				</div><!-- .msclu-settings-layout -->

			<?php elseif ( 'usage' === $active_tab ) : ?>

				<div style="max-width:800px;margin-top:1.5em;">

					<h2><?php esc_html_e( 'Automatic Injection', 'msc-last-updated' ); ?></h2>
					<p><?php esc_html_e( 'When a placement mode other than Manual is selected, the plugin automatically injects the last-updated label into singular post/page content via the_content filter.', 'msc-last-updated' ); ?></p>
					<table class="widefat striped" style="margin-bottom:1.5em;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Mode', 'msc-last-updated' ); ?></th>
								<th><?php esc_html_e( 'Expected output position', 'msc-last-updated' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr><td><strong><?php esc_html_e( 'After content', 'msc-last-updated' ); ?></strong></td><td><?php esc_html_e( 'Label appears below the post body.', 'msc-last-updated' ); ?></td></tr>
							<tr><td><strong><?php esc_html_e( 'Before content', 'msc-last-updated' ); ?></strong></td><td><?php esc_html_e( 'Label appears above the post body.', 'msc-last-updated' ); ?></td></tr>
							<tr><td><strong><?php esc_html_e( 'Before and after', 'msc-last-updated' ); ?></strong></td><td><?php esc_html_e( 'Label appears both above and below the post body.', 'msc-last-updated' ); ?></td></tr>
							<tr><td><strong><?php esc_html_e( 'Manual only', 'msc-last-updated' ); ?></strong></td><td><?php esc_html_e( 'Nothing is injected automatically. Use the template tag below in your theme file.', 'msc-last-updated' ); ?></td></tr>
						</tbody>
					</table>

					<h2><?php esc_html_e( 'Label Template', 'msc-last-updated' ); ?></h2>
					<p>
						<?php
						printf(
							/* translators: %s is a literal %s token placeholder shown in code */
							esc_html__( 'The Label template setting controls the text displayed. Use %s as the date placeholder.', 'msc-last-updated' ),
							'<code>%s</code>'
						);
						?>
					</p>
					<table class="widefat striped" style="margin-bottom:1.5em;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Template setting', 'msc-last-updated' ); ?></th>
								<th><?php esc_html_e( 'Example frontend output', 'msc-last-updated' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr><td><code>Updated %s</code></td><td><?php esc_html_e( 'Updated March 28, 2026', 'msc-last-updated' ); ?></td></tr>
							<tr><td><code>Last checked: %s</code></td><td><?php esc_html_e( 'Last checked: March 28, 2026', 'msc-last-updated' ); ?></td></tr>
							<tr><td><code>Fresh content</code></td><td><?php esc_html_e( 'Fresh content  (no date appended — displayed as-is)', 'msc-last-updated' ); ?></td></tr>
						</tbody>
					</table>

					<h2><?php esc_html_e( 'Template Tags', 'msc-last-updated' ); ?></h2>
					<p><?php esc_html_e( 'Use these PHP functions anywhere in your theme when placement is set to Manual.', 'msc-last-updated' ); ?></p>

					<h3 style="margin-bottom:4px;"><code>msclup_the_last_updated()</code></h3>
					<p class="description" style="margin-bottom:8px;"><?php esc_html_e( 'Echoes the rendered HTML directly. Use inside The Loop or pass a post ID.', 'msc-last-updated' ); ?></p>
					<pre style="background:#f6f7f7;border:1px solid #dcdcde;padding:12px 16px;overflow:auto;border-radius:3px;font-size:13px;">&lt;?php msclup_the_last_updated(); ?&gt;
&lt;?php msclup_the_last_updated( get_the_ID() ); ?&gt;</pre>
					<p style="margin-top:6px;"><?php esc_html_e( 'Expected HTML output:', 'msc-last-updated' ); ?></p>
					<pre style="background:#f6f7f7;border:1px solid #dcdcde;padding:12px 16px;overflow:auto;border-radius:3px;font-size:13px;">&lt;p class="msclu-last-updated"&gt;&lt;time datetime="2026-03-28T14:30:00+00:00"&gt;Updated March 28, 2026&lt;/time&gt;&lt;/p&gt;</pre>

					<h3 style="margin-bottom:4px;margin-top:1.5em;"><code>msclup_get_last_updated()</code></h3>
					<p class="description" style="margin-bottom:8px;"><?php esc_html_e( 'Returns the rendered HTML string instead of echoing. Useful for further manipulation.', 'msc-last-updated' ); ?></p>
					<pre style="background:#f6f7f7;border:1px solid #dcdcde;padding:12px 16px;overflow:auto;border-radius:3px;font-size:13px;">&lt;?php $html = msclup_get_last_updated( get_the_ID() ); ?&gt;
&lt;?php echo wp_kses_post( $html ); ?&gt;</pre>

					<h2 style="margin-top:1.5em;"><?php esc_html_e( 'Date Format Reference', 'msc-last-updated' ); ?></h2>
					<p>
						<?php
						printf(
							/* translators: %s is a URL */
							esc_html__( 'When using a custom date format, the same PHP date tokens used by WordPress apply. %s', 'msc-last-updated' ),
							'<a href="https://wordpress.org/documentation/article/customize-date-and-time-format/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View full format reference &rarr;', 'msc-last-updated' ) . '</a>'
						);
						?>
					</p>
					<table class="widefat striped" style="margin-bottom:1.5em;">
						<thead><tr><th><?php esc_html_e( 'Token', 'msc-last-updated' ); ?></th><th><?php esc_html_e( 'Example output', 'msc-last-updated' ); ?></th></tr></thead>
						<tbody>
							<tr><td><code>F j, Y</code></td><td><?php esc_html_e( 'March 28, 2026', 'msc-last-updated' ); ?></td></tr>
							<tr><td><code>d/m/Y</code></td><td><?php esc_html_e( '28/03/2026', 'msc-last-updated' ); ?></td></tr>
							<tr><td><code>Y-m-d</code></td><td><?php esc_html_e( '2026-03-28', 'msc-last-updated' ); ?></td></tr>
							<tr><td><code>j M Y</code></td><td><?php esc_html_e( '28 Mar 2026', 'msc-last-updated' ); ?></td></tr>
						</tbody>
					</table>

					<h2><?php esc_html_e( 'Frequently Asked Questions', 'msc-last-updated' ); ?></h2>

					<h3><?php esc_html_e( 'The label is not appearing on my posts.', 'msc-last-updated' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Check that Enable output is ticked on the Settings tab.', 'msc-last-updated' ); ?></li>
						<li><?php esc_html_e( 'Check that the post type (e.g. Post, Page) is selected in the Post types list.', 'msc-last-updated' ); ?></li>
						<li>
							<?php
							printf(
								/* translators: %s is a setting name */
								esc_html__( 'If %s is ticked, the label will only show when the article has been edited after its original publish date.', 'msc-last-updated' ),
								'<strong>' . esc_html__( 'Only show when modified date differs from publish date', 'msc-last-updated' ) . '</strong>'
							);
							?>
						</li>
						<li><?php esc_html_e( 'If Automatic placement is set to Manual, nothing is injected automatically — add the template tag to your theme.', 'msc-last-updated' ); ?></li>
					</ol>

					<h3><?php esc_html_e( 'How do I place the label exactly where I want it?', 'msc-last-updated' ); ?></h3>
					<p>
						<?php
						printf(
							/* translators: %1$s and %2$s are setting/function names */
							esc_html__( 'Set Automatic placement to %1$s on the Settings tab, then add %2$s in your theme template wherever you want it to appear.', 'msc-last-updated' ),
							'<strong>' . esc_html__( 'Manual only', 'msc-last-updated' ) . '</strong>',
							'<code>&lt;?php msclup_the_last_updated(); ?&gt;</code>'
						);
						?>
					</p>

					<h3><?php esc_html_e( 'Can I show just a static label with no date?', 'msc-last-updated' ); ?></h3>
					<p>
						<?php
						printf(
							/* translators: %s is a literal %s placeholder shown in code */
							esc_html__( 'Yes. Remove %s from the Label template. The text will be output as-is with no date appended.', 'msc-last-updated' ),
							'<code>%s</code>'
						);
						?>
					</p>

					<h2 style="margin-top:1.5em;"><?php esc_html_e( 'Support', 'msc-last-updated' ); ?></h2>
					<p><?php esc_html_e( 'If you need help with setup, have found a bug, or want to request a feature, get in touch.', 'msc-last-updated' ); ?></p>
					<p>
						<a class="button button-primary" href="https://anomalous.co.za" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Get Support', 'msc-last-updated' ); ?>
						</a>
					</p>
				</div>

			<?php endif; ?>
		</div>
		<?php
	}
}
